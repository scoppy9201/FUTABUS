<?php

namespace App\Observers;

use App\Models\SplitGroup;
use App\Models\SplitGroupMember;
use App\Models\GroupBalanceProposal;
use App\Models\GroupExpenseProposal;
use App\Models\GroupExpenseDebt;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

/**
 * Không phải Observer thuần — là helper static methods
 * được gọi trực tiếp từ các Controller sau khi thực hiện action
 * (vì Group notifications cần context phức tạp hơn observer thông thường)
 */
class GroupNotifier
{
    // ── Mời thành viên ─────────────────────────────────────
    public static function invited(SplitGroup $group, string $email, string $inviterName): void
    {
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) return;

        // Lấy token của invitation mới nhất
        $invitation = \App\Models\GroupInvitation::where('group_id', $group->id)
            ->where('email', $email)
            ->where('trang_thai', 'pending')
            ->latest()
            ->first();

        // URL dạng accept để frontend lấy token
        $acceptUrl = $invitation
            ? route('groups.invite.accept', $invitation->token)
            : route('groups.index');

        NotificationService::send(
            userId:     $user->id,
            loai:       'group_invited',
            tieuDe:     $inviterName,
            noiDung:    "đã mời bạn tham gia nhóm \"{$group->ten_nhom}\"",
            url:        $acceptUrl,  // ← lưu URL accept (có token)
            actorId:    Auth::id(),
            entityType: SplitGroup::class,
            entityId:   $group->id,
        );
    }

    // ── Thành viên mới tham gia ────────────────────────────
    public static function memberJoined(SplitGroup $group, SplitGroupMember $newMember): void
    {
        $newUser     = $newMember->user;
        $memberIds   = $group->activeMembers()
            ->where('user_id', '!=', $newMember->user_id)
            ->pluck('user_id')->toArray();

        NotificationService::sendMany(
            userIds:    $memberIds,
            loai:       'group_joined',
            tieuDe:     $newUser->name,
            noiDung:    "đã tham gia nhóm \"{$group->ten_nhom}\"",
            url:        route('groups.show', $group),
            actorId:    $newMember->user_id,
            entityType: SplitGroup::class,
            entityId:   $group->id,
        );
    }

    // ── Thành viên rời nhóm ────────────────────────────────
    public static function memberLeft(SplitGroup $group, SplitGroupMember $member): void
    {
        $memberIds = $group->activeMembers()
            ->where('user_id', '!=', $member->user_id)
            ->pluck('user_id')->toArray();

        NotificationService::sendMany(
            userIds:    $memberIds,
            loai:       'group_left',
            tieuDe:     $member->user->name,
            noiDung:    "đã rời khỏi nhóm \"{$group->ten_nhom}\"",
            url:        route('groups.show', $group),
            actorId:    $member->user_id,
            entityType: SplitGroup::class,
            entityId:   $group->id,
        );
    }

    // ── Xóa thành viên ─────────────────────────────────────
    public static function memberRemoved(SplitGroup $group, SplitGroupMember $member): void
    {
        // Thông báo cho người bị xóa
        NotificationService::send(
            userId:     $member->user_id,
            loai:       'group_removed',
            tieuDe:     'Bạn đã bị xóa khỏi nhóm',
            noiDung:    "Admin đã xóa bạn khỏi nhóm \"{$group->ten_nhom}\"",
            url:        route('groups.index'),
            actorId:    Auth::id(),
            entityType: SplitGroup::class,
            entityId:   $group->id,
        );
    }

    // ── Chỉ định / Hạ quyền Admin ──────────────────────────
    public static function promoted(SplitGroup $group, SplitGroupMember $member): void
    {
        NotificationService::send(
            userId:     $member->user_id,
            loai:       'group_promoted',
            tieuDe:     'Bạn được nâng lên Admin',
            noiDung:    "Bạn đã được chỉ định làm Admin nhóm \"{$group->ten_nhom}\"",
            url:        route('groups.show', $group),
            actorId:    Auth::id(),
            entityType: SplitGroup::class,
            entityId:   $group->id,
        );
    }

    public static function demoted(SplitGroup $group, SplitGroupMember $member): void
    {
        NotificationService::send(
            userId:     $member->user_id,
            loai:       'group_demoted',
            tieuDe:     'Quyền Admin đã thay đổi',
            noiDung:    "Bạn đã được chuyển về Member trong nhóm \"{$group->ten_nhom}\"",
            url:        route('groups.show', $group),
            actorId:    Auth::id(),
            entityType: SplitGroup::class,
            entityId:   $group->id,
        );
    }

    // ── Balance proposals ───────────────────────────────────
    public static function balanceProposed(GroupBalanceProposal $proposal): void
    {
        $group     = $proposal->group;
        $proposer  = $proposal->proposer;
        $memberIds = $group->activeMembers()
            ->where('user_id', '!=', Auth::id())
            ->pluck('user_id')->toArray();

        NotificationService::sendMany(
            userIds:    $memberIds,
            loai:       'balance_proposed',
            tieuDe:     $proposer->name,
            noiDung:    "đề xuất phân phối số dư mới trong nhóm \"{$group->ten_nhom}\"",
            url:        route('groups.balance.index', $group),
            actorId:    Auth::id(),
            entityType: GroupBalanceProposal::class,
            entityId:   $proposal->id,
        );
    }

    public static function balanceApproved(GroupBalanceProposal $proposal, int $approverId): void
    {
        $group    = $proposal->group;
        $approver = \App\Models\User::find($approverId);

        // Thông báo cho người tạo đề xuất
        if ($proposal->proposed_by !== $approverId) {
            NotificationService::send(
                userId:     $proposal->proposed_by,
                loai:       'balance_approved',
                tieuDe:     $approver->name,
                noiDung:    "đã đồng ý đề xuất phân phối trong nhóm \"{$group->ten_nhom}\"",
                url:        route('groups.balance.index', $group),
                actorId:    $approverId,
                entityType: GroupBalanceProposal::class,
                entityId:   $proposal->id,
            );
        }
    }

    public static function balanceRejected(GroupBalanceProposal $proposal, int $rejecterId): void
    {
        $group    = $proposal->group;
        $rejecter = \App\Models\User::find($rejecterId);

        if ($proposal->proposed_by !== $rejecterId) {
            NotificationService::send(
                userId:     $proposal->proposed_by,
                loai:       'balance_rejected',
                tieuDe:     $rejecter->name,
                noiDung:    "đã từ chối đề xuất phân phối trong nhóm \"{$group->ten_nhom}\"",
                url:        route('groups.balance.index', $group),
                actorId:    $rejecterId,
                entityType: GroupBalanceProposal::class,
                entityId:   $proposal->id,
            );
        }
    }

    public static function balanceExecuted(GroupBalanceProposal $proposal): void
    {
        $group     = $proposal->group;
        $memberIds = $group->activeMembers()->pluck('user_id')->toArray();

        NotificationService::sendMany(
            userIds:    $memberIds,
            loai:       'balance_executed',
            tieuDe:     'Phân phối số dư hoàn tất',
            noiDung:    "Đề xuất phân phối đã được thực hiện trong nhóm \"{$group->ten_nhom}\"",
            url:        route('groups.balance.index', $group),
            actorId:    null,
            entityType: GroupBalanceProposal::class,
            entityId:   $proposal->id,
        );
    }

    // ── Expense proposals ───────────────────────────────────
    public static function expenseProposed(GroupExpenseProposal $proposal): void
    {
        $group     = $proposal->group;
        $proposer  = $proposal->proposer;
        $memberIds = $group->activeMembers()
            ->where('user_id', '!=', Auth::id())
            ->pluck('user_id')->toArray();

        NotificationService::sendMany(
            userIds:    $memberIds,
            loai:       'expense_proposed',
            tieuDe:     $proposer->name,
            noiDung:    "đề xuất chia chi " . number_format($proposal->tong_tien) . "đ trong nhóm \"{$group->ten_nhom}\"",
            url:        route('groups.expense.index', $group),
            actorId:    Auth::id(),
            entityType: GroupExpenseProposal::class,
            entityId:   $proposal->id,
        );
    }

    public static function expenseApproved(GroupExpenseProposal $proposal, int $approverId): void
    {
        $group    = $proposal->group;
        $approver = \App\Models\User::find($approverId);

        if ($proposal->proposed_by !== $approverId) {
            NotificationService::send(
                userId:     $proposal->proposed_by,
                loai:       'expense_approved',
                tieuDe:     $approver->name,
                noiDung:    "đã đồng ý khoản chi " . number_format($proposal->tong_tien) . "đ trong nhóm \"{$group->ten_nhom}\"",
                url:        route('groups.expense.index', $group),
                actorId:    $approverId,
                entityType: GroupExpenseProposal::class,
                entityId:   $proposal->id,
            );
        }
    }

    public static function expenseRejected(GroupExpenseProposal $proposal, int $rejecterId): void
    {
        $group    = $proposal->group;
        $rejecter = \App\Models\User::find($rejecterId);

        if ($proposal->proposed_by !== $rejecterId) {
            NotificationService::send(
                userId:     $proposal->proposed_by,
                loai:       'expense_rejected',
                tieuDe:     $rejecter->name,
                noiDung:    "đã từ chối khoản chi trong nhóm \"{$group->ten_nhom}\"",
                url:        route('groups.expense.index', $group),
                actorId:    $rejecterId,
                entityType: GroupExpenseProposal::class,
                entityId:   $proposal->id,
            );
        }
    }

    public static function expenseExecuted(GroupExpenseProposal $proposal): void
    {
        $group     = $proposal->group;
        $memberIds = $group->activeMembers()->pluck('user_id')->toArray();

        NotificationService::sendMany(
            userIds:    $memberIds,
            loai:       'expense_executed',
            tieuDe:     'Khoản chi đã được chia',
            noiDung:    "Khoản chi " . number_format($proposal->tong_tien) . "đ đã được thực hiện trong \"{$group->ten_nhom}\"",
            url:        route('groups.expense.index', $group),
            actorId:    null,
            entityType: GroupExpenseProposal::class,
            entityId:   $proposal->id,
        );
    }

    // ── Debts ───────────────────────────────────────────────
    public static function debtRecorded(GroupExpenseDebt $debt): void
    {
        $group   = $debt->group;
        $chuNo   = $debt->chuNo;
        $nguoiNo = $debt->nguoiNo;

        // Thông báo cho người nợ
        NotificationService::send(
            userId:     $debt->nguoi_no_id,
            loai:       'debt_recorded',
            tieuDe:     $chuNo->name,
            noiDung:    "ghi nhận bạn nợ " . number_format($debt->so_tien) . "đ trong nhóm \"{$group->ten_nhom}\"",
            url:        route('groups.debt.summary', $group),
            actorId:    Auth::id(),
            entityType: GroupExpenseDebt::class,
            entityId:   $debt->id,
        );

        // Thông báo cho chủ nợ (nếu không phải người ghi)
        if ($debt->chu_no_id !== Auth::id()) {
            NotificationService::send(
                userId:     $debt->chu_no_id,
                loai:       'debt_recorded',
                tieuDe:     $nguoiNo->name,
                noiDung:    "nợ bạn " . number_format($debt->so_tien) . "đ trong nhóm \"{$group->ten_nhom}\"",
                url:        route('groups.debt.summary', $group),
                actorId:    Auth::id(),
                entityType: GroupExpenseDebt::class,
                entityId:   $debt->id,
            );
        }
    }

    public static function debtSettled(GroupExpenseDebt $debt): void
    {
        $group  = $debt->group;
        $chuNo  = $debt->chuNo;

        // Thông báo cho chủ nợ
        NotificationService::send(
            userId:     $debt->chu_no_id,
            loai:       'debt_settled',
            tieuDe:     $debt->nguoiNo->name,
            noiDung:    "đã trả " . number_format($debt->so_tien) . "đ cho bạn trong nhóm \"{$group->ten_nhom}\"",
            url:        route('groups.debt.summary', $group),
            actorId:    Auth::id(),
            entityType: GroupExpenseDebt::class,
            entityId:   $debt->id,
        );
    }
}
