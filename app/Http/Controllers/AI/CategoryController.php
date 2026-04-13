<?php

namespace App\Http\Controllers\AI;

use App\Services\AIService;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

/**
 * Handles all AI intents related to Categories.
 * Hỗ trợ đầy đủ danh mục cha - con (danh_muc_cha_id).
 */
class CategoryController
{
    public function __construct(private AIService $ai) {}

    public function handleCreate(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        $missing = [];
        if (empty($data['ten_danh_muc']))  $missing[] = 'tên danh mục';
        if (empty($data['loai_danh_muc']) && empty($data['danh_muc_cha'])) {
            $missing[] = 'loại danh mục (thu hay chi)';
        }

        if (!empty($missing)) {
            return [
                'success'    => true,
                'message'    => "Để tạo danh mục mới, mình cần:\n- "
                    . implode("\n- ", $missing) . "\nBạn bổ sung được không?",
                'needs_info' => true,
            ];
        }

        // Tìm danh mục cha nếu user chỉ định
        $parentId   = null;
        $parentName = null;
        if (!empty($data['danh_muc_cha'])) {
            $parent = Category::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })->where('ten_danh_muc', 'like', '%' . $data['danh_muc_cha'] . '%')->first();

            if (!$parent) {
                return [
                    'success' => true,
                    'message' => "Mình không tìm thấy danh mục cha \"{$data['danh_muc_cha']}\" {$userName}. "
                        . "Bạn kiểm tra lại tên danh mục cha nhé.",
                ];
            }

