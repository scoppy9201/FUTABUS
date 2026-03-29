<?php

namespace App\Http\Controllers;

use App\Models\MoneyWallet;
use App\Models\WalletAdjustment;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MoneyWalletController extends Controller
{
    // ── Danh sách ví ──────────────────────────────────────
    public function index()
    {
        $userId = Auth::id();

        $wallets = MoneyWallet::forUser($userId)
            ->active()
            ->orderByDesc('created_at')
            ->get();

        $walletsByType = $wallets->groupBy('loai_vi');

        // Tổng tài sản = THU - CHI (bỏ tổng số dư ví)
        $tongThu = Transaction::where('user_id', $userId)
            ->where('loai_giao_dich', 'THU')
            ->where('la_chuyen_vi', false)
            ->sum('so_tien');

        $tongChi = Transaction::where('user_id', $userId)
            ->where('loai_giao_dich', 'CHI')
            ->where('la_chuyen_vi', false)
            ->sum('so_tien');

        $tongTaiSan = $tongThu - $tongChi;

        $stats = [
            'tong_vi'      => $wallets->count(),
            'tong_so_du'   => $tongTaiSan, // giữ key cũ để không break view khác
            'vi_tien_mat'  => $wallets->where('loai_vi', 'tien_mat')->sum('so_du'),
            'vi_ngan_hang' => $wallets->where('loai_vi', 'ngan_hang')->sum('so_du'),
            'vi_dien_tu'   => $wallets->where('loai_vi', 'vi_dien_tu')->sum('so_du'),
        ];

        $inactiveWallets = MoneyWallet::forUser($userId)
            ->where('trang_thai', 'inactive')
            ->get();

        // Tổng so_du_ban_dau hiện tại của tất cả ví active (để validate khi tạo ví mới)
        $tongSoDuVi = $wallets->sum('so_du_ban_dau');

        return view('money-wallets.index', compact(
            'wallets', 'walletsByType', 'stats', 'inactiveWallets',
            'tongTaiSan', 'tongThu', 'tongChi', 'tongSoDuVi'
        ));
    }

    // ── Chi tiết ví + lịch sử ────────────────────────────
    public function show(MoneyWallet $moneyWallet)
    {
        $this->checkOwnership($moneyWallet);

        $moneyWallet->load('transactions.category');

        // Giao dịch thực (loại bỏ chuyển ví)
        $transactions = $moneyWallet->transactions()
            ->where('la_chuyen_vi', false)
            ->with('category')
            ->orderByDesc('ngay_giao_dich')
            ->orderByDesc('created_at')
            ->paginate(15);

        // Lịch sử chuyển ví
        $transfers = \App\Models\WalletTransfer::where(function ($q) use ($moneyWallet) {
                $q->where('from_wallet_id', $moneyWallet->id)
                  ->orWhere('to_wallet_id', $moneyWallet->id);
            })
            ->where('user_id', Auth::id())
            ->with(['fromWallet', 'toWallet', 'category'])
            ->orderByDesc('ngay_chuyen')
            ->limit(20)
            ->get();

        // Lịch sử điều chỉnh
        $adjustments = $moneyWallet->adjustments()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Thống kê ví
        $stats = [
            'tong_thu'  => $moneyWallet->getTotalIncome(),
            'tong_chi'  => $moneyWallet->getTotalExpense(),
        ];

        return view('money-wallets.show', compact(
            'moneyWallet', 'transactions', 'transfers', 'adjustments', 'stats'
        ));
    }

    // ── Thêm ví mới ──────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_vi'         => 'required|string|max:100',
            'loai_vi'        => 'required|in:tien_mat,ngan_hang,vi_dien_tu,the_tin_dung,dau_tu,khac',
            'so_du_ban_dau'  => 'required|numeric|min:0|max:999999999999',
            'don_vi_tien_te' => 'required|string|max:10',
            'bieu_tuong'     => 'nullable|string|max:50',
            'mo_ta'          => 'nullable|string|max:500',
        ], [
            'ten_vi.required'        => 'Vui lòng nhập tên ví',
            'loai_vi.required'       => 'Vui lòng chọn loại ví',
            'so_du_ban_dau.required' => 'Vui lòng nhập số dư ban đầu',
            'so_du_ban_dau.min'      => 'Số dư không được âm',
        ]);

        DB::beginTransaction();
        try {
             // Validate: tổng so_du_ban_dau các ví không được > tổng tài sản
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
                ->sum('so_du_ban_dau');

            if (($tongSoDuHienTai + $validated['so_du_ban_dau']) > $tongTaiSan) {
                DB::rollBack();
                return back()->withInput()->with('error',
                    'Không thể tạo ví! Tổng số dư ban đầu các ví (' .
                    number_format($tongSoDuHienTai + $validated['so_du_ban_dau']) .
                    'đ) vượt quá tổng tài sản (' .
                    number_format($tongTaiSan) . 'đ).'
                );
            }
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

            return redirect()->route('money-wallets.index')
                ->with('success', "Đã tạo ví \"{$wallet->ten_vi}\" thành công!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    // ── Cập nhật thông tin ví ────────────────────────────
    public function update(Request $request, MoneyWallet $moneyWallet)
    {
        $this->checkOwnership($moneyWallet);

        $validated = $request->validate([
            'ten_vi'         => 'required|string|max:100',
            'loai_vi'        => 'required|in:tien_mat,ngan_hang,vi_dien_tu,the_tin_dung,dau_tu,khac',
            'don_vi_tien_te' => 'required|string|max:10',
            'bieu_tuong'     => 'nullable|string|max:50',
            'mo_ta'          => 'nullable|string|max:500',
        ]);

        // KHÔNG cho sửa so_du trực tiếp → phải qua điều chỉnh số dư
        $moneyWallet->update([
            'ten_vi'         => trim($validated['ten_vi']),
            'loai_vi'        => $validated['loai_vi'],
            'don_vi_tien_te' => strtoupper($validated['don_vi_tien_te']),
            'bieu_tuong'     => $validated['bieu_tuong'] ?? $moneyWallet->bieu_tuong,
            'mo_ta'          => $validated['mo_ta'] ?? null,
        ]);

        return redirect()->route('money-wallets.index')
            ->with('success', "Đã cập nhật ví \"{$moneyWallet->ten_vi}\"!");
    }

    // ── Xóa / Vô hiệu hóa ví ────────────────────────────
    public function destroy(MoneyWallet $moneyWallet)
    {
        $this->checkOwnership($moneyWallet);

        // Kiểm tra: nếu là ví duy nhất thì không cho xóa
        $activeCount = MoneyWallet::forUser(Auth::id())->active()->count();
        if ($activeCount <= 1) {
            return back()->with('error', 'Không thể xóa ví duy nhất. Bạn cần ít nhất 1 ví đang hoạt động.');
        }

        if ($moneyWallet->canDelete()) {
            // Chưa có giao dịch → xóa cứng
            $name = $moneyWallet->ten_vi;
            $moneyWallet->delete();
            return redirect()->route('money-wallets.index')
                ->with('success', "Đã xóa ví \"{$name}\".");
        }

        // Đã có giao dịch → chuyển sang inactive
        $moneyWallet->update(['trang_thai' => 'inactive']);
        return redirect()->route('money-wallets.index')
            ->with('success', "Đã ẩn ví \"{$moneyWallet->ten_vi}\". Lịch sử giao dịch vẫn được giữ lại.");
    }

    // ── Khôi phục ví inactive ────────────────────────────
    public function restore(MoneyWallet $moneyWallet)
    {
        $this->checkOwnership($moneyWallet);
        $moneyWallet->update(['trang_thai' => 'active']);
        return back()->with('success', "Đã khôi phục ví \"{$moneyWallet->ten_vi}\".");
    }

    // ── Điều chỉnh số dư (UC09.4) ────────────────────────
    public function adjust(Request $request, MoneyWallet $moneyWallet)
    {
        $this->checkOwnership($moneyWallet);

        $validated = $request->validate([
            'so_du_thuc_te' => 'required|numeric|min:0|max:999999999999',
            'ly_do'         => 'nullable|string|max:255',
            'category_id'   => 'required|exists:categories,id',
        ], [
            'so_du_thuc_te.required' => 'Vui lòng nhập số dư thực tế',
            'so_du_thuc_te.min'      => 'Số dư không được âm',
            'category_id.required'   => 'Vui lòng chọn danh mục cho giao dịch điều chỉnh',
        ]);

        $soDuHienTai = (float) $moneyWallet->so_du;
        $soDuMoi     = (float) $validated['so_du_thuc_te'];
        $chenhLech   = $soDuMoi - $soDuHienTai;

        if (abs($chenhLech) < 0.01) {
            return back()->with('info', 'Số dư không thay đổi.');
        }

        DB::beginTransaction();
        try {
            // Tạo giao dịch đặc biệt để ghi lịch sử
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

            // Ghi lịch sử điều chỉnh
            WalletAdjustment::create([
                'user_id'        => Auth::id(),
                'wallet_id'      => $moneyWallet->id,
                'so_du_truoc'    => $soDuHienTai,
                'so_du_sau'      => $soDuMoi,
                'chenh_lech'     => $chenhLech,
                'transaction_id' => $transaction->id,
                'ly_do'          => $validated['ly_do'] ?? null,
            ]);

            // Cập nhật số dư ví
            $moneyWallet->update(['so_du' => $soDuMoi]);

            DB::commit();

            $msg = $chenhLech > 0
                ? "Đã tăng số dư +" . number_format(abs($chenhLech)) . " {$moneyWallet->don_vi_tien_te}"
                : "Đã giảm số dư -" . number_format(abs($chenhLech)) . " {$moneyWallet->don_vi_tien_te}";

            return redirect()->route('money-wallets.show', $moneyWallet)
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // ── Helper: kiểm tra quyền sở hữu ────────────────────
    private function checkOwnership(MoneyWallet $wallet): void
    {
        abort_if($wallet->user_id !== Auth::id(), 403, 'Bạn không có quyền thực hiện.');
    }
}
