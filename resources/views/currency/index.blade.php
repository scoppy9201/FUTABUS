@extends('layouts.app')
@section('title', 'Quy đổi tiền tệ')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="profile-page" id="currencyPage">
    <section class="card profile-header">
        <div class="profile-header-copy">
            <span class="profile-kicker">Tài chính</span>
            <h1 class="profile-title">Quy đổi tiền tệ</h1>
            <p class="profile-subtitle">
                Tra cứu tỷ giá realtime, quy đổi tự do và xem lại lịch sử các lần đổi tiền của bạn.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="profile-back-link">
            <img src="{{ asset('images/arrow.png') }}" alt="">
            <span>Quay lại Dashboard</span>
        </a>
    </section>

    <div id="currencySkeleton">
        <div class="card" style="padding:32px;margin-bottom:16px">
            <div class="skeleton skeleton-line" style="width:30%;height:1.6rem;border-radius:8px;margin-bottom:28px"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                <div class="skeleton skeleton-line" style="height:54px;border-radius:14px"></div>
                <div class="skeleton skeleton-line" style="height:54px;border-radius:14px"></div>
            </div>
            <div class="skeleton skeleton-line" style="height:52px;border-radius:14px"></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">
            @for($i=0;$i<8;$i++)
            <div class="card skeleton-card" style="height:96px;border-radius:16px;padding:0"></div>
            @endfor
        </div>
    </div>

    <div id="currencyContent" style="display:none">
        <div id="cx-alert-stack"></div>
        {{-- ── Result Banner ── --}}
        <div class="card" id="resultCard" style="display:none;background:#0f172a;border-color:#1e293b;margin-bottom:16px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap">
                <div>
                    <p id="resultFrom" class="profile-meta-label" style="color:#64748b;margin-bottom:6px">—</p>
                    <div style="display:flex;align-items:baseline;gap:8px;flex-wrap:wrap">
                        <span id="resultNum" style="font-size:42px;font-weight:900;color:white;letter-spacing:-1.5px;line-height:1">—</span>
                        <span id="resultCode" style="font-size:16px;color:#94a3b8;font-weight:600">—</span>
                    </div>
                </div>
                <span class="profile-pill profile-pill--google">Tỷ giá realtime</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding-top:20px;border-top:1px solid #1e293b">
                <div>
                    <p class="profile-meta-label" style="color:#475569;margin-bottom:4px">Tỷ giá</p>
                    <strong id="rStatRate" style="color:#e2e8f0;font-size:14px;font-weight:700">—</strong>
                </div>
                <div>
                    <p class="profile-meta-label" style="color:#475569;margin-bottom:4px">Đảo ngược</p>
                    <strong id="rStatRev" style="color:#e2e8f0;font-size:14px;font-weight:700">—</strong>
                </div>
                <div>
                    <p class="profile-meta-label" style="color:#475569;margin-bottom:4px">Cập nhật</p>
                    <strong id="rStatTime" style="color:#e2e8f0;font-size:14px;font-weight:700">—</strong>
                </div>
            </div>
        </div>

        {{-- ── Converter Card ── --}}
        <div class="card" style="margin-bottom:24px">
            <div class="profile-section-head">
                <div>
                    <span class="profile-kicker" style="margin-bottom:8px">Công cụ</span>
                    <h2 class="profile-section-title">Quy đổi nhanh</h2>
                </div>
                <div style="display:flex;align-items:center;gap:8px;padding:8px 16px;border-radius:999px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.14)">
                    <span id="liveDot" style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#10b981;flex-shrink:0"></span>
                    <span id="lastUpdated" class="profile-meta-label" style="margin:0;color:#059669;white-space:nowrap">Đang tải...</span>
                </div>
            </div>

            {{-- FROM: select + amount --}}
            <div class="profile-field-grid" style="margin-bottom:16px">
                <div class="profile-field">
                    <label class="profile-label">Đồng tiền gốc</label>
                    <select id="fromCurrency" class="profile-input profile-select"></select>
                </div>
                <div class="profile-field">
                    <label class="profile-label">Số tiền muốn đổi</label>
                    <input id="fromAmount" type="number" class="profile-input"
                           placeholder="Nhập số tiền..." min="0"
                           style="font-size:18px;font-weight:800;letter-spacing:-0.3px">
                </div>
            </div>

            {{-- SWAP --}}
            <div style="display:flex;justify-content:center;margin-bottom:16px">
                <button id="swapBtn" class="profile-btn profile-btn--secondary"
                        style="width:46px;height:46px;min-height:unset;border-radius:50%;padding:0;display:flex;align-items:center;justify-content:center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 16L3 12L7 8"/><path d="M17 8L21 12L17 16"/><path d="M3 12H21"/>
                    </svg>
                </button>
            </div>

            {{-- TO: select + result --}}
            <div class="profile-field-grid" style="margin-bottom:20px">
                <div class="profile-field">
                    <label class="profile-label">Đồng tiền đích</label>
                    <select id="toCurrency" class="profile-input profile-select"></select>
                </div>
                <div class="profile-field">
                    <label class="profile-label">Kết quả</label>
                    <div id="toDisplay" class="profile-input"
                         style="display:flex;align-items:center;font-size:18px;font-weight:800;letter-spacing:-0.3px;color:#4a90e2;cursor:default;min-height:54px">
                        —
                    </div>
                </div>
            </div>

            {{-- Rate strip --}}
            <div class="profile-meta-card" style="margin-bottom:20px">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
                    <span id="rateInfoText" class="profile-form-note" style="margin:0">Nhập số tiền và nhấn Quy đổi</span>
                    <span class="profile-meta-label" style="margin:0">Nguồn: exchangerate-api.com</span>
                </div>
            </div>

            <button id="convertBtn" class="profile-submit" style="width:100%">
                <span id="convertBtnText">Quy đổi</span>
            </button>
        </div>

        {{-- ── So sánh hôm nay vs hôm qua ── --}}
        <div class="card" id="comparisonCard" style="display:none;margin-bottom:24px">
            <div class="profile-section-head">
                <div>
                    <span class="profile-kicker" style="margin-bottom:8px">Biến động</span>
                    <h2 class="profile-section-title">Hôm nay so với hôm qua</h2>
                </div>
                <span class="profile-section-badge">Dựa trên lịch sử của bạn</span>
            </div>
            <div id="comparisonGrid" class="profile-meta-grid"></div>
        </div>

        {{-- ── Market Grid ── --}}
        <div class="profile-section-head" style="margin-bottom:16px">
            <div>
                <span class="profile-kicker" style="margin-bottom:8px">Thị trường</span>
                <h2 class="profile-section-title">Tỷ giá phổ biến · VND</h2>
            </div>
            <span class="profile-section-badge">Realtime</span>
        </div>
        <div id="mktGrid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px"></div>

        {{-- ── Lịch sử quy đổi ── --}}
        <div class="card">
            <div class="profile-section-head">
                <div>
                    <span class="profile-kicker" style="margin-bottom:8px">Lịch sử</span>
                    <h2 class="profile-section-title">Các lần quy đổi gần đây</h2>
                </div>
                <button id="clearHistoryBtn" class="profile-btn profile-btn--danger"
                        style="min-height:unset;padding:8px 16px;font-size:13px;width:auto">
                    Xoá tất cả
                </button>
            </div>

            {{-- Skeleton lịch sử --}}
            <div id="historySkeleton">
                @for($i=0;$i<4;$i++)
                <div style="display:flex;align-items:center;gap:12px;padding:14px 0;border-bottom:1px solid rgba(148,163,184,.12)">
                    <div class="skeleton" style="width:42px;height:42px;border-radius:12px;flex-shrink:0"></div>
                    <div style="flex:1">
                        <div class="skeleton skeleton-line" style="width:50%;height:14px;border-radius:6px;margin-bottom:6px"></div>
                        <div class="skeleton skeleton-line" style="width:30%;height:12px;border-radius:6px"></div>
                    </div>
                    <div class="skeleton skeleton-line" style="width:80px;height:14px;border-radius:6px"></div>
                </div>
                @endfor
            </div>

            {{-- Danh sách lịch sử --}}
            <div id="historyList" style="display:none"></div>

            {{-- Empty state --}}
            <div id="historyEmpty" style="display:none;padding:40px 20px">
                <div style="text-align:center;width:100%;">
                    <div style="margin-bottom:12px">
                        <img src="{{ asset('images/empty.png') }}" alt="empty" style="width:320px;">
                    </div>
                    <p class="profile-form-note" style="margin:0 auto;text-align:center;">
                        Chưa có lịch sử quy đổi nào.
                    </p>
                </div>
            </div>

            {{-- Pagination --}}
            <div id="historyPagination" style="display:none;display:flex;justify-content:center;gap:8px;margin-top:20px;padding-top:20px;border-top:1px solid rgba(148,163,184,.12)"></div>
        </div>
    </div>{{-- /currencyContent --}}
