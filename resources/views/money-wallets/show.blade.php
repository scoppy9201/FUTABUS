@extends('layouts.app')
@section('title', 'Chi tiết ví')
@section('content')
<style>
:root {
    --primary:#4a90e2;--primary-dark:#2a5298;
    --success:#10b981;--danger:#ef4444;--warning:#f59e0b;
    --radius:16px;--radius-sm:10px;
    --shadow:0 2px 12px rgba(0,0,0,0.06);
    --transition:all 0.25s cubic-bezier(0.4,0,0.2,1);
}

.breadcrumb { display:flex;align-items:center;gap:8px;font-size:13px;color:#9ca3af;margin-bottom:20px; }
.breadcrumb a { color:var(--primary);text-decoration:none;font-weight:600; }

.wallet-hero {
    border-radius: 20px; padding: 28px 32px;
    margin-bottom: 24px; position: relative; overflow: hidden;
    box-shadow: 0 8px 28px rgba(0,0,0,0.15);
}
.wallet-hero::before {
    content:''; position:absolute; top:-50px; right:-50px;
    width:200px; height:200px; background:rgba(255,255,255,0.08); border-radius:50%;
}
.wallet-hero::after {
    content:''; position:absolute; bottom:-60px; left:40%;
    width:160px; height:160px; background:rgba(255,255,255,0.05); border-radius:50%;
}
.hero-top { display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:24px; }
.hero-icon { font-size:52px; line-height:1; }
.hero-name { font-size:26px; font-weight:900; color:white; letter-spacing:-0.5px; margin-bottom:4px; }
.hero-type { font-size:13px; color:rgba(255,255,255,0.75); font-weight:600; }
.hero-actions { display:flex; gap:8px; }
.btn-hero {
    padding:8px 16px; border-radius:10px;
    background:rgba(255,255,255,0.18); border:1px solid rgba(255,255,255,0.25);
    color:white; font-size:13px; font-weight:700; cursor:pointer;
    text-decoration:none; display:flex; align-items:center; gap:6px;
    transition:background 0.2s;
}
.btn-hero:hover { background:rgba(255,255,255,0.28); }
.btn-hero.danger { background:rgba(239,68,68,0.25); border-color:rgba(239,68,68,0.4); }
.hero-balance { margin-bottom:20px; }
.hero-balance-label { font-size:12px;color:rgba(255,255,255,0.65);font-weight:700;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px; }
.hero-balance-val { font-size:44px;font-weight:900;color:white;letter-spacing:-2px;line-height:1; }
.hero-balance-currency { font-size:20px;font-weight:600;opacity:.7;margin-left:8px; }
.hero-stats { display:flex;gap:20px;flex-wrap:wrap; }
.hs-label { font-size:11px;color:rgba(255,255,255,0.6);font-weight:700;text-transform:uppercase;letter-spacing:0.6px; }
.hs-val { font-size:16px;font-weight:800;color:white;margin-top:3px; }

.tab-nav { display:flex;gap:4px;background:#f3f4f6;border-radius:12px;padding:4px;margin-bottom:20px;width:fit-content; }
body.dark .tab-nav { background:rgba(255,255,255,0.06); }
.tab-btn {
    padding:8px 18px;border-radius:10px;font-size:13px;font-weight:700;
    border:none;cursor:pointer;transition:var(--transition);color:#6b7280;background:transparent;
}
.tab-btn.active { background:white;color:var(--primary);box-shadow:0 2px 8px rgba(0,0,0,0.08); }
body.dark .tab-btn.active { background:#191d27;color:var(--primary); }
.tab-content { display:none; }
.tab-content.active { display:block; }

.section-card { background:white;border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:20px; }
body.dark .section-card { background:#191d27; }
.sc-hdr { padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center; }
body.dark .sc-hdr { border-color:rgba(255,255,255,0.06); }
.sc-title { font-size:15px;font-weight:800;color:#1f2937; }
body.dark .sc-title { color:#e5e7eb; }

.tx-item {
    display:flex;align-items:center;gap:14px;
    padding:14px 20px;border-bottom:1px solid #f9fafb;transition:background .15s;
}
body.dark .tx-item { border-color:rgba(255,255,255,0.03); }
.tx-item:last-child { border-bottom:none; }
.tx-item:hover { background:#f9fafb; }
body.dark .tx-item:hover { background:rgba(255,255,255,0.02); }
.tx-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px; }
.tx-icon.income { background:#d1fae5; }
.tx-icon.expense { background:#fee2e2; }
.tx-info { flex:1;min-width:0; }
.tx-cat { font-size:14px;font-weight:700;color:#1f2937; }
body.dark .tx-cat { color:#e5e7eb; }
.tx-note { font-size:12px;color:#9ca3af;margin-top:2px; }
.tx-right { text-align:right;flex-shrink:0; }
.tx-amount { font-size:15px;font-weight:800; }
.tx-amount.income { color:var(--success); }
.tx-amount.expense { color:var(--danger); }
.tx-date { font-size:11px;color:#9ca3af;margin-top:2px; }

.tf-item { display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid #f9fafb;transition:background .15s; }
body.dark .tf-item { border-color:rgba(255,255,255,0.03); }
.tf-item:last-child { border-bottom:none; }
.tf-item:hover { background:#f9fafb; }
body.dark .tf-item:hover { background:rgba(255,255,255,0.02); }
.tf-av { width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;background:#dbeafe;flex-shrink:0; }
.tf-desc { flex:1;min-width:0; }
.tf-names { font-size:13px;font-weight:700;color:#1f2937; }
body.dark .tf-names { color:#e5e7eb; }
.tf-date { font-size:12px;color:#9ca3af;margin-top:2px; }

.adj-item { display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid #f9fafb; }
body.dark .adj-item { border-color:rgba(255,255,255,0.03); }
.adj-item:last-child { border-bottom:none; }
.adj-icon { width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0; }
.adj-icon.pos { background:#d1fae5; }
.adj-icon.neg { background:#fee2e2; }

.empty-msg { text-align:center;padding:40px;color:#9ca3af;font-size:13px;font-weight:500; }

.alert { display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:var(--radius-sm);font-size:14px;font-weight:500;margin-bottom:20px; }
.alert-success { background:#d1fae5;color:#065f46;border-left:4px solid var(--success); }
.alert-error   { background:#fee2e2;color:#991b1b;border-left:4px solid var(--danger); }

.btn-primary { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-sm);background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;transition:opacity .2s; }
.btn-primary:hover { opacity:.88; }

.modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;visibility:hidden;transition:opacity .22s,visibility .22s; }
.modal-overlay.active { opacity:1;visibility:visible; }
.modal-box { background:white;border-radius:20px;width:100%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);transform:scale(0.95) translateY(10px);transition:transform .25s cubic-bezier(0.4,0,0.2,1);max-height:90vh;overflow-y:auto; }
.modal-overlay.active .modal-box { transform:scale(1) translateY(0); }
body.dark .modal-box { background:#191d27; }
.modal-hdr { padding:20px 24px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;justify-content:space-between;align-items:center; }
.modal-hdr-title { font-size:16px;font-weight:800;color:white; }
.modal-close-btn { background:rgba(255,255,255,0.2);border:none;border-radius:8px;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:white;font-size:16px; }
.modal-body { padding:22px 24px; }
.form-group { margin-bottom:16px; }
.form-label { font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:7px; }
body.dark .form-label { color:#9ca3af; }
.required { color:var(--danger); }
.form-ctrl { width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:var(--radius-sm);font-size:14px;background:#f9fafb;color:#1f2937;outline:none;transition:all .2s; }
.form-ctrl:focus { border-color:var(--primary);background:white; }
body.dark .form-ctrl { background:#141820;border-color:rgba(255,255,255,0.1);color:#e5e7eb; }
.modal-foot { padding:16px 24px;border-top:1px solid #f3f4f6;display:flex;gap:10px; }
body.dark .modal-foot { border-color:rgba(255,255,255,0.06);background:#191d27; }
.btn-cancel { flex:1;padding:10px;border-radius:var(--radius-sm);background:#f3f4f6;border:2px solid #e5e7eb;color:#6b7280;font-size:14px;font-weight:600;cursor:pointer; }
.btn-cancel:hover { background:#e5e7eb; }
.modal-foot .btn-primary { flex:2;justify-content:center;padding:10px; }

.skeleton { background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:8px; }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }
</style>

<div class="breadcrumb">
    <a href="{{ route('money-wallets.index') }}">← Ví tiền</a>
    <span>/</span>
    <span id="breadcrumbName">...</span>
</div>

<div id="alertContainer"></div>

<div id="heroWrap">
    <div class="skeleton" style="height:260px;border-radius:20px;margin-bottom:24px;"></div>
</div>

<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('transactions',this)">💰 Giao dịch</button>
    <button class="tab-btn" onclick="switchTab('transfers',this)">↔️ Chuyển ví</button>
    <button class="tab-btn" onclick="switchTab('adjustments',this)">⚖️ Điều chỉnh</button>
</div>

<div class="tab-content active" id="tab-transactions">
    <div class="section-card">
        <div class="sc-hdr">
            <div class="sc-title">Lịch sử giao dịch</div>
            <a id="txAllLink" href="#" style="font-size:12px;color:var(--primary);font-weight:700;text-decoration:none;">Xem tất cả →</a>
        </div>
        <div id="txList"><div class="empty-msg"><div class="skeleton" style="height:60px;margin:8px 20px;border-radius:10px;"></div></div></div>
    </div>
</div>

<div class="tab-content" id="tab-transfers">
    <div class="section-card">
        <div class="sc-hdr">
            <div class="sc-title">Lịch sử chuyển tiền</div>
            <a href="{{ route('wallet-transfers.index') }}" style="font-size:12px;color:var(--primary);font-weight:700;text-decoration:none;">Trang chuyển tiền →</a>
        </div>
        <div id="tfList"><div class="empty-msg"><div class="skeleton" style="height:60px;margin:8px 20px;border-radius:10px;"></div></div></div>
    </div>
</div>

<div class="tab-content" id="tab-adjustments">
    <div class="section-card">
        <div class="sc-hdr"><div class="sc-title">Lịch sử điều chỉnh số dư</div></div>
        <div id="adjList"><div class="empty-msg"><div class="skeleton" style="height:60px;margin:8px 20px;border-radius:10px;"></div></div></div>
    </div>
</div>

<div class="modal-overlay" id="adjustModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">⚖️ Điều chỉnh số dư</div>
            <button class="modal-close-btn" onclick="closeModal('adjustModal')">✕</button>
        </div>
        <div class="modal-body">
            <div style="background:#f0f7ff;border-radius:10px;padding:14px;margin-bottom:16px;font-size:13px;">
                <div style="font-weight:700;color:#1e40af;margin-bottom:4px;">💡 Cách hoạt động</div>
                <div style="color:#4b5563;">Nhập số tiền thực tế bạn đang có. Hệ thống sẽ tự động tạo giao dịch bù trừ để khớp số dư.</div>
                <div style="margin-top:8px;font-weight:700;color:#374151;">
                    Số dư hiện tại: <span id="adjCurrentBal">--</span>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Số dư thực tế <span class="required">*</span></label>
                <input id="adjSoDu" type="number" class="form-ctrl" placeholder="Nhập số tiền thực tế đang có" min="0" step="1000">
            </div>
            <div class="form-group">
                <label class="form-label">Danh mục giao dịch <span class="required">*</span></label>
                <select id="adjCategory" class="form-ctrl">
                    <option value="">-- Chọn danh mục --</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Lý do điều chỉnh</label>
                <input id="adjLyDo" class="form-ctrl" placeholder="VD: Đếm tiền mặt, đối soát sao kê..." maxlength="255">
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('adjustModal')">Hủy</button>
            <button class="btn-primary" onclick="submitAdjust()">Xác nhận điều chỉnh</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="editShowModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">✏️ Chỉnh sửa ví</div>
            <button class="modal-close-btn" onclick="closeModal('editShowModal')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Tên ví <span class="required">*</span></label>
                <input id="editTenVi" class="form-ctrl" maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Loại ví</label>
                <select id="editLoaiVi" class="form-ctrl">
                    <option value="tien_mat">💵 Tiền mặt</option>
                    <option value="ngan_hang">🏦 Ngân hàng</option>
                    <option value="vi_dien_tu">📱 Ví điện tử</option>
                    <option value="the_tin_dung">💳 Thẻ tín dụng</option>
                    <option value="dau_tu">📈 Đầu tư</option>
                    <option value="khac">🗂 Khác</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Đơn vị tiền tệ</label>
                <select id="editDonVi" class="form-ctrl">
                    <option value="VND">VND</option>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="JPY">JPY</option>
                    <option value="KRW">KRW</option>
                    <option value="SGD">SGD</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Biểu tượng</label>
                <input id="editBieuTuong" class="form-ctrl" maxlength="10" placeholder="Nhập emoji...">
            </div>
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <input id="editMoTa" class="form-ctrl" maxlength="500">
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('editShowModal')">Hủy</button>
            <button class="btn-primary" onclick="submitEdit()">Lưu</button>
        </div>
    </div>
</div>

<script>
const WALLET_ID = {{ request()->route('moneyWallet') }};
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

const WALLET_COLORS = {
    tien_mat:'#10b981', ngan_hang:'#4a90e2', vi_dien_tu:'#8b5cf6',
    the_tin_dung:'#ef4444', dau_tu:'#f59e0b', khac:'#6b7280',
};
const WALLET_LABELS = {
    tien_mat:'Tiền mặt', ngan_hang:'Ngân hàng', vi_dien_tu:'Ví điện tử',
    the_tin_dung:'Thẻ tín dụng', dau_tu:'Đầu tư', khac:'Khác',
};

let walletData = null;

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
function fmtDate(d) { return new Date(d).toLocaleDateString('vi-VN'); }

async function loadWallet() {
    const data = await apiFetch(`/api/v1/money-wallets/${WALLET_ID}`);
    walletData = data;

    const color = WALLET_COLORS[data.loai_vi] || '#6b7280';
    const label = WALLET_LABELS[data.loai_vi] || data.loai_vi;
    const bal   = Number(data.so_du);

    document.title = data.ten_vi;
    document.getElementById('breadcrumbName').textContent = data.ten_vi;
    document.getElementById('txAllLink').href = `/transactions?money_wallet_id=${WALLET_ID}`;

    document.getElementById('heroWrap').innerHTML = `
    <div class="wallet-hero" style="background:linear-gradient(135deg,${color}dd,${color});box-shadow:0 8px 28px ${color}55;">
        <div class="hero-top">
            <div>
                <div class="hero-icon">${data.bieu_tuong}</div>
                <div class="hero-name">${data.ten_vi}</div>
                <div class="hero-type">${label} · ${data.don_vi_tien_te}</div>
            </div>
            <div class="hero-actions">
                <button class="btn-hero" onclick="openAdjust()">⚖️ Điều chỉnh số dư</button>
                <button class="btn-hero" onclick="openEdit()">✏️ Sửa</button>
            </div>
        </div>
        <div class="hero-balance">
            <div class="hero-balance-label">Số dư hiện tại</div>
            <div>
                <span class="hero-balance-val">${fmt(bal)}</span>
                <span class="hero-balance-currency">${data.don_vi_tien_te}</span>
            </div>
        </div>
        <div class="hero-stats">
            <div class="hs-item">
                <div class="hs-label">↑ Tổng thu</div>
                <div class="hs-val">+${fmt(data.stats?.tong_thu || 0)}đ</div>
            </div>
            <div class="hs-item">
                <div class="hs-label">↓ Tổng chi</div>
                <div class="hs-val">-${fmt(data.stats?.tong_chi || 0)}đ</div>
            </div>
            <div class="hs-item">
                <div class="hs-label">📅 Số dư ban đầu</div>
                <div class="hs-val">${fmt(data.so_du_ban_dau)}đ</div>
            </div>
            ${data.mo_ta ? `<div class="hs-item"><div class="hs-label">📝 Ghi chú</div><div class="hs-val" style="font-size:13px;">${data.mo_ta}</div></div>` : ''}
        </div>
    </div>`;

    document.getElementById('adjCurrentBal').textContent = fmt(bal) + ' ' + data.don_vi_tien_te;
    document.getElementById('adjSoDu').value = bal;
    document.getElementById('editTenVi').value = data.ten_vi;
    document.getElementById('editLoaiVi').value = data.loai_vi;
    document.getElementById('editDonVi').value = data.don_vi_tien_te;
    document.getElementById('editBieuTuong').value = data.bieu_tuong;
    document.getElementById('editMoTa').value = data.mo_ta || '';
}

async function loadTransactions() {
    const data = await apiFetch(`/api/v1/money-wallets/${WALLET_ID}/transactions`);
    const el = document.getElementById('txList');
    if (!data.length) { el.innerHTML = '<div class="empty-msg">📭 Chưa có giao dịch nào trong ví này</div>'; return; }
    el.innerHTML = data.map(tx => {
        const isIncome = tx.loai_giao_dich === 'THU';
        return `
        <div class="tx-item">
            <div class="tx-icon ${isIncome ? 'income' : 'expense'}">${tx.category?.bieu_tuong || (isIncome ? '💰' : '💸')}</div>
            <div class="tx-info">
                <div class="tx-cat">${tx.category?.ten_danh_muc || 'Không rõ'}</div>
                ${tx.ghi_chu ? `<div class="tx-note">${tx.ghi_chu.substring(0,60)}</div>` : ''}
            </div>
            <div class="tx-right">
                <div class="tx-amount ${isIncome ? 'income' : 'expense'}">${isIncome ? '+' : '-'}${fmt(tx.so_tien)}đ</div>
                <div class="tx-date">${fmtDate(tx.ngay_giao_dich)}</div>
            </div>
        </div>`;
    }).join('');
}

async function loadTransfers() {
    const data = await apiFetch(`/api/v1/money-wallets/${WALLET_ID}/transfers`);
    const el = document.getElementById('tfList');
    if (!data.length) { el.innerHTML = '<div class="empty-msg">↔️ Chưa có giao dịch chuyển tiền nào</div>'; return; }
    el.innerHTML = data.map(tf => {
        const isFrom = tf.from_wallet_id === WALLET_ID;
        return `
        <div class="tf-item">
            <div class="tf-av">${isFrom ? '↗' : '↙'}</div>
            <div class="tf-desc">
                <div class="tf-names">${isFrom ? '→ ' + (tf.to_wallet?.ten_vi || '?') : '← ' + (tf.from_wallet?.ten_vi || '?')}</div>
                <div class="tf-date">${fmtDate(tf.ngay_chuyen)}${tf.ghi_chu ? ' · ' + tf.ghi_chu.substring(0,40) : ''}</div>
            </div>
            <div style="font-size:15px;font-weight:800;color:${isFrom ? '#ef4444' : '#10b981'};">${isFrom ? '-' : '+'}${fmt(tf.so_tien)}đ</div>
        </div>`;
    }).join('');
}

async function loadAdjustments() {
    const data = await apiFetch(`/api/v1/money-wallets/${WALLET_ID}/adjustments`);
    const el = document.getElementById('adjList');
    if (!data.length) { el.innerHTML = '<div class="empty-msg">⚖️ Chưa có điều chỉnh nào</div>'; return; }
    el.innerHTML = data.map(adj => {
        const isPos = adj.chenh_lech > 0;
        return `
        <div class="adj-item">
            <div class="adj-icon ${isPos ? 'pos' : 'neg'}">${isPos ? '↑' : '↓'}</div>
            <div style="flex:1;">
                <div style="font-size:13px;font-weight:700;color:#1f2937;">
                    ${isPos ? 'Tăng số dư' : 'Giảm số dư'}
                    <span style="color:${isPos ? '#10b981' : '#ef4444'}">${isPos ? '+' : ''}${fmt(adj.chenh_lech)}đ</span>
                </div>
                <div style="font-size:12px;color:#9ca3af;">
                    ${fmt(adj.so_du_truoc)}đ → ${fmt(adj.so_du_sau)}đ
                    ${adj.ly_do ? '· ' + adj.ly_do : ''}
                </div>
            </div>
            <div style="font-size:12px;color:#9ca3af;white-space:nowrap;">${fmtDate(adj.created_at)}</div>
        </div>`;
    }).join('');
}

async function loadCategories() {
    const data = await apiFetch('/api/v1/categories');
    const sel = document.getElementById('adjCategory');
    const groups = {};
    data.filter(c => c.danh_muc_cha_id).forEach(c => {
        const g = c.loai_danh_muc === 'THU' ? 'Thu nhập' : 'Chi tiêu';
        if (!groups[g]) groups[g] = [];
        groups[g].push(c);
    });
    Object.entries(groups).forEach(([label, cats]) => {
        const og = document.createElement('optgroup');
        og.label = label;
        cats.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.ten_danh_muc;
            og.appendChild(opt);
        });
        sel.appendChild(og);
    });
}

async function submitAdjust() {
    const body = {
        so_du_thuc_te: parseFloat(document.getElementById('adjSoDu').value),
        category_id:   document.getElementById('adjCategory').value,
        ly_do:         document.getElementById('adjLyDo').value.trim(),
    };
    if (!body.category_id) { showAlert('Vui lòng chọn danh mục', 'error'); return; }

    const res = await apiFetch(`/api/v1/money-wallets/${WALLET_ID}/adjust`, { method: 'POST', body: JSON.stringify(body) });
    if (res.success || res.id) {
        closeModal('adjustModal');
        showAlert('Điều chỉnh số dư thành công');
        loadWallet();
        loadTransactions();
        loadAdjustments();
    } else {
        showAlert(res.message || 'Có lỗi xảy ra', 'error');
    }
}

async function submitEdit() {
    const body = {
        ten_vi:        document.getElementById('editTenVi').value.trim(),
        loai_vi:       document.getElementById('editLoaiVi').value,
        don_vi_tien_te: document.getElementById('editDonVi').value,
        bieu_tuong:    document.getElementById('editBieuTuong').value,
        mo_ta:         document.getElementById('editMoTa').value.trim(),
    };
    if (!body.ten_vi) { showAlert('Vui lòng nhập tên ví', 'error'); return; }

    const res = await apiFetch(`/api/v1/money-wallets/${WALLET_ID}`, { method: 'PUT', body: JSON.stringify(body) });
    if (res.id) {
        closeModal('editShowModal');
        showAlert('Cập nhật ví thành công');
        loadWallet();
    } else {
        showAlert(res.message || 'Có lỗi xảy ra', 'error');
    }
}

async function deleteWallet() {
    if (!confirm('Xóa/ẩn ví này?')) return;
    const res = await apiFetch(`/api/v1/money-wallets/${WALLET_ID}`, { method: 'DELETE' });
    if (res.success) { window.location.href = '{{ route("money-wallets.index") }}'; }
    else showAlert(res.message || 'Có lỗi xảy ra', 'error');
}

function openAdjust() { document.getElementById('adjustModal').classList.add('active'); }
function openEdit()    { document.getElementById('editShowModal').classList.add('active'); }
function closeModal(id){ document.getElementById(id).classList.remove('active'); }

function switchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('active'); });
});

Promise.all([loadWallet(), loadTransactions(), loadTransfers(), loadAdjustments(), loadCategories()]);
</script>
@endsection
