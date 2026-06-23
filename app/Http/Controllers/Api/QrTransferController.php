<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoneyWallet;
use App\Models\QrTransfer;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QrTransferController extends Controller
{
    public function history(): JsonResponse
    {
        $userId = Auth::id();

        $history = QrTransfer::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver', 'senderWallet', 'receiverWallet'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return response()->json($history);
    }

    public function show(string $token): JsonResponse
    {
        $qrTransfer = QrTransfer::where('qr_token', $token)
            ->with(['sender', 'senderWallet'])
            ->first();

        if (!$qrTransfer) {
            return response()->json(['message' => 'QR không tồn tại.'], 404);
        }

        return response()->json($qrTransfer);
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wallet_id' => 'required|exists:money_wallets,id',
            'so_tien'   => 'required|numeric|min:1000|max:999999999',
            'ghi_chu'   => 'nullable|string|max:255',
        ]);

        $wallet = MoneyWallet::where('id', $validated['wallet_id'])
            ->where('user_id', Auth::id())
            ->where('trang_thai', 'active')
            ->firstOrFail();//lấy bản ghi đầu tiên tìm đc nếu ko có thì báo lỗi ngay

        if ($wallet->so_du < $validated['so_tien']) {
            return response()->json([
                'message' => "Ví \"{$wallet->ten_vi}\" không đủ số dư! " .
                    "Cần: " . number_format($validated['so_tien']) . "đ | " .
                    "Hiện có: " . number_format($wallet->so_du) . "đ",
            ], 422);
        }

        $token = Str::random(32);

        $qrTransfer = QrTransfer::create([
            'sender_id'        => Auth::id(),
            'receiver_id'      => Auth::id(),
            'sender_wallet_id' => $wallet->id,
            'so_tien'          => $validated['so_tien'],
            'ghi_chu'          => $validated['ghi_chu'] ?? null,
            'qr_token'         => $token,
            'trang_thai'       => 'pending',
            'expires_at'       => now()->addMinutes(15),
        ]);

        return response()->json([
            'id'        => $qrTransfer->id,
            'qr_token'  => $token,
        ], 201);
    }

    public function confirm(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'receiver_wallet_id' => 'required|exists:money_wallets,id',
        ]);

        $qrTransfer = QrTransfer::where('qr_token', $token)
            ->with(['sender', 'senderWallet'])
            ->first();

        if (!$qrTransfer) {
            return response()->json(['message' => 'QR không tồn tại.'], 404);
        }

        if (!$qrTransfer->isUsable()) {
            return response()->json(['message' => 'QR code không còn hiệu lực.'], 422);
        }

        if ($qrTransfer->sender_id === Auth::id()) {
            return response()->json(['message' => 'Bạn không thể tự chuyển cho mình.'], 422);
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
                return response()->json(['message' => 'Người gửi không còn đủ số dư để thực hiện giao dịch.'], 422);
            }

            $catChi = $this->getOrCreateQrCategory($qrTransfer->sender_id, 'CHI');
            $catThu = $this->getOrCreateQrCategory(Auth::id(), 'THU');

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

            $senderWallet->decrement('so_du', $qrTransfer->so_tien);
            $receiverWallet->increment('so_du', $qrTransfer->so_tien);

            $qrTransfer->update([
                'receiver_id'        => Auth::id(),
                'receiver_wallet_id' => $receiverWallet->id,
                'trang_thai'         => 'completed',
                'completed_at'       => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    public function cancel(QrTransfer $qrTransfer): JsonResponse
    {
        abort_if($qrTransfer->sender_id !== Auth::id(), 403);
        abort_if(!$qrTransfer->isPending(), 422, 'QR không ở trạng thái chờ.');

        $qrTransfer->update(['trang_thai' => 'cancelled']);

        return response()->json(['success' => true]);
    }

    private function getOrCreateQrCategory(int $userId, string $loai): Category
    {
        $parent = Category::firstOrCreate(
            [
                'user_id'         => $userId,
                'ten_danh_muc'    => 'Chuyển khoản QR',
                'danh_muc_cha_id' => null,
                'loai_danh_muc'   => $loai,
            ],
            [
                'bieu_tuong' => '📱',
                'trang_thai' => true,
            ]
        );

        return Category::firstOrCreate(
            [
                'user_id'         => $userId,
                'ten_danh_muc'    => ($loai === 'CHI' ? 'Gửi QR' : 'Nhận QR'),
                'danh_muc_cha_id' => $parent->id,
                'loai_danh_muc'   => $loai,
            ],
            [
                'bieu_tuong' => ($loai === 'CHI' ? '📤' : '📥'),
                'trang_thai' => true,
            ]
        );
    }
}