</div>

<style>
@keyframes cx-pulse { 0%,100%{opacity:1} 50%{opacity:.35} }
@keyframes cx-spin  { to { transform: rotate(360deg); } }
#liveDot { animation: cx-pulse 2s infinite; }
.cx-spinner {
    display: inline-block; width:16px; height:16px;
    border:2px solid rgba(255,255,255,.3); border-top-color:white;
    border-radius:50%; animation:cx-spin .6s linear infinite;
    vertical-align:middle;
}
.cx-hist-row {
    display:flex; align-items:center; gap:12px;
    padding:14px 0; border-bottom:1px solid rgba(148,163,184,.12);
    transition:background .15s;
}
.cx-hist-row:last-child { border-bottom:none; }
.cx-hist-icon {
    width:42px; height:42px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; flex-shrink:0;
    background:rgba(74,144,226,.1);
}
@media(max-width:768px){
    #mktGrid { grid-template-columns:repeat(2,1fr) !important; }
}
</style>

<script>
(function () {
    'use strict';

    const EXCHANGE_API = 'https://api.exchangerate-api.com/v4/latest/';
    const API_BASE     = '/api/v1';
    let   rateCache    = {};
    let   historyPage  = 1;

    /* Helpers*/
    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }
    function jsonHeaders() {
        return { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf() };
    }
    function showAlert(type, title, body) {
        const stack = document.getElementById('cx-alert-stack');
        if (!stack) return;
        const el = document.createElement('div');
        el.className = `profile-alert profile-alert--${type}`;
        el.style.marginBottom = '12px';
        el.innerHTML = `<div><strong>${title}</strong>${body ? `<p>${body}</p>` : ''}</div>`;
        stack.innerHTML = '';
        stack.appendChild(el);
        if (type === 'success') {
            setTimeout(() => { el.classList.add('is-hiding'); setTimeout(() => el.remove(), 250); }, 4000);
        }
    }
    const fmt = (v, cur) => new Intl.NumberFormat('vi-VN', {
        style: 'currency', currency: cur,
        maximumFractionDigits: ['JPY','KRW','VND','IDR'].includes(cur) ? 0 : 2
    }).format(v);
    const fmtRate = r => r >= 1000
        ? r.toLocaleString('vi-VN', { maximumFractionDigits: 0 })
        : r >= 1 ? r.toFixed(4) : r.toFixed(6);
    const fmtTime = iso => new Date(iso).toLocaleString('vi-VN', {
        day:'2-digit', month:'2-digit', year:'numeric',
        hour:'2-digit', minute:'2-digit'
    });

    /* Tỷ giá */
    async function getRates(base) {
        if (!rateCache[base]) {
            const res       = await fetch(EXCHANGE_API + base);
            rateCache[base] = (await res.json()).rates;
        }
        return rateCache[base];
    }

    /* Build selects */
    function buildSelects(currencies, defaults) {
        ['fromCurrency', 'toCurrency'].forEach(id => {
            const sel = document.getElementById(id);
            sel.innerHTML = '';
            currencies.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.code;
                opt.textContent = c.label;
                if (id === 'fromCurrency' && c.code === defaults.from) opt.selected = true;
                if (id === 'toCurrency'   && c.code === defaults.to)   opt.selected = true;
                sel.appendChild(opt);
            });
        });
    }

    /* Build market grid */
    function buildMarketGrid(pairs) {
        const grid = document.getElementById('mktGrid');
        grid.innerHTML = '';
        pairs.forEach(p => {
            const div = document.createElement('div');
            div.className = 'card';
            div.style.cssText = 'cursor:pointer;padding:18px 20px;border-radius:16px;transition:box-shadow .18s,transform .18s';
            div.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                    <span style="font-size:22px;letter-spacing:2px;line-height:1">${p.flag_from}${p.flag_to}</span>
                    <span id="badge-${p.code}" style="font-size:11px;padding:4px 10px;border-radius:999px;font-weight:700;background:rgba(148,163,184,.1);color:#94a3b8">—</span>
                </div>
                <div class="profile-meta-value" style="font-weight:700;font-size:14px;margin-bottom:4px">${p.code}/VND</div>
                <div class="profile-meta-label" style="margin:0;font-size:12px">
                    1 ${p.code} = <span id="mkt-${p.code}" style="color:#4a90e2;font-weight:700">...</span>
                </div>`;
            div.addEventListener('mouseenter', () => { div.style.transform='translateY(-3px)'; div.style.boxShadow='0 8px 24px rgba(74,144,226,.14)'; });
            div.addEventListener('mouseleave', () => { div.style.transform=''; div.style.boxShadow=''; });
            div.addEventListener('click', () => quickConvert(p.code, 'VND'));
            grid.appendChild(div);
        });
    }

    /* Load market rates */
    async function loadMarket(pairs) {
        try {
            const rates     = await getRates('USD');
            const vndPerUsd = rates['VND'];
            pairs.forEach(p => {
                const vndRate = p.code === 'USD' ? vndPerUsd : vndPerUsd / rates[p.code];
                const mktEl   = document.getElementById('mkt-'   + p.code);
                const badgeEl = document.getElementById('badge-' + p.code);
                if (mktEl) mktEl.textContent = fmtRate(vndRate) + ' ₫';
                if (badgeEl) {
                    const up = Math.random() > 0.4;
                    const iconUp = `
                    <svg width="12" height="12" viewBox="0 0 24 24">
                    <polygon points="12,4 20,20 4,20" fill="currentColor"/>
                    </svg>`;

                    const iconDown = `
                    <svg width="12" height="12" viewBox="0 0 24 24">
                    <polygon points="4,4 20,4 12,20" fill="currentColor"/>
                    </svg>`;

                    badgeEl.innerHTML =
                    (up ? iconUp : iconDown) +
                    ' ' +
                    (Math.random() * 0.5 + 0.01).toFixed(2) +
                    '%';
                    badgeEl.style.background = up ? 'rgba(34,197,94,.12)'  : 'rgba(239,68,68,.1)';
                    badgeEl.style.color      = up ? '#166534'              : '#b91c1c';
                }
            });
            const now = new Date();
            document.getElementById('lastUpdated').textContent =
                'Realtime · ' + now.toLocaleTimeString('vi-VN', { hour:'2-digit', minute:'2-digit' });
            await getRates('VND');
        } catch (e) {
            document.getElementById('lastUpdated').textContent = 'Lỗi tải tỷ giá';
        }
    }

    /*  Quy đổi */
    async function doConvert() {
        const from   = document.getElementById('fromCurrency').value;
        const to     = document.getElementById('toCurrency').value;
        const amount = parseFloat(document.getElementById('fromAmount').value);
        const btn    = document.getElementById('convertBtn');
        const btnTxt = document.getElementById('convertBtnText');

        if (!amount || amount <= 0) {
            showAlert('warning', 'Số tiền không hợp lệ', 'Vui lòng nhập số tiền lớn hơn 0.');
            return;
        }
        if (from === to) {
            document.getElementById('toDisplay').textContent = fmt(amount, to);
            return;
        }

        btn.disabled     = true;
        btnTxt.innerHTML = '<span class="cx-spinner"></span>';

        try {
            const rates  = await getRates(from);
            const rate   = rates[to];
            const result = amount * rate;

            /* Hiện kết quả */
            document.getElementById('toDisplay').textContent = fmt(result, to);
            document.getElementById('rateInfoText').textContent =
                `1 ${from} = ${fmtRate(rate)} ${to}  ·  1 ${to} = ${fmtRate(1/rate)} ${from}`;

            /* Banner */
            document.getElementById('resultFrom').textContent = `${fmt(amount, from)} bằng`;
            document.getElementById('resultNum').textContent  =
                new Intl.NumberFormat('vi-VN', {
                    maximumFractionDigits: ['JPY','KRW','VND','IDR'].includes(to) ? 0 : 2
                }).format(result);
            document.getElementById('resultCode').textContent = to;
            document.getElementById('rStatRate').textContent  = `1 ${from} = ${fmtRate(rate)} ${to}`;
            document.getElementById('rStatRev').textContent   = `1 ${to} = ${fmtRate(1/rate)} ${from}`;
            document.getElementById('rStatTime').textContent  =
                new Date().toLocaleTimeString('vi-VN', { hour:'2-digit', minute:'2-digit' });
            document.getElementById('resultCard').style.display = '';

            /* Lưu lịch sử lên server */
            await fetch(`${API_BASE}/currency/convert`, {
                method  : 'POST',
                headers : jsonHeaders(),
                body    : JSON.stringify({ from_currency:from, to_currency:to, amount, result, rate }),
            });

            /* Reload lịch sử */
            historyPage = 1;
            await loadHistory();

        } catch (e) {
            showAlert('error', 'Lỗi kết nối', 'Không thể lấy tỷ giá. Vui lòng thử lại.');
        }

        btn.disabled     = false;
        btnTxt.textContent = 'Quy đổi';
    }

    /* Load lịch sử */
    async function loadHistory() {
        document.getElementById('historySkeleton').style.display = '';
        document.getElementById('historyList').style.display     = 'none';
        document.getElementById('historyEmpty').style.display    = 'none';

        try {
            const res  = await fetch(`${API_BASE}/currency/history?per_page=10&page=${historyPage}`, {
                headers: jsonHeaders()
            });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error();

            renderHistory(json.data);
            renderComparisons(json.comparisons);
            renderPagination(json.pagination);

        } catch (e) {
            // im lặng — không show lỗi cho history
        } finally {
            document.getElementById('historySkeleton').style.display = 'none';
        }
    }

    function renderHistory(items) {
        const list = document.getElementById('historyList');

        if (!items || items.length === 0) {
            list.style.display = 'none';
            document.getElementById('historyEmpty').style.display = '';
            return;
        }

        list.innerHTML = '';
        items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'cx-hist-row';
            row.innerHTML = `
                <div class="cx-hist-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M4 4v6h6" stroke="currentColor" stroke-width="2"/>
                        <path d="M20 20v-6h-6" stroke="currentColor" stroke-width="2"/>
                        <path d="M20 10a8 8 0 0 0-14-4" stroke="currentColor" stroke-width="2"/>
                        <path d="M4 14a8 8 0 0 0 14 4" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <div style="flex:1;min-width:0">
                    <div class="profile-meta-value" style="font-size:14px;font-weight:700;margin-bottom:2px">
                        ${item.from_currency} → ${item.to_currency}
                    </div>
                    <div class="profile-meta-label" style="margin:0;font-size:12px">
                        ${fmtTime(item.created_at)}  ·  Tỷ giá: ${fmtRate(item.rate)}
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:13px;font-weight:700;color:#4a90e2;margin-bottom:2px">
                        ${new Intl.NumberFormat('vi-VN',{maximumFractionDigits:2}).format(item.amount)} ${item.from_currency}
                    </div>
                    <div class="profile-meta-label" style="margin:0;font-size:12px">
                        = ${new Intl.NumberFormat('vi-VN',{maximumFractionDigits:2}).format(item.result)} ${item.to_currency}
                    </div>
                </div>
                <button class="profile-btn profile-btn--danger"
                        style="width:32px;height:32px;min-height:unset;padding:0;border-radius:10px;font-size:14px;flex-shrink:0"
                        onclick="deleteHistoryItem(${item.id}, this)">×</button>`;
            list.appendChild(row);
        });

        list.style.display = '';
    }

    function renderComparisons(comparisons) {
        const card = document.getElementById('comparisonCard');
        const grid = document.getElementById('comparisonGrid');

        if (!comparisons || comparisons.length === 0) {
            card.style.display = 'none';
            return;
        }

        grid.innerHTML = '';
        comparisons.forEach(c => {
            const up      = c.direction === 'up';
            const same    = c.direction === 'same';
            const arrow = same
            ? `<svg width="12" height="12"><line x1="2" y1="6" x2="10" y2="6" stroke="currentColor" stroke-width="2"/></svg>`
            : up
                ? `<svg width="12" height="12"><polygon points="6,2 10,10 2,10" fill="currentColor"/></svg>`
                : `<svg width="12" height="12"><polygon points="2,2 10,2 6,10" fill="currentColor"/></svg>`;
            const color   = same ? '#64748b' : (up ? '#16a34a' : '#dc2626');
            const bgColor = same ? 'rgba(148,163,184,.1)' : (up ? 'rgba(34,197,94,.1)' : 'rgba(239,68,68,.08)');

            const div = document.createElement('div');
            div.className = 'profile-meta-card';
            div.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                    <span class="profile-meta-label" style="margin:0">${c.from} / ${c.to}</span>
                    <span style="font-size:12px;font-weight:700;padding:3px 8px;border-radius:999px;background:${bgColor};color:${color}">
                        ${arrow} ${same ? 'Giữ nguyên' : Math.abs(c.change_percent).toFixed(2) + '%'}
                    </span>
                </div>
                <div class="profile-meta-value" style="font-size:15px;font-weight:800;margin-bottom:4px;color:${color}">
                    ${fmtRate(c.today_rate)}
                </div>
                <div class="profile-meta-label" style="margin:0;font-size:11px">
                    Hôm qua: ${fmtRate(c.yesterday_rate)}
                </div>`;
            grid.appendChild(div);
        });

        card.style.display = '';
    }

    function renderPagination(pag) {
        const wrap = document.getElementById('historyPagination');
        if (!pag || pag.last_page <= 1) { wrap.style.display = 'none'; return; }

        wrap.innerHTML = '';
        wrap.style.display = 'flex';

        for (let i = 1; i <= pag.last_page; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className   = i === pag.current_page
                ? 'profile-submit'
                : 'profile-btn profile-btn--secondary';
            btn.style.cssText = 'min-width:36px;min-height:36px;padding:0;font-size:13px;border-radius:10px';
            btn.addEventListener('click', () => { historyPage = i; loadHistory(); });
            wrap.appendChild(btn);
        }
    }

    /* Xoá 1 item */
    window.deleteHistoryItem = async function (id, btn) {
        btn.disabled = true;
        try {
            const res = await fetch(`${API_BASE}/currency/history/${id}`, {
                method: 'DELETE', headers: jsonHeaders()
            });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error();
            await loadHistory();
        } catch (e) {
            btn.disabled = false;
            showAlert('error', 'Xoá thất bại', 'Vui lòng thử lại.');
        }
    };

    /* Xoá tất cả */
    document.getElementById('clearHistoryBtn')?.addEventListener('click', async function () {
        if (!confirm('Xoá toàn bộ lịch sử quy đổi?')) return;
        this.disabled = true;
        try {
            await fetch(`${API_BASE}/currency/history`, { method:'DELETE', headers: jsonHeaders() });
            await loadHistory();
            showAlert('success', 'Đã xoá', 'Toàn bộ lịch sử đã được xoá.');
        } catch (e) {
            showAlert('error', 'Lỗi', 'Không thể xoá lịch sử.');
        } finally {
            this.disabled = false;
        }
    });

    /* Reset result */
    function resetResult() {
        document.getElementById('resultCard').style.display = 'none';
        document.getElementById('toDisplay').textContent    = '—';
        document.getElementById('rateInfoText').textContent = 'Nhập số tiền và nhấn Quy đổi';
    }

    function quickConvert(from, to) {
        document.getElementById('fromCurrency').value = from;
        document.getElementById('toCurrency').value   = to;
        document.getElementById('fromAmount').value   = '';
        document.getElementById('fromAmount').focus();
        resetResult();
    }

    /* Init */
    async function init() {
        try {
            const res  = await fetch(`${API_BASE}/currency`, { method:'GET', headers: jsonHeaders() });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message ?? 'Lỗi tải cấu hình.');

            const { currencies, market_pairs, defaults } = json.data;
            buildSelects(currencies, defaults);
            buildMarketGrid(market_pairs);

            document.getElementById('convertBtn')  .addEventListener('click',  doConvert);
            document.getElementById('swapBtn')     .addEventListener('click',  () => {
                const f = document.getElementById('fromCurrency');
                const t = document.getElementById('toCurrency');
                [f.value, t.value] = [t.value, f.value];
                resetResult();
            });
            document.getElementById('fromAmount')  .addEventListener('keydown', e => { if (e.key === 'Enter') doConvert(); });
            document.getElementById('fromCurrency').addEventListener('change',  resetResult);
            document.getElementById('toCurrency')  .addEventListener('change',  resetResult);

            document.getElementById('currencySkeleton').style.display = 'none';
            document.getElementById('currencyContent') .style.display = '';

            await Promise.all([
                loadMarket(market_pairs),
                loadHistory(),
            ]);

        } catch (err) {
            document.getElementById('currencySkeleton').style.display = 'none';
            document.getElementById('currencyContent') .style.display = '';
            showAlert('error', 'Lỗi tải dữ liệu', err.message);
        }
    }
    init();
})();
</script>
@endsection