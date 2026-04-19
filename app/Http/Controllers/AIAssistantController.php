<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Budgets;
use App\Models\Transaction;
use App\Services\GeminiService;
use App\Services\AIService;
use App\Http\Controllers\AI\TransactionController as AITransaction;
use App\Http\Controllers\AI\CategoryController   as AICategory;
use App\Http\Controllers\AI\WalletController     as AIWallet;

class AIAssistantController extends Controller
{
    const INTENT_CHAT               = 'CHAT';
    const INTENT_ADD_TRANSACTION    = 'ADD_TRANSACTION';
    const INTENT_UPDATE_TRANSACTION = 'UPDATE_TRANSACTION';
    const INTENT_DELETE_TRANSACTION = 'DELETE_TRANSACTION';
    const INTENT_CREATE_CATEGORY    = 'CREATE_CATEGORY';
    const INTENT_UPDATE_CATEGORY    = 'UPDATE_CATEGORY';
    const INTENT_DELETE_CATEGORY    = 'DELETE_CATEGORY';
    const INTENT_UPDATE_WALLET      = 'UPDATE_WALLET';
    const INTENT_CREATE_WALLET      = 'CREATE_WALLET';
    const INTENT_DELETE_WALLET      = 'DELETE_WALLET';

    public function __construct(
        private GeminiService $gemini,
        private AIService     $ai,
    ) {}

    private function tx():  AITransaction { return app(AITransaction::class); }
    private function cat(): AICategory    { return app(AICategory::class); }
    private function wal(): AIWallet      { return app(AIWallet::class); }

    public function index()
    {
        return view('ai-assistant.index');
    }

    public function clearHistory()
    {
        DB::table('ai_chat_history')->where('user_id', Auth::id())->delete();
        return response()->json(['success' => true]);
    }

    public function suggestions()
    {
        return response()->json(['suggestions' => [
            'Phân tích chi tiêu của tôi tháng này',
            'Tôi vừa chi 150.000đ tiền ăn trưa, ghi giúp tôi',
            'Tạo danh mục Thú cưng cho tôi',
            'Xoá giao dịch ăn uống hôm nay',
            'Cập nhật ngân sách Ăn uống thành 3 triệu',
            'Ngân sách nào đang cảnh báo?',
        ]]);
    }

    // Main chat endpoint
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $userMessage = trim($request->input('message'));
        $userId      = Auth::id();
        $userName    = Auth::user()->name;

