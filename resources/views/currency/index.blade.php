@extends('layouts.app')
@section('title', 'Quy đổi tiền tệ')
@section('content')

<style>
.cx { max-width: 900px; margin: 0 auto; }

/* ── HEADER ── */
.cx-hdr {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 28px;
}
.cx-hdr-left { display: flex; flex-direction: column; gap: 4px; }
.cx-hdr-title {
    font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;
}
body.dark .cx-hdr-title { color: #f1f5f9; }
.cx-hdr-sub {
    font-size: 13px; color: #64748b;
    display: flex; align-items: center; gap: 6px;
}
.cx-live-dot {
    width: 7px; height: 7px; border-radius: 50%; background: #10b981;
    animation: livepulse 2s infinite;
}
@keyframes livepulse {
    0%,100% { opacity: 1; } 50% { opacity: 0.4; }
}

/* ── MAIN CONVERTER ── */
.cx-main {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 24px rgba(0,0,0,0.04);
    margin-bottom: 16px;
    border: 1px solid #f1f5f9;
}
body.dark .cx-main { background: #1e2433; border-color: rgba(255,255,255,0.06); }

.cx-top {
    padding: 32px 32px 0;
    display: grid;
    grid-template-columns: 1fr 56px 1fr;
    gap: 12px;
    align-items: end;
}

.cx-field { display: flex; flex-direction: column; gap: 6px; }

.cx-field-label {
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1px;
    color: #94a3b8;
}

.cx-input-box {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    transition: all 0.2s;
    display: flex;
}
.cx-input-box:focus-within {
    border-color: #4a90e2;
    background: white;
    box-shadow: 0 0 0 4px rgba(74,144,226,0.08);
}
body.dark .cx-input-box { background: #141820; border-color: rgba(255,255,255,0.08); }
body.dark .cx-input-box:focus-within { background: #0f1217; border-color: #4a90e2; }

.cx-select {
    padding: 0 4px 0 14px;
    border: none; outline: none;
    font-size: 14px; font-weight: 700;
    color: #0f172a; background: transparent;
    cursor: pointer; min-width: 105px;
    height: 60px;
    appearance: none; -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%2394a3b8' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 26px;
}
body.dark .cx-select { color: #e2e8f0; }

.cx-divider {
    width: 1px; background: #e2e8f0; align-self: stretch; flex-shrink: 0;
}
body.dark .cx-divider { background: rgba(255,255,255,0.08); }

.cx-amount-input {
    flex: 1; border: none; outline: none;
    font-size: 24px; font-weight: 800;
    color: #0f172a; background: transparent;
    padding: 0 16px; height: 60px;
    letter-spacing: -0.5px; width: 100%;
}
body.dark .cx-amount-input { color: #f1f5f9; }
.cx-amount-input::placeholder { color: #cbd5e1; }

.cx-result-box {
    flex: 1; height: 60px;
    display: flex; align-items: center;
    padding: 0 16px;
    font-size: 24px; font-weight: 800;
    letter-spacing: -0.5px;
    color: #4a90e2; background: transparent;
    white-space: nowrap; overflow: hidden;
}

/* swap */
.cx-swap {
    width: 44px; height: 44px; border-radius: 50%;
    background: white;
    border: 1.5px solid #e2e8f0;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.25s ease;
    flex-shrink: 0; align-self: center; margin-bottom: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.cx-swap:hover {
    background: #4a90e2; border-color: #4a90e2;
    transform: rotate(180deg);
    box-shadow: 0 4px 16px rgba(74,144,226,0.3);
}
.cx-swap:hover svg { stroke: white; }
body.dark .cx-swap { background: #141820; border-color: rgba(255,255,255,0.1); }

/* rate strip */
.cx-rate-strip {
    margin: 16px 32px 0;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 10px;
    font-size: 13px; color: #64748b;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px;
}
body.dark .cx-rate-strip { background: #141820; color: #94a3b8; }
.cx-rate-val { font-weight: 700; color: #0f172a; }
body.dark .cx-rate-val { color: #e2e8f0; }

/* convert btn */
.cx-btn-wrap { padding: 20px 32px 32px; }
.cx-btn {
    width: 100%; height: 52px;
    background: #4a90e2;
    border: none; border-radius: 14px;
    color: white; font-size: 15px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all 0.2s;
    letter-spacing: 0.2px;
}
.cx-btn:hover:not(:disabled) {
    background: #2a5298;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(74,144,226,0.3);
}
.cx-btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
.cx-btn-spin {
    width: 18px; height: 18px;
    border: 2px solid rgba(255,255,255,0.35);
    border-top-color: white; border-radius: 50%;
    animation: spin .6s linear infinite; display: none;
}
.cx-btn.loading .cx-btn-spin { display: block; }
.cx-btn.loading .cx-btn-lbl { display: none; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── RESULT BANNER ── */
.cx-result {
    background: #0f172a;
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 16px;
    display: none;
    animation: fadeUp .3s ease;
    border: 1px solid #1e293b;
}
.cx-result.show { display: block; }
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.cx-result-top {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 16px;
    margin-bottom: 20px; flex-wrap: wrap;
}
.cx-result-from { font-size: 14px; color: #64748b; margin-bottom: 4px; }
.cx-result-num  { font-size: 42px; font-weight: 900; color: white; letter-spacing: -1.5px; line-height: 1; }
.cx-result-code { font-size: 16px; color: #94a3b8; font-weight: 600; margin-left: 6px; }
.cx-result-badge {
    background: #10b981; color: white;
    font-size: 12px; font-weight: 700;
    padding: 6px 14px; border-radius: 20px;
    white-space: nowrap; align-self: flex-start; margin-top: 4px;
}
.cx-result-stats {
    display: grid; grid-template-columns: repeat(3,1fr);
    gap: 12px; padding-top: 20px;
    border-top: 1px solid #1e293b;
}
.cx-stat-item {}
.cx-stat-lbl { font-size: 11px; color: #475569; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 4px; }
.cx-stat-val { font-size: 14px; font-weight: 700; color: #e2e8f0; }

/* ── MARKET GRID ── */
.cx-section-hdr {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 14px;
}
.cx-section-title {
    font-size: 13px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;
}
.cx-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.cx-pair {
    background: white;
    border: 1px solid #f1f5f9;
    border-radius: 14px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.18s ease;
}
.cx-pair:hover {
    border-color: #4a90e2;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(74,144,226,0.12);
}
body.dark .cx-pair { background: #1e2433; border-color: rgba(255,255,255,0.06); }
body.dark .cx-pair:hover { border-color: #4a90e2; }

.cx-pair-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.cx-pair-flags { font-size: 20px; letter-spacing: 2px; }
.cx-pair-badge {
    font-size: 10px; font-weight: 700;
    padding: 2px 7px; border-radius: 20px;
}
.up   { background: #dcfce7; color: #15803d; }
.down { background: #fee2e2; color: #b91c1c; }
body.dark .up   { background: rgba(16,185,129,0.15); color: #34d399; }
body.dark .down { background: rgba(239,68,68,0.15); color: #f87171; }

.cx-pair-name { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
body.dark .cx-pair-name { color: #e2e8f0; }
.cx-pair-rate { font-size: 11px; color: #94a3b8; font-weight: 500; }
.cx-pair-rate span { color: #4a90e2; font-weight: 700; }

@media (max-width: 768px) {
    .cx-top { grid-template-columns: 1fr; padding: 20px 20px 0; }
    .cx-swap { transform: rotate(90deg); margin: 0 auto; }
    .cx-swap:hover { transform: rotate(270deg); }
    .cx-btn-wrap { padding: 16px 20px 24px; }
    .cx-rate-strip { margin: 12px 20px 0; }
    .cx-grid { grid-template-columns: repeat(2,1fr); }
    .cx-result { padding: 20px; }
    .cx-result-num { font-size: 32px; }
    .cx-result-stats { grid-template-columns: 1fr 1fr; }
    .cx-hdr-title { font-size: 20px; }
}
</style>

<div class="cx">

    {{-- Header --}}
    <div class="cx-hdr">
        <div class="cx-hdr-left">
            <div class="cx-hdr-title">Quy đổi tiền tệ</div>
            <div class="cx-hdr-sub">
                <span class="cx-live-dot"></span>
                <span id="lastUpdated">Đang tải tỷ giá...</span>
            </div>
        </div>
    </div>

    {{-- Result banner --}}
    <div class="cx-result" id="resultCard">
        <div class="cx-result-top">
            <div>
                <div class="cx-result-from" id="resultFrom">—</div>
                <div>
                    <span class="cx-result-num" id="resultNum">—</span>
                    <span class="cx-result-code" id="resultCode">—</span>
                </div>
            </div>
            <div class="cx-result-badge">Tỷ giá realtime</div>
        </div>
        <div class="cx-result-stats">
            <div class="cx-stat-item">
                <div class="cx-stat-lbl">Tỷ giá</div>
                <div class="cx-stat-val" id="rStatRate">—</div>
            </div>
            <div class="cx-stat-item">
                <div class="cx-stat-lbl">Đảo ngược</div>
                <div class="cx-stat-val" id="rStatRev">—</div>
            </div>
            <div class="cx-stat-item">
                <div class="cx-stat-lbl">Cập nhật</div>
                <div class="cx-stat-val" id="rStatTime">—</div>
            </div>
        </div>
    </div>

    {{-- Converter --}}
    <div class="cx-main">
        <div class="cx-top">
            {{-- From --}}
            <div class="cx-field">
                <div class="cx-field-label">Từ</div>
                <div class="cx-input-box">
                    <select class="cx-select" id="fromCurrency">
                        @foreach([
                            ['VND','🇻🇳 VND'],['USD','🇺🇸 USD'],['EUR','🇪🇺 EUR'],
                            ['JPY','🇯🇵 JPY'],['KRW','🇰🇷 KRW'],['CNY','🇨🇳 CNY'],
                            ['GBP','🇬🇧 GBP'],['AUD','🇦🇺 AUD'],['CAD','🇨🇦 CAD'],
                            ['SGD','🇸🇬 SGD'],['THB','🇹🇭 THB'],['HKD','🇭🇰 HKD'],
                            ['MYR','🇲🇾 MYR'],['IDR','🇮🇩 IDR'],['PHP','🇵🇭 PHP'],
                            ['INR','🇮🇳 INR'],['CHF','🇨🇭 CHF'],['TWD','🇹🇼 TWD'],
                        ] as [$val,$lbl])
                        <option value="{{ $val }}" {{ $val==='VND'?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <div class="cx-divider"></div>
                    <input class="cx-amount-input" type="number" id="fromAmount" placeholder="0" value="1000000" min="0">
                </div>
            </div>

            {{-- Swap --}}
            <button class="cx-swap" onclick="swapCurrencies()" title="Hoán đổi">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 16L3 12L7 8"/><path d="M17 8L21 12L17 16"/><path d="M3 12H21"/>
                </svg>
            </button>

            {{-- To --}}
            <div class="cx-field">
                <div class="cx-field-label">Sang</div>
                <div class="cx-input-box">
                    <select class="cx-select" id="toCurrency">
                        @foreach([
                            ['VND','🇻🇳 VND'],['USD','🇺🇸 USD'],['EUR','🇪🇺 EUR'],
                            ['JPY','🇯🇵 JPY'],['KRW','🇰🇷 KRW'],['CNY','🇨🇳 CNY'],
                            ['GBP','🇬🇧 GBP'],['AUD','🇦🇺 AUD'],['CAD','🇨🇦 CAD'],
                            ['SGD','🇸🇬 SGD'],['THB','🇹🇭 THB'],['HKD','🇭🇰 HKD'],
                            ['MYR','🇲🇾 MYR'],['IDR','🇮🇩 IDR'],['PHP','🇵🇭 PHP'],
                            ['INR','🇮🇳 INR'],['CHF','🇨🇭 CHF'],['TWD','🇹🇼 TWD'],
                        ] as [$val,$lbl])
                        <option value="{{ $val }}" {{ $val==='USD'?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <div class="cx-divider"></div>
                    <div class="cx-result-box" id="toDisplay">—</div>
                </div>
            </div>
        </div>

        {{-- Rate strip --}}
        <div class="cx-rate-strip">
            <span id="rateInfoText">Nhập số tiền và nhấn quy đổi</span>
            <span style="font-size:11px;color:#94a3b8;">Nguồn: exchangerate-api.com</span>
        </div>

        {{-- Button --}}
        <div class="cx-btn-wrap">
            <button class="cx-btn" id="convertBtn" onclick="doConvert()">
                <div class="cx-btn-spin"></div>
                <span class="cx-btn-lbl">Quy đổi</span>
            </button>
        </div>
    </div>

    {{-- Market grid --}}
    <div class="cx-section-hdr">
        <div class="cx-section-title">Tỷ giá phổ biến · VND</div>
    </div>
    <div class="cx-grid" id="mktGrid">
        @foreach([
            ['USD','🇺🇸','🇻🇳','Đô la / Đồng'],
            ['EUR','🇪🇺','🇻🇳','Euro / Đồng'],
            ['JPY','🇯🇵','🇻🇳','Yên / Đồng'],
            ['KRW','🇰🇷','🇻🇳','Won / Đồng'],
            ['CNY','🇨🇳','🇻🇳','NDT / Đồng'],
            ['GBP','🇬🇧','🇻🇳','Bảng / Đồng'],
            ['SGD','🇸🇬','🇻🇳','SGD / Đồng'],
            ['THB','🇹🇭','🇻🇳','Baht / Đồng'],
        ] as [$code,$f1,$f2,$name])
        <div class="cx-pair" onclick="quickConvert('{{ $code }}','VND')">
            <div class="cx-pair-top">
                <span class="cx-pair-flags">{{ $f1 }}{{ $f2 }}</span>
                <span class="cx-pair-badge up" id="badge-{{ $code }}">—</span>
            </div>
            <div class="cx-pair-name">{{ $code }}/VND</div>
            <div class="cx-pair-rate">1 {{ $code }} = <span id="mkt-{{ $code }}">...</span></div>
        </div>
        @endforeach
    </div>

</div>

<script>
const API = 'https://api.exchangerate-api.com/v4/latest/';
let cache = {};

const fmt = (v, cur) => new Intl.NumberFormat('vi-VN', {
    style: 'currency', currency: cur,
    maximumFractionDigits: ['JPY','KRW','VND','IDR'].includes(cur) ? 0 : 2
}).format(v);

const fmtRate = (r) => r >= 1000 ? r.toLocaleString('vi-VN', {maximumFractionDigits:0})
    : r >= 1 ? r.toFixed(4) : r.toFixed(6);

async function getRates(base) {
    if (!cache[base]) {
        const r = await fetch(API + base);
        cache[base] = (await r.json()).rates;
    }
    return cache[base];
}

async function loadMarket() {
    try {
        const rates = await getRates('USD');
        const vndPerUsd = rates['VND'];
        const pairs = ['USD','EUR','JPY','KRW','CNY','GBP','SGD','THB'];

        // fake prev để tạo badge (thực tế nên cache ngày hôm qua)
        pairs.forEach(code => {
            const vndRate = code === 'USD' ? vndPerUsd : vndPerUsd / rates[code];
            const el = document.getElementById('mkt-' + code);
            const badge = document.getElementById('badge-' + code);
            if (el) el.textContent = fmtRate(vndRate) + ' ₫';
            if (badge) {
                // random up/down nhẹ để minh hoạ — thực tế dùng API lịch sử
                const up = Math.random() > 0.4;
                badge.textContent = (up ? '▲' : '▼') + ' ' + (Math.random()*0.5+0.01).toFixed(2) + '%';
                badge.className   = 'cx-pair-badge ' + (up ? 'up' : 'down');
            }
        });

        const now = new Date();
        document.getElementById('lastUpdated').textContent =
            'Tỷ giá realtime · ' + now.toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'});

        // Load thêm cache VND
        await getRates('VND');
    } catch(e) {
        document.getElementById('lastUpdated').textContent = 'Không thể tải tỷ giá';
    }
}

async function doConvert() {
    const from   = document.getElementById('fromCurrency').value;
    const to     = document.getElementById('toCurrency').value;
    const amount = parseFloat(document.getElementById('fromAmount').value);
    const btn    = document.getElementById('convertBtn');

    if (!amount || amount <= 0) {
        document.getElementById('rateInfoText').textContent = '⚠ Vui lòng nhập số tiền hợp lệ';
        return;
    }
    if (from === to) {
        document.getElementById('toDisplay').textContent = fmt(amount, to);
        return;
    }

    btn.classList.add('loading'); btn.disabled = true;

    try {
        const rates  = await getRates(from);
        const rate   = rates[to];
        const result = amount * rate;

        document.getElementById('toDisplay').textContent    = fmt(result, to);
        document.getElementById('rateInfoText').textContent =
            `1 ${from} = ${fmtRate(rate)} ${to}  ·  1 ${to} = ${fmtRate(1/rate)} ${from}`;

        // Result banner
        const card = document.getElementById('resultCard');
        document.getElementById('resultFrom').textContent  = `${fmt(amount,from)} bằng`;
        document.getElementById('resultNum').textContent   =
            new Intl.NumberFormat('vi-VN', {maximumFractionDigits: ['JPY','KRW','VND','IDR'].includes(to)?0:2}).format(result);
        document.getElementById('resultCode').textContent  = to;
        document.getElementById('rStatRate').textContent   = `1 ${from} = ${fmtRate(rate)} ${to}`;
        document.getElementById('rStatRev').textContent    = `1 ${to} = ${fmtRate(1/rate)} ${from}`;
        document.getElementById('rStatTime').textContent   =
            new Date().toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'});
        card.classList.add('show');

    } catch(e) {
        document.getElementById('rateInfoText').textContent = 'Lỗi kết nối. Thử lại sau!';
    }

    btn.classList.remove('loading'); btn.disabled = false;
}

function swapCurrencies() {
    const f = document.getElementById('fromCurrency');
    const t = document.getElementById('toCurrency');
    [f.value, t.value] = [t.value, f.value];
    document.getElementById('resultCard').classList.remove('show');
    document.getElementById('toDisplay').textContent = '—';
    document.getElementById('rateInfoText').textContent = 'Nhập số tiền và nhấn quy đổi';
}

function quickConvert(from, to) {
    document.getElementById('fromCurrency').value = from;
    document.getElementById('toCurrency').value   = to;
    document.getElementById('fromAmount').value   = ['VND','IDR','KRW','JPY'].includes(from) ? 1000000 : 1;
    doConvert();
}

document.getElementById('fromAmount').addEventListener('keydown', e => {
    if (e.key === 'Enter') doConvert();
});

document.getElementById('fromCurrency').addEventListener('change', () => {
    document.getElementById('resultCard').classList.remove('show');
    document.getElementById('toDisplay').textContent = '—';
});

document.getElementById('toCurrency').addEventListener('change', () => {
    document.getElementById('resultCard').classList.remove('show');
    document.getElementById('toDisplay').textContent = '—';
});

loadMarket();
</script>

@endsection