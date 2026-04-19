<?php

namespace App\Http\Controllers\AI;

use App\Services\AIService;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budgets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransactionController
{
    public function __construct(private AIService $ai) {}

    public function handleAdd(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

        // 1. Kiểm tra thiếu thông tin cơ bản
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

        // 2. Resolve danh mục
        $cat = $this->resolveCategory($data, $userId);
        if (is_array($cat)) return $cat; // trả về lỗi nếu không tìm thấy

        $data['category_id']   = $cat->id;
        $data['category_name'] = $cat->ten_danh_muc;

        // 3. Kiểm tra danh mục có bị vô hiệu hóa không
        if (!$cat->trang_thai) {
            return [
                'success' => true,
                'message' => "Danh mục \"{$cat->ten_danh_muc}\" đã bị vô hiệu hóa {$userName}.\n"
                    . "Không thể thêm giao dịch vào danh mục này. Bạn vui lòng chọn danh mục khác nhé!",
            ];
        }

        // Kiểm tra danh mục cha có bị vô hiệu hóa không
        if ($cat->danh_muc_cha_id) {
            $parentCat = Category::find($cat->danh_muc_cha_id);
            if ($parentCat && !$parentCat->trang_thai) {
                return [
                    'success' => true,
                    'message' => "Danh mục cha \"{$parentCat->ten_danh_muc}\" của \"{$cat->ten_danh_muc}\" đã bị vô hiệu hóa {$userName}.\n"
                        . "Không thể thêm giao dịch vào danh mục này. Bạn vui lòng chọn danh mục khác nhé!",
                ];
            }
        }

        // 4. Kiểm tra ngân sách (chỉ với giao dịch CHI)
        if ($data['loai_giao_dich'] === 'CHI') {
            $budgetCheck = $this->checkBudgetForAdd($userId, $data['category_id'], $data['so_tien'], $userName);
            if ($budgetCheck !== null) return $budgetCheck;
        }

        // 5. Build confirm message
        $data['ngay_giao_dich'] = $data['ngay_giao_dich'] ?? now()->toDateString();
        $loai = $data['loai_giao_dich'] === 'THU' ? 'Thu nhập' : 'Chi tiêu';

        // Lấy thêm thông tin ngân sách để hiển thị trong confirm (nếu CHI)
        $budgetInfo = '';
        if ($data['loai_giao_dich'] === 'CHI') {
            $wallet = Budgets::where('user_id', $userId)
                ->where('category_id', $data['category_id'])
                ->where('trang_thai', true)
                ->first();
            if ($wallet) {
                $budgetInfo = "\n- Ngân sách còn lại: " . number_format($wallet->so_du) . " VND"
                    . "\n- Sau khi chi: " . number_format($wallet->so_du - $data['so_tien']) . " VND";
            }
        }

        $confirmMsg = "Mình sẽ ghi giao dịch sau:\n"
            . "- Loại: {$loai}\n"
            . "- Số tiền: " . number_format($data['so_tien']) . " VND\n"
            . "- Danh mục: {$data['category_name']}\n"
            . "- Ngày: " . Carbon::parse($data['ngay_giao_dich'])->format('d/m/Y') . "\n"
            . (!empty($data['ghi_chu']) ? "- Ghi chú: {$data['ghi_chu']}\n" : '')
            . $budgetInfo
            . "\n\nXác nhận lưu không {$userName}? (có/không)";

        $this->ai->savePendingAction($userId, 'ADD_TRANSACTION', $data);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function handleUpdate(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

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
                'message'    => "Bạn muốn sửa giao dịch này thành gì {$userName}?\n- Thông tin muốn sửa (số tiền mới / ghi chú mới / danh mục mới)",
                'needs_info' => true,
            ];
        }

        // Kiểm tra danh mục mới nếu có thay đổi
        if (!empty($data['category_name_moi'])) {
            $newCat = Category::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })->where('ten_danh_muc', 'like', '%' . $data['category_name_moi'] . '%')->first();

            if (!$newCat) {
                return [
                    'success' => true,
                    'message' => "Mình không tìm thấy danh mục \"{$data['category_name_moi']}\" {$userName}. Bạn kiểm tra lại tên danh mục nhé!",
                ];
            }

            if (!$newCat->trang_thai) {
                return [
                    'success' => true,
                    'message' => "Danh mục \"{$newCat->ten_danh_muc}\" đã bị vô hiệu hóa {$userName}. Vui lòng chọn danh mục khác!",
                ];
            }

            $data['category_id_moi'] = $newCat->id;
        }

        $catName  = $transaction->category?->ten_danh_muc ?? 'Không rõ';
        $loai     = $transaction->loai_giao_dich === 'THU' ? 'Thu' : 'Chi';
        $ngay     = Carbon::parse($transaction->ngay_giao_dich)->format('d/m/Y');
        $newCatDisplay = !empty($data['category_name_moi']) ? $data['category_name_moi'] : $catName;

        $confirmMsg = "Mình sẽ sửa giao dịch:\n"
            . "- Hiện tại: {$loai} | " . number_format($transaction->so_tien) . " VND | {$catName} | {$ngay}\n"
            . "- Thành: "
            . (!empty($data['so_tien_moi']) ? number_format($data['so_tien_moi']) . " VND " : number_format($transaction->so_tien) . " VND ")
            . "| {$newCatDisplay} "
            . (!empty($data['ghi_chu_moi']) ? "| {$data['ghi_chu_moi']}" : '')
            . "\n\nXác nhận sửa không {$userName}? (có/không)";

        $this->ai->savePendingAction($userId, 'UPDATE_TRANSACTION', [
            'transaction_id'   => $transaction->id,
            'so_tien_moi'      => $data['so_tien_moi']      ?? null,
            'ghi_chu_moi'      => $data['ghi_chu_moi']      ?? null,
            'category_id_moi'  => $data['category_id_moi']  ?? null,
            'category_name_moi'=> $data['category_name_moi'] ?? null,
        ]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function handleDelete(array $parsed, int $userId, string $userName): array
    {
        $data = $parsed['data'] ?? [];

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
        ]);

        return ['success' => true, 'message' => $confirmMsg, 'pending' => true];
    }

    public function executeAdd(array $data, int $userId, string $userName): array
    {
        if (empty($data['category_id'])) {
            return [
                'success' => false,
                'message' => "Không thể ghi giao dịch vì thiếu danh mục {$userName}. Bạn thử lại nhé!",
            ];
        }

        DB::beginTransaction();
        try {
            // Kiểm tra lại danh mục (phòng trường hợp bị thay đổi sau khi confirm)
            $cat = Category::find($data['category_id']);
            if (!$cat || !$cat->trang_thai) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Danh mục đã bị vô hiệu hóa {$userName}. Không thể thêm giao dịch!",
                ];
            }

            // Tìm ngân sách active cho danh mục này
            $wallet = null;
            if ($data['loai_giao_dich'] === 'CHI') {
                $wallet = Budgets::where('user_id', $userId)
                    ->where('category_id', $data['category_id'])
                    ->where('trang_thai', true)
                    ->lockForUpdate()
                    ->first();

                if ($wallet) {
                    // Kiểm tra ngân sách đã hết hạn chưa
                    if ($wallet->da_het_han || !$wallet->is_active_time) {
                        $wallet->checkAndExpire();
                        DB::rollBack();
                        return [
                            'success' => false,
                            'message' => "Ngân sách cho danh mục \"{$cat->ten_danh_muc}\" đã hết hạn {$userName}. Không thể thêm giao dịch!",
                        ];
                    }

                    // Kiểm tra số dư đủ không
                    if ($wallet->so_du < $data['so_tien']) {
                        DB::rollBack();
                        return [
                            'success' => false,
                            'message' => "Ngân sách không đủ {$userName}!\n"
                                . "- Cần: " . number_format($data['so_tien']) . " VND\n"
                                . "- Số dư ngân sách còn: " . number_format($wallet->so_du) . " VND\n"
                                . "Bạn có muốn điều chỉnh lại ngân sách không?",
                        ];
                    }
                } else {
                    // Không có wallet active → kiểm tra lý do
                    $coHetHan = Budgets::where('category_id', $data['category_id'])
                        ->where('user_id', $userId)->where('da_het_han', true)->exists();
                    $coVoHieu = Budgets::where('category_id', $data['category_id'])
                        ->where('user_id', $userId)->where('trang_thai', false)->where('da_het_han', false)->exists();

                    if ($coHetHan) {
                        DB::rollBack();
                        return [
                            'success' => false,
                            'message' => "Ngân sách cho danh mục \"{$cat->ten_danh_muc}\" đã hết hạn {$userName}. Không thể thêm giao dịch!",
                        ];
                    }
                    if ($coVoHieu) {
                        DB::rollBack();
                        return [
                            'success' => false,
                            'message' => "Ngân sách cho danh mục \"{$cat->ten_danh_muc}\" đang bị vô hiệu hóa {$userName}. Không thể thêm giao dịch!",
                        ];
                    }
                    // Không có ngân sách nào → cho phép tạo bình thường
                }
            }

            // Tạo giao dịch
            $transaction = Transaction::create([
                'user_id'                => $userId,
                'so_tien'                => $data['so_tien'],
                'loai_giao_dich'         => $data['loai_giao_dich'],
                'category_id'            => $data['category_id'],
                'wallet_id'              => $wallet?->id,
                'phuong_thuc_thanh_toan' => $data['phuong_thuc_thanh_toan'] ?? 'Tiền mặt',
                'ngay_giao_dich'         => $data['ngay_giao_dich'] ?? now()->toDateString(),
                'ghi_chu'                => $data['ghi_chu'] ?? null,
            ]);

            // Cập nhật số dư ngân sách
            if ($wallet && !$wallet->da_het_han && $wallet->is_active_time) {
                if ($data['loai_giao_dich'] === 'THU') {
                    $wallet->increment('so_du', $data['so_tien']);
                } else {
                    $wallet->decrement('so_du', $data['so_tien']);
                }
            }

            DB::commit();

            $loai = $data['loai_giao_dich'] === 'THU' ? 'thu nhập' : 'chi tiêu';
            $budgetNote = '';
            if ($wallet && $data['loai_giao_dich'] === 'CHI') {
                $remainingBudget = $wallet->so_du - $data['so_tien'];
                $budgetNote = "\n- Ngân sách còn lại: " . number_format(max(0, $remainingBudget)) . " VND";
            }

            return [
                'success'     => true,
                'message'     => "Đã ghi giao dịch {$loai} thành công {$userName}!\n"
                    . "- Số tiền: " . number_format($data['so_tien']) . " VND\n"
                    . "- Danh mục: " . ($data['category_name'] ?? $cat->ten_danh_muc) . "\n"
                    . "- Ngày: " . Carbon::parse($data['ngay_giao_dich'] ?? now())->format('d/m/Y')
                    . $budgetNote
                    . "\nBạn cần mình giúp gì thêm không?",
                'action_done' => 'ADD_TRANSACTION',
                'data'        => $transaction->toArray(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AI executeAddTransaction', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Lỗi khi lưu giao dịch. Vui lòng thử lại.'];
        }
    }

    public function executeUpdate(array $data, int $userId, string $userName): array
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::where('user_id', $userId)->findOrFail($data['transaction_id']);

            $oldAmount    = $transaction->so_tien;
            $oldType      = $transaction->loai_giao_dich;
            $oldWalletId  = $transaction->wallet_id;

            // Bước 1: Hoàn tiền về ngân sách CŨ (dùng wallet_id gốc của giao dịch)
            if ($oldWalletId) {
                $oldWallet = Budgets::where('id', $oldWalletId)
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if ($oldWallet) {
                    if ($oldType === 'THU') {
                        $oldWallet->decrement('so_du', $oldAmount);
                    } else {
                        $oldWallet->increment('so_du', $oldAmount);
                    }
                }
            }

            // Xác định category mới
            $newCategoryId = $data['category_id_moi'] ?? $transaction->category_id;
            $newAmount     = $data['so_tien_moi']     ?? $oldAmount;
            $newType       = $transaction->loai_giao_dich; // AI không đổi loại giao dịch

            // Bước 2: Tìm ngân sách ACTIVE cho category mới
            $newWallet = Budgets::where('category_id', $newCategoryId)
                ->where('user_id', $userId)
                ->where('trang_thai', true)
                ->lockForUpdate()
                ->first();

            // Kiểm tra ngân sách mới có đủ không (chỉ với CHI)
            if ($newWallet && $newType === 'CHI') {
                if ($newWallet->da_het_han || !$newWallet->is_active_time) {
                    // Hoàn lại tiền cũ trước khi rollback
                    if ($oldWalletId && isset($oldWallet)) {
                        if ($oldType === 'THU') {
                            $oldWallet->increment('so_du', $oldAmount);
                        } else {
                            $oldWallet->decrement('so_du', $oldAmount);
                        }
                    }
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "Ngân sách cho danh mục đích đã hết hạn {$userName}. Không thể cập nhật giao dịch!",
                    ];
                }

                if ($newWallet->so_du < $newAmount) {
                    // Hoàn lại tiền cũ trước khi rollback
                    if ($oldWalletId && isset($oldWallet)) {
                        if ($oldType === 'THU') {
                            $oldWallet->increment('so_du', $oldAmount);
                        } else {
                            $oldWallet->decrement('so_du', $oldAmount);
                        }
                    }
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "Ngân sách không đủ để cập nhật {$userName}!\n"
                            . "- Cần: " . number_format($newAmount) . " VND\n"
                            . "- Số dư ngân sách còn: " . number_format($newWallet->so_du) . " VND",
                    ];
                }
            }

            // Bước 3: Áp tiền mới vào ngân sách mới
            if ($newWallet && !$newWallet->da_het_han && $newWallet->is_active_time) {
                if ($newType === 'THU') {
                    $newWallet->increment('so_du', $newAmount);
                } else {
                    $newWallet->decrement('so_du', $newAmount);
                }
            }

            // Bước 4: Cập nhật giao dịch
            $updateFields = ['wallet_id' => $newWallet?->id];
            if (!empty($data['so_tien_moi']))    $updateFields['so_tien']     = $data['so_tien_moi'];
            if (!empty($data['ghi_chu_moi']))     $updateFields['ghi_chu']     = $data['ghi_chu_moi'];
            if (!empty($data['category_id_moi'])) $updateFields['category_id'] = $data['category_id_moi'];

            $transaction->update($updateFields);

            DB::commit();

            return [
                'success'     => true,
                'message'     => "Đã sửa giao dịch thành công {$userName}! Bạn cần mình giúp gì thêm không?",
                'action_done' => 'UPDATE_TRANSACTION',
                'data'        => $transaction->fresh()->toArray(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AI executeUpdateTransaction', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể sửa giao dịch. Vui lòng thử lại.'];
        }
    }

    public function executeDelete(array $data, int $userId, string $userName): array
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::where('user_id', $userId)->findOrFail($data['transaction_id']);
            $info = number_format($transaction->so_tien) . " VND - "
                . Carbon::parse($transaction->ngay_giao_dich)->format('d/m/Y');

            // Hoàn tiền về ngân sách (dùng wallet_id trực tiếp — giống TransactionController thủ công)
            if ($transaction->wallet_id) {
                $wallet = Budgets::where('id', $transaction->wallet_id)
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if ($wallet) {
                    if ($transaction->loai_giao_dich === 'THU') {
                        // Xóa giao dịch THU → trừ lại số dư ngân sách
                        if ($wallet->so_du < $transaction->so_tien) {
                            DB::rollBack();
                            return [
                                'success' => false,
                                'message' => "Không thể xóa giao dịch này vì sẽ làm số dư ngân sách âm {$userName}!\n"
                                    . "Số dư hiện tại: " . number_format($wallet->so_du) . " VND",
                            ];
                        }
                        $wallet->decrement('so_du', $transaction->so_tien);
                    } else {
                        // Xóa giao dịch CHI → cộng lại số dư ngân sách
                        $wallet->increment('so_du', $transaction->so_tien);
                    }
                }
            }

            $transaction->delete();

            DB::commit();

            return [
                'success'     => true,
                'message'     => "Đã xoá giao dịch {$info} thành công {$userName}!\n"
                    . "Ngân sách đã được hoàn tiền tương ứng. Bạn cần mình giúp gì thêm không?",
                'action_done' => 'DELETE_TRANSACTION',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AI executeDeleteTransaction', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể xoá giao dịch. Vui lòng thử lại.'];
        }
    }

    /**
     * Resolve danh mục từ category_id hoặc ten_danh_muc trong $data.
     * Trả về Category model nếu thành công, hoặc array lỗi nếu không tìm thấy.
     */
    private function resolveCategory(array $data, int $userId): Category|array
    {
        if (!empty($data['category_id'])) {
            $cat = Category::find($data['category_id']);

            // category_id Gemini bịa ra → fallback tìm theo tên
            if (!$cat && !empty($data['ten_danh_muc'])) {
                $cat = $this->findCategoryByName($data['ten_danh_muc'], $userId);
            }
        } else {
            $cat = $this->findCategoryByName($data['ten_danh_muc'] ?? '', $userId);
        }

        if (!$cat) {
            $tenDanhMuc = $data['ten_danh_muc'] ?? 'không rõ';
            return [
                'success' => true,
                'message' => "Mình không tìm thấy danh mục \"{$tenDanhMuc}\".\n"
                    . "Bạn có muốn tạo danh mục này không? Nếu có, hãy nhắn:\n"
                    . "\"Tạo danh mục {$tenDanhMuc}\"\n"
                    . "Sau đó mình sẽ ghi giao dịch vào danh mục đó cho bạn nhé!",
            ];
        }

        return $cat;
    }

    private function findCategoryByName(string $name, int $userId): ?Category
    {
        return Category::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        })->where('ten_danh_muc', 'like', '%' . $name . '%')->first();
    }

    /**
     * Kiểm tra ngân sách trước khi thêm giao dịch CHI.
     * Trả về null nếu OK, array lỗi nếu không được phép.
     */
    private function checkBudgetForAdd(int $userId, int $categoryId, float $amount, string $userName): ?array
    {
        $wallet = Budgets::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('trang_thai', true)
            ->first();

        if (!$wallet) {
            // Không có ngân sách active → kiểm tra lý do
            $coHetHan = Budgets::where('category_id', $categoryId)
                ->where('user_id', $userId)->where('da_het_han', true)->exists();
            $coVoHieu = Budgets::where('category_id', $categoryId)
                ->where('user_id', $userId)->where('trang_thai', false)->where('da_het_han', false)->exists();

            if ($coHetHan) {
                $cat = Category::find($categoryId);
                return [
                    'success' => true,
                    'message' => "Ngân sách cho danh mục \"{$cat?->ten_danh_muc}\" đã hết hạn {$userName}.\n"
                        . "Không thể thêm giao dịch. Bạn có muốn tạo ngân sách mới không?",
                ];
            }

            if ($coVoHieu) {
                $cat = Category::find($categoryId);
                return [
                    'success' => true,
                    'message' => "Ngân sách cho danh mục \"{$cat?->ten_danh_muc}\" đang bị vô hiệu hóa {$userName}.\n"
                        . "Không thể thêm giao dịch.",
                ];
            }

            // Không có ngân sách nào → cho phép thêm bình thường
            return null;
        }

        // Có ngân sách active → kiểm tra hết hạn
        if ($wallet->da_het_han || !$wallet->is_active_time) {
            $cat = Category::find($categoryId);
            return [
                'success' => true,
                'message' => "Ngân sách cho danh mục \"{$cat?->ten_danh_muc}\" đã hết hạn {$userName}.\n"
                    . "Không thể thêm giao dịch. Bạn có muốn tạo ngân sách mới không?",
            ];
        }

        // Kiểm tra số dư
        if ($wallet->so_du < $amount) {
            $cat = Category::find($categoryId);
            return [
                'success' => true,
                'message' => "Ngân sách không đủ {$userName}!\n"
                    . "- Danh mục: {$cat?->ten_danh_muc}\n"
                    . "- Cần chi: " . number_format($amount) . " VND\n"
                    . "- Ngân sách còn lại: " . number_format($wallet->so_du) . " VND\n"
                    . "Bạn có muốn điều chỉnh lại ngân sách không?",
            ];
        }

        return null; // OK
    }
}