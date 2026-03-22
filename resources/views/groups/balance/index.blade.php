@extends('layouts.app')
@section('title', 'Phân phối số dư · ' . $group->ten_nhom)
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

/* Top bar */
.top-bar {
    display:flex;justify-content:space-between;align-items:center;
    background:white;border-radius:var(--radius);padding:20px 26px;
    margin-bottom:22px;box-shadow:var(--shadow);
}
body.dark .top-bar { background:#191d27; }
.top-bar-title { font-size:20px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:10px; }
body.dark .top-bar-title { color:#e5e7eb; }

/* Alert */
.alert { display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:var(--radius-sm);font-size:14px;font-weight:500;margin-bottom:18px; }
.alert-success { background:#d1fae5;color:#065f46;border-left:4px solid var(--success); }
.alert-error   { background:#fee2e2;color:#991b1b;border-left:4px solid var(--danger); }
.alert-warn    { background:rgba(245,158,11,0.1);color:#92400e;border-left:4px solid var(--warning); }

/* Balance overview */
.balance-grid {
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:16px;
    margin-bottom:24px;
}
.bal-card {
    width:180px;
    flex-shrink:0;
}
.bal-card {
    background:white;border-radius:14px;padding:18px 20px;
    box-shadow:var(--shadow);border:1px solid rgba(255,255,255,0.8);
    transition:var(--transition);
    text-align:center;
}
.bal-card:hover { transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,0.08); }
body.dark .bal-card { background:#191d27;border-color:rgba(255,255,255,0.06); }
.bal-av {
    width:44px;height:44px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    color:white;font-size:16px;font-weight:800;
    margin:0 auto 12px;
}
.bal-name { font-size:14px;font-weight:700;color:#1f2937;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
body.dark .bal-name { color:#e5e7eb; }
.bal-amount { font-size:20px;font-weight:900;letter-spacing:-0.5px; }
.bal-amount.pos { color:var(--success); }
.bal-amount.neg { color:var(--danger); }
.bal-amount.hidden-val { color:#9ca3af;font-size:14px;font-weight:600; }

/* Total row */
.total-row {
    background:linear-gradient(135deg,var(--primary),var(--primary-dark));
    border-radius:14px;padding:18px 24px;margin-bottom:24px;
    display:flex;justify-content:space-between;align-items:center;
    box-shadow:0 4px 16px rgba(74,144,226,0.3);
}
.total-label { font-size:13px;color:rgba(255,255,255,0.75);font-weight:600; }
.total-value { font-size:26px;font-weight:900;color:white;letter-spacing:-0.5px; }

/* Main grid */
.main-grid { display:block; }
.main-left  { width:100%; }
.main-right { width:100%; margin-top:20px; }

/* Propose form */
.propose-card {
    background:white;border-radius:var(--radius);
    box-shadow:var(--shadow);overflow:hidden;margin-bottom:20px;
}
body.dark .propose-card { background:#191d27; }
.propose-hdr {
    padding:18px 22px;border-bottom:1px solid #f3f4f6;
    background:linear-gradient(135deg,rgba(74,144,226,0.06),transparent);
}
body.dark .propose-hdr { border-color:rgba(255,255,255,0.06); }
.propose-hdr-title { font-size:15px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:8px; }
body.dark .propose-hdr-title { color:#e5e7eb; }

.propose-body { padding:22px; }

.form-label { font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:7px; }
body.dark .form-label { color:#9ca3af; }
.form-ctrl {
    width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:var(--radius-sm);
    font-size:14px;background:#f9fafb;color:#1f2937;outline:none;
    transition:border-color .2s;
}
.form-ctrl:focus { border-color:var(--primary);background:white; }
body.dark .form-ctrl { background:#141820;border-color:rgba(255,255,255,0.1);color:#e5e7eb; }

/* Allocation rows */
.alloc-table { width:100%;border-collapse:collapse;margin-top:12px; }
.alloc-table th {
    padding:8px 12px;text-align:left;font-size:11px;font-weight:700;
    color:#9ca3af;text-transform:uppercase;letter-spacing:0.6px;
    background:#f9fafb;border-bottom:1px solid #f3f4f6;
}
body.dark .alloc-table th { background:rgba(255,255,255,0.03);border-color:rgba(255,255,255,0.06);color:#6b7280; }
.alloc-table td { padding:10px 12px;border-bottom:1px solid #f9fafb; }
body.dark .alloc-table td { border-color:rgba(255,255,255,0.03); }
.alloc-user { display:flex;align-items:center;gap:10px; }
.alloc-av { width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:12px;font-weight:800;flex-shrink:0; }
.alloc-uname { font-size:13px;font-weight:600;color:#1f2937; }
body.dark .alloc-uname { color:#e5e7eb; }
.alloc-cur { font-size:11px;color:#9ca3af; }
.alloc-input {
    width:100%;padding:8px 11px;border:2px solid #e5e7eb;border-radius:8px;
    font-size:13px;font-weight:700;background:#f9fafb;color:#1f2937;outline:none;
    transition:border-color .2s;
}
.alloc-input:focus { border-color:var(--primary);background:white; }
body.dark .alloc-input { background:#141820;border-color:rgba(255,255,255,0.1);color:#e5e7eb; }

.total-check {
    display:flex;justify-content:space-between;align-items:center;
    padding:12px 16px;border-radius:10px;margin-top:12px;
    background:#f9fafb;font-size:13px;font-weight:700;
}
body.dark .total-check { background:rgba(255,255,255,0.03); }
.total-check.ok   { background:rgba(16,185,129,0.08);color:#065f46; }
.total-check.warn { background:rgba(239,68,68,0.08);color:#991b1b; }
.total-check-label { color:#6b7280;font-weight:600; }

/* Proposal history */
.proposal-card {
    background:white;border-radius:var(--radius);
    box-shadow:var(--shadow);overflow:hidden;
}
body.dark .proposal-card { background:#191d27; }
.phdr {
    padding:18px 22px;border-bottom:1px solid #f3f4f6;
    display:flex;justify-content:space-between;align-items:center;
}
body.dark .phdr { border-color:rgba(255,255,255,0.06); }
.phdr-title { font-size:15px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:8px; }
body.dark .phdr-title { color:#e5e7eb; }

.proposal-item { padding:16px 22px;border-bottom:1px solid #f9fafb;transition:background .15s; }
body.dark .proposal-item { border-color:rgba(255,255,255,0.03); }
.proposal-item:last-child { border-bottom:none; }
.proposal-item:hover { background:#f9fafb; }
body.dark .proposal-item:hover { background:rgba(255,255,255,0.02); }

.pi-top { display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:10px; }
.pi-info {}
.pi-desc { font-size:14px;font-weight:700;color:#1f2937;margin-bottom:3px; }
body.dark .pi-desc { color:#e5e7eb; }
.pi-meta { font-size:12px;color:#9ca3af; }
.pi-status {
    padding:4px 11px;border-radius:20px;font-size:11px;font-weight:800;
    white-space:nowrap;flex-shrink:0;
}
.st-pending  { background:rgba(245,158,11,0.1);color:#b45309; }
.st-approved { background:rgba(16,185,129,0.1);color:#059669; }
.st-rejected { background:rgba(239,68,68,0.1);color:#dc2626; }
.st-cancelled{ background:rgba(107,114,128,0.1);color:#6b7280; }

.pi-splits { display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px; }
.pi-split-chip {
    display:flex;align-items:center;gap:6px;
    padding:5px 10px;border-radius:20px;background:#f3f4f6;
    font-size:12px;font-weight:600;color:#374151;
}
body.dark .pi-split-chip { background:rgba(255,255,255,0.06);color:#9ca3af; }
.pi-split-arrow { color:#10b981;font-weight:700; }
.pi-split-arrow.neg { color:#ef4444; }

.pi-progress {
    height:6px;background:#e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:6px;
}
body.dark .pi-progress { background:rgba(255,255,255,0.1); }
.pi-progress-fill { height:100%;border-radius:10px;background:linear-gradient(90deg,var(--primary),var(--primary-dark));transition:width .5s; }
.pi-progress-label { font-size:11px;color:#9ca3af; }

.pi-actions { display:flex;gap:8px;margin-top:10px; }

/* Buttons */
.btn-primary {
    display:inline-flex;align-items:center;gap:7px;
    padding:9px 18px;border-radius:var(--radius-sm);
    background:linear-gradient(135deg,var(--primary),var(--primary-dark));
    color:white;font-size:13px;font-weight:700;border:none;cursor:pointer;
    text-decoration:none;transition:opacity .2s;
}
.btn-primary:hover { opacity:.88; }
.btn-sm { padding:6px 12px;font-size:12px; }
.btn-success { background:linear-gradient(135deg,var(--success),#059669); }
.btn-danger  { background:linear-gradient(135deg,var(--danger),#dc2626); }
.btn-ghost {
    display:inline-flex;align-items:center;gap:6px;
    padding:6px 12px;border-radius:8px;
    background:#f3f4f6;border:2px solid #e5e7eb;
    color:#6b7280;font-size:12px;font-weight:600;cursor:pointer;transition:background .2s;
    text-decoration:none;
}
.btn-ghost:hover { background:#e5e7eb; }

@media (max-width:1100px) { .main-grid { grid-template-columns:1fr; } }
</style>

<div class="breadcrumb">
    <a href="{{ route('groups.index') }}">Nhóm</a>
    <span>/</span>
    <a href="{{ route('groups.show', $group) }}">{{ $group->ten_nhom }}</a>
    <span>/</span>
    <span>Phân phối số dư</span>
</div>

<div class="top-bar">
    <div class="top-bar-title">⚖️ Phân phối số dư · {{ $group->ten_nhom }}</div>
    <a href="{{ route('groups.show', $group) }}" class="btn-ghost">← Quay lại</a>
</div>

@if(session('success'))
<div class="alert alert-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">⚠ {{ session('error') }}</div>
@endif

@if(!$group->hien_so_du)
<div class="alert alert-warn">
    ⚠️ Số dư đang bị ẩn. Admin cần bật <strong>"Hiển thị số dư"</strong> trong trang nhóm để sử dụng chức năng này.
</div>
@endif

{{-- Balance overview --}}
@if($group->hien_so_du)
@php $tongSoDu = collect($members)->sum('so_du'); @endphp
<div class="balance-grid">
    @foreach($members as $i => $m)
    @php
        $colors = ['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
        $c = $colors[$i % count($colors)];
        $bal = $m['so_du'] ?? 0;
        $cls = $bal > 0 ? 'pos' : ($bal < 0 ? 'neg' : '');
        $prefix = $bal > 0 ? '+' : '';
    @endphp
    <div class="bal-card">
        @if($m['avatar'] ?? null)
            @if(str_starts_with($m['avatar'], 'http'))
                <img src="{{ $m['avatar'] }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;margin:0 auto 12px;display:block;" alt="">
            @else
                <img src="{{ asset('storage/' . $m['avatar']) }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;margin:0 auto 12px;display:block;" alt="">
            @endif
        @else
            <div class="bal-av" style="background:linear-gradient(135deg,{{ $c }},{{ $c }}cc)">
                {{ strtoupper(substr($m['name'], 0, 2)) }}
            </div>
        @endif
        <div class="bal-name">{{ $m['name'] }}</div>
        <div class="bal-amount {{ $cls }}">{{ $prefix }}{{ number_format($bal) }}đ</div>
    </div>
    @endforeach
</div>

<div class="total-row">
    <div>
        <div class="total-label">Tổng số dư toàn nhóm</div>
        <div style="font-size:12px;color:rgba(255,255,255,0.6)">Sẽ được phân phối lại</div>
    </div>
    <div class="total-value">{{ number_format($tongSoDu) }}đ</div>
</div>
@endif

<div class="main-grid">
    {{-- Pending + History: full width --}}
    <div class="main-left">
        {{-- Pending proposals (cần tôi duyệt) --}}
        @if($myPending->count() > 0)
        <div class="propose-card" style="margin-bottom:20px">
            <div class="propose-hdr" style="background:rgba(245,158,11,0.06)">
                <div class="propose-hdr-title">⏳ Chờ bạn xác nhận ({{ $myPending->count() }})</div>
            </div>
            @foreach($myPending as $p)
            <div class="proposal-item">
                <div class="pi-top">
                    <div class="pi-info">
                        <div class="pi-desc">{{ $p['mo_ta'] ?? 'Phân phối số dư' }}</div>
                        <div class="pi-meta">Đề xuất bởi {{ $p['proposed_by'] }} · {{ \Carbon\Carbon::parse($p['created_at'])->diffForHumans() }}</div>
                    </div>
                    <span class="pi-status st-pending">⏳ Chờ duyệt</span>
                </div>
                <div class="pi-splits">
                    @foreach($p['splits'] as $s)
                    @php $arrow = $s['chenh_lech'] > 0 ? 'pi-split-arrow' : 'pi-split-arrow neg'; @endphp
                    <div class="pi-split-chip">
                        {{ $s['name'] }}
                        <span class="{{ $arrow }}">
                            {{ $s['chenh_lech'] > 0 ? '↑' : '↓' }}{{ number_format(abs($s['chenh_lech'])) }}đ
                        </span>
                    </div>
                    @endforeach
                </div>
                <div class="pi-progress">
                    <div class="pi-progress-fill" style="width:{{ $p['total_members'] > 0 ? round($p['approved_count']/$p['total_members']*100) : 0 }}%"></div>
                </div>
                <div class="pi-progress-label">{{ $p['approved_count'] }}/{{ $p['total_members'] }} người đồng ý</div>
                <div class="pi-actions">
                    <form action="{{ route('groups.balance.approve', [$group, $p['id']]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary btn-success btn-sm">✓ Đồng ý</button>
                    </form>
                    <form action="{{ route('groups.balance.reject',  [$group, $p['id']]) }}" method="POST" style="display:inline">
                        @csrf
                        <button type="submit" class="btn-primary btn-danger btn-sm">✕ Từ chối</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- History --}}
        <div class="proposal-card">
            <div class="phdr">
                <div class="phdr-title">📋 Lịch sử đề xuất</div>
            </div>
            @forelse($proposals as $p)
            <div class="proposal-item">
                <div class="pi-top">
                    <div class="pi-info">
                        <div class="pi-desc">{{ $p['mo_ta'] ?? 'Phân phối số dư' }}</div>
                        <div class="pi-meta">
                            Bởi {{ $p['proposed_by'] }} ·
                            Tổng: {{ number_format($p['tong_so_du']) }}đ ·
                            {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}
                        </div>
                    </div>
                    <span class="pi-status st-{{ $p['trang_thai'] }}">
                        {{ match($p['trang_thai']) {
                            'pending'  => '⏳ Chờ duyệt',
                            'approved' => '✓ Đã thực hiện',
                            'rejected' => '✕ Từ chối',
                            default    => '— Đã hủy',
                        } }}
                    </span>
                </div>

                @if($p['trang_thai'] === 'pending')
                <div class="pi-progress"><div class="pi-progress-fill" style="width:{{ $p['total_members'] > 0 ? round($p['approved_count']/$p['total_members']*100) : 0 }}%"></div></div>
                <div class="pi-progress-label">{{ $p['approved_count'] }}/{{ $p['total_members'] }} người đồng ý</div>

                @if($p['my_approval'] === null)
                <div class="pi-actions">
                    <form action="{{ route('groups.balance.approve', [$group, $p['id']]) }}" method="POST">
                        @csrf <button type="submit" class="btn-primary btn-success btn-sm">✓ Đồng ý</button>
                    </form>
                    <form action="{{ route('groups.balance.reject',  [$group, $p['id']]) }}" method="POST" style="display:inline">
                        @csrf <button type="submit" class="btn-primary btn-danger btn-sm">✕ Từ chối</button>
                    </form>
                    @if($laAdmin ?? false)
                    <form action="{{ route('groups.balance.cancel',  [$group, $p['id']]) }}" method="POST" style="display:inline">
                        @csrf <button type="submit" class="btn-ghost btn-sm">Hủy</button>
                    </form>
                    @endif
                </div>
                @else
                <div style="font-size:12px;color:#9ca3af;margin-top:8px">
                    Bạn đã {{ $p['my_approval'] === 'approved' ? '✓ đồng ý' : '✕ từ chối' }}
                </div>
                @endif
                @endif

                @if($p['trang_thai'] === 'approved')
                <div style="font-size:12px;color:var(--success);margin-top:6px">
                    ✓ Thực hiện lúc {{ \Carbon\Carbon::parse($p['executed_at'])->format('d/m/Y H:i') }}
                </div>
                @endif
            </div>
            @empty
            <div style="text-align:center;padding:50px 20px;color:#9ca3af">
                <div style="font-size:40px;margin-bottom:12px">📋</div>
                <div style="font-weight:600">Chưa có đề xuất nào</div>
                @if($group->hien_so_du)
                <div style="font-size:13px;margin-top:4px">Tạo đề xuất đầu tiên để phân phối số dư</div>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    {{-- Form tạo đề xuất: full width, chỉ hiện cho admin --}}
    @if($laAdmin ?? false)
    <div class="main-right">
        <div class="propose-card">
            <div class="propose-hdr" style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div class="propose-hdr-title">⚖️ Tạo đề xuất phân phối mới</div>
                    <div style="font-size:12px;color:#9ca3af;margin-top:4px">Toàn bộ thành viên phải đồng ý thì mới thực hiện</div>
                </div>
            </div>
            <div class="propose-body">
                @if(!$group->hien_so_du)
                <div class="alert alert-warn" style="margin-bottom:0">
                    ⚠️ Bật hiển thị số dư để tạo đề xuất phân phối
                </div>
                @else
                <form action="{{ route('groups.balance.propose', $group) }}" method="POST">
                    @csrf
                    {{-- Layout 2 cột: mô tả | bảng phân bổ --}}
                    <div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;align-items:start;">
                        {{-- Cột trái: mô tả + tổng --}}
                        <div>
                            <div style="margin-bottom:16px">
                                <label class="form-label">Mô tả</label>
                                <input name="mo_ta" class="form-ctrl" placeholder="VD: Phân phối tháng 3/2026..." maxlength="255">
                            </div>
                            <div class="total-check" id="totalCheck" style="margin-bottom:16px">
                                <span class="total-check-label">Tổng phân bổ</span>
                                <span id="totalAllocated">0đ / {{ number_format($tongSoDu ?? 0) }}đ</span>
                            </div>
                            <div style="background:rgba(74,144,226,0.06);border-radius:10px;padding:12px;font-size:12px;color:#4b5563;margin-bottom:16px;">
                                💡 Tổng số dư mới phải bằng đúng tổng số dư hiện tại (<strong>{{ number_format($tongSoDu ?? 0) }}đ</strong>)
                            </div>
                            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px">
                                ⚖️ Gửi đề xuất
                            </button>
                        </div>
                        {{-- Cột phải: bảng phân bổ từng người --}}
                        <div>
                            <label class="form-label">Phân bổ số dư mới cho từng người <span style="color:var(--danger)">*</span></label>
                            <table class="alloc-table">
                                <thead>
                                    <tr>
                                        <th>Thành viên</th>
                                        <th>Hiện tại</th>
                                        <th>Số dư mới (đ)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($members as $i => $m)
                                    @php
                                        $colors=['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
                                        $c=$colors[$i%count($colors)];
                                        $curBal = $m['so_du'] ?? 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="hidden" name="phan_bo[{{ $i }}][user_id]" value="{{ $m['user_id'] }}">
                                            <div class="alloc-user">
                                                @if($m['avatar'] ?? null)
                                                    @if(str_starts_with($m['avatar'], 'http'))
                                                        <img src="{{ $m['avatar'] }}" class="alloc-av" style="object-fit:cover;" alt="">
                                                    @else
                                                        <img src="{{ asset('storage/' . $m['avatar']) }}" class="alloc-av" style="object-fit:cover;" alt="">
                                                    @endif
                                                @else
                                                    <div class="alloc-av" style="background:linear-gradient(135deg,{{ $c }},{{ $c }}cc)">
                                                        {{ strtoupper(substr($m['name'], 0, 2)) }}
                                                    </div>
                                                @endif
                                                <div class="alloc-uname">{{ $m['name'] }}</div>
                                            </div>
                                        </td>
                                        <td style="font-size:13px;color:#6b7280;white-space:nowrap;">
                                            {{ number_format($curBal) }}đ
                                        </td>
                                        <td>
                                            <input type="number" name="phan_bo[{{ $i }}][so_du_moi]"
                                                class="alloc-input" min="0" step="1000"
                                                value="{{ old("phan_bo.{$i}.so_du_moi", max(0,$curBal)) }}"
                                                oninput="recalcTotal()" required>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<script>
const targetTotal = {{ $tongSoDu ?? 0 }};

function recalcTotal() {
    const inputs = document.querySelectorAll('.alloc-input');
    let sum = 0;
    inputs.forEach(i => sum += parseFloat(i.value)||0);
    const el = document.getElementById('totalAllocated');
    const chk = document.getElementById('totalCheck');
    if (el) {
        el.textContent = sum.toLocaleString('vi-VN') + 'đ / ' + targetTotal.toLocaleString('vi-VN') + 'đ';
        const diff = Math.abs(sum - targetTotal);
        chk.className = 'total-check ' + (diff <= 1 ? 'ok' : 'warn');
    }
}
recalcTotal();

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition='opacity .3s'; a.style.opacity='0';
        setTimeout(()=>a.remove(),300);
    });
}, 4500);
</script>
@endsection