        try {
            // 1. Pending confirmation takes priority
            $pendingAction = $this->ai->getPendingAction($userId);
            if ($pendingAction) {
                return $this->handlePendingConfirmation($userMessage, $pendingAction, $userId, $userName);
            }

            // 2. Build context & call Gemini
            $financialData = $this->getUserFinancialData($userId);
            $categories    = Category::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })->get(['id', 'ten_danh_muc', 'loai_danh_muc']);

            $history      = $this->ai->getChatHistory($userId);
            $systemPrompt = $this->buildIntentSystemPrompt($financialData, $userName, $categories);

            $contents = array_merge(
                [
                    ['role' => 'user',  'parts' => [['text' => $systemPrompt]]],
                    ['role' => 'model', 'parts' => [['text' => 'Đã nắm dữ liệu. Sẵn sàng xử lý!']]],
                ],
                $history,
                [['role' => 'user', 'parts' => [['text' => $userMessage]]]]
            );

            $response = $this->gemini->generateContent([
                'model'    => 'models/gemini-2.5-flash',
                'contents' => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => 1024,  
                    'temperature'     => 0.3,
                    'topP'            => 0.9,
                ],
            ]);

            $rawText = $response['candidates'][0]['content']['parts'][0]['text']
                ?? '{"intent":"CHAT","message":"Xin lỗi, tôi không thể trả lời lúc này."}';

            $parsed = $this->ai->parseGeminiResponse($rawText);
            $intent = $parsed['intent'] ?? self::INTENT_CHAT;

            // 3. Route to correct controller
            $result = $this->dispatchIntent($intent, $parsed, $userId, $userName);

            // 4. Save history — single place
            $this->ai->saveChatHistory($userId, $userMessage, $result['message']);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('AI Chat Error', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại sau.',
            ], 500);
        }
    }

    // Intent dispatcher
    private function dispatchIntent(string $intent, array $parsed, int $userId, string $userName): array
    {
        return match ($intent) {
            self::INTENT_ADD_TRANSACTION    => $this->tx()->handleAdd($parsed, $userId, $userName),
            self::INTENT_UPDATE_TRANSACTION => $this->tx()->handleUpdate($parsed, $userId, $userName),
            self::INTENT_DELETE_TRANSACTION => $this->tx()->handleDelete($parsed, $userId, $userName),
            self::INTENT_CREATE_CATEGORY    => $this->cat()->handleCreate($parsed, $userId, $userName),
            self::INTENT_UPDATE_CATEGORY    => $this->cat()->handleUpdate($parsed, $userId, $userName),
            self::INTENT_DELETE_CATEGORY    => $this->cat()->handleDelete($parsed, $userId, $userName),
            self::INTENT_CREATE_WALLET      => $this->wal()->handleCreate($parsed, $userId, $userName),
            self::INTENT_UPDATE_WALLET      => $this->wal()->handleUpdate($parsed, $userId, $userName),
            self::INTENT_DELETE_WALLET      => $this->wal()->handleDelete($parsed, $userId, $userName),
            default                         => $this->handleChat($parsed),
        };
    }

    // Pending confirmation 
    private function handlePendingConfirmation(
        string $userMessage,
        array  $pending,
        int    $userId,
        string $userName
    ): \Illuminate\Http\JsonResponse {
        $lower = mb_strtolower(trim($userMessage));

        $confirmWords = ['có', 'yes', 'ok', 'đúng', 'xác nhận', 'oke', 'được', 'đồng ý', 'y'];
        $cancelWords  = ['không', 'no', 'huỷ', 'hủy', 'thôi', 'cancel', 'bỏ', 'dừng'];

        $isConfirm = collect($confirmWords)->contains(
            fn($w) => preg_match('/(?<![a-zA-ZÀ-ỹ])' . preg_quote($w, '/') . '(?![a-zA-ZÀ-ỹ])/u', $lower)
        );
        $isCancel = collect($cancelWords)->contains(
            fn($w) => preg_match('/(?<![a-zA-ZÀ-ỹ])' . preg_quote($w, '/') . '(?![a-zA-ZÀ-ỹ])/u', $lower)
        );

        if ($isCancel || (!$isConfirm && !$isCancel)) {
            $this->ai->clearPendingAction($userId);
            $msg = $isCancel
                ? "Đã huỷ thao tác. Bạn cần mình giúp gì thêm không {$userName}?"
                : "Mình không chắc ý bạn. Đã huỷ thao tác để an toàn. Bạn muốn thử lại không?";

            $this->ai->saveChatHistory($userId, $userMessage, $msg);
            return response()->json(['success' => true, 'message' => $msg]);
        }

        $this->ai->clearPendingAction($userId);
        $result = $this->dispatchExecute($pending['action'], $pending['data'], $userId, $userName);

        $this->ai->saveChatHistory($userId, $userMessage, $result['message']);
        return response()->json($result);
    }

    private function dispatchExecute(string $action, array $data, int $userId, string $userName): array
    {
        return match ($action) {
            'ADD_TRANSACTION'    => $this->tx()->executeAdd($data, $userId, $userName),
            'UPDATE_TRANSACTION' => $this->tx()->executeUpdate($data, $userId, $userName),
            'DELETE_TRANSACTION' => $this->tx()->executeDelete($data, $userId, $userName),
            'CREATE_CATEGORY'    => $this->cat()->executeCreate($data, $userId, $userName),
            'UPDATE_CATEGORY'    => $this->cat()->executeUpdate($data, $userId, $userName),
            'DELETE_CATEGORY'    => $this->cat()->executeDelete($data, $userId, $userName),
            'CREATE_WALLET'      => $this->wal()->executeCreate($data, $userId, $userName),
            'UPDATE_WALLET'      => $this->wal()->executeUpdate($data, $userId, $userName),
            'DELETE_WALLET'      => $this->wal()->executeDelete($data, $userId, $userName),
            default              => ['success' => false, 'message' => 'Hành động không hợp lệ.'],
        };
    }

    // Chat (CHAT intent) 
    private function handleChat(array $parsed): array
    {
        $message = $this->ai->formatResponse($parsed['message'] ?? 'Xin lỗi, mình không hiểu. Bạn thử lại nhé!');
        return ['success' => true, 'message' => $message];
    }

    // Analyze & Insights 
    public function analyze(Request $request)
    {
        $userId  = Auth::id();
        $period  = $request->input('period', 30);

        try {
            $data        = $this->getAnalyzeData($userId);
            $analyzePrompt = $this->buildAnalyzePrompt($data, Auth::user()->name, $period);

            $response = $this->gemini->generateContent([
                'model'    => 'models/gemini-2.5-flash',
                'contents' => [
                    ['role' => 'user',  'parts' => [['text' => $analyzePrompt]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 2048,
                    'temperature'     => 0.7,
                ],
            ]);

            $raw    = $response['candidates'][0]['content']['parts'][0]['text']
                ?? '{"intent":"CHAT","message":"Không có dữ liệu để phân tích."}';
            $parsed = $this->ai->parseGeminiResponse($raw);

            return response()->json([
                'success'  => true,
                'analysis' => $this->ai->formatResponse($parsed['message'] ?? $raw),
            ]);

        } catch (\Exception $e) {
            Log::error('AI Analyze Error', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Dự báo dòng tiền (FORECAST intent)
    public function forecast(Request $request)
    {
        $userId = Auth::id();

        try {
            $data           = $this->getForecastData($userId);
            $forecastPrompt = $this->buildForecastPrompt($data, Auth::user()->name);

            $response = $this->gemini->generateContent([
                'model'    => 'models/gemini-2.5-flash',
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $forecastPrompt]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 2048,
                    'temperature'     => 0.6,
                ],
            ]);

            $raw    = $response['candidates'][0]['content']['parts'][0]['text']
                ?? '{"intent":"CHAT","message":"Không đủ dữ liệu để dự báo."}';
            $parsed = $this->ai->parseGeminiResponse($raw);

            return response()->json([
                'success'  => true,
                'forecast' => $this->ai->formatResponse($parsed['message'] ?? $raw),
            ]);

        } catch (\Exception $e) {
            Log::error('AI Forecast Error', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Tối ưu ngân sách (BUDGET SUGGESTION)
    public function budgetSuggestion(Request $request)
    {
        $userId = Auth::id();

        try {
            $data   = $this->getBudgetSuggestionData($userId);
            $prompt = $this->buildBudgetSuggestionPrompt($data, Auth::user()->name);

            $response = $this->gemini->generateContent([
                'model'    => 'models/gemini-2.5-flash',
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 2048,
                    'temperature'     => 0.6,
                ],
            ]);

            $raw    = $response['candidates'][0]['content']['parts'][0]['text']
                ?? '{"intent":"CHAT","message":"Không đủ dữ liệu để đề xuất."}';
            $parsed = $this->ai->parseGeminiResponse($raw);

            return response()->json([
                'success'    => true,
                'suggestion' => $this->ai->formatResponse($parsed['message'] ?? $raw),
            ]);

        } catch (\Exception $e) {
            Log::error('AI BudgetSuggestion Error', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Xuất báo cáo (Export Report)
    public function exportReport(Request $request)
    {
        $userId = Auth::id();
        $period = $request->input('period', 'this_month');
        $format = $request->input('format', 'xlsx');

        if (!in_array($period, ['this_month', 'last_month', 'this_year', 'all'])) {
            $period = 'this_month';
        }
        if (!in_array($format, ['xlsx', 'pdf'])) {
            $format = 'xlsx';
        }

        $periodLabel = match($period) {
            'this_month' => 'tháng này',
            'last_month' => 'tháng trước',
            'this_year'  => 'năm nay',
            default      => 'tất cả',
        };

        $formatLabel = $format === 'pdf' ? 'PDF' : 'Excel';

        // Build download URL
        $downloadUrl = $format === 'pdf'
            ? url("/api/v1/dashboard/export-pdf?period={$period}")
            : url("/api/v1/dashboard/export?period={$period}");

        $token = $request->bearerToken() ?? '';

        return response()->json([
            'success'      => true,
            'message'      => "Báo cáo {$formatLabel} kỳ {$periodLabel} đã sẵn sàng {$this->getUserName($userId)}!\nBấm nút bên dưới để tải về nhé.",
            'download_url' => $downloadUrl,
            'format'       => $format,
            'period'       => $period,
            'period_label' => $periodLabel,
            'format_label' => $formatLabel,
        ]);
    }

    private function getUserName(int $userId): string
    {
        return \App\Models\User::find($userId)?->name ?? '';
    }

    public function insights()
    {
        $userId = Auth::id();
        try {
            return response()->json(['success' => true, 'insights' => [
                'spending_trend'   => $this->getSpendingTrend($userId),
                'top_categories'   => $this->getTopCategories($userId),
                'unusual_spending' => $this->getUnusualSpending($userId),
                'saving_rate'      => $this->getSavingRate($userId),
            ]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không thể lấy insights.'], 500);
        }
    }

    // Financial data 
    private function getUserFinancialData(int $userId): array
    {
        $totalIncome  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')->sum('so_tien');
        $totalExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')->sum('so_tien');
        $monthIncome  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])->sum('so_tien');
        $monthExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])->sum('so_tien');
        $lastMonthExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('so_tien');
        $categoryExpenses = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)->where('transactions.loai_giao_dich', 'CHI')
            ->select('categories.ten_danh_muc', DB::raw('SUM(transactions.so_tien) as total'))
            ->groupBy('categories.id', 'categories.ten_danh_muc')->orderByDesc('total')->get();
        $wallets = Budgets::where('user_id', $userId)->get()->map(function ($w) {
            $w->da_chi         = $w->ngan_sach_goc - $w->so_du;
            $w->spent_percentage = $w->spent_percentage;
            return $w;
        });
        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')->orderByDesc('ngay_giao_dich')->limit(10)->get();

        return compact(
            'totalIncome', 'totalExpense', 'monthIncome', 'monthExpense',
            'lastMonthExpense', 'categoryExpenses', 'wallets', 'recentTransactions'
        );
    }

    // Gemini system prompt
    private function buildIntentSystemPrompt(array $d, string $userName, $categories): string
    {
        $balance    = $d['totalIncome'] - $d['totalExpense'];
        $savingRate = $d['monthIncome'] > 0
            ? round(($d['monthIncome'] - $d['monthExpense']) / $d['monthIncome'] * 100, 1) : 0;
        $catList = $categories->map(fn($c) => "- ID:{$c->id} | {$c->ten_danh_muc} | {$c->loai_danh_muc}")->implode("\n");
        $today   = now()->format('Y-m-d');

        return <<<PROMPT
=== VAI TRÒ ===
Bạn là Monexa AI — trợ lý tài chính cá nhân của ứng dụng Monexa.
Người dùng: {$userName}. Ngôn ngữ: Tiếng Việt. Thân thiện, chuyên nghiệp.

=== NHIỆM VỤ QUAN TRỌNG ===
Phân tích tin nhắn và trả về JSON theo đúng format bên dưới.
KHÔNG trả về text thuần. CHỈ trả về JSON hợp lệ.

=== CÁC INTENT ===
1. CHAT — hội thoại thường, hỏi về tài chính, phân tích chi tiêu
2. ADD_TRANSACTION — user muốn ghi thêm thu nhập hoặc chi tiêu
Dấu hiệu: "tôi vừa chi", "ghi giúp tôi", "thêm khoản", "hôm nay tôi mua", "tôi được nhận"
3. UPDATE_TRANSACTION — user muốn sửa giao dịch
Dấu hiệu: "sửa giao dịch", "đổi số tiền", "cập nhật khoản"
4. DELETE_TRANSACTION — user muốn xoá giao dịch
Dấu hiệu: "xoá giao dịch", "huỷ khoản", "xoá cái vừa thêm"
5. CREATE_CATEGORY — user muốn tạo danh mục mới
Dấu hiệu: "tạo danh mục", "thêm danh mục", "tôi muốn có mục mới"
6. UPDATE_CATEGORY — user muốn sửa/đổi tên/biểu tượng/loại danh mục
Dấu hiệu: "sửa danh mục", "đổi tên danh mục", "thay biểu tượng", "cập nhật danh mục"
7. DELETE_CATEGORY — user muốn xóa danh mục
Dấu hiệu: "xóa danh mục", "bỏ danh mục", "xóa mục"
8. CREATE_WALLET — user muốn tạo ngân sách mới
Dấu hiệu: "tạo ngân sách", "thêm ví", "tạo budget mới", "tạo quỹ"
9. UPDATE_WALLET — user muốn sửa ngân sách
Dấu hiệu: "đổi ngân sách", "cập nhật hạn mức", "sửa budget"
10. DELETE_WALLET — user muốn xóa ngân sách
Dấu hiệu: "xóa ngân sách", "bỏ ví", "xóa budget", "xóa quỹ"

=== FORMAT JSON ===

CHAT:
{"intent":"CHAT","message":"Nội dung trả lời"}

ADD_TRANSACTION:
{"intent":"ADD_TRANSACTION","data":{"so_tien":150000,"loai_giao_dich":"CHI","category_id":3,"ten_danh_muc":"Ăn uống","ngay_giao_dich":"{$today}","ghi_chu":"ăn trưa"}}

UPDATE_TRANSACTION:
{"intent":"UPDATE_TRANSACTION","data":{"so_tien_cu":150000,"category_name":"Ăn uống","ngay_giao_dich":"{$today}","so_tien_moi":200000,"ghi_chu_moi":"ăn tối","category_name_moi":""}}

DELETE_TRANSACTION:
{"intent":"DELETE_TRANSACTION","data":{"so_tien":150000,"category_name":"Ăn uống","ngay_giao_dich":"{$today}"}}

CREATE_CATEGORY (danh mục gốc):
{"intent":"CREATE_CATEGORY","data":{"ten_danh_muc":"Hiếu hỉ","loai_danh_muc":"CHI","bieu_tuong":"🎊","mo_ta":"","danh_muc_cha":""}}

CREATE_CATEGORY (danh mục con):
{"intent":"CREATE_CATEGORY","data":{"ten_danh_muc":"Cafe","loai_danh_muc":"","bieu_tuong":"☕","mo_ta":"","danh_muc_cha":"Ăn uống"}}

Quy tắc:
- danh_muc_cha = tên danh mục cha nếu user muốn tạo danh mục con, để trống "" nếu là danh mục gốc.
- Nếu có danh_muc_cha thì KHÔNG cần loai_danh_muc (sẽ kế thừa từ cha).
Dấu hiệu danh mục con: "trong danh mục", "thuộc", "con của", "dưới mục", "nhỏ hơn", "ví dụ: tạo danh mục Cafe trong Ăn uống"

UPDATE_CATEGORY:
{"intent":"UPDATE_CATEGORY","data":{"ten_danh_muc":"Cafe","ten_danh_muc_moi":"Cà phê","loai_danh_muc_moi":"CHI","bieu_tuong_moi":"☕","mo_ta_moi":"Chi phí cà phê","danh_muc_cha_moi":"Ăn uống"}}

Quy tắc:
- ten_danh_muc: tên danh mục CẦN TÌM (hiện tại).
- Các trường _moi: giá trị muốn cập nhật, bỏ qua nếu không thay đổi (KHÔNG đưa key vào JSON).
- danh_muc_cha_moi: tên danh mục cha mới nếu muốn chuyển cấp.
  + Để "" nếu user muốn chuyển lên thành danh mục gốc.
  + Không đưa key này vào JSON nếu user không đề cập đến danh mục cha.

DELETE_CATEGORY:
{"intent":"DELETE_CATEGORY","data":{"ten_danh_muc":"Cafe"}}

Quy tắc:
- Chỉ cần ten_danh_muc. Hệ thống sẽ tự kiểm tra danh mục con và giao dịch liên quan.

CREATE_WALLET:
{"intent":"CREATE_WALLET","data":{"ten_ngan_sach":"Du lịch","ngan_sach_goc":5000000,"ten_danh_muc":"Du lịch","mo_ta":"Quỹ đi chơi"}}

UPDATE_WALLET:
{"intent":"UPDATE_WALLET","data":{"ten_ngan_sach":"Ăn uống","ngan_sach_goc":3000000}}

DELETE_WALLET:
{"intent":"DELETE_WALLET","data":{"ten_ngan_sach":"Du lịch"}}

=== DANH MỤC HIỆN CÓ ===
{$catList}

=== DỮ LIỆU TÀI CHÍNH ===
- Số dư: {$balance} VND
- Thu tháng này: {$d['monthIncome']} VND
- Chi tháng này: {$d['monthExpense']} VND
- Tỷ lệ tiết kiệm: {$savingRate}%

- TUYỆT ĐỐI KHÔNG tự thông báo đã thực hiện hành động (tạo/sửa/xóa) trong intent CHAT.
- Nếu user yêu cầu tạo/sửa/xóa bất cứ thứ gì → LUÔN trả về đúng intent tương ứng, KHÔNG dùng CHAT để thông báo kết quả.
- Chỉ có hệ thống mới được xác nhận hành động đã hoàn thành. Gemini KHÔNG được tự xác nhận.
- Ví dụ SAI: {"intent":"CHAT","message":"Danh mục X đã được tạo rồi nhé"}
- Ví dụ ĐÚNG: {"intent":"CREATE_CATEGORY","data":{...}}

=== QUY TẮC ===
- intent CHAT: trả lời ngắn gọn, dùng dấu (-) để liệt kê, không dùng markdown (**, ##)
- Không liên quan tài chính: {"intent":"CHAT","message":"Mình chỉ hỗ trợ tư vấn tài chính thôi nhé {$userName}!"}
- Ngày hôm nay: {$today}
- QUAN TRỌNG: Với tất cả intent DELETE_* và UPDATE_*: LUÔN trả về đúng intent, KHÔNG tự phán xét hay từ chối
PROMPT;
    }

    private function getSpendingTrend(int $userId): array
    {
        $current = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])->sum('so_tien');
        $last    = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('so_tien');
        $change  = $last > 0 ? round(($current - $last) / $last * 100, 1) : 0;
        return ['current_month' => $current, 'last_month' => $last, 'change_percentage' => $change, 'trend' => $change > 0 ? 'increase' : 'decrease'];
    }

    private function getTopCategories(int $userId)
    {
        return DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)->where('transactions.loai_giao_dich', 'CHI')
            ->whereBetween('transactions.ngay_giao_dich', [now()->subDays(30), now()])
            ->select('categories.ten_danh_muc', 'categories.bieu_tuong', DB::raw('SUM(transactions.so_tien) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('categories.id', 'categories.ten_danh_muc', 'categories.bieu_tuong')
            ->orderByDesc('total')->limit(3)->get();
    }

    private function getUnusualSpending(int $userId)
    {
        $avg       = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->subMonths(3), now()->subMonth()])->avg('so_tien');
        $threshold = ($avg ?? 0) * 1.5;
        return Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])
            ->where('so_tien', '>', $threshold)->with('category')->orderByDesc('so_tien')->limit(3)->get();
    }

    private function getSavingRate(int $userId): array
    {
        $income  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])->sum('so_tien');
        $expense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [now()->startOfMonth(), now()])->sum('so_tien');
        $rate    = $income > 0 ? round(($income - $expense) / $income * 100, 1) : 0;
        return ['income' => $income, 'expense' => $expense, 'saved' => $income - $expense, 'saving_rate' => $rate, 'status' => $rate >= 20 ? 'good' : ($rate >= 10 ? 'fair' : 'poor')];
    }

    public function getHistory()
    {
        $userId = Auth::id();

        $rows = DB::table('ai_chat_history')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get(['id', 'user_message', 'ai_response', 'created_at']);

        $grouped = $rows->groupBy(function ($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
        })->map(function ($items, $date) {
            $carbon = \Carbon\Carbon::parse($date);
            return [
                'date'     => $date,
                'label'    => $carbon->isToday()      ? 'Hôm nay'
                            : ($carbon->isYesterday()  ? 'Hôm qua'
                            : $carbon->format('d/m/Y')),
                'preview'  => \Illuminate\Support\Str::limit($items->first()->user_message, 50),
                'count'    => $items->count(),
                'messages' => $items->values(),
            ];
        })->values();

        return response()->json(['success' => true, 'history' => $grouped]);
    }

    private function getAnalyzeData(int $userId): array
    {
        $now = now();

        // Tháng này
        $monthIncome  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')
            ->whereBetween('ngay_giao_dich', [$now->copy()->startOfMonth(), $now])->sum('so_tien');
        $monthExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [$now->copy()->startOfMonth(), $now])->sum('so_tien');

        // Tháng trước
        $lastMonthIncome  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')
            ->whereBetween('ngay_giao_dich', [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ])->sum('so_tien');
        $lastMonthExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ])->sum('so_tien');

        // % thay đổi chi tiêu
        $expenseChange = $lastMonthExpense > 0
            ? round(($monthExpense - $lastMonthExpense) / $lastMonthExpense * 100, 1)
            : 0;

        // Top 5 danh mục chi nhiều nhất tháng này
        $topCategories = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.loai_giao_dich', 'CHI')
            ->whereBetween('transactions.ngay_giao_dich', [$now->copy()->startOfMonth(), $now])
            ->select(
                'categories.ten_danh_muc',
                'categories.bieu_tuong',
                DB::raw('SUM(transactions.so_tien) as total'),
                DB::raw('COUNT(*) as so_lan')
            )
            ->groupBy('categories.id', 'categories.ten_danh_muc', 'categories.bieu_tuong')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Ngân sách đang cảnh báo (đã dùng >= 80%)
        $budgetWarnings = Budgets::where('user_id', $userId)
            ->where('trang_thai', true)
            ->where('da_het_han', false)
            ->get()
            ->filter(fn($b) => $b->spent_percentage >= 80)
            ->map(fn($b) => [
                'ten'          => $b->ten_ngan_sach,
                'goc'          => $b->ngan_sach_goc,
                'da_chi'       => $b->ngan_sach_goc - $b->so_du,
                'con_lai'      => $b->so_du,
                'phan_tram'    => $b->spent_percentage,
            ])->values();

        // Ngân sách đã vượt (so_du < 0)
        $budgetOverspent = Budgets::where('user_id', $userId)
            ->where('trang_thai', true)
            ->where('so_du', '<', 0)
            ->get()
            ->map(fn($b) => [
                'ten'       => $b->ten_ngan_sach,
                'vuot'      => abs($b->so_du),
            ])->values();

        // Tỷ lệ tiết kiệm
        $savingRate = $monthIncome > 0
            ? round(($monthIncome - $monthExpense) / $monthIncome * 100, 1)
            : 0;

        // Chi tiêu theo ngày trong tháng (để phát hiện ngày chi nhiều)
        $dailyExpense = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [$now->copy()->startOfMonth(), $now])
            ->select(DB::raw('DATE(ngay_giao_dich) as ngay'), DB::raw('SUM(so_tien) as total'))
            ->groupBy('ngay')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        return compact(
            'monthIncome', 'monthExpense',
            'lastMonthIncome', 'lastMonthExpense', 'expenseChange',
            'topCategories', 'budgetWarnings', 'budgetOverspent',
            'savingRate', 'dailyExpense'
        );
    }

    private function buildAnalyzePrompt(array $d, string $userName, int $period): string
    {
        $thangNay    = now()->format('m/Y');
        $thangTruoc  = now()->subMonth()->format('m/Y');
        $savingStatus = $d['savingRate'] >= 20 ? 'tốt' : ($d['savingRate'] >= 10 ? 'trung bình' : 'cần cải thiện');
        $expenseTrend = $d['expenseChange'] > 0
            ? "tăng {$d['expenseChange']}% so với tháng trước"
            : "giảm " . abs($d['expenseChange']) . "% so với tháng trước";

        // Format top categories
        $topCatText = $d['topCategories']->map(function ($c, $i) use ($d) {
            $pct = $d['monthExpense'] > 0 ? round($c->total / $d['monthExpense'] * 100, 1) : 0;
            return ($i + 1) . ". {$c->ten_danh_muc}: " . number_format($c->total) . " VND ({$pct}% tổng chi, {$c->so_lan} lần)";
        })->implode("\n");

        // Format budget warnings
        $budgetWarnText = $d['budgetWarnings']->count() > 0
            ? $d['budgetWarnings']->map(fn($b) =>
                "- {$b['ten']}: đã dùng {$b['phan_tram']}% ({$b['da_chi']} / {$b['goc']} VND, còn " . number_format($b['con_lai']) . " VND)"
            )->implode("\n")
            : "Không có ngân sách nào cảnh báo.";

        $budgetOverText = $d['budgetOverspent']->count() > 0
            ? $d['budgetOverspent']->map(fn($b) =>
                "- {$b['ten']}: vượt " . number_format($b['vuot']) . " VND"
            )->implode("\n")
            : "Không có ngân sách nào bị vượt.";

        // Format ngày chi nhiều nhất
        $topDaysText = $d['dailyExpense']->map(fn($day) =>
            "- Ngày {$day->ngay}: " . number_format($day->total) . " VND"
        )->implode("\n");

        return <<<PROMPT
    Bạn là Monexa AI — chuyên gia phân tích tài chính cá nhân.
    Hãy phân tích chi tiêu của {$userName} dựa trên dữ liệu thực tế dưới đây.
    Trả về JSON hợp lệ duy nhất: {"intent":"CHAT","message":"..."}
    KHÔNG dùng markdown (**, ##). Dùng (-) để liệt kê. Ngắn gọn, có số liệu cụ thể.

    === DỮ LIỆU THÁNG {$thangNay} ===
    - Thu nhập: {$d['monthIncome']} VND
    - Chi tiêu: {$d['monthExpense']} VND (${expenseTrend})
    - Tiết kiệm: {$d['savingRate']}% ({$savingStatus})
    - Tháng trước ({$thangTruoc}): Thu {$d['lastMonthIncome']} VND | Chi {$d['lastMonthExpense']} VND

    === TOP DANH MỤC CHI NHIỀU NHẤT ===
    {$topCatText}

    === NGÀY CHI NHIỀU NHẤT ===
    {$topDaysText}

    === NGÂN SÁCH CẢNH BÁO (>= 80%) ===
    {$budgetWarnText}

    === NGÂN SÁCH ĐÃ VƯỢT ===
    {$budgetOverText}

    === YÊU CẦU PHÂN TÍCH ===
    Hãy viết phân tích theo cấu trúc:
    1. Tổng quan tháng này (1-2 câu có số liệu)
    2. Chi tiêu nổi bật (danh mục nào đáng chú ý, so sánh tỷ trọng)
    3. Cảnh báo ngân sách (nếu có)
    4. 3 lời khuyên cụ thể có số liệu thực tế từ dữ liệu trên
    5. Đánh giá tổng thể ngắn gọn

    Viết thân thiện, xưng "mình" với {$userName}, có số liệu cụ thể.
    PROMPT;
    }

    private function getForecastData(int $userId): array
    {
        $now = now();

        // ── 3 tháng gần nhất (không tính tháng hiện tại) ──
        $months = [];
        for ($i = 3; $i >= 1; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end   = $now->copy()->subMonths($i)->endOfMonth();
            $label = $now->copy()->subMonths($i)->format('m/Y');

            $income  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')
                ->whereBetween('ngay_giao_dich', [$start, $end])->sum('so_tien');
            $expense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
                ->whereBetween('ngay_giao_dich', [$start, $end])->sum('so_tien');

            $months[] = [
                'label'   => $label,
                'income'  => $income,
                'expense' => $expense,
                'saving'  => $income - $expense,
            ];
        }

        // ── Tháng hiện tại ──
        $currentStart   = $now->copy()->startOfMonth();
        $currentIncome  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')
            ->whereBetween('ngay_giao_dich', [$currentStart, $now])->sum('so_tien');
        $currentExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [$currentStart, $now])->sum('so_tien');

        // Số ngày đã qua và còn lại trong tháng
        $daysInMonth  = $now->daysInMonth;
        $daysPassed   = $now->day;
        $daysLeft     = $daysInMonth - $daysPassed;

        // Tốc độ chi trung bình mỗi ngày tháng này
        $dailyRate = $daysPassed > 0 ? $currentExpense / $daysPassed : 0;

        // Dự báo chi tiêu cuối tháng = chi hiện tại + (tốc độ ngày * số ngày còn lại)
        $forecastExpense = $currentExpense + ($dailyRate * $daysLeft);

        // Trung bình chi 3 tháng trước
        $avgExpense3Months = collect($months)->avg('expense');
        $avgIncome3Months  = collect($months)->avg('income');

        // ── Chi tiêu theo danh mục tháng này ──
        $categoryThisMonth = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.loai_giao_dich', 'CHI')
            ->whereBetween('transactions.ngay_giao_dich', [$currentStart, $now])
            ->select(
                'categories.ten_danh_muc',
                DB::raw('SUM(transactions.so_tien) as total')
            )
            ->groupBy('categories.id', 'categories.ten_danh_muc')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ── Ngân sách có nguy cơ vượt ──
        // Dự báo từng ngân sách dựa trên tốc độ chi hiện tại
        $budgets = Budgets::where('user_id', $userId)
            ->where('trang_thai', true)
            ->where('da_het_han', false)
            ->get();

        $budgetRisks = $budgets->map(function ($b) use ($daysPassed, $daysLeft) {
            $daChi       = $b->ngan_sach_goc - $b->so_du;
            $dailyBudget = $daysPassed > 0 ? $daChi / $daysPassed : 0;
            $forecast    = $daChi + ($dailyBudget * $daysLeft);
            $pct         = $b->ngan_sach_goc > 0 ? round($forecast / $b->ngan_sach_goc * 100, 1) : 0;

            return [
                'ten'             => $b->ten_ngan_sach,
                'ngan_sach_goc'   => $b->ngan_sach_goc,
                'da_chi'          => $daChi,
                'con_lai'         => $b->so_du,
                'du_bao_cuoi_thang' => round($forecast),
                'du_bao_pct'      => $pct,
                'nguy_co'         => $pct >= 100 ? 'vượt' : ($pct >= 80 ? 'cảnh báo' : 'an toàn'),
            ];
        })->filter(fn($b) => $b['nguy_co'] !== 'an toàn')
        ->sortByDesc('du_bao_pct')
        ->values();

        return compact(
            'months', 'currentIncome', 'currentExpense',
            'daysPassed', 'daysLeft', 'daysInMonth',
            'dailyRate', 'forecastExpense',
            'avgExpense3Months', 'avgIncome3Months',
            'categoryThisMonth', 'budgetRisks'
        );
    }

    private function buildForecastPrompt(array $d, string $userName): string
    {
        $thangNay    = now()->format('m/Y');
        $daysInMonth = $d['daysInMonth'];
        $daysPassed  = $d['daysPassed'];
        $daysLeft    = $d['daysLeft'];

        // Format lịch sử 3 tháng
        $historyText = collect($d['months'])->map(fn($m) =>
            "- Tháng {$m['label']}: Thu " . number_format($m['income']) . " | Chi " . number_format($m['expense']) . " | Tiết kiệm " . number_format($m['saving']) . " VND"
        )->implode("\n");

        // Format danh mục tháng này
        $catText = $d['categoryThisMonth']->map(fn($c) =>
            "- {$c->ten_danh_muc}: " . number_format($c->total) . " VND"
        )->implode("\n");

        // Format ngân sách rủi ro
        $riskText = $d['budgetRisks']->count() > 0
            ? $d['budgetRisks']->map(fn($b) =>
                "- {$b['ten']}: đã chi " . number_format($b['da_chi']) . " / " . number_format($b['ngan_sach_goc']) . " VND"
                . " → dự báo cuối tháng: " . number_format($b['du_bao_cuoi_thang']) . " VND ({$b['du_bao_pct']}%) — {$b['nguy_co']}"
            )->implode("\n")
            : "Không có ngân sách nào có nguy cơ vượt.";

        $forecastVsAvg = $d['avgExpense3Months'] > 0
            ? round(($d['forecastExpense'] - $d['avgExpense3Months']) / $d['avgExpense3Months'] * 100, 1)
            : 0;
        $forecastTrend = $forecastVsAvg > 0
            ? "cao hơn trung bình 3 tháng {$forecastVsAvg}%"
            : "thấp hơn trung bình 3 tháng " . abs($forecastVsAvg) . "%";

        return <<<PROMPT
    Bạn là Monexa AI — chuyên gia phân tích và dự báo tài chính cá nhân.
    Hãy dự báo chi tiêu của {$userName} dựa trên dữ liệu thực tế dưới đây.
    Trả về JSON hợp lệ duy nhất: {"intent":"CHAT","message":"..."}
    KHÔNG dùng markdown (**, ##). Dùng (-) để liệt kê. Thân thiện, có số liệu cụ thể.

    === LỊCH SỬ 3 THÁNG GẦN NHẤT ===
    {$historyText}
    - Trung bình chi/tháng: {$d['avgExpense3Months']} VND
    - Trung bình thu/tháng: {$d['avgIncome3Months']} VND

    === THÁNG HIỆN TẠI ({$thangNay}) ===
    - Đã qua: {$daysPassed}/{$daysInMonth} ngày (còn {$daysLeft} ngày)
    - Thu đến nay: {$d['currentIncome']} VND
    - Chi đến nay: {$d['currentExpense']} VND
    - Tốc độ chi trung bình/ngày: {$d['dailyRate']} VND

    === DỰ BÁO CUỐI THÁNG ===
    - Chi tiêu dự kiến: {$d['forecastExpense']} VND ({$forecastTrend})

    === CHI TIÊU THEO DANH MỤC THÁNG NÀY ===
    {$catText}

    === NGÂN SÁCH CÓ NGUY CƠ VƯỢT ===
    {$riskText}

    === YÊU CẦU DỰ BÁO ===
    Hãy viết dự báo theo cấu trúc:
    1. Dự báo tổng quan (chi tiêu dự kiến cuối tháng, so với thu nhập)
    2. Xu hướng so với 3 tháng trước (tăng/giảm, lý do có thể)
    3. Cảnh báo ngân sách có nguy cơ vượt (nếu có, kèm số liệu cụ thể)
    4. 3 gợi ý hành động cụ thể để kiểm soát chi tiêu những ngày còn lại
    5. Kết luận ngắn gọn (lạc quan nếu tình hình tốt, thúc đẩy nếu cần cải thiện)

    Xưng "mình" với {$userName}. Viết tự nhiên, không khô khan.
    PROMPT;
    }

    private function getBudgetSuggestionData(int $userId): array
    {
        $now = now();

        // ── Lịch sử chi tiêu 3 tháng theo danh mục ──
        $categoryHistory = [];
        for ($i = 3; $i >= 1; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end   = $now->copy()->subMonths($i)->endOfMonth();
            $label = $now->copy()->subMonths($i)->format('m/Y');

            $rows = DB::table('transactions')
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.user_id', $userId)
                ->where('transactions.loai_giao_dich', 'CHI')
                ->whereBetween('transactions.ngay_giao_dich', [$start, $end])
                ->select(
                    'categories.id',
                    'categories.ten_danh_muc',
                    DB::raw('SUM(transactions.so_tien) as total')
                )
                ->groupBy('categories.id', 'categories.ten_danh_muc')
                ->get();

            foreach ($rows as $row) {
                if (!isset($categoryHistory[$row->id])) {
                    $categoryHistory[$row->id] = [
                        'ten'     => $row->ten_danh_muc,
                        'months'  => [],
                        'avg'     => 0,
                    ];
                }
                $categoryHistory[$row->id]['months'][$label] = $row->total;
            }
        }

        // Tính trung bình mỗi danh mục
        foreach ($categoryHistory as $id => $cat) {
            $categoryHistory[$id]['avg'] = round(collect($cat['months'])->avg());
        }

        // ── Ngân sách hiện tại ──
        $budgets = Budgets::where('user_id', $userId)
            ->where('trang_thai', true)
            ->where('da_het_han', false)
            ->with('category')
            ->get()
            ->keyBy('category_id');

        // ── Danh mục CHI không có ngân sách ──
        $categoryIds = array_keys($categoryHistory);
        $budgetCategoryIds = $budgets->keys()->toArray();
        $noBudgetCategoryIds = array_diff($categoryIds, $budgetCategoryIds);

        $noBudgetCategories = collect($categoryHistory)
            ->filter(fn($cat, $id) => in_array($id, $noBudgetCategoryIds))
            ->map(fn($cat, $id) => [
                'id'  => $id,
                'ten' => $cat['ten'],
                'avg' => $cat['avg'],
            ])->values();

        // ── Ngân sách thường xuyên vượt (so sánh ngan_sach_goc vs avg chi lịch sử) ──
        $overspentBudgets = $budgets->filter(function ($b) use ($categoryHistory) {
            $catId = $b->category_id;
            $avg   = $categoryHistory[$catId]['avg'] ?? 0;
            return $avg > $b->ngan_sach_goc && $b->ngan_sach_goc > 0;
        })->map(function ($b) use ($categoryHistory) {
            $avg = $categoryHistory[$b->category_id]['avg'] ?? 0;
            return [
                'ten'          => $b->ten_ngan_sach,
                'hien_tai'     => $b->ngan_sach_goc,
                'avg_chi'      => $avg,
                'chenh_lech'   => $avg - $b->ngan_sach_goc,
                'de_xuat'      => round($avg * 1.1 / 100000) * 100000, // làm tròn 100k
            ];
        })->values();

        // ── Ngân sách dư nhiều (chi < 50% ngân sách liên tục) ──
        $underusedBudgets = $budgets->filter(function ($b) use ($categoryHistory) {
            $catId = $b->category_id;
            $avg   = $categoryHistory[$catId]['avg'] ?? 0;
            return $avg > 0 && $avg < ($b->ngan_sach_goc * 0.5);
        })->map(function ($b) use ($categoryHistory) {
            $avg = $categoryHistory[$b->category_id]['avg'] ?? 0;
            return [
                'ten'        => $b->ten_ngan_sach,
                'hien_tai'   => $b->ngan_sach_goc,
                'avg_chi'    => $avg,
                'su_dung_pct'=> round($avg / $b->ngan_sach_goc * 100, 1),
                'de_xuat'    => round($avg * 1.2 / 100000) * 100000,
            ];
        })->values();

        // ── Tháng này ──
        $monthIncome  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')
            ->whereBetween('ngay_giao_dich', [$now->copy()->startOfMonth(), $now])->sum('so_tien');
        $monthExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')
            ->whereBetween('ngay_giao_dich', [$now->copy()->startOfMonth(), $now])->sum('so_tien');
        $totalBudget  = $budgets->sum('ngan_sach_goc');

        return compact(
            'categoryHistory', 'budgets',
            'noBudgetCategories', 'overspentBudgets', 'underusedBudgets',
            'monthIncome', 'monthExpense', 'totalBudget'
        );
    }

    private function buildBudgetSuggestionPrompt(array $d, string $userName): string
    {
        $thangNay = now()->format('m/Y');

        // Format ngân sách thường vượt
        $overspentText = $d['overspentBudgets']->count() > 0
            ? $d['overspentBudgets']->map(fn($b) =>
                "- {$b['ten']}: ngân sách " . number_format($b['hien_tai']) . " VND"
                . " | trung bình chi thực tế " . number_format($b['avg_chi']) . " VND"
                . " | chênh lệch +" . number_format($b['chenh_lech']) . " VND"
                . " → đề xuất nâng lên " . number_format($b['de_xuat']) . " VND"
            )->implode("\n")
            : "Không có ngân sách nào thường xuyên bị vượt.";

        // Format ngân sách dư nhiều
        $underusedText = $d['underusedBudgets']->count() > 0
            ? $d['underusedBudgets']->map(fn($b) =>
                "- {$b['ten']}: ngân sách " . number_format($b['hien_tai']) . " VND"
                . " | chỉ dùng {$b['su_dung_pct']}% (avg " . number_format($b['avg_chi']) . " VND)"
                . " → có thể giảm xuống " . number_format($b['de_xuat']) . " VND"
            )->implode("\n")
            : "Không có ngân sách nào dư nhiều.";

        // Format danh mục chưa có ngân sách
        $noBudgetText = $d['noBudgetCategories']->count() > 0
            ? $d['noBudgetCategories']->map(fn($c) =>
                "- {$c['ten']}: trung bình chi " . number_format($c['avg']) . " VND/tháng (chưa có ngân sách)"
            )->implode("\n")
            : "Tất cả danh mục đều đã có ngân sách.";

        // Format lịch sử chi theo danh mục
        $historyText = collect($d['categoryHistory'])->map(function ($cat, $id) {
            $monthsStr = collect($cat['months'])->map(
                fn($total, $label) => "{$label}: " . number_format($total)
            )->implode(" | ");
            return "- {$cat['ten']}: {$monthsStr} | Avg: " . number_format($cat['avg']) . " VND";
        })->implode("\n");

        return <<<PROMPT
    Bạn là Monexa AI — chuyên gia tư vấn tài chính cá nhân.
    Hãy đưa ra đề xuất cải thiện ngân sách cho {$userName} dựa trên dữ liệu thực tế.
    Trả về JSON hợp lệ duy nhất: {"intent":"CHAT","message":"..."}
    KHÔNG dùng markdown (**, ##). Dùng (-) để liệt kê. Thân thiện, thực tế, có số liệu.

    === TỔNG QUAN THÁNG {$thangNay} ===
    - Thu nhập: {$d['monthIncome']} VND
    - Chi tiêu: {$d['monthExpense']} VND
    - Tổng ngân sách đang quản lý: {$d['totalBudget']} VND

    === LỊCH SỬ CHI THEO DANH MỤC (3 THÁNG) ===
    {$historyText}

    === NGÂN SÁCH THƯỜNG XUYÊN BỊ VƯỢT ===
    {$overspentText}

    === NGÂN SÁCH DƯ NHIỀU (dùng < 50%) ===
    {$underusedText}

    === DANH MỤC CHI NHIỀU NHƯNG CHƯA CÓ NGÂN SÁCH ===
    {$noBudgetText}

    === YÊU CẦU ĐỀ XUẤT ===
    Hãy tư vấn theo cấu trúc:
    1. Tổng quan tình trạng ngân sách hiện tại (1-2 câu)
    2. Ngân sách cần điều chỉnh tăng (nêu cụ thể tên, số tiền đề xuất, lý do)
    3. Ngân sách có thể giảm để tối ưu (nêu cụ thể, giải phóng bao nhiêu VND)
    4. Danh mục nên tạo ngân sách mới (nếu có, kèm mức đề xuất dựa trên lịch sử)
    5. Lời khuyên phân bổ ngân sách tổng thể (ngắn gọn, thực tế)

    Quan trọng:
    - Đề xuất số tiền cụ thể, làm tròn đến 100.000 VND
    - Xưng "mình" với {$userName}
    - Nếu tình hình tốt thì khen ngợi và gợi ý tối ưu thêm
    - Nếu có vấn đề thì thẳng thắn nhưng động viên
    PROMPT;
    }
}