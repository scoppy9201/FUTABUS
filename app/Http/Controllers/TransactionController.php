<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budgets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // Danh sách giao dịch 
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = Transaction::with('category')->where('user_id', $userId);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $searchEscaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);

            $query->where(function($q) use ($searchEscaped, $userId) {
                $q->where('ghi_chu', 'like', '%' . $searchEscaped . '%');

                if (is_numeric(str_replace(',', '', $searchEscaped))) {
                    $q->orWhere('so_tien', 'like', '%' . str_replace(',', '', $searchEscaped) . '%');
                }

                $q->orWhereHas('category', function($categoryQuery) use ($searchEscaped, $userId) {
                    $categoryQuery->where('ten_danh_muc', 'like', '%' . $searchEscaped . '%')
                                ->where('user_id', $userId);
                });
            });
        }

        if ($request->filled('danh_muc_id')) {
            $query->where('category_id', $request->danh_muc_id);
        }

        if ($request->filled('loai')) {
            $query->where('loai_giao_dich', $request->loai);
        }

        if ($request->filled('phuong_thuc')) {
            $query->where('phuong_thuc_thanh_toan', $request->phuong_thuc);
        }

        if ($request->filled('tu_ngay')) {
            $query->where('ngay_giao_dich', '>=', $request->tu_ngay);
        }

        if ($request->filled('den_ngay')) {
            $query->where('ngay_giao_dich', '<=', $request->den_ngay);
        }

        $transactions = $query->orderBy('ngay_giao_dich', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10)
                            ->withQueryString();

        $categories = Category::where('user_id', $userId)
                            ->where('trang_thai', true)
                            ->whereNotNull('danh_muc_cha_id')
                            ->orderBy('loai_danh_muc')
                            ->orderBy('ten_danh_muc')
                            ->get();

        $wallets = Budgets::where('user_id', $userId)
                        ->where('trang_thai', true)
                        ->with('category')
                        ->orderBy('category_id')
                        ->orderBy('ten_ngan_sach')
                        ->get();

        $moneyWallets = \App\Models\MoneyWallet::where('user_id', $userId)
                        ->where('trang_thai', true)
                        ->orderBy('ten_vi')
                        ->get();

        $totalIncome  = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'THU')->sum('so_tien');
        $totalExpense = Transaction::where('user_id', $userId)->where('loai_giao_dich', 'CHI')->sum('so_tien');

        return response()->json([
            'transactions' => $transactions,
            'categories'   => $categories,
            'wallets'      => $wallets,
            'moneyWallets' => $moneyWallets, 
            'totalIncome'  => $totalIncome,
            'totalExpense' => $totalExpense,
        ]);
    }

    // Lưu giao dịch 
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
                    $loaiGiaoDich = $request->input('loai_giao_dich');
                    if ($category->loai_danh_muc !== $loaiGiaoDich) {
                        $fail('Danh mục "' . $category->ten_danh_muc . '" là loại ' .
                            $category->loai_danh_muc . ', không khớp với giao dịch ' . $loaiGiaoDich . '!');
                    }
                }
            ],
            'loai_giao_dich'         => 'required|in:THU,CHI',
            'phuong_thuc_thanh_toan' => 'required|in:Tiền mặt,Chuyển khoản',
            'so_tien' => [
                'required', 'numeric', 'min:1000', 'max:100000000',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'ngay_giao_dich' => 'required|date|before_or_equal:today',
            'ghi_chu' => [
                'nullable', 'string', 'max:500',
                'regex:/^[\p{L}\p{N}\s\.,!?@#\-\(\)]*$/u',
            ],
            'money_wallet_id' => [
                'nullable',
                'exists:money_wallets,id',
                function ($attr, $value, $fail) {
                    if ($value && !\App\Models\MoneyWallet::where('id', $value)
                        ->where('user_id', Auth::id())->exists()) {
                        $fail('Ví không hợp lệ!');
                    }
                }
            ],
        ], [
            'category_id.required'           => 'Vui lòng chọn danh mục',
            'category_id.exists'             => 'Danh mục không tồn tại',
            'loai_giao_dich.required'        => 'Vui lòng chọn loại giao dịch',
            'loai_giao_dich.in'              => 'Loại giao dịch không hợp lệ',
            'phuong_thuc_thanh_toan.required'=> 'Vui lòng chọn phương thức thanh toán',
            'phuong_thuc_thanh_toan.in'      => 'Phương thức thanh toán không hợp lệ',
            'so_tien.required'               => 'Vui lòng nhập số tiền',
            'so_tien.numeric'                => 'Số tiền phải là số hợp lệ',
            'so_tien.min'                    => 'Số tiền phải từ 1,000 VNĐ trở lên',
            'so_tien.max'                    => 'Số tiền không được vượt quá 100,000,000 VNĐ',
            'so_tien.regex'                  => 'Số tiền không hợp lệ',
            'ngay_giao_dich.required'        => 'Vui lòng chọn ngày giao dịch',
            'ngay_giao_dich.date'            => 'Ngày giao dịch không hợp lệ',
            'ngay_giao_dich.before_or_equal' => 'Ngày giao dịch không được là ngày trong tương lai',
            'ghi_chu.max'                    => 'Ghi chú không được vượt quá 500 ký tự',
            'ghi_chu.regex'                  => 'Ghi chú chứa ký tự không hợp lệ',
        ]);

        $validated['so_tien'] = trim($validated['so_tien']);
        $validated['ghi_chu'] = $validated['ghi_chu'] ? trim($validated['ghi_chu']) : null;

        DB::beginTransaction();
        try {
            $category = Category::where('id', $validated['category_id'])
                                ->where('user_id', Auth::id())
                                ->whereNotNull('danh_muc_cha_id')
                                ->first();

            if (!$category) {
                DB::rollBack();
                return response()->json(['message' => 'Chỉ có thể tạo giao dịch cho danh mục con!'], 422);
            }

            if ($category->loai_danh_muc !== $validated['loai_giao_dich']) {
                DB::rollBack();
                return response()->json(['message' => 'Loại giao dịch không khớp với loại danh mục!'], 422);
            }


            // Kiểm tra danh mục cha có đang active không
            $parentCategory = Category::find($category->danh_muc_cha_id);
            if ($parentCategory && !$parentCategory->trang_thai) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Danh mục cha đã bị vô hiệu hóa, không thể thực hiện giao dịch!'
                ], 422);
            }

            $wallet = Budgets::where('category_id', $validated['category_id'])
                            ->where('user_id', Auth::id())
                            ->where('trang_thai', true)
                            ->lockForUpdate()
                            ->first();

            // Kiểm tra ngân sách chỉ với giao dịch CHI
            if ($validated['loai_giao_dich'] === 'CHI') {

                if (!$wallet) {
                    // Không có budget active → kiểm tra tại sao
                    $coHetHan = Budgets::where('category_id', $validated['category_id'])
                        ->where('user_id', Auth::id())
                        ->where('da_het_han', true)
                        ->exists();

                    $coVoHieu = Budgets::where('category_id', $validated['category_id'])
                        ->where('user_id', Auth::id())
                        ->where('trang_thai', false)
                        ->where('da_het_han', false)
                        ->exists();

                    if ($coHetHan) {
                        DB::rollBack();
                        return response()->json([
                            'message'      => 'Ngân sách cho danh mục này đã hết hạn, không thể thêm giao dịch!',
                            'warning_type' => 'het_han',
                        ], 422);
                    }

                    if ($coVoHieu) {
                        DB::rollBack();
                        return response()->json([
                            'message'      => 'Ngân sách cho danh mục này đang bị vô hiệu hóa!',
                            'warning_type' => 'vo_hieu',
                        ], 422);
                    }

                    // Không có budget nào → cho phép tạo giao dịch bình thường (không bắt buộc phải có budget)

                } else {
                    // Có budget active → kiểm tra thời hạn
                    if ($wallet->da_het_han || !$wallet->is_active_time) {
                        $wallet->checkAndExpire();
                        DB::rollBack();
                        return response()->json([
                            'message'      => 'Ngân sách cho danh mục này đã hết hạn, không thể thêm giao dịch!',
                            'warning_type' => 'het_han',
                        ], 422);
                    }

                    // Kiểm tra số dư
                    if ($wallet->so_du < $validated['so_tien']) {
                        DB::rollBack();
                        return response()->json([
                            'message'      => 'Ngân sách không đủ! Số dư hiện tại: '
                                . number_format($wallet->so_du, 0, ',', '.') . 'đ',
                            'warning_type' => 'khong_du',
                        ], 422);
                    }
                }
            }

            // Tạo giao dịch
            Transaction::create([
                'user_id'                => Auth::id(),
                'category_id'            => $validated['category_id'],
                'wallet_id'              => $wallet?->id,
                'loai_giao_dich'         => $validated['loai_giao_dich'],
                'phuong_thuc_thanh_toan' => $validated['phuong_thuc_thanh_toan'],
                'so_tien'                => $validated['so_tien'],
                'ngay_giao_dich'         => $validated['ngay_giao_dich'],
                'ghi_chu'                => $validated['ghi_chu'],
                'money_wallet_id'        => $validated['money_wallet_id'] ?? null,
            ]);

            // Cập nhật số dư budget nếu còn active và còn hạn
            if ($wallet && !$wallet->da_het_han && $wallet->is_active_time) {
                if ($validated['loai_giao_dich'] == 'THU') {
                    $wallet->increment('so_du', $validated['so_tien']);
                } else {
                    $wallet->decrement('so_du', $validated['so_tien']);
                }
            }

            // Cập nhật MoneyWallet
            if (!empty($validated['money_wallet_id'])) {
                $mWallet = \App\Models\MoneyWallet::where('id', $validated['money_wallet_id'])
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->first();

                if ($mWallet) {
                    if ($validated['loai_giao_dich'] == 'CHI') {
                        if ($mWallet->so_du < $validated['so_tien']) {
                            DB::rollBack();
                            return response()->json([
                                'message' => "Ví \"{$mWallet->ten_vi}\" không đủ số dư! " .
                                    "Cần: " . number_format($validated['so_tien']) . "đ | " .
                                    "Hiện có: " . number_format($mWallet->so_du) . "đ"
                            ], 422);
                        }
                        $mWallet->decrement('so_du', $validated['so_tien']);
                    } else {
                        $mWallet->increment('so_du', $validated['so_tien']);
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Thêm giao dịch thành công!'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    // Cập nhật giao dịch 
    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
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
                    $loaiGiaoDich = $request->input('loai_giao_dich');
                    if ($category->loai_danh_muc !== $loaiGiaoDich) {
                        $fail('Danh mục "' . $category->ten_danh_muc . '" là loại ' .
                            $category->loai_danh_muc . ', không khớp với giao dịch ' . $loaiGiaoDich . '!');
                    }
                }
            ],
            'loai_giao_dich'         => 'required|in:THU,CHI',
            'phuong_thuc_thanh_toan' => 'required|in:Tiền mặt,Chuyển khoản',
            'so_tien' => [
                'required', 'numeric', 'min:1000', 'max:100000000',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'ngay_giao_dich' => 'required|date|before_or_equal:today',
            'ghi_chu' => [
                'nullable', 'string', 'max:500',
                'regex:/^[\p{L}\p{N}\s\.,!?@#\-\(\)]*$/u',
            ],
        ], [
            'category_id.required'           => 'Vui lòng chọn danh mục',
            'category_id.exists'             => 'Danh mục không tồn tại',
            'loai_giao_dich.required'        => 'Vui lòng chọn loại giao dịch',
            'loai_giao_dich.in'              => 'Loại giao dịch không hợp lệ',
            'phuong_thuc_thanh_toan.required'=> 'Vui lòng chọn phương thức thanh toán',
            'phuong_thuc_thanh_toan.in'      => 'Phương thức thanh toán không hợp lệ',
            'so_tien.required'               => 'Vui lòng nhập số tiền',
            'so_tien.numeric'                => 'Số tiền phải là số hợp lệ',
            'so_tien.min'                    => 'Số tiền phải từ 1,000 VNĐ trở lên',
            'so_tien.max'                    => 'Số tiền không được vượt quá 100,000,000 VNĐ',
            'so_tien.regex'                  => 'Số tiền không hợp lệ',
            'ngay_giao_dich.required'        => 'Vui lòng chọn ngày giao dịch',
            'ngay_giao_dich.date'            => 'Ngày giao dịch không hợp lệ',
            'ngay_giao_dich.before_or_equal' => 'Ngày giao dịch không được là ngày trong tương lai',
            'ghi_chu.max'                    => 'Ghi chú không được vượt quá 500 ký tự',
            'ghi_chu.regex'                  => 'Ghi chú chứa ký tự không hợp lệ',
        ]);

        $validated['so_tien'] = trim($validated['so_tien']);
        $validated['ghi_chu'] = $validated['ghi_chu'] ? trim($validated['ghi_chu']) : null;

        DB::beginTransaction();
        try {
            $category = Category::where('id', $validated['category_id'])
                                ->where('user_id', Auth::id())
                                ->whereNotNull('danh_muc_cha_id')
                                ->first();

            if (!$category) {
                DB::rollBack();
                return response()->json(['message' => 'Chỉ có thể cập nhật giao dịch cho danh mục con!'], 422);
            }

            $oldAmount        = $transaction->so_tien;
            $oldType          = $transaction->loai_giao_dich;
            $oldMoneyWalletId = $transaction->money_wallet_id;
            $oldWalletId      = $transaction->wallet_id; // ← lấy wallet_id cũ trực tiếp

            // Bước 1: Hoàn tiền về budget CŨ (dùng wallet_id, không tìm theo category nữa)
            if ($oldWalletId) {
                $oldWallet = Budgets::where('id', $oldWalletId)
                                    ->where('user_id', Auth::id())
                                    ->lockForUpdate()
                                    ->first();

                if ($oldWallet) {
                    if ($oldType == 'THU') {
                        $oldWallet->decrement('so_du', $oldAmount);
                    } else {
                        $oldWallet->increment('so_du', $oldAmount);
                    }
                }
            }

            // Bước 2: Tìm budget ACTIVE của category mới để áp tiền vào
            $newWallet = Budgets::where('category_id', $validated['category_id'])
                                ->where('user_id', Auth::id())
                                ->where('trang_thai', true)
                                ->lockForUpdate()
                                ->first();

            if ($newWallet) {
                if ($validated['loai_giao_dich'] == 'CHI') {
                    if ($newWallet->so_du < $validated['so_tien']) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Ngân sách không đủ! Số dư hiện tại: '
                                . number_format($newWallet->so_du, 0, ',', '.') . 'đ'
                        ], 422);
                    }
                }

                if ($validated['loai_giao_dich'] == 'THU') {
                    $newWallet->increment('so_du', $validated['so_tien']);
                } else {
                    $newWallet->decrement('so_du', $validated['so_tien']);
                }
            }

            // Bước 3: Cập nhật giao dịch — gắn wallet_id mới
            $transaction->update([
                'category_id'            => $validated['category_id'],
                'wallet_id'              => $newWallet?->id, // ← cập nhật wallet_id mới
                'loai_giao_dich'         => $validated['loai_giao_dich'],
                'phuong_thuc_thanh_toan' => $validated['phuong_thuc_thanh_toan'],
                'so_tien'                => $validated['so_tien'],
                'ngay_giao_dich'         => $validated['ngay_giao_dich'],
                'ghi_chu'                => $validated['ghi_chu'],
                'money_wallet_id'        => $validated['money_wallet_id'] ?? null,
            ]);

            // Bước 4: Hoàn tiền về MoneyWallet cũ
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

            // Bước 5: Áp tiền vào MoneyWallet mới
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
            return response()->json(['message' => 'Cập nhật giao dịch thành công!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            // Dùng wallet_id trực tiếp — chính xác budget nào sở hữu giao dịch này
            if ($transaction->wallet_id) {
                $wallet = Budgets::where('id', $transaction->wallet_id)
                                ->where('user_id', Auth::id())
                                ->lockForUpdate()
                                ->first();

                if ($wallet) {
                    if ($transaction->loai_giao_dich == 'THU') {
                        if ($wallet->so_du < $transaction->so_tien) {
                            DB::rollBack();
                            return response()->json([
                                'message' => 'Không thể xóa giao dịch này vì sẽ làm số dư âm! Số dư hiện tại: ' .
                                    number_format($wallet->so_du, 0, ',', '.') . 'đ'
                            ], 422);
                        }
                        $wallet->decrement('so_du', $transaction->so_tien);
                    } else {
                        $wallet->increment('so_du', $transaction->so_tien);
                    }
                }
            }

            // Hoàn tiền MoneyWallet
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
            return response()->json(['message' => 'Xóa giao dịch thành công!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
}
