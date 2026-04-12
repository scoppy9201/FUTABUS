<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    // GET /api/v1/categories
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $query  = Category::query()->with('parent')->where('user_id', $userId);

        if ($request->filled('loai'))       $query->loai($request->loai);
        if ($request->filled('search'))     $query->search($request->search);
        if ($request->filled('trang_thai')) $query->trangThai($request->trang_thai);

        $sortBy    = in_array($request->get('sort_by'), ['ten_danh_muc', 'created_at'])
                        ? $request->get('sort_by') : 'created_at';
        $sortOrder = $request->get('sort_order', 'desc');

        $categories       = $query->orderBy($sortBy, $sortOrder)->paginate(15)->withQueryString();
        $parentCategories = Category::where('user_id', $userId)
                                    ->whereNull('danh_muc_cha_id')
                                    ->orderBy('ten_danh_muc')
                                    ->get(['id', 'ten_danh_muc']);

        return response()->json([
            'success'           => true,
            'categories'        => $categories,
            'parentCategories'  => $parentCategories,
        ]);
    }

    // POST /api/v1/categories
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ten_danh_muc'    => 'required|string|max:255',
            'loai_danh_muc'   => 'required|in:THU,CHI',
            'danh_muc_cha_id' => 'nullable|exists:categories,id',
            'bieu_tuong'      => 'required|string|max:100',
            'mo_ta'           => 'nullable|string',
        ]);

        if (!empty($validated['danh_muc_cha_id'])) {
            $parent = Category::where('id', $validated['danh_muc_cha_id'])
                              ->where('user_id', Auth::id())->first();
            if (!$parent) return response()->json(['message' => 'Danh mục cha không hợp lệ!'], 422);
        }

        $category = Category::create([
            ...$validated,
            'user_id'    => Auth::id(),
            'trang_thai' => true,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Thêm danh mục thành công!',
            'category' => $category->load('parent'),
        ], 201);
    }

    // GET /api/v1/categories/{id}
    public function show(Category $category): JsonResponse
    {
        if ($category->user_id !== Auth::id()) return response()->json(['message' => 'Unauthorized'], 403);
        return response()->json(['success' => true, 'category' => $category->load('parent')]);
    }

    // PATCH /api/v1/categories/{id}
    public function update(Request $request, Category $category): JsonResponse
    {
        if ($category->user_id !== Auth::id()) return response()->json(['message' => 'Unauthorized'], 403);

        $validated = $request->validate([
            'ten_danh_muc'    => 'required|string|max:255',
            'loai_danh_muc'   => 'required|in:THU,CHI',
            'danh_muc_cha_id' => 'nullable|exists:categories,id',
            'bieu_tuong'      => 'required|string|max:100',
            'mo_ta'           => 'nullable|string',
        ]);

        if (!empty($validated['danh_muc_cha_id'])) {
            $parent = Category::where('id', $validated['danh_muc_cha_id'])
                              ->where('user_id', Auth::id())->first();
            if (!$parent) return response()->json(['message' => 'Danh mục cha không hợp lệ!'], 422);
        }

        $category->update($validated);
        return response()->json(['success' => true, 'message' => 'Cập nhật thành công!', 'category' => $category->load('parent')]);
    }

    // DELETE /api/v1/categories/{id}
    public function destroy(Category $category): JsonResponse
    {
        if ($category->user_id !== Auth::id()) return response()->json(['message' => 'Unauthorized'], 403);

        if (!$category->canDelete())
            return response()->json(['message' => 'Không thể xóa danh mục đã có giao dịch.'], 422);
        if ($category->children()->count() > 0)
            return response()->json(['message' => 'Không thể xóa danh mục có danh mục con.'], 422);

        DB::beginTransaction();
        try {
            $category->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Xóa danh mục thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa danh mục.'], 500);
        }
    }

    // PATCH /api/v1/categories/{id}/status
    public function toggleStatus(Category $category): JsonResponse
    {
        if ($category->user_id !== Auth::id()) return response()->json(['message' => 'Unauthorized'], 403);

        $category->update(['trang_thai' => !$category->trang_thai]);
        $status = $category->trang_thai ? 'kích hoạt' : 'vô hiệu hóa';

        return response()->json([
            'success'    => true,
            'message'    => "Đã {$status} danh mục thành công!",
            'trang_thai' => $category->trang_thai,
        ]);
    }
}
