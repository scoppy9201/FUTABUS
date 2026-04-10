<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
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

        $query = Transaction::where('user_id', $userId);
        $this->applyPeriodFilter($query, $period);

        $totalIncome = (clone $query)->where('loai_giao_dich', 'THU')->sum('so_tien');
        $totalExpense = (clone $query)->where('loai_giao_dich', 'CHI')->sum('so_tien');
        $balance = $totalIncome - $totalExpense;

        $totalTransactions = (clone $query)->count();
        $incomeCount = (clone $query)->where('loai_giao_dich', 'THU')->count();
        $expenseCount = (clone $query)->where('loai_giao_dich', 'CHI')->count();

        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->orderBy('ngay_giao_dich', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'so_tien' => (float) $transaction->so_tien,
                    'loai_giao_dich' => $transaction->loai_giao_dich,
                    'ngay_giao_dich' => optional($transaction->ngay_giao_dich)->format('Y-m-d'),
                    'category' => [
                        'ten_danh_muc' => $transaction->category->ten_danh_muc ?? 'Khong ro',
                        'bieu_tuong' => $transaction->category->bieu_tuong ?? 'money.png',
                    ],
                ];
            })
            ->values();

        $warningWallets = Wallet::where('user_id', $userId)
            ->where('trang_thai', true)
            ->get()
            ->filter(function ($wallet) {
                return $wallet->spent_percentage >= 50;
            })
            ->sortByDesc('spent_percentage')
            ->take(5)
            ->map(function ($wallet) {
                return [
                    'id' => $wallet->id,
                    'ten_ngan_sach' => $wallet->ten_ngan_sach,
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
                    'id' => $category->id,
                    'ten_danh_muc' => $category->ten_danh_muc,
                    'bieu_tuong' => $category->bieu_tuong ?? 'money.png',
                    'total_expense' => (float) $category->total_expense,
                ];
            })
            ->values();

        $activeWallets = Wallet::where('user_id', $userId)
            ->where('trang_thai', true)
            ->with('category')
            ->orderByDesc('ngan_sach_goc')
            ->get()
            ->map(function ($wallet) {
                return [
                    'id' => $wallet->id,
                    'ten_ngan_sach' => $wallet->ten_ngan_sach,
                    'so_du' => (float) $wallet->so_du,
                    'spent_percentage' => (float) $wallet->spent_percentage,
                    'category' => [
                        'bieu_tuong' => $wallet->category->bieu_tuong ?? 'money.png',
                    ],
                ];
            })
            ->values();

        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $income = Transaction::where('user_id', $userId)
                ->where('loai_giao_dich', 'THU')
                ->whereMonth('ngay_giao_dich', $date->month)
                ->whereYear('ngay_giao_dich', $date->year)
                ->sum('so_tien');

            $expense = Transaction::where('user_id', $userId)
                ->where('loai_giao_dich', 'CHI')
                ->whereMonth('ngay_giao_dich', $date->month)
                ->whereYear('ngay_giao_dich', $date->year)
                ->sum('so_tien');

            $monthlyData[] = [
                'month' => $date->format('n'),
                'income' => (float) $income,
                'expense' => (float) $expense,
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
            ->map(function ($category) {
                return [
                    'name' => $category->ten_danh_muc,
                    'total' => (float) $category->total,
                ];
            })
            ->values();

        return response()->json([
            'period' => $period,
            'totalIncome' => (float) $totalIncome,
            'totalExpense' => (float) $totalExpense,
            'balance' => (float) $balance,
            'totalTransactions' => $totalTransactions,
            'incomeCount' => $incomeCount,
            'expenseCount' => $expenseCount,
            'recentTransactions' => $recentTransactions,
            'warningWallets' => $warningWallets,
            'topCategories' => $topCategories,
            'activeWallets' => $activeWallets,
            'monthlyData' => $monthlyData,
            'categoryExpenses' => $categoryExpenses,
        ]);
    }

    protected function applyPeriodFilter($query, string $period)
    {
        if ($period === 'all') {
            return $query;
        }

        if ($period === 'this_month') {
            return $query
                ->whereMonth('ngay_giao_dich', now()->month)
                ->whereYear('ngay_giao_dich', now()->year);
        }

        if ($period === 'last_month') {
            $lastMonth = now()->subMonth();

            return $query
                ->whereMonth('ngay_giao_dich', $lastMonth->month)
                ->whereYear('ngay_giao_dich', $lastMonth->year);
        }

        if ($period === 'this_year') {
            return $query->whereYear('ngay_giao_dich', now()->year);
        }

        return $query;
    }

    public function export(Request $request)
    {
        $userId = $request->user()?->id ?? Auth::id();
        $period = $request->string('period')->toString() ?: 'this_month';

        if (!in_array($period, ['all', 'this_month', 'last_month', 'this_year'], true)) {
            $period = 'this_month';
        }

        // Tái sử dụng logic index, decode JSON response
        $response = $this->index($request);
        $data = json_decode($response->getContent(), true);

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
