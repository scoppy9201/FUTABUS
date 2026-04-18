@extends('layouts.app')
@section('title', 'Ví tiền')
@section('content')
<style>
:root {
    --primary: #4a90e2;
    --primary-dark: #2a5298;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --radius: 16px;
    --radius-sm: 10px;
    --shadow: 0 2px 12px rgba(0,0,0,0.06);
    --transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
}

.pw-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 24px; padding: 22px 28px;
    background: white; border-radius: var(--radius); box-shadow: var(--shadow);
}
body.dark .pw-header { background: #191d27; }
.pw-title {
    display: flex; align-items: center; gap: 14px;
    font-size: 22px; font-weight: 800; color: #1f2937;
}
body.dark .pw-title { color: #e5e7eb; }
.pw-icon {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex; align-items: center; justify-content: center; font-size: 22px;
    box-shadow: 0 4px 14px rgba(74,144,226,0.35);
}
.pw-actions { display: flex; gap: 10px; }

.total-card {
    background: linear-gradient(135deg, #1e3a5f 0%, #2a5298 50%, #4a90e2 100%);
    border-radius: var(--radius); padding: 28px 32px;
    margin-bottom: 24px; position: relative; overflow: hidden;
    box-shadow: 0 8px 32px rgba(42,82,152,0.35);
}
.total-card::before {
    content: ''; position: absolute; top: -60px; right: -40px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,0.06); border-radius: 50%;
    pointer-events: none;
}
.total-card::after {
    content: ''; position: absolute; bottom: -80px; left: 30%;
    width: 180px; height: 180px;
    background: rgba(255,255,255,0.04); border-radius: 50%;
    pointer-events: none;
}
.total-label { font-size: 13px; color: rgba(255,255,255,0.7); font-weight: 600; margin-bottom: 8px; }
.total-amount { font-size: 40px; font-weight: 900; color: white; letter-spacing: -1.5px; line-height: 1; margin-bottom: 20px; }
.total-breakdown { display: flex; gap: 24px; flex-wrap: wrap; }
.breakdown-item { text-align: center; }
.breakdown-label { font-size: 11px; color: rgba(255,255,255,0.6); font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
.breakdown-val { font-size: 15px; font-weight: 800; color: white; }

.wallet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px,1fr)); gap: 20px; margin-bottom: 28px; }

