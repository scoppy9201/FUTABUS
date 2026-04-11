<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budgets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\GeminiService;
use Carbon\Carbon;

class AIAssistantController extends Controller
{
    private GeminiService $gemini;

    const INTENT_CHAT               = 'CHAT';
    const INTENT_ADD_TRANSACTION    = 'ADD_TRANSACTION';
    const INTENT_DELETE_TRANSACTION = 'DELETE_TRANSACTION';
    const INTENT_CREATE_CATEGORY    = 'CREATE_CATEGORY';
    const INTENT_UPDATE_WALLET      = 'UPDATE_WALLET';
    const INTENT_UPDATE_TRANSACTION  = 'UPDATE_TRANSACTION';
    const INTENT_DELETE_CATEGORY     = 'DELETE_CATEGORY';
    const INTENT_CREATE_WALLET       = 'CREATE_WALLET';
    const INTENT_DELETE_WALLET       = 'DELETE_WALLET';

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function index()
    {
        return view('ai-assistant.index');
    }

    // CHAT CHÍNH
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $userMessage = trim($request->input('message'));
        $userId      = Auth::id();
        $userName    = Auth::user()->name;

        try {
            // Kiểm tra pending action (đang chờ xác nhận)
            $pendingAction = $this->getPendingAction($userId);
            if ($pendingAction) {
                return $this->handlePendingConfirmation($userMessage, $pendingAction, $userId, $userName);
            }

            // Lấy dữ liệu
            $financialData = $this->getUserFinancialData($userId);
            $categories    = Category::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })->get(['id', 'ten_danh_muc', 'loai_danh_muc']);

            $history      = $this->getChatHistory($userId);
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
                    'maxOutputTokens' => 800,
                    'temperature'     => 0.3,
                    'topP'            => 0.9,
                ],
            ]);

            $rawText = $response['candidates'][0]['content']['parts'][0]['text']
                ?? '{"intent":"CHAT","message":"Xin lỗi, tôi không thể trả lời lúc này."}';

            $parsed = $this->parseGeminiResponse($rawText);
            $intent = $parsed['intent'] ?? self::INTENT_CHAT;

            // Xử lý theo intent — tất cả đều trả về array
            $result = match ($intent) {
                self::INTENT_ADD_TRANSACTION    => $this->handleAddTransaction($parsed, $userId, $userName),
                self::INTENT_DELETE_TRANSACTION => $this->handleDeleteTransaction($parsed, $userId, $userName),
                self::INTENT_CREATE_CATEGORY    => $this->handleCreateCategory($parsed, $userId, $userName),
                self::INTENT_UPDATE_WALLET      => $this->handleUpdateWallet($parsed, $userId, $userName),
                self::INTENT_UPDATE_TRANSACTION  => $this->handleUpdateTransaction($parsed, $userId, $userName),
                self::INTENT_DELETE_CATEGORY     => $this->handleDeleteCategory($parsed, $userId, $userName),
                self::INTENT_CREATE_WALLET       => $this->handleCreateWallet($parsed, $userId, $userName),
                self::INTENT_DELETE_WALLET       => $this->handleDeleteWallet($parsed, $userId, $userName),
                default                         => $this->handleChat($parsed),
            };

            // Lưu lịch sử — 1 chỗ duy nhất
            $this->saveChatHistory($userId, $userMessage, $result['message']);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('AI Chat Error', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại sau.',
            ], 500);
        }
    }

    // XỬ LÝ XÁC NHẬN PENDING ACTION
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

        // Ưu tiên cancel nếu có cả 2
        if ($isCancel || (!$isConfirm && !$isCancel)) {
            $this->clearPendingAction($userId);
            $msg = $isCancel
                ? "Đã huỷ thao tác. Bạn cần mình giúp gì thêm không {$userName}?"
                : "Mình không chắc ý bạn. Đã huỷ thao tác để an toàn. Bạn muốn thử lại không?";

            $this->saveChatHistory($userId, $userMessage, $msg);
            return response()->json(['success' => true, 'message' => $msg]);
        }

        // User xác nhận → thực thi action
        $this->clearPendingAction($userId);
        $action = $pending['action'];
        $data   = $pending['data'];

        // Execute trả về array, sau đó lưu history rồi return
        $result = match ($action) {
            'ADD_TRANSACTION'    => $this->executeAddTransaction($data, $userId, $userName),
            'DELETE_TRANSACTION' => $this->executeDeleteTransaction($data, $userId, $userName),
            'CREATE_CATEGORY'    => $this->executeCreateCategory($data, $userId, $userName),
            'UPDATE_WALLET'      => $this->executeUpdateWallet($data, $userId, $userName),
            'UPDATE_TRANSACTION' => $this->executeUpdateTransaction($data, $userId, $userName),
            'DELETE_CATEGORY'    => $this->executeDeleteCategory($data, $userId, $userName),
            'CREATE_WALLET'      => $this->executeCreateWallet($data, $userId, $userName),
            'DELETE_WALLET'      => $this->executeDeleteWallet($data, $userId, $userName),
            default              => ['success' => false, 'message' => 'Hành động không hợp lệ.'],
        };

        $this->saveChatHistory($userId, $userMessage, $result['message']);

        return response()->json($result);
    }

    // HANDLE INTENTS — chỉ hỏi xác nhận, trả về array
    private function handleAddTransaction(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        $missing = [];
        if (empty($data['so_tien']) || $data['so_tien'] <= 0) $missing[] = 'số tiền';
        if (empty($data['loai_giao_dich']))                    $missing[] = 'loại giao dịch (thu hay chi)';
        if (empty($data['category_id']) && empty($data['ten_danh_muc'])) $missing[] = 'danh mục';

        if (!empty($missing)) {
            return [
                'success'    => true,
                'message'    => "Bạn ơi, mình cần thêm thông tin để ghi giao dịch:\n- "
                                . implode("\n- ", $missing)
                                . "\nBạn cung cấp được không?",
                'needs_info' => true,
            ];
        }

        // Resolve category
        if (empty($data['category_id']) && !empty($data['ten_danh_muc'])) {
            $cat = Category::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })->where('ten_danh_muc', 'like', '%' . $data['ten_danh_muc'] . '%')->first();
            $data['category_id']   = $cat?->id;
            $data['category_name'] = $cat?->ten_danh_muc ?? $data['ten_danh_muc'];
        } else {
            $cat = Category::find($data['category_id']);
            $data['category_name'] = $cat?->ten_danh_muc ?? 'Không rõ';
        }

        $data['ngay_giao_dich'] = $data['ngay_giao_dich'] ?? now()->toDateString();
        $loai = $data['loai_giao_dich'] === 'THU' ? 'Thu nhập' : 'Chi tiêu';

        $confirmMsg = "Mình sẽ ghi giao dịch sau:\n"
            . "- Loại: {$loai}\n"
            . "- Số tiền: " . number_format($data['so_tien']) . " VND\n"
            . "- Danh mục: " . ($data['category_name'] ?? 'Không rõ') . "\n"
            . "- Ngày: " . Carbon::parse($data['ngay_giao_dich'])->format('d/m/Y') . "\n"
            . (!empty($data['ghi_chu']) ? "- Ghi chú: {$data['ghi_chu']}\n" : '')
            . "\nXác nhận lưu không {$userName}? (có/không)";

        $this->savePendingAction($userId, 'ADD_TRANSACTION', $data);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    private function handleDeleteTransaction(array $parsed, int $userId, string $userName): array
    {
        $data  = $parsed['data'] ?? [];
        $query = Transaction::where('user_id', $userId);

        if (!empty($data['so_tien']))       $query->where('so_tien', $data['so_tien']);
        if (!empty($data['category_name'])) {
            $cat = Category::where('ten_danh_muc', 'like', '%' . $data['category_name'] . '%')->first();
            if ($cat) $query->where('category_id', $cat->id);
        }
        if (!empty($data['ngay_giao_dich'])) $query->whereDate('ngay_giao_dich', $data['ngay_giao_dich']);

        $transaction = $query->orderByDesc('ngay_giao_dich')->first();

        if (!$transaction) {
            return [
                'success' => true,
                'message' => "Mình không tìm thấy giao dịch phù hợp {$userName}. Bạn có thể mô tả rõ hơn không?",
            ];
        }

        $cat  = $transaction->category?->ten_danh_muc ?? 'Không rõ';
        $loai = $transaction->loai_giao_dich === 'THU' ? 'Thu' : 'Chi';
        $ngay = Carbon::parse($transaction->ngay_giao_dich)->format('d/m/Y');

        $confirmMsg = "Mình tìm thấy giao dịch này:\n"
            . "- {$loai} | " . number_format($transaction->so_tien) . " VND | {$cat} | {$ngay}\n"
            . "\nXác nhận XOÁ giao dịch này không {$userName}? (có/không)";

        $this->savePendingAction($userId, 'DELETE_TRANSACTION', [
            'transaction_id' => $transaction->id,
            'category_id'    => $transaction->category_id,
        ]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    private function handleCreateCategory(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        $missing = [];
        if (empty($data['ten_danh_muc']))  $missing[] = 'tên danh mục';
        if (empty($data['loai_danh_muc'])) $missing[] = 'loại danh mục (thu hay chi)';

        if (!empty($missing)) {
            return [
                'success'    => true,
                'message'    => "Để tạo danh mục mới, mình cần:\n- "
                                . implode("\n- ", $missing)
                                . "\nBạn bổ sung được không?",
                'needs_info' => true,
            ];
        }

        $exists = Category::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        })->where('ten_danh_muc', $data['ten_danh_muc'])->exists();

        if ($exists) {
            return [
                'success' => true,
                'message' => "Danh mục \"{$data['ten_danh_muc']}\" đã tồn tại rồi {$userName}! Bạn muốn dùng danh mục này không?",
            ];
        }

        $loai       = $data['loai_danh_muc'] === 'THU' ? 'Thu nhập' : 'Chi tiêu';
        $bieu_tuong = !empty($data['bieu_tuong']) ? $data['bieu_tuong'] : ($data['loai_danh_muc'] === 'THU' ? '💰' : '💸');

        $confirmMsg = "Mình sẽ tạo danh mục mới:\n"
            . "- Tên: {$data['ten_danh_muc']} {$bieu_tuong}\n"
            . "- Loại: {$loai}\n"
            . (!empty($data['mo_ta']) ? "- Mô tả: {$data['mo_ta']}\n" : '')
            . "\nXác nhận tạo không {$userName}? (có/không)";

        $this->savePendingAction($userId, 'CREATE_CATEGORY', $data);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    private function handleUpdateWallet(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        $missing = [];
        if (empty($data['ten_ngan_sach']) && empty($data['wallet_id'])) $missing[] = 'tên ngân sách';
        if (empty($data['ngan_sach_goc']) || $data['ngan_sach_goc'] <= 0) $missing[] = 'số tiền ngân sách mới';

        if (!empty($missing)) {
            return [
                'success'    => true,
                'message'    => "Để cập nhật ngân sách, mình cần:\n- "
                                . implode("\n- ", $missing)
                                . "\nBạn bổ sung được không?",
                'needs_info' => true,
            ];
        }

        $wallet = null;
        if (!empty($data['wallet_id'])) {
            $wallet = Budgets::where('user_id', $userId)->find($data['wallet_id']);
        } elseif (!empty($data['ten_ngan_sach'])) {
            $wallet = Budgets::where('user_id', $userId)
                ->where('ten_ngan_sach', 'like', '%' . $data['ten_ngan_sach'] . '%')
                ->first();
        }

        if (!$wallet) {
            return [
                'success' => true,
                'message' => "Mình không tìm thấy ngân sách \"{$data['ten_ngan_sach']}\" {$userName}. Bạn kiểm tra lại tên không?",
            ];
        }

        $data['wallet_id'] = $wallet->id;

        $confirmMsg = "Mình sẽ cập nhật ngân sách:\n"
            . "- Tên: {$wallet->ten_ngan_sach}\n"
            . "- Ngân sách hiện tại: " . number_format($wallet->ngan_sach_goc) . " VND\n"
            . "- Ngân sách mới: " . number_format($data['ngan_sach_goc']) . " VND\n"
            . "\nXác nhận cập nhật không {$userName}? (có/không)";

        $this->savePendingAction($userId, 'UPDATE_WALLET', $data);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    private function handleChat(array $parsed): array
    {
        $message = $this->formatResponse($parsed['message'] ?? 'Xin lỗi, mình không hiểu. Bạn thử lại nhé!');
        return ['success' => true, 'message' => $message];
    }

    // EXECUTE ACTIONS — chỉ trả về array, KHÔNG lưu history
    private function executeAddTransaction(array $data, int $userId, string $userName): array
    {
        try {
            $transaction = Transaction::create([
                'user_id'        => $userId,
                'so_tien'        => $data['so_tien'],
                'loai_giao_dich' => $data['loai_giao_dich'],
                'category_id'    => $data['category_id'] ?? null,
                'ngay_giao_dich' => $data['ngay_giao_dich'] ?? now()->toDateString(),
                'ghi_chu'        => $data['ghi_chu'] ?? null,
            ]);

            if (!empty($data['category_id'])) {
                $wallet = Budgets::where('user_id', $userId)
                    ->where('category_id', $data['category_id'])
                    ->where('trang_thai', true)
                    ->first();
                if ($wallet) {
                    $wallet->recalculateBalance();
                }
            }

            $loai = $data['loai_giao_dich'] === 'THU' ? 'thu nhập' : 'chi tiêu';
            $msg  = "Đã ghi giao dịch {$loai} " . number_format($data['so_tien']) . " VND thành công!\n"
                  . "- Danh mục: " . ($data['category_name'] ?? 'Không rõ') . "\n"
                  . "- Ngày: " . Carbon::parse($data['ngay_giao_dich'])->format('d/m/Y') . "\n"
                  . "Bạn cần mình giúp gì thêm không {$userName}?";

            return [
                'success'     => true,
                'message'     => $msg,
                'action_done' => 'ADD_TRANSACTION',
                'data'        => $transaction->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('executeAddTransaction error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Lỗi khi lưu giao dịch. Vui lòng thử lại.'];
        }
    }

    private function executeDeleteTransaction(array $data, int $userId, string $userName): array
    {
        try {
            $transaction = Transaction::where('user_id', $userId)->findOrFail($data['transaction_id']);
            $categoryId  = $data['category_id'] ?? $transaction->category_id; // FIX #4
            $info        = number_format($transaction->so_tien) . " VND - "
                         . Carbon::parse($transaction->ngay_giao_dich)->format('d/m/Y');

            $transaction->delete();

            if ($categoryId) {
                $wallet = Budgets::where('user_id', $userId)
                    ->where('category_id', $categoryId)
                    ->where('trang_thai', true)
                    ->first();
                if ($wallet) {
                    $wallet->recalculateBalance();
                }
            }

            return [
                'success'     => true,
                'message'     => "Đã xoá giao dịch {$info} thành công {$userName}!",
                'action_done' => 'DELETE_TRANSACTION',
            ];

        } catch (\Exception $e) {
            Log::error('executeDeleteTransaction error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể xoá giao dịch. Vui lòng thử lại.'];
        }
    }

    private function executeCreateCategory(array $data, int $userId, string $userName): array
    {
        try {
            $bieu_tuong = !empty($data['bieu_tuong'])
                ? $data['bieu_tuong']
                : ($data['loai_danh_muc'] === 'THU' ? '💰' : '💸');

            $category = Category::create([
                'user_id'       => $userId,
                'ten_danh_muc'  => $data['ten_danh_muc'],
                'loai_danh_muc' => $data['loai_danh_muc'],
                'bieu_tuong'    => $bieu_tuong,
                'mo_ta'         => $data['mo_ta'] ?? null,
                'trang_thai'    => true, // FIX: active ngay
            ]);

            $loai = $data['loai_danh_muc'] === 'THU' ? 'thu nhập' : 'chi tiêu';

            return [
                'success'     => true,
                'message'     => "Đã tạo danh mục \"{$data['ten_danh_muc']}\" {$bieu_tuong} ({$loai}) thành công {$userName}! "
                               . "Bạn có thể dùng ngay khi ghi giao dịch rồi nhé.",
                'action_done' => 'CREATE_CATEGORY',
                'data'        => $category->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('executeCreateCategory error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể tạo danh mục. Vui lòng thử lại.'];
        }
    }

    private function executeUpdateWallet(array $data, int $userId, string $userName): array
    {
        try {
            $wallet    = Budgets::where('user_id', $userId)->findOrFail($data['wallet_id']);
            $oldBudget = $wallet->ngan_sach_goc;

            $wallet->update(['ngan_sach_goc' => $data['ngan_sach_goc']]);
            $wallet->recalculateBalance(); // FIX: sync so_du sau khi đổi ngân sách gốc

            return [
                'success'     => true,
                'message'     => "Đã cập nhật ngân sách \"{$wallet->ten_ngan_sach}\" thành công {$userName}!\n"
                               . "- Ngân sách cũ: " . number_format($oldBudget) . " VND\n"
                               . "- Ngân sách mới: " . number_format($data['ngan_sach_goc']) . " VND\n"
                               . "- Số dư hiện tại: " . number_format($wallet->fresh()->so_du) . " VND",
                'action_done' => 'UPDATE_WALLET',
                'data'        => $wallet->fresh()->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('executeUpdateWallet error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể cập nhật ngân sách. Vui lòng thử lại.'];
        }
    }

    // PENDING ACTION
    private function savePendingAction(int $userId, string $action, array $data): void
    {
        DB::table('ai_pending_actions')->updateOrInsert(
            ['user_id' => $userId],
            ['action' => $action, 'data' => json_encode($data, JSON_UNESCAPED_UNICODE), 'created_at' => now(), 'updated_at' => now()]
        );
    }

    private function getPendingAction(int $userId): ?array
    {
        $row = DB::table('ai_pending_actions')
            ->where('user_id', $userId)
            ->where('updated_at', '>=', now()->subMinutes(10))
            ->first();

        if (!$row) return null;

        return ['action' => $row->action, 'data' => json_decode($row->data, true)];
    }

    private function clearPendingAction(int $userId): void
    {
        DB::table('ai_pending_actions')->where('user_id', $userId)->delete();
    }

    // HELPERS
    private function saveChatHistory(int $userId, string $userMessage, string $aiResponse): void
    {
        DB::table('ai_chat_history')->insert([
            'user_id'      => $userId,
            'user_message' => $userMessage,
            'ai_response'  => $aiResponse,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function parseGeminiResponse(string $raw): array
    {
        if (preg_match('/```json\s*(.*?)\s*```/s', $raw, $m)) {
            $raw = $m[1];
        } elseif (preg_match('/(\{.*\})/s', $raw, $m)) {
            $raw = $m[1];
        }

        $parsed = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['intent' => self::INTENT_CHAT, 'message' => $raw];
        }

        return $parsed;
    }

    private function getChatHistory(int $userId): array
    {
        $rows = DB::table('ai_chat_history')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $history = [];
        foreach ($rows as $item) {
            $history[] = ['role' => 'user',  'parts' => [['text' => $item->user_message]]];
            $history[] = ['role' => 'model', 'parts' => [['text' => $item->ai_response]]];
        }
        return $history;
    }

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
    3. DELETE_TRANSACTION — user muốn xoá giao dịch
    Dấu hiệu: "xoá giao dịch", "huỷ khoản", "xoá cái vừa thêm"
    4. CREATE_CATEGORY — user muốn tạo danh mục mới
    Dấu hiệu: "tạo danh mục", "thêm danh mục", "tôi muốn có mục mới"
    5. UPDATE_WALLET — user muốn sửa ngân sách
    Dấu hiệu: "đổi ngân sách", "cập nhật hạn mức", "sửa budget"
    6. UPDATE_TRANSACTION — user muốn sửa giao dịch
    Dấu hiệu: "sửa giao dịch", "đổi số tiền", "cập nhật khoản", "sửa khoản vừa thêm"
    7. DELETE_CATEGORY — user muốn xóa danh mục
    Dấu hiệu: "xóa danh mục", "bỏ danh mục", "xóa mục"
    Lưu ý: LUÔN trả về intent này khi user muốn xóa danh mục, KHÔNG tự phán xét
    8. CREATE_WALLET — user muốn tạo ngân sách mới
    Dấu hiệu: "tạo ngân sách", "thêm ví", "tạo budget mới", "tạo quỹ"
    9. DELETE_WALLET — user muốn xóa ngân sách
    Dấu hiệu: "xóa ngân sách", "bỏ ví", "xóa budget", "xóa quỹ"

    === FORMAT JSON ===

    CHAT:
    {"intent":"CHAT","message":"Nội dung trả lời"}

    ADD_TRANSACTION:
    {"intent":"ADD_TRANSACTION","data":{"so_tien":150000,"loai_giao_dich":"CHI","category_id":3,"ten_danh_muc":"Ăn uống","ngay_giao_dich":"{$today}","ghi_chu":"ăn trưa"}}
    Lưu ý: loai_giao_dich là "THU" hoặc "CHI", so_tien là số nguyên, category_id lấy từ danh sách bên dưới

    DELETE_TRANSACTION:
    {"intent":"DELETE_TRANSACTION","data":{"so_tien":150000,"category_name":"Ăn uống","ngay_giao_dich":"{$today}"}}
    Lưu ý: LUÔN trả về intent này khi user muốn xóa giao dịch, để hệ thống backend tự tìm và xử lý

    CREATE_CATEGORY:
    {"intent":"CREATE_CATEGORY","data":{"ten_danh_muc":"Thú cưng","loai_danh_muc":"CHI","bieu_tuong":"🐾","mo_ta":"Chi phí cho thú cưng"}}
    Lưu ý: loai_danh_muc là "THU" hoặc "CHI", bieu_tuong là emoji phù hợp

    UPDATE_WALLET:
    {"intent":"UPDATE_WALLET","data":{"ten_ngan_sach":"Ăn uống","ngan_sach_goc":3000000}}

    UPDATE_TRANSACTION:
    {"intent":"UPDATE_TRANSACTION","data":{"so_tien_cu":150000,"category_name":"Ăn uống","ngay_giao_dich":"{$today}","so_tien_moi":200000,"ghi_chu_moi":"ăn tối","category_name_moi":""}}
    Lưu ý: so_tien_cu + category_name + ngay_giao_dich để tìm giao dịch, các trường _moi là giá trị muốn cập nhật, để trống nếu không sửa

    DELETE_CATEGORY:
    {"intent":"DELETE_CATEGORY","data":{"ten_danh_muc":"Thú cưng"}}
    Lưu ý: LUÔN trả về intent này, để hệ thống backend tự kiểm tra và xử lý

    CREATE_WALLET:
    {"intent":"CREATE_WALLET","data":{"ten_ngan_sach":"Du lịch","ngan_sach_goc":5000000,"ten_danh_muc":"Du lịch","mo_ta":"Quỹ đi chơi"}}
    Lưu ý: ten_danh_muc để liên kết với danh mục có sẵn (không bắt buộc)

    DELETE_WALLET:
    {"intent":"DELETE_WALLET","data":{"ten_ngan_sach":"Du lịch"}}
    Lưu ý: LUÔN trả về intent này khi user muốn xóa ngân sách, để hệ thống backend tự tìm và xử lý

    === DANH MỤC HIỆN CÓ ===
    {$catList}

    === DỮ LIỆU TÀI CHÍNH ===
    - Số dư: {$balance} VND
    - Thu tháng này: {$d['monthIncome']} VND
    - Chi tháng này: {$d['monthExpense']} VND
    - Tỷ lệ tiết kiệm: {$savingRate}%

    === QUY TẮC ===
    - intent CHAT: trả lời ngắn gọn, dùng dấu (-) để liệt kê, không dùng markdown (**, ##)
    - Không liên quan tài chính: {"intent":"CHAT","message":"Mình chỉ hỗ trợ tư vấn tài chính thôi nhé {$userName}!"}
    - Ngày hôm nay: {$today}
    - QUAN TRỌNG: Với tất cả intent DELETE_* và UPDATE_*: LUÔN trả về đúng intent, KHÔNG tự phán xét hay từ chối, để hệ thống backend tự kiểm tra và xử lý
    PROMPT;
    }

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
        $wallets = Budgets::where('user_id', $userId)->get()->map(function ($w) use ($userId) {
            $w->da_chi = $w->ngan_sach_goc - $w->so_du;
            $w->spent_percentage = $w->spent_percentage; // dùng accessor từ model
            return $w;
        });
        $recentTransactions = Transaction::where('user_id', $userId)->with('category')
            ->orderByDesc('ngay_giao_dich')->limit(10)->get();

        return compact(
            'totalIncome', 'totalExpense', 'monthIncome', 'monthExpense',
            'lastMonthExpense', 'categoryExpenses', 'wallets', 'recentTransactions'
        );
    }

    private function formatResponse(string $text): string
    {
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/\*(.*?)\*/',     '$1', $text);
        $text = preg_replace('/#{1,6}\s/',      '',   $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
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

    public function analyze(Request $request)
    {
        $userId = Auth::id();
        $period = $request->input('period', 30);
        try {
            $data         = $this->getUserFinancialData($userId);
            $systemPrompt = $this->buildIntentSystemPrompt($data, Auth::user()->name, collect());
            $response     = $this->gemini->generateContent([
                'model'    => 'models/gemini-2.5-flash',
                'contents' => [
                    ['role' => 'user',  'parts' => [['text' => $systemPrompt]]],
                    ['role' => 'model', 'parts' => [['text' => 'Đã nắm dữ liệu.']]],
                    ['role' => 'user',  'parts' => [['text' => "Phân tích chi tiêu {$period} ngày qua và đưa 3 lời khuyên cụ thể có số liệu. Trả về JSON: {\"intent\":\"CHAT\",\"message\":\"...\"}."]]],
                ],
                'generationConfig' => ['maxOutputTokens' => 800, 'temperature' => 0.7],
            ]);
            $raw    = $response['candidates'][0]['content']['parts'][0]['text'] ?? '{"intent":"CHAT","message":"Không có dữ liệu."}';
            $parsed = $this->parseGeminiResponse($raw);
            return response()->json(['success' => true, 'analysis' => $this->formatResponse($parsed['message'] ?? $raw)]);
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
        return [
            'income'       => $income,
            'expense'      => $expense,
            'saved'        => $income - $expense,
            'saving_rate'  => $rate,
            'status'       => $rate >= 20 ? 'good' : ($rate >= 10 ? 'fair' : 'poor'),
        ];
    }

    // Cập nhật giao dịch 
    private function handleUpdateTransaction(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        // Tìm giao dịch cần sửa
        $query = Transaction::where('user_id', $userId);
        if (!empty($data['so_tien_cu']))      $query->where('so_tien', $data['so_tien_cu']);
        if (!empty($data['category_name'])) {
            $cat = Category::where('ten_danh_muc', 'like', '%' . $data['category_name'] . '%')->first();
            if ($cat) $query->where('category_id', $cat->id);
        }
        if (!empty($data['ngay_giao_dich'])) $query->whereDate('ngay_giao_dich', $data['ngay_giao_dich']);

        $transaction = $query->orderByDesc('ngay_giao_dich')->first();

        if (!$transaction) {
            return [
                'success' => true,
                'message' => "Mình không tìm thấy giao dịch phù hợp {$userName}. Bạn mô tả rõ hơn được không?",
            ];
        }

        // Kiểm tra có thứ gì để sửa không
        $missing = [];
        if (empty($data['so_tien_moi']) && empty($data['ghi_chu_moi']) && empty($data['category_name_moi'])) {
            $missing[] = 'thông tin muốn sửa (số tiền mới / ghi chú mới / danh mục mới)';
        }
        if (!empty($missing)) {
            return [
                'success'    => true,
                'message'    => "Bạn muốn sửa giao dịch này thành gì {$userName}?\n- " . implode("\n- ", $missing),
                'needs_info' => true,
            ];
        }

        $cat  = $transaction->category?->ten_danh_muc ?? 'Không rõ';
        $loai = $transaction->loai_giao_dich === 'THU' ? 'Thu' : 'Chi';
        $ngay = Carbon::parse($transaction->ngay_giao_dich)->format('d/m/Y');

        $confirmMsg = "Mình sẽ sửa giao dịch:\n"
            . "- Hiện tại: {$loai} | " . number_format($transaction->so_tien) . " VND | {$cat} | {$ngay}\n"
            . "- Thành: "
            . (!empty($data['so_tien_moi'])      ? number_format($data['so_tien_moi']) . " VND " : number_format($transaction->so_tien) . " VND ")
            . (!empty($data['category_name_moi']) ? "| {$data['category_name_moi']} "            : "| {$cat} ")
            . (!empty($data['ghi_chu_moi'])       ? "| {$data['ghi_chu_moi']}"                   : '')
            . "\n\nXác nhận sửa không {$userName}? (có/không)";

        $this->savePendingAction($userId, 'UPDATE_TRANSACTION', [
            'transaction_id'   => $transaction->id,
            'so_tien_moi'      => $data['so_tien_moi']      ?? null,
            'ghi_chu_moi'      => $data['ghi_chu_moi']      ?? null,
            'category_name_moi'=> $data['category_name_moi'] ?? null,
        ]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    private function executeUpdateTransaction(array $data, int $userId, string $userName): array
    {
        try {
            $transaction = Transaction::where('user_id', $userId)->findOrFail($data['transaction_id']);

            $updateFields = [];
            if (!empty($data['so_tien_moi']))       $updateFields['so_tien'] = $data['so_tien_moi'];
            if (!empty($data['ghi_chu_moi']))        $updateFields['ghi_chu'] = $data['ghi_chu_moi'];
            if (!empty($data['category_name_moi'])) {
                $cat = Category::where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)->orWhereNull('user_id');
                })->where('ten_danh_muc', 'like', '%' . $data['category_name_moi'] . '%')->first();
                if ($cat) $updateFields['category_id'] = $cat->id;
            }

            $transaction->update($updateFields);

            // Recalculate wallet nếu có
            $wallet = Budgets::where('user_id', $userId)
                ->where('category_id', $transaction->category_id)
                ->where('trang_thai', true)->first();
            if ($wallet) $wallet->recalculateBalance();

            return [
                'success'     => true,
                'message'     => "Đã sửa giao dịch thành công {$userName}! Bạn cần mình giúp gì thêm không?",
                'action_done' => 'UPDATE_TRANSACTION',
                'data'        => $transaction->fresh()->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('executeUpdateTransaction error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể sửa giao dịch. Vui lòng thử lại.'];
        }
    }

    // Xóa danh mục hệ thống 
    private function handleDeleteCategory(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        if (empty($data['ten_danh_muc']) && empty($data['category_id'])) {
            return [
                'success'    => true,
                'message'    => "Bạn muốn xóa danh mục nào {$userName}? Cho mình biết tên danh mục nhé.",
                'needs_info' => true,
            ];
        }

        // Tìm category — chỉ cho xóa category do user tạo (user_id không null)
        $query = Category::where('user_id', $userId);
        if (!empty($data['category_id']))   $query->where('id', $data['category_id']);
        if (!empty($data['ten_danh_muc']))  $query->where('ten_danh_muc', 'like', '%' . $data['ten_danh_muc'] . '%');

        $category = $query->first();

        if (!$category) {
            return [
                'success' => true,
                'message' => "Mình không tìm thấy danh mục \"{$data['ten_danh_muc']}\" do bạn tạo {$userName}. "
                        . "Lưu ý: danh mục mặc định của hệ thống không thể xóa nhé.",
            ];
        }

        // Kiểm tra có giao dịch đang dùng không
        $txCount = Transaction::where('user_id', $userId)->where('category_id', $category->id)->count();
        $warning = $txCount > 0
            ? "\n⚠️ Danh mục này đang có {$txCount} giao dịch liên quan. Giao dịch sẽ mất liên kết danh mục sau khi xóa."
            : '';

        $loai = $category->loai_danh_muc === 'THU' ? 'Thu nhập' : 'Chi tiêu';

        $confirmMsg = "Mình sẽ xóa danh mục:\n"
            . "- Tên: {$category->ten_danh_muc} {$category->bieu_tuong}\n"
            . "- Loại: {$loai}\n"
            . $warning
            . "\n\nXác nhận XÓA không {$userName}? (có/không)";

        $this->savePendingAction($userId, 'DELETE_CATEGORY', ['category_id' => $category->id]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    private function executeDeleteCategory(array $data, int $userId, string $userName): array
    {
        try {
            $category = Category::where('user_id', $userId)->findOrFail($data['category_id']);
            $name     = $category->ten_danh_muc;
            $icon     = $category->bieu_tuong;

            // Null out category_id trên các giao dịch liên quan
            Transaction::where('user_id', $userId)
                ->where('category_id', $category->id)
                ->update(['category_id' => null]);

            $category->delete();

            return [
                'success'     => true,
                'message'     => "Đã xóa danh mục \"{$name}\" {$icon} thành công {$userName}!",
                'action_done' => 'DELETE_CATEGORY',
            ];
        } catch (\Exception $e) {
            Log::error('executeDeleteCategory error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể xóa danh mục. Vui lòng thử lại.'];
        }
    }

    // Tạo ngân sách 
    private function handleCreateWallet(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        $missing = [];
        if (empty($data['ten_ngan_sach']))                         $missing[] = 'tên ngân sách';
        if (empty($data['ngan_sach_goc']) || $data['ngan_sach_goc'] <= 0) $missing[] = 'số tiền ngân sách';

        if (!empty($missing)) {
            return [
                'success'    => true,
                'message'    => "Để tạo ngân sách mới, mình cần:\n- " . implode("\n- ", $missing) . "\nBạn bổ sung được không?",
                'needs_info' => true,
            ];
        }

        // Kiểm tra trùng tên
        $exists = Budgets::where('user_id', $userId)
            ->where('ten_ngan_sach', $data['ten_ngan_sach'])->exists();

        if ($exists) {
            return [
                'success' => true,
                'message' => "Ngân sách \"{$data['ten_ngan_sach']}\" đã tồn tại rồi {$userName}! Bạn muốn cập nhật số tiền của nó không?",
            ];
        }

        // Resolve category nếu có
        $categoryName = 'Không liên kết';
        if (!empty($data['ten_danh_muc'])) {
            $cat = Category::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })->where('ten_danh_muc', 'like', '%' . $data['ten_danh_muc'] . '%')->first();
            if ($cat) {
                $data['category_id'] = $cat->id;
                $categoryName        = $cat->ten_danh_muc;
            }
        }

        $confirmMsg = "Mình sẽ tạo ngân sách mới:\n"
            . "- Tên: {$data['ten_ngan_sach']}\n"
            . "- Hạn mức: " . number_format($data['ngan_sach_goc']) . " VND\n"
            . "- Danh mục liên kết: {$categoryName}\n"
            . (!empty($data['mo_ta']) ? "- Mô tả: {$data['mo_ta']}\n" : '')
            . "\nXác nhận tạo không {$userName}? (có/không)";

        $this->savePendingAction($userId, 'CREATE_WALLET', $data);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    private function executeCreateWallet(array $data, int $userId, string $userName): array
    {
        try {
            $wallet = Budgets::create([
                'user_id'       => $userId,
                'ten_ngan_sach' => $data['ten_ngan_sach'],
                'ngan_sach_goc' => $data['ngan_sach_goc'],
                'so_du'         => $data['ngan_sach_goc'], // ban đầu bằng ngân sách gốc
                'category_id'   => $data['category_id'] ?? null,
                'mo_ta'         => $data['mo_ta']        ?? null,
                'trang_thai'    => true,
            ]);

            return [
                'success'     => true,
                'message'     => "Đã tạo ngân sách \"{$wallet->ten_ngan_sach}\" "
                            . number_format($wallet->ngan_sach_goc) . " VND thành công {$userName}! "
                            . "Bạn có thể dùng ngay rồi nhé.",
                'action_done' => 'CREATE_WALLET',
                'data'        => $wallet->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('executeCreateWallet error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể tạo ngân sách. Vui lòng thử lại.'];
        }
    }

    // Xóa ngân sách 
    private function handleDeleteWallet(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        if (empty($data['ten_ngan_sach']) && empty($data['wallet_id'])) {
            return [
                'success'    => true,
                'message'    => "Bạn muốn xóa ngân sách nào {$userName}? Cho mình biết tên nhé.",
                'needs_info' => true,
            ];
        }

        $wallet = null;
        if (!empty($data['wallet_id'])) {
            $wallet = Budgets::where('user_id', $userId)->find($data['wallet_id']);
        } elseif (!empty($data['ten_ngan_sach'])) {
            $wallet = Budgets::where('user_id', $userId)
                ->where('ten_ngan_sach', 'like', '%' . $data['ten_ngan_sach'] . '%')->first();
        }

        if (!$wallet) {
            return [
                'success' => true,
                'message' => "Mình không tìm thấy ngân sách \"{$data['ten_ngan_sach']}\" {$userName}. Bạn kiểm tra lại tên không?",
            ];
        }

        $confirmMsg = "Mình sẽ xóa ngân sách:\n"
            . "- Tên: {$wallet->ten_ngan_sach}\n"
            . "- Hạn mức: " . number_format($wallet->ngan_sach_goc) . " VND\n"
            . "- Số dư hiện tại: " . number_format($wallet->so_du) . " VND\n"
            . "\n⚠️ Hành động này không thể hoàn tác!\n"
            . "\nXác nhận XÓA không {$userName}? (có/không)";

        $this->savePendingAction($userId, 'DELETE_WALLET', ['wallet_id' => $wallet->id]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    private function executeDeleteWallet(array $data, int $userId, string $userName): array
    {
        try {
            $wallet = Budgets::where('user_id', $userId)->findOrFail($data['wallet_id']);
            $name   = $wallet->ten_ngan_sach;

            $wallet->delete();

            return [
                'success'     => true,
                'message'     => "Đã xóa ngân sách \"{$name}\" thành công {$userName}!",
                'action_done' => 'DELETE_WALLET',
            ];
        } catch (\Exception $e) {
            Log::error('executeDeleteWallet error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể xóa ngân sách. Vui lòng thử lại.'];
        }
    }
}