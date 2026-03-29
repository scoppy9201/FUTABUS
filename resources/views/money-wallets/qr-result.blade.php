@extends('layouts.app')
@section('title', 'Mã QR chuyển tiền')
@section('content')
<style>
.qr-result-wrap {
    max-width: 480px; margin: 0 auto;
    background: white; border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;
}
body.dark .qr-result-wrap { background: #191d27; }
.qr-result-hdr {
    padding: 22px 28px;
    background: linear-gradient(135deg, #1e3a5f, #4a90e2);
    text-align: center; color: white;
}
.qr-result-hdr h2 { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
.qr-result-hdr p  { font-size: 13px; opacity: .85; }
.qr-result-body { padding: 32px 28px; text-align: center; }
.qr-img-wrap {
    display: inline-block; padding: 14px; background: white;
    border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.qr-img-wrap img { display: block; width: 260px; height: 260px; }
.qr-info { background: #f8fafc; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; text-align: left; }
body.dark .qr-info { background: rgba(255,255,255,.04); }
.qr-info-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; }
.qr-info-row + .qr-info-row { border-top: 1px solid #f0f0f0; }
body.dark .qr-info-row + .qr-info-row { border-color: rgba(255,255,255,.06); }
.qr-info-label { font-size: 13px; color: #9ca3af; font-weight: 600; }
.qr-info-val   { font-size: 14px; font-weight: 800; color: #1f2937; }
body.dark .qr-info-val { color: #e5e7eb; }
.countdown {
    font-size: 28px; font-weight: 900; color: #f59e0b;
    margin-bottom: 8px; letter-spacing: -1px;
}
.countdown.danger { color: #ef4444; animation: pulse 1s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
.qr-actions { display: flex; gap: 12px; margin-top: 20px; }
.btn-copy, .btn-print, .btn-back {
    flex: 1; padding: 11px; border-radius: 10px;
    font-size: 13px; font-weight: 700; border: 2px solid; cursor: pointer; transition: all .2s;
}
.btn-copy  { background: var(--primary); color: white; border-color: var(--primary); }
.btn-print { background: transparent; color: #374151; border-color: #e5e7eb; }
body.dark .btn-print { color: #e5e7eb; border-color: rgba(255,255,255,.12); }
.btn-back  { background: transparent; color: var(--primary); border-color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
.btn-copy:hover  { opacity: .88; }
.btn-print:hover { background: #f3f4f6; }
.btn-back:hover  { background: var(--primary); color: white; }
</style>

<div style="max-width:480px;margin:0 auto;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <a href="{{ route('money-wallets.qr.index') }}" style="font-size:22px;color:#9ca3af;text-decoration:none;">←</a>
        <h1 style="font-size:20px;font-weight:800;color:#1f2937;margin:0;">Mã QR của bạn</h1>
    </div>
</div>

<div class="qr-result-wrap">
    <div class="qr-result-hdr">
        <h2>🔲 Mã QR sẵn sàng</h2>
        <p>Cho người nhận quét mã hoặc gửi link bên dưới</p>
    </div>
    <div class="qr-result-body">
        {{-- QR Image --}}
        <div class="qr-img-wrap">
            <img src="{{ $qrApiUrl }}" alt="QR Code" id="qrImg">
        </div>

        {{-- Countdown --}}
        <div class="countdown" id="countdown">15:00</div>
        <div style="font-size:12px;color:#9ca3af;margin-bottom:20px;">Mã hết hạn sau thời gian trên</div>

        {{-- Info --}}
        <div class="qr-info">
            <div class="qr-info-row">
                <span class="qr-info-label">Ví nguồn</span>
                <span class="qr-info-val">{{ $wallet->bieu_tuong }} {{ $wallet->ten_vi }}</span>
            </div>
            <div class="qr-info-row">
                <span class="qr-info-label">Số tiền</span>
                <span class="qr-info-val" style="color:#ef4444;">-{{ number_format($qrTransfer->so_tien) }}đ</span>
            </div>
            @if($qrTransfer->ghi_chu)
            <div class="qr-info-row">
                <span class="qr-info-label">Ghi chú</span>
                <span class="qr-info-val">{{ $qrTransfer->ghi_chu }}</span>
            </div>
            @endif
            <div class="qr-info-row">
                <span class="qr-info-label">Mã token</span>
                <span class="qr-info-val" style="font-size:12px;font-family:monospace;">{{ Str::limit($qrTransfer->qr_token, 16) }}...</span>
            </div>
        </div>

        {{-- Link để gửi --}}
        <div style="background:#f0f4ff;border-radius:10px;padding:12px 16px;margin-bottom:8px;word-break:break-all;font-size:12px;color:#374151;text-align:left;">
            <span style="font-weight:700;color:var(--primary);">🔗 Link: </span>
            <span id="linkText">{{ $qrUrl }}</span>
        </div>

        <div class="qr-actions">
            <button class="btn-copy" onclick="copyLink()">📋 Copy link</button>
            <button class="btn-print" onclick="window.print()">🖨️ In QR</button>
            <a href="{{ route('money-wallets.qr.index') }}" class="btn-back">← Quay lại</a>
        </div>

        {{-- Huỷ QR --}}
        <form action="{{ route('money-wallets.qr.cancel', $qrTransfer) }}" method="POST"
              style="margin-top:14px;" onsubmit="return confirm('Huỷ mã QR này?')">
            @csrf
            <button type="submit" style="background:none;border:none;color:#ef4444;font-size:13px;font-weight:600;cursor:pointer;text-decoration:underline;">
                Huỷ mã QR
            </button>
        </form>
    </div>
</div>

<script>
// Đếm ngược 15 phút
let total = 15 * 60;
const el = document.getElementById('countdown');
const timer = setInterval(() => {
    total--;
    if (total <= 0) {
        clearInterval(timer);
        el.textContent = 'Hết hạn';
        el.classList.add('danger');
        return;
    }
    const m = String(Math.floor(total / 60)).padStart(2, '0');
    const s = String(total % 60).padStart(2, '0');
    el.textContent = `${m}:${s}`;
    if (total <= 60) el.classList.add('danger');
}, 1000);

function copyLink() {
    const link = document.getElementById('linkText').textContent;
    navigator.clipboard.writeText(link).then(() => {
        const btn = event.target;
        btn.textContent = '✓ Đã copy!';
        setTimeout(() => btn.textContent = '📋 Copy link', 2000);
    });
}
</script>
@endsection