.wallet-card {
    background: white; border-radius: var(--radius); overflow: hidden;
    box-shadow: var(--shadow); transition: var(--transition);
    border: 1px solid rgba(255,255,255,0.8);
    text-decoration: none; color: inherit; display: flex; flex-direction: column;
    position: relative;
}
.wallet-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.1); }
body.dark .wallet-card { background: #191d27; border-color: rgba(255,255,255,0.06); }
.wallet-card-bar { height: 4px; }
.wc-body { padding: 20px 22px; flex: 1; }
.wc-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px; }
.wc-icon-wrap { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 26px; }
.wc-type-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.wc-name { font-size: 16px; font-weight: 800; color: #1f2937; margin-bottom: 6px; }
body.dark .wc-name { color: #e5e7eb; }
.wc-desc { font-size: 12px; color: #9ca3af; font-weight: 500; }
.wc-balance-section { margin-top: 16px; padding-top: 16px; border-top: 1px solid #f3f4f6; }
body.dark .wc-balance-section { border-color: rgba(255,255,255,0.06); }
.wc-balance-label { font-size: 11px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
.wc-balance { font-size: 26px; font-weight: 900; letter-spacing: -1px; }
.wc-currency { font-size: 14px; font-weight: 600; color: #9ca3af; margin-left: 4px; }
.wc-footer {
    padding: 12px 22px; background: #fafafa;
    border-top: 1px solid #f3f4f6;
    display: flex; align-items: center; justify-content: space-between;
}
body.dark .wc-footer { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.06); }
.wc-date { font-size: 12px; color: #9ca3af; }
.wc-arrow {
    width: 28px; height: 28px; border-radius: 8px;
    background: rgba(74,144,226,0.08); color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    transition: var(--transition); font-size: 14px;
}
.wallet-card:hover .wc-arrow { background: var(--primary); color: white; }

.wallet-card-delete {
    position: absolute; top: 10px; right: 10px; z-index: 5;
    background: rgba(255,255,255,0.9); border: 1px solid rgba(0,0,0,0.06);
    width:34px; height:34px; border-radius:8px; display:flex;align-items:center;justify-content:center;cursor:pointer;
    font-size:14px; color:#ef4444;
    transition: background 0.2s;
}
.wallet-card-delete:hover { background: #fee2e2; }
body.dark .wallet-card-delete { background: rgba(0,0,0,0.18); border-color: rgba(255,255,255,0.06); }

.empty-wrap {
    grid-column: 1/-1; text-align: center; padding: 80px 20px;
    background: white; border-radius: var(--radius); box-shadow: var(--shadow);
}
body.dark .empty-wrap { background: #191d27; }
.empty-icon-big { font-size: 56px; margin-bottom: 16px; }
.empty-title { font-size: 20px; font-weight: 800; color: #1f2937; margin-bottom: 8px; }
body.dark .empty-title { color: #e5e7eb; }
.empty-sub { font-size: 14px; color: #9ca3af; margin-bottom: 28px; }

.inactive-section {
    background: white; border-radius: var(--radius); padding: 20px 24px;
    margin-top: 8px; box-shadow: var(--shadow);
}
body.dark .inactive-section { background: #191d27; }
.inactive-hdr {
    display: flex; align-items: center; gap: 8px; cursor: pointer;
    font-size: 14px; font-weight: 700; color: #9ca3af;
    user-select: none;
}
.inactive-list { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
.inactive-chip {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 14px; border-radius: 10px;
    background: #f3f4f6; border: 1px solid #e5e7eb;
    font-size: 13px; font-weight: 600; color: #6b7280;
}
body.dark .inactive-chip { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.08); color: #9ca3af; }
.restore-btn {
    padding: 3px 10px; border-radius: 8px;
    background: rgba(74,144,226,0.1); border: none;
    color: var(--primary); font-size: 11px; font-weight: 700;
    cursor: pointer; transition: background 0.2s;
}
.restore-btn:hover { background: rgba(74,144,226,0.2); }

/* ── Form inline alert (inside modal) ── */
.form-alert {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 600; margin-bottom: 16px;
    animation: fadeInDown 0.2s ease;
}
.form-alert.error   { background: #fee2e2; color: #991b1b; border-left: 3px solid var(--danger); }
.form-alert.success { background: #d1fae5; color: #065f46; border-left: 3px solid var(--success); }
body.dark .form-alert.error   { background: rgba(239,68,68,0.12); color: #fca5a5; border-left-color: var(--danger); }
body.dark .form-alert.success { background: rgba(16,185,129,0.12); color: #6ee7b7; border-left-color: var(--success); }
@keyframes fadeInDown { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:none; } }

/* ── Page toast (top-right, minimal, only when no modal open) ── */
.page-toast {
    position: fixed; top: 20px; right: 20px; z-index: 99999;
    display: flex; align-items: center; gap: 10px;
    padding: 12px 18px; border-radius: 12px;
    font-size: 13px; font-weight: 700;
    box-shadow: 0 8px 24px rgba(0,0,0,0.14);
    animation: toastIn 0.25s cubic-bezier(0.4,0,0.2,1);
    max-width: 340px;
}
.page-toast.success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
.page-toast.error   { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--danger); }
body.dark .page-toast.success { background: #064e3b; color: #6ee7b7; }
body.dark .page-toast.error   { background: #7f1d1d; color: #fca5a5; }
@keyframes toastIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:none; } }

.btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; font-size: 14px; font-weight: 700;
    border: none; cursor: pointer; text-decoration: none; transition: opacity 0.2s;
    white-space: nowrap;
}
.btn-primary:hover { opacity: 0.88; }
.btn-primary:disabled { opacity: 0.55; cursor: not-allowed; }
.btn-outline {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 18px; border-radius: var(--radius-sm);
    border: 2px solid var(--primary); color: var(--primary);
    background: transparent; font-size: 14px; font-weight: 700;
    cursor: pointer; text-decoration: none; transition: all 0.2s; white-space: nowrap;
}
.btn-outline:hover { background: var(--primary); color: white; }

.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);
    z-index: 9999; display: flex; align-items: center; justify-content: center;
    padding: 20px; opacity: 0; visibility: hidden;
    transition: opacity 0.22s, visibility 0.22s;
}
.modal-overlay.active { opacity: 1; visibility: visible; }
.modal-box {
    background: white; border-radius: 20px;
    width: 100%; max-width: 540px; overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    transform: scale(0.95) translateY(10px);
    transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
    max-height: 90vh; display: flex; flex-direction: column;
}
.modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
body.dark .modal-box { background: #191d27; }
.modal-hdr {
    padding: 22px 28px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex; justify-content: space-between; align-items: center;
}
.modal-hdr-title { font-size: 17px; font-weight: 800; color: white; display: flex; align-items: center; gap: 10px; }
.modal-close-btn {
    background: rgba(255,255,255,0.2); border: none; border-radius: 8px;
    width: 32px; height: 32px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 18px; transition: background 0.2s;
}
.modal-close-btn:hover { background: rgba(255,255,255,0.32); }
.modal-body { padding: 24px 28px; overflow-y: auto; flex: 1; }

.form-group { margin-bottom: 16px; }
.form-label { font-size: 13px; font-weight: 700; color: #374151; display: block; margin-bottom: 7px; }
body.dark .form-label { color: #9ca3af; }
.required { color: var(--danger); }
.form-ctrl {
    width: 100%; padding: 10px 14px;
    border: 2px solid #e5e7eb; border-radius: var(--radius-sm);
    font-size: 14px; background: #f9fafb; color: #1f2937;
    outline: none; transition: all 0.2s; box-sizing: border-box;
}
.form-ctrl:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(74,144,226,0.1); }
body.dark .form-ctrl { background: #141820; border-color: rgba(255,255,255,0.1); color: #e5e7eb; }
body.dark .form-ctrl:focus { background: #0f1217; box-shadow: 0 0 0 3px rgba(74,144,226,0.15); }
body.dark .form-ctrl::placeholder { color: #4b5563; }

.loai-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; margin-top: 6px; }
.loai-card {
    border: 2px solid #e5e7eb; border-radius: 12px;
    padding: 12px 8px; text-align: center; cursor: pointer;
    transition: var(--transition); position: relative;
}
.loai-card:hover { border-color: var(--primary); }
.loai-card.selected { border-color: var(--primary); background: rgba(74,144,226,0.06); }
body.dark .loai-card { border-color: rgba(255,255,255,0.1); }
body.dark .loai-card.selected { background: rgba(74,144,226,0.1); }
.loai-card input { position: absolute; opacity: 0; pointer-events: none; }
.loai-emoji { font-size: 24px; margin-bottom: 5px; }
.loai-name { font-size: 11px; font-weight: 700; color: #374151; }
body.dark .loai-name { color: #e5e7eb; }

.emoji-grid { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.emoji-btn {
    width: 38px; height: 38px; border-radius: 8px; border: 2px solid #e5e7eb;
    background: #f9fafb; cursor: pointer; font-size: 18px;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.15s;
}
.emoji-btn:hover { border-color: var(--primary); background: rgba(74,144,226,0.05); }
.emoji-btn.selected { border-color: var(--primary); background: rgba(74,144,226,0.1); }

.modal-foot {
    padding: 16px 28px; border-top: 1px solid #f3f4f6;
    display: flex; gap: 10px; flex-shrink: 0;
    background: white;
}
body.dark .modal-foot { border-color: rgba(255,255,255,0.06); background: #191d27; }
.btn-cancel {
    flex: 1; padding: 10px; border-radius: var(--radius-sm);
    background: #f3f4f6; border: 2px solid #e5e7eb;
    color: #6b7280; font-size: 14px; font-weight: 600; cursor: pointer;
    transition: background 0.2s;
}
.btn-cancel:hover { background: #e5e7eb; }
body.dark .btn-cancel { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #9ca3af; }
.modal-foot .btn-primary { flex: 2; justify-content: center; padding: 10px; }

.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 8px;
}
body.dark .skeleton {
    background: linear-gradient(90deg, #1e2333 25%, #262d3d 50%, #1e2333 75%);
    background-size: 200% 100%;
}
@keyframes shimmer { 0% { background-position: 200% 0 } 100% { background-position: -200% 0 } }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
    .wallet-grid  { grid-template-columns: 1fr; }
    .pw-header    { flex-direction: column; align-items: flex-start; gap: 12px; }
    .loai-grid    { grid-template-columns: repeat(2,1fr); }
}
</style>

<div class="pw-header">
    <div class="pw-title">
        <div class="pw-icon">💳</div>
        <div>
            <div>Ví tiền</div>
            <div style="font-size:13px;font-weight:500;color:#6b7280;margin-top:2px;">Quản lý các nguồn tiền của bạn</div>
        </div>
    </div>
    <div class="pw-actions">
        <a href="{{ route('wallet-transfers.index') }}" class="btn-outline">Chuyển tiền</a>
        <a href="{{ route('money-wallets.qr.index') }}" class="btn-outline">QR Transfer</a>
        <button class="btn-primary" id="btnOpenCreate">+ Thêm ví</button>
    </div>
</div>

<div id="totalCard" class="total-card">
    <div class="total-label">TỔNG TÀI SẢN (THU - CHI)</div>
    <div class="total-amount" id="totalAmount"><div class="skeleton" style="width:260px;height:44px;"></div></div>
    <div class="total-breakdown" id="totalBreakdown">
        <div class="skeleton" style="width:100px;height:36px;"></div>
        <div class="skeleton" style="width:100px;height:36px;"></div>
        <div class="skeleton" style="width:80px;height:36px;"></div>
        <div class="skeleton" style="width:120px;height:36px;"></div>
    </div>
</div>

<div class="wallet-grid" id="walletGrid">
    <div class="skeleton" style="height:220px;border-radius:16px;"></div>
    <div class="skeleton" style="height:220px;border-radius:16px;"></div>
    <div class="skeleton" style="height:220px;border-radius:16px;"></div>
</div>

<div id="inactiveSection"></div>

{{-- ── Create Modal ── --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">💳 Thêm ví mới</div>
            <button class="modal-close-btn" id="btnCloseCreate">✕</button>
        </div>
        <div class="modal-body">
            {{-- Inline alert inside form --}}
            <div id="createFormAlert" style="display:none;"></div>

            <div class="form-group">
                <label class="form-label">Loại ví <span class="required">*</span></label>
                <div class="loai-grid" id="createLoaiGrid">
                    <label class="loai-card selected" data-val="tien_mat">
                        <input type="radio" name="loai_vi" value="tien_mat" checked>
                        <div class="loai-emoji">💵</div><div class="loai-name">Tiền mặt</div>
                    </label>
                    <label class="loai-card" data-val="ngan_hang">
                        <input type="radio" name="loai_vi" value="ngan_hang">
                        <div class="loai-emoji">🏦</div><div class="loai-name">Ngân hàng</div>
                    </label>
                    <label class="loai-card" data-val="vi_dien_tu">
                        <input type="radio" name="loai_vi" value="vi_dien_tu">
                        <div class="loai-emoji">📱</div><div class="loai-name">Ví điện tử</div>
                    </label>
                    <label class="loai-card" data-val="the_tin_dung">
                        <input type="radio" name="loai_vi" value="the_tin_dung">
                        <div class="loai-emoji">💳</div><div class="loai-name">Thẻ tín dụng</div>
                    </label>
                    <label class="loai-card" data-val="dau_tu">
                        <input type="radio" name="loai_vi" value="dau_tu">
                        <div class="loai-emoji">📈</div><div class="loai-name">Đầu tư</div>
                    </label>
                    <label class="loai-card" data-val="khac">
                        <input type="radio" name="loai_vi" value="khac">
                        <div class="loai-emoji">🗂</div><div class="loai-name">Khác</div>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Tên ví <span class="required">*</span></label>
                <input id="createTenVi" class="form-ctrl" placeholder="Ví dụ: Tiền mặt cá nhân, ACB, MoMo..." maxlength="100">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Số dư ban đầu <span class="required">*</span></label>
                    <input id="createSoDu" type="number" class="form-ctrl" placeholder="0" min="0" step="1000" value="0">
                    <div id="soDuHint" style="font-size:11px;margin-top:5px;color:#9ca3af;">
                        Còn có thể phân bổ: <strong id="conLai">--</strong>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Đơn vị tiền tệ <span class="required">*</span></label>
                    <select id="createDonVi" class="form-ctrl">
                        <option value="VND" selected>VND — Việt Nam Đồng</option>
                        <option value="USD">USD — Đô la Mỹ</option>
                        <option value="EUR">EUR — Euro</option>
                        <option value="JPY">JPY — Yên Nhật</option>
                        <option value="KRW">KRW — Won Hàn Quốc</option>
                        <option value="SGD">SGD — Đô la Singapore</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top:16px;">
                <label class="form-label">Biểu tượng</label>
                <div class="emoji-grid" id="createEmojiGrid">
                    <button type="button" class="emoji-btn selected" data-emoji="💰">💰</button>
                    <button type="button" class="emoji-btn" data-emoji="💵">💵</button>
                    <button type="button" class="emoji-btn" data-emoji="💴">💴</button>
                    <button type="button" class="emoji-btn" data-emoji="💶">💶</button>
                    <button type="button" class="emoji-btn" data-emoji="🏦">🏦</button>
                    <button type="button" class="emoji-btn" data-emoji="💳">💳</button>
                    <button type="button" class="emoji-btn" data-emoji="📱">📱</button>
                    <button type="button" class="emoji-btn" data-emoji="💹">💹</button>
                    <button type="button" class="emoji-btn" data-emoji="📈">📈</button>
                    <button type="button" class="emoji-btn" data-emoji="🪙">🪙</button>
                    <button type="button" class="emoji-btn" data-emoji="💎">💎</button>
                    <button type="button" class="emoji-btn" data-emoji="🏧">🏧</button>
                    <button type="button" class="emoji-btn" data-emoji="🛍">🛍</button>
                    <button type="button" class="emoji-btn" data-emoji="🎁">🎁</button>
                    <button type="button" class="emoji-btn" data-emoji="🗂">🗂</button>
                    <button type="button" class="emoji-btn" data-emoji="📊">📊</button>
                </div>
                <input type="hidden" id="createEmoji" value="💰">
            </div>
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <input id="createMoTa" class="form-ctrl" placeholder="Ghi chú thêm..." maxlength="500">
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" id="btnCancelCreate">Hủy</button>
            <button class="btn-primary" id="btnSubmitCreate">Tạo ví</button>
        </div>
    </div>
</div>

{{-- ── Edit Modal ── --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">✏️ Chỉnh sửa ví</div>
            <button class="modal-close-btn" id="btnCloseEdit">✕</button>
        </div>
        <div class="modal-body">
            <div id="editFormAlert" style="display:none;"></div>
            <input type="hidden" id="editWalletId">
            <div class="form-group">
                <label class="form-label">Loại ví <span class="required">*</span></label>
                <div class="loai-grid" id="editLoaiGrid">
                    <label class="loai-card" data-val="tien_mat"><input type="radio" name="edit_loai_vi" value="tien_mat"><div class="loai-emoji">💵</div><div class="loai-name">Tiền mặt</div></label>
                    <label class="loai-card" data-val="ngan_hang"><input type="radio" name="edit_loai_vi" value="ngan_hang"><div class="loai-emoji">🏦</div><div class="loai-name">Ngân hàng</div></label>
                    <label class="loai-card" data-val="vi_dien_tu"><input type="radio" name="edit_loai_vi" value="vi_dien_tu"><div class="loai-emoji">📱</div><div class="loai-name">Ví điện tử</div></label>
                    <label class="loai-card" data-val="the_tin_dung"><input type="radio" name="edit_loai_vi" value="the_tin_dung"><div class="loai-emoji">💳</div><div class="loai-name">Thẻ tín dụng</div></label>
                    <label class="loai-card" data-val="dau_tu"><input type="radio" name="edit_loai_vi" value="dau_tu"><div class="loai-emoji">📈</div><div class="loai-name">Đầu tư</div></label>
                    <label class="loai-card" data-val="khac"><input type="radio" name="edit_loai_vi" value="khac"><div class="loai-emoji">🗂</div><div class="loai-name">Khác</div></label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Tên ví <span class="required">*</span></label>
                <input id="editTenVi" class="form-ctrl" maxlength="100">
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
                <div class="emoji-grid" id="editEmojiGrid">
                    <button type="button" class="emoji-btn" data-emoji="💰">💰</button>
                    <button type="button" class="emoji-btn" data-emoji="💵">💵</button>
                    <button type="button" class="emoji-btn" data-emoji="💴">💴</button>
                    <button type="button" class="emoji-btn" data-emoji="💶">💶</button>
                    <button type="button" class="emoji-btn" data-emoji="🏦">🏦</button>
                    <button type="button" class="emoji-btn" data-emoji="💳">💳</button>
                    <button type="button" class="emoji-btn" data-emoji="📱">📱</button>
                    <button type="button" class="emoji-btn" data-emoji="💹">💹</button>
                    <button type="button" class="emoji-btn" data-emoji="📈">📈</button>
                    <button type="button" class="emoji-btn" data-emoji="🪙">🪙</button>
                    <button type="button" class="emoji-btn" data-emoji="💎">💎</button>
                    <button type="button" class="emoji-btn" data-emoji="🏧">🏧</button>
                    <button type="button" class="emoji-btn" data-emoji="🛍">🛍</button>
                    <button type="button" class="emoji-btn" data-emoji="🎁">🎁</button>
                    <button type="button" class="emoji-btn" data-emoji="🗂">🗂</button>
                    <button type="button" class="emoji-btn" data-emoji="📊">📊</button>
                </div>
                <input type="hidden" id="editEmoji" value="">
            </div>
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <input id="editMoTa" class="form-ctrl" maxlength="500">
            </div>
            <div style="background:rgba(245,158,11,0.08);border-radius:10px;padding:12px;font-size:12px;color:#92400e;display:flex;align-items:center;gap:8px;">
                ⚠️ Muốn thay đổi số dư, vào chi tiết ví và dùng <strong>"Điều chỉnh số dư"</strong>.
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" id="btnCancelEdit">Hủy</button>
            <button class="btn-primary" id="btnSubmitEdit">Lưu thay đổi</button>
        </div>
    </div>
</div>

<script>
(function () {
    // Guard SPA
    if (window.__walletIndexInit) return;
    window.__walletIndexInit = true;

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const API = {
        wallets: '/api/v1/money-wallets',
        summary: '/api/v1/money-wallets/summary',
        restore: id => `/api/v1/money-wallets/${id}/restore`,
        destroy: id => `/api/v1/money-wallets/${id}`,
        update:  id => `/api/v1/money-wallets/${id}`,
        store:   '/api/v1/money-wallets',
    };

    const WALLET_COLORS = {
        tien_mat: '#10b981', ngan_hang: '#4a90e2',
        vi_dien_tu: '#8b5cf6', the_tin_dung: '#ef4444',
        dau_tu: '#f59e0b', khac: '#6b7280',
    };
    const WALLET_LABELS = {
        tien_mat: 'Tiền mặt', ngan_hang: 'Ngân hàng',
        vi_dien_tu: 'Ví điện tử', the_tin_dung: 'Thẻ tín dụng',
        dau_tu: 'Đầu tư', khac: 'Khác',
    };

    let summaryData  = {};
    let createEmoji  = '💰';
    let editEmoji    = '';

    // ── Helpers ──────────────────────────────────────────
    function fmt(n) { return Number(n).toLocaleString('vi-VN'); }

    async function apiFetch(url, opts = {}) {
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                ...(opts.headers || {}),
            },
            ...opts,
        });
        let data;
        try { data = await res.json(); } catch { data = { message: 'Lỗi phản hồi từ server' }; }
        data.__status = res.status;
        return data;
    }

    // ── Toast (chỉ dùng khi không có modal nào đang mở) ──
    function pageToast(msg, type = 'success') {
        const el = document.createElement('div');
        el.className = `page-toast ${type}`;
        el.textContent = (type === 'success' ? '✓ ' : '⚠ ') + msg;
        document.body.appendChild(el);
        setTimeout(() => {
            el.style.transition = 'opacity .3s, transform .3s';
            el.style.opacity = '0';
            el.style.transform = 'translateX(20px)';
            setTimeout(() => el.remove(), 320);
        }, 3500);
    }

    // ── Alert bên trong form modal ────────────────────────
    function showFormAlert(containerId, msg, type = 'error') {
        const el = document.getElementById(containerId);
        if (!el) return;
        el.style.display = 'block';
        el.innerHTML = `<div class="form-alert ${type}">${type === 'error' ? '⚠' : '✓'} ${msg}</div>`;
        // Scroll to top of modal body so alert is visible
        el.closest('.modal-body')?.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function clearFormAlert(containerId) {
        const el = document.getElementById(containerId);
        if (el) { el.style.display = 'none'; el.innerHTML = ''; }
    }

    // ── Wallet card HTML ──────────────────────────────────
    function walletCardHTML(w) {
        const color   = WALLET_COLORS[w.loai_vi] || '#6b7280';
        const bgLight = color + '18';
        const label   = WALLET_LABELS[w.loai_vi] || w.loai_vi;
        const bal     = Number(w.so_du);
        const created = new Date(w.created_at).toLocaleDateString('vi-VN');
        return `
        <a href="/money-wallets/${w.id}" class="wallet-card">
            <button class="wallet-card-delete" data-delete-id="${w.id}">🗑</button>
            <div class="wallet-card-bar" style="background:${color};"></div>
            <div class="wc-body">
                <div class="wc-top">
                    <div class="wc-icon-wrap" style="background:${bgLight};">
                        <span style="font-size:28px;">${w.bieu_tuong}</span>
                    </div>
                    <span class="wc-type-badge" style="background:${bgLight};color:${color};">${label}</span>
                </div>
                <div class="wc-name">${w.ten_vi}</div>
                ${w.mo_ta ? `<div class="wc-desc">${w.mo_ta.substring(0,50)}</div>` : ''}
                <div class="wc-balance-section">
                    <div class="wc-balance-label">Số dư hiện tại</div>
                    <div>
                        <span class="wc-balance" style="color:${bal >= 0 ? color : '#ef4444'};">${fmt(bal)}</span>
                        <span class="wc-currency">${w.don_vi_tien_te}</span>
                    </div>
                </div>
            </div>
            <div class="wc-footer">
                <span class="wc-date">Tạo ${created}</span>
                <div class="wc-arrow">→</div>
            </div>
        </a>`;
    }

    // ── Load page data ────────────────────────────────────
    async function loadPage() {
        const [walletRes, summaryRes] = await Promise.all([
            apiFetch(API.wallets),
            apiFetch(API.summary),
        ]);

        summaryData = summaryRes;
        const { tong_tai_san, tong_thu, tong_chi, tong_vi, tong_so_du_vi, con_lai } = summaryRes;

        const amountEl = document.getElementById('totalAmount');
        if (amountEl) {
            amountEl.innerHTML = `
                ${tong_tai_san >= 0 ? '+' : ''}${fmt(tong_tai_san)}
                <span style="font-size:20px;font-weight:600;opacity:.7;">VND</span>`;
            amountEl.style.color = tong_tai_san >= 0 ? 'white' : '#fca5a5';
        }

        const breakdownEl = document.getElementById('totalBreakdown');
        if (breakdownEl) {
            breakdownEl.innerHTML = `
            <div class="breakdown-item">
                <div class="breakdown-label">📈 Tổng thu</div>
                <div class="breakdown-val" style="color:#4ade80;">+${fmt(tong_thu)}đ</div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label">📉 Tổng chi</div>
                <div class="breakdown-val" style="color:#fca5a5;">-${fmt(tong_chi)}đ</div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label">💳 Số ví</div>
                <div class="breakdown-val">${tong_vi} ví</div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label">🏦 Phân bổ vào ví</div>
                <div class="breakdown-val" style="color:${tong_so_du_vi > tong_tai_san ? '#fca5a5' : '#93c5fd'};">${fmt(tong_so_du_vi)}đ</div>
            </div>`;
        }

        // Update "còn lại" hint in create form
        const conLaiEl = document.getElementById('conLai');
        if (conLaiEl) {
            const soDuVal = parseFloat(document.getElementById('createSoDu')?.value) || 0;
            const remaining = (con_lai || 0) - soDuVal;
            conLaiEl.textContent = (remaining < 0 ? '-' : '') + fmt(Math.abs(remaining)) + 'đ';
        }

        const _list  = Array.isArray(walletRes) ? walletRes : (walletRes.data ?? []);
        const active   = _list.filter(w => w.trang_thai !== 'khong_hoat_dong');
        const inactive = _list.filter(w => w.trang_thai === 'khong_hoat_dong');

        const grid = document.getElementById('walletGrid');
        if (grid) {
            if (active.length === 0) {
                grid.innerHTML = `
                <div class="empty-wrap">
                    <div class="empty-icon-big">💳</div>
                    <div class="empty-title">Chưa có ví nào</div>
                    <div class="empty-sub">Tạo ví đầu tiên để quản lý tài chính theo từng nguồn tiền</div>
                    <button class="btn-primary" id="btnOpenCreateEmpty">+ Thêm ví đầu tiên</button>
                </div>`;
                document.getElementById('btnOpenCreateEmpty')?.addEventListener('click', openCreate);
            } else {
                grid.innerHTML = active.map(walletCardHTML).join('');
                // Bind delete buttons (event delegation)
                grid.addEventListener('click', gridClickHandler, { once: true });
            }
        }

        // Re-bind every reload because grid is replaced
        bindGridDeleteButtons();

        const inactiveSection = document.getElementById('inactiveSection');
        if (inactiveSection) {
            if (inactive.length > 0) {
                inactiveSection.innerHTML = `
                <div class="inactive-section">
                    <div class="inactive-hdr" id="inactiveHdr">
                        <span>🗄</span>
                        <span>Ví đã ẩn (${inactive.length})</span>
                        <span id="inactive-arrow" style="margin-left:auto;">▼</span>
                    </div>
                    <div class="inactive-list" id="inactive-list" style="display:none;">
                        ${inactive.map(w => `
                        <div class="inactive-chip">
                            <span>${w.bieu_tuong}</span>
                            <span>${w.ten_vi}</span>
                            <span style="color:#9ca3af;font-size:12px;">${fmt(w.so_du)}đ</span>
                            <button class="restore-btn" data-restore-id="${w.id}">Khôi phục</button>
                        </div>`).join('')}
                    </div>
                </div>`;
                document.getElementById('inactiveHdr')?.addEventListener('click', toggleInactive);
                inactiveSection.querySelectorAll('[data-restore-id]').forEach(btn => {
                    btn.addEventListener('click', () => restoreWallet(btn.dataset.restoreId));
                });
            } else {
                inactiveSection.innerHTML = '';
            }
        }
    }

    // ── Delete buttons (re-bind after each load) ──────────
    function bindGridDeleteButtons() {
        document.getElementById('walletGrid')?.querySelectorAll('[data-delete-id]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                deleteWallet(this.dataset.deleteId);
            });
        });
    }

    function gridClickHandler() {} // placeholder, binding done in bindGridDeleteButtons

    // ── Delete wallet ─────────────────────────────────────
    async function deleteWallet(id) {
        if (!confirm('Xóa/ẩn ví này?')) return;
        try {
            const res = await apiFetch(API.destroy(id), { method: 'DELETE' });
            if (res.__status >= 200 && res.__status < 300) {
                pageToast('Đã xóa/ẩn ví');
                loadPage();
            } else {
                const msg = res.errors
                    ? Object.values(res.errors).flat().join(' • ')
                    : (res.message || 'Không thể xóa ví này');
                pageToast(msg, 'error');
            }
        } catch {
            pageToast('Không thể kết nối đến server', 'error');
        }
    }

    // ── Restore wallet ────────────────────────────────────
    async function restoreWallet(id) {
        try {
            const res = await apiFetch(API.restore(id), { method: 'POST' });
            if (res.__status >= 200 && res.__status < 300) {
                pageToast('Đã khôi phục ví');
                loadPage();
            } else {
                const msg = res.errors
                    ? Object.values(res.errors).flat().join(' • ')
                    : (res.message || 'Có lỗi xảy ra');
                pageToast(msg, 'error');
            }
        } catch {
            pageToast('Không thể kết nối đến server', 'error');
        }
    }

    // ── Open / close modals ───────────────────────────────
    function openCreate() {
        clearFormAlert('createFormAlert');
        resetCreateForm();
        document.getElementById('createModal').classList.add('active');
    }
    function closeCreate() {
        document.getElementById('createModal').classList.remove('active');
    }
    function closeEdit() {
        document.getElementById('editModal').classList.remove('active');
    }

    function resetCreateForm() {
        document.getElementById('createTenVi').value = '';
        document.getElementById('createSoDu').value = '0';
        document.getElementById('createMoTa').value = '';
        createEmoji = '💰';
        document.getElementById('createEmoji').value = '💰';
        // Reset loai
        document.querySelectorAll('#createLoaiGrid .loai-card').forEach(c => {
            c.classList.toggle('selected', c.dataset.val === 'tien_mat');
            const r = c.querySelector('input');
            if (r) r.checked = c.dataset.val === 'tien_mat';
        });
        // Reset emoji
        document.querySelectorAll('#createEmojiGrid .emoji-btn').forEach(b => {
            b.classList.toggle('selected', b.dataset.emoji === '💰');
        });
        // Update con lai
        updateConLai(0);
    }

    function updateConLai(soDuVal) {
        const conLaiMax = summaryData.con_lai || 0;
        const remaining = conLaiMax - soDuVal;
        const conEl = document.getElementById('conLai');
        const hint  = document.getElementById('soDuHint');
        if (conEl) {
            conEl.textContent = (remaining < 0 ? '-' : '') + fmt(Math.abs(remaining)) + 'đ';
            conEl.style.color = remaining < 0 ? '#ef4444' : '#10b981';
        }
        if (hint) hint.style.color = remaining < 0 ? '#ef4444' : '#9ca3af';
    }

    // ── Submit Create ─────────────────────────────────────
    async function submitCreate() {
        clearFormAlert('createFormAlert');
        const loaiEl = document.querySelector('#createLoaiGrid .loai-card.selected input');
        const tenVi  = document.getElementById('createTenVi').value.trim();

        if (!tenVi) {
            showFormAlert('createFormAlert', 'Vui lòng nhập tên ví');
            document.getElementById('createTenVi').focus();
            return;
        }

        const body = {
            loai_vi:        loaiEl?.value || 'tien_mat',
            ten_vi:         tenVi,
            so_du_ban_dau:  parseFloat(document.getElementById('createSoDu').value) || 0,
            don_vi_tien_te: document.getElementById('createDonVi').value,
            bieu_tuong:     createEmoji,
            mo_ta:          document.getElementById('createMoTa').value.trim(),
        };

        const btn = document.getElementById('btnSubmitCreate');
        btn.disabled = true;
        btn.innerHTML = `<svg style="width:14px;height:14px;animation:spin .6s linear infinite" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Đang tạo...`;

        try {
            const res = await apiFetch(API.store, { method: 'POST', body: JSON.stringify(body) });
            if (res.id) {
                closeCreate();
                pageToast('Tạo ví thành công!');
                loadPage();
            } else {
                // Lỗi từ server — hiện TRONG form
                const msg = res.errors
                    ? Object.values(res.errors).flat().join(' • ')
                    : (res.message || 'Có lỗi xảy ra');
                showFormAlert('createFormAlert', msg);
            }
        } catch {
            showFormAlert('createFormAlert', 'Không thể kết nối đến server');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Tạo ví';
        }
    }

    // ── Submit Edit ───────────────────────────────────────
    async function submitEdit() {
        clearFormAlert('editFormAlert');
        const id     = document.getElementById('editWalletId').value;
        const loaiEl = document.querySelector('#editLoaiGrid .loai-card.selected input');
        const tenVi  = document.getElementById('editTenVi').value.trim();

        if (!tenVi) {
            showFormAlert('editFormAlert', 'Vui lòng nhập tên ví');
            document.getElementById('editTenVi').focus();
            return;
        }

        const body = {
            loai_vi:        loaiEl?.value,
            ten_vi:         tenVi,
            don_vi_tien_te: document.getElementById('editDonVi').value,
            bieu_tuong:     editEmoji,
            mo_ta:          document.getElementById('editMoTa').value.trim(),
        };

        const btn = document.getElementById('btnSubmitEdit');
        btn.disabled = true;
        btn.innerHTML = `<svg style="width:14px;height:14px;animation:spin .6s linear infinite" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Đang lưu...`;

        try {
            const res = await apiFetch(API.update(id), { method: 'PUT', body: JSON.stringify(body) });
            if (res.id) {
                closeEdit();
                pageToast('Cập nhật ví thành công!');
                loadPage();
            } else {
                const msg = res.errors
                    ? Object.values(res.errors).flat().join(' • ')
                    : (res.message || 'Có lỗi xảy ra');
                showFormAlert('editFormAlert', msg);
            }
        } catch {
            showFormAlert('editFormAlert', 'Không thể kết nối đến server');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Lưu thay đổi';
        }
    }

    // ── Open edit modal with data ─────────────────────────
    function openEditModal(wallet) {
        clearFormAlert('editFormAlert');
        document.getElementById('editWalletId').value = wallet.id;
        document.getElementById('editTenVi').value    = wallet.ten_vi;
        document.getElementById('editMoTa').value     = wallet.mo_ta || '';
        document.getElementById('editDonVi').value    = wallet.don_vi_tien_te;

        editEmoji = wallet.bieu_tuong;
        document.getElementById('editEmoji').value = editEmoji;

        // Set loai
        document.querySelectorAll('#editLoaiGrid .loai-card').forEach(c => {
            const match = c.dataset.val === wallet.loai_vi;
            c.classList.toggle('selected', match);
            const r = c.querySelector('input');
            if (r) r.checked = match;
        });

        // Set emoji
        document.querySelectorAll('#editEmojiGrid .emoji-btn').forEach(b => {
            b.classList.toggle('selected', b.dataset.emoji === wallet.bieu_tuong);
        });

        document.getElementById('editModal').classList.add('active');
    }

    // ── Toggle inactive list ──────────────────────────────
    function toggleInactive() {
        const list  = document.getElementById('inactive-list');
        const arrow = document.getElementById('inactive-arrow');
        if (!list) return;
        const shown = list.style.display !== 'none';
        list.style.display  = shown ? 'none' : 'flex';
        if (arrow) arrow.textContent = shown ? '▼' : '▲';
    }

    // ── Loai card selection ───────────────────────────────
    function bindLoaiGrid(gridId) {
        document.getElementById(gridId)?.querySelectorAll('.loai-card').forEach(card => {
            card.addEventListener('click', function () {
                document.querySelectorAll(`#${gridId} .loai-card`).forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                const r = this.querySelector('input');
                if (r) r.checked = true;
            });
        });
    }

    // ── Emoji grid selection ──────────────────────────────
    function bindEmojiGrid(gridId, onSelect) {
        document.getElementById(gridId)?.querySelectorAll('.emoji-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll(`#${gridId} .emoji-btn`).forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                onSelect(this.dataset.emoji);
            });
        });
    }

    // ── Bind modal buttons ────────────────────────────────
    document.getElementById('btnOpenCreate')?.addEventListener('click', openCreate);
    document.getElementById('btnCloseCreate')?.addEventListener('click', closeCreate);
    document.getElementById('btnCancelCreate')?.addEventListener('click', closeCreate);
    document.getElementById('btnSubmitCreate')?.addEventListener('click', submitCreate);

    document.getElementById('btnCloseEdit')?.addEventListener('click', closeEdit);
    document.getElementById('btnCancelEdit')?.addEventListener('click', closeEdit);
    document.getElementById('btnSubmitEdit')?.addEventListener('click', submitEdit);

    // Close on overlay click
    document.querySelectorAll('.modal-overlay').forEach(o => {
        o.addEventListener('click', e => {
            if (e.target === o) o.classList.remove('active');
        });
    });

    // Close on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(o => o.classList.remove('active'));
        }
    });

    // Loai grids
    bindLoaiGrid('createLoaiGrid');
    bindLoaiGrid('editLoaiGrid');

    // Emoji grids
    bindEmojiGrid('createEmojiGrid', emoji => {
        createEmoji = emoji;
        document.getElementById('createEmoji').value = emoji;
    });
    bindEmojiGrid('editEmojiGrid', emoji => {
        editEmoji = emoji;
        document.getElementById('editEmoji').value = emoji;
    });

    // So du hint
    document.getElementById('createSoDu')?.addEventListener('input', function () {
        updateConLai(parseFloat(this.value) || 0);
    });

    // ── Init ──────────────────────────────────────────────
    loadPage();

    // Cleanup on SPA navigation
    window.addEventListener('spa:navigated', function cleanup() {
        window.__walletIndexInit = false;
        window.removeEventListener('spa:navigated', cleanup);
    }, { once: true });
})();
</script>
@endsection
