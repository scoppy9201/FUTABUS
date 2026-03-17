<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\GeminiService;

class AIAssistantController extends Controller
{
    private GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function index()
    {
        return view('ai-assistant.index');
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        $userMessage = trim($request->input('message'));
        $userId      = Auth::id();
        $userName    = Auth::user()->name;

        try {
            // 1. Lấy dữ liệu tài chính realtime
            $financialData = $this->getUserFinancialData($userId);

            // 2. Lấy lịch sử chat gần nhất (10 lượt = 20 message)
            $rawHistory = DB::table('ai_chat_history')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->reverse()
                ->values();

            $history = [];
            foreach ($rawHistory as $item) {
                $history[] = ['role' => 'user',  'parts' => [['text' => $item->user_message]]];
                $history[] = ['role' => 'model', 'parts' => [['text' => $item->ai_response]]];
            }

            // 3. Build system prompt
            $systemPrompt = $this->buildSystemPrompt($financialData, $userName);

            // 4. Build contents — system prompt chỉ gửi 1 lần ở đầu
            $contents = array_merge(
                [
                    ['role' => 'user',  'parts' => [['text' => $systemPrompt]]],
                    ['role' => 'model', 'parts' => [['text' => 'Đã nắm rõ thông tin tài chính của ' . $userName . '. Sẵn sàng tư vấn!']]],
                ],
                $history,
                [
                    ['role' => 'user', 'parts' => [['text' => $userMessage]]]
                ]
            );

            // 5. Gọi Gemini
            $response = $this->gemini->generateContent([
                'model'    => 'models/gemini-2.5-flash',
                'contents' => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => 700,
                    'temperature'     => 0.75,
                    'topP'            => 0.9,
                    'topK'            => 40,
                ],
                'safetySettings' => [
                    ['category' => 'HARM_CATEGORY_HARASSMENT',  'threshold' => 'BLOCK_NONE'],
                    ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
                ],
            ]);

            $aiResponse = $response['candidates'][0]['content']['parts'][0]['text']
                ?? 'Xin lỗi, tôi không thể trả lời lúc này. Vui lòng thử lại.';

            // 6. Format response đẹp
            $aiResponse = $this->formatResponse($aiResponse);

            // 7. Lưu lịch sử
            DB::table('ai_chat_history')->insert([
                'user_id'      => $userId,
                'user_message' => $userMessage,
                'ai_response'  => $aiResponse,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return response()->json(['success' => true, 'message' => $aiResponse]);

        } catch (\Exception $e) {
            Log::error('AI Chat Error', [
                'user_id' => $userId,
                'message' => $userMessage,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại sau.',
                'error_detail' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function clearHistory()
    {
        DB::table('ai_chat_history')->where('user_id', Auth::id())->delete();
        return response()->json(['success' => true]);
    }
    
    // FINANCIAL DATA
    private function getUserFinancialData($userId): array
    {
        // Tổng toàn thời gian
        $totalIncome  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')->sum('so_tien');
        $totalExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')->sum('so_tien');

        // Tháng này
        $monthIncome  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])->sum('so_tien');
        $monthExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])->sum('so_tien');

        // Tháng trước
        $lastMonthExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('so_tien');

        // Chi theo danh mục
        $categoryExpenses = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.loai_giao_dich', 'CHI')
            ->select('categories.ten_danh_muc', DB::raw('SUM(transactions.so_tien) as total'))
            ->groupBy('categories.id', 'categories.ten_danh_muc')
            ->orderByDesc('total')
            ->get();

        // Ngân sách
        $wallets = Wallet::where('user_id', $userId)->get()->map(function ($w) use ($userId) {
            $spent = Transaction::where('user_id', $userId)
                ->where('loai_giao_dich', 'CHI')
                ->where('category_id', $w->category_id)
                ->sum('so_tien');
            $w->da_chi          = $spent;
            $w->spent_percentage = $w->ngan_sach_goc > 0 ? round(($spent / $w->ngan_sach_goc) * 100, 1) : 0;
            return $w;
        });

        // 10 giao dịch gần nhất
        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->orderByDesc('ngay_giao_dich')
            ->limit(10)
            ->get();

        return compact(
            'totalIncome', 'totalExpense',
            'monthIncome', 'monthExpense', 'lastMonthExpense',
            'categoryExpenses', 'wallets', 'recentTransactions'
        );
    }

    // SYSTEM PROMPT
    private function buildSystemPrompt(array $d, string $userName): string
    {
        $balance      = $d['totalIncome'] - $d['totalExpense'];
        $savingRate   = $d['monthIncome'] > 0
            ? round(($d['monthIncome'] - $d['monthExpense']) / $d['monthIncome'] * 100, 1)
            : 0;
        $monthTrend   = $d['lastMonthExpense'] > 0
            ? round(($d['monthExpense'] - $d['lastMonthExpense']) / $d['lastMonthExpense'] * 100, 1)
            : 0;
        $trendText    = $monthTrend > 0 ? "tăng {$monthTrend}%" : "giảm " . abs($monthTrend) . "%";

        $prompt  = "=== VAI TRÒ ===\n";
        $prompt .= "Bạn là Monexa AI — trợ lý tài chính cá nhân thông minh của ứng dụng Monexa.\n";
        $prompt .= "Người dùng: {$userName}. Hãy gọi họ là '{$userName}' hoặc 'bạn'.\n";
        $prompt .= "Ngôn ngữ: Tiếng Việt. Giọng điệu: thân thiện, chuyên nghiệp, thực tế.\n\n";

        $prompt .= "=== QUY TẮC TRẢ LỜI ===\n";
        $prompt .= "1. Dùng số liệu thực tế từ dữ liệu bên dưới khi trả lời.\n";
        $prompt .= "2. Trả lời ngắn gọn, súc tích. Tối đa 5-7 dòng trừ khi được yêu cầu phân tích sâu.\n";
        $prompt .= "3. Dùng dấu gạch đầu dòng (-) để liệt kê, KHÔNG dùng markdown (**, ##).\n";
        $prompt .= "4. Nếu câu hỏi không liên quan tài chính, trả lời: 'Mình chỉ hỗ trợ tư vấn tài chính cá nhân thôi nhé {$userName}! Bạn có muốn hỏi gì về chi tiêu không?'\n";
        $prompt .= "5. Luôn kết thúc bằng 1 gợi ý hành động cụ thể nếu phù hợp.\n\n";

        $prompt .= "=== DỮ LIỆU TÀI CHÍNH ===\n";
        $prompt .= "Tổng quan:\n";
        $prompt .= "- Thu nhập toàn bộ: " . number_format($d['totalIncome']) . " VND\n";
        $prompt .= "- Chi tiêu toàn bộ: " . number_format($d['totalExpense']) . " VND\n";
        $prompt .= "- Số dư hiện tại: " . number_format($balance) . " VND\n";
        $prompt .= "- Thu nhập tháng này: " . number_format($d['monthIncome']) . " VND\n";
        $prompt .= "- Chi tiêu tháng này: " . number_format($d['monthExpense']) . " VND (so tháng trước: {$trendText})\n";
        $prompt .= "- Tỷ lệ tiết kiệm tháng này: {$savingRate}%";

        if ($savingRate >= 20) $prompt .= " (Tốt - đạt chuẩn 50/30/20)\n";
        elseif ($savingRate >= 10) $prompt .= " (Trung bình - cần cải thiện)\n";
        elseif ($savingRate > 0) $prompt .= " (Thấp - cần chú ý)\n";
        else $prompt .= " (Cảnh báo: chi vượt thu!)\n";

        if ($d['categoryExpenses']->count() > 0) {
            $prompt .= "\nChi tiêu theo danh mục (toàn bộ):\n";
            foreach ($d['categoryExpenses'] as $cat) {
                $pct = $d['totalExpense'] > 0 ? round($cat->total / $d['totalExpense'] * 100, 1) : 0;
                $prompt .= "- {$cat->ten_danh_muc}: " . number_format($cat->total) . " VND ({$pct}%)\n";
            }
        }

        if ($d['wallets']->count() > 0) {
            $prompt .= "\nNgân sách:\n";
            foreach ($d['wallets'] as $w) {
                $status = $w->spent_percentage >= 90 ? '[NGUY HIỂM]'
                    : ($w->spent_percentage >= 70 ? '[CẢNH BÁO]' : '[ỔN ĐỊNH]');
                $prompt .= "- {$w->ten_ngan_sach}: đã dùng {$w->spent_percentage}% "
                    . "(" . number_format($w->da_chi) . "/" . number_format($w->ngan_sach_goc) . " VND) {$status}\n";
            }
        }

        if ($d['recentTransactions']->count() > 0) {
            $prompt .= "\n10 giao dịch gần nhất:\n";
            foreach ($d['recentTransactions'] as $t) {
                $type    = $t->loai_giao_dich == 'THU' ? 'Thu' : 'Chi';
                $date    = \Carbon\Carbon::parse($t->ngay_giao_dich)->format('d/m/Y');
                $cat     = $t->category ? $t->category->ten_danh_muc : 'Không rõ';
                $note    = $t->ghi_chu ? " - {$t->ghi_chu}" : '';
                $prompt .= "- {$date} | {$type} | " . number_format($t->so_tien) . " VND | {$cat}{$note}\n";
            }
        }

        $prompt .= "\n=== QUY TẮC 50/30/20 ===\n";
        $prompt .= "Nhu cầu thiết yếu: 50% thu nhập | Giải trí/cá nhân: 30% | Tiết kiệm/đầu tư: 20%\n";
        $prompt .= "Dùng quy tắc này để đánh giá và so sánh khi người dùng hỏi về tiết kiệm hoặc phân bổ chi tiêu.\n";

        return $prompt;
    }

    // FORMAT RESPONSE
    private function formatResponse(string $text): string
    {
        // Xóa markdown bold/italic/heading
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/\*(.*?)\*/',     '$1', $text);
        $text = preg_replace('/#{1,6}\s/',      '',   $text);

        // Chuẩn hóa xuống dòng
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    public function analyze(Request $request)
    {
        $userId = Auth::id();
        $period = $request->input('period', 30);

        try {
            $data         = $this->getUserFinancialData($userId);
            $systemPrompt = $this->buildSystemPrompt($data, Auth::user()->name);

            $response = $this->gemini->generateContent([
                'model'    => 'models/gemini-2.5-flash',
                'contents' => [
                    ['role' => 'user',  'parts' => [['text' => $systemPrompt]]],
                    ['role' => 'model', 'parts' => [['text' => 'Đã nắm dữ liệu. Sẵn sàng phân tích!']]],
                    ['role' => 'user',  'parts' => [['text' => "Phân tích chi tiêu {$period} ngày qua và đưa ra 3 lời khuyên cụ thể có số liệu."]]],
                ],
                'generationConfig' => ['maxOutputTokens' => 800, 'temperature' => 0.7],
            ]);

            $analysis = $response['candidates'][0]['content']['parts'][0]['text']
                ?? 'Không nhận được phân tích.';

            return response()->json(['success' => true, 'analysis' => $this->formatResponse($analysis)]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function suggestions()
    {
        return response()->json([
            'suggestions' => [
                'Phân tích chi tiêu của tôi tháng này',
                'Tôi nên tiết kiệm như thế nào?',
                'Danh mục nào tôi chi nhiều nhất?',
                'So sánh chi tiêu tháng này với tháng trước',
                'Ngân sách nào đang cảnh báo?',
            ]
        ]);
    }

    public function insights()
    {
        $userId = Auth::id();
        try {
            return response()->json([
                'success'  => true,
                'insights' => [
                    'spending_trend'    => $this->getSpendingTrend($userId),
                    'top_categories'    => $this->getTopCategories($userId),
                    'unusual_spending'  => $this->getUnusualSpending($userId),
                    'saving_rate'       => $this->getSavingRate($userId),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không thể lấy insights.'], 500);
        }
    }

    private function getSpendingTrend($userId): array
    {
        $current  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])->sum('so_tien');
        $last     = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('so_tien');
        $change   = $last > 0 ? round(($current - $last) / $last * 100, 1) : 0;

        return ['current_month' => $current, 'last_month' => $last, 'change_percentage' => $change, 'trend' => $change > 0 ? 'increase' : 'decrease'];
    }

    private function getTopCategories($userId)
    {
        return DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.loai_giao_dich', 'CHI')
            ->whereBetween('transactions.ngay_giao_dich', [now()->subDays(30), now()])
            ->select('categories.ten_danh_muc', 'categories.bieu_tuong', DB::raw('SUM(transactions.so_tien) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('categories.id', 'categories.ten_danh_muc', 'categories.bieu_tuong')
            ->orderByDesc('total')->limit(3)->get();
    }

    private function getUnusualSpending($userId)
    {
        $avg       = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->subMonths(3), now()->subMonth()])->avg('so_tien');
        $threshold = ($avg ?? 0) * 1.5;

        return Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])
            ->where('so_tien', '>', $threshold)->with('category')->orderByDesc('so_tien')->limit(3)->get();
    }

    private function getSavingRate($userId): array
    {
        $income  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])->sum('so_tien');
        $expense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])->sum('so_tien');
        $rate    = $income > 0 ? round(($income - $expense) / $income * 100, 1) : 0;

        return ['income' => $income, 'expense' => $expense, 'saved' => $income - $expense, 'saving_rate' => $rate,
            'status' => $rate >= 20 ? 'good' : ($rate >= 10 ? 'fair' : 'poor')];
    }
}