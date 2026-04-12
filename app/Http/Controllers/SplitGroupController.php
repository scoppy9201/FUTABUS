<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SplitGroup;
use App\Models\SplitGroupMember;

class SplitGroupController extends Controller
{
    // ── Danh sách nhóm của user ────────────────────────────
    public function index()
    {
        $userId = Auth::id();

        $groups = SplitGroup::whereHas('activeMembers', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->with(['creator', 'activeMembers.user'])
                    ->where('trang_thai', 'active')
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(function ($group) use ($userId) {
                        $colors = ['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
                        $members = $group->activeMembers->take(4)->map(fn($m, $i) => [
                            'name'   => $m->user->name,
                            'avatar' => $m->user->avatar,
                            'color'  => $colors[$i % count($colors)],
                        ])->values();

                        return [
                            'id'            => $group->id,
                            'ten_nhom'      => $group->ten_nhom,
                            'mo_ta'         => $group->mo_ta,
                            'che_do'        => $group->che_do,
                            'hien_so_du'    => $group->hien_so_du,
                            'so_thanh_vien' => $group->activeMembers->count(),
                            'la_admin'      => $group->isAdmin($userId),
                            'nguoi_tao'     => $group->creator->name,
                            'created_at'    => $group->created_at,
                            'members'       => $members,
                        ];
                    });

        if (request()->wantsJson()) {
            return response()->json(['groups' => $groups]);
        }

        return view('groups.index', compact('groups'));
    }

    // ── Chi tiết nhóm ─────────────────────────────────────
    public function show(SplitGroup $group)
    {
        $userId = Auth::id();
        $this->assertMember($group);

        $group->load(['activeMembers.user', 'creator']);

        $laAdmin  = $group->isAdmin($userId);
        $hienSoDu = $group->hien_so_du;

        $members = $group->activeMembers->map(function ($m) use ($hienSoDu) {
            $data = [
                'id'        => $m->id,
                'user_id'   => $m->user_id,
                'name'      => $m->user->name,
                'email'     => $m->user->email,
                'avatar'    => $m->user->avatar,
                'vai_tro'   => $m->vai_tro,
                'joined_at' => $m->joined_at,
            ];

            // Chỉ kèm số dư khi nhóm bật hien_so_du
            if ($hienSoDu) {
                $data['so_du'] = $this->getUserBalance($m->user_id);
            }

            return $data;
        });

        // Đề xuất đang pending (để hiện badge thông báo)
        $pendingBalance = $group->balanceProposals()
            ->where('trang_thai', 'pending')->count();
        $pendingExpense = $group->expenseProposals()
            ->where('trang_thai', 'pending')->count();

        if (request()->wantsJson()) {
            return response()->json([
                'group' => $group,
                'members' => $members,
                'laAdmin' => $laAdmin,
                'hienSoDu' => $hienSoDu,
                'pendingBalance' => $pendingBalance,
                'pendingExpense' => $pendingExpense,
            ]);
        }

        return view('groups.show', compact(
            'group', 'members', 'laAdmin', 'hienSoDu',
            'pendingBalance', 'pendingExpense'
        ));
    }

    // ── Tạo nhóm mới ──────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_nhom' => 'required|string|max:100',
            'mo_ta'    => 'nullable|string|max:255',
            'che_do'   => 'required|in:balance,expense,both',
        ], [
            'ten_nhom.required' => 'Vui lòng nhập tên nhóm',
            'ten_nhom.max'      => 'Tên nhóm tối đa 100 ký tự',
            'che_do.required'   => 'Vui lòng chọn chế độ',
            'che_do.in'         => 'Chế độ không hợp lệ',
        ]);

