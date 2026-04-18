@extends('layouts.app')
@section('title', 'Chia tiền nhóm')
@section('content')
<style>
:root {
    --primary: #4a90e2;
    --primary-dark: #2a5298;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --radius: 16px;
    --radius-sm: 10px;
    --shadow: 0 2px 8px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
}

/* ── Page wrapper ── */
.page-wrap {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    padding: 20px;
    background: #f3f4f6;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
}

/* ── Page header ── */
.pg-hdr {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    padding: 22px 28px;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}
body.dark .pg-hdr { background: #191d27; }
.pg-title {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 22px;
    font-weight: 800;
    color: #1f2937;
}
body.dark .pg-title { color: #e5e7eb; }
.pg-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    box-shadow: 0 4px 14px rgba(74,144,226,0.35);
}

/* ── Alerts ── */
.alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 20px;
}
.alert-success { background:#d1fae5; color:#065f46; border-left:4px solid var(--success); }
.alert-error   { background:#fee2e2; color:#991b1b; border-left:4px solid var(--danger); }

/* ── Stats strip ── */
.stats-strip {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
    width: 100%;
    box-sizing: border-box;
}
.ss-card {
    background: white;
    border-radius: var(--radius);
    padding: 20px 24px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1px solid rgba(255,255,255,0.8);
    transition: var(--transition);
    min-width: 0;
}
.ss-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
body.dark .ss-card { background: #191d27; border-color: rgba(255,255,255,0.06); }
.ss-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}
.ss-icon.blue { background: rgba(74,144,226,0.12); }
.ss-icon.green{ background: rgba(16,185,129,0.12); }
.ss-icon.amber{ background: rgba(245,158,11,0.12); }
.ss-label { font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
.ss-value { font-size: 26px; font-weight: 900; color: #1f2937; letter-spacing: -0.5px; }
body.dark .ss-value { color: #e5e7eb; }

/* ── Group cards grid ── */
.groups-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(280px, 100%), 1fr));
    gap: 20px;
    width: 100%;
    box-sizing: border-box;
}

.group-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid rgba(255,255,255,0.8);
    transition: var(--transition);
    overflow: hidden;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    position: relative;
    min-width: 0; /* critical: prevent grid blowout */
}
.group-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.1);
    border-color: var(--primary);
}
body.dark .group-card { background: #191d27; border-color: rgba(255,255,255,0.06); }

/* Gradient top accent */
.group-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}
.group-card:hover::before { transform: scaleX(1); }

.gc-body { padding: 22px 22px 18px; flex: 1; min-width: 0; }

.gc-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }

