<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoneyWallet;
use App\Models\WalletAdjustment;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MoneyWalletController extends Controller
{
    public function index(): JsonResponse
    {
        $wallets = MoneyWallet::forUser(Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json($wallets);
    }

    public function summary(): JsonResponse
    {
        $userId = Auth::id();

        $wallets = MoneyWallet::forUser($userId)->active()->get();

        $tongThu = Transaction::where('user_id', $userId)
            ->where('loai_giao_dich', 'THU')
            ->where('la_chuyen_vi', false)
            ->sum('so_tien');

        $tongChi = Transaction::where('user_id', $userId)
            ->where('loai_giao_dich', 'CHI')
            ->where('la_chuyen_vi', false)
            ->sum('so_tien');

        $tongTaiSan  = $tongThu - $tongChi;
        $tongSoDuVi = $wallets->sum('so_du');

        return response()->json([
            'tong_tai_san'  => $tongTaiSan,
            'tong_thu'      => $tongThu,
            'tong_chi'      => $tongChi,
            'tong_vi'       => $wallets->count(),
            'tong_so_du_vi' => $tongSoDuVi,
            'con_lai'       => $tongTaiSan - $tongSoDuVi,
        ]);
    }

    public function show(MoneyWallet $moneyWallet): JsonResponse
    {
        $this->checkOwnership($moneyWallet);

        $moneyWallet->load('transactions.category');

        $moneyWallet->stats = [
            'tong_thu' => $moneyWallet->getTotalIncome(),
            'tong_chi' => $moneyWallet->getTotalExpense(),
        ];

        return response()->json($moneyWallet);
    }

    public function transactions(MoneyWallet $moneyWallet): JsonResponse
    {
        $this->checkOwnership($moneyWallet);

        $transactions = $moneyWallet->transactions()
            ->where('la_chuyen_vi', false)
            ->with('category')
            ->orderByDesc('ngay_giao_dich')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($transactions->items());
    }

    public function transfers(MoneyWallet $moneyWallet): JsonResponse
    {
        $this->checkOwnership($moneyWallet);

        $transfers = \App\Models\WalletTransfer::where(function ($q) use ($moneyWallet) {
                $q->where('from_wallet_id', $moneyWallet->id)
                  ->orWhere('to_wallet_id', $moneyWallet->id);
            })
            ->where('user_id', Auth::id())
            ->with(['fromWallet', 'toWallet', 'category'])
            ->orderByDesc('ngay_chuyen')
            ->limit(20)
            ->get();

        return response()->json($transfers);
    }

    public function adjustments(MoneyWallet $moneyWallet): JsonResponse
    {
        $this->checkOwnership($moneyWallet);

        $adjustments = $moneyWallet->adjustments()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json($adjustments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ten_vi'         => 'required|string|max:100',
            'loai_vi'        => 'required|in:tien_mat,ngan_hang,vi_dien_tu,the_tin_dung,dau_tu,khac',
            'so_du_ban_dau'  => 'required|numeric|min:0|max:999999999999',
            'don_vi_tien_te' => 'required|string|max:10',
            'bieu_tuong'     => 'nullable|string|max:50',
            'mo_ta'          => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $tongThu = Transaction::where('user_id', Auth::id())
                ->where('loai_giao_dich', 'THU')
                ->where('la_chuyen_vi', false)
                ->sum('so_tien');

            $tongChi = Transaction::where('user_id', Auth::id())
                ->where('loai_giao_dich', 'CHI')
                ->where('la_chuyen_vi', false)
                ->sum('so_tien');

            $tongTaiSan = $tongThu - $tongChi;

            $tongSoDuHienTai = MoneyWallet::forUser(Auth::id())
                ->active()
                 ->sum('so_du');

            $wallet = MoneyWallet::create([
                'user_id'        => Auth::id(),
                'ten_vi'         => trim($validated['ten_vi']),
                'loai_vi'        => $validated['loai_vi'],
                'so_du'          => $validated['so_du_ban_dau'],
                'so_du_ban_dau'  => $validated['so_du_ban_dau'],
                'don_vi_tien_te' => strtoupper($validated['don_vi_tien_te']),
                'bieu_tuong'     => $validated['bieu_tuong'] ?? '💰',
                'mo_ta'          => $validated['mo_ta'] ?? null,
                'trang_thai'     => 'active',
            ]);

            DB::commit();

            return response()->json($wallet, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, MoneyWallet $moneyWallet): JsonResponse
    {
        $this->checkOwnership($moneyWallet);

        $validated = $request->validate([
            'ten_vi'         => 'required|string|max:100',
            'loai_vi'        => 'required|in:tien_mat,ngan_hang,vi_dien_tu,the_tin_dung,dau_tu,khac',
            'don_vi_tien_te' => 'required|string|max:10',
            'bieu_tuong'     => 'nullable|string|max:50',
            'mo_ta'          => 'nullable|string|max:500',
        ]);

        $moneyWallet->update([
            'ten_vi'         => trim($validated['ten_vi']),
            'loai_vi'        => $validated['loai_vi'],
            'don_vi_tien_te' => strtoupper($validated['don_vi_tien_te']),
            'bieu_tuong'     => $validated['bieu_tuong'] ?? $moneyWallet->bieu_tuong,
            'mo_ta'          => $validated['mo_ta'] ?? null,
        ]);

        return response()->json($moneyWallet->fresh());
    }

    public function destroy(MoneyWallet $moneyWallet): JsonResponse
    {
        $this->checkOwnership($moneyWallet);

        $activeCount = MoneyWallet::where('user_id', Auth::id())
            ->where('trang_thai', 'active')
            ->where('id', '!=', $moneyWallet->id)
            ->count();

        if ($activeCount < 1) {
            return response()->json([
                'message' => 'Không thể xóa ví duy nhất. Bạn cần ít nhất 1 ví đang hoạt động.',
            ], 422);
        }

        $moneyWallet->delete();

        return response()->json(['success' => true, 'deleted' => true]);
    }

    public function restore(MoneyWallet $moneyWallet): JsonResponse
    {
        $this->checkOwnership($moneyWallet);

        $moneyWallet->update(['trang_thai' => 'active']);

        return response()->json(['success' => true]);
    }

    public function adjust(Request $request, MoneyWallet $moneyWallet): JsonResponse
    {
        $this->checkOwnership($moneyWallet);

        $validated = $request->validate([
            'so_du_thuc_te' => 'required|numeric|min:0|max:999999999999',
            'ly_do'         => 'nullable|string|max:255',
            'category_id'   => 'required|exists:categories,id',
        ]);

        $soDuHienTai = (float) $moneyWallet->so_du;
        $soDuMoi     = (float) $validated['so_du_thuc_te'];
        $chenhLech   = $soDuMoi - $soDuHienTai;

        if (abs($chenhLech) < 0.01) {
            return response()->json(['success' => true, 'message' => 'Số dư không thay đổi.']);
        }

        DB::beginTransaction();
        try {
            $loaiGiaoDich = $chenhLech > 0 ? 'THU' : 'CHI';

            $transaction = Transaction::create([
                'user_id'                => Auth::id(),
                'money_wallet_id'        => $moneyWallet->id,
                'category_id'            => $validated['category_id'],
                'loai_giao_dich'         => $loaiGiaoDich,
                'phuong_thuc_thanh_toan' => 'Chuyển khoản',
                'so_tien'                => abs($chenhLech),
                'ngay_giao_dich'         => now()->toDateString(),
                'ghi_chu'                => '[Điều chỉnh số dư] ' . ($validated['ly_do'] ?? ''),
                'la_chuyen_vi'           => false,
            ]);

            WalletAdjustment::create([
                'user_id'        => Auth::id(),
                'wallet_id'      => $moneyWallet->id,
                'so_du_truoc'    => $soDuHienTai,
                'so_du_sau'      => $soDuMoi,
                'chenh_lech'     => $chenhLech,
                'transaction_id' => $transaction->id,
                'ly_do'          => $validated['ly_do'] ?? null,
            ]);

            $moneyWallet->update(['so_du' => $soDuMoi]);

            DB::commit();

            return response()->json(['success' => true, 'so_du' => $soDuMoi]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    private function checkOwnership(MoneyWallet $wallet): void
    {
        abort_if($wallet->user_id !== Auth::id(), 403);
    }
}
