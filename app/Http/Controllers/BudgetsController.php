<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Budgets;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BudgetsController extends Controller
{
    /**
     * GET /api/v1/budgets
     * Danh sách ngân sách (có lọc + phân trang)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Budgets::with('category')
            ->where('user_id', Auth::id());

        // Tìm kiếm theo tên
        if ($request->filled('search')) {
            $search = trim($request->search);
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where('ten_ngan_sach', 'like', '%' . $escaped . '%');
        }

        // Lọc theo danh mục
        if ($request->filled('category_id')) {
            $categoryExists = Category::where('id', $request->category_id)
                ->where('user_id', Auth::id())
                ->exists();

            if ($categoryExists) {
                $query->where('category_id', $request->category_id);
            }
        }

        // Lọc theo trạng thái
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', (bool) $request->trang_thai);
        }

        // Sắp xếp
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['ten_ngan_sach', 'ngan_sach_goc', 'so_du', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Phân trang
        $wallets = $query->paginate(10)->withQueryString();

        // Gắn thêm thông tin tính toán cho mỗi wallet
        $wallets->getCollection()->transform(function (Budgets $wallet) {
            $wallet->append(['spent_amount', 'spent_percentage', 'is_over_budget']);
            return $wallet;
        });

        // Danh mục CHI để hiển thị filter / form
        $categories = Category::where('user_id', Auth::id())
            ->where('trang_thai', true)
            ->where('loai_danh_muc', 'CHI')
            ->whereNotNull('danh_muc_cha_id')
            ->orderBy('ten_danh_muc')
            ->get(['id', 'ten_danh_muc', 'bieu_tuong']);

        return response()->json([
            'wallets'    => $wallets,
            'categories' => $categories,
        ]);
    }

    /**
     * POST /api/v1/budgets
     * Tạo ngân sách mới
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateWallet($request);

        DB::beginTransaction();
        try {
            // Xác nhận category hợp lệ
            $category = $this->getValidCategory($validated['category_id']);
            if (!$category) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Chỉ có thể tạo ngân sách cho danh mục con loại CHI!',
                ], 422);
            }

            // Kiểm tra đã có ngân sách active cho danh mục này chưa
            $exists = Budgets::where('user_id', Auth::id())
                ->where('category_id', $validated['category_id'])
                ->where('trang_thai', true)
                ->exists();

            if ($exists) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Danh mục "' . $category->ten_danh_muc . '" đã có ngân sách đang hoạt động!',
                ], 422);
            }

            // Kiểm tra trùng tên trong tất cả ngân sách của user
            $duplicateName = Budgets::where('user_id', Auth::id())
                ->where('ten_ngan_sach', $validated['ten_ngan_sach'])
                ->exists();

            if ($duplicateName) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Tên ngân sách "' . $validated['ten_ngan_sach'] . '" đã tồn tại, vui lòng chọn tên khác!',
                    'errors'  => ['ten_ngan_sach' => ['Tên ngân sách đã tồn tại.']],
                ], 422);
            }

            $wallet = Budgets::create([
                'user_id'        => Auth::id(),
                'category_id'    => $validated['category_id'],
                'ten_ngan_sach'  => $validated['ten_ngan_sach'],
                'ngan_sach_goc'  => $validated['ngan_sach_goc'],
                'so_du'          => $validated['ngan_sach_goc'],
                'mo_ta'          => $validated['mo_ta'] ?? null,
                'trang_thai'     => true,
                'loai_thoi_gian' => $validated['loai_thoi_gian'],  
                'ngay_bat_dau'   => $validated['ngay_bat_dau'],    
                'ngay_ket_thuc'  => $validated['ngay_ket_thuc'],  
                'tu_dong_reset'  => $validated['tu_dong_reset'],   
                'da_het_han'     => false,                        
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Thêm ngân sách thành công!',
                'wallet'  => $wallet->load('category'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PATCH /api/v1/budgets/{wallet}
     * Cập nhật ngân sách
     */
    public function update(Request $request, Budgets $wallet): JsonResponse
    {
        if ($wallet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $this->validateWallet($request, $wallet->id);

        DB::beginTransaction();
        try {
            $category = $this->getValidCategory($validated['category_id']);
            if (!$category) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Chỉ có thể cập nhật cho danh mục con loại CHI!',
                ], 422);
            }

            // Kiểm tra trùng tên ngân sách
            $duplicateName = Budgets::where('user_id', Auth::id())
                ->where('ten_ngan_sach', $validated['ten_ngan_sach'])
                ->where('id', '!=', $wallet->id)
                ->exists();

            if ($duplicateName) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Tên ngân sách "' . $validated['ten_ngan_sach'] . '" đã tồn tại, vui lòng chọn tên khác!',
                    'errors'  => ['ten_ngan_sach' => ['Tên ngân sách đã tồn tại.']],
                ], 422);
            }

            // Đổi danh mục
            if ($wallet->category_id != $validated['category_id']) {
                if ($wallet->transactions()->exists()) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Không thể đổi danh mục cho ngân sách đã có giao dịch!',
                    ], 422);
                }

                $exists = Budgets::where('user_id', Auth::id())
                    ->where('category_id', $validated['category_id'])
                    ->where('trang_thai', true)
                    ->where('id', '!=', $wallet->id)
                    ->exists();

                if ($exists) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Danh mục "' . $category->ten_danh_muc . '" đã có ngân sách đang hoạt động!',
                    ], 422);
                }

                $wallet->update([
                    'ten_ngan_sach'  => $validated['ten_ngan_sach'],
                    'category_id'    => $validated['category_id'],
                    'ngan_sach_goc'  => $validated['ngan_sach_goc'],
                    'so_du'          => $validated['ngan_sach_goc'],
                    'mo_ta'          => $validated['mo_ta'] ?? null,
                    'loai_thoi_gian' => $validated['loai_thoi_gian'],  
                    'ngay_bat_dau'   => $validated['ngay_bat_dau'],   
                    'ngay_ket_thuc'  => $validated['ngay_ket_thuc'],   
                    'tu_dong_reset'  => $validated['tu_dong_reset'],   
                    'da_het_han'     => false,                        
                ]);

            } else {
                // Giữ nguyên danh mục – tính lại số dư
                $spentAmount = $wallet->spent_amount;
                $newBalance  = $validated['ngan_sach_goc'] - $spentAmount;

                if ($newBalance < 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Hạn mức mới phải lớn hơn hoặc bằng số tiền đã chi ('
                            . number_format($spentAmount, 0, ',', '.') . 'đ)!',
                    ], 422);
                }

                $wallet->update([
                    'ten_ngan_sach'  => $validated['ten_ngan_sach'],
                    'ngan_sach_goc'  => $validated['ngan_sach_goc'],
                    'so_du'          => $newBalance,
                    'mo_ta'          => $validated['mo_ta'] ?? null,
                    'loai_thoi_gian' => $validated['loai_thoi_gian'],  
                    'ngay_bat_dau'   => $validated['ngay_bat_dau'],   
                    'ngay_ket_thuc'  => $validated['ngay_ket_thuc'],  
                    'tu_dong_reset'  => $validated['tu_dong_reset'],   
                    'da_het_han'     => false,                       
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Cập nhật ngân sách thành công!',
                'wallet'  => $wallet->fresh()->load('category'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/v1/budgets/{wallet}
     * Xóa ngân sách
     */
    public function destroy(Budgets $wallet): JsonResponse
    {
        if ($wallet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        DB::beginTransaction();
        try {
            if (!$wallet->canDelete()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Không thể xóa ngân sách đã có giao dịch!',
                ], 422);
            }

            $name = $wallet->ten_ngan_sach;
            $wallet->delete();
            DB::commit();

            return response()->json([
                'message' => "Xóa ngân sách '{$name}' thành công!",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PATCH /api/v1/budgets/{wallet}/status
     * Bật / tắt trạng thái ngân sách
    */
    public function toggleStatus(Budgets $wallet): JsonResponse
    {
        if ($wallet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        DB::beginTransaction();
        try {
            $newStatus = !$wallet->trang_thai;

            if ($newStatus) {
                // ← Chặn kích hoạt lại nếu đã hết hạn
                if ($wallet->da_het_han) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Ngân sách '{$wallet->ten_ngan_sach}' đã hết hạn, không thể kích hoạt lại!",
                    ], 422);
                }

                // Kiểm tra trùng lặp khi kích hoạt lại
                $exists = Budgets::where('user_id', Auth::id())
                    ->where('category_id', $wallet->category_id)
                    ->where('trang_thai', true)
                    ->where('id', '!=', $wallet->id)
                    ->exists();

                if ($exists) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Danh mục này đã có ngân sách đang hoạt động!',
                    ], 422);
                }

                $wallet->update(['trang_thai' => true]);
                $newBalance = $wallet->recalculateBalance();

                DB::commit();
                return response()->json([
                    'message' => "Đã kích hoạt ngân sách '{$wallet->ten_ngan_sach}' và cập nhật số dư: "
                        . number_format($newBalance, 0, ',', '.') . 'đ',
                    'wallet'  => $wallet->fresh()->load('category'),
                ]);
            }

            // Vô hiệu hóa
            $wallet->update(['trang_thai' => false]);
            DB::commit();

            return response()->json([
                'message' => "Đã vô hiệu hóa ngân sách '{$wallet->ten_ngan_sach}' thành công!",
                'wallet'  => $wallet->fresh()->load('category'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/budgets/{wallet}/sync
     * Đồng bộ số dư thủ công
     */
    public function syncBalance(Budgets $wallet): JsonResponse
    {
        if ($wallet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        DB::beginTransaction();
        try {
            $newBalance = $wallet->recalculateBalance();
            DB::commit();

            return response()->json([
                'message'     => "Đã đồng bộ số dư ngân sách '{$wallet->ten_ngan_sach}'. Số dư mới: "
                    . number_format($newBalance, 0, ',', '.') . 'đ',
                'new_balance' => $newBalance,
                'wallet'      => $wallet->fresh()->load('category'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Validate dữ liệu wallet (dùng chung cho store & update)
     */
    private function validateWallet(Request $request, ?int $walletId = null): array
    {
        $validated = $request->validate([
            'ten_ngan_sach' => [
                'required', 'string', 'max:255',
                'regex:/^[\p{L}\p{N}\s\.,\-\(\)]*$/u',
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $category = Category::find($value);
                    if (!$category || $category->user_id !== Auth::id()) {
                        $fail('Danh mục không hợp lệ!');
                        return;
                    }
                    if ($category->loai_danh_muc !== 'CHI') {
                        $fail('Chỉ có thể tạo ngân sách cho danh mục CHI!');
                        return;
                    }
                    if (!$category->danh_muc_cha_id) {
                        $fail('Chỉ có thể chọn danh mục con!');
                    }
                },
            ],
            'ngan_sach_goc' => [
                'required', 'numeric', 'min:1000', 'max:100000000',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'mo_ta' => [
                'nullable', 'string', 'max:500',
                'regex:/^[\p{L}\p{N}\s\.,!?@#\-\(\)]*$/u',
            ],

            // validation thời gian
            'loai_thoi_gian' => ['required', 'in:thang,ngay'],
            'ngay_bat_dau'   => [
                'required', 'date',
            ],
            'ngay_ket_thuc'  => [
                'required', 'date', 'after:ngay_bat_dau',
                // Loại ngày: tối đa 30 ngày
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->loai_thoi_gian === 'ngay') {
                        $start = \Carbon\Carbon::parse($request->ngay_bat_dau);
                        $end   = \Carbon\Carbon::parse($value);
                        if ($start->diffInDays($end) > 30) {
                            $fail('Ngân sách theo ngày không được vượt quá 30 ngày!');
                        }
                    }
                },
            ],
            'tu_dong_reset'  => ['boolean'],
        ], [
            'ten_ngan_sach.required' => 'Vui lòng nhập tên ngân sách',
            'ten_ngan_sach.max'      => 'Tên ngân sách không được vượt quá 255 ký tự',
            'ten_ngan_sach.regex'    => 'Tên ngân sách chứa ký tự không hợp lệ',
            'category_id.required'   => 'Vui lòng chọn danh mục',
            'category_id.exists'     => 'Danh mục không tồn tại',
            'ngan_sach_goc.required' => 'Vui lòng nhập hạn mức ngân sách',
            'ngan_sach_goc.numeric'  => 'Hạn mức phải là số hợp lệ',
            'ngan_sach_goc.min'      => 'Hạn mức phải từ 1,000 VNĐ trở lên',
            'ngan_sach_goc.max'      => 'Hạn mức không được vượt quá 100,000,000 VNĐ',
            'ngan_sach_goc.regex'    => 'Hạn mức không hợp lệ',
            'mo_ta.max'              => 'Mô tả không được vượt quá 500 ký tự',
            'mo_ta.regex'            => 'Mô tả chứa ký tự không hợp lệ',
            'loai_thoi_gian.required'=> 'Vui lòng chọn loại thời gian',
            'loai_thoi_gian.in'      => 'Loại thời gian không hợp lệ',
            'ngay_bat_dau.required'  => 'Vui lòng chọn ngày bắt đầu',
            'ngay_bat_dau.date'      => 'Ngày bắt đầu không hợp lệ',
            'ngay_ket_thuc.required' => 'Vui lòng chọn ngày kết thúc',
            'ngay_ket_thuc.date'     => 'Ngày kết thúc không hợp lệ',
            'ngay_ket_thuc.after'    => 'Ngày kết thúc phải sau ngày bắt đầu',
        ]);

        // Trim
        $validated['ten_ngan_sach'] = trim($validated['ten_ngan_sach']);
        $validated['ngan_sach_goc'] = (float) trim((string) $validated['ngan_sach_goc']);
        $validated['mo_ta']         = isset($validated['mo_ta']) ? trim($validated['mo_ta']) : null;
        $validated['tu_dong_reset'] = $validated['tu_dong_reset'] ?? true;

        // Loại tháng → tự động tính ngày bắt đầu & kết thúc theo tháng
        if ($validated['loai_thoi_gian'] === 'thang') {
            $start = \Carbon\Carbon::parse($validated['ngay_bat_dau'])->startOfMonth();
            $validated['ngay_bat_dau']  = $start->toDateString();
            $validated['ngay_ket_thuc'] = $start->endOfMonth()->toDateString();
        }

        return $validated;
    }

    /**
     * Lấy category hợp lệ: thuộc user, loại CHI, là danh mục con
     */
    private function getValidCategory(int $categoryId): ?Category
    {
        return Category::where('id', $categoryId)
            ->where('user_id', Auth::id())
            ->where('loai_danh_muc', 'CHI')
            ->whereNotNull('danh_muc_cha_id')
            ->first();
    }
}