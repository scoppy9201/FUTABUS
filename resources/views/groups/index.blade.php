@extends('layouts.app')
@section('title', 'Chia tiền nhóm')
@section('content')
<style>
:root {
    --primary: #4a90e2;
    --primary-dark: #2a5298;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --dark-bg: #0f1217;
    --dark-card: #191d27;
    --dark-border: rgba(255,255,255,0.06);
    --radius: 16px;
    --radius-sm: 10px;
    --shadow: 0 2px 8px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
}

/* ── Page wrapper ── */
.page-wrap {
    display: flex; flex-direction: column;
    min-height: 100vh; padding: 20px;
    background: #f3f4f6; width: 100%; max-width: 100%;
    box-sizing: border-box; overflow-x: hidden;
}
body.dark .page-wrap { background: var(--dark-bg); }

/* ── Header ── */
.pg-hdr {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 28px; padding: 22px 28px;
    background: white; border-radius: var(--radius); box-shadow: var(--shadow);
    border: 1px solid rgba(226,232,240,0.8);
}
body.dark .pg-hdr {
    background: var(--dark-card);
    border-color: var(--dark-border);
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}

.pg-title {
    display: flex; align-items: center; gap: 14px;
    font-size: 22px; font-weight: 800; color: #1f2937;
}
body.dark .pg-title { color: #e5e7eb; }

.pg-title > div:last-child > div:last-child { color: #6b7280; }
body.dark .pg-title > div:last-child > div:last-child { color: #6b7280; }

.pg-icon {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; box-shadow: 0 4px 14px rgba(74,144,226,0.35);
    color: white; flex-shrink: 0;
}

/* ── Groups grid ── */
.groups-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(280px, 100%), 1fr));
    gap: 20px; width: 100%; box-sizing: border-box;
}

/* ── Group card ── */
.group-card {
    background: white; border-radius: var(--radius);
    box-shadow: var(--shadow); border: 1px solid rgba(255,255,255,0.8);
    transition: var(--transition); overflow: hidden;
    text-decoration: none; display: flex; flex-direction: column;
    position: relative; min-width: 0;
}
.group-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.1);
    border-color: var(--primary);
}
body.dark .group-card {
    background: var(--dark-card);
    border-color: var(--dark-border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}
body.dark .group-card:hover {
    box-shadow: 0 12px 36px rgba(0,0,0,0.45);
    border-color: var(--primary);
}
.group-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    transform: scaleX(0); transform-origin: left; transition: transform 0.3s ease;
}
.group-card:hover::before { transform: scaleX(1); }

.gc-body { padding: 22px 22px 18px; flex: 1; min-width: 0; }
.gc-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }

