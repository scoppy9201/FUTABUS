<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    // Hiển thị danh sách danh mục
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Category::query()->with('parent')->where('user_id', $userId);

        // Bộ lọc theo loại
        if ($request->filled('loai')) {
            $query->loai($request->loai);
        }

        // Tìm kiếm theo tên
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Lọc theo trạng thái
        if ($request->filled('trang_thai')) {
            $query->trangThai($request->trang_thai);
        }

        // Sắp xếp
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['ten_danh_muc', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // If client expects JSON (API fetch), return full list as JSON (not paginated)
        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            $list = $query->orderBy('ten_danh_muc')->get();
            return response()->json($list);
        }

        $categories = $query->paginate(15)->withQueryString();

        // Lấy parent categories chỉ của user hiện tại
        $parentCategories = Category::where('user_id', $userId)
                                   ->whereNull('danh_muc_cha_id')
                                   ->orderBy('ten_danh_muc')
                                   ->get();

        return view('categories.index', compact('categories', 'parentCategories'));
    }

    // Hiển thị form tạo mới
    public function create()
    {
        $userId = Auth::id();
        $parentCategories = Category::where('user_id', $userId)
                                   ->whereNull('danh_muc_cha_id')
                                   ->orderBy('ten_danh_muc')
                                   ->get();

        return view('categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'loai_danh_muc' => 'required|in:THU,CHI',
            'danh_muc_cha_id' => 'nullable|exists:categories,id',
            'bieu_tuong' => 'required|string|max:100',
            'mo_ta' => 'nullable|string',
        ]);

        // Kiểm tra nếu có danh_muc_cha_id thì phải thuộc về user hiện tại
        if ($validated['danh_muc_cha_id']) {
            $parentCategory = Category::where('id', $validated['danh_muc_cha_id'])
                                     ->where('user_id', Auth::id())
                                     ->first();

            if (!$parentCategory) {
                return redirect()->back()
                               ->with('error', 'Danh mục cha không hợp lệ!')
                               ->withInput();
            }
        }

        // Tạo danh mục mới
        Category::create([
            'user_id' => Auth::id(),
            'ten_danh_muc' => $validated['ten_danh_muc'],
            'loai_danh_muc' => $validated['loai_danh_muc'],
            'danh_muc_cha_id' => $validated['danh_muc_cha_id'],
            'bieu_tuong' => $validated['bieu_tuong'],
            'mo_ta' => $validated['mo_ta'],
            'trang_thai' => true,
        ]);

        return redirect()->route('categories.index')->with('success', 'Thêm danh mục thành công!');
    }

    // Hiển thị form chỉnh sửa
    public function edit(Category $category)
    {
        // Kiểm tra danh mục có thuộc về user hiện tại không
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $userId = Auth::id();

        $parentCategories = Category::where('user_id', $userId)
                                   ->whereNull('danh_muc_cha_id')
                                   ->where('id', '!=', $category->id)
                                   ->orderBy('ten_danh_muc')
                                   ->get();

        return view('categories.edit', compact('category', 'parentCategories'));
    }

    // Update
    public function update(Request $request, Category $category)
    {
        // Kiểm tra danh mục có thuộc về user hiện tại không
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'loai_danh_muc' => 'required|in:THU,CHI',
            'danh_muc_cha_id' => 'nullable|exists:categories,id',
            'bieu_tuong' => 'required|string|max:100',
            'mo_ta' => 'nullable|string',
        ]);

        // Kiểm tra nếu có danh_muc_cha_id thì phải thuộc về user hiện tại
        if ($validated['danh_muc_cha_id']) {
            $parentCategory = Category::where('id', $validated['danh_muc_cha_id'])
                                     ->where('user_id', Auth::id())
                                     ->first();

            if (!$parentCategory) {
                return redirect()->back()
                               ->with('error', 'Danh mục cha không hợp lệ!')
                               ->withInput();
            }
        }

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Cập nhật danh mục thành công!');
    }

    // Xóa danh mục
    public function destroy(Category $category)
    {
        // Kiểm tra danh mục có thuộc về user hiện tại không
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Kiểm tra xem danh mục có giao dịch không
            if (!$category->canDelete()) {
                return redirect()->route('categories.index')->with('error', 'Không thể xóa danh mục đã có giao dịch. Vui lòng vô hiệu hóa thay vì xóa.');
            }

            // Kiểm tra có danh mục con không
            if ($category->children()->count() > 0) {
                return redirect()->route('categories.index')->with('error', 'Không thể xóa danh mục có danh mục con.');
            }

            $category->delete();
            DB::commit();

            return redirect()->route('categories.index')->with('success', 'Xóa danh mục thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('categories.index')->with('error', 'Có lỗi xảy ra khi xóa danh mục.');
        }
    }

    // Toggle trạng thái danh mục (kích hoạt/vô hiệu hóa)
    public function toggleStatus(Category $category)
    {
        // Kiểm tra danh mục có thuộc về user hiện tại không
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $category->update([
            'trang_thai' => !$category->trang_thai
        ]);

        $status = $category->trang_thai ? 'kích hoạt' : 'vô hiệu hóa';
        return redirect()->route('categories.index')->with('success', "Đã {$status} danh mục thành công!");
    }
}