        DB::beginTransaction();
        try {
            $group = SplitGroup::create([
                'created_by' => Auth::id(),
                'ten_nhom'   => trim($validated['ten_nhom']),
                'mo_ta'      => $validated['mo_ta'] ? trim($validated['mo_ta']) : null,
                'che_do'     => $validated['che_do'],
                'hien_so_du' => false,
                'trang_thai' => 'active',
            ]);

            // Người tạo tự động là admin
            SplitGroupMember::create([
                'group_id'   => $group->id,
                'user_id'    => Auth::id(),
                'vai_tro'    => 'admin',
                'trang_thai' => 'active',
                'joined_at'  => now(),
            ]);

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    'message' => "Tạo nhóm \"{$group->ten_nhom}\" thành công!",
                    'group' => $group,
                    'redirect' => route('groups.show', $group),
                ], 201);
            }

            return redirect()->route('groups.show', $group)
                ->with('success', "Tạo nhóm \"{$group->ten_nhom}\" thành công!");

        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    // ── Cập nhật nhóm ─────────────────────────────────────
    public function update(Request $request, SplitGroup $group)
    {
        $this->assertAdmin($group);

        $validated = $request->validate([
            'ten_nhom' => 'required|string|max:100',
            'mo_ta'    => 'nullable|string|max:255',
            'che_do'   => 'required|in:balance,expense,both',
        ]);

        $group->update([
            'ten_nhom' => trim($validated['ten_nhom']),
            'mo_ta'    => $validated['mo_ta'] ? trim($validated['mo_ta']) : null,
            'che_do'   => $validated['che_do'],
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Cập nhật nhóm thành công!',
                'group' => $group,
                'redirect' => route('groups.show', $group),
            ]);
        }

        return redirect()->route('groups.show', $group)
            ->with('success', 'Cập nhật nhóm thành công!');
    }

    // ── Archive nhóm (không xóa cứng) ─────────────────────
    public function destroy(SplitGroup $group)
    {
        $this->assertAdmin($group);

        $group->update(['trang_thai' => 'archived']);

        if (request()->wantsJson()) {
            return response()->json([
                'message' => "Đã lưu trữ nhóm \"{$group->ten_nhom}\".",
                'group_id' => $group->id,
                'redirect' => route('groups.index'),
            ]);
        }

        return redirect()->route('groups.index')
            ->with('success', "Đã lưu trữ nhóm \"{$group->ten_nhom}\".");
    }

    // ── Bật/tắt hiển thị số dư ────────────────────────────
    public function toggleBalanceVisibility(SplitGroup $group)
    {
        $this->assertAdmin($group);

        if (!in_array($group->che_do, ['balance', 'both'])) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Nhóm này không hỗ trợ chế độ phân phối số dư.'], 422);
            }

            return back()->with('error', 'Nhóm này không hỗ trợ chế độ phân phối số dư.');
        }

        $group->update(['hien_so_du' => !$group->hien_so_du]);

        $msg = $group->fresh()->hien_so_du
            ? 'Đã bật hiển thị số dư cho các thành viên.'
            : 'Đã tắt hiển thị số dư.';

        if (request()->wantsJson()) {
            return response()->json(['message' => $msg, 'hien_so_du' => $group->fresh()->hien_so_du]);
        }

        return back()->with('success', $msg);
    }

    public function searchUsers(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $exclude = array_filter(explode(',', $request->get('exclude', '')));

        if (strlen($q) < 1) {
            return response()->json(['users' => []]);
        }

        // Escape ký tự đặc biệt trong LIKE
        $qEsc = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);

        $users = \App\Models\User::where(function ($query) use ($qEsc) {
                $query->where('name',  'like', '%' . $qEsc . '%')
                      ->orWhere('email', 'like', '%' . $qEsc . '%');
            })
            ->when(!empty($exclude), fn($q) => $q->whereNotIn('id', $exclude))
            ->where('id', '!=', Auth::id())
            ->select('id', 'name', 'email', 'avatar')
            ->limit(8)
            ->get()
            ->map(fn($u) => [
                'id'     => $u->id,
                'name'   => $u->name,
                'email'  => $u->email,
                'avatar' => $u->avatar,
            ]);

        return response()->json(['users' => $users]);
    }

    // ── Helpers ────────────────────────────────────────────

    private function getUserBalance(int $userId): float
    {
        $income  = \App\Models\Transaction::where('user_id', $userId)
                    ->where('loai_giao_dich', 'THU')->sum('so_tien');
        $expense = \App\Models\Transaction::where('user_id', $userId)
                    ->where('loai_giao_dich', 'CHI')->sum('so_tien');
        return (float) ($income - $expense);
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
