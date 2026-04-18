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
}
.total-card::after {
    content: ''; position: absolute; bottom: -80px; left: 30%;
    width: 180px; height: 180px;
    background: rgba(255,255,255,0.04); border-radius: 50%;
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
}
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

.alert {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px; border-radius: var(--radius-sm);
    font-size: 14px; font-weight: 500; margin-bottom: 20px;
}
.alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
.alert-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--danger); }
.alert-info    { background: #dbeafe; color: #1e40af; border-left: 4px solid var(--primary); }

.btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; font-size: 14px; font-weight: 700;
    border: none; cursor: pointer; text-decoration: none; transition: opacity 0.2s;
    white-space: nowrap;
}
.btn-primary:hover { opacity: 0.88; }
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
    max-height: 90vh; overflow-y: auto;
}
.modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
body.dark .modal-box { background: #191d27; }
.modal-hdr {
    padding: 22px 28px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex; justify-content: space-between; align-items: center;
    position: sticky; top: 0; z-index: 1;
}
.modal-hdr-title { font-size: 17px; font-weight: 800; color: white; display: flex; align-items: center; gap: 10px; }
.modal-close-btn {
    background: rgba(255,255,255,0.2); border: none; border-radius: 8px;
    width: 32px; height: 32px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 18px; transition: background 0.2s;
}
.modal-close-btn:hover { background: rgba(255,255,255,0.32); }
.modal-body { padding: 24px 28px; }

.form-group { margin-bottom: 16px; }
.form-label { font-size: 13px; font-weight: 700; color: #374151; display: block; margin-bottom: 7px; }
body.dark .form-label { color: #9ca3af; }
.required { color: var(--danger); }
.form-ctrl {
    width: 100%; padding: 10px 14px;
    border: 2px solid #e5e7eb; border-radius: var(--radius-sm);
    font-size: 14px; background: #f9fafb; color: #1f2937;
    outline: none; transition: all 0.2s;
}
.form-ctrl:focus { border-color: var(--primary); background: white; }
body.dark .form-ctrl { background: #141820; border-color: rgba(255,255,255,0.1); color: #e5e7eb; }

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
    display: flex; gap: 10px;
    position: sticky; bottom: 0;
    background: white; z-index: 1;
}
body.dark .modal-foot { border-color: rgba(255,255,255,0.06); background: #191d27; }
.btn-cancel {
    flex: 1; padding: 10px; border-radius: var(--radius-sm);
    background: #f3f4f6; border: 2px solid #e5e7eb;
    color: #6b7280; font-size: 14px; font-weight: 600; cursor: pointer;
}
.btn-cancel:hover { background: #e5e7eb; }
.modal-foot .btn-primary { flex: 2; justify-content: center; padding: 10px; }

.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 8px;
}
@keyframes shimmer { 0% { background-position: 200% 0 } 100% { background-position: -200% 0 } }

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
        <button class="btn-primary" onclick="openCreate()">+ Thêm ví</button>
    </div>
</div>

<div id="alertContainer"></div>

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

<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">💳 Thêm ví mới</div>
            <button class="modal-close-btn" onclick="closeModal('createModal')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Loại ví <span class="required">*</span></label>
                <div class="loai-grid" id="createLoaiGrid">
                    <label class="loai-card selected" onclick="selectLoai(this)" data-val="tien_mat">
                        <input type="radio" name="loai_vi" value="tien_mat" checked>
                        <div class="loai-emoji">💵</div>
                        <div class="loai-name">Tiền mặt</div>
                    </label>
                    <label class="loai-card" onclick="selectLoai(this)" data-val="ngan_hang">
                        <input type="radio" name="loai_vi" value="ngan_hang">
                        <div class="loai-emoji">🏦</div>
                        <div class="loai-name">Ngân hàng</div>
                    </label>
                    <label class="loai-card" onclick="selectLoai(this)" data-val="vi_dien_tu">
                        <input type="radio" name="loai_vi" value="vi_dien_tu">
                        <div class="loai-emoji">📱</div>
                        <div class="loai-name">Ví điện tử</div>
                    </label>
                    <label class="loai-card" onclick="selectLoai(this)" data-val="the_tin_dung">
                        <input type="radio" name="loai_vi" value="the_tin_dung">
                        <div class="loai-emoji">💳</div>
                        <div class="loai-name">Thẻ tín dụng</div>
                    </label>
                    <label class="loai-card" onclick="selectLoai(this)" data-val="dau_tu">
                        <input type="radio" name="loai_vi" value="dau_tu">
                        <div class="loai-emoji">📈</div>
                        <div class="loai-name">Đầu tư</div>
                    </label>
                    <label class="loai-card" onclick="selectLoai(this)" data-val="khac">
                        <input type="radio" name="loai_vi" value="khac">
                        <div class="loai-emoji">🗂</div>
                        <div class="loai-name">Khác</div>
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
                    <input id="createSoDu" type="number" class="form-ctrl" placeholder="0" min="0" step="1000" value="0" oninput="checkSoDuCreate(this.value)">
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
                    <button type="button" class="emoji-btn selected" onclick="selectEmoji(this,'💰')">💰</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'💵')">💵</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'💴')">💴</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'💶')">💶</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'🏦')">🏦</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'💳')">💳</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'📱')">📱</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'💹')">💹</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'📈')">📈</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'🪙')">🪙</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'💎')">💎</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'🏧')">🏧</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'🛍')">🛍</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'🎁')">🎁</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'🗂')">🗂</button>
                    <button type="button" class="emoji-btn" onclick="selectEmoji(this,'📊')">📊</button>
                </div>
                <input type="hidden" id="createEmoji" value="💰">
            </div>
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <input id="createMoTa" class="form-ctrl" placeholder="Ghi chú thêm..." maxlength="500">
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeModal('createModal')">Hủy</button>
            <button class="btn-primary" onclick="submitCreate()">Tạo ví</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">✏️ Chỉnh sửa ví</div>
            <button class="modal-close-btn" onclick="closeModal('editModal')">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editWalletId">
            <div class="form-group">
                <label class="form-label">Loại ví <span class="required">*</span></label>
                <div class="loai-grid" id="editLoaiGrid">
                    <label class="loai-card" onclick="selectLoaiEdit(this)" data-val="tien_mat"><input type="radio" name="edit_loai_vi" value="tien_mat"><div class="loai-emoji">💵</div><div class="loai-name">Tiền mặt</div></label>
                    <label class="loai-card" onclick="selectLoaiEdit(this)" data-val="ngan_hang"><input type="radio" name="edit_loai_vi" value="ngan_hang"><div class="loai-emoji">🏦</div><div class="loai-name">Ngân hàng</div></label>
                    <label class="loai-card" onclick="selectLoaiEdit(this)" data-val="vi_dien_tu"><input type="radio" name="edit_loai_vi" value="vi_dien_tu"><div class="loai-emoji">📱</div><div class="loai-name">Ví điện tử</div></label>
                    <label class="loai-card" onclick="selectLoaiEdit(this)" data-val="the_tin_dung"><input type="radio" name="edit_loai_vi" value="the_tin_dung"><div class="loai-emoji">💳</div><div class="loai-name">Thẻ tín dụng</div></label>
                    <label class="loai-card" onclick="selectLoaiEdit(this)" data-val="dau_tu"><input type="radio" name="edit_loai_vi" value="dau_tu"><div class="loai-emoji">📈</div><div class="loai-name">Đầu tư</div></label>
                    <label class="loai-card" onclick="selectLoaiEdit(this)" data-val="khac"><input type="radio" name="edit_loai_vi" value="khac"><div class="loai-emoji">🗂</div><div class="loai-name">Khác</div></label>
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
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'💰')">💰</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'💵')">💵</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'💴')">💴</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'💶')">💶</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'🏦')">🏦</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'💳')">💳</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'📱')">📱</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'💹')">💹</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'📈')">📈</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'🪙')">🪙</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'💎')">💎</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'🏧')">🏧</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'🛍')">🛍</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'🎁')">🎁</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'🗂')">🗂</button>
                    <button type="button" class="emoji-btn" onclick="selectEmojiEdit(this,'📊')">📊</button>
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
            <button class="btn-cancel" onclick="closeModal('editModal')">Hủy</button>
            <button class="btn-primary" onclick="submitEdit()">Lưu thay đổi</button>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
