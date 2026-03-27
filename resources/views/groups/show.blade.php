@extends('layouts.app')
@section('title', $group->ten_nhom)
@section('content')
<style>
:root {
    --primary: #4a90e2; --primary-dark: #2a5298;
    --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
    --radius: 16px; --radius-sm: 10px;
    --shadow: 0 2px 8px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
}

/* Breadcrumb */
.breadcrumb {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: #9ca3af; margin-bottom: 20px;
}
.breadcrumb a { color: var(--primary); text-decoration: none; font-weight: 600; }
.breadcrumb a:hover { text-decoration: underline; }

/* Hero */
.group-hero {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 20px; padding: 32px 36px;
    margin-bottom: 24px; position: relative; overflow: hidden;
    box-shadow: 0 8px 28px rgba(74,144,226,0.3);
}
.group-hero::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.06); border-radius: 50%;
}
.group-hero::after {
    content: '';
    position: absolute; bottom: -40px; left: 40%;
    width: 160px; height: 160px;
    background: rgba(255,255,255,0.04); border-radius: 50%;
}
.hero-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap; }
.hero-name { font-size: 28px; font-weight: 900; color: white; letter-spacing: -0.5px; margin-bottom: 6px; }
.hero-desc { font-size: 14px; color: rgba(255,255,255,0.75); }
.hero-meta { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 12px; }
.hero-tag {
    padding: 5px 12px; border-radius: 20px;
    background: rgba(255,255,255,0.15); color: white;
    font-size: 12px; font-weight: 700; backdrop-filter: blur(4px);
}
.hero-actions { display: flex; gap: 8px; flex-shrink: 0; }
.btn-hero {
    padding: 9px 16px; border-radius: var(--radius-sm);
    background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.25);
    color: white; font-size: 13px; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; gap: 6px; text-decoration: none;
    transition: background 0.2s; backdrop-filter: blur(4px);
}
.btn-hero:hover { background: rgba(255,255,255,0.28); }
.btn-hero.danger { background: rgba(239,68,68,0.25); border-color: rgba(239,68,68,0.4); }

/* Main layout */
.show-grid {
    display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;
}

