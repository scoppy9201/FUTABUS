@extends('layouts.app')
@section('title', 'Chuyển tiền QR')
@section('content')
<style>
:root {
    --primary: #4a90e2; --primary-dark: #2a5298;
    --success: #10b981; --danger: #ef4444;
    --radius: 16px; --radius-sm: 10px;
    --shadow: 0 2px 12px rgba(0,0,0,0.07);
}
.qr-layout { display: grid; grid-template-columns: 420px 1fr; gap: 24px; align-items: start; }
.section-card { background: white; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
body.dark .section-card { background: #191d27; }
.sc-header {
    padding: 18px 24px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    font-size: 16px; font-weight: 800; color: white;
    display: flex; align-items: center; gap: 10px;
}
.sc-body { padding: 24px; }
.form-group { margin-bottom: 18px; }
.form-label { font-size: 13px; font-weight: 700; color: #374151; display: block; margin-bottom: 7px; }
body.dark .form-label { color: #9ca3af; }
.required { color: var(--danger); }
.form-ctrl {
    width: 100%; padding: 11px 14px;
    border: 2px solid #e5e7eb; border-radius: var(--radius-sm);
    font-size: 14px; background: #f9fafb; color: #1f2937; outline: none; transition: all .2s;
}
.form-ctrl:focus { border-color: var(--primary); background: white; }
body.dark .form-ctrl { background: #141820; border-color: rgba(255,255,255,.1); color: #e5e7eb; }
.btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 24px; border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; font-size: 14px; font-weight: 700;
    border: none; cursor: pointer; width: 100%; justify-content: center; transition: opacity .2s;
}
.btn-primary:hover { opacity: .88; }
.alert { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 500; margin-bottom: 20px; }
.alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
.alert-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--danger); }

.scan-box {
    border: 2px dashed #d1d5db; border-radius: var(--radius);
    padding: 20px; text-align: center; margin-top: 20px; cursor: pointer; transition: all .2s;
}
.scan-box:hover { border-color: var(--primary); background: rgba(74,144,226,.03); }
body.dark .scan-box { border-color: rgba(255,255,255,.12); }
#video { width: 100%; border-radius: 10px; display: none; margin-top: 12px; }

.history-list { display: flex; flex-direction: column; gap: 12px; }
.history-item {
    background: white; border-radius: var(--radius-sm);
    padding: 16px 20px; box-shadow: var(--shadow);
    display: flex; align-items: center; gap: 16px;
    border-left: 4px solid #e5e7eb;
}
body.dark .history-item { background: #191d27; }
.history-item.sent     { border-left-color: var(--danger); }
.history-item.received { border-left-color: var(--success); }
.history-item.pending  { border-left-color: #f59e0b; }
.history-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
.history-icon.sent     { background: #fee2e2; }
.history-icon.received { background: #d1fae5; }
.history-icon.pending  { background: #fef3c7; }
.history-info { flex: 1; min-width: 0; }
.history-title { font-size: 14px; font-weight: 700; color: #1f2937; }
body.dark .history-title { color: #e5e7eb; }
.history-sub { font-size: 12px; color: #9ca3af; margin-top: 3px; }
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.badge-pending   { background: #fef3c7; color: #92400e; }
.badge-completed { background: #d1fae5; color: #065f46; }
.badge-cancelled { background: #f3f4f6; color: #6b7280; }
.badge-expired   { background: #fee2e2; color: #991b1b; }
.btn-cancel-sm { padding: 4px 12px; border-radius: 8px; background: #fee2e2; border: none; color: var(--danger); font-size: 12px; font-weight: 700; cursor: pointer; transition: background .2s; }
.btn-cancel-sm:hover { background: #fecaca; }
.empty-history { text-align: center; padding: 60px 20px; color: #9ca3af; }
.skeleton { background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 8px; }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }
@media (max-width: 900px) { .qr-layout { grid-template-columns: 1fr; } }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('money-wallets.index') }}" style="text-decoration:none;font-size:22px;color:#9ca3af;">←</a>
        <h1 style="font-size:22px;font-weight:800;color:#1f2937;margin:0;">Chuyển tiền QR</h1>
    </div>
</div>

<div id="alertContainer"></div>

<div class="qr-layout">
    <div>
        <div class="section-card">
            <div class="sc-header">Tạo mã QR chuyển tiền</div>
            <div class="sc-body">
                <div class="form-group">
                    <label class="form-label">Ví nguồn <span class="required">*</span></label>
                    <select id="walletId" class="form-ctrl">
                        <option value="">-- Chọn ví --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Số tiền <span class="required">*</span></label>
                    <input type="number" id="soTien" class="form-ctrl" placeholder="Nhập số tiền..." min="1000" step="1000">
                    <div style="font-size:11px;color:#9ca3af;margin-top:4px;">Tối thiểu 1.000đ</div>
                </div>
                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label">Ghi chú</label>
                    <input type="text" id="ghiChu" class="form-ctrl" placeholder="Nội dung chuyển tiền..." maxlength="255">
                </div>
                <button class="btn-primary" onclick="generateQR()">Tạo mã QR</button>

                <div class="scan-box" id="scanToggle" onclick="toggleCamera()">
                    <div style="font-size:32px;margin-bottom:8px;">📷</div>
                    <div style="font-weight:700;color:#374151;font-size:14px;">Quét mã QR</div>
                    <div style="font-size:12px;color:#9ca3af;margin-top:4px;">Nhấn để mở camera và quét</div>
                </div>
                <video id="video" autoplay playsinline></video>
                <div id="scanError" style="color:var(--danger);font-size:13px;margin-top:8px;display:none;"></div>

                <div class="scan-box" id="uploadBox" style="margin-top:12px;" onclick="document.getElementById('qrFileInput').click()">
                    <div style="font-size:32px;margin-bottom:8px;">🖼️</div>
                    <div style="font-weight:700;color:#374151;font-size:14px;">Upload ảnh QR</div>
                    <div style="font-size:12px;color:#9ca3af;margin-top:4px;">Nhấn để chọn ảnh chứa mã QR</div>
                </div>
                <input type="file" id="qrFileInput" accept="image/*" style="display:none" onchange="handleQrUpload(event)">
                <div id="uploadResult" style="margin-top:10px;display:none;"></div>
            </div>
        </div>

        <div class="section-card" style="margin-top:16px;">
            <div class="sc-header" style="background:linear-gradient(135deg,#059669,#047857);">Hướng dẫn</div>
            <div class="sc-body" style="font-size:13px;color:#374151;line-height:1.8;">
                <b>Người gửi:</b><br>
                Chọn ví & nhập số tiền → Tạo QR<br>
                Cho người nhận quét mã hoặc gửi link<br>
                QR hết hạn sau <b>15 phút</b><br><br>
                <b>Người nhận:</b><br>
                Quét QR hoặc mở link<br>
                Chọn ví nhận → Xác nhận<br>
                Tiền được chuyển ngay lập tức
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="sc-header">Lịch sử QR Transfer</div>
        <div class="sc-body">
            <div id="historyContainer">
                <div class="skeleton" style="height:80px;border-radius:10px;margin-bottom:12px;"></div>
                <div class="skeleton" style="height:80px;border-radius:10px;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/@zxing/library@0.18.6/umd/index.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
const CURRENT_USER_ID = {{ auth()->id() }};

async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json', ...options.headers },
        ...options,
    });
    return res.json();
}

function showAlert(msg, type = 'success') {
    const el = document.createElement('div');
    el.className = `alert alert-${type}`;
    el.textContent = (type === 'success' ? '✓ ' : '⚠ ') + msg;
    document.getElementById('alertContainer').appendChild(el);
    setTimeout(() => { el.style.transition='opacity .3s'; el.style.opacity='0'; setTimeout(()=>el.remove(),320); }, 4500);
}

function fmt(n) { return Number(n).toLocaleString('vi-VN'); }

async function loadWallets() {
    const wallets = await apiFetch('/api/money-wallets');
    const sel = document.getElementById('walletId');
    const active = wallets.filter ? wallets.filter(w => w.trang_thai !== 'khong_hoat_dong') : wallets;
    if (!active.length) {
        sel.innerHTML = '<option value="">Bạn chưa có ví nào. <a href="{{ route("money-wallets.index") }}">Tạo ví ngay</a></option>';
        return;
    }
    sel.innerHTML = '<option value="">-- Chọn ví --</option>' +
        active.map(w => `<option value="${w.id}">${w.bieu_tuong} ${w.ten_vi} — ${fmt(w.so_du)}đ</option>`).join('');
}

async function loadHistory() {
    const data = await apiFetch('/api/money-wallets/qr/history');
    const el = document.getElementById('historyContainer');

    if (!data.length) {
        el.innerHTML = `
        <div class="empty-history">
            <div style="font-size:48px;margin-bottom:12px;">📭</div>
            <div style="font-weight:700;font-size:15px;color:#374151;margin-bottom:6px;">Chưa có giao dịch QR</div>
            <div style="font-size:13px;">Tạo mã QR đầu tiên để chuyển tiền</div>
        </div>`;
        return;
    }

    const STATUS_LABELS = { pending:'Chờ', completed:'Hoàn thành', cancelled:'Đã huỷ', expired:'Hết hạn' };

    el.innerHTML = `<div class="history-list">${data.map(t => {
        const isSender   = t.sender_id === CURRENT_USER_ID;
        const statusClass = t.trang_thai === 'completed' ? (isSender ? 'sent' : 'received') : (t.trang_thai === 'pending' ? 'pending' : 'sent');
        const icon        = t.trang_thai === 'completed' ? (isSender ? '📤' : '📥') : (t.trang_thai === 'pending' ? '⏳' : '❌');
        const date        = new Date(t.created_at).toLocaleDateString('vi-VN') + ' ' + new Date(t.created_at).toLocaleTimeString('vi-VN', {hour:'2-digit',minute:'2-digit'});
        const walletName  = isSender ? t.sender_wallet?.ten_vi : t.receiver_wallet?.ten_vi;
        const amtColor    = t.trang_thai === 'completed' && !isSender ? '#10b981' : (t.trang_thai === 'completed' ? '#ef4444' : '#f59e0b');

        let titleHtml = '';
        if (t.trang_thai === 'pending') {
            titleHtml = `Đang chờ nhận${isSender ? ` <a href="/money-wallets/qr/scan/${t.qr_token}" style="font-size:11px;color:var(--primary);margin-left:6px;">🔗 Link</a>` : ''}`;
        } else if (t.trang_thai === 'completed') {
            titleHtml = isSender ? `→ ${t.receiver?.name || ''}` : `← ${t.sender?.name || ''}`;
        } else {
            titleHtml = t.trang_thai.charAt(0).toUpperCase() + t.trang_thai.slice(1);
        }

        return `
        <div class="history-item ${statusClass}">
            <div class="history-icon ${statusClass}">${icon}</div>
            <div class="history-info">
                <div class="history-title">${titleHtml}</div>
                <div class="history-sub">${date}${t.ghi_chu ? ' · ' + t.ghi_chu.substring(0,40) : ''}</div>
                ${walletName ? `<div class="history-sub">${walletName}</div>` : ''}
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <div style="font-size:17px;font-weight:900;color:${amtColor};">${isSender ? '-' : '+'}${fmt(t.so_tien)}đ</div>
                <div style="margin-top:6px;"><span class="badge badge-${t.trang_thai}">${STATUS_LABELS[t.trang_thai] || t.trang_thai}</span></div>
                ${t.trang_thai === 'pending' && isSender ? `<button class="btn-cancel-sm" style="margin-top:6px;" onclick="cancelQR(${t.id})">Huỷ</button>` : ''}
            </div>
        </div>`;
    }).join('')}</div>`;
}

async function generateQR() {
    const wallet_id = document.getElementById('walletId').value;
    const so_tien   = parseFloat(document.getElementById('soTien').value);
    const ghi_chu   = document.getElementById('ghiChu').value.trim();

    if (!wallet_id) { showAlert('Vui lòng chọn ví nguồn', 'error'); return; }
    if (!so_tien || so_tien < 1000) { showAlert('Số tiền tối thiểu là 1.000đ', 'error'); return; }

    const res = await apiFetch('/api/money-wallets/qr/generate', {
        method: 'POST',
        body: JSON.stringify({ wallet_id, so_tien, ghi_chu }),
    });

    if (res.qr_token) {
        window.location.href = `/money-wallets/qr/result/${res.qr_token}`;
    } else {
        showAlert(res.message || 'Có lỗi xảy ra', 'error');
    }
}

async function cancelQR(id) {
    if (!confirm('Huỷ mã QR này?')) return;
    const res = await apiFetch(`/api/money-wallets/qr/${id}/cancel`, { method: 'POST' });
    if (res.success) { showAlert('Đã huỷ mã QR'); loadHistory(); }
    else showAlert(res.message || 'Có lỗi xảy ra', 'error');
}

let cameraOn = false;
let codeReader = null;

function toggleCamera() {
    const video = document.getElementById('video');
    const err   = document.getElementById('scanError');
    const box   = document.getElementById('scanToggle');

    if (!cameraOn) {
        cameraOn = true;
        video.style.display = 'block';
        box.innerHTML = '<div style="font-size:24px;color:var(--danger);">⏹ Dừng camera</div>';
        box.onclick = stopCamera;
        err.style.display = 'none';

        codeReader = new ZXing.BrowserQRCodeReader();
        codeReader.decodeFromVideoDevice(null, 'video', (result, error) => {
            if (result) {
                const url = result.getText();
                stopCamera();
                if (url.includes('/qr/')) {
                    window.location.href = url;
                } else {
                    err.textContent = 'QR code không hợp lệ.';
                    err.style.display = 'block';
                }
            }
        });
    }
}

function stopCamera() {
    if (codeReader) { codeReader.reset(); codeReader = null; }
    const video = document.getElementById('video');
    const box   = document.getElementById('scanToggle');
    video.style.display = 'none';
    cameraOn = false;
    box.innerHTML = `<div style="font-size:32px;margin-bottom:8px;">📷</div>
        <div style="font-weight:700;color:#374151;font-size:14px;">Quét mã QR</div>
        <div style="font-size:12px;color:#9ca3af;margin-top:4px;">Nhấn để mở camera và quét</div>`;
    box.onclick = toggleCamera;
}

function handleQrUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const resultEl  = document.getElementById('uploadResult');
    const uploadBox = document.getElementById('uploadBox');

    uploadBox.innerHTML = `<div style="font-size:20px;">⏳</div><div style="font-size:13px;color:#9ca3af;margin-top:6px;">Đang đọc mã QR...</div>`;
    resultEl.style.display = 'none';

    const fileReader = new FileReader();

    fileReader.onerror = function() {
        resetUploadBox();
        showUploadError('Không thể đọc file ảnh. Vui lòng thử lại.');
        event.target.value = '';
    };

    fileReader.onload = function(e) {
        const img = new Image();

        img.onerror = function() {
            resetUploadBox();
            showUploadError('Không thể tải ảnh. Vui lòng thử lại.');
            event.target.value = '';
        };

        img.onload = function() {
            const canvas = document.createElement('canvas');
            const ctx    = canvas.getContext('2d');
            const maxSize = 800;
            let w = img.naturalWidth, h = img.naturalHeight;
            if (w > maxSize || h > maxSize) {
                const scale = Math.min(maxSize / w, maxSize / h);
                w = Math.floor(w * scale);
                h = Math.floor(h * scale);
            }
            canvas.width = w; canvas.height = h;
            ctx.drawImage(img, 0, 0, w, h);

            const qrReader = new ZXing.BrowserQRCodeReader();
            const resizedImg = new Image();
            resizedImg.onload = function() {
                qrReader.decodeFromImage(resizedImg)
                    .then(function(result) {
                        const qrUrl = result.getText();
                        resetUploadBox();
                        if (qrUrl.includes('/money-wallets/qr/scan/') || qrUrl.includes('/qr/')) {
                            resultEl.style.display = 'block';
                            resultEl.innerHTML = `
                            <div style="background:#d1fae5;border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:10px;">
                                <span style="font-size:20px;">✅</span>
                                <div>
                                    <div style="font-size:13px;font-weight:700;color:#065f46;">Đọc QR thành công!</div>
                                    <div style="font-size:12px;color:#047857;margin-top:2px;">Đang chuyển hướng...</div>
                                </div>
                            </div>`;
                            setTimeout(() => window.location.href = qrUrl, 800);
                        } else {
                            showUploadError('QR code không phải của hệ thống này.');
                        }
                    })
                    .catch(function() {
                        resetUploadBox();
                        showUploadError('Không tìm thấy mã QR trong ảnh. Hãy thử ảnh rõ hơn hoặc dùng camera.');
                    });
            };
            resizedImg.src = canvas.toDataURL('image/jpeg', 0.92);
            event.target.value = '';
        };

        img.src = e.target.result;
    };

    fileReader.readAsDataURL(file);
}

function showUploadError(msg) {
    const resultEl = document.getElementById('uploadResult');
    resultEl.style.display = 'block';
    resultEl.innerHTML = `
    <div style="background:#fee2e2;border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:10px;">
        <span style="font-size:20px;">❌</span>
        <div style="font-size:13px;font-weight:600;color:#991b1b;">${msg}</div>
    </div>`;
}

function resetUploadBox() {
    document.getElementById('uploadBox').innerHTML = `
    <div style="font-size:32px;margin-bottom:8px;">🖼️</div>
    <div style="font-weight:700;color:#374151;font-size:14px;">Upload ảnh QR</div>
    <div style="font-size:12px;color:#9ca3af;margin-top:4px;">Nhấn để chọn ảnh chứa mã QR</div>`;
}

loadWallets();
loadHistory();
</script>
@endsection
