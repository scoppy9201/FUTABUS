<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SplitGroup;
use App\Models\SplitGroupMember;
use App\Models\GroupExpenseProposal;
use App\Models\GroupExpenseSplit;
use App\Observers\GroupNotifier;
use App\Models\GroupApproval;
use App\Models\Transaction;
use App\Models\Category;

class GroupExpenseController extends Controller
{
    // ── Danh sách khoản chi của nhóm ──────────────────────
    public function index(SplitGroup $group)
    {
        $userId = Auth::id();
        $this->assertMember($group);

        abort_if(
            !in_array($group->che_do, ['expense', 'both']),
            403,
            'Nhóm này không hỗ trợ chế độ chia khoản chi.'
        );

        // Đề xuất đang pending mà user chưa duyệt
        $myPending = GroupExpenseProposal::where('group_id', $group->id)
            ->where('trang_thai', 'pending')
            ->whereDoesntHave('approvals', fn($q) => $q->where('user_id', $userId))
            ->with(['proposer', 'splits.user', 'category'])
            ->get()
            ->map(fn($p) => $this->formatProposal($p, $userId));

        // Lịch sử đề xuất (20 gần nhất)
        $proposals = GroupExpenseProposal::where('group_id', $group->id)
            ->with(['proposer', 'splits.user', 'category'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($p) => $this->formatProposal($p, $userId));

        // Danh mục CHI của user để chọn khi tạo đề xuất
        $categories = Category::where('user_id', $userId)
            ->where('loai_danh_muc', 'CHI')
            ->where('trang_thai', true)
            ->whereNotNull('danh_muc_cha_id')
            ->orderBy('ten_danh_muc')
            ->get();

        $members = $group->activeMembers()->with('user')->get();

        if (request()->wantsJson()) {
            return response()->json([
                'group' => $group,
                'myPending' => $myPending,
                'proposals' => $proposals,
                'categories' => $categories,
                'members' => $members,
            ]);
        }

        return view('groups.expense.index', compact(
            'group', 'myPending', 'proposals', 'categories', 'members'
        ));
    }

    // ── Tạo đề xuất chia khoản chi ────────────────────────
    public function store(Request $request, SplitGroup $group)
    {
        $this->assertMember($group);

        abort_if(
            !in_array($group->che_do, ['expense', 'both']),
            403,
            'Nhóm này không hỗ trợ chế độ chia khoản chi.'
        );

        $memberIds = SplitGroupMember::where('group_id', $group->id)
            ->where('trang_thai', 'active')
            ->pluck('user_id')
            ->toArray();

        $validated = $request->validate([
            'mo_ta'               => 'required|string|max:255',
            'tong_tien'           => 'required|numeric|min:1000|max:100000000',
            'ngay_chi'            => 'required|date|before_or_equal:today',
            'kieu_chia'           => 'required|in:equal,custom,percentage',
            'category_id'         => 'nullable|exists:categories,id',
            'phan_bo'             => 'required|array|min:1',
            'phan_bo.*.user_id'   => 'required|integer|in:' . implode(',', $memberIds),
            'phan_bo.*.so_tien'   => 'required_if:kieu_chia,custom|nullable|numeric|min:0',
            'phan_bo.*.ty_le'     => 'required_if:kieu_chia,percentage|nullable|numeric|min:0|max:100',
        ], [
            'mo_ta.required'           => 'Vui lòng nhập mô tả khoản chi',
            'tong_tien.required'       => 'Vui lòng nhập tổng tiền',
            'tong_tien.min'            => 'Số tiền phải từ 1,000 VNĐ',
            'tong_tien.max'            => 'Số tiền không vượt quá 100 triệu',
            'ngay_chi.required'        => 'Vui lòng chọn ngày',
            'ngay_chi.before_or_equal' => 'Ngày không được là tương lai',
            'kieu_chia.required'       => 'Vui lòng chọn kiểu chia',
            'phan_bo.required'         => 'Vui lòng chọn ít nhất 1 thành viên',
        ]);

        $tongTien = (float) $validated['tong_tien'];
        $kieuChia = $validated['kieu_chia'];
        $phanBo   = collect($validated['phan_bo']);

        // ── Tính số tiền từng người theo kiểu chia ──────
        $splits = $this->calculateSplits($kieuChia, $tongTien, $phanBo);

        if (is_string($splits)) {
            // $splits trả về chuỗi lỗi
            if (request()->wantsJson()) {
                return response()->json(['message' => $splits], 422);
            }

            return back()->with('error', $splits)->withInput();
        }

        // Validate category thuộc user hiện tại nếu có
        if (!empty($validated['category_id'])) {
            $cat = Category::where('id', $validated['category_id'])
                ->where('user_id', Auth::id())
                ->where('loai_danh_muc', 'CHI')
                ->first();

            if (!$cat) {
                if (request()->wantsJson()) {
                    return response()->json(['message' => 'Danh mục không hợp lệ.'], 422);
                }

                return back()->with('error', 'Danh mục không hợp lệ.')->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $proposal = GroupExpenseProposal::create([
                'group_id'    => $group->id,
                'proposed_by' => Auth::id(),
                'category_id' => $validated['category_id'] ?? null,
                'mo_ta'       => trim($validated['mo_ta']),
                'tong_tien'   => $tongTien,
                'ngay_chi'    => $validated['ngay_chi'],
                'kieu_chia'   => $kieuChia,
                'trang_thai'  => 'pending',
            ]);

            foreach ($splits as $split) {
                GroupExpenseSplit::create([
                    'proposal_id'       => $proposal->id,
                    'user_id'           => $split['user_id'],
                    'so_tien'           => $split['so_tien'],
                    'ty_le'             => $split['ty_le'] ?? null,
                    'trang_thai_dong_y' => 'pending',
                ]);
            }

            // Người tạo tự động approve
            GroupApproval::create([
                'approvable_type' => GroupExpenseProposal::class,
                'approvable_id'   => $proposal->id,
                'user_id'         => Auth::id(),
                'quyet_dinh'      => 'approved',
                'created_at'      => now(),
            ]);

            // Thử thực thi nếu chỉ có 1 thành viên
            $this->tryExecuteProposal($proposal);

            DB::commit();

            GroupNotifier::expenseProposed($proposal->fresh());

            if (request()->wantsJson()) {
                return response()->json([
                    'message' => 'Đã tạo đề xuất chia chi. Chờ xác nhận.',
                    'proposal_id' => $proposal->id,
                    'redirect' => route('groups.expense.index', $group),
                ], 201);
            }

            return redirect()->route('groups.expense.index', $group)
                ->with('success', 'Đã tạo đề xuất chia chi. Chờ các thành viên xác nhận.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    // ── Đồng ý đề xuất ────────────────────────────────────
    public function approve(SplitGroup $group, GroupExpenseProposal $proposal)
    {
        $userId = Auth::id();

        $this->assertMember($group);
        $this->assertProposalPending($proposal);
        $this->assertNotYetResponded($proposal, $userId);

        DB::beginTransaction();
        try {
            GroupApproval::create([
                'approvable_type' => GroupExpenseProposal::class,
                'approvable_id'   => $proposal->id,
                'user_id'         => $userId,
                'quyet_dinh'      => 'approved',
                'created_at'      => now(),
            ]);

            GroupNotifier::expenseApproved($proposal, $userId);
            $this->tryExecuteProposal($proposal->fresh());

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    'message' => 'Đã xác nhận đồng ý khoản chi.',
                    'redirect' => route('groups.expense.index', $group),
                ]);
            }

            return redirect()->route('groups.expense.index', $group)
                ->with('success', 'Đã xác nhận đồng ý khoản chi.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // ── Từ chối đề xuất ───────────────────────────────────
    public function reject(Request $request, SplitGroup $group, GroupExpenseProposal $proposal)
    {
        $userId = Auth::id();

        $this->assertMember($group);
        $this->assertProposalPending($proposal);
        $this->assertNotYetResponded($proposal, $userId);

        $request->validate(['ghi_chu' => 'nullable|string|max:255']);

        DB::beginTransaction();
        try {
            GroupApproval::create([
                'approvable_type' => GroupExpenseProposal::class,
                'approvable_id'   => $proposal->id,
                'user_id'         => $userId,
                'quyet_dinh'      => 'rejected',
                'ghi_chu'         => $request->ghi_chu,
                'created_at'      => now(),
            ]);

            $proposal->update(['trang_thai' => 'rejected']);

            GroupNotifier::expenseRejected($proposal, $userId);

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    'message' => 'Đã từ chối đề xuất khoản chi.',
                    'redirect' => route('groups.expense.index', $group),
                ]);
            }

            return redirect()->route('groups.expense.index', $group)
                ->with('success', 'Đã từ chối đề xuất khoản chi.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // ── Huỷ đề xuất ───────────────────────────────────────
    public function cancel(SplitGroup $group, GroupExpenseProposal $proposal)
    {
        $this->assertMember($group);
        $this->assertProposalPending($proposal);

        // Chỉ người tạo hoặc admin mới được huỷ
        abort_if(
            $proposal->proposed_by !== Auth::id() && !$group->isAdmin(Auth::id()),
            403, 'Bạn không có quyền huỷ đề xuất này.'
        );

        $proposal->update(['trang_thai' => 'cancelled']);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Đã huỷ đề xuất khoản chi.', 'redirect' => route('groups.expense.index', $group)]);
        }

        return redirect()->route('groups.expense.index', $group)
            ->with('success', 'Đã huỷ đề xuất khoản chi.');
    }

    // ── TÍNH TOÁN PHÂN CHIA (internal) ───────────────────
    private function calculateSplits(string $kieuChia, float $tongTien, $phanBo): array|string
    {
        $splits = [];

        switch ($kieuChia) {

            case 'equal':
                // Chia đều — phan_bo chỉ cần có user_id
                $count    = $phanBo->count();
                $perPerson = round($tongTien / $count, 0);
                $remainder = $tongTien - ($perPerson * $count); // phần dư do làm tròn

                foreach ($phanBo as $i => $item) {
                    // Người đầu tiên chịu phần dư để tổng = tongTien
                    $soTien = $perPerson + ($i === 0 ? $remainder : 0);
                    $splits[] = [
                        'user_id' => $item['user_id'],
                        'so_tien' => $soTien,
                        'ty_le'   => null,
                    ];
                }
                break;

            case 'custom':
                // Nhập tay — kiểm tra tổng phải bằng tongTien
                $tongPhanBo = collect($phanBo)->sum('so_tien');
                if (abs($tongPhanBo - $tongTien) > 1) {
                    return "Tổng phân bổ ({$tongPhanBo} VND) phải bằng tổng tiền ({$tongTien} VND).";
                }
                foreach ($phanBo as $item) {
                    $splits[] = [
                        'user_id' => $item['user_id'],
                        'so_tien' => (float) $item['so_tien'],
                        'ty_le'   => null,
                    ];
                }
                break;

            case 'percentage':
                // Theo % — kiểm tra tổng % phải = 100
                $tongTyLe = collect($phanBo)->sum('ty_le');
                if (abs($tongTyLe - 100) > 0.01) {
                    return "Tổng tỷ lệ ({$tongTyLe}%) phải bằng 100%.";
                }
                $tongTinhDc = 0;
                $lastIndex  = $phanBo->count() - 1;

                foreach ($phanBo as $i => $item) {
                    if ($i === $lastIndex) {
                        // Người cuối chịu phần còn lại để tránh lệch do làm tròn
                        $soTien = $tongTien - $tongTinhDc;
                    } else {
                        $soTien = round($tongTien * $item['ty_le'] / 100, 0);
                        $tongTinhDc += $soTien;
                    }
                    $splits[] = [
                        'user_id' => $item['user_id'],
                        'so_tien' => $soTien,
                        'ty_le'   => (float) $item['ty_le'],
                    ];
                }
                break;
        }

        return $splits;
    }

    // ── THỰC THI ĐỀ XUẤT (internal) ───────────────────────
    private function tryExecuteProposal(GroupExpenseProposal $proposal): void
    {
        if (!$proposal->isPending()) return;
        if (!$proposal->isFullyApproved()) return;

        $group  = $proposal->group;
        $splits = $proposal->splits()->with('user')->get();

        foreach ($splits as $split) {
            if ($split->so_tien <= 0) continue;
            if (!$split->user) continue;


            // Lấy category CHI phù hợp cho từng user
            $category = $this->getOrCreateExpenseCategory(
                $split->user_id,
                $proposal->category_id
            );

            if (!$category) continue;

            $transaction = Transaction::create([
                'user_id'                => $split->user_id,
                'category_id'            => $category->id,
                'loai_giao_dich'         => 'CHI',
                'phuong_thuc_thanh_toan' => 'Tiền mặt',
                'so_tien'                => $split->so_tien,
                'ngay_giao_dich'         => $proposal->ngay_chi,
                'ghi_chu'                => 'Chi nhóm "' . $group->ten_nhom . '": ' . $proposal->mo_ta,
            ]);

            $split->update([
                'transaction_id'    => $transaction->id,
                'trang_thai_dong_y' => 'approved',
                'responded_at'      => now(),
            ]);
        }

        $proposal->update([
            'trang_thai'  => 'approved',
            'executed_at' => now(),
        ]);

        GroupNotifier::expenseExecuted($proposal->fresh());
    }

    // ── Lấy category CHI cho user ─────────────────────────
    // Ưu tiên dùng category từ đề xuất (nếu user đó có)
    // Nếu không thì tạo category "Chi nhóm" tự động
    private function getOrCreateExpenseCategory(int $userId, ?int $proposalCategoryId): ?Category
    {
        // Nếu category từ đề xuất thuộc về user này thì dùng luôn
        if ($proposalCategoryId) {
            $cat = Category::where('id', $proposalCategoryId)
                ->where('user_id', $userId)
                ->where('loai_danh_muc', 'CHI')
                ->whereNotNull('danh_muc_cha_id')
                ->first();

            if ($cat) return $cat;
        }

        // Tạo category tự động "Chi nhóm"
        $parent = Category::firstOrCreate(
            ['user_id' => $userId, 'ten_danh_muc' => 'Nhóm', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => '👥', 'trang_thai' => true]
        );

        return Category::firstOrCreate(
            ['user_id' => $userId, 'ten_danh_muc' => 'Chi nhóm', 'danh_muc_cha_id' => $parent->id],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => '🧾', 'trang_thai' => true]
        );
    }

    // ── Format proposal ────────────────────────────────────
    private function formatProposal(GroupExpenseProposal $proposal, int $userId): array
    {
        $myApproval    = $proposal->getUserApproval($userId);
        $approvedCount = $proposal->approvals()->where('quyet_dinh', 'approved')->count();
        $totalMembers  = SplitGroupMember::where('group_id', $proposal->group_id)
            ->where('trang_thai', 'active')->count();

        return [
            'id'             => $proposal->id,
            'mo_ta'          => $proposal->mo_ta,
            'tong_tien'      => $proposal->tong_tien,
            'ngay_chi'       => $proposal->ngay_chi ? \Carbon\Carbon::parse($proposal->ngay_chi) : now(),
            'kieu_chia'      => $proposal->kieu_chia,
            'trang_thai'     => $proposal->trang_thai,
            'category'       => $proposal->category?->ten_danh_muc,
            'proposed_by'    => $proposal->proposer?->name ?? 'Không rõ',
            'created_at'     => $proposal->created_at,
            'executed_at'    => $proposal->executed_at,
            'my_approval'    => $myApproval?->quyet_dinh,
            'approved_count' => $approvedCount,
            'total_members'  => $totalMembers,
            'splits'         => $proposal->splits->map(fn($s) => [
                'user_id' => $s->user_id,
                'name'    => $s->user?->name ?? 'Không rõ',
                'so_tien' => $s->so_tien,
                'ty_le'   => $s->ty_le,
            ]),
        ];
    }

    // ── Helpers ────────────────────────────────────────────

    private function assertProposalPending(GroupExpenseProposal $proposal): void
    {
        abort_if(!$proposal->isPending(), 422, 'Đề xuất không còn ở trạng thái chờ duyệt.');
    }

    private function assertNotYetResponded(GroupExpenseProposal $proposal, int $userId): void
    {
        abort_if($proposal->getUserApproval($userId) !== null, 422, 'Bạn đã phản hồi rồi.');
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