.gc-icon {
    width: 50px; height: 50px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; flex-shrink: 0; color: white;
    box-shadow: 0 4px 12px rgba(74,144,226,0.3);
}
.gc-icon.mode-balance { background: linear-gradient(135deg,#10b981,#059669); box-shadow: 0 4px 12px rgba(16,185,129,0.35); }
.gc-icon.mode-expense { background: linear-gradient(135deg,#f59e0b,#d97706); box-shadow: 0 4px 12px rgba(245,158,11,0.35); }
.gc-icon.mode-both    { background: linear-gradient(135deg,var(--primary),var(--primary-dark)); }

/* Badges */
.gc-badges { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; flex-shrink: 0; margin-left: 8px; }
.gc-badge {
    padding: 4px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 700; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 4px;
}
.gc-badge.admin  { background: rgba(74,144,226,0.12); color: var(--primary); }
.gc-badge.member { background: rgba(107,114,128,0.1); color: #6b7280; }
.gc-badge.mode-b { background: rgba(16,185,129,0.1); color: #059669; }
.gc-badge.mode-e { background: rgba(245,158,11,0.1); color: #d97706; }
.gc-badge.mode-m { background: rgba(74,144,226,0.1); color: var(--primary); }

body.dark .gc-badge.admin  { background: rgba(74,144,226,0.18); color: #7db8f7; }
body.dark .gc-badge.member { background: rgba(107,114,128,0.18); color: #9ca3af; }
body.dark .gc-badge.mode-b { background: rgba(16,185,129,0.18); color: #34d399; }
body.dark .gc-badge.mode-e { background: rgba(245,158,11,0.18); color: #fbbf24; }
body.dark .gc-badge.mode-m { background: rgba(74,144,226,0.18); color: #7db8f7; }

/* Name & desc */
.gc-name {
    font-size: 17px; font-weight: 800; color: #1f2937;
    margin-bottom: 4px; line-height: 1.3;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
body.dark .gc-name { color: #f3f4f6; }

.gc-desc {
    font-size: 13px; color: #6b7280; line-height: 1.5;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
body.dark .gc-desc { color: #6b7280; }

/* Members row */
.gc-members {
    display: flex; align-items: center; gap: 8px;
    margin-top: 16px; padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}
body.dark .gc-members { border-color: var(--dark-border); }

.gc-avatars { display: flex; }
.gc-av {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg,var(--primary),var(--primary-dark));
    border: 2px solid white; color: white;
    font-size: 11px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    margin-left: -8px; flex-shrink: 0;
}
body.dark .gc-av { border-color: var(--dark-card); }
.gc-av:first-child { margin-left: 0; }
.gc-av.extra { background: #e5e7eb; color: #6b7280; }
body.dark .gc-av.extra { background: #2a2f3e; color: #9ca3af; }

.gc-member-count { font-size: 12px; color: #9ca3af; font-weight: 500; }

/* Card footer */
.gc-footer {
    padding: 14px 22px; background: #fafafa;
    border-top: 1px solid #f3f4f6;
    display: flex; justify-content: space-between; align-items: center;
}
body.dark .gc-footer {
    background: rgba(255,255,255,0.02);
    border-color: var(--dark-border);
}

.gc-date { font-size: 12px; color: #9ca3af; display: flex; align-items: center; gap: 5px; }

.gc-arrow {
    width: 28px; height: 28px; border-radius: 8px;
    background: rgba(74,144,226,0.08); color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    transition: var(--transition);
}
body.dark .gc-arrow { background: rgba(74,144,226,0.12); }
.group-card:hover .gc-arrow { background: var(--primary); color: white; }

/* ── Skeleton ── */
.skeleton {
    background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);
    background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 8px;
}
body.dark .skeleton {
    background: linear-gradient(90deg,#1e2333 25%,#262d3d 50%,#1e2333 75%);
    background-size: 200% 100%;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.skeleton-card {
    background: white; border-radius: var(--radius);
    box-shadow: var(--shadow); padding: 22px; min-height: 200px;
    border: 1px solid rgba(226,232,240,0.8);
}
body.dark .skeleton-card {
    background: var(--dark-card);
    border-color: var(--dark-border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}

/* ── Empty state ── */
.empty-wrap {
    grid-column: 1/-1; text-align: center;
    padding: 80px 20px; background: white;
    border-radius: var(--radius); box-shadow: var(--shadow);
    border: 1px solid rgba(226,232,240,0.8);
}
body.dark .empty-wrap {
    background: var(--dark-card);
    border-color: var(--dark-border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}

.empty-icon-big {
    width: 90px; height: 90px; border-radius: 24px;
    background: rgba(74,144,226,0.08);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px; color: var(--primary);
}
body.dark .empty-icon-big { background: rgba(74,144,226,0.12); }

.empty-wrap h3 { font-size: 20px; font-weight: 700; color: #374151; margin-bottom: 8px; }
body.dark .empty-wrap h3 { color: #e5e7eb; }
.empty-wrap p { font-size: 14px; color: #9ca3af; margin-bottom: 28px; }

/* ── Buttons ── */
.btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: var(--radius-sm);
    background: linear-gradient(135deg,var(--primary),var(--primary-dark));
    color: white; font-size: 14px; font-weight: 700;
    border: none; cursor: pointer; text-decoration: none;
    transition: opacity 0.2s, transform 0.2s; white-space: nowrap;
}
.btn-primary:hover { opacity: 0.88; transform: translateY(-1px); }

/* ── Modal overlay ── */
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
    width: 100%; max-width: 520px; max-height: 90vh;
    overflow: hidden; display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    transform: scale(0.95) translateY(10px);
    transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
    border: 1px solid rgba(226,232,240,0.5);
}
.modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
body.dark .modal-box {
    background: var(--dark-card);
    border-color: var(--dark-border);
    box-shadow: 0 20px 60px rgba(0,0,0,0.55);
}

.modal-hdr {
    padding: 24px 28px;
    background: linear-gradient(135deg,var(--primary),var(--primary-dark));
    display: flex; justify-content: space-between; align-items: center;
}
.modal-hdr-title { font-size: 18px; font-weight: 800; color: white; display: flex; align-items: center; gap: 10px; }
.modal-close {
    width: 34px; height: 34px; border-radius: 8px;
    background: rgba(255,255,255,0.2); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 18px; transition: background 0.2s;
}
.modal-close:hover { background: rgba(255,255,255,0.32); }

.modal-body { padding: 28px; overflow-y: auto; flex: 1; background: white; }
body.dark .modal-body { background: var(--dark-card); }

/* Form elements */
.form-group { margin-bottom: 18px; }
.form-label { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 7px; display: block; }
body.dark .form-label { color: #9ca3af; }
.required { color: var(--danger); }

.form-ctrl {
    width: 100%; padding: 11px 14px;
    border: 2px solid #e5e7eb; border-radius: var(--radius-sm);
    font-size: 14px; background: #f9fafb; color: #1f2937;
    transition: border-color 0.2s, background 0.2s; outline: none;
    box-sizing: border-box;
}
.form-ctrl:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(74,144,226,0.1); }
body.dark .form-ctrl {
    background: #0f1217;
    border-color: rgba(255,255,255,0.1);
    color: #e5e7eb;
}
body.dark .form-ctrl:focus {
    border-color: var(--primary);
    background: #141820;
    box-shadow: 0 0 0 3px rgba(74,144,226,0.15);
}
body.dark .form-ctrl::placeholder { color: #4b5563; }

/* Mode selector */
.mode-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; margin-top: 6px; }
.mode-card {
    border: 2px solid #e5e7eb; border-radius: 12px;
    padding: 14px 10px; text-align: center; cursor: pointer;
    transition: var(--transition); position: relative;
    background: #fafafa;
}
.mode-card:hover { border-color: var(--primary); background: rgba(74,144,226,0.03); }
.mode-card.selected { border-color: var(--primary); background: rgba(74,144,226,0.06); }
body.dark .mode-card {
    border-color: rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.02);
}
body.dark .mode-card:hover { border-color: var(--primary); background: rgba(74,144,226,0.08); }
body.dark .mode-card.selected { border-color: var(--primary); background: rgba(74,144,226,0.12); }

.mode-card input { position: absolute; opacity: 0; pointer-events: none; }
.mode-emoji { font-size: 26px; margin-bottom: 6px; display: flex; justify-content: center; color: #374151; }
body.dark .mode-emoji { color: #c9d1e0; }
.mode-name { font-size: 12px; font-weight: 700; color: #374151; }
body.dark .mode-name { color: #e5e7eb; }
.mode-card.selected .mode-name { color: var(--primary); }
body.dark .mode-card.selected .mode-name { color: #7db8f7; }
.mode-desc { font-size: 11px; color: #9ca3af; margin-top: 2px; }
body.dark .mode-desc { color: #4b5563; }

/* Modal footer */
.modal-foot {
    padding: 18px 28px; border-top: 1px solid #f3f4f6;
    display: flex; gap: 10px; background: white;
}
body.dark .modal-foot {
    border-color: var(--dark-border);
    background: var(--dark-card);
}

.btn-cancel {
    flex: 1; padding: 11px; border-radius: var(--radius-sm);
    background: #f3f4f6; border: 2px solid #e5e7eb;
    color: #6b7280; font-size: 14px; font-weight: 600; cursor: pointer;
    transition: background 0.2s;
}
.btn-cancel:hover { background: #e5e7eb; }
body.dark .btn-cancel {
    background: rgba(255,255,255,0.04);
    border-color: rgba(255,255,255,0.1);
    color: #9ca3af;
}
body.dark .btn-cancel:hover { background: rgba(255,255,255,0.08); }

.modal-foot .btn-primary { flex: 2; justify-content: center; padding: 11px; }

@keyframes spin { to { transform: rotate(360deg); } }
@media (max-width: 768px) {
    .groups-grid { grid-template-columns: 1fr; }
    .mode-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-wrap">
    <div class="pg-hdr">
        <div class="pg-title">
            <div class="pg-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px"><circle cx="7.5" cy="6.5" r="2.5"/><path d="M2 17c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><circle cx="14" cy="6" r="2"/><path d="M18 17c0-2.5-1.8-4.2-4-4.7"/></svg>
            </div>
            <div>
                <div>Chia tiền nhóm</div>
                <div style="font-size:13px;font-weight:500;color:#6b7280;margin-top:2px;">Quản lý chi tiêu cùng gia đình &amp; bạn bè</div>
            </div>
        </div>
        <button class="btn-primary" id="btnOpenCreate">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><path d="M10 4v12M4 10h12"/></svg>
            Tạo nhóm mới
        </button>
    </div>

    <div class="groups-grid" id="groupsGrid">
        {{-- Skeleton loading --}}
        @for($i = 0; $i < 3; $i++)
        <div class="skeleton-card">
            <div style="display:flex;justify-content:space-between;margin-bottom:16px">
                <div class="skeleton" style="width:50px;height:50px;border-radius:14px"></div>
                <div style="display:flex;flex-direction:column;gap:6px">
                    <div class="skeleton" style="width:70px;height:20px"></div>
                    <div class="skeleton" style="width:90px;height:20px"></div>
                </div>
            </div>
            <div class="skeleton" style="width:60%;height:18px;margin-bottom:8px"></div>
            <div class="skeleton" style="width:90%;height:14px;margin-bottom:4px"></div>
            <div class="skeleton" style="width:70%;height:14px;margin-bottom:20px"></div>
            <div style="display:flex;gap:6px;padding-top:16px;border-top:1px solid #f3f4f6">
                <div class="skeleton" style="width:30px;height:30px;border-radius:50%"></div>
                <div class="skeleton" style="width:30px;height:30px;border-radius:50%;margin-left:-8px"></div>
                <div class="skeleton" style="width:80px;height:14px;margin-left:8px;align-self:center"></div>
            </div>
        </div>
        @endfor
    </div>
</div>

{{-- Modal tạo nhóm --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><circle cx="7.5" cy="6.5" r="2.5"/><path d="M2 17c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><circle cx="14" cy="6" r="2"/><path d="M18 17c0-2.5-1.8-4.2-4-4.7"/></svg>
                Tạo nhóm mới
            </div>
            <button class="modal-close" id="btnCloseCreate">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><path d="M5 5l10 10M15 5L5 15"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Tên nhóm <span class="required">*</span></label>
                <input id="createTenNhom" class="form-ctrl" placeholder="VD: Gia đình, Du lịch Đà Lạt..." maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <input id="createMoTa" class="form-ctrl" placeholder="Mô tả ngắn về nhóm..." maxlength="255">
            </div>
            <div class="form-group">
                <label class="form-label">Chế độ hoạt động <span class="required">*</span></label>
                <div class="mode-grid" id="createModeGrid">
                    <label class="mode-card" data-value="balance">
                        <input type="radio" name="che_do_create" value="balance">
                        <div class="mode-emoji">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:26px;height:26px"><path d="M10 3v14M4 17h12"/><path d="M4 7 2 12h4zM16 7l-2 5h4z"/><path d="M4 7h12"/></svg>
                        </div>
                        <div class="mode-name">Phân phối<br>số dư</div>
                        <div class="mode-desc">Chia lại tiền trong nhóm</div>
                    </label>
                    <label class="mode-card" data-value="expense">
                        <input type="radio" name="che_do_create" value="expense">
                        <div class="mode-emoji">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:26px;height:26px"><path d="M5 2h10a1 1 0 011 1v14l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5V3a1 1 0 011-1z"/><path d="M7.5 7h5M7.5 10h5M7.5 13h3"/></svg>
                        </div>
                        <div class="mode-name">Chia khoản<br>chi</div>
                        <div class="mode-desc">Chia tiền khi thanh toán</div>
                    </label>
                    <label class="mode-card selected" data-value="both">
                        <input type="radio" name="che_do_create" value="both" checked>
                        <div class="mode-emoji">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:26px;height:26px"><path d="M3 6h9l3-3 3 3-3 3"/><path d="M3 14h9l3-3 3 3-3 3"/><path d="M6 9l-3 3M6 11l-3-3"/></svg>
                        </div>
                        <div class="mode-name">Cả hai<br>chế độ</div>
                        <div class="mode-desc">Linh hoạt nhất</div>
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" id="btnCancelCreate">Hủy</button>
            <button class="btn-primary" id="btnSubmitCreate">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><path d="M10 4v12M4 10h12"/></svg>
                Tạo nhóm
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    // Guard: chỉ init 1 lần per navigation đảm bảo đoạn code phía dưới chỉ chạy 1 lần duy nhất
    if (window.__groupsIndexInit) return;
    window.__groupsIndexInit = true;

    // ── Helpers ──────────────────────────────────────────
    //Biến kí tự nguy hiểm thành an toàn tránh XSS
    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function formatDate(str) {
        const d = new Date(str);
        return d.toLocaleDateString('vi-VN');
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    // ── Render helpers ───────────────────────────────────
    // trả về CSS chính cho từng chế độ
    function modeClass(che_do) {
        return che_do === 'balance' ? 'mode-balance' : (che_do === 'expense' ? 'mode-expense' : 'mode-both');
    }

    // trả về text hiển thị cho user
    function modeName(che_do) {
        return che_do === 'balance' ? 'Phân phối số dư' : (che_do === 'expense' ? 'Chia khoản chi' : 'Cả hai chế độ');
    }

    // trả về class ngắn hơn(icon, màu,label)
    function modeBadgeClass(che_do) {
        return che_do === 'balance' ? 'mode-b' : (che_do === 'expense' ? 'mode-e' : 'mode-m');
    }

    function modeIconSvg(che_do) {
        if (che_do === 'balance') return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:24px;height:24px"><path d="M10 3v14M4 17h12"/><path d="M4 7 2 12h4zM16 7l-2 5h4z"/><path d="M4 7h12"/></svg>';
        if (che_do === 'expense') return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:24px;height:24px"><path d="M5 2h10a1 1 0 011 1v14l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5V3a1 1 0 011-1z"/><path d="M7.5 7h5M7.5 10h5M7.5 13h3"/></svg>';
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:24px;height:24px"><path d="M3 6h9l3-3 3 3-3 3"/><path d="M3 14h9l3-3 3 3-3 3"/><path d="M6 9l-3 3M6 11l-3-3"/></svg>';
    }


    // render html cho từng thành viên nhóm
    function buildMembersHtml(members, total) {
        const avColors = ['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
        let html = '<div class="gc-avatars">';
        (members || []).forEach((m, i) => {
            if (m.avatar) {
                const src = m.avatar.startsWith('http') ? m.avatar : '/storage/' + m.avatar;
                html += `<img src="${src}" class="gc-av" style="object-fit:cover;" alt="" onerror="this.outerHTML='<div class=gc-av style=background:${avColors[i%avColors.length]}>${esc(String(m.name||'').substring(0,2).toUpperCase())}</div>'">`;
            } else {
                html += `<div class="gc-av" style="background:${m.color || avColors[i%avColors.length]}">${esc(String(m.name||'').substring(0,2).toUpperCase())}</div>`;
            }
        });
        if ((total || 0) > 4) {
            html += `<div class="gc-av extra">+${total - 4}</div>`;
        }
        html += '</div>';
        return html;
    }

    //render ra card nhóm
    function buildCardHtml(g) {
        const roleClass  = g.la_admin ? 'admin' : 'member';
        const roleLabel  = g.la_admin ? 'Admin' : 'Thành viên';
        const roleIcon   = g.la_admin
            ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px"><path d="M3 14L6 7l4 4 4-4 3 7H3z"/><path d="M3 14h14"/><circle cx="10" cy="3.5" r="1" fill="currentColor" stroke="none"/></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px"><circle cx="10" cy="6.5" r="3"/><path d="M3.5 18c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"/></svg>';

        const calSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px"><rect x="3" y="4" width="14" height="14" rx="2"/><path d="M3 9h14M7 2v4M13 2v4"/></svg>';
        const arrowSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><path d="M8 4l6 6-6 6"/></svg>';

        return `
        <a href="/groups/${g.id}" class="group-card">
            <div class="gc-body">
                <div class="gc-top">
                    <div class="gc-icon ${modeClass(g.che_do)}">${modeIconSvg(g.che_do)}</div>
                    <div class="gc-badges">
                        <span class="gc-badge ${roleClass}">${roleIcon} ${roleLabel}</span>
                        <span class="gc-badge ${modeBadgeClass(g.che_do)}">${modeName(g.che_do)}</span>
                    </div>
                </div>
                <div class="gc-name">${esc(g.ten_nhom)}</div>
                <div class="gc-desc">${esc(g.mo_ta || 'Chưa có mô tả')}</div>
                <div class="gc-members">
                    ${buildMembersHtml(g.members, g.so_thanh_vien)}
                    <span class="gc-member-count">${g.so_thanh_vien || 0} thành viên</span>
                </div>
            </div>
            <div class="gc-footer">
                <span class="gc-date">${calSvg} ${formatDate(g.created_at)}</span>
                <div class="gc-arrow">${arrowSvg}</div>
            </div>
        </a>`;
    }

    function renderEmpty() {
        return `<div class="empty-wrap">
            <div class="empty-icon-big">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:42px;height:42px"><circle cx="7.5" cy="6.5" r="2.5"/><path d="M2 17c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><circle cx="14" cy="6" r="2"/><path d="M18 17c0-2.5-1.8-4.2-4-4.7"/></svg>
            </div>
            <h3>Chưa có nhóm nào</h3>
            <p>Tạo nhóm đầu tiên để bắt đầu chia sẻ chi tiêu cùng gia đình hoặc bạn bè</p>
            <button class="btn-primary" id="btnOpenCreateEmpty">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><path d="M10 4v12M4 10h12"/></svg>
                Tạo nhóm đầu tiên
            </button>
        </div>`;
    }

    // ── Load groups via API ──────────────────────────────
    function loadGroups() {
        const grid = document.getElementById('groupsGrid');
        if (!grid) return;

        fetch('/api/v1/groups', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
        })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(data => {
            const groups = data.groups || [];
            if (groups.length === 0) {
                grid.innerHTML = renderEmpty();
                const emptyBtn = document.getElementById('btnOpenCreateEmpty');
                emptyBtn?.addEventListener('click', openCreateModal);
                return;
            }
            grid.innerHTML = groups.map(buildCardHtml).join('');
        })
        .catch(() => {
            if (grid) grid.innerHTML = renderEmpty();
        });
    }

    // ── Modal logic ──────────────────────────────────────
    function openCreateModal() {
        document.getElementById('createModal')?.classList.add('active');
    }
    function closeCreateModal() {
        document.getElementById('createModal')?.classList.remove('active');
        // Reset form
        const tenEl = document.getElementById('createTenNhom');
        const moTaEl = document.getElementById('createMoTa');
        if (tenEl) tenEl.value = '';
        if (moTaEl) moTaEl.value = '';
        // Reset mode selection to 'both'
        document.querySelectorAll('#createModeGrid .mode-card').forEach(c => {
            c.classList.remove('selected');
            if (c.dataset.value === 'both') {
                c.classList.add('selected');
                const radio = c.querySelector('input[type=radio]');
                if (radio) radio.checked = true;
            }
        });
    }

    // Mode card selection
    document.querySelectorAll('#createModeGrid .mode-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('#createModeGrid .mode-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type=radio]');
            if (radio) radio.checked = true;
        });
    });

    // Open / close buttons
    document.getElementById('btnOpenCreate')?.addEventListener('click', openCreateModal);
    document.getElementById('btnCloseCreate')?.addEventListener('click', closeCreateModal);
    document.getElementById('btnCancelCreate')?.addEventListener('click', closeCreateModal);

    // Close on overlay click
    document.getElementById('createModal')?.addEventListener('click', function (e) {
        if (e.target === this) closeCreateModal();
    });

    // Close on Escape
    document.addEventListener('keydown', function handleEsc(e) {
        if (e.key === 'Escape') closeCreateModal();
    });

    // ── Submit create group ──────────────────────────────
    document.getElementById('btnSubmitCreate')?.addEventListener('click', function () {
        const tenNhom = (document.getElementById('createTenNhom')?.value || '').trim();
        const moTa    = (document.getElementById('createMoTa')?.value || '').trim();
        const cheDoEl = document.querySelector('#createModeGrid input[type=radio]:checked');
        const cheDo   = cheDoEl ? cheDoEl.value : 'both';

        if (!tenNhom) {
            showToast({ type: 'error', title: 'Lỗi', message: 'Vui lòng nhập tên nhóm' });
            document.getElementById('createTenNhom')?.focus();
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btn.innerHTML = `<svg style="width:14px;height:14px;animation:spin .6s linear infinite" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Đang tạo...`;

        const body = new FormData();
        body.append('ten_nhom', tenNhom);
        body.append('mo_ta', moTa);
        body.append('che_do', cheDo);
        body.append('_token', csrfToken());

        fetch('/api/v1/groups', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body,
        })
        .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
        .then(({ ok, data }) => {
            if (!ok) throw new Error(data.message || 'Lỗi tạo nhóm');
            closeCreateModal();
            showToast({ type: 'success', title: 'Thành công', message: data.message });
            if (data.redirect) {
                setTimeout(() => { window.location.href = data.redirect; }, 600);
            } else {
                loadGroups();
            }
        })
        .catch(err => {
            showToast({ type: 'error', title: 'Lỗi', message: err.message || 'Có lỗi xảy ra' });
        })
        .finally(() => {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><path d="M10 4v12M4 10h12"/></svg> Tạo nhóm`;
        });
    });

    // ── Initial load ──────────────────────────────────────
    loadGroups();

    // Cleanup guard on next SPA navigation
    window.addEventListener('spa:navigated', function cleanup() {
        window.__groupsIndexInit = false;
        window.removeEventListener('spa:navigated', cleanup);
    }, { once: true });
})();
</script>
@endsection
