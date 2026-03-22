<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $period = $request->get('period', 'this_month');
        
        // Xác định khoảng thời gian
        $query = Transaction::where('user_id', $userId);
        
        switch ($period) {
            case 'this_month':
                $query->whereMonth('ngay_giao_dich', now()->month)
                      ->whereYear('ngay_giao_dich', now()->year);
                break;
            case 'last_month':
                $query->whereMonth('ngay_giao_dich', now()->subMonth()->month)
                      ->whereYear('ngay_giao_dich', now()->subMonth()->year);
                break;
            case 'this_year':
                $query->whereYear('ngay_giao_dich', now()->year);
                break;
        }
        
        // 1. Tổng thu nhập
        $totalIncome = (clone $query)->where('loai_giao_dich', 'THU')->sum('so_tien');
        
        // 2. Tổng chi tiêu
        $totalExpense = (clone $query)->where('loai_giao_dich', 'CHI')->sum('so_tien');
        
        // 3. Số dư
        $balance = $totalIncome - $totalExpense;
        
        // 4. Tổng số giao dịch
        $totalTransactions = (clone $query)->count();
        $incomeCount = (clone $query)->where('loai_giao_dich', 'THU')->count();
        $expenseCount = (clone $query)->where('loai_giao_dich', 'CHI')->count();
        
        // 5. Giao dịch gần đây (5 giao dịch)
        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->orderBy('ngay_giao_dich', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // 6. Ngân sách cảnh báo (>= 50%)
        $warningWallets = Wallet::where('user_id', $userId)
            ->where('trang_thai', true)
            ->get()
            ->filter(function($wallet) {
                return $wallet->spent_percentage >= 50;
            })
            ->sortByDesc('spent_percentage')
            ->take(5);
        
        // 7. Top 5 danh mục chi tiêu nhiều nhất
        $topCategories = Category::where('user_id', $userId)
            ->where('loai_danh_muc', 'CHI')
            ->withSum(['transactions as total_expense' => function($query) use ($period) {
                $query->where('loai_giao_dich', 'CHI');
                
                switch ($period) {
                    case 'this_month':
                        $query->whereMonth('ngay_giao_dich', now()->month)
                              ->whereYear('ngay_giao_dich', now()->year);
                        break;
                    case 'last_month':
                        $query->whereMonth('ngay_giao_dich', now()->subMonth()->month)
                              ->whereYear('ngay_giao_dich', now()->subMonth()->year);
                        break;
                    case 'this_year':
                        $query->whereYear('ngay_giao_dich', now()->year);
                        break;
                }
            }], 'so_tien')
            ->having('total_expense', '>', 0)
            ->orderByDesc('total_expense')
            ->limit(5)
            ->get();
        
        // 8. Danh sách ngân sách đang hoạt động
        $activeWallets = Wallet::where('user_id', $userId)
            ->where('trang_thai', true)
            ->with('category')
            ->orderByDesc('ngan_sach_goc')
            ->get();
        
        // 9. Dữ liệu cho line chart - Thu Chi 6 tháng gần nhất
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
                'income' => $income,
                'expense' => $expense
            ];
        }
        
        // 10. DỮ LIỆU CHO PIE CHART - Chi tiêu theo danh mục
        $categoryExpenses = Category::where('user_id', $userId)
            ->where('loai_danh_muc', 'CHI')
            ->withSum(['transactions as total' => function($query) use ($period) {
                $query->where('loai_giao_dich', 'CHI');
                
                switch ($period) {
                    case 'this_month':
                        $query->whereMonth('ngay_giao_dich', now()->month)
                              ->whereYear('ngay_giao_dich', now()->year);
                        break;
                    case 'last_month':
                        $query->whereMonth('ngay_giao_dich', now()->subMonth()->month)
                              ->whereYear('ngay_giao_dich', now()->subMonth()->year);
                        break;
                    case 'this_year':
                        $query->whereYear('ngay_giao_dich', now()->year);
                        break;
                }
            }], 'so_tien')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(function($category) {
                return [
                    'name' => $category->ten_danh_muc,
                    'total' => $category->total
                ];
            });
        
        return view('dashboard', compact(
            'period',
            'totalIncome',
            'totalExpense',
            'balance',
            'totalTransactions',
            'incomeCount',
            'expenseCount',
            'recentTransactions',
            'warningWallets',
            'topCategories',
            'activeWallets',
            'monthlyData',
            'categoryExpenses'
        ));
    }
}