.gc-icon {
    width: 50px; height: 50px; border-radius: 14px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; box-shadow: 0 4px 12px rgba(74,144,226,0.3); flex-shrink: 0;
}
.gc-icon.mode-balance { background: linear-gradient(135deg,#10b981,#059669); }
.gc-icon.mode-expense { background: linear-gradient(135deg,#f59e0b,#d97706); }
.gc-icon.mode-both    { background: linear-gradient(135deg,var(--primary),var(--primary-dark)); }

.gc-badges { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; flex-shrink: 0; margin-left: 8px; }

.gc-badge {
    padding: 4px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 700; white-space: nowrap;
}
.gc-badge.admin  { background: rgba(74,144,226,0.12); color: var(--primary); }
.gc-badge.member { background: rgba(107,114,128,0.1); color: #6b7280; }
.gc-badge.mode-b { background: rgba(16,185,129,0.1); color: #059669; }
.gc-badge.mode-e { background: rgba(245,158,11,0.1); color: #d97706; }
.gc-badge.mode-m { background: rgba(74,144,226,0.1); color: var(--primary); }

.gc-name {
    font-size: 17px; font-weight: 800; color: #1f2937;
    margin-bottom: 4px; line-height: 1.3;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
body.dark .gc-name { color: #e5e7eb; }
.gc-desc {
    font-size: 13px; color: #6b7280; line-height: 1.5;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}

/* Member avatars strip */
.gc-members {
    display: flex; align-items: center; gap: 8px;
    margin-top: 16px; padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}
body.dark .gc-members { border-color: rgba(255,255,255,0.06); }
.gc-avatars { display: flex; }
.gc-av {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg,var(--primary),var(--primary-dark));
    border: 2px solid white; color: white;
    font-size: 11px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    margin-left: -8px; flex-shrink: 0;
}
body.dark .gc-av { border-color: #191d27; }
.gc-av:first-child { margin-left: 0; }
.gc-av.extra { background: #e5e7eb; color: #6b7280; }
.gc-member-count { font-size: 12px; color: #9ca3af; font-weight: 500; }

/* Footer row */
.gc-footer {
    padding: 14px 22px;
    background: #fafafa;
    border-top: 1px solid #f3f4f6;
    display: flex; justify-content: space-between; align-items: center;
}
body.dark .gc-footer { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.06); }
.gc-date { font-size: 12px; color: #9ca3af; }
.gc-arrow {
    width: 28px; height: 28px; border-radius: 8px;
    background: rgba(74,144,226,0.08); color: var(--primary);
    display: flex; align-items: center; justify-content: center; font-size: 14px;
    transition: var(--transition);
}
.group-card:hover .gc-arrow { background: var(--primary); color: white; }

/* ── Empty state ── */
.empty-wrap {
    grid-column: 1/-1; text-align: center;
    padding: 80px 20px;
    background: white; border-radius: var(--radius); box-shadow: var(--shadow);
}
body.dark .empty-wrap { background: #191d27; }
.empty-icon-big {
    width: 90px; height: 90px; border-radius: 24px;
    background: rgba(74,144,226,0.08);
    display: flex; align-items: center; justify-content: center;
    font-size: 42px; margin: 0 auto 20px;
}
.empty-wrap h3 { font-size: 20px; font-weight: 700; color: #374151; margin-bottom: 8px; }
body.dark .empty-wrap h3 { color: #e5e7eb; }
.empty-wrap p { font-size: 14px; color: #9ca3af; margin-bottom: 28px; }

/* ── Buttons ── */
.btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: var(--radius-sm);
    background: linear-gradient(135deg,var(--primary),var(--primary-dark));
    color: white; font-size: 14px; font-weight: 700;
    border: none; cursor: pointer; text-decoration: none;
    transition: opacity 0.2s; white-space: nowrap;
}
.btn-primary:hover { opacity: 0.88; }

/* ── Modal ── */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);
    z-index: 9999; display: flex; align-items: center; justify-content: center;
    padding: 20px; opacity: 0; visibility: hidden;
    transition: opacity 0.22s, visibility 0.22s;
}
.modal-overlay.active { opacity: 1; visibility: visible; }

.modal-box {
    background: white; border-radius: 20px;
    width: 100%; max-width: 520px; max-height: 90vh;
    overflow: hidden; display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    transform: scale(0.95) translateY(10px);
    transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
}
.modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
body.dark .modal-box { background: #191d27; }

.modal-hdr {
    padding: 24px 28px;
    background: linear-gradient(135deg,var(--primary),var(--primary-dark));
    display: flex; justify-content: space-between; align-items: center;
}
.modal-hdr-title {
    font-size: 18px; font-weight: 800; color: white;
    display: flex; align-items: center; gap: 10px;
}
.modal-close {
    width: 34px; height: 34px; border-radius: 8px;
    background: rgba(255,255,255,0.2); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 18px; transition: background 0.2s;
}
.modal-close:hover { background: rgba(255,255,255,0.32); }

.modal-body { padding: 28px; overflow-y: auto; flex: 1; }

.form-group { margin-bottom: 18px; }
.form-label { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 7px; display: block; }
body.dark .form-label { color: #9ca3af; }
.required { color: var(--danger); }

.form-ctrl {
    width: 100%; padding: 11px 14px;
    border: 2px solid #e5e7eb; border-radius: var(--radius-sm);
    font-size: 14px; background: #f9fafb; color: #1f2937;
    transition: border-color 0.2s, background 0.2s; outline: none;
    box-sizing: border-box;
}
.form-ctrl:focus { border-color: var(--primary); background: white; }
body.dark .form-ctrl { background: #141820; border-color: rgba(255,255,255,0.1); color: #e5e7eb; }

/* Mode selector */
.mode-grid {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; margin-top: 6px;
}
.mode-card {
    border: 2px solid #e5e7eb; border-radius: 12px;
    padding: 14px 10px; text-align: center; cursor: pointer;
    transition: var(--transition); position: relative;
}
.mode-card:hover { border-color: var(--primary); }
.mode-card.selected { border-color: var(--primary); background: rgba(74,144,226,0.06); }
body.dark .mode-card { border-color: rgba(255,255,255,0.1); }
body.dark .mode-card.selected { background: rgba(74,144,226,0.1); }
.mode-card input { position: absolute; opacity: 0; pointer-events: none; }
.mode-emoji { font-size: 26px; margin-bottom: 6px; display: flex; justify-content: center; }
.mode-name { font-size: 12px; font-weight: 700; color: #374151; }
body.dark .mode-name { color: #e5e7eb; }
.mode-desc { font-size: 11px; color: #9ca3af; margin-top: 2px; }

.modal-foot {
    padding: 18px 28px; border-top: 1px solid #f3f4f6;
    display: flex; gap: 10px;
}
body.dark .modal-foot { border-color: rgba(255,255,255,0.06); background: #191d27; }
.btn-cancel {
    flex: 1; padding: 11px; border-radius: var(--radius-sm);
    background: #f3f4f6; border: 2px solid #e5e7eb;
    color: #6b7280; font-size: 14px; font-weight: 600; cursor: pointer;
    transition: background 0.2s;
}
.btn-cancel:hover { background: #e5e7eb; }
.modal-foot .btn-primary { flex: 2; justify-content: center; padding: 11px; }

@media (max-width: 768px) {
    .stats-strip { grid-template-columns: 1fr; }
    .groups-grid { grid-template-columns: 1fr; }
    .mode-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-wrap">

<div class="pg-hdr">
    <div class="pg-title">
        <div class="pg-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><circle cx="7.5" cy="6.5" r="2.5"/><path d="M2 17c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><circle cx="14" cy="6" r="2"/><path d="M18 17c0-2.5-1.8-4.2-4-4.7"/></svg></div>
        <div>
            <div>Chia tiền nhóm</div>
            <div style="font-size:13px;font-weight:500;color:#6b7280;margin-top:2px;">Quản lý chi tiêu cùng gia đình & bạn bè</div>
        </div>
    </div>
    <button class="btn-primary" onclick="openCreate()">
        <span style="font-size:18px;line-height:1;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M10 4v12M4 10h12"/></svg></span> Tạo nhóm mới
    </button>
</div>

@if(session('success'))
<div class="alert alert-success"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M4 10l4.5 4.5L16 6"/></svg> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M10 2L2 17h16z"/><path d="M10 8v4M10 14.5v.5"/></svg> {{ session('error') }}</div>
@endif

{{-- Stats strip --}}
@php
    $total   = count($groups);
    $adminOf = collect($groups)->filter(fn($g) => $g['la_admin'])->count();
    $memberOf= $total - $adminOf;
@endphp

{{-- Groups grid --}}
<div class="groups-grid">
    @forelse($groups as $group)
    @php
        $modeClass = match($group['che_do']) {
            'balance' => 'mode-balance',
            'expense' => 'mode-expense',
            default   => 'mode-both',
        };
        $modeIcon = match($group['che_do']) {
            'balance' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M10 3v14M4 17h12"/><path d="M4 7 2 12h4zM16 7l-2 5h4z"/><path d="M4 7h12"/></svg>',
            'expense' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M5 2h10a1 1 0 011 1v14l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5V3a1 1 0 011-1z"/><path d="M7.5 7h5M7.5 10h5M7.5 13h3"/></svg>',
            default   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M3 6h9l3-3 3 3-3 3"/><path d="M3 14h9l3-3 3 3-3 3"/><path d="M6 9l-3 3M6 11l-3-3"/></svg>',
        };
        $modeName = match($group['che_do']) {
            'balance' => 'Phân phối số dư',
            'expense' => 'Chia khoản chi',
            default   => 'Cả hai chế độ',
        };
        $modeBadgeClass = match($group['che_do']) {
            'balance' => 'mode-b',
            'expense' => 'mode-e',
            default   => 'mode-m',
        };
    @endphp
    <a href="{{ route('groups.show', $group['id']) }}" class="group-card">
        <div class="gc-body">
            <div class="gc-top">
                <div class="gc-icon {{ $modeClass }}">{!! $modeIcon !!}</div>
                <div class="gc-badges">
                    <span class="gc-badge {{ $group['la_admin'] ? 'admin' : 'member' }}">
                        @if($group['la_admin'])
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M3 14L6 7l4 4 4-4 3 7H3z"/><path d="M3 14h14"/><circle cx="10" cy="3.5" r="1" fill="currentColor" stroke="none"/></svg> Admin
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><circle cx="10" cy="6.5" r="3"/><path d="M3.5 18c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"/></svg> Thành viên
                        @endif
                    </span>
                    <span class="gc-badge {{ $modeBadgeClass }}">{{ $modeName }}</span>
                </div>
            </div>
            <div class="gc-name">{{ $group['ten_nhom'] }}</div>
            <div class="gc-desc">{{ $group['mo_ta'] ?? 'Chưa có mô tả' }}</div>
            <div class="gc-members">
                <div class="gc-avatars">
                    @foreach($group['members'] as $mv)
                    @if($mv['avatar'])
                        @if(str_starts_with($mv['avatar'], 'http'))
                            <img src="{{ $mv['avatar'] }}" class="gc-av" style="object-fit:cover;" alt="">
                        @else
                            <img src="{{ asset('storage/' . $mv['avatar']) }}" class="gc-av" style="object-fit:cover;" alt="">
                        @endif
                    @else
                        <div class="gc-av" style="background:{{ $mv['color'] }}">
                            {{ strtoupper(substr($mv['name'], 0, 2)) }}
                        </div>
                    @endif
                    @endforeach
                    @if($group['so_thanh_vien'] > 4)
                    <div class="gc-av extra">+{{ $group['so_thanh_vien'] - 4 }}</div>
                    @endif
                </div>
                <span class="gc-member-count">{{ $group['so_thanh_vien'] }} thành viên</span>
            </div>
        </div>
        <div class="gc-footer">
            <span class="gc-date"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><rect x="3" y="4" width="14" height="14" rx="2"/><path d="M3 9h14M7 2v4M13 2v4"/><circle cx="7" cy="13" r="0.8" fill="currentColor" stroke="none"/><circle cx="10" cy="13" r="0.8" fill="currentColor" stroke="none"/><circle cx="13" cy="13" r="0.8" fill="currentColor" stroke="none"/></svg> {{ \Carbon\Carbon::parse($group['created_at'])->format('d/m/Y') }}</span>
            <div class="gc-arrow"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M8 4l6 6-6 6"/></svg></div>
        </div>
    </a>
    @empty
    <div class="empty-wrap">
        <div class="empty-icon-big"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><circle cx="7.5" cy="6.5" r="2.5"/><path d="M2 17c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><circle cx="14" cy="6" r="2"/><path d="M18 17c0-2.5-1.8-4.2-4-4.7"/></svg></div>
        <h3>Chưa có nhóm nào</h3>
        <p>Tạo nhóm đầu tiên để bắt đầu chia sẻ chi tiêu cùng gia đình hoặc bạn bè</p>
        <button class="btn-primary" onclick="openCreate()">
            <span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M10 4v12M4 10h12"/></svg></span> Tạo nhóm đầu tiên
        </button>
    </div>
    @endforelse
</div>

</div>{{-- end .page-wrap --}}

{{-- Modal tạo nhóm --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><circle cx="7.5" cy="6.5" r="2.5"/><path d="M2 17c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><circle cx="14" cy="6" r="2"/><path d="M18 17c0-2.5-1.8-4.2-4-4.7"/></svg> Tạo nhóm mới</div>
            <button class="modal-close" onclick="closeCreate()"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M5 5l10 10M15 5L5 15"/></svg></button>
        </div>
        <form action="{{ route('groups.store') }}" method="POST" class="js-rest">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tên nhóm <span class="required">*</span></label>
                    <input name="ten_nhom" class="form-ctrl" placeholder="VD: Gia đình, Du lịch Đà Lạt..." required maxlength="100" value="{{ old('ten_nhom') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <input name="mo_ta" class="form-ctrl" placeholder="Mô tả ngắn về nhóm..." maxlength="255" value="{{ old('mo_ta') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Chế độ hoạt động <span class="required">*</span></label>
                    <div class="mode-grid">
                        <label class="mode-card {{ old('che_do','both') == 'balance' ? 'selected' : '' }}" onclick="selectMode(this)">
                            <input type="radio" name="che_do" value="balance" {{ old('che_do') == 'balance' ? 'checked' : '' }}>
                            <div class="mode-emoji"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M10 3v14M4 17h12"/><path d="M4 7 2 12h4zM16 7l-2 5h4z"/><path d="M4 7h12"/></svg></div>
                            <div class="mode-name">Phân phối<br>số dư</div>
                            <div class="mode-desc">Chia lại tiền trong nhóm</div>
                        </label>
                        <label class="mode-card {{ old('che_do','both') == 'expense' ? 'selected' : '' }}" onclick="selectMode(this)">
                            <input type="radio" name="che_do" value="expense" {{ old('che_do') == 'expense' ? 'checked' : '' }}>
                            <div class="mode-emoji"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M5 2h10a1 1 0 011 1v14l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5V3a1 1 0 011-1z"/><path d="M7.5 7h5M7.5 10h5M7.5 13h3"/></svg></div>
                            <div class="mode-name">Chia khoản<br>chi</div>
                            <div class="mode-desc">Chia tiền khi thanh toán</div>
                        </label>
                        <label class="mode-card selected" onclick="selectMode(this)">
                            <input type="radio" name="che_do" value="both" checked>
                            <div class="mode-emoji"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0"><path d="M3 6h9l3-3 3 3-3 3"/><path d="M3 14h9l3-3 3 3-3 3"/><path d="M6 9l-3 3M6 11l-3-3"/></svg></div>
                            <div class="mode-name">Cả hai<br>chế độ</div>
                            <div class="mode-desc">Linh hoạt nhất</div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="closeCreate()">Hủy</button>
                <button type="submit" class="btn-primary">Tạo nhóm</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    function openCreate()  { document.getElementById('createModal').classList.add('active'); }
    function closeCreate() { document.getElementById('createModal').classList.remove('active'); }

    // Gắn vào window để onclick= trong HTML tìm thấy
    window.openCreate  = openCreate;
    window.closeCreate = closeCreate;

    window.selectMode = function(el) {
        document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        el.querySelector('input').checked = true;
    };

    document.getElementById('createModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeCreate();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeCreate();
    });

    @if($errors->any())
    openCreate();
    @endif

    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => {
            a.style.transition = 'opacity .3s';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 300);
        });
    }, 4500);
})();
</script>

@endsection
