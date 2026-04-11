{{-- ═══ qr/scan-confirm.blade.php ═══ --}}
@extends('layouts.app')
@section('title', 'Xác nhận nhận tiền')
@section('content')
<style>
.scan-confirm-wrap {
    max-width: 460px; margin: 0 auto;
    background: white; border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;
}
body.dark .scan-confirm-wrap { background: #191d27; }
.sc-hdr {
    padding: 24px 28px;
    background: linear-gradient(135deg, #065f46, #10b981);
    text-align: center; color: white;
}
.sc-hdr .amount { font-size: 40px; font-weight: 900; margin: 12px 0 4px; letter-spacing: -2px; }
.sc-hdr .from   { font-size: 14px; opacity: .9; }
.sc-body { padding: 28px; }
.info-box { background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 20px; }
body.dark .info-box { background: rgba(255,255,255,.04); }
.info-row { display: flex; justify-content: space-between; padding: 7px 0; font-size: 14px; }
.info-row + .info-row { border-top: 1px solid #f0f0f0; }
body.dark .info-row + .info-row { border-color: rgba(255,255,255,.06); }
.info-row .lbl { color: #9ca3af; font-weight: 600; }
.info-row .val { font-weight: 700; color: #1f2937; }
body.dark .info-row .val { color: #e5e7eb; }
.form-group { margin-bottom: 20px; }
.form-label { font-size: 13px; font-weight: 700; color: #374151; display: block; margin-bottom: 7px; }
body.dark .form-label { color: #9ca3af; }
.form-ctrl {
    width: 100%; padding: 11px 14px;
    border: 2px solid #e5e7eb; border-radius: 10px;
    font-size: 14px; background: #f9fafb; color: #1f2937; outline: none; transition: all .2s;
}
.form-ctrl:focus { border-color: #10b981; background: white; }
body.dark .form-ctrl { background: #141820; border-color: rgba(255,255,255,.1); color: #e5e7eb; }
.btn-confirm {
    width: 100%; padding: 14px; border-radius: 12px;
    background: linear-gradient(135deg, #059669, #10b981);
    color: white; font-size: 16px; font-weight: 800;
    border: none; cursor: pointer; transition: opacity .2s;
}
.btn-confirm:hover { opacity: .9; }
.btn-back-link { display: block; text-align: center; margin-top: 16px; color: #9ca3af; font-size: 13px; text-decoration: none; }
.btn-back-link:hover { color: var(--primary); }
.skeleton { background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 8px; }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }
.alert { display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:10px;font-size:14px;font-weight:500;margin-bottom:20px; }
.alert-error { background:#fee2e2;color:#991b1b;border-left:4px solid #ef4444; }
</style>

<div style="max-width:460px;margin:0 auto;">
    <h1 style="font-size:20px;font-weight:800;color:#1f2937;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
        📥 Nhận tiền QR
    </h1>
</div>

<div id="alertContainer" style="max-width:460px;margin:0 auto;"></div>

<div id="confirmWrap" style="max-width:460px;margin:0 auto;">
    <div class="skeleton" style="height:420px;border-radius:20px;"></div>
</div>

<script>
const QR_TOKEN = '{{ request()->route("token") }}';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json', ...options.headers },
        ...options,
    });
    return res.json();
}

function showAlert(msg, type = 'error') {
    const el = document.createElement('div');
    el.className = `alert alert-${type}`;
    el.textContent = '⚠ ' + msg;
    document.getElementById('alertContainer').appendChild(el);
}

function fmt(n) { return Number(n).toLocaleString('vi-VN'); }

async function loadConfirm() {
    const [qrData, walletData] = await Promise.all([
        apiFetch(`/api/money-wallets/qr/${QR_TOKEN}`),
        apiFetch('/api/money-wallets'),
    ]);

    if (!qrData.qr_token || qrData.trang_thai !== 'pending') {
        document.getElementById('confirmWrap').innerHTML = `
        <div class="scan-confirm-wrap">
            <div style="text-align:center;padding:48px 32px;color:#ef4444;">
                <div style="font-size:48px;margin-bottom:12px;">❌</div>
                <div style="font-weight:700;font-size:16px;">${qrData.message || 'QR không hợp lệ hoặc đã hết hạn'}</div>
                <a href="{{ route('money-wallets.qr.index') }}" class="btn-back-link" style="margin-top:16px;display:inline-block;">← Quay lại</a>
            </div>
        </div>`;
        return;
    }

    const myWallets = (walletData.filter ? walletData : []).filter(w => w.trang_thai !== 'khong_hoat_dong');
    const expiresAt = new Date(qrData.expires_at).toLocaleString('vi-VN');

    const walletOptions = myWallets.length
        ? '<option value="">-- Chọn ví --</option>' + myWallets.map(w => `<option value="${w.id}">${w.bieu_tuong} ${w.ten_vi} — ${fmt(w.so_du)}đ</option>`).join('')
        : '<option value="">Bạn chưa có ví nào</option>';

    document.getElementById('confirmWrap').innerHTML = `
    <div class="scan-confirm-wrap">
        <div class="sc-hdr">
            <div style="font-size:13px;opacity:.85;">Bạn được chuyển</div>
            <div class="amount">+${fmt(qrData.so_tien)}đ</div>
            <div class="from">từ ${qrData.sender?.name || ''}</div>
        </div>
        <div class="sc-body">
            <div class="info-box">
                <div class="info-row"><span class="lbl">Người gửi</span><span class="val">${qrData.sender?.name || ''}</span></div>
                <div class="info-row"><span class="lbl">Ví gửi</span><span class="val">${qrData.sender_wallet?.bieu_tuong || ''} ${qrData.sender_wallet?.ten_vi || ''}</span></div>
                ${qrData.ghi_chu ? `<div class="info-row"><span class="lbl">Ghi chú</span><span class="val">${qrData.ghi_chu}</span></div>` : ''}
                <div class="info-row"><span class="lbl">Hết hạn lúc</span><span class="val">${expiresAt}</span></div>
            </div>
            ${!myWallets.length
                ? `<div style="text-align:center;color:#9ca3af;padding:20px;">Bạn chưa có ví nào để nhận tiền. <a href="{{ route('money-wallets.index') }}">Tạo ví ngay</a></div>`
                : `<div class="form-group">
                    <label class="form-label">Chọn ví nhận <span style="color:#ef4444;">*</span></label>
                    <select id="receiverWalletId" class="form-ctrl">${walletOptions}</select>
                </div>
                <button class="btn-confirm" onclick="confirmReceive()">✅ Xác nhận nhận tiền</button>`
            }
            <a href="{{ route('money-wallets.qr.index') }}" class="btn-back-link">← Quay lại</a>
        </div>
    </div>`;
}

async function confirmReceive() {
    const receiver_wallet_id = document.getElementById('receiverWalletId')?.value;
    if (!receiver_wallet_id) { showAlert('Vui lòng chọn ví nhận'); return; }

    const res = await apiFetch(`/api/money-wallets/qr/${QR_TOKEN}/confirm`, {
        method: 'POST',
        body: JSON.stringify({ receiver_wallet_id }),
    });

    if (res.success) {
        window.location.href = '{{ route("money-wallets.index") }}';
    } else {
        showAlert(res.message || 'Có lỗi xảy ra');
    }
}

loadConfirm();
</script>
@endsection
