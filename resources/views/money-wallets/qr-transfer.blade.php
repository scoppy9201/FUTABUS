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
.section-card {
    background: white; border-radius: var(--radius);
    box-shadow: var(--shadow); overflow: hidden;
}
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
.alert {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px; border-radius: var(--radius-sm);
    font-size: 14px; font-weight: 500; margin-bottom: 20px;
}
.alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
.alert-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--danger); }

/* Scan via camera */
.scan-box {
    border: 2px dashed #d1d5db; border-radius: var(--radius);
    padding: 20px; text-align: center; margin-top: 20px; cursor: pointer; transition: all .2s;
}
.scan-box:hover { border-color: var(--primary); background: rgba(74,144,226,.03); }
body.dark .scan-box { border-color: rgba(255,255,255,.12); }
#video { width: 100%; border-radius: 10px; display: none; margin-top: 12px; }

/* History */
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
.history-amount { font-size: 17px; font-weight: 900; }
.badge {
    display: inline-flex; align-items: center; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
}
.badge-pending   { background: #fef3c7; color: #92400e; }
.badge-completed { background: #d1fae5; color: #065f46; }
.badge-cancelled { background: #f3f4f6; color: #6b7280; }
.badge-expired   { background: #fee2e2; color: #991b1b; }
.cancel-form { display: inline; }
.btn-cancel-sm {
    padding: 4px 12px; border-radius: 8px;
    background: #fee2e2; border: none; color: var(--danger);
    font-size: 12px; font-weight: 700; cursor: pointer; transition: background .2s;
}
.btn-cancel-sm:hover { background: #fecaca; }
.empty-history { text-align: center; padding: 60px 20px; color: #9ca3af; }
.empty-history .ei { font-size: 48px; margin-bottom: 12px; }
@media (max-width: 900px) { .qr-layout { grid-template-columns: 1fr; } }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('money-wallets.index') }}" style="text-decoration:none;font-size:22px;color:#9ca3af;">←</a>
        <h1 style="font-size:22px;font-weight:800;color:#1f2937;margin:0;">Chuyển tiền QR</h1>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">⚠ {{ session('error') }}</div>
@endif

<div class="qr-layout">

    {{-- ── Cột trái: Tạo QR ── --}}
    <div>
        <div class="section-card">
            <div class="sc-header">Tạo mã QR chuyển tiền</div>
            <div class="sc-body">
                @if($wallets->isEmpty())
                    <div style="text-align:center;padding:30px;color:#9ca3af;">
                        Bạn chưa có ví nào. <a href="{{ route('money-wallets.index') }}">Tạo ví ngay</a>
                    </div>
                @else
                <form action="{{ route('money-wallets.qr.generate') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Ví nguồn <span class="required">*</span></label>
                        <select name="wallet_id" class="form-ctrl" required>
                            <option value="">-- Chọn ví --</option>
                            @foreach($wallets as $w)
                            <option value="{{ $w->id }}" {{ old('wallet_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->bieu_tuong }} {{ $w->ten_vi }} — {{ number_format($w->so_du) }}đ
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Số tiền <span class="required">*</span></label>
                        <input type="number" name="so_tien" class="form-ctrl"
                               placeholder="Nhập số tiền..." min="1000" step="1000"
                               value="{{ old('so_tien') }}" required>
                        <div style="font-size:11px;color:#9ca3af;margin-top:4px;">Tối thiểu 1.000đ</div>
                    </div>
                    <div class="form-group" style="margin-bottom:24px;">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="ghi_chu" class="form-ctrl"
                               placeholder="Nội dung chuyển tiền..." maxlength="255"
                               value="{{ old('ghi_chu') }}">
                    </div>
                    <button type="submit" class="btn-primary">Tạo mã QR</button>
                </form>

                {{-- Scan bằng camera --}}
                <div class="scan-box" id="scanToggle" onclick="toggleCamera()">
                    <div style="font-size:32px;margin-bottom:8px;">📷</div>
                    <div style="font-weight:700;color:#374151;font-size:14px;">Quét mã QR</div>
                    <div style="font-size:12px;color:#9ca3af;margin-top:4px;">Nhấn để mở camera và quét</div>
                </div>
                <video id="video" autoplay playsinline></video>
                <div id="scanError" style="color:var(--danger);font-size:13px;margin-top:8px;display:none;"></div>

                {{-- Upload ảnh QR --}}
                <div class="scan-box" id="uploadBox" style="margin-top:12px;"
                     onclick="document.getElementById('qrFileInput').click()">
                    <div style="font-size:32px;margin-bottom:8px;">🖼️</div>
                    <div style="font-weight:700;color:#374151;font-size:14px;">Upload ảnh QR</div>
                    <div style="font-size:12px;color:#9ca3af;margin-top:4px;">Nhấn để chọn ảnh chứa mã QR</div>
                </div>
                <input type="file" id="qrFileInput" accept="image/*" style="display:none" onchange="handleQrUpload(event)">
                <div id="uploadResult" style="margin-top:10px;display:none;"></div>
                @endif
            </div>
        </div>

        {{-- Hướng dẫn --}}
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

    {{-- ── Cột phải: Lịch sử ── --}}
    <div class="section-card">
        <div class="sc-header">Lịch sử QR Transfer</div>
        <div class="sc-body">
            @if($history->isEmpty())
            <div class="empty-history">
                <div class="ei">📭</div>
                <div style="font-weight:700;font-size:15px;color:#374151;margin-bottom:6px;">Chưa có giao dịch QR</div>
                <div style="font-size:13px;">Tạo mã QR đầu tiên để chuyển tiền</div>
            </div>
            @else
            <div class="history-list">
                @foreach($history as $t)
                @php
                    $isSender   = $t->sender_id === Auth::id();
                    $statusClass= $t->trang_thai === 'completed' ? ($isSender ? 'sent' : 'received')
                                : ($t->trang_thai === 'pending' ? 'pending' : 'sent');
                    $icon       = $t->trang_thai === 'completed'
                                ? ($isSender ? '📤' : '📥')
                                : ($t->trang_thai === 'pending' ? '⏳' : '❌');
                @endphp
                <div class="history-item {{ $statusClass }}">
                    <div class="history-icon {{ $statusClass }}">{{ $icon }}</div>
                    <div class="history-info">
                        <div class="history-title">
                            @if($t->trang_thai === 'pending')
                                Đang chờ nhận
                                @if($isSender)
                                <a href="{{ route('money-wallets.qr.scan-page', $t->qr_token) }}"
                                   style="font-size:11px;color:var(--primary);margin-left:6px;">
                                   🔗 Link
                                </a>
                                @endif
                            @elseif($t->trang_thai === 'completed')
                                {{ $isSender ? '→ ' . $t->receiver->name : '← ' . $t->sender->name }}
                            @else
                                {{ ucfirst($t->trang_thai) }}
                            @endif
                        </div>
                        <div class="history-sub">
                            {{ $t->created_at->format('d/m/Y H:i') }}
                            @if($t->ghi_chu) · {{ Str::limit($t->ghi_chu, 40) }} @endif
                        </div>
                        <div class="history-sub">
                            {{ $isSender ? $t->senderWallet?->ten_vi : $t->receiverWallet?->ten_vi }}
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div class="history-amount" style="color:{{ $t->trang_thai === 'completed' && !$isSender ? '#10b981' : ($t->trang_thai === 'completed' ? '#ef4444' : '#f59e0b') }}">
                            {{ $isSender ? '-' : '+' }}{{ number_format($t->so_tien) }}đ
                        </div>
                        <div style="margin-top:6px;">
                            <span class="badge badge-{{ $t->trang_thai }}">
                                {{ ['pending'=>'Chờ','completed'=>'Hoàn thành','cancelled'=>'Đã huỷ','expired'=>'Hết hạn'][$t->trang_thai] ?? $t->trang_thai }}
                            </span>
                        </div>
                        @if($t->trang_thai === 'pending' && $isSender)
                        <form class="cancel-form" action="{{ route('money-wallets.qr.cancel', $t) }}" method="POST"
                              onsubmit="return confirm('Huỷ QR này?')">
                            @csrf
                            <button type="submit" class="btn-cancel-sm" style="margin-top:6px;">Huỷ</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<script src="https://unpkg.com/@zxing/library@0.18.6/umd/index.min.js"></script>
<script>
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

// ── Upload ảnh QR và decode ──────────────────────────
function handleQrUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const resultEl  = document.getElementById('uploadResult');
    const uploadBox = document.getElementById('uploadBox');

    // Hiện loading
    uploadBox.innerHTML = `<div style="font-size:20px;">⏳</div><div style="font-size:13px;color:#9ca3af;margin-top:6px;">Đang đọc mã QR...</div>`;
    resultEl.style.display = 'none';

    // FIX: Dùng FileReader → DataURL thay vì blob URL (ổn định hơn)
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
            // FIX: Resize ảnh xuống max 800px trước khi decode
            // → ZXing đọc ảnh lớn rất dễ fail
            const canvas = document.createElement('canvas');
            const ctx    = canvas.getContext('2d');

            const maxSize = 800;
            let w = img.naturalWidth;
            let h = img.naturalHeight;

            if (w > maxSize || h > maxSize) {
                const scale = Math.min(maxSize / w, maxSize / h);
                w = Math.floor(w * scale);
                h = Math.floor(h * scale);
            }

            canvas.width  = w;
            canvas.height = h;
            ctx.drawImage(img, 0, 0, w, h);

            // FIX: Dùng decodeFromImage (API chuẩn của ZXing) thay vì
            // tự build RGBLuminanceSource + BinaryBitmap thủ công (rất dễ lỗi)
            const qrReader = new ZXing.BrowserQRCodeReader();

            // Tạo image element từ canvas để truyền vào decodeFromImage
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
                            showUploadError('QR code không phải của hệ thống Monexa.');
                        }
                    })
                    .catch(function(err) {
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
</script>
@endsection
