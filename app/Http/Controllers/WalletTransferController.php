<?php

namespace App\Http\Controllers;

use App\Models\MoneyWallet;
use App\Models\WalletTransfer;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletTransferController extends Controller
{
    // ── Danh sách lịch sử chuyển tiền ────────────────────
    public function index()
    {
        $userId = Auth::id();

        $transfers = WalletTransfer::where('user_id', $userId)
            ->with(['fromWallet', 'toWallet', 'category'])
            ->orderByDesc('ngay_chuyen')
            ->orderByDesc('created_at')
            ->paginate(20);

        $wallets = MoneyWallet::forUser($userId)->active()->get();

        // Danh mục để chọn khi tạo chuyển khoản
        $categories = Category::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->whereNotNull('danh_muc_cha_id')
            ->orderBy('loai_danh_muc')
            ->orderBy('ten_danh_muc')
            ->get();

        return view('money-wallets.transfers.index', compact('transfers', 'wallets', 'categories'));
    }

    // ── Thực hiện chuyển tiền ─────────────────────────────
    public function store(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'from_wallet_id' => 'required|exists:money_wallets,id',
            'to_wallet_id'   => 'required|exists:money_wallets,id|different:from_wallet_id',
            'so_tien'        => 'required|numeric|min:1000|max:999999999999',
            'phi_chuyen'     => 'nullable|numeric|min:0',
            'category_id'    => 'required|exists:categories,id',
            'ngay_chuyen'    => 'required|date|before_or_equal:today',
            'ghi_chu'        => 'nullable|string|max:500',
        ], [
            'from_wallet_id.required' => 'Vui lòng chọn ví nguồn',
            'to_wallet_id.required'   => 'Vui lòng chọn ví đích',
            'to_wallet_id.different'  => 'Ví nguồn và ví đích không được giống nhau',
            'so_tien.required'        => 'Vui lòng nhập số tiền',
            'so_tien.min'             => 'Số tiền phải từ 1,000 trở lên',
            'category_id.required'    => 'Vui lòng chọn danh mục',
            'ngay_chuyen.required'    => 'Vui lòng chọn ngày',
        ]);

        // Kiểm tra ví thuộc user
        $fromWallet = MoneyWallet::where('id', $validated['from_wallet_id'])
            ->where('user_id', $userId)->firstOrFail();
        $toWallet = MoneyWallet::where('id', $validated['to_wallet_id'])
            ->where('user_id', $userId)->firstOrFail();

        $soTien    = (float) $validated['so_tien'];
        $phiChuyen = (float) ($validated['phi_chuyen'] ?? 0);
        $tongTru   = $soTien + $phiChuyen;

        // Kiểm tra số dư ví nguồn
        if ($fromWallet->so_du < $tongTru) {
            return back()->withInput()->with(
                'error',
                'Ví nguồn không đủ số dư! ' .
                'Cần: ' . number_format($tongTru) . ' ' . $fromWallet->don_vi_tien_te .
                ' | Hiện có: ' . number_format($fromWallet->so_du) . ' ' . $fromWallet->don_vi_tien_te
            );
        }

        DB::beginTransaction();
        try {
            $ghiChu = $validated['ghi_chu'] ?? '';
            $ngay   = $validated['ngay_chuyen'];
            $catId  = $validated['category_id'];

            // Lấy category để xác định loại
            $category = Category::find($catId);

            // Tạo giao dịch CHI từ ví nguồn
            $fromTx = Transaction::create([
                'user_id'                => $userId,
                'money_wallet_id'        => $fromWallet->id,
                'category_id'            => $catId,
                'loai_giao_dich'         => 'CHI',
                'phuong_thuc_thanh_toan' => 'Chuyển khoản',
                'so_tien'                => $tongTru,
                'ngay_giao_dich'         => $ngay,
                'ghi_chu'                => "[Chuyển ví → {$toWallet->ten_vi}]" . ($ghiChu ? " {$ghiChu}" : ''),
                'la_chuyen_vi'           => true,
            ]);

            // Tạo giao dịch THU vào ví đích
            $toTx = Transaction::create([
                'user_id'                => $userId,
                'money_wallet_id'        => $toWallet->id,
                'category_id'            => $catId,
                'loai_giao_dich'         => 'THU',
                'phuong_thuc_thanh_toan' => 'Chuyển khoản',
                'so_tien'                => $soTien,
                'ngay_giao_dich'         => $ngay,
                'ghi_chu'                => "[Nhận từ ví ← {$fromWallet->ten_vi}]" . ($ghiChu ? " {$ghiChu}" : ''),
                'la_chuyen_vi'           => true,
            ]);

            // Ghi wallet_transfers
            WalletTransfer::create([
                'user_id'             => $userId,
                'from_wallet_id'      => $fromWallet->id,
                'to_wallet_id'        => $toWallet->id,
                'so_tien'             => $soTien,
                'phi_chuyen'          => $phiChuyen,
                'from_transaction_id' => $fromTx->id,
                'to_transaction_id'   => $toTx->id,
                'category_id'         => $catId,
                'ngay_chuyen'         => $ngay,
                'ghi_chu'             => $ghiChu,
            ]);

            // Cập nhật số dư ví
            $fromWallet->decrement('so_du', $tongTru);
            $toWallet->increment('so_du', $soTien);

            DB::commit();

            return redirect()->route('wallet-transfers.index')
                ->with('success',
                    "Đã chuyển " . number_format($soTien) . " {$fromWallet->don_vi_tien_te} " .
                    "từ \"{$fromWallet->ten_vi}\" sang \"{$toWallet->ten_vi}\"!"
                );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // ── Hủy chuyển tiền (hoàn tác) ───────────────────────
    public function destroy(WalletTransfer $walletTransfer)
    {
        abort_if($walletTransfer->user_id !== Auth::id(), 403);

        DB::beginTransaction();
        try {
            $soTien    = (float) $walletTransfer->so_tien;
            $phiChuyen = (float) $walletTransfer->phi_chuyen;

            // Hoàn tác số dư
            $walletTransfer->fromWallet?->increment('so_du', $soTien + $phiChuyen);
            $walletTransfer->toWallet?->decrement('so_du', $soTien);

            // Xóa 2 giao dịch liên quan
            Transaction::whereIn('id', array_filter([
                $walletTransfer->from_transaction_id,
                $walletTransfer->to_transaction_id,
            ]))->delete();

            $walletTransfer->delete();

            DB::commit();

            return back()->with('success', 'Đã hủy và hoàn tác giao dịch chuyển tiền.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi: ' . $e->getMessage());
        }
    }
}
