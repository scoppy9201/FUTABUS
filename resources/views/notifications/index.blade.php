@extends('layouts.app')
@section('title', 'Thông báo')
@section('content')
<style>
:root { --primary:#4a90e2;--primary-dark:#2a5298;--success:#10b981;--danger:#ef4444;--warning:#f59e0b;--radius:16px;--shadow:0 2px 8px rgba(0,0,0,0.05); }

.page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:22px; }
.page-title  { font-size:22px;font-weight:900;color:#1f2937;display:flex;align-items:center;gap:10px; }
body.dark .page-title { color:#e5e7eb; }

.notif-toolbar {
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
    background:white;border-radius:var(--radius);padding:14px 20px;
    margin-bottom:16px;box-shadow:var(--shadow);
}
body.dark .notif-toolbar { background:#191d27; }

.filter-btn {
    padding:7px 16px;border-radius:20px;font-size:13px;font-weight:700;
    border:2px solid #e5e7eb;background:white;color:#6b7280;cursor:pointer;
    transition:all .15s;
}
.filter-btn:hover { border-color:#4a90e2;color:#4a90e2; }
.filter-btn.active { background:#4a90e2;border-color:#4a90e2;color:white; }
body.dark .filter-btn { background:#191d27;border-color:rgba(255,255,255,0.1);color:#9ca3af; }
body.dark .filter-btn.active { background:#4a90e2;border-color:#4a90e2;color:white; }

.mark-all-btn {
    margin-left:auto;padding:8px 18px;border-radius:10px;
    background:rgba(74,144,226,0.08);border:none;color:#4a90e2;
    font-size:13px;font-weight:700;cursor:pointer;transition:background .15s;
}
.mark-all-btn:hover { background:rgba(74,144,226,0.15); }

/* Notification cards */
.notif-section { margin-bottom:8px; }
.date-label {
    font-size:12px;font-weight:700;color:#9ca3af;
    text-transform:uppercase;letter-spacing:0.7px;
    padding:0 4px;margin:18px 0 8px;
}

.notif-card {
    background:white;border-radius:14px;
    box-shadow:var(--shadow);overflow:hidden;
    border-left:4px solid transparent;
    margin-bottom:4px;
    display:flex;align-items:flex-start;gap:14px;
    padding:14px 18px;
    text-decoration:none;color:inherit;
    transition:transform .15s, box-shadow .15s;
    cursor:pointer;
}
.notif-card:hover { transform:translateX(3px);box-shadow:0 4px 16px rgba(0,0,0,0.08); }
body.dark .notif-card { background:#191d27; }
.notif-card.unread { border-left-color:#4a90e2;background:rgba(74,144,226,0.03); }
body.dark .notif-card.unread { background:rgba(74,144,226,0.07); }

.nc-av {
    width:46px;height:46px;border-radius:50%;flex-shrink:0;
    position:relative;display:flex;align-items:center;justify-content:center;
    font-size:16px;font-weight:800;color:white;
}
.nc-av img { width:100%;height:100%;border-radius:50%;object-fit:cover; }
.nc-av-icon {
    position:absolute;bottom:-2px;right:-2px;
    width:20px;height:20px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:11px;border:2px solid white;
}
body.dark .nc-av-icon { border-color:#191d27; }

.nc-body { flex:1;min-width:0; }
.nc-text { font-size:14px;color:#374151;line-height:1.5; }
body.dark .nc-text { color:#9ca3af; }
.nc-text strong { color:#1f2937;font-weight:700; }
body.dark .nc-text strong { color:#e5e7eb; }
.nc-time { font-size:12px;color:#9ca3af;margin-top:4px;font-weight:600; }
.nc-time.fresh { color:#4a90e2; }

.nc-unread-dot {
    width:10px;height:10px;border-radius:50%;
    background:#4a90e2;flex-shrink:0;margin-top:6px;
}

/* Empty state */
.empty-state {
    text-align:center;padding:80px 20px;
    background:white;border-radius:var(--radius);box-shadow:var(--shadow);
}
body.dark .empty-state { background:#191d27; }
.empty-icon { font-size:56px;margin-bottom:16px; }
.empty-title { font-size:18px;font-weight:800;color:#1f2937;margin-bottom:8px; }
body.dark .empty-title { color:#e5e7eb; }
.empty-sub { font-size:14px;color:#9ca3af; }
</style>

<div class="page-header">
    <div class="page-title">🔔 Thông báo</div>
</div>

<div class="notif-toolbar">
    <button class="filter-btn active" onclick="filterNotifs('all', this)">Tất cả</button>
    <button class="filter-btn" onclick="filterNotifs('unread', this)">Chưa đọc</button>
    <button class="filter-btn" onclick="filterNotifs('group', this)">Nhóm</button>
    <button class="filter-btn" onclick="filterNotifs('transaction', this)">Giao dịch</button>
    <button class="filter-btn" onclick="filterNotifs('wallet', this)">Ngân sách</button>

    <form action="{{ route('notifications.mark-all-read') }}" method="POST" style="margin-left:auto">
        @csrf
        <button type="submit" class="mark-all-btn">✓ Đánh dấu tất cả đã đọc</button>
    </form>
</div>

@php
    use Carbon\Carbon;
    $today     = Carbon::today()->toDateString();
    $yesterday = Carbon::yesterday()->toDateString();
    $grouped   = $notifications->getCollection()->groupBy(fn($n) => $n->created_at->toDateString());
    $colors    = ['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
@endphp

@if($notifications->isEmpty())
<div class="empty-state">
    <div class="empty-icon">🔔</div>
    <div class="empty-title">Chưa có thông báo nào</div>
    <div class="empty-sub">Thông báo sẽ xuất hiện khi có hoạt động trong hệ thống</div>
</div>
@else

@foreach($grouped as $date => $notifs)
<div class="notif-section" data-date="{{ $date }}">
    <div class="date-label">
        @if($date === $today) 📅 Hôm nay
        @elseif($date === $yesterday) 📅 Hôm qua
        @else 📅 {{ Carbon::parse($date)->format('d/m/Y') }}
        @endif
    </div>

    @foreach($notifs as $i => $n)
    @php
        $avBg   = $n->actor && !$n->actor->avatar ? $colors[$n->id % count($colors)] : '#f3f4f6';
        $isFresh = $n->created_at->diffInHours(now()) < 1;
    @endphp
    <a href="{{ $n->url ?? '#' }}"
       class="notif-card {{ $n->da_doc ? '' : 'unread' }}"
       data-type="{{ Str::before($n->loai, '_') }}"
       onclick="markRead(event, {{ $n->id }}, '{{ $n->url ?? '' }}')">

        {{-- Avatar --}}
        <div class="nc-av" style="background:{{ $avBg }}">
            @if($n->actor?->avatar)
                @if(str_starts_with($n->actor->avatar, 'http'))
                    <img src="{{ $n->actor->avatar }}" alt="">
                @else
                    <img src="{{ asset('storage/' . $n->actor->avatar) }}" alt="">
                @endif
            @elseif($n->actor)
                {{ strtoupper(substr($n->actor->name, 0, 2)) }}
            @else
                <span style="font-size:22px;">🔔</span>
            @endif
            <div class="nc-av-icon" style="background:{{ $n->color }}">{{ $n->icon }}</div>
        </div>

        {{-- Content --}}
        <div class="nc-body">
            <div class="nc-text">
                <strong>{{ $n->tieu_de }}</strong> {{ $n->noi_dung }}
            </div>
            <div class="nc-time {{ $isFresh ? 'fresh' : '' }}">
                {{ $n->time_ago }}
                @if($isFresh) · Mới @endif
            </div>
        </div>

        {{-- Unread dot --}}
        @if(!$n->da_doc)
        <div class="nc-unread-dot"></div>
        @endif
    </a>
    @endforeach
</div>
@endforeach

{{-- Pagination --}}
<div style="margin-top:20px;text-align:center;">
    {{ $notifications->links() }}
</div>
@endif

<script>
function filterNotifs(type, el) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');

    document.querySelectorAll('.notif-card').forEach(card => {
        if (type === 'all') {
            card.style.display = '';
        } else if (type === 'unread') {
            card.style.display = card.classList.contains('unread') ? '' : 'none';
        } else {
            card.style.display = (card.dataset.type === type) ? '' : 'none';
        }
    });

    // Ẩn date headers nếu không còn item nào visible
    document.querySelectorAll('.notif-section').forEach(section => {
        const visible = [...section.querySelectorAll('.notif-card')].some(c => c.style.display !== 'none');
        section.style.display = visible ? '' : 'none';
    });
}

async function markRead(e, id, url) {
    e.preventDefault();
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        await fetch(`{{ url('/notifications/mark-read') }}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });
    } catch(_) {}
    if (url && url !== '#') window.location.href = url;
    else window.location.reload();
}
</script>
@endsection
