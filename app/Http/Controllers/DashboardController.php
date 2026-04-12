<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Budgets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DashboardExport;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id ?? Auth::id();
        $period = $request->string('period')->toString() ?: 'this_month';

        if (!in_array($period, ['all', 'this_month', 'last_month', 'this_year'], true)) {
            $period = 'this_month';
        }

        $data = $this->getDashboardData($userId, $period);

        return response()->json($data);
    }

    /**
     * Tách data ra hàm riêng để tái sử dụng cho cả index() và export()
     */
    protected function getDashboardData(int $userId, string $period): array
    {
        $query = Transaction::where('user_id', $userId);
        $this->applyPeriodFilter($query, $period);

        $totalIncome  = (clone $query)->where('loai_giao_dich', 'THU')->sum('so_tien');
        $totalExpense = (clone $query)->where('loai_giao_dich', 'CHI')->sum('so_tien');
        $balance      = $totalIncome - $totalExpense;

        $totalTransactions = (clone $query)->count();
        $incomeCount       = (clone $query)->where('loai_giao_dich', 'THU')->count();
        $expenseCount      = (clone $query)->where('loai_giao_dich', 'CHI')->count();

        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->orderBy('ngay_giao_dich', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id'             => $transaction->id,
                    'so_tien'        => (float) $transaction->so_tien,
                    'loai_giao_dich' => $transaction->loai_giao_dich,
                    'ngay_giao_dich' => optional($transaction->ngay_giao_dich)->format('Y-m-d'),
                    'category'       => [
                        'ten_danh_muc' => $transaction->category->ten_danh_muc ?? 'Không rõ',
                        'bieu_tuong'   => $transaction->category->bieu_tuong ?? 'money.png',
                    ],
                ];
            })
            ->values();

        $warningWallets = Budgets::where('user_id', $userId)
            ->where('trang_thai', true)
            ->get()
            ->filter(fn($wallet) => $wallet->spent_percentage >= 50)
            ->sortByDesc('spent_percentage')
            ->take(5)
            ->map(function ($wallet) {
                return [
                    'id'               => $wallet->id,
                    'ten_ngan_sach'    => $wallet->ten_ngan_sach,
                    'spent_percentage' => (float) $wallet->spent_percentage,
                ];
            })
            ->values();

        $topCategories = Category::where('user_id', $userId)
            ->where('loai_danh_muc', 'CHI')
            ->withSum(['transactions as total_expense' => function ($query) use ($period) {
                $query->where('loai_giao_dich', 'CHI');
                $this->applyPeriodFilter($query, $period);
            }], 'so_tien')
            ->having('total_expense', '>', 0)
            ->orderByDesc('total_expense')
            ->limit(5)
            ->get()
            ->map(function ($category) {
                return [
                    'id'            => $category->id,
                    'ten_danh_muc'  => $category->ten_danh_muc,
                    'bieu_tuong'    => $category->bieu_tuong ?? 'money.png',
                    'total_expense' => (float) $category->total_expense,
                ];
            })
            ->values();

        $activeWallets = Budgets::where('user_id', $userId)
            ->where('trang_thai', true)
            ->with('category')
            ->orderByDesc('ngan_sach_goc')
            ->get()
            ->map(function ($wallet) {
                return [
                    'id'               => $wallet->id,
                    'ten_ngan_sach'    => $wallet->ten_ngan_sach,
                    'so_du'            => (float) $wallet->so_du,
                    'spent_percentage' => (float) $wallet->spent_percentage,
                    'category'         => [
                        'bieu_tuong' => $wallet->category->bieu_tuong ?? 'money.png',
                    ],
                ];
            })
            ->values();

        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $monthlyData[] = [
                'month'   => $date->format('n'),
                'income'  => (float) Transaction::where('user_id', $userId)
                    ->where('loai_giao_dich', 'THU')
                    ->whereMonth('ngay_giao_dich', $date->month)
                    ->whereYear('ngay_giao_dich', $date->year)
                    ->sum('so_tien'),
                'expense' => (float) Transaction::where('user_id', $userId)
                    ->where('loai_giao_dich', 'CHI')
                    ->whereMonth('ngay_giao_dich', $date->month)
                    ->whereYear('ngay_giao_dich', $date->year)
                    ->sum('so_tien'),
            ];
        }

        $categoryExpenses = Category::where('user_id', $userId)
            ->where('loai_danh_muc', 'CHI')
            ->withSum(['transactions as total' => function ($query) use ($period) {
                $query->where('loai_giao_dich', 'CHI');
                $this->applyPeriodFilter($query, $period);
            }], 'so_tien')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn($category) => [
                'name'  => $category->ten_danh_muc,
                'total' => (float) $category->total,
            ])
            ->values();

        return [
            'period'             => $period,
            'totalIncome'        => (float) $totalIncome,
            'totalExpense'       => (float) $totalExpense,
            'balance'            => (float) $balance,
            'totalTransactions'  => $totalTransactions,
            'incomeCount'        => $incomeCount,
            'expenseCount'       => $expenseCount,
            'recentTransactions' => $recentTransactions,
            'warningWallets'     => $warningWallets,
            'topCategories'      => $topCategories,
            'activeWallets'      => $activeWallets,
            'monthlyData'        => $monthlyData,
            'categoryExpenses'   => $categoryExpenses,
        ];
    }

    protected function applyPeriodFilter($query, string $period)
    {
        match($period) {
            'this_month' => $query
                ->whereMonth('ngay_giao_dich', now()->month)
                ->whereYear('ngay_giao_dich', now()->year),
            'last_month' => $query
                ->whereMonth('ngay_giao_dich', now()->subMonth()->month)
                ->whereYear('ngay_giao_dich', now()->subMonth()->year),
            'this_year'  => $query->whereYear('ngay_giao_dich', now()->year),
            default      => null,
        };

        return $query;
    }

    public function export(Request $request)
    {
        $userId = $request->user()?->id ?? Auth::id();
        $period = $request->string('period')->toString() ?: 'this_month';

        if (!in_array($period, ['all', 'this_month', 'last_month', 'this_year'], true)) {
            $period = 'this_month';
        }

        $data = $this->getDashboardData($userId, $period);

        $periodLabel = match($period) {
            'this_month' => 'Thang_nay',
            'last_month' => 'Thang_truoc',
            'this_year'  => 'Nam_nay',
            default      => 'Tat_ca',
        };

        $filename = "baocao_{$periodLabel}_" . now()->format('Y-m-d') . ".xlsx";

        return Excel::download(new DashboardExport($data, $period), $filename);
    }
}