/* Section card */
.section-card {
    background: white; border-radius: var(--radius);
    box-shadow: var(--shadow); margin-bottom: 20px;
    border: 1px solid rgba(255,255,255,0.8); overflow: hidden;
}
body.dark .section-card { background: #191d27; border-color: rgba(255,255,255,0.06); }
.section-hdr {
    padding: 18px 22px; border-bottom: 1px solid #f3f4f6;
    display: flex; justify-content: space-between; align-items: center;
}
body.dark .section-hdr { border-color: rgba(255,255,255,0.06); }
.section-title {
    font-size: 15px; font-weight: 800; color: #1f2937;
    display: flex; align-items: center; gap: 8px;
}
body.dark .section-title { color: #e5e7eb; }

/* Mode buttons */
.mode-nav {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 12px; padding: 20px 22px;
}
.mode-btn {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    padding: 20px 16px; border-radius: 14px;
    text-decoration: none; transition: var(--transition);
    border: 2px solid #e5e7eb; background: #fafafa;
}
.mode-btn:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(74,144,226,0.15); }
body.dark .mode-btn { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); }
.mode-btn-icon { font-size: 32px; }
.mode-btn-name { font-size: 13px; font-weight: 800; color: #1f2937; text-align: center; }
body.dark .mode-btn-name { color: #e5e7eb; }
.mode-btn-desc { font-size: 11px; color: #9ca3af; text-align: center; }
.mode-btn.active { border-color: var(--primary); background: rgba(74,144,226,0.05); }
.mode-btn.active .mode-btn-name { color: var(--primary); }

/* Pending badge */
.pending-badge {
    background: var(--danger); color: white;
    font-size: 10px; font-weight: 800;
    padding: 2px 7px; border-radius: 20px;
}

/* Member list */
.member-item {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 22px; border-bottom: 1px solid #f3f4f6;
    transition: background 0.15s;
}
body.dark .member-item { border-color: rgba(255,255,255,0.05); }
.member-item:last-child { border-bottom: none; }
.member-item:hover { background: #f9fafb; }
body.dark .member-item:hover { background: rgba(255,255,255,0.02); }

.member-av {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 15px; font-weight: 800; flex-shrink: 0;
}
.member-info { flex: 1; min-width: 0; }
.member-name { font-size: 14px; font-weight: 700; color: #1f2937; }
body.dark .member-name { color: #e5e7eb; }
.member-email { font-size: 12px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.member-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
.member-role {
    padding: 3px 9px; border-radius: 12px;
    font-size: 11px; font-weight: 700;
}
.role-admin  { background: rgba(74,144,226,0.1); color: var(--primary); }
.role-member { background: rgba(107,114,128,0.1); color: #6b7280; }
.member-balance { font-size: 13px; font-weight: 700; }
.member-balance.pos { color: var(--success); }
.member-balance.neg { color: var(--danger); }
.member-balance.zero { color: #9ca3af; }

/* Invite section */
.invite-box { padding: 18px 22px; }
.invite-form { display: flex; gap: 8px; }
.invite-input {
    flex: 1; padding: 10px 14px;
    border: 2px solid #e5e7eb; border-radius: var(--radius-sm);
    font-size: 14px; background: #f9fafb; color: #1f2937; outline: none;
    transition: border-color 0.2s;
}
.invite-input:focus { border-color: var(--primary); background: white; }
body.dark .invite-input { background: #141820; border-color: rgba(255,255,255,0.1); color: #e5e7eb; }

/* Alerts */
.alert {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px; border-radius: var(--radius-sm);
    font-size: 14px; font-weight: 500; margin-bottom: 20px;
}
.alert-success { background:#d1fae5; color:#065f46; border-left:4px solid var(--success); }
.alert-error   { background:#fee2e2; color:#991b1b; border-left:4px solid var(--danger); }

/* Buttons */
.btn-primary {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 18px; border-radius: var(--radius-sm);
    background: linear-gradient(135deg,var(--primary),var(--primary-dark));
    color: white; font-size: 13px; font-weight: 700;
    border: none; cursor: pointer; text-decoration: none; transition: opacity 0.2s;
}
.btn-primary:hover { opacity: 0.88; }
.btn-sm { padding: 6px 12px; font-size: 12px; }
.btn-danger { background: linear-gradient(135deg,var(--danger),#dc2626); }
.btn-warning { background: linear-gradient(135deg,var(--warning),#d97706); }

/* Pending proposals alert */
.proposal-alert {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px; border-radius: var(--radius-sm);
    background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3);
    margin-bottom: 16px;
}
.proposal-alert-icon { font-size: 20px; }
.proposal-alert-text { flex: 1; font-size: 13px; color: #92400e; font-weight: 600; }
body.dark .proposal-alert-text { color: #fcd34d; }

/* Visibility toggle */
.vis-toggle {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 22px; background: rgba(74,144,226,0.04);
    border-top: 1px solid #f3f4f6;
}
body.dark .vis-toggle { border-color: rgba(255,255,255,0.06); background: rgba(74,144,226,0.06); }
.vis-label { flex: 1; font-size: 13px; color: #374151; font-weight: 600; }
body.dark .vis-label { color: #9ca3af; }
.toggle-switch {
    position: relative; width: 44px; height: 24px;
    cursor: pointer;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-track {
    position: absolute; inset: 0; border-radius: 12px;
    background: #d1d5db; transition: background 0.2s;
}
.toggle-switch input:checked + .toggle-track { background: var(--primary); }
.toggle-thumb {
    position: absolute; top: 3px; left: 3px;
    width: 18px; height: 18px; border-radius: 50%;
    background: white; transition: transform 0.2s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.toggle-switch input:checked ~ .toggle-thumb { transform: translateX(20px); }

@media (max-width:1100px) {
    .show-grid { grid-template-columns: 1fr; }
}
</style>

{{-- Breadcrumb --}}
<div class="breadcrumb">
    <a href="{{ route('groups.index') }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M12 4L6 10l6 6"/></svg> Nhóm của tôi</a>
    <span>/</span>
    <span>{{ $group->ten_nhom }}</span>
</div>

@if(session('success'))
<div class="alert alert-success"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" stroke-width="2.5"><path d="M4 10l4.5 4.5L16 6"/></svg> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M10 2L2 17h16z"/><path d="M10 8v4M10 14.5v.5"/></svg> {{ session('error') }}</div>
@endif

{{-- Hero --}}
<div class="group-hero">
    <div class="hero-top">
        <div>
            <div class="hero-name">{{ $group->ten_nhom }}</div>
            <div class="hero-desc">{{ $group->mo_ta ?? 'Chưa có mô tả' }}</div>
            <div class="hero-meta">
                <span class="hero-tag">

                    @if($group->che_do=="balance")
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M10 3v14M4 17h12"/><path d="M4 7 2 12h4zM16 7l-2 5h4z"/><path d="M4 7h12"/></svg> Phân phối số dư
                    @elseif($group->che_do=="expense")
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M5 2h10a1 1 0 011 1v14l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5V3a1 1 0 011-1z"/><path d="M7.5 7h5M7.5 10h5M7.5 13h3"/></svg> Chia khoản chi
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M3 6h9l3-3 3 3-3 3"/><path d="M3 14h9l3-3 3 3-3 3"/><path d="M6 9l-3 3M6 11l-3-3"/></svg> Cả hai chế độ
                    @endif
                </span>
                <span class="hero-tag"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><circle cx="7.5" cy="6.5" r="2.5"/><path d="M2 17c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><circle cx="14" cy="6" r="2"/><path d="M18 17c0-2.5-1.8-4.2-4-4.7"/></svg> {{ count($members) }} thành viên</span>
                <span class="hero-tag"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><rect x="3" y="4" width="14" height="14" rx="2"/><path d="M3 9h14M7 2v4M13 2v4"/><circle cx="7" cy="13" r="0.8" fill="currentColor" stroke="none"/><circle cx="10" cy="13" r="0.8" fill="currentColor" stroke="none"/><circle cx="13" cy="13" r="0.8" fill="currentColor" stroke="none"/></svg> {{ $group->created_at->format('d/m/Y') }}</span>
                @if($group->hien_so_du)
                <span class="hero-tag"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M2 10S5 4.5 10 4.5 18 10 18 10s-3 5.5-8 5.5S2 10 2 10z"/><circle cx="10" cy="10" r="2.5"/></svg> Hiển thị số dư</span>
                @endif
            </div>
        </div>
        <div class="hero-actions">
            @if($laAdmin)
            <button class="btn-hero" onclick="openEdit()"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M13.5 3.5L16.5 6.5 7 16H4v-3z"/><path d="M11.5 5.5l3 3"/></svg> Sửa</button>
            @endif
            <form action="{{ route('groups.leave', $group) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn rời nhóm?')">
                @csrf
                <button type="submit" class="btn-hero danger"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M5 2h10a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V3a1 1 0 011-1z"/><path d="M3 18h14"/><circle cx="13" cy="10.5" r="1" fill="currentColor" stroke="none"/></svg> Rời nhóm</button>
            </form>
        </div>
    </div>
</div>

<div class="show-grid">
    {{-- LEFT: Actions + Pending --}}
    <div>
        {{-- Pending proposals alert --}}
        @if($pendingBalance > 0)
        <div class="proposal-alert">
            <div class="proposal-alert-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><circle cx="10" cy="10" r="7.5"/><path d="M10 6v4.5l3 1.5"/></svg></div>
            <div class="proposal-alert-text">Có {{ $pendingBalance }} đề xuất phân phối số dư đang chờ bạn xác nhận</div>
            <a href="{{ route('groups.balance.index', $group) }}" class="btn-primary btn-sm">Xem ngay</a>
        </div>
        @endif

        @if($pendingExpense > 0)
        <div class="proposal-alert">
            <div class="proposal-alert-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><circle cx="10" cy="10" r="7.5"/><path d="M10 6v4.5l3 1.5"/></svg></div>
            <div class="proposal-alert-text">Có {{ $pendingExpense }} đề xuất chia chi đang chờ bạn xác nhận</div>
            <a href="{{ route('groups.expense.index', $group) }}" class="btn-primary btn-sm">Xem ngay</a>
        </div>
        @endif

        {{-- Mode navigation --}}
        <div class="section-card">
            <div class="section-hdr">
                <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M11 2L5 11h5.5L9 18l7-9h-5.5z"/></svg> Chức năng nhóm</div>
            </div>
            <div class="mode-nav">
                @if(in_array($group->che_do, ['balance','both']))
                <a href="{{ route('groups.balance.index', $group) }}" class="mode-btn {{ $pendingBalance > 0 ? 'active' : '' }}">
                    <div class="mode-btn-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M10 3v14M4 17h12"/><path d="M4 7 2 12h4zM16 7l-2 5h4z"/><path d="M4 7h12"/></svg></div>
                    <div class="mode-btn-name">
                        Phân phối số dư
                        @if($pendingBalance > 0)
                        <span class="pending-badge">{{ $pendingBalance }}</span>
                        @endif
                    </div>
                    <div class="mode-btn-desc">Chia lại tiền trong nhóm</div>
                </a>
                @endif

                @if(in_array($group->che_do, ['expense','both']))
                <a href="{{ route('groups.expense.index', $group) }}" class="mode-btn {{ $pendingExpense > 0 ? 'active' : '' }}">
                    <div class="mode-btn-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M5 2h10a1 1 0 011 1v14l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5V3a1 1 0 011-1z"/><path d="M7.5 7h5M7.5 10h5M7.5 13h3"/></svg></div>
                    <div class="mode-btn-name">
                        Chia khoản chi
                        @if($pendingExpense > 0)
                        <span class="pending-badge">{{ $pendingExpense }}</span>
                        @endif
                    </div>
                    <div class="mode-btn-desc">Chia tiền khi thanh toán chung</div>
                </a>

                <a href="{{ route('groups.debt.summary', $group) }}" class="mode-btn">
                    <div class="mode-btn-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><rect x="3" y="11" width="3" height="6" rx="0.5"/><rect x="8.5" y="7" width="3" height="10" rx="0.5"/><rect x="14" y="4" width="3" height="13" rx="0.5"/><path d="M2 18h16"/></svg></div>
                    <div class="mode-btn-name">Tổng kết nợ</div>
                    <div class="mode-btn-desc">Xem ai nợ ai bao nhiêu</div>
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- RIGHT: Members --}}
    <div>
        <div class="section-card">
            <div class="section-hdr">
                <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><circle cx="7.5" cy="6.5" r="2.5"/><path d="M2 17c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><circle cx="14" cy="6" r="2"/><path d="M18 17c0-2.5-1.8-4.2-4-4.7"/></svg> Thành viên ({{ count($members) }})</div>
                @if($laAdmin)
                <button class="btn-primary btn-sm" onclick="openInvite()">+ Mời</button>
                @endif
            </div>

            {{-- Visibility toggle (admin only, balance mode) --}}
            @if($laAdmin && in_array($group->che_do, ['balance','both']))
            <div class="vis-toggle">
                <div class="vis-label">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M2 10S5 4.5 10 4.5 18 10 18 10s-3 5.5-8 5.5S2 10 2 10z"/><circle cx="10" cy="10" r="2.5"/></svg> Hiển thị số dư cho thành viên
                    <span style="font-size:11px;font-weight:600;margin-left:6px;
                        color:{{ $group->hien_so_du ? '#10b981' : '#9ca3af' }}">
                        {{ $group->hien_so_du ? '(Đang bật)' : '(Đang tắt)' }}
                    </span>
                </div>
                <form action="{{ route('groups.toggle-visibility', $group) }}" method="POST" id="visForm">
                    @csrf
                    <label class="toggle-switch" title="{{ $group->hien_so_du ? 'Nhấn để tắt' : 'Nhấn để bật' }} hiển thị số dư"
                           onclick="this.closest('form').submit(); return false;"
                           style="cursor:pointer;">
                        <input type="checkbox" {{ $group->hien_so_du ? 'checked' : '' }} style="display:none;">
                        <div class="toggle-track" style="{{ $group->hien_so_du ? 'background:#4a90e2' : '' }}"></div>
                        <div class="toggle-thumb" style="{{ $group->hien_so_du ? 'transform:translateX(20px)' : '' }}"></div>
                    </label>
                </form>
            </div>
            @endif

            @foreach($members as $m)
            @php
                $initials = strtoupper(substr($m['name'], 0, 2));
                $colors   = ['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
                $color    = $colors[$loop->index % count($colors)];
            @endphp
            <div class="member-item">
                @if($m['avatar'])
                @if(str_starts_with($m['avatar'], 'http'))
                    <img src="{{ $m['avatar'] }}" class="member-av" style="object-fit:cover;" alt="">
                @else
                    <img src="{{ asset('storage/' . $m['avatar']) }}" class="member-av" style="object-fit:cover;" alt="">
                @endif
            @else
                <div class="member-av" style="background:linear-gradient(135deg,{{ $color }},{{ $color }}cc)">
                    {{ strtoupper(substr($m['name'], 0, 2)) }}
                </div>
            @endif
                <div class="member-info">
                    <div class="member-name">{{ $m['name'] }}</div>
                    <div class="member-email">{{ $m['email'] }}</div>
                </div>
                <div class="member-meta">
                    <span class="member-role {{ $m['vai_tro'] === 'admin' ? 'role-admin' : 'role-member' }}">

                        @if($m['vai_tro']==="admin")
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M3 14L6 7l4 4 4-4 3 7H3z"/><path d="M3 14h14"/><circle cx="10" cy="3.5" r="1" fill="currentColor" stroke="none"/></svg> Admin
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><circle cx="10" cy="6.5" r="3"/><path d="M3.5 18c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"/></svg> Member
                        @endif
                    </span>
                    @if($hienSoDu && isset($m['so_du']))
                    @php
                        $bal = $m['so_du'];
                        $cls = $bal > 0 ? 'pos' : ($bal < 0 ? 'neg' : 'zero');
                        $prefix = $bal > 0 ? '+' : '';
                    @endphp
                    <span class="member-balance {{ $cls }}">
                        {{ $prefix }}{{ number_format($bal) }}đ
                    </span>
                    @endif

                    {{-- Nút chỉ định admin: chỉ admin thấy, không áp cho chính mình --}}
                    @if($laAdmin && $m['user_id'] !== Auth::id())
                        @if($m['vai_tro'] === 'member')
                        <form action="{{ route('groups.members.promote', [$group, $m['id']]) }}" method="POST"
                              onsubmit="return confirm('Chỉ định {{ $m['name'] }} làm Admin?')">
                            @csrf
                            <button type="submit" style="
                                background:rgba(74,144,226,0.08);border:1px solid rgba(74,144,226,0.3);
                                color:#4a90e2;font-size:11px;font-weight:700;padding:3px 9px;
                                border-radius:8px;cursor:pointer;transition:all .2s;white-space:nowrap;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M10 14V6M6 10l4-4 4 4"/></svg> Đặt Admin
                            </button>
                        </form>
                        @else
                        <form action="{{ route('groups.members.demote', [$group, $m['id']]) }}" method="POST"
                              onsubmit="return confirm('Hạ quyền {{ $m['name'] }} xuống Member?')">
                            @csrf
                            <button type="submit" style="
                                background:rgba(107,114,128,0.08);border:1px solid rgba(107,114,128,0.2);
                                color:#6b7280;font-size:11px;font-weight:700;padding:3px 9px;
                                border-radius:8px;cursor:pointer;transition:all .2s;white-space:nowrap;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M10 6v8M6 10l4 4 4-4"/></svg> Hạ quyền
                            </button>
                        </form>
                        @endif
                    @endif

                    {{-- Nút xóa thành viên --}}
                    @if($laAdmin && $m['user_id'] !== Auth::id() && $m['vai_tro'] !== 'admin')
                    <form action="{{ route('groups.members.remove', [$group, $m['id']]) }}" method="POST"
                          onsubmit="return confirm('Xóa {{ $m['name'] }} khỏi nhóm?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="
                            background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);
                            color:#ef4444;font-size:11px;font-weight:700;padding:3px 9px;
                            border-radius:8px;cursor:pointer;transition:all .2s;white-space:nowrap;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" stroke-width="2.5"><path d="M5 5l10 10M15 5L5 15"/></svg> Xóa
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Invite picker --}}
            <div id="inviteBox" style="display:none; padding:16px 20px; border-top:1px solid #f3f4f6;">
                <form action="{{ route('groups.invite', $group) }}" method="POST" id="inviteForm">
                    @csrf
                    <input type="hidden" name="email" id="inviteEmailHidden">
                    <div style="position:relative; margin-bottom:10px;">
                        <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid #e5e7eb;border-radius:12px;background:#f9fafb;transition:border-color .2s;" id="searchWrap">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                            <input type="text" id="userSearchInput" placeholder="Tìm theo tên hoặc email..."
                                autocomplete="off"
                                style="border:none;outline:none;background:transparent;font-size:13px;color:#1f2937;width:100%;font-weight:500;"
                                oninput="searchUsers(this.value)"
                                onfocus="document.getElementById('searchWrap').style.borderColor='#4a90e2'"
                                onblur="setTimeout(()=>{document.getElementById('searchWrap').style.borderColor='#e5e7eb'},200)">
                            <div id="searchSpinnerInvite" style="display:none;width:14px;height:14px;border:2px solid #e5e7eb;border-top-color:#4a90e2;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0;"></div>
                        </div>
                        <div id="userDropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;background:white;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.14);border:1px solid rgba(0,0,0,0.06);z-index:999;max-height:280px;overflow-y:auto;"></div>
                    </div>
                    <div id="selectedUser" style="display:none; margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(74,144,226,0.06);border:2px solid rgba(74,144,226,0.3);border-radius:12px;">
                            <div id="selAvatar" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:800;flex-shrink:0;"></div>
                            <div style="flex:1;min-width:0;">
                                <div id="selName" style="font-size:13px;font-weight:700;color:#1f2937;"></div>
                                <div id="selEmail" style="font-size:12px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                            </div>
                            <button type="button" onclick="clearSelected()" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:16px;padding:2px 4px;line-height:1;">&#x2715;</button>
                        </div>
                    </div>
                    <button type="submit" id="inviteSubmitBtn" disabled
                        style="width:100%;padding:11px;border-radius:10px;background:linear-gradient(135deg,#4a90e2,#2a5298);color:white;border:none;font-size:13px;font-weight:700;cursor:pointer;opacity:.45;transition:opacity .2s;display:flex;align-items:center;justify-content:center;gap:7px;">
                        Gửi lời mời
                    </button>
                    <div style="font-size:11px;color:#9ca3af;margin-top:8px;text-align:center;">Loi moi het han sau 48 gio</div>
                </form>
            </div>
        </div>

        {{-- Danger zone --}}
        @if($laAdmin)
        <div class="section-card">
            <div class="section-hdr">
                <div class="section-title" style="color:#ef4444;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M10 2L2 17h16z"/><path d="M10 8v4M10 14.5v.5"/></svg> Khu vực nguy hiểm</div>
            </div>
            <div style="padding:18px 22px;">
                <form action="{{ route('groups.destroy', $group) }}" method="POST"
                      onsubmit="return confirm('Lưu trữ nhóm {{ addslashes($group->ten_nhom) }}? Dữ liệu sẽ không bị xóa.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-primary btn-danger" style="width:100%;justify-content:center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><rect x="2" y="4" width="16" height="4" rx="1"/><path d="M3 8v8a1 1 0 001 1h12a1 1 0 001-1V8"/><path d="M8 12h4"/></svg> Lưu trữ nhóm
                    </button>
                </form>
                <div style="font-size:12px;color:#9ca3af;margin-top:8px;text-align:center">
                    Nhóm sẽ được lưu trữ, không bị xóa hoàn toàn
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Edit modal --}}
@if($laAdmin)
<div class="modal-overlay" id="editModal" style="opacity:0;visibility:hidden;position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;transition:opacity .22s,visibility .22s">
    <div style="background:white;border-radius:20px;width:100%;max-width:500px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18)">
        <div style="padding:22px 26px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;justify-content:space-between;align-items:center">
            <div style="font-size:17px;font-weight:800;color:white"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M13.5 3.5L16.5 6.5 7 16H4v-3z"/><path d="M11.5 5.5l3 3"/></svg> Sửa thông tin nhóm</div>
            <button onclick="closeEdit()" style="background:rgba(255,255,255,0.2);border:none;border-radius:8px;width:32px;height:32px;cursor:pointer;color:white;font-size:16px"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" stroke-width="2.5"><path d="M5 5l10 10M15 5L5 15"/></svg></button>
        </div>
        <form action="{{ route('groups.update', $group) }}" method="POST" style="padding:24px 26px">
            @csrf @method('PUT')
            <div style="margin-bottom:16px">
                <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px">Tên nhóm *</label>
                <input name="ten_nhom" value="{{ $group->ten_nhom }}" required maxlength="100"
                    style="width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:10px;font-size:14px;background:#f9fafb;outline:none">
            </div>
            <div style="margin-bottom:16px">
                <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px">Mô tả</label>
                <input name="mo_ta" value="{{ $group->mo_ta }}" maxlength="255"
                    style="width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:10px;font-size:14px;background:#f9fafb;outline:none">
            </div>
            <div style="margin-bottom:20px">
                <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px">Chế độ</label>
                <select name="che_do" style="width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:10px;font-size:14px;background:#f9fafb;outline:none">
                    <option value="balance" {{ $group->che_do=='balance'?'selected':'' }}>Phân phối số dư</option>
                    <option value="expense" {{ $group->che_do=='expense'?'selected':'' }}>Chia khoản chi</option>
                    <option value="both"    {{ $group->che_do=='both'?'selected':'' }}>Cả hai chế độ</option>
                </select>
            </div>
            <div style="display:flex;gap:10px">
                <button type="button" onclick="closeEdit()" style="flex:1;padding:10px;border-radius:10px;background:#f3f4f6;border:2px solid #e5e7eb;color:#6b7280;font-weight:600;cursor:pointer">Hủy</button>
                <button type="submit" style="flex:2;padding:10px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;border:none;font-weight:700;cursor:pointer">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
// ── Invite toggle ──
function openInvite() {
    const box = document.getElementById('inviteBox');
    const isHidden = box.style.display === 'none' || box.style.display === '';
    box.style.display = isHidden ? 'block' : 'none';
    if (isHidden) {
        setTimeout(() => document.getElementById('userSearchInput')?.focus(), 60);
    }
}

// ── Edit modal ──
function openEdit()  { const m=document.getElementById('editModal'); m.style.opacity='1'; m.style.visibility='visible'; }
function closeEdit() { const m=document.getElementById('editModal'); m.style.opacity='0'; m.style.visibility='hidden'; }
document.getElementById('editModal')?.addEventListener('click', e => { if(e.target===e.currentTarget) closeEdit(); });

// ── User search for invite ──
const memberIds = @json(collect($members)->pluck('user_id')->toArray());
let searchTimer = null;

function searchUsers(q) {
    clearTimeout(searchTimer);
    const drop = document.getElementById('userDropdown');
    const spin = document.getElementById('searchSpinnerInvite');

    if (q.trim().length < 1) { drop.style.display='none'; return; }

    spin.style.display = 'block';
    searchTimer = setTimeout(() => {
        fetch(`/groups/search-users?q=${encodeURIComponent(q)}&exclude=${memberIds.join(',')}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            spin.style.display = 'none';
            renderDropdown(data.users || []);
        })
        .catch(() => { spin.style.display = 'none'; });
    }, 280);
}

function renderDropdown(users) {
    const drop = document.getElementById('userDropdown');
    if (users.length === 0) {
        drop.innerHTML = `<div style="padding:20px;text-align:center;color:#9ca3af;font-size:13px;font-weight:500">
            Không tìm thấy người dùng nào
        </div>`;
        drop.style.display = 'block';
        return;
    }
    const isDark = document.body.classList.contains('dark');
    drop.innerHTML = users.map(u => {
        const initials = u.name.substring(0, 2).toUpperCase();
        const avatarColors = ['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
        const color = avatarColors[u.id % avatarColors.length];
        // Normalize avatar URL — same logic as profile/topbar
        const avatarSrc = u.avatar
            ? (u.avatar.startsWith('http') ? u.avatar : '/storage/' + u.avatar)
            : null;
        const avatarHtml = avatarSrc
            ? `<img src="${avatarSrc}"
                   style="width:38px;height:38px;border-radius:50%;object-fit:cover;flex-shrink:0;"
                   onerror="this.outerHTML='<div style=\'width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,${color},${color}cc);display:flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:800;flex-shrink:0;\'>${initials}</div>'"
                   alt="">`
            : `<div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,${color},${color}cc);
                    display:flex;align-items:center;justify-content:center;color:white;
                    font-size:13px;font-weight:800;flex-shrink:0;">${initials}</div>`;

        return `<div class="user-result-item" onclick="selectUser(${u.id}, '${escHtml(u.name)}', '${escHtml(u.email)}', '${u.avatar || ''}', '${color}')"
            style="display:flex;align-items:center;gap:12px;padding:11px 16px;cursor:pointer;
                   transition:background .15s;border-bottom:1px solid #f9fafb;"
            onmouseover="this.style.background='#f8f9fd'"
            onmouseout="this.style.background='transparent'">
            ${avatarHtml}
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:700;color:#1f2937;margin-bottom:1px;">${escHtml(u.name)}</div>
                <div style="font-size:12px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escHtml(u.email)}</div>
            </div>
            <div style="font-size:11px;color:#4a90e2;font-weight:700;flex-shrink:0;">+ Mời</div>
        </div>`;
    }).join('');
    drop.style.display = 'block';
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

function selectUser(id, name, email, avatar, color) {
    // Fill hidden email
    document.getElementById('inviteEmailHidden').value = email;

    // Show selected preview
    const sel = document.getElementById('selectedUser');
    const av  = document.getElementById('selAvatar');
    // Normalize avatar — same as profile page
    const selAvatarSrc = avatar
        ? (avatar.startsWith('http') ? avatar : '/storage/' + avatar)
        : null;
    if (selAvatarSrc) {
        av.innerHTML = `<img src="${selAvatarSrc}"
            style="width:36px;height:36px;border-radius:50%;object-fit:cover;"
            onerror="this.parentElement.innerHTML='${name.substring(0,2).toUpperCase()}'">`;
        av.style.background = 'transparent';
        av.style.padding = '0';
    } else {
        const initials = name.substring(0, 2).toUpperCase();
        av.textContent = initials;
        av.style.background = `linear-gradient(135deg,${color},${color}cc)`;
    }
    document.getElementById('selName').textContent  = name;
    document.getElementById('selEmail').textContent = email;
    sel.style.display = 'block';

    // Enable button
    const btn = document.getElementById('inviteSubmitBtn');
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor  = 'pointer';

    // Clear search
    document.getElementById('userSearchInput').value = '';
    document.getElementById('userDropdown').style.display = 'none';
}

function clearSelected() {
    document.getElementById('inviteEmailHidden').value = '';
    document.getElementById('selectedUser').style.display = 'none';
    document.getElementById('userSearchInput').value = '';
    const btn = document.getElementById('inviteSubmitBtn');
    btn.disabled = true;
    btn.style.opacity = '0.45';
    btn.style.cursor  = 'not-allowed';
}

// Close dropdown on outside click
document.addEventListener('click', e => {
    if (!e.target.closest('#inviteBox')) {
        document.getElementById('userDropdown').style.display = 'none';
    }
});

// Validate before submit
document.getElementById('inviteForm')?.addEventListener('submit', e => {
    if (!document.getElementById('inviteEmailHidden').value) {
        e.preventDefault();
        document.getElementById('userSearchInput').focus();
    }
});

// Auto-hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition='opacity .3s'; a.style.opacity='0';
        setTimeout(()=>a.remove(),300);
    });
}, 4500);

// Dark mode fixes for dropdown
const observer = new MutationObserver(() => {
    const drop = document.getElementById('userDropdown');
    if (document.body.classList.contains('dark')) {
        drop.style.background = '#1a1f29';
        drop.style.borderColor = 'rgba(255,255,255,0.08)';
        drop.querySelectorAll('.user-result-item').forEach(el => {
            el.style.borderColor = 'rgba(255,255,255,0.04)';
        });
    }
});
observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
</script>
@endsection
