<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoneyWallet;
use App\Models\WalletTransfer;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletTransferController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        $transfers = WalletTransfer::where('user_id', $userId)
            ->with(['fromWallet', 'toWallet', 'category'])
            ->orderByDesc('ngay_chuyen')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($transfers);
    }

    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'from_wallet_id' => 'required|exists:money_wallets,id',
            'to_wallet_id'   => 'required|exists:money_wallets,id|different:from_wallet_id',
            'so_tien'        => 'required|numeric|min:1000|max:999999999999',
            'phi_chuyen'     => 'nullable|numeric|min:0',
            'category_id'    => 'nullable|exists:categories,id',
            'ngay_chuyen'    => 'required|date|before_or_equal:today',
            'ghi_chu'        => 'nullable|string|max:500',
        ]);

        $fromWallet = MoneyWallet::where('id', $validated['from_wallet_id'])
            ->where('user_id', $userId)->firstOrFail();
        $toWallet = MoneyWallet::where('id', $validated['to_wallet_id'])
            ->where('user_id', $userId)->firstOrFail();

        $soTien    = (float) $validated['so_tien'];
        $phiChuyen = (float) ($validated['phi_chuyen'] ?? 0);
        $tongTru   = $soTien + $phiChuyen;

        if ($fromWallet->so_du < $tongTru) {
            return response()->json([
                'message' => 'Ví nguồn không đủ số dư! ' .
                    'Cần: ' . number_format($tongTru) . ' ' . $fromWallet->don_vi_tien_te .
                    ' | Hiện có: ' . number_format($fromWallet->so_du) . ' ' . $fromWallet->don_vi_tien_te,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $ghiChu = $validated['ghi_chu'] ?? '';
            $ngay   = $validated['ngay_chuyen'];
            $catId  = $validated['category_id'] ?? null;

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

            $transfer = WalletTransfer::create([
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

            $fromWallet->decrement('so_du', $tongTru);
            $toWallet->increment('so_du', $soTien);

            DB::commit();

            return response()->json($transfer->load(['fromWallet', 'toWallet']), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(WalletTransfer $walletTransfer): JsonResponse
    {
        abort_if($walletTransfer->user_id !== Auth::id(), 403);

        DB::beginTransaction();
        try {
            $soTien    = (float) $walletTransfer->so_tien;
            $phiChuyen = (float) $walletTransfer->phi_chuyen;

            $walletTransfer->fromWallet?->increment('so_du', $soTien + $phiChuyen);
            $walletTransfer->toWallet?->decrement('so_du', $soTien);

            Transaction::whereIn('id', array_filter([
                $walletTransfer->from_transaction_id,
                $walletTransfer->to_transaction_id,
            ]))->delete();

            $walletTransfer->delete();

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi: ' . $e->getMessage()], 500);
        }
    }
}
