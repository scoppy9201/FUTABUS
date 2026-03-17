<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Giao dịch
        $transactions = Transaction::with('category')
            ->where('user_id', Auth::id())
            ->where(function($query) use ($q) {
                $query->where('ghi_chu', 'like', "%{$q}%")
                      ->orWhereHas('category', fn($c) => $c->where('ten_danh_muc', 'like', "%{$q}%"));
            })
            ->orderBy('ngay_giao_dich', 'desc')
            ->limit(5)
            ->get();

        foreach ($transactions as $t) {
            $results[] = [
                'type'     => 'transaction',
                'label'    => $t->category->ten_danh_muc ?? 'Không rõ',
                'sub'      => ($t->loai_giao_dich == 'thu' ? '+' : '-') . number_format($t->so_tien, 0, ',', '.') . 'đ · ' . $t->ngay_giao_dich->format('d/m/Y'),
                'url'      => route('transactions.index'),
                'badge'    => $t->loai_giao_dich == 'thu' ? 'income' : 'expense',
            ];
        }

        // Danh mục
        $categories = Category::where('user_id', Auth::id())
            ->where('ten_danh_muc', 'like', "%{$q}%")
            ->limit(3)
            ->get();

        foreach ($categories as $c) {
            $results[] = [
                'type'  => 'category',
                'label' => $c->ten_danh_muc,
                'sub'   => 'Danh mục · ' . ($c->loai ?? ''),
                'url'   => route('categories.index'),
                'badge' => 'category',
            ];
        }

        // Ngân sách
        $wallets = Wallet::where('user_id', Auth::id())
            ->where('ten_ngan_sach', 'like', "%{$q}%")
            ->limit(3)
            ->get();

        foreach ($wallets as $w) {
            $results[] = [
                'type'  => 'wallet',
                'label' => $w->ten_ngan_sach,
                'sub'   => 'Ngân sách · ' . number_format($w->so_du, 0, ',', '.') . 'đ',
                'url'   => route('wallets.index'),
                'badge' => 'wallet',
            ];
        }

        return response()->json(['results' => $results]);
    }
}