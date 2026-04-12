<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CurrencyHistory;
use Carbon\Carbon;

class CurrencyController extends Controller
{
    /**
     * GET /api/v1/currency
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'currencies' => [
                    ['code' => 'VND', 'label' => '🇻🇳 VND'],
                    ['code' => 'USD', 'label' => '🇺🇸 USD'],
                    ['code' => 'EUR', 'label' => '🇪🇺 EUR'],
                    ['code' => 'JPY', 'label' => '🇯🇵 JPY'],
                    ['code' => 'KRW', 'label' => '🇰🇷 KRW'],
                    ['code' => 'CNY', 'label' => '🇨🇳 CNY'],
                    ['code' => 'GBP', 'label' => '🇬🇧 GBP'],
                    ['code' => 'AUD', 'label' => '🇦🇺 AUD'],
                    ['code' => 'CAD', 'label' => '🇨🇦 CAD'],
                    ['code' => 'SGD', 'label' => '🇸🇬 SGD'],
                    ['code' => 'THB', 'label' => '🇹🇭 THB'],
                    ['code' => 'HKD', 'label' => '🇭🇰 HKD'],
                    ['code' => 'MYR', 'label' => '🇲🇾 MYR'],
                    ['code' => 'IDR', 'label' => '🇮🇩 IDR'],
                    ['code' => 'PHP', 'label' => '🇵🇭 PHP'],
                    ['code' => 'INR', 'label' => '🇮🇳 INR'],
                    ['code' => 'CHF', 'label' => '🇨🇭 CHF'],
                    ['code' => 'TWD', 'label' => '🇹🇼 TWD'],
                ],
                'market_pairs' => [
                    ['code' => 'USD', 'flag_from' => '🇺🇸', 'flag_to' => '🇻🇳', 'name' => 'Đô la / Đồng'],
                    ['code' => 'EUR', 'flag_from' => '🇪🇺', 'flag_to' => '🇻🇳', 'name' => 'Euro / Đồng'],
                    ['code' => 'JPY', 'flag_from' => '🇯🇵', 'flag_to' => '🇻🇳', 'name' => 'Yên / Đồng'],
                    ['code' => 'KRW', 'flag_from' => '🇰🇷', 'flag_to' => '🇻🇳', 'name' => 'Won / Đồng'],
                    ['code' => 'CNY', 'flag_from' => '🇨🇳', 'flag_to' => '🇻🇳', 'name' => 'NDT / Đồng'],
                    ['code' => 'GBP', 'flag_from' => '🇬🇧', 'flag_to' => '🇻🇳', 'name' => 'Bảng / Đồng'],
                    ['code' => 'SGD', 'flag_from' => '🇸🇬', 'flag_to' => '🇻🇳', 'name' => 'SGD / Đồng'],
                    ['code' => 'THB', 'flag_from' => '🇹🇭', 'flag_to' => '🇻🇳', 'name' => 'Baht / Đồng'],
                ],
                'defaults' => [
                    'from'   => 'USD',
                    'to'     => 'VND',
                    'amount' => null,
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/currency/convert
     * JS tính xong rồi gửi lên để lưu lịch sử.
     */
    public function convert(Request $request)
    {
        $validated = $request->validate([
            'from_currency' => 'required|string|size:3',
            'to_currency'   => 'required|string|size:3',
            'amount'        => 'required|numeric|min:0.000001',
            'result'        => 'required|numeric|min:0',
            'rate'          => 'required|numeric|min:0',
        ]);

        $history = CurrencyHistory::create([
            'user_id'       => Auth::id(),
            'from_currency' => strtoupper($validated['from_currency']),
            'to_currency'   => strtoupper($validated['to_currency']),
            'amount'        => $validated['amount'],
            'result'        => $validated['result'],
            'rate'          => $validated['rate'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu lịch sử.',
            'data'    => $history,
        ], 201);
    }

    /**
     * GET /api/v1/currency/history
     * Lịch sử + so sánh tỷ giá hôm nay vs hôm qua.
     */
    public function history(Request $request)
    {
        $userId = Auth::id();

        $histories = CurrencyHistory::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayRates = CurrencyHistory::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->selectRaw('from_currency, to_currency, AVG(rate) as avg_rate')
            ->groupBy('from_currency', 'to_currency')
            ->get()
            ->keyBy(fn($r) => $r->from_currency . '_' . $r->to_currency);

        $yesterdayRates = CurrencyHistory::where('user_id', $userId)
            ->whereDate('created_at', $yesterday)
            ->selectRaw('from_currency, to_currency, AVG(rate) as avg_rate')
            ->groupBy('from_currency', 'to_currency')
            ->get()
            ->keyBy(fn($r) => $r->from_currency . '_' . $r->to_currency);

        $comparisons = [];
        foreach ($todayRates as $key => $todayR) {
            $yesterdayR = $yesterdayRates[$key] ?? null;
            if ($yesterdayR && $yesterdayR->avg_rate > 0) {
                $change = (($todayR->avg_rate - $yesterdayR->avg_rate) / $yesterdayR->avg_rate) * 100;
                $comparisons[] = [
                    'from'           => $todayR->from_currency,
                    'to'             => $todayR->to_currency,
                    'today_rate'     => round($todayR->avg_rate, 6),
                    'yesterday_rate' => round($yesterdayR->avg_rate, 6),
                    'change_percent' => round($change, 4),
                    'direction'      => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'same'),
                ];
            }
        }

        return response()->json([
            'success'     => true,
            'data'        => $histories->items(),
            'comparisons' => $comparisons,
            'pagination'  => [
                'current_page' => $histories->currentPage(),
                'last_page'    => $histories->lastPage(),
                'per_page'     => $histories->perPage(),
                'total'        => $histories->total(),
            ],
        ]);
    }

    /**
     * DELETE /api/v1/currency/history/{id}
     */
    public function deleteHistory(CurrencyHistory $currencyHistory)
    {
        if ($currencyHistory->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền.'], 403);
        }
        $currencyHistory->delete();
        return response()->json(['success' => true, 'message' => 'Đã xoá.']);
    }

    /**
     * DELETE /api/v1/currency/history
     */
    public function clearHistory()
    {
        CurrencyHistory::where('user_id', Auth::id())->delete();
        return response()->json(['success' => true, 'message' => 'Đã xoá toàn bộ lịch sử.']);
    }
}