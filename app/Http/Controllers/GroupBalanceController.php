<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SplitGroup;
use App\Models\SplitGroupMember;
use App\Models\GroupBalanceProposal;
use App\Models\GroupBalanceSplit;
use App\Models\GroupApproval;
use App\Observers\GroupNotifier;
use App\Models\Transaction;
use App\Models\Category;

class GroupBalanceController extends Controller
{
    // ── Xem tổng quan số dư nhóm + lịch sử đề xuất ────────
    public function index(SplitGroup $group)
    {
        $userId = Auth::id();
        $member = $this->assertMember($group);

        // Chỉ cho xem khi nhóm hỗ trợ chế độ balance
        abort_if(
            !in_array($group->che_do, ['balance', 'both']),
            403,
            'Nhóm này không hỗ trợ chế độ phân phối số dư.'
        );

        $group->load('activeMembers.user');

        // Số dư từng thành viên — chỉ hiển thị khi hien_so_du = true
        $members = $group->activeMembers->map(function ($m) use ($group) {
            $data = [
                'user_id'  => $m->user_id,
                'name'     => $m->user->name,
                'avatar'   => $m->user->avatar,
                'vai_tro'  => $m->vai_tro,
            ];
            if ($group->hien_so_du) {
                $data['so_du'] = $this->getUserBalance($m->user_id);
            }
            return $data;
        });

        // Tổng số dư toàn nhóm (dùng để validate khi tạo đề xuất)
        $tongSoDu = $group->hien_so_du
            ? $members->sum('so_du')
            : null;

        // Danh sách đề xuất gần nhất (10 cái)
        $proposals = GroupBalanceProposal::where('group_id', $group->id)
            ->with(['proposer', 'splits.user'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($p) => $this->formatProposal($p, $userId));

        // Đề xuất đang pending mà user chưa duyệt
        $myPending = GroupBalanceProposal::where('group_id', $group->id)
            ->where('trang_thai', 'pending')
            ->whereDoesntHave('approvals', fn($q) => $q->where('user_id', $userId))
            ->with('splits.user')
            ->get()
            ->map(fn($p) => $this->formatProposal($p, $userId));

        $laAdmin = $group->isAdmin(Auth::id());

        return view('groups.balance.index', compact(
            'group', 'members', 'tongSoDu', 'proposals', 'myPending', 'laAdmin'
        ));
    }

    // ── Tạo đề xuất phân phối số dư mới ───────────────────
    public function propose(Request $request, SplitGroup $group)
    {
        $this->assertAdmin($group);

        abort_if(
            !in_array($group->che_do, ['balance', 'both']),
            403,
            'Nhóm này không hỗ trợ chế độ phân phối số dư.'
        );

        abort_if(
            !$group->hien_so_du,
            403,
            'Cần bật hiển thị số dư trước khi tạo đề xuất phân phối.'
        );

        // Kiểm tra không có đề xuất pending nào đang tồn tại
        $hasPending = GroupBalanceProposal::where('group_id', $group->id)
            ->where('trang_thai', 'pending')
            ->exists();

        if ($hasPending) {
            return back()->with('error',
                'Đang có đề xuất chờ duyệt. Hãy huỷ hoặc chờ đề xuất đó hoàn tất trước.'
            );
        }

        $memberIds = SplitGroupMember::where('group_id', $group->id)
            ->where('trang_thai', 'active')
            ->pluck('user_id')
            ->toArray();

        // Validate: phân bổ cho đúng user_id trong nhóm
        $validated = $request->validate([
            'mo_ta'                => 'nullable|string|max:255',
            'phan_bo'              => 'required|array|min:1',
            'phan_bo.*.user_id'    => 'required|integer|in:' . implode(',', $memberIds),
            'phan_bo.*.so_du_moi'  => 'required|numeric|min:0|max:999999999',
        ], [
            'phan_bo.required'            => 'Vui lòng nhập phân bổ cho các thành viên',
            'phan_bo.*.user_id.in'        => 'Thành viên không hợp lệ',
            'phan_bo.*.so_du_moi.required' => 'Vui lòng nhập số dư mới',
            'phan_bo.*.so_du_moi.min'     => 'Số dư mới không được âm',
        ]);

        // Tính tổng số dư hiện tại của nhóm (snapshot tại thời điểm đề xuất)
        $tongSoDuHienTai = collect($memberIds)
            ->sum(fn($uid) => $this->getUserBalance($uid));

        // Tính tổng số dư mới từ đề xuất
        $tongSoDuMoi = collect($validated['phan_bo'])->sum('so_du_moi');

        // Validate: tổng phải bằng tổng hiện tại (sai lệch tối đa 1 VND do làm tròn)
        if (abs($tongSoDuMoi - $tongSoDuHienTai) > 1) {
            return back()->with('error',
                'Tổng số dư phân bổ (' . number_format($tongSoDuMoi) .
                ' VND) phải bằng tổng số dư hiện tại (' .
                number_format($tongSoDuHienTai) . ' VND).'
            )->withInput();
        }

        DB::beginTransaction();
        try {
            $proposal = GroupBalanceProposal::create([
                'group_id'    => $group->id,
                'proposed_by' => Auth::id(),
                'mo_ta'       => $validated['mo_ta'] ? trim($validated['mo_ta']) : null,
                'tong_so_du'  => $tongSoDuHienTai,
                'trang_thai'  => 'pending',
            ]);

            // Tạo split cho từng thành viên
            foreach ($validated['phan_bo'] as $item) {
                $soDuCu   = $this->getUserBalance((int) $item['user_id']);
                $soDuMoi  = (float) $item['so_du_moi'];
                $chenhLech = $soDuMoi - $soDuCu;

                GroupBalanceSplit::create([
                    'proposal_id'       => $proposal->id,
                    'user_id'           => $item['user_id'],
                    'so_du_cu'          => $soDuCu,
                    'so_du_moi'         => $soDuMoi,
                    'chenh_lech'        => $chenhLech,
                    'trang_thai_dong_y' => 'pending',
                ]);
            }

            // Người đề xuất tự động approve
            GroupApproval::create([
                'approvable_type' => GroupBalanceProposal::class,
                'approvable_id'   => $proposal->id,
                'user_id'         => Auth::id(),
                'quyet_dinh'      => 'approved',
                'created_at'      => now(),
            ]);

            // Nếu chỉ có 1 thành viên (chính admin) thì thực hiện luôn
            $this->tryExecuteProposal($proposal);
            GroupNotifier::balanceProposed($proposal->fresh());

            DB::commit();

            return redirect()->route('groups.balance.index', $group)
                ->with('success', 'Đã tạo đề xuất phân phối. Chờ các thành viên xác nhận.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    // ── Thành viên đồng ý đề xuất ─────────────────────────
    public function approve(SplitGroup $group, GroupBalanceProposal $proposal)
    {
        $userId = Auth::id();

        $this->assertMember($group);
        $this->assertProposalPending($proposal);
        $this->assertNotYetResponded($proposal, $userId);

        DB::beginTransaction();
        try {
            GroupApproval::create([
                'approvable_type' => GroupBalanceProposal::class,
                'approvable_id'   => $proposal->id,
                'user_id'         => $userId,
                'quyet_dinh'      => 'approved',
                'created_at'      => now(),
            ]);

            // Thử thực thi nếu tất cả đã approve
            GroupNotifier::balanceApproved($proposal, $userId);
            $this->tryExecuteProposal($proposal->fresh());

            DB::commit();

            return redirect()->route('groups.balance.index', $group)
                ->with('success', 'Đã xác nhận đồng ý phân phối.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // ── Thành viên từ chối đề xuất ─────────────────────────
    public function reject(Request $request, SplitGroup $group, GroupBalanceProposal $proposal)
    {
        $userId = Auth::id();

        $this->assertMember($group);
        $this->assertProposalPending($proposal);
        $this->assertNotYetResponded($proposal, $userId);

        $request->validate([
            'ghi_chu' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            GroupApproval::create([
                'approvable_type' => GroupBalanceProposal::class,
                'approvable_id'   => $proposal->id,
                'user_id'         => $userId,
                'quyet_dinh'      => 'rejected',
                'ghi_chu'         => $request->ghi_chu,
                'created_at'      => now(),
            ]);

            // Khi có 1 người reject → đề xuất bị từ chối ngay
            $proposal->update(['trang_thai' => 'rejected']);

            GroupNotifier::balanceRejected($proposal, $userId);

            DB::commit();

            return redirect()->route('groups.balance.index', $group)
                ->with('success', 'Đã từ chối đề xuất phân phối.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // ── Admin huỷ đề xuất ─────────────────────────────────
    public function cancel(SplitGroup $group, GroupBalanceProposal $proposal)
    {

        $this->assertAdmin($group);
        $this->assertProposalPending($proposal);

        // Chỉ người đề xuất hoặc admin mới được huỷ
        abort_if(
            $proposal->proposed_by !== Auth::id() && !$group->isAdmin(Auth::id()),
            403,
            'Bạn không có quyền huỷ đề xuất này.'
        );

        $proposal->update(['trang_thai' => 'cancelled']);

        return redirect()->route('groups.balance.index', $group)
            ->with('success', 'Đã huỷ đề xuất phân phối.');
    }

    // ── THỰC THI ĐỀ XUẤT (internal) ───────────────────────
    // Gọi sau mỗi lần approve — tự kiểm tra đủ điều kiện chưa
    private function tryExecuteProposal(GroupBalanceProposal $proposal): void
    {
        if (!$proposal->isPending()) return;
        if (!$proposal->isFullyApproved()) return;

        $group = $proposal->group;

        // Lấy danh mục mặc định để tạo Transaction
        // Dùng danh mục của người đề xuất — nếu không có thì bỏ qua Transaction
        $splits = $proposal->splits()->with('user')->get();

        foreach ($splits as $split) {
            // Chênh lệch = 0 thì bỏ qua, không cần tạo Transaction
            if (abs($split->chenh_lech) < 1) continue;

            $loai = $split->chenh_lech > 0 ? 'THU' : 'CHI';

            // Tìm category "Điều chỉnh số dư nhóm" của user này
            // Nếu chưa có thì tạo tự động
            $category = $this->getOrCreateAdjustCategory($split->user_id);

            if (!$category) continue; // bỏ qua nếu không tạo được category

            $transaction = Transaction::create([
                'user_id'               => $split->user_id,
                'category_id'           => $category->id,
                'loai_giao_dich'        => $loai,
                'phuong_thuc_thanh_toan'=> 'Chuyển khoản',
                'so_tien'               => abs($split->chenh_lech),
                'ngay_giao_dich'        => now()->toDateString(),
                'ghi_chu'               => 'Phân phối số dư nhóm "' . $group->ten_nhom . '"'
                    . ($proposal->mo_ta ? ': ' . $proposal->mo_ta : ''),
            ]);

            // Lưu transaction_id vào split
            $split->update([
                'transaction_id'    => $transaction->id,
                'trang_thai_dong_y' => 'approved',
                'responded_at'      => now(),
            ]);
        }

        // Đánh dấu proposal đã thực thi
        $proposal->update([
            'trang_thai'  => 'approved',
            'executed_at' => now(),
        ]);

        GroupNotifier::balanceExecuted($proposal->fresh());
    }

    // ── Tìm hoặc tạo category "Điều chỉnh số dư nhóm" ────
    private function getOrCreateAdjustCategory(int $userId): ?\App\Models\Category
    {
        // Tìm danh mục cha "Nhóm" trước
        $parent = Category::firstOrCreate(
            ['user_id' => $userId, 'ten_danh_muc' => 'Nhóm', 'danh_muc_cha_id' => null],
            [
                'loai_danh_muc' => 'THU', // cha không quan trọng loại
                'bieu_tuong'    => '👥',
                'trang_thai'    => true,
            ]
        );

        // Tìm danh mục con THU "Điều chỉnh nhóm"
        $catThu = Category::firstOrCreate(
            ['user_id' => $userId, 'ten_danh_muc' => 'Điều chỉnh nhóm (THU)', 'danh_muc_cha_id' => $parent->id],
            [
                'loai_danh_muc' => 'THU',
                'bieu_tuong'    => '💰',
                'trang_thai'    => true,
            ]
        );

        // Tìm danh mục con CHI "Điều chỉnh nhóm"
        Category::firstOrCreate(
            ['user_id' => $userId, 'ten_danh_muc' => 'Điều chỉnh nhóm (CHI)', 'danh_muc_cha_id' => $parent->id],
            [
                'loai_danh_muc' => 'CHI',
                'bieu_tuong'    => '💸',
                'trang_thai'    => true,
            ]
        );

        return $catThu; // trả về để caller tự chọn đúng loại
    }

    // ── Format proposal để trả về view ────────────────────
    private function formatProposal(GroupBalanceProposal $proposal, int $userId): array
    {
        $myApproval = $proposal->getUserApproval($userId);

        $approvedCount = $proposal->approvals()
            ->where('quyet_dinh', 'approved')->count();
        $totalMembers  = SplitGroupMember::where('group_id', $proposal->group_id)
            ->where('trang_thai', 'active')->count();

        return [
            'id'             => $proposal->id,
            'mo_ta'          => $proposal->mo_ta,
            'tong_so_du'     => $proposal->tong_so_du,
            'trang_thai'     => $proposal->trang_thai,
            'proposed_by'    => $proposal->proposer?->name ?? 'Không rõ',
            'created_at'     => $proposal->created_at,
            'executed_at'    => $proposal->executed_at,
            'my_approval'    => $myApproval?->quyet_dinh,
            'approved_count' => $approvedCount,
            'total_members'  => $totalMembers,
            'splits'         => $proposal->splits->map(fn($s) => [
                'user_id'    => $s->user_id,
                'name'       => $s->user?->name ?? 'Không rõ',
                'so_du_cu'   => $s->so_du_cu,
                'so_du_moi'  => $s->so_du_moi,
                'chenh_lech' => $s->chenh_lech,
            ]),
        ];
    }

    // ── Helpers ────────────────────────────────────────────

    private function getUserBalance(int $userId): float
    {
        $income  = Transaction::where('user_id', $userId)
                    ->where('loai_giao_dich', 'THU')->sum('so_tien');
        $expense = Transaction::where('user_id', $userId)
                    ->where('loai_giao_dich', 'CHI')->sum('so_tien');
        return (float) ($income - $expense);
    }

    private function assertProposalPending(GroupBalanceProposal $proposal): void
    {
        abort_if(
            !$proposal->isPending(),
            422,
            'Đề xuất này không còn ở trạng thái chờ duyệt.'
        );
    }

    private function assertNotYetResponded(GroupBalanceProposal $proposal, int $userId): void
    {
        $already = $proposal->getUserApproval($userId);
        abort_if($already !== null, 422, 'Bạn đã phản hồi đề xuất này rồi.');
    }

    protected function assertMember(SplitGroup $group): SplitGroupMember
    {
        $member = SplitGroupMember::where('group_id', $group->id)
            ->where('user_id', Auth::id())
            ->where('trang_thai', 'active')
            ->first();

        abort_if(!$member, 403, 'Bạn không phải thành viên của nhóm này.');

        return $member;
    }

    protected function assertAdmin(SplitGroup $group): void
    {
        $member = $this->assertMember($group);
        abort_if($member->vai_tro !== 'admin', 403, 'Chỉ admin mới có quyền thực hiện.');
    }
}
