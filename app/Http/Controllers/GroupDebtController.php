<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SplitGroup;
use App\Models\SplitGroupMember;
use App\Models\GroupExpenseDebt;
use App\Observers\GroupNotifier;
use App\Models\Transaction;
use App\Models\Category;

class GroupDebtController extends Controller
{
    // ── Ghi nợ thẳng: A nợ B bao nhiêu ───────────────────
    public function store(Request $request, SplitGroup $group)
    {
        $this->assertMember($group);

        abort_if(
            !in_array($group->che_do, ['expense', 'both']),
            403,
            'Nhóm này không hỗ trợ chế độ ghi nợ.'
        );

        $memberIds = SplitGroupMember::where('group_id', $group->id)
            ->where('trang_thai', 'active')
            ->pluck('user_id')
            ->toArray();

        $validated = $request->validate([
            'nguoi_no_id' => 'required|integer|in:' . implode(',', $memberIds),
            'chu_no_id'   => 'required|integer|in:' . implode(',', $memberIds),
            'so_tien'     => 'required|numeric|min:1000|max:100000000',
            'ghi_chu'     => 'nullable|string|max:255',
        ], [
            'nguoi_no_id.required' => 'Vui lòng chọn người nợ',
            'nguoi_no_id.in'       => 'Người nợ phải là thành viên nhóm',
            'chu_no_id.required'   => 'Vui lòng chọn chủ nợ',
            'chu_no_id.in'         => 'Chủ nợ phải là thành viên nhóm',
            'so_tien.required'     => 'Vui lòng nhập số tiền',
            'so_tien.min'          => 'Số tiền phải từ 1,000 VNĐ',
        ]);

        if ($validated['nguoi_no_id'] == $validated['chu_no_id']) {
            return back()->with('error', 'Người nợ và chủ nợ không được là cùng 1 người.');
        }

        DB::beginTransaction();
        try {
            GroupExpenseDebt::create([
                'group_id'    => $group->id,
                'chu_no_id'   => $validated['chu_no_id'],
                'nguoi_no_id' => $validated['nguoi_no_id'],
                'so_tien'     => $validated['so_tien'],
                'ghi_chu'     => $validated['ghi_chu'] ? trim($validated['ghi_chu']) : null,
                'trang_thai'  => 'confirmed', // ghi thẳng = không cần confirm
            ]);

            DB::commit();

            GroupNotifier::debtRecorded($debt->fresh());

            return redirect()->route('groups.debt.summary', $group)
                ->with('success', 'Đã ghi nhận khoản nợ.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // ── Tổng kết nợ + thuật toán rút gọn ─────────────────
    public function summary(SplitGroup $group)
    {
        $this->assertMember($group);

        abort_if(
            !in_array($group->che_do, ['expense', 'both']),
            403,
            'Nhóm này không hỗ trợ chế độ ghi nợ.'
        );

        $group->load('activeMembers.user');
        $members = $group->activeMembers->keyBy('user_id');

        // Lấy tất cả nợ confirmed (chưa settled) trong nhóm
        $debts = GroupExpenseDebt::where('group_id', $group->id)
            ->where('trang_thai', 'confirmed')
            ->get();

        // ── Bước 1: Tính net balance từng cặp ─────────────
        // rawDebts[A][B] = tổng A nợ B
        $rawDebts = [];
        foreach ($debts as $d) {
            $from = $d->nguoi_no_id;
            $to   = $d->chu_no_id;
            $rawDebts[$from][$to] = ($rawDebts[$from][$to] ?? 0) + $d->so_tien;
        }

        // ── Bước 2: Tính net balance mỗi người ────────────
        // balance > 0 = đang được nợ, balance < 0 = đang nợ
        $balances = [];
        foreach ($members as $userId => $member) {
            $balances[$userId] = 0;
        }

        foreach ($rawDebts as $from => $tos) {
            foreach ($tos as $to => $amount) {
                // Trừ qua lại để tính net
                $net = $amount - ($rawDebts[$to][$from] ?? 0);
                if ($net > 0) {
                    $balances[$from] = ($balances[$from] ?? 0) - $net;
                    $balances[$to]   = ($balances[$to] ?? 0) + $net;
                }
            }
        }

        // ── Bước 3: Debt Simplification ───────────────────
        $simplified = $this->simplifyDebts($balances, $members);

        // ── Tổng hợp để hiển thị ──────────────────────────
        // Nợ gốc (raw) để hiển thị lịch sử
        $rawList = $debts->map(fn($d) => [
            'id'               => $d->id,
            'nguoi_no'         => $members[$d->nguoi_no_id]?->user?->name ?? 'Không rõ',
            'chu_no'           => $members[$d->chu_no_id]?->user?->name ?? 'Không rõ',
            // Thêm avatar để view render đồng bộ với profile
            'nguoi_no_avatar'  => $members[$d->nguoi_no_id]?->user?->avatar ?? null,
            'chu_no_avatar'    => $members[$d->chu_no_id]?->user?->avatar ?? null,
            'so_tien'          => $d->so_tien,
            'ghi_chu'          => $d->ghi_chu,
            'trang_thai'       => $d->trang_thai,
            'created_at'       => $d->created_at,
        ]);

        return view('groups.debt.summary', compact(
            'group', 'members', 'simplified', 'rawList', 'balances'
        ));
    }

    // ── Thanh toán nợ ─────────────────────────────────────
    public function settle(Request $request, SplitGroup $group, GroupExpenseDebt $debt)
    {
        $userId = Auth::id();

        $this->assertMember($group);

        // Chỉ chủ nợ hoặc người nợ mới được settle
        abort_if(
            $debt->chu_no_id !== $userId && $debt->nguoi_no_id !== $userId,
            403,
            'Bạn không liên quan đến khoản nợ này.'
        );

        abort_if(
            $debt->trang_thai === 'settled',
            422,
            'Khoản nợ này đã được thanh toán rồi.'
        );

        $validated = $request->validate([
            'ghi_vao_so' => 'boolean', // true = tạo Transaction, false = chỉ đánh dấu
        ]);

        DB::beginTransaction();
        try {
            if (!empty($validated['ghi_vao_so'])) {
                // Tạo Transaction CHI cho người nợ
                $category = $this->getOrCreateDebtCategory($debt->nguoi_no_id);

                if ($category) {
                    Transaction::create([
                        'user_id'                => $debt->nguoi_no_id,
                        'category_id'            => $category->id,
                        'loai_giao_dich'         => 'CHI',
                        'phuong_thuc_thanh_toan' => 'Chuyển khoản',
                        'so_tien'                => $debt->so_tien,
                        'ngay_giao_dich'         => now()->toDateString(),
                        'ghi_chu'                => 'Trả nợ nhóm "' . $group->ten_nhom . '"'
                            . ($debt->ghi_chu ? ': ' . $debt->ghi_chu : ''),
                    ]);
                }

                // Tạo Transaction THU cho chủ nợ
                $catThu = $this->getOrCreateDebtReceiveCategory($debt->chu_no_id);

                if ($catThu) {
                    Transaction::create([
                        'user_id'                => $debt->chu_no_id,
                        'category_id'            => $catThu->id,
                        'loai_giao_dich'         => 'THU',
                        'phuong_thuc_thanh_toan' => 'Chuyển khoản',
                        'so_tien'                => $debt->so_tien,
                        'ngay_giao_dich'         => now()->toDateString(),
                        'ghi_chu'                => 'Nhận nợ từ nhóm "' . $group->ten_nhom . '"'
                            . ($debt->ghi_chu ? ': ' . $debt->ghi_chu : ''),
                    ]);
                }
            }

            $debt->update([
                'trang_thai' => 'settled',
                'settled_at' => now(),
            ]);

            DB::commit();

            GroupNotifier::debtSettled($debt->fresh());

            return redirect()->route('groups.debt.summary', $group)
                ->with('success', 'Đã đánh dấu khoản nợ là đã thanh toán.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // ── THUẬT TOÁN RÚT GỌN NỢ ─────────────────────────────
    // Input:  balances[user_id] = net (dương = được nợ, âm = đang nợ)
    // Output: mảng ['from' => id, 'to' => id, 'amount' => số tiền, 'from_name', 'to_name']
    private function simplifyDebts(array $balances, $members): array
    {
        $result = [];

        // Tách thành 2 nhóm
        $creditors = []; // đang được nợ (balance > 0)
        $debtors   = []; // đang nợ      (balance < 0)

        foreach ($balances as $userId => $balance) {
            if ($balance > 1) {
                $creditors[] = ['user_id' => $userId, 'amount' => $balance];
            } elseif ($balance < -1) {
                $debtors[] = ['user_id' => $userId, 'amount' => abs($balance)];
            }
        }

        // Sắp xếp giảm dần để ghép cặp lớn nhất trước
        usort($creditors, fn($a, $b) => $b['amount'] <=> $a['amount']);
        usort($debtors,   fn($a, $b) => $b['amount'] <=> $a['amount']);

        $i = 0; // index creditors
        $j = 0; // index debtors

        while ($i < count($creditors) && $j < count($debtors)) {
            $credit = $creditors[$i]['amount'];
            $debt   = $debtors[$j]['amount'];
            $amount = min($credit, $debt);

            if ($amount > 1) {
                $fromId = $debtors[$j]['user_id'];
                $toId   = $creditors[$i]['user_id'];

                $result[] = [
                    'from'      => $fromId,
                    'to'        => $toId,
                    'amount'    => round($amount),
                    'from_name' => $members[$fromId]->user->name ?? '?',
                    'to_name'   => $members[$toId]->user->name ?? '?',
                ];
            }

            // Trừ đi phần đã xử lý
            $creditors[$i]['amount'] -= $amount;
            $debtors[$j]['amount']   -= $amount;

            if ($creditors[$i]['amount'] < 1) $i++;
            if ($debtors[$j]['amount'] < 1)   $j++;
        }

        return $result;
    }

    // ── Tạo category tự động cho trả nợ ───────────────────
    private function getOrCreateDebtCategory(int $userId): ?Category
    {
        $parent = Category::firstOrCreate(
            ['user_id' => $userId, 'ten_danh_muc' => 'Nhóm', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => '👥', 'trang_thai' => true]
        );

        return Category::firstOrCreate(
            ['user_id' => $userId, 'ten_danh_muc' => 'Trả nợ nhóm', 'danh_muc_cha_id' => $parent->id],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => '💳', 'trang_thai' => true]
        );
    }

    private function getOrCreateDebtReceiveCategory(int $userId): ?Category
    {
        $parent = Category::firstOrCreate(
            ['user_id' => $userId, 'ten_danh_muc' => 'Nhóm', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'THU', 'bieu_tuong' => '👥', 'trang_thai' => true]
        );

        return Category::firstOrCreate(
            ['user_id' => $userId, 'ten_danh_muc' => 'Thu nợ nhóm', 'danh_muc_cha_id' => $parent->id],
            ['loai_danh_muc' => 'THU', 'bieu_tuong' => '🤝', 'trang_thai' => true]
        );
    }

    // ── Helpers ────────────────────────────────────────────

    protected function assertMember(SplitGroup $group): SplitGroupMember
    {
        $member = SplitGroupMember::where('group_id', $group->id)
            ->where('user_id', Auth::id())
            ->where('trang_thai', 'active')
            ->first();

        abort_if(!$member, 403, 'Bạn không phải thành viên của nhóm này.');

        return $member;
    }
}
