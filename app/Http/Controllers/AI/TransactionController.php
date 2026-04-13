<?php

namespace App\Http\Controllers\AI;

use App\Services\AIService;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budgets;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Handles all AI intents related to Transactions.
 * Inject AIService (không inject AIAssistantController) để tránh circular dependency.
 */
class TransactionController
{
    public function __construct(private AIService $ai) {}

    public function handleAdd(array $parsed, int $userId, string $userName): array
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
                    . implode("\n- ", $missing) . "\nBạn cung cấp được không?",
                'needs_info' => true,
            ];
        }

        // Resolve category theo tên
        if (empty($data['category_id']) && !empty($data['ten_danh_muc'])) {
            $cat = Category::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })->where('ten_danh_muc', 'like', '%' . $data['ten_danh_muc'] . '%')->first();

            // Danh mục không tồn tại → gợi ý tạo mới, không tiếp tục
            if (!$cat) {
                return [
                    'success' => true,
                    'message' => "Mình không tìm thấy danh mục \"{$data['ten_danh_muc']}\" {$userName}.\n"
                        . "Bạn có muốn tạo danh mục này không? Nếu có, hãy nhắn:\n"
                        . "\"Tạo danh mục {$data['ten_danh_muc']}\"\n"
                        . "Sau đó mình sẽ ghi giao dịch vào danh mục đó cho bạn nhé!",
                ];
            }

            $data['category_id']   = $cat->id;
            $data['category_name'] = $cat->ten_danh_muc;

        } elseif (!empty($data['category_id'])) {
            $cat = Category::find($data['category_id']);

            // category_id Gemini bịa ra không tồn tại → tìm lại theo tên nếu có
            if (!$cat && !empty($data['ten_danh_muc'])) {
                $cat = Category::where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)->orWhereNull('user_id');
                })->where('ten_danh_muc', 'like', '%' . $data['ten_danh_muc'] . '%')->first();
            }

            if (!$cat) {
                return [
                    'success' => true,
                    'message' => "Mình không tìm thấy danh mục phù hợp {$userName}.\n"
                        . "Bạn cho mình biết tên danh mục muốn dùng để ghi giao dịch nhé!",
                ];
            }

            $data['category_id']   = $cat->id;
            $data['category_name'] = $cat->ten_danh_muc;
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

        $this->ai->savePendingAction($userId, 'ADD_TRANSACTION', $data);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function handleUpdate(array $parsed, int $userId, string $userName): array
    {
        $data  = $parsed['data'] ?? [];

        $query = Transaction::where('user_id', $userId);
        if (!empty($data['so_tien_cu']))     $query->where('so_tien', $data['so_tien_cu']);
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

        if (empty($data['so_tien_moi']) && empty($data['ghi_chu_moi']) && empty($data['category_name_moi'])) {
            return [
                'success'    => true,
                'message'    => "Bạn muốn sửa giao dịch này thành gì {$userName}?\n- thông tin muốn sửa (số tiền mới / ghi chú mới / danh mục mới)",
                'needs_info' => true,
            ];
        }

        $cat  = $transaction->category?->ten_danh_muc ?? 'Không rõ';
        $loai = $transaction->loai_giao_dich === 'THU' ? 'Thu' : 'Chi';
        $ngay = Carbon::parse($transaction->ngay_giao_dich)->format('d/m/Y');

        $confirmMsg = "Mình sẽ sửa giao dịch:\n"
            . "- Hiện tại: {$loai} | " . number_format($transaction->so_tien) . " VND | {$cat} | {$ngay}\n"
            . "- Thành: "
            . (!empty($data['so_tien_moi'])       ? number_format($data['so_tien_moi']) . " VND "  : number_format($transaction->so_tien) . " VND ")
            . (!empty($data['category_name_moi'])  ? "| {$data['category_name_moi']} "             : "| {$cat} ")
            . (!empty($data['ghi_chu_moi'])         ? "| {$data['ghi_chu_moi']}"                   : '')
            . "\n\nXác nhận sửa không {$userName}? (có/không)";

        $this->ai->savePendingAction($userId, 'UPDATE_TRANSACTION', [
            'transaction_id'    => $transaction->id,
            'so_tien_moi'       => $data['so_tien_moi']       ?? null,
            'ghi_chu_moi'       => $data['ghi_chu_moi']       ?? null,
            'category_name_moi' => $data['category_name_moi'] ?? null,
        ]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function handleDelete(array $parsed, int $userId, string $userName): array
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

        $this->ai->savePendingAction($userId, 'DELETE_TRANSACTION', [
            'transaction_id' => $transaction->id,
            'category_id'    => $transaction->category_id,
        ]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function executeAdd(array $data, int $userId, string $userName): array
    {
        try {
            // Bảo vệ lần cuối: nếu category_id vẫn null thì không crash
            if (empty($data['category_id'])) {
                return [
                    'success' => false,
                    'message' => "Không thể ghi giao dịch vì thiếu danh mục {$userName}. Bạn thử lại nhé!",
                ];
            }

            $transaction = Transaction::create([
                'user_id'        => $userId,
                'so_tien'        => $data['so_tien'],
                'loai_giao_dich' => $data['loai_giao_dich'],
                'category_id'    => $data['category_id'],
                'ngay_giao_dich' => $data['ngay_giao_dich'] ?? now()->toDateString(),
                'ghi_chu'        => $data['ghi_chu'] ?? null,
            ]);

            $this->recalcWallet($userId, $data['category_id']);

            $loai = $data['loai_giao_dich'] === 'THU' ? 'thu nhập' : 'chi tiêu';

            return [
                'success'     => true,
                'message'     => "Đã ghi giao dịch {$loai} " . number_format($data['so_tien']) . " VND thành công!\n"
                    . "- Danh mục: " . ($data['category_name'] ?? 'Không rõ') . "\n"
                    . "- Ngày: " . Carbon::parse($data['ngay_giao_dich'])->format('d/m/Y') . "\n"
                    . "Bạn cần mình giúp gì thêm không {$userName}?",
                'action_done' => 'ADD_TRANSACTION',
                'data'        => $transaction->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('AI executeAddTransaction', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Lỗi khi lưu giao dịch. Vui lòng thử lại.'];
        }
    }

    public function executeUpdate(array $data, int $userId, string $userName): array
    {
        try {
            $transaction  = Transaction::where('user_id', $userId)->findOrFail($data['transaction_id']);
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
            $this->recalcWallet($userId, $transaction->category_id);

            return [
                'success'     => true,
                'message'     => "Đã sửa giao dịch thành công {$userName}! Bạn cần mình giúp gì thêm không?",
                'action_done' => 'UPDATE_TRANSACTION',
                'data'        => $transaction->fresh()->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('AI executeUpdateTransaction', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể sửa giao dịch. Vui lòng thử lại.'];
        }
    }

    public function executeDelete(array $data, int $userId, string $userName): array
    {
        try {
            $transaction = Transaction::where('user_id', $userId)->findOrFail($data['transaction_id']);
            $categoryId  = $data['category_id'] ?? $transaction->category_id;
            $info        = number_format($transaction->so_tien) . " VND - "
                . Carbon::parse($transaction->ngay_giao_dich)->format('d/m/Y');

            $transaction->delete();
            $this->recalcWallet($userId, $categoryId);

            return [
                'success'     => true,
                'message'     => "Đã xoá giao dịch {$info} thành công {$userName}!",
                'action_done' => 'DELETE_TRANSACTION',
            ];

        } catch (\Exception $e) {
            Log::error('AI executeDeleteTransaction', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể xoá giao dịch. Vui lòng thử lại.'];
        }
    }

    private function recalcWallet(int $userId, ?int $categoryId): void
    {
        if (!$categoryId) return;
        $wallet = Budgets::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('trang_thai', true)
            ->first();
        $wallet?->recalculateBalance();
    }
}