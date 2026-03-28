<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    // Danh sách giao dịch
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = Transaction::with('category')->where('user_id', $userId);

        // Tìm kiếm đa trường
        if ($request->filled('search')) {
            $search = trim($request->search);

            // Escape các ký tự đặc biệt trong LIKE
            $searchEscaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);

            $query->where(function($q) use ($searchEscaped, $userId) {
                $q->where('ghi_chu', 'like', '%' . $searchEscaped . '%');

                // Tìm theo số tiền chỉ khi là số
                if (is_numeric(str_replace(',', '', $searchEscaped))) {
                    $q->orWhere('so_tien', 'like', '%' . str_replace(',', '', $searchEscaped) . '%');
                }

                $q->orWhereHas('category', function($categoryQuery) use ($searchEscaped, $userId) {
                    $categoryQuery->where('ten_danh_muc', 'like', '%' . $searchEscaped . '%')
                                ->where('user_id', $userId);
                });
            });
        }

        // Lọc theo danh mục
        if ($request->filled('danh_muc_id')) {
            $query->where('category_id', $request->danh_muc_id);
        }

        // Lọc theo loại giao dịch
        if ($request->filled('loai')) {
            $query->where('loai_giao_dich', $request->loai);
        }

        // Lọc theo phương thức thanh toán
        if ($request->filled('phuong_thuc')) {
            $query->where('phuong_thuc_thanh_toan', $request->phuong_thuc);
        }

        // Lọc theo ngày giao dịch
        if ($request->filled('tu_ngay')) {
            $query->where('ngay_giao_dich', '>=', $request->tu_ngay);
        }

        if ($request->filled('den_ngay')) {
            $query->where('ngay_giao_dich', '<=', $request->den_ngay);
        }

        // Sắp xếp + phân trang
        $transactions = $query->orderBy('ngay_giao_dich', 'desc')->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Lấy danh mục con (có danh_muc_cha_id)
        $categories = Category::where('user_id', $userId)->where('trang_thai', true)->whereNotNull('danh_muc_cha_id')->orderBy('loai_danh_muc')->orderBy('ten_danh_muc')->get();

        // Lấy wallets
        $wallets = Wallet::where('user_id', $userId)->where('trang_thai', true)->with('category')->orderBy('category_id')->orderBy('ten_ngan_sach')->get();

        // Thống kê tổng thu / chi
        $totalIncome = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')->sum('so_tien');
        $totalExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')->sum('so_tien');

        return view('transactions.index', compact('transactions','categories','wallets','totalIncome','totalExpense'));
    }

    // Thêm giao dịch mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($request) {
                    $category = Category::find($value);
                    if (!$category || $category->user_id !== Auth::id()) {
                        $fail('Danh mục không hợp lệ!');
                    }
                    if (!$category->danh_muc_cha_id) {
                        $fail('Chỉ có thể chọn danh mục con!');
                    }

                    // Kiểm tra loại
                    $loaiGiaoDich = $request->input('loai_giao_dich');
                    if ($category->loai_danh_muc !== $loaiGiaoDich) {
                        $fail('Danh mục "' . $category->ten_danh_muc . '" là loại ' .
                            $category->loai_danh_muc . ', không khớp với giao dịch ' .
                            $loaiGiaoDich . '!');
                    }
                }
            ],
            'loai_giao_dich' => 'required|in:THU,CHI',
            'phuong_thuc_thanh_toan' => 'required|in:Tiền mặt,Chuyển khoản',
            'so_tien' => [
                'required',
                'numeric',
                'min:1000',
                'max:100000000',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'ngay_giao_dich' => 'required|date|before_or_equal:today',
            'ghi_chu' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[\p{L}\p{N}\s\.,!?@#\-\(\)]*$/u',
            ],

            'money_wallet_id' => [
                'nullable',
                'exists:money_wallets,id',
                function ($attr, $value, $fail) {
                    if ($value && !\App\Models\MoneyWallet::where('id', $value)
                        ->where('user_id', Auth::id())
                        ->exists()) {
                        $fail('Ví không hợp lệ!');
                    }
                }
            ],
        ], [
            'category_id.required' => 'Vui lòng chọn danh mục',
            'category_id.exists' => 'Danh mục không tồn tại',
            'loai_giao_dich.required' => 'Vui lòng chọn loại giao dịch',
            'loai_giao_dich.in' => 'Loại giao dịch không hợp lệ',
            'phuong_thuc_thanh_toan.required' => 'Vui lòng chọn phương thức thanh toán',
            'phuong_thuc_thanh_toan.in' => 'Phương thức thanh toán không hợp lệ',
            'so_tien.required' => 'Vui lòng nhập số tiền',
            'so_tien.numeric' => 'Số tiền phải là số hợp lệ',
            'so_tien.min' => 'Số tiền phải từ 1,000 VNĐ trở lên',
            'so_tien.max' => 'Số tiền không được vượt quá 100,000,000 VNĐ (100 triệu)',
            'so_tien.regex' => 'Số tiền không hợp lệ. Chỉ được nhập số và tối đa 2 chữ số thập phân',
            'ngay_giao_dich.required' => 'Vui lòng chọn ngày giao dịch',
            'ngay_giao_dich.date' => 'Ngày giao dịch không hợp lệ',
            'ngay_giao_dich.before_or_equal' => 'Ngày giao dịch không được là ngày trong tương lai',
            'ghi_chu.max' => 'Ghi chú không được vượt quá 500 ký tự',
            'ghi_chu.regex' => 'Ghi chú chứa ký tự không hợp lệ',
        ]);

        // Trim dữ liệu
        $validated['so_tien'] = trim($validated['so_tien']);
        $validated['ghi_chu'] = $validated['ghi_chu'] ? trim($validated['ghi_chu']) : null;

        DB::beginTransaction();
        try {
            // Kiểm tra category phải thuộc user và là danh mục con
            $category = Category::where('id', $validated['category_id'])
                               ->where('user_id', Auth::id())
                               ->whereNotNull('danh_muc_cha_id')
                               ->first();

            if (!$category) {
                DB::rollBack();
                return back()
                    ->with('error', 'Chỉ có thể tạo giao dịch cho danh mục con!')
                    ->withInput();
            }

            // Kiểm tra loại giao dịch phải khớp với loại danh mục
            if ($category->loai_danh_muc !== $validated['loai_giao_dich']) {
                DB::rollBack();
                return back()
                    ->with('error', 'Loại giao dịch không khớp với loại danh mục!')
                    ->withInput();
            }

            // Tìm ngân sách của danh mục này (nếu có)
            $wallet = Wallet::where('category_id', $validated['category_id'])
                           ->where('user_id', Auth::id())
                           ->where('trang_thai', true)
                           ->lockForUpdate()
                           ->first();

            // Nếu là CHI và có ngân sách, kiểm tra số dư
            if ($validated['loai_giao_dich'] == 'CHI' && $wallet) {
                if ($wallet->so_du < $validated['so_tien']) {
                    DB::rollBack();
                    return back()
                        ->with('error', 'Ngân sách không đủ! Số dư hiện tại: ' . number_format($wallet->so_du, 0, ',', '.') . 'đ')
                        ->withInput();
                }
            }

            // Tạo giao dịch
            Transaction::create([
                'user_id' => Auth::id(),
                'category_id' => $validated['category_id'],
                'loai_giao_dich' => $validated['loai_giao_dich'],
                'phuong_thuc_thanh_toan' => $validated['phuong_thuc_thanh_toan'],
                'so_tien' => $validated['so_tien'],
                'ngay_giao_dich' => $validated['ngay_giao_dich'],
                'ghi_chu' => $validated['ghi_chu'],
                'money_wallet_id' => $validated['money_wallet_id'] ?? null,
            ]);

            // Cập nhật số dư ngân sách (nếu có)
            if ($wallet) {
                if ($validated['loai_giao_dich'] == 'THU') {
                    $wallet->increment('so_du', $validated['so_tien']);
                } else {
                    $wallet->decrement('so_du', $validated['so_tien']);
                }
            }

            if (!empty($validated['money_wallet_id'])) {

                $mWallet = \App\Models\MoneyWallet::where('id', $validated['money_wallet_id'])
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->first();

                if ($mWallet) {
                    if ($validated['loai_giao_dich'] == 'THU') {
                        $mWallet->increment('so_du', $validated['so_tien']);
                    } else {
                        $mWallet->decrement('so_du', $validated['so_tien']);
                    }
                }
            }

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Thêm giao dịch thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Cập nhật giao dịch
    public function update(Request $request, Transaction $transaction)
    {
        // Kiểm tra quyền sở hữu
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($request) {
                    $category = Category::find($value);
                    if (!$category || $category->user_id !== Auth::id()) {
                        $fail('Danh mục không hợp lệ!');
                    }
                    if (!$category->danh_muc_cha_id) {
                        $fail('Chỉ có thể chọn danh mục con!');
                    }

                    // Kiểm tra loại danh mục khớp với loại giao dịch
                    $loaiGiaoDich = $request->input('loai_giao_dich');
                    if ($category->loai_danh_muc !== $loaiGiaoDich) {
                        $fail('Danh mục "' . $category->ten_danh_muc . '" là loại ' .
                            $category->loai_danh_muc . ', không khớp với giao dịch ' .
                            $loaiGiaoDich . '!');
                    }
                }
            ],
            'loai_giao_dich' => 'required|in:THU,CHI',
            'phuong_thuc_thanh_toan' => 'required|in:Tiền mặt,Chuyển khoản',
            'so_tien' => [
                'required',
                'numeric',
                'min:1000',
                'max:100000000',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'ngay_giao_dich' => 'required|date|before_or_equal:today',
            'ghi_chu' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[\p{L}\p{N}\s\.,!?@#\-\(\)]*$/u',
            ],
        ], [
            'category_id.required' => 'Vui lòng chọn danh mục',
            'category_id.exists' => 'Danh mục không tồn tại',
            'loai_giao_dich.required' => 'Vui lòng chọn loại giao dịch',
            'loai_giao_dich.in' => 'Loại giao dịch không hợp lệ',
            'phuong_thuc_thanh_toan.required' => 'Vui lòng chọn phương thức thanh toán',
            'phuong_thuc_thanh_toan.in' => 'Phương thức thanh toán không hợp lệ',
            'so_tien.required' => 'Vui lòng nhập số tiền',
            'so_tien.numeric' => 'Số tiền phải là số hợp lệ',
            'so_tien.min' => 'Số tiền phải từ 1,000 VNĐ trở lên',
            'so_tien.max' => 'Số tiền không được vượt quá 100,000,000 VNĐ (100 triệu)',
            'so_tien.regex' => 'Số tiền không hợp lệ. Chỉ được nhập số và tối đa 2 chữ số thập phân',
            'ngay_giao_dich.required' => 'Vui lòng chọn ngày giao dịch',
            'ngay_giao_dich.date' => 'Ngày giao dịch không hợp lệ',
            'ngay_giao_dich.before_or_equal' => 'Ngày giao dịch không được là ngày trong tương lai',
            'ghi_chu.max' => 'Ghi chú không được vượt quá 500 ký tự',
            'ghi_chu.regex' => 'Ghi chú chứa ký tự không hợp lệ',
        ]);

        // Trim dữ liệu
        $validated['so_tien'] = trim($validated['so_tien']);
        $validated['ghi_chu'] = $validated['ghi_chu'] ? trim($validated['ghi_chu']) : null;

        DB::beginTransaction();
        try {
            // Kiểm tra category (đã validate rồi, nhưng vẫn cần query để dùng)
            $category = Category::where('id', $validated['category_id'])
                            ->where('user_id', Auth::id())
                            ->whereNotNull('danh_muc_cha_id')
                            ->first();

            if (!$category) {
                DB::rollBack();
                return back()
                    ->with('error', 'Chỉ có thể cập nhật giao dịch cho danh mục con!')
                    ->withInput();
            }

            // Lưu thông tin giao dịch cũ
            $oldCategoryId = $transaction->category_id;
            $oldAmount = $transaction->so_tien;
            $oldType = $transaction->loai_giao_dich;
            $oldMoneyWalletId = $transaction->money_wallet_id;

            // Trường hợp 1: Cùng danh mục
            if ($oldCategoryId == $validated['category_id']) {
                $wallet = Wallet::where('category_id', $oldCategoryId)
                            ->where('user_id', Auth::id())
                            ->where('trang_thai', true)
                            ->lockForUpdate()
                            ->first();

                if ($wallet) {
                    // Hoàn trả số tiền cũ
                    if ($oldType == 'THU') {
                        $wallet->decrement('so_du', $oldAmount);
                    } else {
                        $wallet->increment('so_du', $oldAmount);
                    }

                    // Refresh để lấy giá trị mới
                    $wallet->refresh();

                    // Kiểm tra số dư trước khi trừ (nếu là CHI)
                    if ($validated['loai_giao_dich'] == 'CHI') {
                        if ($wallet->so_du < $validated['so_tien']) {
                            DB::rollBack();
                            return back()
                                ->with('error', 'Ngân sách không đủ! Số dư hiện tại: ' . number_format($wallet->so_du, 0, ',', '.') . 'đ')
                                ->withInput();
                        }
                    }

                    // Áp dụng số tiền mới
                    if ($validated['loai_giao_dich'] == 'THU') {
                        $wallet->increment('so_du', $validated['so_tien']);
                    } else {
                        $wallet->decrement('so_du', $validated['so_tien']);
                    }
                }
            }
            // Trường hợp 2: Khác danh mục
            else {
                // Lấy ngân sách cũ (nếu có)
                $oldWallet = Wallet::where('category_id', $oldCategoryId)
                                ->where('user_id', Auth::id())
                                ->where('trang_thai', true)
                                ->lockForUpdate()
                                ->first();

                // Hoàn trả ngân sách cũ (nếu có)
                if ($oldWallet) {
                    if ($oldType == 'THU') {
                        // Kiểm tra trước khi trừ
                        if ($oldWallet->so_du < $oldAmount) {
                            DB::rollBack();
                            return back()
                                ->with('error', 'Không thể cập nhật vì sẽ làm số dư ngân sách cũ âm!')
                                ->withInput();
                        }
                        $oldWallet->decrement('so_du', $oldAmount);
                    } else {
                        $oldWallet->increment('so_du', $oldAmount);
                    }
                }

                // Lấy lại ngân sách mới SAU KHI đã hoàn trả ngân sách cũ
                $newWallet = Wallet::where('category_id', $validated['category_id'])
                                ->where('user_id', Auth::id())
                                ->where('trang_thai', true)
                                ->lockForUpdate()
                                ->first();

                // Kiểm tra và áp dụng ngân sách mới (nếu có)
                if ($newWallet) {
                    // Kiểm tra số dư trước khi trừ (nếu là CHI)
                    if ($validated['loai_giao_dich'] == 'CHI') {
                        if ($newWallet->so_du < $validated['so_tien']) {
                            DB::rollBack();
                            return back()
                                ->with('error', 'Ngân sách mới không đủ! Số dư hiện tại: ' . number_format($newWallet->so_du, 0, ',', '.') . 'đ')
                                ->withInput();
                        }
                    }

                    // Áp dụng số tiền mới
                    if ($validated['loai_giao_dich'] == 'THU') {
                        $newWallet->increment('so_du', $validated['so_tien']);
                    } else {
                        $newWallet->decrement('so_du', $validated['so_tien']);
                    }
                }
            }

            // Cập nhật giao dịch
            $transaction->update([
                'category_id' => $validated['category_id'],
                'loai_giao_dich' => $validated['loai_giao_dich'],
                'phuong_thuc_thanh_toan' => $validated['phuong_thuc_thanh_toan'],
                'so_tien' => $validated['so_tien'],
                'ngay_giao_dich' => $validated['ngay_giao_dich'],
                'ghi_chu' => $validated['ghi_chu'],
                'money_wallet_id' => $validated['money_wallet_id'] ?? null,
            ]);

            // ====== REVERT OLD MONEY WALLET ======
            if ($oldMoneyWalletId) {

                $oldW = \App\Models\MoneyWallet::where('id', $oldMoneyWalletId)
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->first();

                if ($oldW) {
                    if ($oldType == 'THU') {
                        $oldW->decrement('so_du', $oldAmount);
                    } else {
                        $oldW->increment('so_du', $oldAmount);
                    }
                }
            }

            // ====== APPLY NEW MONEY WALLET ======
            $newMoneyWalletId = $validated['money_wallet_id'] ?? null;

            if ($newMoneyWalletId) {

                $newW = \App\Models\MoneyWallet::where('id', $newMoneyWalletId)
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->first();

                if ($newW) {
                    if ($validated['loai_giao_dich'] == 'THU') {
                        $newW->increment('so_du', $validated['so_tien']);
                    } else {
                        $newW->decrement('so_du', $validated['so_tien']);
                    }
                }
            }

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Cập nhật giao dịch thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Xóa giao dịch
    public function destroy(Transaction $transaction)
    {
        // Kiểm tra quyền sở hữu
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Hoàn trả số dư ngân sách (nếu có)
            $wallet = Wallet::where('category_id', $transaction->category_id)
                        ->where('user_id', Auth::id())
                        ->where('trang_thai', true)
                        ->lockForUpdate()
                        ->first();

            if ($wallet) {
                if ($transaction->loai_giao_dich == 'THU') {
                    // Kiểm tra trước khi trừ
                    if ($wallet->so_du < $transaction->so_tien) {
                        DB::rollBack();
                        return back()->with('error',
                            'Không thể xóa giao dịch này vì sẽ làm số dư âm! ' .
                            'Số dư hiện tại: ' . number_format($wallet->so_du, 0, ',', '.') . 'đ'
                        );
                    }
                    $wallet->decrement('so_du', $transaction->so_tien);
                } else {
                    $wallet->increment('so_du', $transaction->so_tien);
                }
            }

            // ====== MONEY WALLET ======
            if ($transaction->money_wallet_id) {

                $mWallet = \App\Models\MoneyWallet::where('id', $transaction->money_wallet_id)
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->first();

                if ($mWallet) {
                    if ($transaction->loai_giao_dich == 'THU') {
                        $mWallet->decrement('so_du', $transaction->so_tien);
                    } else {
                        $mWallet->increment('so_du', $transaction->so_tien);
                    }
                }
            }

            $transaction->delete();

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Xóa giao dịch thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
