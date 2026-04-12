<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budgets as Wallet;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Wallet::with('category')
            ->where('user_id', Auth::id());

        if ($request->filled('search')) {
            $search = trim($request->search);
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where('ten_ngan_sach', 'like', '%' . $escaped . '%');
        }

        if ($request->filled('category_id')) {
            $categoryExists = Category::where('id', $request->category_id)
                ->where('user_id', Auth::id())
                ->exists();

            if ($categoryExists) {
                $query->where('category_id', $request->category_id);
            }
        }

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['ten_ngan_sach', 'ngan_sach_goc', 'so_du', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $wallets = $query->paginate(10)->withQueryString();

        return response()->json($wallets);
    }

    public function store(Request $request): JsonResponse
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
        ], [
            'ten_ngan_sach.required' => 'Vui lòng nhập tên ngân sách',
            'ten_ngan_sach.max'      => 'Tên ngân sách không được vượt quá 255 ký tự',
            'ten_ngan_sach.regex'    => 'Tên ngân sách chứa ký tự không hợp lệ',
            'category_id.required'  => 'Vui lòng chọn danh mục',
            'category_id.exists'    => 'Danh mục không tồn tại',
            'ngan_sach_goc.required'=> 'Vui lòng nhập hạn mức ngân sách',
            'ngan_sach_goc.numeric' => 'Hạn mức phải là số hợp lệ',
            'ngan_sach_goc.min'     => 'Hạn mức phải từ 1,000 VNĐ trở lên',
            'ngan_sach_goc.max'     => 'Hạn mức không được vượt quá 100,000,000 VNĐ (100 triệu)',
            'ngan_sach_goc.regex'   => 'Hạn mức không hợp lệ. Chỉ được nhập số và tối đa 2 chữ số thập phân',
            'mo_ta.max'             => 'Mô tả không được vượt quá 500 ký tự',
            'mo_ta.regex'           => 'Mô tả chứa ký tự không hợp lệ',
        ]);

        $validated['ten_ngan_sach'] = trim($validated['ten_ngan_sach']);
        $validated['ngan_sach_goc'] = trim($validated['ngan_sach_goc']);
        $validated['mo_ta']         = isset($validated['mo_ta']) ? trim($validated['mo_ta']) : null;

        DB::beginTransaction();
        try {
            $category = Category::where('id', $validated['category_id'])
                ->where('user_id', Auth::id())
                ->where('loai_danh_muc', 'CHI')
                ->whereNotNull('danh_muc_cha_id')
                ->first();

            if (!$category) {
                DB::rollBack();
                return response()->json(['message' => 'Chỉ có thể tạo ngân sách cho danh mục con loại chi!'], 422);
            }

            $existingWallet = Wallet::where('user_id', Auth::id())
                ->where('category_id', $validated['category_id'])
                ->where('trang_thai', true)
                ->exists();

            if ($existingWallet) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Danh mục "' . $category->ten_danh_muc . '" đã có ngân sách đang hoạt động!',
                ], 422);
            }

            $wallet = Wallet::create([
                'user_id'       => Auth::id(),
                'category_id'   => $validated['category_id'],
                'ten_ngan_sach' => $validated['ten_ngan_sach'],
                'ngan_sach_goc' => $validated['ngan_sach_goc'],
                'so_du'         => $validated['ngan_sach_goc'],
                'mo_ta'         => $validated['mo_ta'],
                'trang_thai'    => true,
            ]);

            DB::commit();

            return response()->json($wallet->load('category'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Wallet $wallet): JsonResponse
    {
        if ($wallet->user_id !== Auth::id()) {
            abort(403);
        }

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
                        $fail('Chỉ có thể chọn danh mục CHI!');
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
        ], [
            'ten_ngan_sach.required' => 'Vui lòng nhập tên ngân sách',
            'ten_ngan_sach.max'      => 'Tên ngân sách không được vượt quá 255 ký tự',
            'ten_ngan_sach.regex'    => 'Tên ngân sách chứa ký tự không hợp lệ',
            'category_id.required'  => 'Vui lòng chọn danh mục',
            'category_id.exists'    => 'Danh mục không tồn tại',
            'ngan_sach_goc.required'=> 'Vui lòng nhập hạn mức ngân sách',
            'ngan_sach_goc.numeric' => 'Hạn mức phải là số hợp lệ',
            'ngan_sach_goc.min'     => 'Hạn mức phải từ 1,000 VNĐ trở lên',
            'ngan_sach_goc.max'     => 'Hạn mức không được vượt quá 100,000,000 VNĐ (100 triệu)',
            'ngan_sach_goc.regex'   => 'Hạn mức không hợp lệ. Chỉ được nhập số và tối đa 2 chữ số thập phân',
            'mo_ta.max'             => 'Mô tả không được vượt quá 500 ký tự',
            'mo_ta.regex'           => 'Mô tả chứa ký tự không hợp lệ',
        ]);

        $validated['ten_ngan_sach'] = trim($validated['ten_ngan_sach']);
        $validated['ngan_sach_goc'] = trim($validated['ngan_sach_goc']);
        $validated['mo_ta']         = isset($validated['mo_ta']) ? trim($validated['mo_ta']) : null;

        DB::beginTransaction();
        try {
            $category = Category::where('id', $validated['category_id'])
                ->where('user_id', Auth::id())
                ->where('loai_danh_muc', 'CHI')
                ->whereNotNull('danh_muc_cha_id')
                ->first();

            if (!$category) {
                DB::rollBack();
                return response()->json(['message' => 'Chỉ có thể cập nhật cho danh mục con loại CHI!'], 422);
            }

            if ($wallet->category_id != $validated['category_id']) {
                if ($wallet->transactions()->exists()) {
                    DB::rollBack();
                    return response()->json(['message' => 'Không thể đổi danh mục cho ngân sách đã có giao dịch!'], 422);
                }

                $existingWallet = Wallet::where('user_id', Auth::id())
                    ->where('category_id', $validated['category_id'])
                    ->where('trang_thai', true)
                    ->where('id', '!=', $wallet->id)
                    ->exists();

                if ($existingWallet) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Danh mục "' . $category->ten_danh_muc . '" đã có ngân sách đang hoạt động!',
                    ], 422);
                }

                $wallet->update([
                    'ten_ngan_sach' => $validated['ten_ngan_sach'],
                    'category_id'   => $validated['category_id'],
                    'ngan_sach_goc' => $validated['ngan_sach_goc'],
                ]);
            }

            $wallet->update([
                'ten_ngan_sach' => $validated['ten_ngan_sach'],
                'mo_ta'         => $validated['mo_ta'] ?? $wallet->mo_ta,
            ]);

            DB::commit();

            return response()->json($wallet->fresh()->load('category'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
}