            $parentId   = $parent->id;
            $parentName = $parent->ten_danh_muc;
            // Danh mục con kế thừa loại từ cha
            $data['loai_danh_muc'] = $parent->loai_danh_muc;
        }

        $data['danh_muc_cha_id'] = $parentId;

        // Kiểm tra trùng tên trong cùng cấp
        $exists = Category::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        })->where('ten_danh_muc', $data['ten_danh_muc'])
          ->where('danh_muc_cha_id', $parentId)
          ->exists();

        if ($exists) {
            $scope = $parentName ? "trong danh mục \"{$parentName}\"" : "";
            return [
                'success' => true,
                'message' => "Danh mục \"{$data['ten_danh_muc']}\" {$scope} đã tồn tại rồi {$userName}! "
                    . "Bạn muốn dùng danh mục này không?",
            ];
        }

        $bieu_tuong = !empty($data['bieu_tuong'])
            ? $data['bieu_tuong']
            : ($data['loai_danh_muc'] === 'THU' ? '💰' : '💸');
        $loai = $data['loai_danh_muc'] === 'THU' ? 'Thu nhập' : 'Chi tiêu';

        $confirmMsg = "Mình sẽ tạo danh mục mới:\n"
            . "- Tên: {$data['ten_danh_muc']} {$bieu_tuong}\n"
            . "- Loại: {$loai}\n"
            . ($parentName ? "- Thuộc danh mục cha: {$parentName}\n" : "- Cấp: Danh mục gốc\n")
            . (!empty($data['mo_ta']) ? "- Mô tả: {$data['mo_ta']}\n" : '')
            . "\nXác nhận tạo không {$userName}? (có/không)";

        $this->ai->savePendingAction($userId, 'CREATE_CATEGORY', $data);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function handleUpdate(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        if (empty($data['ten_danh_muc']) && empty($data['category_id'])) {
            return [
                'success'    => true,
                'message'    => "Bạn muốn sửa danh mục nào {$userName}? Cho mình biết tên danh mục cần sửa nhé.",
                'needs_info' => true,
            ];
        }

        // Chỉ cho sửa danh mục do user tạo
        $query = Category::where('user_id', $userId);
        if (!empty($data['category_id']))  $query->where('id', $data['category_id']);
        if (!empty($data['ten_danh_muc'])) $query->where('ten_danh_muc', 'like', '%' . $data['ten_danh_muc'] . '%');

        $category = $query->first();

        if (!$category) {
            return [
                'success' => true,
                'message' => "Mình không tìm thấy danh mục \"{$data['ten_danh_muc']}\" do bạn tạo {$userName}. "
                    . "Lưu ý: danh mục mặc định của hệ thống không thể sửa nhé.",
            ];
        }

        $hasChange = !empty($data['ten_danh_muc_moi'])
            || !empty($data['loai_danh_muc_moi'])
            || !empty($data['bieu_tuong_moi'])
            || !empty($data['mo_ta_moi'])
            || array_key_exists('danh_muc_cha_moi', $data);

        if (!$hasChange) {
            return [
                'success'    => true,
                'message'    => "Bạn muốn sửa danh mục \"{$category->ten_danh_muc}\" thành gì {$userName}?\n"
                    . "Mình có thể sửa: tên mới / loại (thu|chi) / biểu tượng emoji / mô tả / danh mục cha.",
                'needs_info' => true,
            ];
        }

        // Xử lý thay đổi danh mục cha
        $newParentId   = $category->danh_muc_cha_id; 
        $newParentName = $category->parent?->ten_danh_muc;

        if (array_key_exists('danh_muc_cha_moi', $data)) {
            if (empty($data['danh_muc_cha_moi'])) {
                // Chuyển lên thành danh mục gốc
                $newParentId   = null;
                $newParentName = null;
            } else {
                $newParent = Category::where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)->orWhereNull('user_id');
                })->where('ten_danh_muc', 'like', '%' . $data['danh_muc_cha_moi'] . '%')
                  ->where('id', '!=', $category->id) // không tự làm cha của mình
                  ->first();

                if (!$newParent) {
                    return [
                        'success' => true,
                        'message' => "Mình không tìm thấy danh mục cha \"{$data['danh_muc_cha_moi']}\" {$userName}. "
                            . "Bạn kiểm tra lại tên nhé.",
                    ];
                }

                // Tránh tạo vòng lặp: không cho đặt danh mục con làm cha
                if ($this->isDescendant($category->id, $newParent->id)) {
                    return [
                        'success' => true,
                        'message' => "Không thể đặt \"{$newParent->ten_danh_muc}\" làm cha vì nó đang là danh mục con của \"{$category->ten_danh_muc}\" {$userName}.",
                    ];
                }

                $newParentId   = $newParent->id;
                $newParentName = $newParent->ten_danh_muc;
            }
        }

        $loaiHienTai  = $category->loai_danh_muc === 'THU' ? 'Thu nhập' : 'Chi tiêu';
        $loaiMoi      = !empty($data['loai_danh_muc_moi'])
            ? ($data['loai_danh_muc_moi'] === 'THU' ? 'Thu nhập' : 'Chi tiêu')
            : $loaiHienTai;
        $tenMoi       = $data['ten_danh_muc_moi']  ?? $category->ten_danh_muc;
        $bieuTuongMoi = $data['bieu_tuong_moi']     ?? $category->bieu_tuong;
        $moTaMoi      = $data['mo_ta_moi']          ?? $category->mo_ta;

        $chaHienTai = $category->parent?->ten_danh_muc ?? 'Danh mục gốc';
        $chaMoi     = $newParentName ?? 'Danh mục gốc';

        $confirmMsg = "Mình sẽ cập nhật danh mục:\n"
            . "- Hiện tại: {$category->ten_danh_muc} {$category->bieu_tuong} ({$loaiHienTai}) | Cha: {$chaHienTai}\n"
            . "- Thành:    {$tenMoi} {$bieuTuongMoi} ({$loaiMoi}) | Cha: {$chaMoi}\n"
            . (!empty($moTaMoi) ? "- Mô tả: {$moTaMoi}\n" : '')
            . "\nXác nhận cập nhật không {$userName}? (có/không)";

        $this->ai->savePendingAction($userId, 'UPDATE_CATEGORY', [
            'category_id'        => $category->id,
            'ten_danh_muc_moi'   => $data['ten_danh_muc_moi']   ?? null,
            'loai_danh_muc_moi'  => $data['loai_danh_muc_moi']  ?? null,
            'bieu_tuong_moi'     => $data['bieu_tuong_moi']      ?? null,
            'mo_ta_moi'          => $data['mo_ta_moi']           ?? null,
            'danh_muc_cha_id_moi'=> $newParentId,
            '_cha_changed'       => array_key_exists('danh_muc_cha_moi', $data),
        ]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function handleDelete(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        if (empty($data['ten_danh_muc']) && empty($data['category_id'])) {
            return [
                'success'    => true,
                'message'    => "Bạn muốn xóa danh mục nào {$userName}? Cho mình biết tên danh mục nhé.",
                'needs_info' => true,
            ];
        }

        $query = Category::where('user_id', $userId);
        if (!empty($data['category_id']))  $query->where('id', $data['category_id']);
        if (!empty($data['ten_danh_muc'])) $query->where('ten_danh_muc', 'like', '%' . $data['ten_danh_muc'] . '%');

        $category = $query->first();

        if (!$category) {
            return [
                'success' => true,
                'message' => "Mình không tìm thấy danh mục \"{$data['ten_danh_muc']}\" do bạn tạo {$userName}. "
                    . "Lưu ý: danh mục mặc định của hệ thống không thể xóa nhé.",
            ];
        }

        $loai = $category->loai_danh_muc === 'THU' ? 'Thu nhập' : 'Chi tiêu';

        // Kiểm tra giao dịch của chính danh mục này
        $txCount = Transaction::where('user_id', $userId)
            ->where('category_id', $category->id)
            ->count();

        if ($txCount > 0) {
            return [
                'success' => true,
                'message' => "⚠️ Không thể xóa danh mục \"{$category->ten_danh_muc}\" {$userName} vì đang có "
                    . "{$txCount} giao dịch liên quan.\n"
                    . "Bạn cần chuyển hoặc xóa các giao dịch đó trước nhé.",
            ];
        }

        // Kiểm tra danh mục con
        $children      = Category::where('danh_muc_cha_id', $category->id)->get();
        $childrenCount = $children->count();

        if ($childrenCount > 0) {
            // Kiểm tra xem danh mục con nào có giao dịch
            $childrenWithTx = $children->filter(function ($child) use ($userId) {
                return Transaction::where('user_id', $userId)
                    ->where('category_id', $child->id)
                    ->exists();
            });

            if ($childrenWithTx->isNotEmpty()) {
                $names = $childrenWithTx->map(fn($c) => "\"{$c->ten_danh_muc}\"")
                                        ->implode(', ');
                return [
                    'success' => true,
                    'message' => "⚠️ Không thể xóa danh mục \"{$category->ten_danh_muc}\" {$userName}.\n"
                        . "Các danh mục con sau đang có giao dịch: {$names}.\n"
                        . "Bạn cần xóa hoặc chuyển giao dịch trong các danh mục con đó trước nhé.",
                ];
            }

            // Danh mục con không có giao dịch → cảnh báo sẽ xóa cả con
            $childNames = $children->map(fn($c) => "  • {$c->ten_danh_muc} {$c->bieu_tuong}")->implode("\n");
            $confirmMsg = "⚠️ Danh mục \"{$category->ten_danh_muc}\" có {$childrenCount} danh mục con:\n"
                . "{$childNames}\n\n"
                . "Nếu xóa, toàn bộ danh mục con trên cũng sẽ bị xóa theo.\n"
                . "Bạn có chắc chắn muốn XÓA TẤT CẢ không {$userName}? (có/không)";

            $this->ai->savePendingAction($userId, 'DELETE_CATEGORY', [
                'category_id'   => $category->id,
                'delete_children' => true,
            ]);

            return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
        }

        // Danh mục đơn, không có con, không có giao dịch
        $confirmMsg = "Mình sẽ xóa danh mục:\n"
            . "- Tên: {$category->ten_danh_muc} {$category->bieu_tuong}\n"
            . "- Loại: {$loai}\n"
            . ($category->parent ? "- Thuộc: {$category->parent->ten_danh_muc}\n" : '')
            . "\nXác nhận XÓA không {$userName}? (có/không)";

        $this->ai->savePendingAction($userId, 'DELETE_CATEGORY', [
            'category_id'     => $category->id,
            'delete_children' => false,
        ]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function executeCreate(array $data, int $userId, string $userName): array
    {
        try {
            $bieu_tuong = !empty($data['bieu_tuong'])
                ? $data['bieu_tuong']
                : ($data['loai_danh_muc'] === 'THU' ? '💰' : '💸');

            $category = Category::create([
                'user_id'          => $userId,
                'danh_muc_cha_id'  => $data['danh_muc_cha_id'] ?? null,
                'ten_danh_muc'     => $data['ten_danh_muc'],
                'loai_danh_muc'    => $data['loai_danh_muc'],
                'bieu_tuong'       => $bieu_tuong,
                'mo_ta'            => $data['mo_ta'] ?? null,
                'trang_thai'       => true,
            ]);

            $loai       = $data['loai_danh_muc'] === 'THU' ? 'thu nhập' : 'chi tiêu';
            $parentInfo = '';
            if (!empty($data['danh_muc_cha_id'])) {
                $parent     = Category::find($data['danh_muc_cha_id']);
                $parentInfo = $parent ? " (con của \"{$parent->ten_danh_muc}\")" : '';
            }

            return [
                'success'     => true,
                'message'     => "Đã tạo danh mục \"{$data['ten_danh_muc']}\" {$bieu_tuong} ({$loai}){$parentInfo} thành công {$userName}! "
                    . "Bạn có thể dùng ngay khi ghi giao dịch rồi nhé.",
                'action_done' => 'CREATE_CATEGORY',
                'data'        => $category->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('AI executeCreateCategory', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể tạo danh mục. Vui lòng thử lại.'];
        }
    }

    public function executeUpdate(array $data, int $userId, string $userName): array
    {
        try {
            $category     = Category::where('user_id', $userId)->findOrFail($data['category_id']);
            $updateFields = [];

            if (!empty($data['ten_danh_muc_moi']))   $updateFields['ten_danh_muc']  = $data['ten_danh_muc_moi'];
            if (!empty($data['loai_danh_muc_moi']))  $updateFields['loai_danh_muc'] = $data['loai_danh_muc_moi'];
            if (!empty($data['bieu_tuong_moi']))      $updateFields['bieu_tuong']    = $data['bieu_tuong_moi'];
            if (isset($data['mo_ta_moi']))            $updateFields['mo_ta']         = $data['mo_ta_moi'];

            // Cập nhật danh mục cha nếu có thay đổi
            if (!empty($data['_cha_changed'])) {
                $updateFields['danh_muc_cha_id'] = $data['danh_muc_cha_id_moi']; // có thể null
            }

            $category->update($updateFields);
            $fresh = $category->fresh(['parent']);
            $loai  = $fresh->loai_danh_muc === 'THU' ? 'thu nhập' : 'chi tiêu';
            $chaInfo = $fresh->parent ? " (thuộc \"{$fresh->parent->ten_danh_muc}\")" : '';

            return [
                'success'     => true,
                'message'     => "Đã cập nhật danh mục thành \"{$fresh->ten_danh_muc}\" {$fresh->bieu_tuong} ({$loai}){$chaInfo} thành công {$userName}!",
                'action_done' => 'UPDATE_CATEGORY',
                'data'        => $fresh->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('AI executeUpdateCategory', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể cập nhật danh mục. Vui lòng thử lại.'];
        }
    }

    public function executeDelete(array $data, int $userId, string $userName): array
    {
        try {
            $category = Category::where('user_id', $userId)->findOrFail($data['category_id']);
            $name     = $category->ten_danh_muc;
            $icon     = $category->bieu_tuong;

            if (!empty($data['delete_children'])) {
                // Xóa toàn bộ danh mục con (chỉ xóa được nếu con không có giao dịch — đã check ở handle)
                $children = Category::where('danh_muc_cha_id', $category->id)->get();
                foreach ($children as $child) {
                    // Null hóa category_id cho giao dịch liên quan (phòng ngừa)
                    Transaction::where('user_id', $userId)
                        ->where('category_id', $child->id)
                        ->update(['category_id' => null]);
                    $child->delete();
                }
                $deletedChildCount = $children->count();
            }

            // Null hóa giao dịch của chính danh mục cha (phòng ngừa)
            Transaction::where('user_id', $userId)
                ->where('category_id', $category->id)
                ->update(['category_id' => null]);

            $category->delete();

            $childMsg = !empty($data['delete_children']) && isset($deletedChildCount) && $deletedChildCount > 0
                ? " và {$deletedChildCount} danh mục con"
                : '';

            return [
                'success'     => true,
                'message'     => "Đã xóa danh mục \"{$name}\" {$icon}{$childMsg} thành công {$userName}!",
                'action_done' => 'DELETE_CATEGORY',
            ];

        } catch (\Exception $e) {
            Log::error('AI executeDeleteCategory', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể xóa danh mục. Vui lòng thử lại.'];
        }
    }

    /**
     * Kiểm tra xem $potentialDescendantId có phải là hậu duệ (con/cháu) của $categoryId không.
     * Dùng để tránh tạo vòng lặp cha-con khi update.
     */
    private function isDescendant(int $categoryId, int $potentialDescendantId): bool
    {
        $children = Category::where('danh_muc_cha_id', $categoryId)->pluck('id');
        if ($children->contains($potentialDescendantId)) {
            return true;
        }
        foreach ($children as $childId) {
            if ($this->isDescendant($childId, $potentialDescendantId)) {
                return true;
            }
        }
        return false;
    }
}