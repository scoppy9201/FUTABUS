@extends('layouts.app')
@section('title', 'Chia khoản chi · ' . $group->ten_nhom)
@section('content')
<style>
:root {
    --primary:#4a90e2;--primary-dark:#2a5298;
    --success:#10b981;--danger:#ef4444;--warning:#f59e0b;
    --radius:16px;--radius-sm:10px;
    --shadow:0 2px 8px rgba(0,0,0,0.05);
    --transition:all 0.25s cubic-bezier(0.4,0,0.2,1);
}

.breadcrumb { display:flex;align-items:center;gap:8px;font-size:13px;color:#9ca3af;margin-bottom:20px; }
.breadcrumb a { color:var(--primary);text-decoration:none;font-weight:600; }

.top-bar { display:flex;justify-content:space-between;align-items:center;background:white;border-radius:var(--radius);padding:20px 26px;margin-bottom:22px;box-shadow:var(--shadow); }
body.dark .top-bar { background:#191d27; }
.top-bar-title { font-size:20px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:10px; }
body.dark .top-bar-title { color:#e5e7eb; }

.tab-nav { display:flex;gap:4px;background:#f3f4f6;border-radius:12px;padding:4px;margin-bottom:22px;width:fit-content; }
body.dark .tab-nav { background:rgba(255,255,255,0.06); }
.tab-btn {
    padding:9px 20px;border-radius:10px;font-size:13px;font-weight:700;
    border:none;cursor:pointer;transition:var(--transition);color:#6b7280;background:transparent;
    display:flex;align-items:center;gap:6px;
}
.tab-btn.active { background:white;color:var(--primary);box-shadow:0 2px 8px rgba(0,0,0,0.08); }
body.dark .tab-btn.active { background:#191d27;color:var(--primary); }
.tab-badge { background:var(--danger);color:white;font-size:10px;font-weight:800;padding:1px 6px;border-radius:20px; }

.alert { display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:var(--radius-sm);font-size:14px;font-weight:500;margin-bottom:18px; }
.alert-success { background:#d1fae5;color:#065f46;border-left:4px solid var(--success); }
.alert-error   { background:#fee2e2;color:#991b1b;border-left:4px solid var(--danger); }

.tab-content { display:none; }
.tab-content.active { display:block; }

.main-grid { display:grid;grid-template-columns:1fr 380px;gap:22px;align-items:start; }

.section-card { background:white;border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:20px; }
body.dark .section-card { background:#191d27; }
.sc-hdr { padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center; }
body.dark .sc-hdr { border-color:rgba(255,255,255,0.06); }
.sc-title { font-size:15px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:8px; }
body.dark .sc-title { color:#e5e7eb; }

.proposal-item { padding:16px 22px;border-bottom:1px solid #f9fafb;transition:background .15s; }
body.dark .proposal-item { border-color:rgba(255,255,255,0.03); }
.proposal-item:last-child { border-bottom:none; }
.proposal-item:hover { background:#f9fafb; }
body.dark .proposal-item:hover { background:rgba(255,255,255,0.02); }

.pi-top { display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:8px; }
.pi-desc { font-size:14px;font-weight:700;color:#1f2937;margin-bottom:3px; }
body.dark .pi-desc { color:#e5e7eb; }
.pi-meta { font-size:12px;color:#9ca3af; }
.pi-amount { font-size:18px;font-weight:900;color:var(--danger);letter-spacing:-0.5px;white-space:nowrap; }

.pi-status {
    padding:4px 11px;border-radius:20px;font-size:11px;font-weight:800;white-space:nowrap;flex-shrink:0;
    display:inline-flex;align-items:center;gap:4px;
}
.st-pending  { background:rgba(245,158,11,0.1);color:#b45309; }
.st-approved { background:rgba(16,185,129,0.1);color:#059669; }
.st-rejected { background:rgba(239,68,68,0.1);color:#dc2626; }
.st-cancelled{ background:rgba(107,114,128,0.1);color:#6b7280; }

.split-pills { display:flex;flex-wrap:wrap;gap:6px;margin:10px 0; }
.split-pill { display:flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;background:#f3f4f6;font-size:12px;font-weight:600;color:#374151; }
body.dark .split-pill { background:rgba(255,255,255,0.06);color:#9ca3af; }
.split-amount { color:var(--danger);font-weight:800; }

.pi-progress { height:5px;background:#e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:4px; }
body.dark .pi-progress { background:rgba(255,255,255,0.1); }
.pi-progress-fill { height:100%;border-radius:10px;background:linear-gradient(90deg,var(--primary),var(--primary-dark));transition:width .5s; }
.pi-progress-label { font-size:11px;color:#9ca3af; }
.pi-actions { display:flex;gap:8px;margin-top:10px;flex-wrap:wrap; }

.form-group { margin-bottom:16px; }
.form-label { font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:7px; }
body.dark .form-label { color:#9ca3af; }
.required { color:var(--danger); }
.form-ctrl { width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:var(--radius-sm);font-size:14px;background:#f9fafb;color:#1f2937;outline:none;transition:border-color .2s; }
.form-ctrl:focus { border-color:var(--primary);background:white; }
body.dark .form-ctrl { background:#141820;border-color:rgba(255,255,255,0.1);color:#e5e7eb; }

.kieu-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:6px; }
.kieu-card { border:2px solid #e5e7eb;border-radius:10px;padding:10px;text-align:center;cursor:pointer;transition:var(--transition);position:relative; }
.kieu-card.selected { border-color:var(--primary);background:rgba(74,144,226,0.05); }
body.dark .kieu-card { border-color:rgba(255,255,255,0.1); }
.kieu-card input { position:absolute;opacity:0;pointer-events:none; }
.kieu-icon { font-size:20px;margin-bottom:4px;display:flex;justify-content:center; }
.kieu-name { font-size:12px;font-weight:700;color:#374151; }
body.dark .kieu-name { color:#e5e7eb; }

.member-table { width:100%;border-collapse:collapse; }
.member-table td { padding:8px 0;border-bottom:1px solid #f3f4f6; }
body.dark .member-table td { border-color:rgba(255,255,255,0.05); }
.member-check { display:flex;align-items:center;gap:10px;cursor:pointer; }
.mem-av { width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:12px;font-weight:800;flex-shrink:0; }
.mem-name { font-size:13px;font-weight:600;color:#1f2937;flex:1; }
body.dark .mem-name { color:#e5e7eb; }
.mem-input { width:110px;padding:7px 10px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;font-weight:700;background:#f9fafb;color:#1f2937;outline:none;transition:border-color .2s; }
.mem-input:focus { border-color:var(--primary);background:white; }
body.dark .mem-input { background:#141820;border-color:rgba(255,255,255,0.1);color:#e5e7eb; }

.debt-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;margin-bottom:22px; }
.debt-flow-card { background:white;border-radius:14px;padding:20px;box-shadow:var(--shadow);border:2px solid transparent;transition:var(--transition);display:flex;align-items:center;gap:14px; }
.debt-flow-card:hover { border-color:var(--danger);transform:translateY(-2px);box-shadow:0 6px 20px rgba(239,68,68,0.1); }
body.dark .debt-flow-card { background:#191d27; }
.dfc-from,.dfc-to { text-align:center;flex:1; }
.dfc-av { width:44px;height:44px;border-radius:50%;margin:0 auto 6px;display:flex;align-items:center;justify-content:center;color:white;font-size:14px;font-weight:800; }
.dfc-name { font-size:13px;font-weight:700;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
body.dark .dfc-name { color:#e5e7eb; }
.dfc-arrow { font-size:24px;color:var(--danger);flex-shrink:0; }
.dfc-amount { font-size:11px;font-weight:700;color:var(--danger);margin-top:2px; }

.simplified-hdr { padding:16px 22px;border-bottom:1px solid #f3f4f6;background:rgba(239,68,68,0.04); }
body.dark .simplified-hdr { border-color:rgba(255,255,255,0.06); }

.btn-primary { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-sm);background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;transition:opacity .2s; }
.btn-primary:hover { opacity:.88; }
.btn-sm { padding:6px 12px;font-size:12px; }
.btn-success { background:linear-gradient(135deg,var(--success),#059669); }
.btn-danger  { background:linear-gradient(135deg,var(--danger),#dc2626); }
.btn-ghost { display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;background:#f3f4f6;border:2px solid #e5e7eb;color:#6b7280;font-size:12px;font-weight:600;cursor:pointer;transition:background .2s;text-decoration:none; }
.btn-ghost:hover { background:#e5e7eb; }

.empty-msg { text-align:center;padding:50px 20px;color:#9ca3af; }
.empty-msg-icon { font-size:40px;margin-bottom:12px; }
.empty-msg-text { font-weight:600; }

/* Kieu hint box */
#kieu-hint { display:flex;align-items:center;gap:6px;font-size:12px;color:#9ca3af;margin-top:8px; }
#kieu-hint svg { flex-shrink:0; }

.icon { width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0; }

@media (max-width:1100px) { .main-grid { grid-template-columns:1fr; } }
</style>

@php
$iconCheck  = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10l4.5 4.5L16 6"/></svg>';
$iconX      = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 5l10 10M15 5L5 15"/></svg>';
$iconClock  = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7.5"/><path d="M10 6v4.5l3 1.5"/></svg>';
$iconReceipt= '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 2h10a1 1 0 011 1v14l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5V3a1 1 0 011-1z"/><path d="M7.5 7h5M7.5 10h5M7.5 13h3"/></svg>';
$iconList   = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="4" width="10" height="14" rx="1.5"/><path d="M8 4V3a1 1 0 011-1h2a1 1 0 011 1v1"/><path d="M8 9h4M8 12h4M8 15h2"/></svg>';
$iconLeft   = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4L6 10l6 6"/></svg>';
$iconPlus   = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 4v12M4 10h12"/></svg>';
$iconEqual  = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h12"/><circle cx="10" cy="6" r="1.3" fill="currentColor" stroke="none"/><circle cx="10" cy="14" r="1.3" fill="currentColor" stroke="none"/></svg>';
$iconPen    = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13.5 3.5L16.5 6.5 7 16H4v-3z"/><path d="M11.5 5.5l3 3"/></svg>';
$iconPct    = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 15L15 5"/><circle cx="6.5" cy="6.5" r="2"/><circle cx="13.5" cy="13.5" r="2"/></svg>';
$iconBar    = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="3" height="6" rx="0.5"/><rect x="8.5" y="7" width="3" height="10" rx="0.5"/><rect x="14" y="4" width="3" height="13" rx="0.5"/><path d="M2 18h16"/></svg>';
$iconDoc    = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2z"/><path d="M7 7h6M7 10h6M7 13h4"/></svg>';
$iconDown   = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 6v8M6 10l4 4 4-4"/></svg>';
$iconInfo   = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2.5a5.5 5.5 0 014 9.3V14H6v-2.2A5.5 5.5 0 0110 2.5z"/><path d="M8 14v1.5a2 2 0 004 0V14"/></svg>';
$iconHash   = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h10M5 12h10M8 4l-2 12M12 4l-2 12"/></svg>';
@endphp

<div class="breadcrumb">
    <a href="{{ route('groups.index') }}">Nhóm</a>
    <span>/</span>
    <a href="{{ route('groups.show', $group) }}">{{ $group->ten_nhom }}</a>
    <span>/</span>
    <span>Chia khoản chi</span>
</div>

<div class="top-bar">
    <div class="top-bar-title">{!! $iconReceipt !!} Chia khoản chi · {{ $group->ten_nhom }}</div>
    <a href="{{ route('groups.show', $group) }}" class="btn-ghost">{!! $iconLeft !!} Quay lại</a>
</div>

@if(session('success'))<div class="alert alert-success">{!! $iconCheck !!} {{ session('success') }}</div>@endif

{{-- Tabs --}}
<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('expense',this)">
        {!! $iconReceipt !!} Chia khoản chi
        @if($myPending->count() > 0)
        <span class="tab-badge">{{ $myPending->count() }}</span>
        @endif
    </button>
    <button class="tab-btn" onclick="switchTab('debt',this)">
        {!! $iconBar !!} Ghi nợ thẳng
    </button>
</div>

{{-- ═══ TAB 1: EXPENSE ═══ --}}
<div class="tab-content active" id="tab-expense">
    <div class="main-grid">
        {{-- LEFT --}}
        <div>
            {{-- Pending --}}
            @if($myPending->count() > 0)
            <div class="section-card" style="margin-bottom:20px;border:2px solid rgba(245,158,11,0.3)">
                <div class="sc-hdr" style="background:rgba(245,158,11,0.05)">
                    <div class="sc-title">{!! $iconClock !!} Chờ bạn xác nhận ({{ $myPending->count() }})</div>
                </div>
                @foreach($myPending as $p)
                <div class="proposal-item">
                    <div class="pi-top">
                        <div>
                            <div class="pi-desc">{{ $p['mo_ta'] }}</div>
                            <div class="pi-meta">
                                Bởi {{ $p['proposed_by'] }} · {{ $p['ngay_chi']->format('d/m/Y') }}
                                @if($p['category']) · {{ $p['category'] }} @endif
                            </div>
                        </div>
                        <div class="pi-amount">-{{ number_format($p['tong_tien']) }}đ</div>
                    </div>
                    <div class="split-pills">
                        @foreach($p['splits'] as $s)
                        <div class="split-pill">
                            {{ $s['name'] }}
                            <span class="split-amount">{{ number_format($s['so_tien']) }}đ</span>
                            @if($s['ty_le']) <span style="color:#9ca3af">({{ $s['ty_le'] }}%)</span> @endif
                        </div>
                        @endforeach
                    </div>
                    <div class="pi-progress"><div class="pi-progress-fill" style="width:{{ $p['total_members']>0 ? round($p['approved_count']/$p['total_members']*100) : 0 }}%"></div></div>
                    <div class="pi-progress-label">{{ $p['approved_count'] }}/{{ $p['total_members'] }} người đồng ý</div>
                    <div class="pi-actions">
                        <form action="{{ route('groups.expense.approve', [$group, $p['id']]) }}" method="POST">
                            @csrf <button type="submit" class="btn-primary btn-success btn-sm">{!! $iconCheck !!} Đồng ý</button>
                        </form>
                        <form action="{{ route('groups.expense.reject', [$group, $p['id']]) }}" method="POST" style="display:inline">
                            @csrf <button type="submit" class="btn-primary btn-danger btn-sm">{!! $iconX !!} Từ chối</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- History --}}
            <div class="section-card">
                <div class="sc-hdr"><div class="sc-title">{!! $iconList !!} Lịch sử khoản chi</div></div>
                @forelse($proposals as $p)
                <div class="proposal-item">
                    <div class="pi-top">
                        <div>
                            <div class="pi-desc">{{ $p['mo_ta'] }}</div>
                            <div class="pi-meta">
                                {{ $p['proposed_by'] }} · {{ \Carbon\Carbon::parse($p['ngay_chi'])->format('d/m/Y') }}
                                · @if($p['kieu_chia']==='equal') Chia đều @elseif($p['kieu_chia']==='custom') Tùy chỉnh @else Theo % @endif
                                @if($p['category']) · {{ $p['category'] }} @endif
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
                            <div class="pi-amount">-{{ number_format($p['tong_tien']) }}đ</div>
                            {{-- FIX: dùng @if thay match() để tránh SVG bị escape --}}
                            @if($p['trang_thai']==='pending')
                                <span class="pi-status st-pending">{!! $iconClock !!} Chờ duyệt</span>
                            @elseif($p['trang_thai']==='approved')
                                <span class="pi-status st-approved">{!! $iconCheck !!} Thực hiện</span>
                            @elseif($p['trang_thai']==='rejected')
                                <span class="pi-status st-rejected">{!! $iconX !!} Từ chối</span>
                            @else
                                <span class="pi-status st-cancelled">— Đã hủy</span>
                            @endif
                        </div>
                    </div>
                    <div class="split-pills">
                        @foreach($p['splits'] as $s)
                        <div class="split-pill">{{ $s['name'] }} <span class="split-amount">{{ number_format($s['so_tien']) }}đ</span></div>
                        @endforeach
                    </div>
                    @if($p['trang_thai']==='pending')
                    <div class="pi-progress"><div class="pi-progress-fill" style="width:{{ $p['total_members']>0 ? round($p['approved_count']/$p['total_members']*100) : 0 }}%"></div></div>
                    <div class="pi-progress-label">{{ $p['approved_count'] }}/{{ $p['total_members'] }} người đồng ý</div>
                    @if($p['my_approval']===null)
                    <div class="pi-actions">
                        <form action="{{ route('groups.expense.approve', [$group, $p['id']]) }}" method="POST">
                            @csrf <button type="submit" class="btn-primary btn-success btn-sm">{!! $iconCheck !!} Đồng ý</button>
                        </form>
                        <form action="{{ route('groups.expense.reject', [$group, $p['id']]) }}" method="POST" style="display:inline">
                            @csrf <button type="submit" class="btn-primary btn-danger btn-sm">{!! $iconX !!} Từ chối</button>
                        </form>
                        <form action="{{ route('groups.expense.cancel', [$group, $p['id']]) }}" method="POST" style="display:inline">
                            @csrf <button type="submit" class="btn-ghost btn-sm">Hủy</button>
                        </form>
                    </div>
                    @else
                    <div style="font-size:12px;color:#9ca3af;margin-top:6px;display:flex;align-items:center;gap:4px;">
                        Bạn đã
                        @if($p['my_approval']==='approved')
                            {!! $iconCheck !!} đồng ý
                        @else
                            {!! $iconX !!} từ chối
                        @endif
                    </div>
                    @endif
                    @endif
                    @if($p['trang_thai']==='approved')
                    <div style="font-size:12px;color:var(--success);margin-top:6px;display:flex;align-items:center;gap:4px;">
                        {!! $iconCheck !!} Thực hiện {{ \Carbon\Carbon::parse($p['executed_at'])->format('d/m/Y H:i') }}
                    </div>
                    @endif
                </div>
                @empty
                <div class="empty-msg">
                    <div class="empty-msg-icon">{!! $iconReceipt !!}</div>
                    <div class="empty-msg-text">Chưa có khoản chi nào</div>
                    <div style="font-size:13px;margin-top:4px">Tạo đề xuất để chia tiền cùng nhau</div>
                </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT: Create form --}}
        <div>
            <div class="section-card">
                <div class="sc-hdr"><div class="sc-title">{!! $iconPlus !!} Tạo khoản chi chung</div></div>
                <form action="{{ route('groups.expense.store', $group) }}" method="POST" style="padding:20px">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Mô tả <span class="required">*</span></label>
                        <input name="mo_ta" class="form-ctrl" placeholder="VD: Tiền điện tháng 3, Bữa tối..." required maxlength="255" value="{{ old('mo_ta') }}">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label class="form-label">Tổng tiền <span class="required">*</span></label>
                            <input name="tong_tien" type="number" class="form-ctrl" placeholder="0" min="1000" max="100000000" required value="{{ old('tong_tien') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ngày <span class="required">*</span></label>
                            <input name="ngay_chi" type="date" class="form-ctrl" required value="{{ old('ngay_chi', date('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Danh mục</label>
                        <select name="category_id" class="form-ctrl">
                            <option value="">-- Không chọn --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'selected':'' }}>{{ $cat->ten_danh_muc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kiểu chia <span class="required">*</span></label>
                        <div class="kieu-grid">
                            <label class="kieu-card selected" onclick="selectKieu(this,'equal')">
                                <input type="radio" name="kieu_chia" value="equal" checked>
                                <div class="kieu-icon">{!! $iconEqual !!}</div>
                                <div class="kieu-name">Chia đều</div>
                            </label>
                            <label class="kieu-card" onclick="selectKieu(this,'custom')">
                                <input type="radio" name="kieu_chia" value="custom">
                                <div class="kieu-icon">{!! $iconPen !!}</div>
                                <div class="kieu-name">Tùy chỉnh</div>
                            </label>
                            <label class="kieu-card" onclick="selectKieu(this,'percentage')">
                                <input type="radio" name="kieu_chia" value="percentage">
                                <div class="kieu-icon">{!! $iconPct !!}</div>
                                <div class="kieu-name">Theo %</div>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Thành viên tham gia <span class="required">*</span></label>
                        <table class="member-table">
                            @foreach($members as $i => $m)
                            @php $colors=['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4']; $c=$colors[$i%count($colors)]; @endphp
                            <tr>
                                <td>
                                    <input type="hidden" name="phan_bo[{{ $i }}][user_id]" value="{{ $m->user_id }}">
                                    <div class="member-check">
                                        @if($m->user->avatar)
                                            @if(str_starts_with($m->user->avatar, 'http'))
                                                <img src="{{ $m->user->avatar }}" class="mem-av" style="object-fit:cover;" alt="">
                                            @else
                                                <img src="{{ asset('storage/' . $m->user->avatar) }}" class="mem-av" style="object-fit:cover;" alt="">
                                            @endif
                                        @else
                                            <div class="mem-av" style="background:linear-gradient(135deg,{{ $c }},{{ $c }}cc)">
                                                {{ strtoupper(substr($m->user->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <span class="mem-name">{{ $m->user->name }}</span>
                                    </div>
                                </td>
                                <td style="text-align:right">
                                    <input type="number" name="phan_bo[{{ $i }}][so_tien]"
                                        class="mem-input mem-so-tien" data-idx="{{ $i }}"
                                        placeholder="Số tiền" min="0" step="1000" style="display:none">
                                    <input type="number" name="phan_bo[{{ $i }}][ty_le]"
                                        class="mem-input mem-ty-le" data-idx="{{ $i }}"
                                        placeholder="%" min="0" max="100" step="1" style="display:none">
                                    <span class="mem-equal-label" style="font-size:12px;color:#10b981;font-weight:600;display:inline-flex;align-items:center;gap:4px;">{!! $iconCheck !!} Chia đều</span>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                        {{-- FIX: dùng innerHTML trong JS, không dùng textContent --}}
                        <div id="kieu-hint">
                            {!! $iconEqual !!} <span id="kieu-hint-text">Chia đều — hệ thống tự tính, không cần nhập</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px">
                        {!! $iconReceipt !!} Gửi đề xuất chia chi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ═══ TAB 2: DEBT ═══ --}}
<div class="tab-content" id="tab-debt">
    <div class="main-grid">
        {{-- LEFT --}}
        <div>
            <div class="section-card" style="margin-bottom:20px">
                <div class="sc-hdr simplified-hdr">
                    <div class="sc-title">{!! $iconHash !!} Sau khi rút gọn</div>
                    <a href="{{ route('groups.debt.summary', $group) }}" class="btn-ghost btn-sm">Xem chi tiết</a>
                </div>
                <div style="padding:16px">
                    <div class="debt-grid" id="debtFlowGrid">
                        <div class="empty-msg"><div class="empty-msg-icon">✓</div><div class="empty-msg-text">Không có ai nợ ai</div></div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="sc-hdr"><div class="sc-title">{!! $iconDoc !!} Danh sách nợ</div></div>
                <div style="padding:20px;text-align:center">
                    <a href="{{ route('groups.debt.summary', $group) }}" class="btn-primary">
                        {!! $iconBar !!} Xem tổng kết nợ đầy đủ
                    </a>
                </div>
            </div>
        </div>

        {{-- RIGHT: Add debt form --}}
        <div>
            <div class="section-card">
                <div class="sc-hdr"><div class="sc-title">{!! $iconDoc !!} Ghi nợ thẳng</div></div>
                <form action="{{ route('groups.debt.store', $group) }}" method="POST" style="padding:20px">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Người nợ <span class="required">*</span></label>
                        <select name="nguoi_no_id" class="form-ctrl" required>
                            <option value="">-- Chọn người nợ --</option>
                            @foreach($members as $m)
                            <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="text-align:center;font-size:20px;color:#9ca3af;margin:4px 0;display:flex;justify-content:center;align-items:center;gap:6px;">{!! $iconDown !!} nợ</div>
                    <div class="form-group">
                        <label class="form-label">Chủ nợ <span class="required">*</span></label>
                        <select name="chu_no_id" class="form-ctrl" required>
                            <option value="">-- Chọn chủ nợ --</option>
                            @foreach($members as $m)
                            <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Số tiền <span class="required">*</span></label>
                        <input type="number" name="so_tien" class="form-ctrl" placeholder="0" min="1000" max="100000000" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ghi chú</label>
                        <input name="ghi_chu" class="form-ctrl" placeholder="VD: Tiền điện tháng 3..." maxlength="255">
                    </div>
                    <div style="background:rgba(74,144,226,0.06);border-radius:10px;padding:12px;font-size:12px;color:#4b5563;margin-bottom:16px;display:flex;align-items:flex-start;gap:8px;">
                        {!! $iconInfo !!} <span>Ghi nợ thẳng sẽ được xác nhận ngay (không cần toàn bộ đồng ý). Dùng khi bạn chắc chắn về khoản nợ.</span>
                    </div>
                    <button type="submit" class="btn-primary btn-danger" style="width:100%;justify-content:center;padding:13px">
                        {!! $iconDoc !!} Ghi nhận nợ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

// FIX: chỉ cập nhật text, icon đã có sẵn trong #kieu-hint qua Blade
// Dùng innerHTML để tránh SVG bị render thành text
const hintMap = {
    equal:      'Chia đều — hệ thống tự tính, không cần nhập',
    custom:     'Tùy chỉnh — nhập số tiền cho từng người (tổng phải bằng tổng tiền)',
    percentage: 'Theo % — nhập tỷ lệ % cho từng người (tổng phải bằng 100%)',
};

function selectKieu(el, kieu) {
    document.querySelectorAll('.kieu-card').forEach(k => k.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;

    const soTienInputs = document.querySelectorAll('.mem-so-tien');
    const tyLeInputs   = document.querySelectorAll('.mem-ty-le');
    const equalLabels  = document.querySelectorAll('.mem-equal-label');

    soTienInputs.forEach(i => { i.style.display='none'; i.required=false; i.value=''; });
    tyLeInputs.forEach(i   => { i.style.display='none'; i.required=false; i.value=''; });
    equalLabels.forEach(l  => { l.style.display='none'; });

    if (kieu === 'equal') {
        equalLabels.forEach(l => l.style.display='inline-flex');
    } else if (kieu === 'custom') {
        soTienInputs.forEach(i => { i.style.display='block'; i.required=true; });
    } else if (kieu === 'percentage') {
        tyLeInputs.forEach(i => { i.style.display='block'; i.required=true; });
    }

    // Chỉ cập nhật text, không gán innerHTML có SVG (tránh lỗi XSS)
    document.getElementById('kieu-hint-text').textContent = hintMap[kieu] || '';
}

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition='opacity .3s'; a.style.opacity='0';
        setTimeout(()=>a.remove(),300);
    });
}, 4500);
</script>
@endsection
