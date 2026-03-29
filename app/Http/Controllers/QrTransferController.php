<?php

namespace App\Http\Controllers;

use App\Models\MoneyWallet;
use App\Models\QrTransfer;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QrTransferController extends Controller
{
    // Trang chính: tạo QR + lịch sử
    public function index()
    {
        $userId  = Auth::id();
        $wallets = MoneyWallet::forUser($userId)->active()->orderBy('ten_vi')->get();

        $history = QrTransfer::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver', 'senderWallet', 'receiverWallet'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('money-wallets.qr-transfer', compact('wallets', 'history'));
    }

    // Tạo QR code mới
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'wallet_id' => 'required|exists:money_wallets,id',
            'so_tien'   => 'required|numeric|min:1000|max:999999999',
            'ghi_chu'   => 'nullable|string|max:255',
        ], [
            'so_tien.min' => 'Số tiền tối thiểu 1.000đ',
            'so_tien.max' => 'Số tiền quá lớn',
        ]);

        $wallet = MoneyWallet::where('id', $validated['wallet_id'])
            ->where('user_id', Auth::id())
            ->where('trang_thai', 'active')
            ->firstOrFail();

        if ($wallet->so_du < $validated['so_tien']) {
            return back()->withInput()->with('error',
                "Ví \"{$wallet->ten_vi}\" không đủ số dư! " .
                "Cần: " . number_format($validated['so_tien']) . "đ | " .
                "Hiện có: " . number_format($wallet->so_du) . "đ"
            );
        }

        $token = Str::random(32);

        $qrTransfer = QrTransfer::create([
            'sender_id'        => Auth::id(),
            'receiver_id'      => Auth::id(), // placeholder, cập nhật khi confirm
            'sender_wallet_id' => $wallet->id,
            'so_tien'          => $validated['so_tien'],
            'ghi_chu'          => $validated['ghi_chu'] ?? null,
            'qr_token'         => $token,
            'trang_thai'       => 'pending',
            'expires_at'       => now()->addMinutes(15),
        ]);

        // URL nhúng vào QR
        $qrUrl = route('money-wallets.qr.scan-page', $token);

        // Tạo QR bằng Google Charts API (không cần cài package)
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&ecc=H&data=' . urlencode($qrUrl);

        return view('money-wallets.qr-result', compact('qrTransfer', 'qrApiUrl', 'wallet', 'qrUrl'));
    }

    // Trang scan (người nhận mở link này)
    public function scanPage(string $token)
    {
        $qrTransfer = QrTransfer::where('qr_token', $token)
            ->with(['sender', 'senderWallet'])
            ->firstOrFail();

        if (!$qrTransfer->isUsable()) {
            $msg = $qrTransfer->trang_thai === 'completed'
                ? 'QR code này đã được sử dụng.'
                : ($qrTransfer->trang_thai === 'cancelled'
                    ? 'QR code này đã bị huỷ.'
                    : 'QR code đã hết hạn (15 phút). Yêu cầu người gửi tạo mã mới.');
            return view('money-wallets.qr-invalid', compact('msg'));
        }

        if ($qrTransfer->sender_id === Auth::id()) {
            return view('money-wallets.qr-invalid', [
                'msg' => 'Bạn không thể nhận tiền từ chính mình.'
            ]);
        }

        $myWallets = MoneyWallet::forUser(Auth::id())->active()->orderBy('ten_vi')->get();

        return view('money-wallets.qr-scan', compact('qrTransfer', 'myWallets', 'token'));
    }

    // Xác nhận nhận tiền
    public function confirm(Request $request, string $token)
    {
        $validated = $request->validate([
            'receiver_wallet_id' => 'required|exists:money_wallets,id',
        ]);

        $qrTransfer = QrTransfer::where('qr_token', $token)
            ->with(['sender', 'senderWallet'])
            ->firstOrFail();

        if (!$qrTransfer->isUsable()) {
            return back()->with('error', 'QR code không còn hiệu lực.');
        }

        if ($qrTransfer->sender_id === Auth::id()) {
            return back()->with('error', 'Bạn không thể tự chuyển cho mình.');
        }

        $receiverWallet = MoneyWallet::where('id', $validated['receiver_wallet_id'])
            ->where('user_id', Auth::id())
            ->where('trang_thai', 'active')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $senderWallet = MoneyWallet::where('id', $qrTransfer->sender_wallet_id)
                ->lockForUpdate()->firstOrFail();

            if ($senderWallet->so_du < $qrTransfer->so_tien) {
                DB::rollBack();
                return back()->with('error', 'Người gửi không còn đủ số dư để thực hiện giao dịch.');
            }

            // Lấy/tạo category
            $catChi = $this->getOrCreateQrCategory($qrTransfer->sender_id, 'CHI');
            $catThu = $this->getOrCreateQrCategory(Auth::id(), 'THU');

            // Giao dịch CHI cho người gửi
            Transaction::create([
                'user_id'                => $qrTransfer->sender_id,
                'money_wallet_id'        => $senderWallet->id,
                'category_id'            => $catChi->id,
                'loai_giao_dich'         => 'CHI',
                'phuong_thuc_thanh_toan' => 'Chuyển khoản',
                'so_tien'                => $qrTransfer->so_tien,
                'ngay_giao_dich'         => now()->toDateString(),
                'ghi_chu'                => '[QR] → ' . Auth::user()->name .
                                           ($qrTransfer->ghi_chu ? ': ' . $qrTransfer->ghi_chu : ''),
                'la_chuyen_vi'           => false,
            ]);

            // Giao dịch THU cho người nhận
            Transaction::create([
                'user_id'                => Auth::id(),
                'money_wallet_id'        => $receiverWallet->id,
                'category_id'            => $catThu->id,
                'loai_giao_dich'         => 'THU',
                'phuong_thuc_thanh_toan' => 'Chuyển khoản',
                'so_tien'                => $qrTransfer->so_tien,
                'ngay_giao_dich'         => now()->toDateString(),
                'ghi_chu'                => '[QR] ← ' . $qrTransfer->sender->name .
                                           ($qrTransfer->ghi_chu ? ': ' . $qrTransfer->ghi_chu : ''),
                'la_chuyen_vi'           => false,
            ]);

            // Cập nhật số dư ví
            $senderWallet->decrement('so_du', $qrTransfer->so_tien);
            $receiverWallet->increment('so_du', $qrTransfer->so_tien);

            // Hoàn tất QR
            $qrTransfer->update([
                'receiver_id'        => Auth::id(),
                'receiver_wallet_id' => $receiverWallet->id,
                'trang_thai'         => 'completed',
                'completed_at'       => now(),
            ]);

            DB::commit();

            return redirect()->route('money-wallets.qr.index')
                ->with('success',
                    '✅ Đã nhận ' . number_format($qrTransfer->so_tien) . 'đ từ ' . $qrTransfer->sender->name . '!'
                );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Huỷ QR (chỉ người tạo)
    public function cancel(QrTransfer $qrTransfer)
    {
        abort_if($qrTransfer->sender_id !== Auth::id(), 403);
        abort_if(!$qrTransfer->isPending(), 422, 'QR không ở trạng thái chờ.');

        $qrTransfer->update(['trang_thai' => 'cancelled']);

        return back()->with('success', 'Đã huỷ QR code.');
    }

    // Helper: lấy hoặc tạo category QR
    private function getOrCreateQrCategory(int $userId, string $loai): Category
    {
        $parent = Category::firstOrCreate(
            [
                'user_id'        => $userId,
                'ten_danh_muc'   => 'Chuyển khoản QR',
                'danh_muc_cha_id'=> null,
                'loai_danh_muc'  => $loai,
            ],
            [
                'bieu_tuong' => '📱',
                'trang_thai' => true,
            ]
        );

        return Category::firstOrCreate(
            [
                'user_id'        => $userId,
                'ten_danh_muc'   => ($loai === 'CHI' ? 'Gửi QR' : 'Nhận QR'),
                'danh_muc_cha_id'=> $parent->id,
                'loai_danh_muc'  => $loai,
            ],
            [
                'bieu_tuong' => ($loai === 'CHI' ? '📤' : '📥'),
                'trang_thai' => true,
            ]
        );
    }
}
