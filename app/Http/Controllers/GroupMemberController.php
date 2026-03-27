<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\SplitGroup;
use App\Models\SplitGroupMember;
use App\Models\GroupInvitation;
use App\Models\User;
use App\Observers\GroupNotifier;

class GroupMemberController extends Controller
{
    // ── Gửi lời mời qua email ─────────────────────────────
    public function invite(Request $request, SplitGroup $group)
    {
        $this->assertAdmin($group);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Vui lòng nhập email',
            'email.email'    => 'Email không hợp lệ',
            'email.exists'   => 'Email này chưa có tài khoản Monexa',
        ]);

        $email       = strtolower(trim($validated['email']));
        $invitedUser = User::where('email', $email)->first();

        // Không mời chính mình
        if ($invitedUser->id === Auth::id()) {
            return back()->with('error', 'Bạn không thể mời chính mình.');
        }

        // Đã là thành viên active?
        $alreadyMember = SplitGroupMember::where('group_id', $group->id)
            ->where('user_id', $invitedUser->id)
            ->where('trang_thai', 'active')
            ->exists();

        if ($alreadyMember) {
            return back()->with('error', "{$invitedUser->name} đã là thành viên của nhóm.");
        }

        // Đã có lời mời pending còn hiệu lực?
        $existingInvite = GroupInvitation::where('group_id', $group->id)
            ->where('email', $email)
            ->where('trang_thai', 'pending')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existingInvite) {
            return back()->with('error', "Đã có lời mời đang chờ xác nhận cho {$email}.");
        }

        DB::beginTransaction();
        try {
            // Hủy lời mời cũ đã expired
            GroupInvitation::where('group_id', $group->id)
                ->where('email', $email)
                ->where('trang_thai', 'pending')
                ->update(['trang_thai' => 'expired']);

            $invitation = GroupInvitation::create([
                'group_id'   => $group->id,
                'invited_by' => Auth::id(),
                'email'      => $email,
                'token'      => Str::random(64),
                'trang_thai' => 'pending',
                'expires_at' => now()->addHours(48),
            ]);

            // Gửi email lời mời
            Mail::send('groups.emails.invitation', [
                'inviterName' => Auth::user()->name,
                'groupName'   => $group->ten_nhom,
                'acceptUrl'   => route('groups.invite.accept', $invitation->token),
                'declineUrl'  => route('groups.invite.decline', $invitation->token),
                'expiresAt'   => $invitation->expires_at->format('d/m/Y H:i'),
            ], function ($message) use ($email, $group) {
                $message->to($email);
                $message->subject("Lời mời tham gia nhóm \"{$group->ten_nhom}\" trên Monexa");
            });

            DB::commit();

            GroupNotifier::invited($group, $email, Auth::user()->name);

            return back()->with('success', "Đã gửi lời mời đến {$email}. Hết hạn sau 48 giờ.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi khi gửi lời mời: ' . $e->getMessage());
        }
    }

    // ── Chấp nhận lời mời ────────────────────────────────
    public function accept(string $token)
    {
        $invitation = GroupInvitation::where('token', $token)->firstOrFail();

        // Email phải khớp với user đang đăng nhập
        if ($invitation->email !== Auth::user()->email) {
            return redirect()->route('groups.index')
                ->with('error', 'Lời mời này không dành cho tài khoản của bạn.');
        }

        if (!$invitation->isUsable()) {
            $msg = $invitation->isExpired()
                ? 'Lời mời đã hết hạn (48 giờ). Vui lòng yêu cầu admin mời lại.'
                : 'Lời mời này không còn hiệu lực.';
            return redirect()->route('groups.index')->with('error', $msg);
        }

        DB::beginTransaction();
        try {
            $group = $invitation->group;

            // Nếu đã từng là thành viên (đã rời) thì tái kích hoạt
            $existingMember = SplitGroupMember::where('group_id', $group->id)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingMember) {
                $existingMember->update([
                    'vai_tro'    => 'member',
                    'trang_thai' => 'active',
                    'joined_at'  => now(),
                    'left_at'    => null,
                ]);
            } else {
                SplitGroupMember::create([
                    'group_id'   => $group->id,
                    'user_id'    => Auth::id(),
                    'vai_tro'    => 'member',
                    'trang_thai' => 'active',
                    'joined_at'  => now(),
                ]);
            }

            $invitation->update([
                'trang_thai'   => 'accepted',
                'responded_at' => now(),
            ]);

            DB::commit();

             $newMem = $existingMember ?? SplitGroupMember::where('group_id', $group->id)
                ->where('user_id', Auth::id())->first();
            if ($newMem) GroupNotifier::memberJoined($group, $newMem);

            return redirect()->route('groups.show', $group)
                ->with('success', "Chào mừng bạn đã gia nhập nhóm \"{$group->ten_nhom}\"!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('groups.index')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // ── Từ chối lời mời ──────────────────────────────────
    public function decline(string $token)
    {
        $invitation = GroupInvitation::where('token', $token)->firstOrFail();

        if ($invitation->email !== Auth::user()->email) {
            return redirect()->route('groups.index')
                ->with('error', 'Lời mời này không dành cho tài khoản của bạn.');
        }

        if (!$invitation->isUsable()) {
            return redirect()->route('groups.index')
                ->with('error', 'Lời mời này không còn hiệu lực.');
        }

        $invitation->update([
            'trang_thai'   => 'declined',
            'responded_at' => now(),
        ]);

        return redirect()->route('groups.index')
            ->with('success', 'Đã từ chối lời mời tham gia nhóm.');
    }

    // ── Admin xóa thành viên ──────────────────────────────
    public function remove(SplitGroup $group, SplitGroupMember $member)
    {
        $this->assertAdmin($group);

        abort_if($member->group_id !== $group->id, 404);

        if ($member->user_id === Auth::id()) {
            return back()->with('error', 'Dùng chức năng "Rời nhóm" để tự rời.');
        }

        if ($member->vai_tro === 'admin') {
            return back()->with('error', 'Không thể xóa admin. Hãy hạ quyền thành member trước.');
        }

        $member->update([
            'trang_thai' => 'left',
            'left_at'    => now(),
        ]);

        GroupNotifier::memberRemoved($group, $member);

        return back()->with('success', "Đã xóa {$member->user->name} khỏi nhóm.");
    }

    // ── Tự rời nhóm ──────────────────────────────────────
    public function leave(SplitGroup $group)
    {
        $member = $this->assertMember($group);

        // Admin duy nhất không được rời
        if ($member->vai_tro === 'admin') {
            $adminCount = SplitGroupMember::where('group_id', $group->id)
                ->where('trang_thai', 'active')
                ->where('vai_tro', 'admin')
                ->count();

            if ($adminCount <= 1) {
                return back()->with('error',
                    'Bạn là admin duy nhất. Hãy chỉ định admin khác trước khi rời nhóm.'
                );
            }
        }

        $member->update([
            'trang_thai' => 'left',
            'left_at'    => now(),
        ]);

        GroupNotifier::memberLeft($group, $member);

        return redirect()->route('groups.index')
            ->with('success', "Bạn đã rời nhóm \"{$group->ten_nhom}\".");
    }

    // ── Chỉ định Admin ───────────────────────────────────
    public function promote(SplitGroup $group, SplitGroupMember $member)
    {
        $this->assertAdmin($group);
        abort_if($member->group_id !== $group->id, 404);
        abort_if($member->trang_thai !== 'active', 422, 'Thành viên không còn trong nhóm.');

        $member->update(['vai_tro' => 'admin']);

        GroupNotifier::promoted($group, $member);

        return back()->with('success', "{$member->user->name} đã được chỉ định làm Admin.");
    }

    // ── Hạ quyền Admin → Member ──────────────────────────
    public function demote(SplitGroup $group, SplitGroupMember $member)
    {
        $this->assertAdmin($group);
        abort_if($member->group_id !== $group->id, 404);

        // Không cho hạ quyền chính mình
        if ($member->user_id === Auth::id()) {
            return back()->with('error', 'Không thể hạ quyền chính mình. Hãy dùng chức năng Rời nhóm.');
        }

        // Đảm bảo vẫn còn ít nhất 1 admin
        $adminCount = SplitGroupMember::where('group_id', $group->id)
            ->where('trang_thai', 'active')
            ->where('vai_tro', 'admin')
            ->count();

        if ($adminCount <= 1) {
            return back()->with('error', 'Nhóm phải có ít nhất 1 Admin.');
        }

        $member->update(['vai_tro' => 'member']);

        GroupNotifier::demoted($group, $member);

        return back()->with('success', "Đã hạ quyền {$member->user->name} xuống Member.");
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

    protected function assertAdmin(SplitGroup $group): void
    {
        $member = $this->assertMember($group);
        abort_if($member->vai_tro !== 'admin', 403, 'Chỉ admin mới có quyền thực hiện.');
    }
}
