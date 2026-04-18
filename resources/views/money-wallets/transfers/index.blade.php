@extends('layouts.app')
@section('title', 'Chuyển tiền giữa ví')
@section('content')
<style>
:root {
    --primary:#4a90e2;--primary-dark:#2a5298;
    --success:#10b981;--danger:#ef4444;--warning:#f59e0b;
    --radius:16px;--radius-sm:10px;
    --shadow:0 2px 12px rgba(0,0,0,0.06);
    --transition:all 0.25s cubic-bezier(0.4,0,0.2,1);
}

.page-hdr { display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;padding:20px 26px;background:white;border-radius:var(--radius);box-shadow:var(--shadow); }
body.dark .page-hdr { background:#191d27; }
.page-title { font-size:20px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:10px; }
body.dark .page-title { color:#e5e7eb; }

.alert { display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:var(--radius-sm);font-size:14px;font-weight:500;margin-bottom:20px; }
.alert-success { background:#d1fae5;color:#065f46;border-left:4px solid var(--success); }
.alert-error   { background:#fee2e2;color:#991b1b;border-left:4px solid var(--danger); }

.main-grid { display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start; }

.form-card { background:white;border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden; }
body.dark .form-card { background:#191d27; }
.fc-hdr { padding:18px 22px;border-bottom:1px solid #f3f4f6;background:linear-gradient(135deg,rgba(74,144,226,0.06),transparent); }
body.dark .fc-hdr { border-color:rgba(255,255,255,0.06); }
.fc-title { font-size:15px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:8px; }
body.dark .fc-title { color:#e5e7eb; }
.fc-body { padding:22px; }

.form-group { margin-bottom:16px; }
.form-label { font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:7px; }
body.dark .form-label { color:#9ca3af; }
.required { color:var(--danger); }
.form-ctrl { width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:var(--radius-sm);font-size:14px;background:#f9fafb;color:#1f2937;outline:none;transition:all .2s; }
.form-ctrl:focus { border-color:var(--primary);background:white; }
body.dark .form-ctrl { background:#141820;border-color:rgba(255,255,255,0.1);color:#e5e7eb; }

.wallet-select-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:6px; }
.ws-card {
    border:2px solid #e5e7eb;border-radius:12px;padding:12px 14px;
    cursor:pointer;transition:var(--transition);display:flex;align-items:center;gap:10px;
}
.ws-card:hover { border-color:var(--primary); }
.ws-card.selected { border-color:var(--primary);background:rgba(74,144,226,0.05); }
.ws-card.disabled-card { opacity:.4;pointer-events:none; }
body.dark .ws-card { border-color:rgba(255,255,255,0.1); }
body.dark .ws-card.selected { background:rgba(74,144,226,0.1); }
.ws-icon { font-size:22px;flex-shrink:0; }
.ws-name { font-size:13px;font-weight:700;color:#1f2937; }
body.dark .ws-name { color:#e5e7eb; }
.ws-balance { font-size:11px;font-weight:600;color:#9ca3af; }

.transfer-preview {
    background:linear-gradient(135deg,rgba(74,144,226,0.06),rgba(42,82,152,0.04));
    border:1px solid rgba(74,144,226,0.2);border-radius:12px;
    padding:16px;margin-bottom:16px;display:flex;align-items:center;
    gap:12px;min-height:60px;
}
.preview-from,.preview-to { text-align:center;flex:1;min-width:0; }
.preview-wallet-name { font-size:13px;font-weight:700;color:#1f2937; }
body.dark .preview-wallet-name { color:#e5e7eb; }
.preview-wallet-bal { font-size:11px;color:#9ca3af; }
.preview-arrow { font-size:28px;color:var(--primary);flex-shrink:0; }
.preview-amount { font-size:14px;font-weight:900;color:var(--primary); }
.preview-placeholder { font-size:13px;color:#9ca3af;text-align:center;width:100%; }

.section-card { background:white;border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden; }
body.dark .section-card { background:#191d27; }
.sc-hdr { padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center; }
body.dark .sc-hdr { border-color:rgba(255,255,255,0.06); }
.sc-title { font-size:15px;font-weight:800;color:#1f2937; }
body.dark .sc-title { color:#e5e7eb; }

.tf-item { display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid #f9fafb;transition:background .15s; }
body.dark .tf-item { border-color:rgba(255,255,255,0.03); }
.tf-item:last-child { border-bottom:none; }
.tf-item:hover { background:#f9fafb; }
body.dark .tf-item:hover { background:rgba(255,255,255,0.02); }
.tf-icon { width:40px;height:40px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
.tf-info { flex:1;min-width:0; }
.tf-route { font-size:14px;font-weight:700;color:#1f2937; }
body.dark .tf-route { color:#e5e7eb; }
.tf-meta { font-size:12px;color:#9ca3af;margin-top:2px; }
.tf-right { text-align:right;flex-shrink:0; }
.tf-amount { font-size:16px;font-weight:900;color:var(--primary); }
.tf-date { font-size:11px;color:#9ca3af;margin-top:2px; }
.tf-cancel-btn {
    padding:4px 10px;border-radius:8px;border:1px solid #e5e7eb;
    background:white;color:#9ca3af;font-size:11px;font-weight:600;cursor:pointer;
    transition:all .15s;margin-top:4px;
}
.tf-cancel-btn:hover { border-color:var(--danger);color:var(--danger);background:#fee2e2; }

.empty-msg { text-align:center;padding:40px;color:#9ca3af;font-size:13px; }

.btn-primary { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-sm);background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;transition:opacity .2s; }
.btn-primary:hover { opacity:.88; }
.btn-outline { display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:var(--radius-sm);border:2px solid var(--primary);color:var(--primary);background:transparent;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;transition:all .2s; }
.btn-outline:hover { background:var(--primary);color:white; }

.phi-hint { font-size:12px;color:#9ca3af;margin-top:5px;display:flex;align-items:center;gap:4px; }

.skeleton { background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:8px; }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }

@media (max-width:1100px) { .main-grid { grid-template-columns:1fr; } }
@media (max-width:768px) { .wallet-select-grid { grid-template-columns:1fr; } }
</style>

<div class="page-hdr">
    <div class="page-title">Chuyển tiền giữa ví</div>
    <a href="{{ route('money-wallets.index') }}" class="btn-outline">← Quay lại ví</a>
</div>

<div id="alertContainer"></div>

<div class="main-grid">
    <div>
        <div class="form-card">
            <div class="fc-hdr"><div class="fc-title">Tạo giao dịch chuyển tiền</div></div>
            <div class="fc-body" id="formBody">
                <div class="skeleton" style="height:300px;border-radius:10px;"></div>
            </div>
        </div>
    </div>

    <div>
        <div class="section-card">
            <div class="sc-hdr"><div class="sc-title">Lịch sử chuyển tiền</div></div>
            <div id="historyList"><div class="empty-msg"><div class="skeleton" style="height:80px;margin:8px;border-radius:10px;"></div></div></div>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

let wallets = [];
let selectedFrom = null, selectedTo = null, previewAmount = 0;
let categories = [];

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

function renderForm() {
    if (wallets.length < 2) {
        document.getElementById('formBody').innerHTML = `
        <div style="text-align:center;padding:40px;color:#9ca3af;">
            <div style="font-size:40px;margin-bottom:12px;">💳</div>
            <div style="font-weight:700;margin-bottom:8px;">Cần ít nhất 2 ví</div>
            <div style="font-size:13px;margin-bottom:20px;">Tạo thêm ví để chuyển tiền giữa các nguồn</div>
            <a href="{{ route('money-wallets.index') }}" class="btn-primary">+ Thêm ví</a>
        </div>`;
        return;
    }

    const today = new Date().toISOString().split('T')[0];

    const catOptions = (Array.isArray(categories) ? categories : []).reduce((acc, c) => {
        if (!c.danh_muc_cha_id) return acc;
        const g = c.loai_danh_muc === 'THU' ? 'Thu nhập' : 'Chi tiêu';
        if (!acc[g]) acc[g] = [];
        acc[g].push(c);
        return acc;
    }, {});

    const catHtml = Object.entries(catOptions).map(([label, cats]) =>
        `<optgroup label="${label}">${cats.map(c => `<option value="${c.id}">${c.ten_danh_muc}</option>`).join('')}</optgroup>`
    ).join('');

    document.getElementById('formBody').innerHTML = `
    <div class="form-group">
        <label class="form-label">Từ ví <span class="required">*</span></label>
        <div class="wallet-select-grid" id="fromGrid">
            ${wallets.map(w => `
            <div class="ws-card" data-wallet-id="${w.id}" data-grid="from" onclick="selectFromWallet(${w.id})">
                <div class="ws-icon">${w.bieu_tuong}</div>
                <div>
                    <div class="ws-name">${w.ten_vi}</div>
                    <div class="ws-balance">${fmt(w.so_du)} ${w.don_vi_tien_te}</div>
                </div>
            </div>`).join('')}
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Đến ví <span class="required">*</span></label>
        <div class="wallet-select-grid" id="toGrid">
            ${wallets.map(w => `
            <div class="ws-card" data-wallet-id="${w.id}" data-grid="to" onclick="selectToWallet(${w.id})">
                <div class="ws-icon">${w.bieu_tuong}</div>
                <div>
                    <div class="ws-name">${w.ten_vi}</div>
                    <div class="ws-balance">${fmt(w.so_du)} ${w.don_vi_tien_te}</div>
                </div>
            </div>`).join('')}
        </div>
    </div>

    <div class="transfer-preview" id="transferPreview">
        <div class="preview-placeholder">Chọn ví nguồn và ví đích để xem trước</div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Số tiền <span class="required">*</span></label>
            <input id="soTien" type="number" class="form-ctrl" placeholder="0" min="1000" step="1000" oninput="updatePreviewAmount(this.value)">
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Phí chuyển</label>
            <input id="phiChuyen" type="number" class="form-ctrl" placeholder="0 (nếu có)" min="0" step="1000" value="0">
            <div class="phi-hint">💡 Phí sẽ bị trừ thêm từ ví nguồn</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px;">
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Ngày <span class="required">*</span></label>
            <input id="ngayChuyen" type="date" class="form-ctrl" value="${today}" max="${today}">
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Danh mục (tùy chọn)</label>
            <select id="categoryId" class="form-ctrl">
                <option value="">-- Không chọn --</option>
                ${catHtml}
            </select>
        </div>
    </div>

        <div class="form-group" style="margin-top:16px;">
            <label class="form-label">Ghi chú</label>
            <input id="ghiChu" class="form-ctrl" placeholder="Lý do chuyển tiền..." maxlength="500">
        </div>

    <button onclick="submitTransfer()" class="btn-primary" style="width:100%;justify-content:center;padding:13px;margin-top:4px;">
        Chuyển tiền
    </button>`;
}

function selectFromWallet(id) {
    if (selectedTo === id) {
        selectedTo = null;
        document.querySelectorAll('#toGrid .ws-card').forEach(c => {
            c.classList.remove('selected', 'disabled-card');
        });
    }
    document.querySelectorAll('#fromGrid .ws-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`#fromGrid [data-wallet-id="${id}"]`)?.classList.add('selected');
    selectedFrom = id;
    syncDisabled('toGrid', id);
    updatePreview();
}

function selectToWallet(id) {
    if (selectedFrom === id) {
        selectedFrom = null;
        document.querySelectorAll('#fromGrid .ws-card').forEach(c => {
            c.classList.remove('selected', 'disabled-card');
        });
    }
    document.querySelectorAll('#toGrid .ws-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`#toGrid [data-wallet-id="${id}"]`)?.classList.add('selected');
    selectedTo = id;
    syncDisabled('fromGrid', id);
    updatePreview();
}

function syncDisabled(gridId, disableId) {
    document.querySelectorAll(`#${gridId} .ws-card`).forEach(c => {
        const id = parseInt(c.dataset.walletId);
        c.classList.toggle('disabled-card', id === disableId);
    });
}

function updatePreviewAmount(val) {
    previewAmount = parseFloat(val) || 0;
    updatePreview();
}

function updatePreview() {
    const preview = document.getElementById('transferPreview');
    if (!preview) return;
    const from = wallets.find(w => w.id === selectedFrom);
    const to   = wallets.find(w => w.id === selectedTo);

    if (!from && !to) {
        preview.innerHTML = '<div class="preview-placeholder">Chọn ví nguồn và ví đích để xem trước</div>';
        return;
    }

    const fromHtml = from
        ? `<div class="preview-from"><div style="font-size:28px;">${from.bieu_tuong}</div><div class="preview-wallet-name">${from.ten_vi}</div><div class="preview-wallet-bal">${fmt(from.so_du)}đ</div></div>`
        : `<div class="preview-from" style="opacity:.4;text-align:center;font-size:13px;color:#9ca3af;">Chọn ví nguồn</div>`;

    const toHtml = to
        ? `<div class="preview-to"><div style="font-size:28px;">${to.bieu_tuong}</div><div class="preview-wallet-name">${to.ten_vi}</div><div class="preview-wallet-bal">${fmt(to.so_du)}đ</div></div>`
        : `<div class="preview-to" style="opacity:.4;text-align:center;font-size:13px;color:#9ca3af;">Chọn ví đích</div>`;

    const amountHtml = previewAmount > 0
        ? `<div style="text-align:center;flex-shrink:0;"><div class="preview-arrow">→</div><div class="preview-amount">${fmt(previewAmount)}đ</div></div>`
        : `<div class="preview-arrow" style="flex-shrink:0;">→</div>`;

    preview.innerHTML = fromHtml + amountHtml + toHtml;
}

async function submitTransfer() {
    if (!selectedFrom || !selectedTo) { showAlert('Vui lòng chọn ví nguồn và ví đích', 'error'); return; }
    const body = {
        from_wallet_id: selectedFrom,
        to_wallet_id:   selectedTo,
        so_tien:        parseFloat(document.getElementById('soTien').value),
        phi_chuyen:     parseFloat(document.getElementById('phiChuyen').value) || 0,
        ngay_chuyen:    document.getElementById('ngayChuyen').value,
        category_id:    document.getElementById('categoryId').value,
        ghi_chu:        document.getElementById('ghiChu').value.trim(),
    };
    if (!body.so_tien || body.so_tien < 1000) { showAlert('Số tiền tối thiểu là 1.000đ', 'error'); return; }
    if (!body.ngay_chuyen) { showAlert('Vui lòng chọn ngày', 'error'); return; }

    const res = await apiFetch('/api/v1/wallet-transfers', { method: 'POST', body: JSON.stringify(body) });
    if (res.id) {
        showAlert('Chuyển tiền thành công');
        selectedFrom = null; selectedTo = null; previewAmount = 0;
        loadPage();
    } else {
        showAlert(res.message || 'Có lỗi xảy ra', 'error');
    }
}

async function cancelTransfer(id) {
    if (!confirm('Hoàn tác giao dịch chuyển tiền này?')) return;
    const res = await apiFetch(`/api/v1/wallet-transfers/${id}`, { method: 'DELETE' });
    if (res.success) { showAlert('Đã hoàn tác giao dịch'); loadHistory(); }
    else showAlert(res.message || 'Có lỗi xảy ra', 'error');
}

async function loadHistory() {
    const data = await apiFetch('/api/v1/wallet-transfers');
    const el = document.getElementById('historyList');
    if (!data.data?.length && !data.length) {
        el.innerHTML = '<div class="empty-msg">Chưa có giao dịch chuyển tiền nào</div>';
        return;
    }
    const items = data.data || data;
    el.innerHTML = items.map(tf => {
        const date = new Date(tf.ngay_chuyen).toLocaleDateString('vi-VN');
        const ago  = tf.created_at_human || '';
        return `
        <div class="tf-item">
            <div class="tf-icon">↔️</div>
            <div class="tf-info">
                <div class="tf-route">
                    ${tf.from_wallet?.bieu_tuong || ''} ${tf.from_wallet?.ten_vi || '?'}
                    → ${tf.to_wallet?.bieu_tuong || ''} ${tf.to_wallet?.ten_vi || '?'}
                </div>
                <div class="tf-meta">
                    ${date}
                    ${tf.phi_chuyen > 0 ? ` · Phí: ${fmt(tf.phi_chuyen)}đ` : ''}
                    ${tf.ghi_chu ? ` · ${tf.ghi_chu.substring(0,30)}` : ''}
                </div>
                <button class="tf-cancel-btn" onclick="cancelTransfer(${tf.id})">↩ Hoàn tác</button>
            </div>
            <div class="tf-right">
                <div class="tf-amount">${fmt(tf.so_tien)}đ</div>
                ${ago ? `<div class="tf-date">${ago}</div>` : ''}
            </div>
        </div>`;
    }).join('');
}

async function loadPage() {
    const [walletRes, catRes] = await Promise.all([
        apiFetch('/api/v1/money-wallets'),
        apiFetch('/api/v1/categories'),
    ]);
    const _walletList = Array.isArray(walletRes) ? walletRes : (walletRes.data ?? []);
    wallets    = _walletList.filter(w => w.trang_thai !== 'khong_hoat_dong');
    categories = catRes.categories?.data ?? (Array.isArray(catRes) ? catRes : (catRes.data ?? []));
    renderForm();
    loadHistory();
}

loadPage();
</script>
@endsection
