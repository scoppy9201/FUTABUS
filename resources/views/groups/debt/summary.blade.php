@extends('layouts.app')
@section('title', 'Tổng kết nợ · ' . $group->ten_nhom)
@section('content')
<style>
:root { --primary:#4a90e2;--primary-dark:#2a5298;--success:#10b981;--danger:#ef4444;--warning:#f59e0b;--radius:16px;--radius-sm:10px;--shadow:0 2px 8px rgba(0,0,0,0.05); }

.breadcrumb { display:flex;align-items:center;gap:8px;font-size:13px;color:#9ca3af;margin-bottom:20px; }
.breadcrumb a { color:var(--primary);text-decoration:none;font-weight:600; }

.top-bar { display:flex;justify-content:space-between;align-items:center;background:white;border-radius:var(--radius);padding:20px 26px;margin-bottom:22px;box-shadow:var(--shadow); }
body.dark .top-bar { background:#191d27; }
.top-bar-title { font-size:20px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:10px; }
body.dark .top-bar-title { color:#e5e7eb; }

.alert { display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:var(--radius-sm);font-size:14px;font-weight:500;margin-bottom:18px; }
.alert-success { background:#d1fae5;color:#065f46;border-left:4px solid var(--success); }
.alert-error   { background:#fee2e2;color:#991b1b;border-left:4px solid var(--danger); }

/* Balance cards */
.balance-row {
    display:flex;flex-wrap:wrap;justify-content:center;
    gap:12px;margin-bottom:24px;
}
.bal-card {
    background:white;border-radius:14px;padding:16px 18px;box-shadow:var(--shadow);
    border-left:4px solid transparent;transition:all .2s;
    width:170px;flex-shrink:0;text-align:center;
}
body.dark .bal-card { background:#191d27; }
.bal-card.pos { border-color:var(--success); }
.bal-card.neg { border-color:var(--danger); }
.bal-card.zero{ border-color:#e5e7eb; }
.bal-av { width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:800;margin:0 auto 10px; }
.bal-name { font-size:13px;font-weight:700;color:#1f2937;margin-bottom:3px; }
body.dark .bal-name { color:#e5e7eb; }
.bal-val { font-size:18px;font-weight:900; }
.bal-val.pos { color:var(--success); }
.bal-val.neg { color:var(--danger); }
.bal-val.zero { color:#9ca3af;font-size:14px; }
.bal-label { font-size:11px;color:#9ca3af;margin-top:2px; }

/* Section */
.section-card { background:white;border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:22px; }
body.dark .section-card { background:#191d27; }
.sc-hdr { padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center; }
body.dark .sc-hdr { border-color:rgba(255,255,255,0.06); }
.sc-title { font-size:15px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:8px; }
body.dark .sc-title { color:#e5e7eb; }

/* Simplified flows */
.flows-grid { display:flex;flex-wrap:wrap;justify-content:center;gap:14px;padding:20px; }

.flow-card {
    background:#fafafa;border:2px solid #f3f4f6;border-radius:14px;
    padding:18px 20px;display:flex;align-items:center;gap:12px;
    transition:all .2s;width:260px;flex-shrink:0;
}
.flow-card:hover { border-color:var(--danger);transform:translateY(-2px);box-shadow:0 6px 18px rgba(239,68,68,0.1); }
body.dark .flow-card { background:rgba(255,255,255,0.02);border-color:rgba(255,255,255,0.08); }

.fc-person { text-align:center;flex:1;min-width:0; }
.fc-av { width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:15px;font-weight:800;margin:0 auto 6px; }
.fc-name { font-size:12px;font-weight:700;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
body.dark .fc-name { color:#e5e7eb; }

.fc-mid { display:flex;flex-direction:column;align-items:center;gap:4px;flex-shrink:0; }
.fc-arrow { font-size:22px;color:var(--danger); }
.fc-amount { font-size:13px;font-weight:800;color:var(--danger); }

.fc-settle-btn {
    display:block;margin-top:10px;width:100%;
    padding:7px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;
    background:rgba(16,185,129,0.1);color:var(--success);transition:background .2s;
}
.fc-settle-btn:hover { background:rgba(16,185,129,0.2); }

/* Cleared state */
.cleared-box {
    text-align: center;
    padding: 60px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
}
.cleared-icon { font-size:56px;margin-bottom:16px; }
.cleared-title { font-size:20px;font-weight:800;color:#1f2937;margin-bottom:8px; }
body.dark .cleared-title { color:#e5e7eb; }
.cleared-sub { font-size:14px;color:#9ca3af; }

/* Raw debt table */
.debt-table { width:100%;border-collapse:collapse; }
.debt-table th { padding:10px 20px;text-align:left;font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.6px;background:#f9fafb;border-bottom:1px solid #f3f4f6; }
body.dark .debt-table th { background:rgba(255,255,255,0.02);border-color:rgba(255,255,255,0.06); }
.debt-table td { padding:14px 20px;border-bottom:1px solid #f9fafb;font-size:13px;color:#374151; }
body.dark .debt-table td { border-color:rgba(255,255,255,0.03);color:#9ca3af; }
.debt-table tbody tr:hover { background:#f9fafb; }
body.dark .debt-table tbody tr:hover { background:rgba(255,255,255,0.02); }

.debt-av-sm { width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:800; }
.debt-persons { display:flex;align-items:center;gap:8px; }
.debt-arrow-sm { color:#9ca3af;font-size:16px; }

.st-badge { padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700; }
.st-confirmed { background:rgba(245,158,11,0.1);color:#b45309; }
.st-settled { background:rgba(16,185,129,0.1);color:#059669; }

/* Buttons */
.btn-primary { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-sm);background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;transition:opacity .2s; }
.btn-primary:hover { opacity:.88; }
.btn-sm { padding:6px 12px;font-size:12px; }
.btn-success { background:linear-gradient(135deg,var(--success),#059669); }
.btn-ghost { display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;background:#f3f4f6;border:2px solid #e5e7eb;color:#6b7280;font-size:12px;font-weight:600;cursor:pointer;transition:background .2s;text-decoration:none; }
.btn-ghost:hover { background:#e5e7eb; }

/* Balance cards */
body.dark .bal-card { background: #191d27; }
body.dark .bal-card.zero { border-color: rgba(255,255,255,0.08); }
body.dark .bal-name { color: #e5e7eb; }
body.dark .bal-label { color: #6b7280; }

/* btn-ghost */
body.dark .btn-ghost {
    background: rgba(255,255,255,0.06);
    border-color: rgba(255,255,255,0.1);
    color: #9ca3af;
}
body.dark .btn-ghost:hover { background: rgba(255,255,255,0.12); color: #e5e7eb; }

/* Flow cards */
body.dark .flow-card {
    background: rgba(255,255,255,0.02);
    border-color: rgba(255,255,255,0.08);
}
body.dark .flow-card:hover {
    border-color: var(--danger);
    box-shadow: 0 6px 18px rgba(239,68,68,0.12);
}
body.dark .fc-name { color: #e5e7eb; }

/* Settle button inside flow card */
body.dark .fc-settle-btn {
    background: rgba(16,185,129,0.12);
    color: #6ee7b7;
}
body.dark .fc-settle-btn:hover { background: rgba(16,185,129,0.2); }

/* Cleared box */
body.dark .cleared-title { color: #e5e7eb; }
body.dark .cleared-sub { color: #6b7280; }

/* sc-hdr subtle tint */
body.dark .sc-hdr[style*="rgba(239,68,68,0.04)"] {
    background: rgba(239,68,68,0.07) !important;
}

/* Debt table */
body.dark .debt-table th {
    background: rgba(255,255,255,0.02);
    border-color: rgba(255,255,255,0.06);
    color: #6b7280;
}
body.dark .debt-table td {
    border-color: rgba(255,255,255,0.03);
    color: #9ca3af;
}
body.dark .debt-table tbody tr:hover { background: rgba(255,255,255,0.02); }

/* Person names in debt table (hardcoded inline color:#1f2937) */
body.dark .debt-persons strong { color: #e5e7eb !important; }
body.dark .debt-arrow-sm { color: #4b5563; }

/* Status badges */
body.dark .st-confirmed { background: rgba(245,158,11,0.12); color: #fbbf24; }
body.dark .st-settled   { background: rgba(16,185,129,0.12); color: #6ee7b7; }

/* Section subtitle text */
body.dark .sc-hdr div[style*="color:#9ca3af"] { color: #4b5563 !important; }

/* Empty state */
body.dark div[style*="color:#9ca3af"] { color: #6b7280; }
</style>

<div class="breadcrumb">
    <a href="{{ route('groups.index') }}">Nhóm</a> <span>/</span>
    <a href="{{ route('groups.show', $group) }}">{{ $group->ten_nhom }}</a> <span>/</span>
    <span>Tổng kết nợ</span>
</div>

<div class="top-bar">
    <div class="top-bar-title"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><rect x="3" y="11" width="3" height="6" rx="0.5"/><rect x="8.5" y="7" width="3" height="10" rx="0.5"/><rect x="14" y="4" width="3" height="13" rx="0.5"/><path d="M2 18h16"/></svg> Tổng kết nợ · {{ $group->ten_nhom }}</div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('groups.expense.index', $group) }}" class="btn-ghost"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M12 4L6 10l6 6"/></svg> Chia khoản chi</a>
        <a href="{{ route('groups.show', $group) }}" class="btn-ghost">Nhóm</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" stroke-width="2.5"><path d="M4 10l4.5 4.5L16 6"/></svg> {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M10 2L2 17h16z"/><path d="M10 8v4M10 14.5v.5"/></svg> {{ session('error') }}</div>@endif

{{-- Balance overview --}}
<div class="balance-row">
    @foreach($balances as $userId => $balance)
    @php
        $member = $members[$userId] ?? null;
        if (!$member) continue;
        $colors=['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
        $c = $colors[$loop->index % count($colors)];
        $cls = $balance > 1 ? 'pos' : ($balance < -1 ? 'neg' : 'zero');
        $prefix = $balance > 1 ? '+' : '';
    @endphp
    <div class="bal-card {{ $cls }}">
        @if($member->user->avatar)
            @if(str_starts_with($member->user->avatar, 'http'))
                <img src="{{ $member->user->avatar }}" class="bal-av" style="object-fit:cover;" alt="">
            @else
                <img src="{{ asset('storage/' . $member->user->avatar) }}" class="bal-av" style="object-fit:cover;" alt="">
            @endif
        @else
            <div class="bal-av" style="background:linear-gradient(135deg,{{ $c }},{{ $c }}cc)">
                {{ strtoupper(substr($member->user->name, 0, 2)) }}
            </div>
        @endif
        <div class="bal-name">{{ $member->user->name }}</div>
        @if(abs($balance) <= 1)
        <div class="bal-val zero">Không nợ</div>
        @else
        <div class="bal-val {{ $cls }}">{{ $prefix }}{{ number_format(abs($balance)) }}đ</div>
        <div class="bal-label">{!! $balance > 1 ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M12 4L6 10l6 6"/></svg> Đang được nợ' : '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M8 4l6 6-6 6"/></svg> Đang nợ người khác' !!}</div>
        @endif
    </div>
    @endforeach
</div>

{{-- Simplified debts --}}
<div class="section-card">
    <div class="sc-hdr" style="background:rgba(239,68,68,0.04)">
        <div class="sc-title"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M5 8h10M5 12h10M8 4l-2 12M12 4l-2 12"/></svg> Giao dịch tối giản cần thực hiện</div>
        <div style="font-size:12px;color:#9ca3af">Thuật toán rút gọn số giao dịch xuống mức tối thiểu</div>
    </div>

    @if(count($simplified) === 0)
    <div class="cleared-box">
        <div class="cleared-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M4 16L9 4l7 7z"/><path d="M9 4l2 2M14 8l2 2"/><circle cx="15" cy="5" r="1" fill="currentColor" stroke="none"/><circle cx="5" cy="4" r="0.8" fill="currentColor" stroke="none"/></svg></div>
        <div class="cleared-title">Không có ai nợ ai!</div>
        <div class="cleared-sub">Tất cả các khoản nợ đã được cân bằng hoặc thanh toán</div>
    </div>
    @else
    <div class="flows-grid" style="justify-content:center;align-items:center;">
        @foreach($simplified as $flow)
        @php
            $colors=['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
            $fromMember = $members[$flow['from']] ?? null;
            $toMember   = $members[$flow['to']] ?? null;
            $cf = $colors[array_search($flow['from'], array_keys($members->toArray())) % count($colors)];
            $ct = $colors[array_search($flow['to'],   array_keys($members->toArray())) % count($colors)];
        @endphp
        <div class="flow-card">
            <div class="fc-person">
                @if($members[$flow['from']]->user->avatar ?? null)
                    @if(str_starts_with($members[$flow['from']]->user->avatar, 'http'))
                        <img src="{{ $members[$flow['from']]->user->avatar }}" class="fc-av" style="object-fit:cover;margin:0 auto 6px;display:block;" alt="">
                    @else
                        <img src="{{ asset('storage/' . $members[$flow['from']]->user->avatar) }}" class="fc-av" style="object-fit:cover;margin:0 auto 6px;display:block;" alt="">
                    @endif
                @else
                    <div class="fc-av" style="background:linear-gradient(135deg,{{$cf}},{{$cf}}cc)">
                        {{ strtoupper(substr($flow['from_name'], 0, 2)) }}
                    </div>
                @endif
                <div class="fc-name">{{ $flow['from_name'] }}</div>
                <div style="font-size:11px;color:var(--danger);font-weight:600">nợ</div>
            </div>
            <div class="fc-mid">
                <div class="fc-arrow"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M8 4l6 6-6 6"/></svg></div>
                <div class="fc-amount">{{ number_format($flow['amount']) }}đ</div>
            </div>
            <div class="fc-person">
                @if($members[$flow['to']]->user->avatar ?? null)
                    @if(str_starts_with($members[$flow['to']]->user->avatar, 'http'))
                        <img src="{{ $members[$flow['to']]->user->avatar }}" class="fc-av" style="object-fit:cover;margin:0 auto 6px;display:block;" alt="">
                    @else
                        <img src="{{ asset('storage/' . $members[$flow['to']]->user->avatar) }}" class="fc-av" style="object-fit:cover;margin:0 auto 6px;display:block;" alt="">
                    @endif
                @else
                    <div class="fc-av" style="background:linear-gradient(135deg,{{$ct}},{{$ct}}cc)">
                        {{ strtoupper(substr($flow['to_name'], 0, 2)) }}
                    </div>
                @endif
                <div class="fc-name">{{ $flow['to_name'] }}</div>
                <div style="font-size:11px;color:var(--success);font-weight:600">nhận</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Raw list --}}
<div class="section-card">
    <div class="sc-hdr">
        <div class="sc-title"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M6 2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2z"/><path d="M7 7h6M7 10h6M7 13h4"/></svg> Danh sách nợ gốc</div>
    </div>
    @if($rawList->count() > 0)
    <div style="overflow-x:auto">
        <table class="debt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Người nợ <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M8 4l6 6-6 6"/></svg> Chủ nợ</th>
                    <th>Số tiền</th>
                    <th>Ghi chú</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rawList as $i => $d)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>
                        <div class="debt-persons">
                            @php
                                $colors=['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
                            @endphp
                            @if($d['nguoi_no_avatar'] ?? null)
                                @if(str_starts_with($d['nguoi_no_avatar'], 'http'))
                                    <img src="{{ $d['nguoi_no_avatar'] }}" class="debt-av-sm" style="object-fit:cover;" alt="">
                                @else
                                    <img src="{{ asset('storage/' . $d['nguoi_no_avatar']) }}" class="debt-av-sm" style="object-fit:cover;" alt="">
                                @endif
                            @else
                                <div class="debt-av-sm" style="background:{{ $colors[$i%count($colors)] }}">
                                    {{ strtoupper(substr($d['nguoi_no'], 0, 2)) }}
                                </div>
                            @endif
                            <strong style="font-size:13px;color:#1f2937">{{ $d['nguoi_no'] }}</strong>
                            <span class="debt-arrow-sm"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M8 4l6 6-6 6"/></svg></span>
                            @if($d['chu_no_avatar'] ?? null)
                                @if(str_starts_with($d['chu_no_avatar'], 'http'))
                                    <img src="{{ $d['chu_no_avatar'] }}" class="debt-av-sm" style="object-fit:cover;" alt="">
                                @else
                                    <img src="{{ asset('storage/' . $d['chu_no_avatar']) }}" class="debt-av-sm" style="object-fit:cover;" alt="">
                                @endif
                            @else
                                <div class="debt-av-sm" style="background:{{ $colors[($i+1)%count($colors)] }}">
                                    {{ strtoupper(substr($d['chu_no'], 0, 2)) }}
                                </div>
                            @endif
                            <strong style="font-size:13px;color:#1f2937">{{ $d['chu_no'] }}</strong>
                        </div>
                    </td>
                    <td><strong style="color:var(--danger)">{{ number_format($d['so_tien']) }}đ</strong></td>
                    <td style="color:#6b7280">{{ $d['ghi_chu'] ?? '—' }}</td>
                    <td><span class="st-badge st-{{ $d['trang_thai'] }}">{!! $d['trang_thai']==='settled'?'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" stroke-width="2.5"><path d="M4 10l4.5 4.5L16 6"/></svg> Đã trả':'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><circle cx="10" cy="10" r="7.5"/><path d="M10 6v4.5l3 1.5"/></svg> Chờ trả' !!}</span></td>
                    <td>
                        @if($d['trang_thai'] !== 'settled')
                        <form action="{{ route('groups.debt.settle', [$group, $d['id']]) }}" method="POST" style="display:inline" class="js-rest">
                            @csrf
                            <input type="hidden" name="ghi_vao_so" value="1">
                            <button type="submit" class="btn-primary btn-success btn-sm"
                                onclick="return confirm('Đánh dấu đã thanh toán và ghi vào sổ?')">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" stroke-width="2.5"><path d="M4 10l4.5 4.5L16 6"/></svg> Đã trả
                            </button>
                        </form>
                        @else
                        <span style="font-size:12px;color:#9ca3af">Hoàn tất</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align:center;padding:50px;color:#9ca3af">
        <div style="font-size:40px;margin-bottom:12px"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em;flex-shrink:0" ><path d="M6 2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2z"/><path d="M7 7h6M7 10h6M7 13h4"/></svg></div>
        <div style="font-weight:600">Chưa có khoản nợ nào</div>
    </div>
    @endif
</div>

<script>
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition='opacity .3s'; a.style.opacity='0';
        setTimeout(()=>a.remove(),300);
    });
}, 4500);
</script>
<script>
async function refreshDebtSummary() {
    try {
        const GROUP_ID = {{ $group->id }};
        const res = await fetch(`/api/v1/groups/${GROUP_ID}/debts/summary`, { credentials: 'same-origin' });
        if (!res.ok) return;
        const j = await res.json();
        if (!j) return;

        // update balances
        if (j.balances) {
            const balRow = document.querySelector('.balance-row');
            if (balRow) {
                balRow.innerHTML = Object.entries(j.balances).map(([uid, bal], i) => {
                    const member = (j.members || []).find(m => m.user_id == uid) || {};
                    const initials = (member.name||'').substr(0,2).toUpperCase();
                    const cls = bal > 1 ? 'pos' : (bal < -1 ? 'neg' : 'zero');
                    const prefix = bal > 1 ? '+' : '';
                    const avatar = member.avatar ? (member.avatar.startsWith('http') ? member.avatar : '/storage/' + member.avatar) : null;
                    return `
                        <div class="bal-card ${cls}">
                            ${avatar ? `<img src="${avatar}" class="bal-av" style="object-fit:cover;" alt="">` : `<div class="bal-av" style="background:linear-gradient(135deg,#4a90e2,#4a90e2cc)">${initials}</div>`}
                            <div class="bal-name">${escapeHtml(member.name || '—')}</div>
                            ${Math.abs(bal) <= 1 ? `<div class="bal-val zero">Không nợ</div>` : `<div class="bal-val ${cls}">${prefix}${Number(Math.abs(bal)).toLocaleString('vi-VN')}đ</div>`}
                        </div>`;
                }).join('');
            }
        }

        // simplified flows
        if (j.simplified) {
            const grid = document.querySelector('.flows-grid');
            if (grid) {
                if (j.simplified.length === 0) {
                    grid.innerHTML = '<div class="cleared-box"><div class="cleared-icon">✔</div><div class="cleared-title">Không có ai nợ ai!</div><div class="cleared-sub">Tất cả các khoản nợ đã được cân bằng hoặc thanh toán</div></div>';
                } else {
                    grid.innerHTML = j.simplified.map(flow => `
                        <div class="flow-card">
                            <div class="fc-person"><div class="fc-av" style="background:linear-gradient(135deg,#4a90e2,#4a90e2cc)">${(flow.from_name||'').substr(0,2).toUpperCase()}</div><div class="fc-name">${escapeHtml(flow.from_name)}</div><div style="font-size:11px;color:var(--danger);font-weight:600">nợ</div></div>
                            <div class="fc-mid"><div class="fc-arrow">→</div><div class="fc-amount">${Number(flow.amount).toLocaleString('vi-VN')}đ</div></div>
                            <div class="fc-person"><div class="fc-av" style="background:linear-gradient(135deg,#10b981,#10b981cc)">${(flow.to_name||'').substr(0,2).toUpperCase()}</div><div class="fc-name">${escapeHtml(flow.to_name)}</div><div style="font-size:11px;color:var(--success);font-weight:600">nhận</div></div>
                        </div>
                    `).join('');
                }
            }
        }

        // rawList table
        if (j.rawList) {
            const tbody = document.querySelector('.debt-table tbody');
            if (tbody) {
                tbody.innerHTML = j.rawList.map((d, i) => `
                    <tr>
                        <td>${i+1}</td>
                        <td>
                            <div class="debt-persons">
                                ${d.nguoi_no_avatar ? `<img src="${(d.nguoi_no_avatar.startsWith('http')?d.nguoi_no_avatar:'/storage/'+d.nguoi_no_avatar)}" class="debt-av-sm" style="object-fit:cover;">` : `<div class="debt-av-sm" style="background:#4a90e2">${(d.nguoi_no||'').substr(0,2).toUpperCase()}</div>`}
                                <strong style="font-size:13px;color:#1f2937">${escapeHtml(d.nguoi_no)}</strong>
                                <span class="debt-arrow-sm">→</span>
                                ${d.chu_no_avatar ? `<img src="${(d.chu_no_avatar.startsWith('http')?d.chu_no_avatar:'/storage/'+d.chu_no_avatar)}" class="debt-av-sm" style="object-fit:cover;">` : `<div class="debt-av-sm" style="background:#10b981">${(d.chu_no||'').substr(0,2).toUpperCase()}</div>`}
                                <strong style="font-size:13px;color:#1f2937">${escapeHtml(d.chu_no)}</strong>
                            </div>
                        </td>
                        <td><strong style="color:var(--danger)">${Number(d.so_tien).toLocaleString('vi-VN')}đ</strong></td>
                        <td style="color:#6b7280">${escapeHtml(d.ghi_chu || '—')}</td>
                        <td><span class="st-badge st-${d.trang_thai}">${escapeHtml(d.trang_thai)}</span></td>
                        <td>${d.trang_thai !== 'settled' ? `<button class="btn-primary btn-success btn-sm">Đã trả</button>` : '<span style="font-size:12px;color:#9ca3af">Hoàn tất</span>'}</td>
                    </tr>
                `).join('');
            }
        }
    } catch (e) {}
}

function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

document.addEventListener('DOMContentLoaded', () => refreshDebtSummary());
</script>
@endsection
