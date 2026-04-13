<?php

namespace App\Http\Controllers\AI;

use App\Services\AIService;
use App\Models\Budgets;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

/**
 * Handles all AI intents related to Wallets (Budgets).
 * Inject AIService (không inject AIAssistantController) để tránh circular dependency.
 */
class WalletController
{
    public function __construct(private AIService $ai) {}

    public function handleCreate(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        $missing = [];
        if (empty($data['ten_ngan_sach']))                                $missing[] = 'tên ngân sách';
        if (empty($data['ngan_sach_goc']) || $data['ngan_sach_goc'] <= 0) $missing[] = 'số tiền ngân sách';

        if (!empty($missing)) {
            return [
                'success'    => true,
                'message'    => "Để tạo ngân sách mới, mình cần:\n- "
                    . implode("\n- ", $missing) . "\nBạn bổ sung được không?",
                'needs_info' => true,
            ];
        }

        $exists = Budgets::where('user_id', $userId)
            ->where('ten_ngan_sach', $data['ten_ngan_sach'])->exists();

        if ($exists) {
            return [
                'success' => true,
                'message' => "Ngân sách \"{$data['ten_ngan_sach']}\" đã tồn tại rồi {$userName}! Bạn muốn cập nhật số tiền của nó không?",
            ];
        }

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

        $this->ai->savePendingAction($userId, 'CREATE_WALLET', $data);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function handleUpdate(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        $missing = [];
        if (empty($data['ten_ngan_sach']) && empty($data['wallet_id'])) $missing[] = 'tên ngân sách';
        if (empty($data['ngan_sach_goc']) || $data['ngan_sach_goc'] <= 0) $missing[] = 'số tiền ngân sách mới';

        if (!empty($missing)) {
            return [
                'success'    => true,
                'message'    => "Để cập nhật ngân sách, mình cần:\n- "
                    . implode("\n- ", $missing) . "\nBạn bổ sung được không?",
                'needs_info' => true,
            ];
        }

        $wallet = $this->findWallet($userId, $data);

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

        $this->ai->savePendingAction($userId, 'UPDATE_WALLET', $data);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function handleDelete(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        if (empty($data['ten_ngan_sach']) && empty($data['wallet_id'])) {
            return [
                'success'    => true,
                'message'    => "Bạn muốn xóa ngân sách nào {$userName}? Cho mình biết tên nhé.",
                'needs_info' => true,
            ];
        }

        $wallet = $this->findWallet($userId, $data);

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

        $this->ai->savePendingAction($userId, 'DELETE_WALLET', ['wallet_id' => $wallet->id]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    // ── EXECUTE (after confirmation) ──────────────────────────────────────────

    public function executeCreate(array $data, int $userId, string $userName): array
    {
        try {
            $wallet = Budgets::create([
                'user_id'       => $userId,
                'ten_ngan_sach' => $data['ten_ngan_sach'],
                'ngan_sach_goc' => $data['ngan_sach_goc'],
                'so_du'         => $data['ngan_sach_goc'],
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
            Log::error('AI executeCreateWallet', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể tạo ngân sách. Vui lòng thử lại.'];
        }
    }

    public function executeUpdate(array $data, int $userId, string $userName): array
    {
        try {
            $wallet    = Budgets::where('user_id', $userId)->findOrFail($data['wallet_id']);
            $oldBudget = $wallet->ngan_sach_goc;

            $wallet->update(['ngan_sach_goc' => $data['ngan_sach_goc']]);
            $wallet->recalculateBalance();

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
            Log::error('AI executeUpdateWallet', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể cập nhật ngân sách. Vui lòng thử lại.'];
        }
    }

    public function executeDelete(array $data, int $userId, string $userName): array
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
            Log::error('AI executeDeleteWallet', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể xóa ngân sách. Vui lòng thử lại.'];
        }
    }

    private function findWallet(int $userId, array $data): ?Budgets
    {
        if (!empty($data['wallet_id'])) {
            return Budgets::where('user_id', $userId)->find($data['wallet_id']);
        }
        if (!empty($data['ten_ngan_sach'])) {
            return Budgets::where('user_id', $userId)
                ->where('ten_ngan_sach', 'like', '%' . $data['ten_ngan_sach'] . '%')
                ->first();
        }
        return null;
    }
}