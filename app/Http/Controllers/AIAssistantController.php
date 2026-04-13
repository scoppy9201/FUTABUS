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
        $userId = Auth::id();
        $period = $request->input('period', 30);

        try {
            $data         = $this->getUserFinancialData($userId);
            $systemPrompt = $this->buildIntentSystemPrompt($data, Auth::user()->name, collect());

            $response = $this->gemini->generateContent([
                'model'    => 'models/gemini-2.5-flash',
                'contents' => [
                    ['role' => 'user',  'parts' => [['text' => $systemPrompt]]],
                    ['role' => 'model', 'parts' => [['text' => 'Đã nắm dữ liệu.']]],
                    ['role' => 'user',  'parts' => [['text' => "Phân tích chi tiêu {$period} ngày qua và đưa 3 lời khuyên cụ thể có số liệu. Trả về JSON: {\"intent\":\"CHAT\",\"message\":\"...\"}."]]],
                ],
                'generationConfig' => ['maxOutputTokens' => 800, 'temperature' => 0.7],
            ]);

            $raw    = $response['candidates'][0]['content']['parts'][0]['text']
                ?? '{"intent":"CHAT","message":"Không có dữ liệu."}';
            $parsed = $this->ai->parseGeminiResponse($raw);

            return response()->json([
                'success'  => true,
                'analysis' => $this->ai->formatResponse($parsed['message'] ?? $raw),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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

    // ── Insights helpers ──────────────────────────────────────────────────────

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
}