const API = {
    wallets:  '/api/v1/money-wallets',
    summary:  '/api/v1/money-wallets/summary',
    restore:  (id) => `/api/v1/money-wallets/${id}/restore`,
    destroy:  (id) => `/api/v1/money-wallets/${id}`,
    update:   (id) => `/api/v1/money-wallets/${id}`,
    store:    '/api/v1/money-wallets',
};

let summaryData = {};
let createEmojiSelected = '💰';
let editEmojiSelected = '';

async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', ...options.headers },
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

function walletCardHTML(w) {
    const color   = WALLET_COLORS[w.loai_vi] || '#6b7280';
    const bgLight = color + '18';
    const label   = WALLET_LABELS[w.loai_vi] || w.loai_vi;
    const bal     = Number(w.so_du);
    const created = new Date(w.created_at).toLocaleDateString('vi-VN');
    return `
    <a href="/money-wallets/${w.id}" class="wallet-card">
        <div class="wallet-card-delete" onclick="event.preventDefault(); event.stopPropagation(); deleteWalletFromIndex(${w.id})">🗑</div>
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

async function loadPage() {
    const [walletRes, summaryRes] = await Promise.all([
        apiFetch(API.wallets),
        apiFetch(API.summary),
    ]);

    summaryData = summaryRes;
    const { tong_tai_san, tong_thu, tong_chi, tong_vi, tong_so_du_vi, con_lai } = summaryRes;

    const amountEl = document.getElementById('totalAmount');
    amountEl.innerHTML = `
        ${tong_tai_san >= 0 ? '+' : ''}${fmt(tong_tai_san)}
        <span style="font-size:20px;font-weight:600;opacity:.7;">VND</span>`;
    amountEl.style.color = tong_tai_san >= 0 ? 'white' : '#fca5a5';

    document.getElementById('totalBreakdown').innerHTML = `
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

    document.getElementById('conLai').textContent = fmt(con_lai) + 'đ';

    const _walletList = Array.isArray(walletRes) ? walletRes : (walletRes.data ?? []);
    const active   = _walletList.filter(w => w.trang_thai !== 'khong_hoat_dong');
    const inactive = _walletList.filter(w => w.trang_thai === 'khong_hoat_dong');

    const grid = document.getElementById('walletGrid');
    if (active.length === 0) {
        grid.innerHTML = `
        <div class="empty-wrap">
            <div class="empty-icon-big">💳</div>
            <div class="empty-title">Chưa có ví nào</div>
            <div class="empty-sub">Tạo ví đầu tiên để quản lý tài chính theo từng nguồn tiền</div>
            <button class="btn-primary" onclick="openCreate()">+ Thêm ví đầu tiên</button>
        </div>`;
    } else {
        grid.innerHTML = active.map(walletCardHTML).join('');
    }

    const inactiveSection = document.getElementById('inactiveSection');
    if (inactive.length > 0) {
        inactiveSection.innerHTML = `
        <div class="inactive-section">
            <div class="inactive-hdr" onclick="toggleInactive()">
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
                    <button class="restore-btn" onclick="restoreWallet(${w.id})">Khôi phục</button>
                </div>`).join('')}
            </div>
        </div>`;
    } else {
        inactiveSection.innerHTML = '';
    }
}

async function submitCreate() {
    const loaiEl = document.querySelector('#createLoaiGrid .loai-card.selected input');
    const body = {
        loai_vi:       loaiEl?.value || 'tien_mat',
        ten_vi:        document.getElementById('createTenVi').value.trim(),
        so_du_ban_dau: parseFloat(document.getElementById('createSoDu').value) || 0,
        don_vi_tien_te: document.getElementById('createDonVi').value,
        bieu_tuong:    createEmojiSelected,
        mo_ta:         document.getElementById('createMoTa').value.trim(),
    };
    if (!body.ten_vi) { showAlert('Vui lòng nhập tên ví', 'error'); return; }

    const res = await apiFetch(API.store, { method: 'POST', body: JSON.stringify(body) });
    if (res.id) {
        closeModal('createModal');
        showAlert('Tạo ví thành công');
        loadPage();
    } else {
        showAlert(res.message || 'Có lỗi xảy ra', 'error');
    }
}

async function submitEdit() {
    const id = document.getElementById('editWalletId').value;
    const loaiEl = document.querySelector('#editLoaiGrid .loai-card.selected input');
    const body = {
        loai_vi:       loaiEl?.value,
        ten_vi:        document.getElementById('editTenVi').value.trim(),
        don_vi_tien_te: document.getElementById('editDonVi').value,
        bieu_tuong:    editEmojiSelected,
        mo_ta:         document.getElementById('editMoTa').value.trim(),
    };
    if (!body.ten_vi) { showAlert('Vui lòng nhập tên ví', 'error'); return; }

    const res = await apiFetch(API.update(id), { method: 'PUT', body: JSON.stringify(body) });
    if (res.id) {
        closeModal('editModal');
        showAlert('Cập nhật ví thành công');
        loadPage();
    } else {
        showAlert(res.message || 'Có lỗi xảy ra', 'error');
    }
}

async function restoreWallet(id) {
    const res = await apiFetch(API.restore(id), { method: 'POST' });
    if (res.success) { showAlert('Đã khôi phục ví'); loadPage(); }
    else showAlert(res.message || 'Có lỗi xảy ra', 'error');
}

function openCreate() { document.getElementById('createModal').classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

function selectLoai(el) {
    document.querySelectorAll('#createLoaiGrid .loai-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
}
function selectLoaiEdit(el) {
    document.querySelectorAll('#editLoaiGrid .loai-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
}
function selectEmoji(btn, emoji) {
    document.querySelectorAll('#createEmojiGrid .emoji-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    createEmojiSelected = emoji;
    document.getElementById('createEmoji').value = emoji;
}
function selectEmojiEdit(btn, emoji) {
    document.querySelectorAll('#editEmojiGrid .emoji-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    editEmojiSelected = emoji;
    document.getElementById('editEmoji').value = emoji;
}

function toggleInactive() {
    const list  = document.getElementById('inactive-list');
    const arrow = document.getElementById('inactive-arrow');
    const shown = list.style.display !== 'none';
    list.style.display = shown ? 'none' : 'flex';
    arrow.textContent  = shown ? '▼' : '▲';
}

function checkSoDuCreate(val) {
    const conLaiMax = summaryData.con_lai || 0;
    const v = parseFloat(val) || 0;
    const hint  = document.getElementById('soDuHint');
    const conEl = document.getElementById('conLai');
    if (!hint) return;
    const remaining = conLaiMax - v;
    conEl.textContent = (remaining < 0 ? '-' : '') + fmt(Math.abs(remaining)) + 'đ';
    hint.style.color  = v > conLaiMax ? '#ef4444' : '#9ca3af';
    conEl.style.color = v > conLaiMax ? '#ef4444' : '#10b981';
}

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('active'); });
});

loadPage();

async function deleteWalletFromIndex(id) {
    if (!confirm('Xóa/ẩn ví này?')) return;
    const res = await apiFetch(API.destroy(id), { method: 'DELETE' });
    if (res.success) {
        showAlert('Đã xóa/ẩn ví');
        loadPage();
    } else {
        showAlert(res.message || 'Có lỗi xảy ra', 'error');
    }
}
</script>
@endsection
