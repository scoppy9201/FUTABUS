@extends('layouts.app')
@section('title', 'Cài đặt')
@section('content')

<style>
    .settings-container { max-width: 800px; margin: 0 auto; }

    .page-header {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 24px; padding: 20px;
        background: white; border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    body.dark .page-header { background: #191d27; }

    .page-icon {
        width: 40px; height: 40px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-radius: 10px; display: flex;
        align-items: center; justify-content: center; padding: 10px;
    }

    .page-icon img { width: 100%; }

    .page-title { font-size: 24px; font-weight: 700; color: #1f2937; }
    body.dark .page-title { color: #e5e7eb; }

    .settings-section {
        background: white; border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        margin-bottom: 20px; overflow: hidden;
    }

    body.dark .settings-section { background: #191d27; }

    .section-header {
        padding: 18px 24px; border-bottom: 1px solid #f3f4f6;
        display: flex; align-items: center; gap: 10px;
    }

    body.dark .section-header { border-color: rgba(255,255,255,0.06); }

    .section-title { font-size: 16px; font-weight: 700; color: #1f2937; }
    body.dark .section-title { color: #e5e7eb; }

    .section-icon {
        width: 32px; height: 32px; border-radius: 8px;
        background: linear-gradient(135deg, rgba(74,144,226,0.15), rgba(74,144,226,0.05));
        display: flex; align-items: center; justify-content: center; padding: 7px;
    }

    .section-icon img { width: 100%; }

    .setting-row {
        padding: 18px 24px; border-bottom: 1px solid #f9fafb;
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px; transition: background 0.2s;
    }

    .setting-row:last-child { border-bottom: none; }
    .setting-row:hover { background: #fafafa; }
    body.dark .setting-row:hover { background: rgba(255,255,255,0.02); }
    body.dark .setting-row { border-color: rgba(255,255,255,0.04); }

    .setting-info { flex: 1; }
    .setting-label { font-size: 14px; font-weight: 600; color: #1f2937; margin-bottom: 3px; }
    body.dark .setting-label { color: #e5e7eb; }
    .setting-desc { font-size: 12px; color: #9ca3af; font-weight: 500; }

    .toggle-switch { position: relative; width: 48px; height: 26px; flex-shrink: 0; }
    .toggle-switch input { display: none; }
    .toggle-slider {
        position: absolute; inset: 0; background: #e5e7eb;
        border-radius: 13px; cursor: pointer; transition: background 0.3s;
    }
    .toggle-slider::before {
        content: ''; position: absolute; width: 20px; height: 20px;
        border-radius: 50%; background: white; top: 3px; left: 3px;
        transition: transform 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .toggle-switch input:checked + .toggle-slider { background: var(--primary); }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(22px); }

    .setting-select {
        padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 8px;
        font-size: 13px; font-weight: 600; background: #f9fafb; color: #1f2937;
        cursor: pointer; min-width: 140px; outline: none; transition: border-color 0.2s;
    }
    .setting-select:focus { border-color: var(--primary); background: white; }
    body.dark .setting-select { background: #1a1f29; border-color: rgba(255,255,255,0.08); color: #e5e7eb; }

    .setting-range { width: 140px; accent-color: var(--primary); }
    .range-value { font-size: 13px; font-weight: 700; color: var(--primary); min-width: 36px; text-align: right; }
    .range-wrap { display: flex; align-items: center; gap: 10px; }

    .btn-preview {
        padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
        border: 2px solid var(--primary); color: var(--primary);
        background: transparent; cursor: pointer; transition: all 0.2s; white-space: nowrap;
    }
    .btn-preview:hover { background: var(--primary); color: white; }

    .pos-btn {
        padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;
        border: 2px solid #e5e7eb; color: #6b7280; background: #f9fafb;
        cursor: pointer; transition: all 0.2s;
    }
    .pos-btn.active { border-color: var(--primary); color: var(--primary); background: rgba(74,144,226,0.08); }
    body.dark .pos-btn { background: #1a1f29; border-color: rgba(255,255,255,0.08); color: #9ca3af; }
    body.dark .pos-btn.active { border-color: var(--primary); color: var(--primary); }
    .position-options { display: flex; gap: 8px; }

    .save-bar {
        position: fixed; bottom: 24px; left: 50%;
        background: white; border-radius: 14px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        padding: 14px 24px; display: flex; align-items: center;
        gap: 16px; z-index: 1000; border: 1px solid #e5e7eb;
        opacity: 0; visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        transform: translateX(-50%) translateY(20px);
    }
    .save-bar.show { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
    body.dark .save-bar { background: #191d27; border-color: rgba(255,255,255,0.08); }
    .save-bar-text { font-size: 14px; font-weight: 600; color: #374151; }
    body.dark .save-bar-text { color: #e5e7eb; }

    .btn-save {
        padding: 10px 24px; border-radius: 10px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white; font-size: 14px; font-weight: 700;
        border: none; cursor: pointer; transition: opacity 0.2s;
    }
    .btn-save:hover { opacity: 0.9; }

    .btn-discard {
        padding: 10px 16px; border-radius: 10px; background: #f3f4f6;
        color: #6b7280; font-size: 14px; font-weight: 600;
        border: none; cursor: pointer; transition: background 0.2s;
    }
    .btn-discard:hover { background: #e5e7eb; }

    /* Email form */
    .email-alert {
        margin: 12px 24px; padding: 12px 16px;
        border-radius: 10px; font-size: 14px; display: none;
    }
    .email-alert.success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
    .email-alert.error   { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
</style>

<div class="settings-container">
    <!-- Header -->
    <div class="page-header">
        <div class="page-icon">
            <img src="{{ asset('images/settings.png') }}" alt="Settings">
        </div>
        <span class="page-title">Cài đặt hệ thống</span>
    </div>

    <!-- Toast Notification Settings -->
    <div class="settings-section">
        <div class="section-header">
            <div class="section-icon">
                <img src="{{ asset('images/bell.png') }}" alt="Notification">
            </div>
            <span class="section-title">Thông báo Toast</span>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Bật thông báo Toast</div>
                <div class="setting-desc">Hiện thông báo dạng popup góc màn hình</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="toast-enabled" checked>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Vị trí hiển thị</div>
                <div class="setting-desc">Chọn góc màn hình để hiện thông báo</div>
            </div>
            <div class="position-options">
                <button class="pos-btn" data-pos="top-left">Trên trái</button>
                <button class="pos-btn active" data-pos="top-right">Trên phải</button>
                <button class="pos-btn" data-pos="bottom-left">Dưới trái</button>
                <button class="pos-btn" data-pos="bottom-right">Dưới phải</button>
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Thời gian tự đóng</div>
                <div class="setting-desc">Thông báo tự động đóng sau bao nhiêu giây</div>
            </div>
            <div class="range-wrap">
                <input type="range" class="setting-range" id="toast-duration" min="2" max="15" step="1" value="5">
                <span class="range-value" id="duration-label">5s</span>
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Âm thanh thông báo</div>
                <div class="setting-desc">Phát âm thanh nhỏ khi có thông báo mới</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="toast-sound">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Xem thử thông báo</div>
                <div class="setting-desc">Nhấn nút để xem trước các loại thông báo</div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="btn-preview" onclick="previewToast('success')" style="display:flex;align-items:center;gap:6px;">
                    <img src="{{ asset('images/check.png') }}" style="width:16px;height:16px;object-fit:contain;"> Success
                </button>
                <button class="btn-preview" onclick="previewToast('error')" style="border-color:#ef4444;color:#ef4444;display:flex;align-items:center;gap:6px;" onmouseover="this.style.background='#ef4444';this.style.color='white'" onmouseout="this.style.background='';this.style.color='#ef4444'">
                    <img src="{{ asset('images/warning.png') }}" style="width:16px;height:16px;object-fit:contain;"> Error
                </button>
                <button class="btn-preview" onclick="previewToast('warning')" style="border-color:#f59e0b;color:#f59e0b;display:flex;align-items:center;gap:6px;" onmouseover="this.style.background='#f59e0b';this.style.color='white'" onmouseout="this.style.background='';this.style.color='#f59e0b'">
                    <img src="{{ asset('images/alert.png') }}" style="width:16px;height:16px;object-fit:contain;"> Warning
                </button>
                <button class="btn-preview" onclick="previewToast('info')" style="border-color:#06b6d4;color:#06b6d4;display:flex;align-items:center;gap:6px;" onmouseover="this.style.background='#06b6d4';this.style.color='white'" onmouseout="this.style.background='';this.style.color='#06b6d4'">
                    <img src="{{ asset('images/info.png') }}" style="width:16px;height:16px;object-fit:contain;"> Info
                </button>
            </div>
        </div>
    </div>

    <!-- Giao diện -->
    <div class="settings-section">
        <div class="section-header">
            <div class="section-icon">
                <img src="{{ asset('images/dark-mode.png') }}" alt="Theme">
            </div>
            <span class="section-title">Giao diện</span>
        </div>
        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Chế độ tối</div>
                <div class="setting-desc">Bật giao diện tối cho toàn hệ thống</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="dark-mode-toggle">
                <span class="toggle-slider"></span>
            </label>
        </div>
    </div>

    <!-- Email SMTP -->
    <div class="settings-section">
        <div class="section-header">
            <div class="section-icon">
                <img src="{{ asset('images/envelope.png') }}" alt="Email">
            </div>
            <span class="section-title">Cấu hình Email (SMTP)</span>
        </div>

        <!-- Alert email -->
        <div id="email-alert" class="email-alert"></div>

        <!-- Toggle bật/tắt -->
        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Dùng cấu hình email từ hệ thống</div>
                <div class="setting-desc">Bật để dùng SMTP bên dưới thay cho .env</div>
            </div>
            <label class="toggle-switch" style="cursor:pointer;">
                <input type="checkbox" id="email-is-active">
                <span class="toggle-slider" onclick="handleToggleEmail()"></span>
            </label>
        </div>

        <!-- Form SMTP -->
        <div class="setting-row" style="flex-direction:column;align-items:flex-start;gap:16px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;width:100%;">
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                        SMTP Host <span style="color:#ef4444">*</span>
                    </label>
                    <input type="text" id="mail_host" class="setting-select" style="width:100%;padding:9px 12px;" placeholder="smtp.gmail.com">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                        Port <span style="color:#ef4444">*</span>
                    </label>
                    <select id="mail_port" class="setting-select" style="width:100%;padding:9px 12px;">
                        <option value="587">587 (TLS)</option>
                        <option value="465">465 (SSL)</option>
                        <option value="25">25</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                        Email / Username <span style="color:#ef4444">*</span>
                    </label>
                    <input type="email" id="mail_username" class="setting-select" style="width:100%;padding:9px 12px;" placeholder="your@gmail.com">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                        Password / App Password
                    </label>
                    <input type="password" id="mail_password" class="setting-select" style="width:100%;padding:9px 12px;" placeholder="Nhập mật khẩu ứng dụng">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                        Mã hóa <span style="color:#ef4444">*</span>
                    </label>
                    <select id="mail_encryption" class="setting-select" style="width:100%;padding:9px 12px;">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="starttls">STARTTLS</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                        Tên hiển thị <span style="color:#ef4444">*</span>
                    </label>
                    <input type="text" id="mail_from_name" class="setting-select" style="width:100%;padding:9px 12px;" placeholder="Monexa">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                        Email gửi đi (From Address) <span style="color:#ef4444">*</span>
                    </label>
                    <input type="email" id="mail_from_address" class="setting-select" style="width:100%;padding:9px 12px;" placeholder="noreply@yourdomain.com">
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;width:100%;">
                <button class="btn-save" style="padding:10px 24px; display:flex; align-items:center; gap:8px;" onclick="handleSaveEmail()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Lưu cấu hình
                </button>
            </div>

            <div style="width:100%;padding-top:14px;border-top:1px solid #f3f4f6;display:flex;gap:10px;align-items:flex-end;margin-top:10px;">
                <div style="flex:1;">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                        Gửi email test đến
                    </label>
                    <input type="email" id="test_email" class="setting-select" style="width:100%;padding:9px 12px;"
                        placeholder="test@example.com" value="{{ Auth::user()->email }}">
                </div>
                <button class="btn-preview" style="white-space:nowrap;height:40px; display:flex; align-items:center; gap:8px;" onclick="handleTestEmail()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    Gửi thử
                </button>
            </div>

            <div style="width:100%;padding:14px;background:rgba(74,144,226,0.06);border-radius:10px;font-size:12px;color:#4b5563;line-height:1.7;margin-top:10px; display:flex; gap:10px;">
                <div style="flex-shrink:0; margin-top:2px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4a90e2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"></path><path d="M10 22h4"></path><path d="M15.09 14c.18-.98.37-1.74 1.41-2.5 1.1-.8 1.5-1.5 1.5-2.5a5 5 0 0 0-10 0c0 1 .4 1.7 1.5 2.5 1.04.76 1.23 1.52 1.41 2.5"></path></svg>
                </div>
                <div>
                    <strong style="color:#1e293b;">Hướng dẫn Gmail:</strong><br>
                    Bật 2FA → <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#4a90e2; font-weight:500;">myaccount.google.com/apppasswords</a>
                    → Tạo App Password → Dùng thay cho mật khẩu Google thường.<br>
                    <strong>Host:</strong> <code style="background:#fff; padding:2px 4px; border-radius:4px;">smtp.gmail.com</code> &nbsp;|&nbsp;
                    <strong>Port:</strong> <code style="background:#fff; padding:2px 4px; border-radius:4px;">587</code> &nbsp;|&nbsp;
                    <strong>Mã hóa:</strong> <code style="background:#fff; padding:2px 4px; border-radius:4px;">TLS</code>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Save Bar (cho UI settings) -->
<div class="save-bar" id="saveBar">
    <span class="save-bar-text" style="display:flex;align-items:center;gap:8px;">
        <img src="{{ asset('images/save.png') }}" style="width:18px;height:18px;object-fit:contain;">
        Bạn có thay đổi chưa lưu
    </span>
    <button class="btn-discard" onclick="discardChanges()">Hủy</button>
    <button class="btn-save" onclick="saveSettings()">Lưu cài đặt</button>
</div>

<script>
(function () {

const API_EMAIL = '/api/v1/settings/email';
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const SETTINGS_KEY = 'monexa_settings';

/* ── HELPERS ── */
function apiCall(method, url, body = null) {
    const opts = {
        method,
        headers: {
            'Accept':       'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        credentials: 'same-origin',
    };
    if (body) opts.body = JSON.stringify(body);
    return fetch(url, opts).then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw data;
        return data;
    });
}

function showEmailAlert(msg, type = 'success') {
    const el = document.getElementById('email-alert');
    el.className = `email-alert ${type}`;
    el.textContent = (type === 'success' ? '✓ ' : '✗ ') + msg;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 5000);
}

/* LOAD EMAIL CONFIG */
async function loadEmailConfig() {
    try {
        const data = await apiCall('GET', API_EMAIL);
        const s = data.setting;
        if (!s) return;

        document.getElementById('mail_host').value         = s.mail_host        ?? 'smtp.gmail.com';
        document.getElementById('mail_port').value         = s.mail_port        ?? '587';
        document.getElementById('mail_username').value     = s.mail_username    ?? '';
        document.getElementById('mail_encryption').value   = s.mail_encryption  ?? 'tls';
        document.getElementById('mail_from_name').value    = s.mail_from_name   ?? 'Monexa';
        document.getElementById('mail_from_address').value = s.mail_from_address ?? '';
        document.getElementById('email-is-active').checked = !!s.is_active;
        document.getElementById('mail_password').placeholder =
            s.mail_username ? '••••••• (giữ nguyên nếu không đổi)' : 'Nhập mật khẩu ứng dụng';
    } catch {
        // không có config → giữ default
    }
}

/* SAVE EMAIL CONFIG */
window.handleSaveEmail = async function () {
    const body = {
        mail_host:         document.getElementById('mail_host').value.trim(),
        mail_port:         parseInt(document.getElementById('mail_port').value),
        mail_username:     document.getElementById('mail_username').value.trim(),
        mail_password:     document.getElementById('mail_password').value || null,
        mail_encryption:   document.getElementById('mail_encryption').value,
        mail_from_name:    document.getElementById('mail_from_name').value.trim(),
        mail_from_address: document.getElementById('mail_from_address').value.trim(),
        is_active:         document.getElementById('email-is-active').checked,
    };

    try {
        const data = await apiCall('PATCH', API_EMAIL, body);
        showEmailAlert(data.message, 'success');
        showToast({ type: 'success', title: 'Đã lưu cấu hình email!' });
        document.getElementById('mail_password').value = '';
        document.getElementById('mail_password').placeholder = '••••••• (giữ nguyên nếu không đổi)';
    } catch (err) {
        const msg = err.message ?? 'Lưu thất bại!';
        showEmailAlert(msg, 'error');
        showToast({ type: 'error', title: msg });
    }
};

/* ── TOGGLE EMAIL ── */
window.handleToggleEmail = async function () {
    try {
        const data = await apiCall('PATCH', `${API_EMAIL}/toggle`);
        document.getElementById('email-is-active').checked = data.is_active;
        showEmailAlert(data.message, 'success');
        showToast({ type: 'success', title: data.message });
    } catch (err) {
        showEmailAlert(err.message ?? 'Có lỗi xảy ra!', 'error');
    }
};

/* TEST EMAIL */
window.handleTestEmail = async function () {
    const testEmail = document.getElementById('test_email').value.trim();
    if (!testEmail) {
        showEmailAlert('Vui lòng nhập email nhận test!', 'error');
        return;
    }

    showToast({ type: 'info', title: 'Đang gửi email test...' });

    try {
        const data = await apiCall('POST', `${API_EMAIL}/test`, { test_email: testEmail });
        showEmailAlert(data.message, 'success');
        showToast({ type: 'success', title: data.message });
    } catch (err) {
        const msg = err.message ?? 'Gửi thất bại!';
        showEmailAlert(msg, 'error');
        showToast({ type: 'error', title: msg });
    }
};

/*UI SETTINGS (localStorage) */
const defaultSettings = {
    toastEnabled:  true,
    toastPosition: 'top-right',
    toastDuration: 5,
    toastSound:    false,
    darkMode:      false,
};

function loadSettings() {
    try { return { ...defaultSettings, ...JSON.parse(localStorage.getItem(SETTINGS_KEY) || '{}') }; }
    catch { return { ...defaultSettings }; }
}

function applyToUI(s) {
    document.getElementById('toast-enabled').checked    = s.toastEnabled;
    document.getElementById('toast-sound').checked      = s.toastSound;
    document.getElementById('toast-duration').value     = s.toastDuration;
    document.getElementById('duration-label').textContent = s.toastDuration + 's';
    document.getElementById('dark-mode-toggle').checked = s.darkMode;
    document.querySelectorAll('.pos-btn').forEach(b =>
        b.classList.toggle('active', b.dataset.pos === s.toastPosition)
    );
}

let currentSettings = loadSettings();
let hasChanges = false;
applyToUI(currentSettings);

// Sync dark mode
const darkToggle = document.getElementById('dark-mode-toggle');
const _s = JSON.parse(localStorage.getItem('monexa_settings') || '{}');
darkToggle.checked = _s.darkMode ?? document.body.classList.contains('dark');

darkToggle.addEventListener('change', () => {
    document.body.classList.toggle('dark', darkToggle.checked);
    localStorage.setItem('theme', darkToggle.checked ? 'dark' : 'light');
    markChanged();
});

new MutationObserver(mutations => {
    mutations.forEach(m => {
        if (m.attributeName !== 'class') return;
        const isDark = document.body.classList.contains('dark');
        if (darkToggle.checked !== isDark) darkToggle.checked = isDark;
    });
}).observe(document.body, { attributes: true });

// Duration
document.getElementById('toast-duration').addEventListener('input', function () {
    document.getElementById('duration-label').textContent = this.value + 's';
    markChanged();
});

// Position
document.querySelectorAll('.pos-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.pos-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const c = document.getElementById('toastContainer');
        if (c) {
            const pos = btn.dataset.pos;
            c.style.top    = pos.includes('bottom') ? 'auto' : '84px';
            c.style.bottom = pos.includes('bottom') ? '20px' : 'auto';
            c.style.left   = pos.includes('left')   ? '20px' : 'auto';
            c.style.right  = pos.includes('left')   ? 'auto' : '20px';
        }
        markChanged();
    });
});

['toast-enabled', 'toast-sound'].forEach(id =>
    document.getElementById(id)?.addEventListener('change', markChanged)
);

function markChanged() {
    hasChanges = true;
    document.getElementById('saveBar').classList.add('show');
}

window.saveSettings = function () {
    const pos = document.querySelector('.pos-btn.active')?.dataset.pos || 'top-right';
    const newSettings = {
        toastEnabled:  document.getElementById('toast-enabled').checked,
        toastPosition: pos,
        toastDuration: parseInt(document.getElementById('toast-duration').value),
        toastSound:    document.getElementById('toast-sound').checked,
        darkMode:      document.getElementById('dark-mode-toggle').checked,
    };
    localStorage.setItem(SETTINGS_KEY, JSON.stringify(newSettings));
    currentSettings = newSettings;
    hasChanges = false;
    document.getElementById('saveBar').classList.remove('show');
    showToast({ type: 'success', title: 'Đã lưu cài đặt', message: 'Thay đổi của bạn đã được áp dụng.' });
};

window.discardChanges = function () {
    applyToUI(currentSettings);
    document.body.classList.toggle('dark', currentSettings.darkMode);
    localStorage.setItem('theme', currentSettings.darkMode ? 'dark' : 'light');
    hasChanges = false;
    document.getElementById('saveBar').classList.remove('show');
};

window.previewToast = function (type) {
    const msgs = { success: 'Thành công!', error: 'Lỗi!', warning: 'Cảnh báo!', info: 'Thông báo' };
    const dur = parseInt(document.getElementById('toast-duration').value) * 1000;
    showToast({ type, title: msgs[type], duration: dur, id: 'preview_' + Date.now() });
};

/* INIT */
loadEmailConfig();

})();
</script>
@endsection
