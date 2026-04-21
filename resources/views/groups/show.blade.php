@extends('layouts.app')
@section('title', 'Chi tiết nhóm')
@section('content')
<style>
:root {
    --primary: #4a90e2; --primary-dark: #2a5298;
    --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
    --dark-bg: #0f1217; --dark-card: #191d27; --dark-border: rgba(255,255,255,0.06);
    --radius: 16px; --radius-sm: 10px;
    --shadow: 0 2px 8px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
}

body.dark .content { background: var(--dark-bg); }

.breadcrumb {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: #9ca3af; margin-bottom: 20px;
}
.breadcrumb a { color: var(--primary); text-decoration: none; font-weight: 600; }
.breadcrumb a:hover { text-decoration: underline; }
body.dark .breadcrumb { color: #4b5563; }

.group-hero {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 20px; padding: 32px 36px;
    margin-bottom: 24px; position: relative; overflow: hidden;
    box-shadow: 0 8px 28px rgba(74,144,226,0.3);
}
.group-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:200px; height:200px;
    background:rgba(255,255,255,0.06); border-radius:50%;
}
.group-hero::after {
    content:''; position:absolute; bottom:-40px; left:40%;
    width:160px; height:160px;
    background:rgba(255,255,255,0.04); border-radius:50%;
}

.hero-top { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; flex-wrap:wrap; }
.hero-name { font-size:28px; font-weight:900; color:white; letter-spacing:-0.5px; margin-bottom:6px; }
.hero-desc { font-size:14px; color:rgba(255,255,255,0.75); }
.hero-meta { display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }
.hero-tag {
    padding:5px 12px; border-radius:20px;
    background:rgba(255,255,255,0.15); color:white;
    font-size:12px; font-weight:700; backdrop-filter:blur(4px);
    display:inline-flex; align-items:center; gap:5px;
}
.hero-actions { display:flex; gap:8px; flex-shrink:0; }
.btn-hero {
    padding:9px 16px; border-radius:var(--radius-sm);
    background:rgba(255,255,255,0.18); border:1px solid rgba(255,255,255,0.25);
    color:white; font-size:13px; font-weight:700; cursor:pointer;
    display:flex; align-items:center; gap:6px; text-decoration:none;
    transition:background 0.2s; backdrop-filter:blur(4px);
}
.btn-hero:hover { background:rgba(255,255,255,0.28); }
.btn-hero.danger { background:rgba(239,68,68,0.25); border-color:rgba(239,68,68,0.4); }
.btn-hero.danger:hover { background:rgba(239,68,68,0.38); }

.show-grid { display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:start; }

.section-card {
    background:white; border-radius:var(--radius);
    box-shadow:var(--shadow); margin-bottom:20px;
    border:1px solid rgba(226,232,240,0.8); overflow:hidden;
}
body.dark .section-card {
    background:var(--dark-card);
    border-color:var(--dark-border);
    box-shadow:0 2px 12px rgba(0,0,0,0.3);
}

.section-hdr {
    padding:18px 22px; border-bottom:1px solid #f3f4f6;
    display:flex; justify-content:space-between; align-items:center;
}
body.dark .section-hdr { border-color:var(--dark-border); }

.section-title {
    font-size:15px; font-weight:800; color:#1f2937;
    display:flex; align-items:center; gap:8px;
}
body.dark .section-title { color:#e5e7eb; }

.mode-nav { display:grid; grid-template-columns:1fr 1fr; gap:12px; padding:20px 22px; }
.mode-btn {
    display:flex; flex-direction:column; align-items:center; gap:8px;
    padding:20px 16px; border-radius:14px;
    text-decoration:none; transition:var(--transition);
    border:2px solid #e5e7eb; background:#fafafa;
}
.mode-btn:hover { border-color:var(--primary); transform:translateY(-2px); box-shadow:0 6px 20px rgba(74,144,226,0.15); }
body.dark .mode-btn {
    background:rgba(255,255,255,0.02);
    border-color:rgba(255,255,255,0.08);
}
body.dark .mode-btn:hover {
    border-color:var(--primary);
    background:rgba(74,144,226,0.06);
    box-shadow:0 6px 20px rgba(0,0,0,0.3);
}
.mode-btn.active { border-color:var(--primary); background:rgba(74,144,226,0.05); }
body.dark .mode-btn.active { background:rgba(74,144,226,0.1); }
.mode-btn.active .mode-btn-name { color:var(--primary); }
body.dark .mode-btn.active .mode-btn-name { color:#7db8f7; }

.mode-btn-icon { font-size:32px; color:#374151; }
body.dark .mode-btn-icon { color:#9ca3af; }
.mode-btn:hover .mode-btn-icon { color:var(--primary); }
body.dark .mode-btn:hover .mode-btn-icon { color:#7db8f7; }

.mode-btn-name { font-size:13px; font-weight:800; color:#1f2937; text-align:center; }
body.dark .mode-btn-name { color:#e5e7eb; }
.mode-btn-desc { font-size:11px; color:#9ca3af; text-align:center; }
body.dark .mode-btn-desc { color:#4b5563; }

.pending-badge {
    background:var(--danger); color:white;
    font-size:10px; font-weight:800;
    padding:2px 7px; border-radius:20px; margin-left:4px;
}

.proposal-alert {
    display:flex; align-items:center; gap:12px;
    padding:14px 18px; border-radius:var(--radius-sm);
    background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3);
    margin-bottom:16px;
}
body.dark .proposal-alert {
    background:rgba(245,158,11,0.08);
    border-color:rgba(245,158,11,0.2);
}
.proposal-alert-icon { font-size:20px; color:#d97706; }
.proposal-alert-text { flex:1; font-size:13px; color:#92400e; font-weight:600; }
body.dark .proposal-alert-text { color:#fcd34d; }

.member-item {
    display:flex; align-items:center; gap:14px;
    padding:14px 22px; border-bottom:1px solid #f3f4f6;
    transition:background 0.15s;
}
body.dark .member-item { border-color:rgba(255,255,255,0.04); }
.member-item:last-child { border-bottom:none; }
.member-item:hover { background:#f9fafb; }
body.dark .member-item:hover { background:rgba(255,255,255,0.025); }

.member-av {
    width:42px; height:42px; border-radius:50%;
    background:linear-gradient(135deg,var(--primary),var(--primary-dark));
    display:flex; align-items:center; justify-content:center;
    color:white; font-size:15px; font-weight:800; flex-shrink:0;
    object-fit:cover;
}
.member-info { flex:1; min-width:0; }
.member-name { font-size:14px; font-weight:700; color:#1f2937; }
body.dark .member-name { color:#e5e7eb; }
.member-email { font-size:12px; color:#9ca3af; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
body.dark .member-email { color:#4b5563; }

.member-meta { display:flex; flex-direction:column; align-items:flex-end; gap:4px; }
.member-role { padding:3px 9px; border-radius:12px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:4px; }
.role-admin  { background:rgba(74,144,226,0.1); color:var(--primary); }
.role-member { background:rgba(107,114,128,0.1); color:#6b7280; }
body.dark .role-admin  { background:rgba(74,144,226,0.18); color:#7db8f7; }
body.dark .role-member { background:rgba(107,114,128,0.18); color:#9ca3af; }

.member-balance { font-size:13px; font-weight:700; }
.member-balance.pos  { color:var(--success); }
.member-balance.neg  { color:var(--danger); }
.member-balance.zero { color:#9ca3af; }

body.dark [data-action="promote"] {
    background:rgba(74,144,226,0.12) !important;
    border-color:rgba(74,144,226,0.25) !important;
    color:#7db8f7 !important;
}
body.dark [data-action="demote"] {
    background:rgba(107,114,128,0.12) !important;
    border-color:rgba(107,114,128,0.2) !important;
    color:#9ca3af !important;
}
body.dark [data-action="remove"] {
    background:rgba(239,68,68,0.12) !important;
    border-color:rgba(239,68,68,0.2) !important;
    color:#f87171 !important;
}

/* ── Visibility toggle (redesigned) ── */
.vis-toggle-row {
    display:flex; align-items:center; gap:14px;
    padding:14px 22px;
    border-bottom:1px solid #f3f4f6;
    background:rgba(74,144,226,0.03);
}
body.dark .vis-toggle-row {
    border-bottom-color:var(--dark-border);
    background:rgba(74,144,226,0.05);
}
.vis-toggle-label {
    flex:1; min-width:0;
    font-size:13px; font-weight:600; color:#374151;
    display:flex; align-items:center; gap:6px;
}
body.dark .vis-toggle-label { color:#9ca3af; }
.vis-status-badge {
    font-size:11px; font-weight:700; padding:2px 8px;
    border-radius:20px; margin-left:4px;
    transition: background 0.25s, color 0.25s;
}
.vis-status-badge.on  { background:rgba(16,185,129,0.12); color:#10b981; }
.vis-status-badge.off { background:rgba(156,163,175,0.15); color:#9ca3af; }
body.dark .vis-status-badge.on  { background:rgba(16,185,129,0.2); color:#34d399; }
body.dark .vis-status-badge.off { background:rgba(255,255,255,0.07); color:#6b7280; }

/* pill toggle switch */
.pill-toggle {
    position:relative; width:50px; height:28px;
    flex-shrink:0; cursor:pointer;
    border:none; background:none; padding:0;
    outline:none;
}
.pill-track {
    position:absolute; inset:0; border-radius:14px;
    background:#d1d5db;
    transition:background 0.25s cubic-bezier(0.4,0,0.2,1);
    box-shadow:inset 0 1px 3px rgba(0,0,0,0.12);
}
body.dark .pill-track { background:#2a2f3e; }
.pill-track.on { background:var(--primary); box-shadow:0 2px 8px rgba(74,144,226,0.4); }
body.dark .pill-track.on { background:var(--primary); box-shadow:0 2px 8px rgba(74,144,226,0.35); }
.pill-thumb {
    position:absolute; top:4px; left:4px;
    width:20px; height:20px; border-radius:50%;
    background:white;
    box-shadow:0 1px 4px rgba(0,0,0,0.22), 0 0 0 1px rgba(0,0,0,0.04);
    transition:transform 0.25s cubic-bezier(0.4,0,0.2,1);
}
.pill-thumb.on { transform:translateX(22px); }

/* invite box */
.invite-box { padding:16px 20px; border-top:1px solid #f3f4f6; }
body.dark .invite-box { border-color:var(--dark-border); }

body.dark #inviteSearchWrap {
    background:#0f1217 !important;
    border-color:rgba(255,255,255,0.1) !important;
}
body.dark #inviteSearchInput { color:#e5e7eb !important; }
body.dark #inviteSearchInput::placeholder { color:#4b5563 !important; }
body.dark #inviteDropdown {
    background:var(--dark-card) !important;
    border-color:var(--dark-border) !important;
    box-shadow:0 8px 30px rgba(0,0,0,0.5) !important;
}
body.dark #inviteDropdown [data-invite-user-id] { border-color:rgba(255,255,255,0.04) !important; }
body.dark #inviteDropdown [data-invite-user-id] div[style*="font-size:13px"] { color:#e5e7eb; }
body.dark #inviteDropdown [data-invite-user-id] div[style*="font-size:12px"] { color:#4b5563; }
body.dark #inviteDropdown [data-invite-user-id]:hover { background:#212736 !important; }
body.dark #inviteSelectedUser > div {
    background:rgba(74,144,226,0.1) !important;
    border-color:rgba(74,144,226,0.25) !important;
}
body.dark #inviteSelectedUser #selName,
body.dark #inviteSelectedUser div[style*="font-weight:700"] { color:#e5e7eb !important; }

.btn-primary {
    display:inline-flex; align-items:center; gap:7px;
    padding:9px 18px; border-radius:var(--radius-sm);
    background:linear-gradient(135deg,var(--primary),var(--primary-dark));
    color:white; font-size:13px; font-weight:700;
    border:none; cursor:pointer; text-decoration:none;
    transition:opacity 0.2s, transform 0.2s;
}
.btn-primary:hover { opacity:0.88; transform:translateY(-1px); }
.btn-sm { padding:6px 12px; font-size:12px; }
.btn-danger { background:linear-gradient(135deg,var(--danger),#dc2626); }

.skeleton {
    background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);
    background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:8px;
}
body.dark .skeleton {
    background:linear-gradient(90deg,#1e2333 25%,#262d3d 50%,#1e2333 75%);
    background-size:200% 100%;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.modal-overlay {
    position:fixed; inset:0;
    background:rgba(0,0,0,0.55); backdrop-filter:blur(6px);
    z-index:9999; display:flex; align-items:center; justify-content:center;
    padding:20px; opacity:0; visibility:hidden;
    transition:opacity 0.22s,visibility 0.22s;
}
.modal-overlay.active { opacity:1; visibility:visible; }
.modal-box {
    background:white; border-radius:20px;
    width:100%; max-width:500px; overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,0.18);
    transform:scale(0.95) translateY(10px);
    transition:transform 0.25s cubic-bezier(0.4,0,0.2,1);
    border:1px solid rgba(226,232,240,0.5);
}
.modal-overlay.active .modal-box { transform:scale(1) translateY(0); }
body.dark .modal-box {
    background:var(--dark-card);
    border-color:var(--dark-border);
    box-shadow:0 20px 60px rgba(0,0,0,0.6);
}
body.dark #editModalContent label[style*="color:#374151"] { color:#9ca3af !important; }
body.dark #editModalContent > div:last-child { background:var(--dark-card) !important; }

.form-ctrl {
    width:100%; padding:10px 14px;
    border:2px solid #e5e7eb; border-radius:10px;
    font-size:14px; background:#f9fafb; color:#1f2937; outline:none;
    box-sizing:border-box; transition:border-color 0.2s, background 0.2s;
}
.form-ctrl:focus { border-color:var(--primary); background:white; box-shadow:0 0 0 3px rgba(74,144,226,0.1); }
body.dark .form-ctrl {
    background:#0f1217;
    border-color:rgba(255,255,255,0.1);
    color:#e5e7eb;
}
body.dark .form-ctrl:focus {
    border-color:var(--primary);
    background:#141820;
    box-shadow:0 0 0 3px rgba(74,144,226,0.15);
}
body.dark .form-ctrl::placeholder { color:#4b5563; }
body.dark .section-card [style*="color:#ef4444"].section-title { color:#f87171 !important; }

@keyframes spin { to { transform:rotate(360deg); } }
@media (max-width:1100px) { .show-grid { grid-template-columns:1fr; } }
</style>

{{-- Breadcrumb --}}
<div class="breadcrumb">
    <a href="/groups">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px"><path d="M12 4L6 10l6 6"/></svg>
        Nhóm của tôi
    </a>
    <span>/</span>
    <span id="breadcrumbName">
        <span class="skeleton" style="display:inline-block;width:120px;height:14px;vertical-align:middle"></span>
    </span>
</div>

{{-- Hero skeleton --}}
<div id="heroWrap">
    <div style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:20px;padding:32px 36px;margin-bottom:24px;box-shadow:0 8px 28px rgba(74,144,226,0.3)">
        <div class="skeleton" style="width:220px;height:32px;margin-bottom:10px;border-radius:8px"></div>
        <div class="skeleton" style="width:160px;height:16px;border-radius:6px"></div>
    </div>
</div>

{{-- Main grid skeleton --}}
<div class="show-grid" id="showGrid">
    <div>
        <div class="section-card" style="min-height:180px">
            <div class="section-hdr"><div class="skeleton" style="width:140px;height:18px"></div></div>
            <div style="padding:20px 22px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="skeleton" style="height:80px;border-radius:14px"></div>
                <div class="skeleton" style="height:80px;border-radius:14px"></div>
            </div>
        </div>
    </div>
    <div>
        <div class="section-card">
            <div class="section-hdr"><div class="skeleton" style="width:100px;height:18px"></div></div>
            @for($i=0;$i<3;$i++)
            <div style="display:flex;align-items:center;gap:14px;padding:14px 22px;border-bottom:1px solid #f3f4f6">
                <div class="skeleton" style="width:42px;height:42px;border-radius:50%;flex-shrink:0"></div>
                <div style="flex:1"><div class="skeleton" style="width:60%;height:14px;margin-bottom:6px"></div><div class="skeleton" style="width:80%;height:12px"></div></div>
            </div>
            @endfor
        </div>
    </div>
</div>

{{-- Edit modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div id="editModalContent"></div>
    </div>
</div>

<script>
(function () {
    if (window.__groupsShowInit) return;
    window.__groupsShowInit = true;

    //lấy group_id và ktra xem có hợp lệ hay ko
    const pathParts = window.location.pathname.split('/');
    const GROUP_ID = pathParts[pathParts.indexOf('groups') + 1];
    if (!GROUP_ID || isNaN(GROUP_ID)) return;

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    //chuẩn hóa cách gọi api
    function apiFetch(url, opts = {}) {
        //gộp header
        const headers = Object.assign({
            'Accept': 'application/json',//trả về json
            'X-Requested-With': 'XMLHttpRequest',// báo ajax request
            'X-CSRF-TOKEN': csrfToken(),//chống csrf
        }, opts.headers || {});
        return fetch(url, { credentials: 'same-origin', ...opts, headers })//gửi cookie/session cùng domain
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })));
    }

    function svgIcon(type) {
        const icons = {
            balance: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M10 3v14M4 17h12"/><path d="M4 7 2 12h4zM16 7l-2 5h4z"/><path d="M4 7h12"/></svg>',
            expense: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M5 2h10a1 1 0 011 1v14l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5V3a1 1 0 011-1z"/><path d="M7.5 7h5M7.5 10h5M7.5 13h3"/></svg>',
            both:    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M3 6h9l3-3 3 3-3 3"/><path d="M3 14h9l3-3 3 3-3 3"/><path d="M6 9l-3 3M6 11l-3-3"/></svg>',
            chart:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><rect x="3" y="11" width="3" height="6" rx="0.5"/><rect x="8.5" y="7" width="3" height="10" rx="0.5"/><rect x="14" y="4" width="3" height="13" rx="0.5"/><path d="M2 18h16"/></svg>',
            clock:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><circle cx="10" cy="10" r="7.5"/><path d="M10 6v4.5l3 1.5"/></svg>',
            eye:     '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M2 10S5 4.5 10 4.5 18 10 18 10s-3 5.5-8 5.5S2 10 2 10z"/><circle cx="10" cy="10" r="2.5"/></svg>',
            users:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><circle cx="7.5" cy="6.5" r="2.5"/><path d="M2 17c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><circle cx="14" cy="6" r="2"/><path d="M18 17c0-2.5-1.8-4.2-4-4.7"/></svg>',
            admin:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M3 14L6 7l4 4 4-4 3 7H3z"/><path d="M3 14h14"/><circle cx="10" cy="3.5" r="1" fill="currentColor" stroke="none"/></svg>',
            member:  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><circle cx="10" cy="6.5" r="3"/><path d="M3.5 18c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"/></svg>',
            edit:    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M13.5 3.5L16.5 6.5 7 16H4v-3z"/><path d="M11.5 5.5l3 3"/></svg>',
            leave:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M5 2h10a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V3a1 1 0 011-1z"/><path d="M3 18h14"/><circle cx="13" cy="10.5" r="1" fill="currentColor" stroke="none"/></svg>',
            warn:    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M10 2L2 17h16z"/><path d="M10 8v4M10 14.5v.5"/></svg>',
            archive: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><rect x="2" y="4" width="16" height="4" rx="1"/><path d="M3 8v8a1 1 0 001 1h12a1 1 0 001-1V8"/><path d="M8 12h4"/></svg>',
            cal:     '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><rect x="3" y="4" width="14" height="14" rx="2"/><path d="M3 9h14M7 2v4M13 2v4"/></svg>',
            bolt:    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M11 2L5 11h5.5L9 18l7-9h-5.5z"/></svg>',
            up:      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M10 14V6M6 10l4-4 4 4"/></svg>',
            down:    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M10 6v8M6 10l4 4 4-4"/></svg>',
            x:       '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em;vertical-align:-0.15em"><path d="M5 5l10 10M15 5L5 15"/></svg>',
            close:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><path d="M5 5l10 10M15 5L5 15"/></svg>',
        };
        return icons[type] || '';
    }

    function modeName(che_do) {
        return che_do === 'balance' ? 'Phân phối số dư' : (che_do === 'expense' ? 'Chia khoản chi' : 'Cả hai chế độ');
    }

    // ── Render hero ────────────────────────────────────
    function renderHero(g, laAdmin) {
        const cheDoIcon = svgIcon(g.che_do || 'both');
        return `
        <div class="group-hero">
            <div class="hero-top">
                <div>
                    <div class="hero-name">${esc(g.ten_nhom)}</div>
                    <div class="hero-desc">${esc(g.mo_ta || 'Chưa có mô tả')}</div>
                    <div class="hero-meta">
                        <span class="hero-tag">${cheDoIcon} ${modeName(g.che_do)}</span>
                        <span class="hero-tag">${svgIcon('users')} <span id="heroMemberCount">...</span> thành viên</span>
                        <span class="hero-tag">${svgIcon('cal')} ${new Date(g.created_at).toLocaleDateString('vi-VN')}</span>
                        ${g.hien_so_du ? `<span class="hero-tag">${svgIcon('eye')} Hiển thị số dư</span>` : ''}
                    </div>
                </div>
                <div class="hero-actions">
                    ${laAdmin ? `<button class="btn-hero" id="btnOpenEdit">${svgIcon('edit')} Sửa</button>` : ''}
                    <button class="btn-hero danger" id="btnLeaveGroup">${svgIcon('leave')} Rời nhóm</button>
                </div>
            </div>
        </div>`;
    }

    // ── Render mode nav ────────────────────────────────
    function renderModeNav(g, pendingBalance, pendingExpense) {
        let html = '';
        if (['balance','both'].includes(g.che_do)) {
            const isActive = pendingBalance > 0 ? 'active' : '';
            html += `
            <a href="/groups/${GROUP_ID}/balance" class="mode-btn ${isActive}">
                <div class="mode-btn-icon">${svgIcon('balance')}</div>
                <div class="mode-btn-name">Phân phối số dư${pendingBalance > 0 ? `<span class="pending-badge">${pendingBalance}</span>` : ''}</div>
                <div class="mode-btn-desc">Chia lại tiền trong nhóm</div>
            </a>`;
        }
        if (['expense','both'].includes(g.che_do)) {
            const isActive = pendingExpense > 0 ? 'active' : '';
            html += `
            <a href="/groups/${GROUP_ID}/expense" class="mode-btn ${isActive}">
                <div class="mode-btn-icon">${svgIcon('expense')}</div>
                <div class="mode-btn-name">Chia khoản chi${pendingExpense > 0 ? `<span class="pending-badge">${pendingExpense}</span>` : ''}</div>
                <div class="mode-btn-desc">Chia tiền khi thanh toán chung</div>
            </a>
            <a href="/groups/${GROUP_ID}/debt/summary" class="mode-btn">
                <div class="mode-btn-icon">${svgIcon('chart')}</div>
                <div class="mode-btn-name">Tổng kết nợ</div>
                <div class="mode-btn-desc">Xem ai nợ ai bao nhiêu</div>
            </a>`;
        }
        return html;
    }

    // ── Render member item ─────────────────────────────
    function renderMemberItem(m, laAdmin, hienSoDu, currentUserId) {
        const avColors = ['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
        const color = avColors[m.user_id % avColors.length] || '#4a90e2';
        let avatarHtml;
        if (m.avatar) {
            const src = m.avatar.startsWith('http') ? m.avatar : '/storage/' + m.avatar;
            avatarHtml = `<img src="${src}" class="member-av" style="object-fit:cover;border-radius:50%;" alt="" onerror="this.outerHTML='<div class=member-av style=background:linear-gradient(135deg,${color},${color}cc)>${esc(String(m.name||'').substring(0,2).toUpperCase())}</div>'">`;
        } else {
            avatarHtml = `<div class="member-av" style="background:linear-gradient(135deg,${color},${color}cc)">${esc(String(m.name||'').substring(0,2).toUpperCase())}</div>`;
        }

        const roleClass = m.vai_tro === 'admin' ? 'role-admin' : 'role-member';
        const roleIcon  = m.vai_tro === 'admin' ? svgIcon('admin') : svgIcon('member');
        const roleLabel = m.vai_tro === 'admin' ? 'Admin' : 'Member';

        let balHtml = '';
        if (hienSoDu && m.so_du !== undefined) {
            const bal = parseFloat(m.so_du) || 0;
            const cls = bal > 0 ? 'pos' : (bal < 0 ? 'neg' : 'zero');
            const prefix = bal > 0 ? '+' : '';
            balHtml = `<span class="member-balance ${cls}">${prefix}${bal.toLocaleString('vi-VN')}đ</span>`;
        }

        let actionHtml = '';
        if (laAdmin && m.user_id !== currentUserId) {
            const btnBase = 'font-size:11px;font-weight:700;padding:3px 9px;border-radius:8px;cursor:pointer;transition:all .2s;white-space:nowrap;border:1px solid';
            if (m.vai_tro === 'member') {
                actionHtml += `<button data-action="promote" data-member-id="${m.id}" data-member-name="${esc(m.name)}"
                    style="${btnBase} rgba(74,144,226,0.3);background:rgba(74,144,226,0.08);color:#4a90e2;">
                    ${svgIcon('up')} Đặt Admin
                </button>`;
            } else {
                actionHtml += `<button data-action="demote" data-member-id="${m.id}" data-member-name="${esc(m.name)}"
                    style="${btnBase} rgba(107,114,128,0.2);background:rgba(107,114,128,0.08);color:#6b7280;">
                    ${svgIcon('down')} Hạ quyền
                </button>`;
            }
            if (m.vai_tro !== 'admin') {
                actionHtml += `<button data-action="remove" data-member-id="${m.id}" data-member-name="${esc(m.name)}"
                    style="${btnBase} rgba(239,68,68,0.2);background:rgba(239,68,68,0.08);color:#ef4444;">
                    ${svgIcon('x')} Xóa
                </button>`;
            }
        }

        return `
        <div class="member-item" data-member-id="${m.id}">
            ${avatarHtml}
            <div class="member-info">
                <div class="member-name">${esc(m.name)}</div>
                <div class="member-email">${esc(m.email || '')}</div>
            </div>
            <div class="member-meta">
                <span class="member-role ${roleClass}">${roleIcon} ${roleLabel}</span>
                ${balHtml}
                ${actionHtml}
            </div>
        </div>`;
    }

    // ── Build visibility toggle HTML ───────────────────
    function buildVisToggleHtml(isOn) {
        return `
        <div class="vis-toggle-row" id="visToggleRow">
            <div class="vis-toggle-label">
                ${svgIcon('eye')} Hiển thị số dư cho thành viên
                <span class="vis-status-badge ${isOn ? 'on' : 'off'}" id="visStatusBadge">${isOn ? 'Đang bật' : 'Đang tắt'}</span>
            </div>
            <button class="pill-toggle" id="visToggleBtn" title="${isOn ? 'Nhấn để tắt' : 'Nhấn để bật'} hiển thị số dư" aria-pressed="${isOn}">
                <div class="pill-track ${isOn ? 'on' : ''}" id="pillTrack"></div>
                <div class="pill-thumb ${isOn ? 'on' : ''}" id="pillThumb"></div>
            </button>
        </div>`;
    }

    // ── Render full page ───────────────────────────────
    function renderPage(group, members, laAdmin, hienSoDu, pendingBalance, pendingExpense) {
        const currentUserId = window.__currentUserId || 0;

        document.getElementById('heroWrap').innerHTML = renderHero(group, laAdmin);
        document.getElementById('breadcrumbName').textContent = group.ten_nhom;
        document.title = group.ten_nhom + ' - Quản lý chi tiêu';

        const heroCount = document.getElementById('heroMemberCount');
        if (heroCount) heroCount.textContent = members.length;

        // Pending alerts
        let alertsHtml = '';
        if (pendingBalance > 0) {
            alertsHtml += `<div class="proposal-alert">
                <div class="proposal-alert-icon">${svgIcon('clock')}</div>
                <div class="proposal-alert-text">Có ${pendingBalance} đề xuất phân phối số dư đang chờ bạn xác nhận</div>
                <a href="/groups/${GROUP_ID}/balance" class="btn-primary btn-sm">Xem ngay</a>
            </div>`;
        }
        if (pendingExpense > 0) {
            alertsHtml += `<div class="proposal-alert">
                <div class="proposal-alert-icon">${svgIcon('clock')}</div>
                <div class="proposal-alert-text">Có ${pendingExpense} đề xuất chia chi đang chờ bạn xác nhận</div>
                <a href="/groups/${GROUP_ID}/expense" class="btn-primary btn-sm">Xem ngay</a>
            </div>`;
        }

        // Visibility toggle — chỉ hiện với admin + balance mode
        const visToggleHtml = (laAdmin && ['balance','both'].includes(group.che_do))
            ? buildVisToggleHtml(hienSoDu)
            : '';

        // Invite box
        const inviteBoxHtml = laAdmin ? `
        <div class="invite-box" id="inviteBox" style="display:none;">
            <div style="position:relative;margin-bottom:10px;">
                <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid #e5e7eb;border-radius:12px;background:#f9fafb;transition:border-color .2s;" id="inviteSearchWrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <input type="text" id="inviteSearchInput" placeholder="Tìm theo tên hoặc email..."
                        autocomplete="off"
                        style="border:none;outline:none;background:transparent;font-size:13px;color:#1f2937;width:100%;font-weight:500;"
                        onfocus="document.getElementById('inviteSearchWrap').style.borderColor='#4a90e2'"
                        onblur="setTimeout(()=>{if(document.getElementById('inviteSearchWrap'))document.getElementById('inviteSearchWrap').style.borderColor='#e5e7eb'},200)">
                    <div id="inviteSearchSpinner" style="display:none;width:14px;height:14px;border:2px solid #e5e7eb;border-top-color:#4a90e2;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0;"></div>
                </div>
                <div id="inviteDropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;background:white;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.14);border:1px solid rgba(0,0,0,0.06);z-index:999;max-height:280px;overflow-y:auto;"></div>
            </div>
            <div id="inviteSelectedUser" style="display:none;margin-bottom:12px;"></div>
            <button id="inviteSubmitBtn" disabled
                style="width:100%;padding:11px;border-radius:10px;background:linear-gradient(135deg,#4a90e2,#2a5298);color:white;border:none;font-size:13px;font-weight:700;cursor:not-allowed;opacity:.45;transition:opacity .2s;display:flex;align-items:center;justify-content:center;gap:7px;">
                Gửi lời mời
            </button>
            <div style="font-size:11px;color:#9ca3af;margin-top:8px;text-align:center;">Lời mời hết hạn sau 48 giờ</div>
        </div>` : '';

        // Danger zone
        const dangerZoneHtml = laAdmin ? `
        <div class="section-card">
            <div class="section-hdr">
                <div class="section-title" style="color:#ef4444;">${svgIcon('warn')} Khu vực nguy hiểm</div>
            </div>
            <div style="padding:18px 22px;">
                <button id="btnArchiveGroup" class="btn-primary btn-danger" style="width:100%;justify-content:center">
                    ${svgIcon('archive')} Lưu trữ nhóm
                </button>
                <div style="font-size:12px;color:#9ca3af;margin-top:8px;text-align:center">Nhóm sẽ được lưu trữ, không bị xóa hoàn toàn</div>
            </div>
        </div>` : '';

        const membersHtml = members.map(m => renderMemberItem(m, laAdmin, hienSoDu, currentUserId)).join('');

        document.getElementById('showGrid').innerHTML = `
        <div>
            ${alertsHtml}
            <div class="section-card">
                <div class="section-hdr">
                    <div class="section-title">${svgIcon('bolt')} Chức năng nhóm</div>
                </div>
                <div class="mode-nav">${renderModeNav(group, pendingBalance, pendingExpense)}</div>
            </div>
        </div>
        <div>
            <div class="section-card">
                <div class="section-hdr">
                    <div class="section-title">${svgIcon('users')} Thành viên (${members.length})</div>
                    ${laAdmin ? `<button class="btn-primary btn-sm" id="btnToggleInvite">+ Mời</button>` : ''}
                </div>
                ${visToggleHtml}
                <div id="membersList">${membersHtml}</div>
                ${inviteBoxHtml}
            </div>
            ${dangerZoneHtml}
        </div>`;

        bindPageEvents(group, laAdmin, hienSoDu, members);
    }

    // ── Bind events ────────────────────────────────────
    function bindPageEvents(group, laAdmin, hienSoDu, members) {
        //mở form edit
        document.getElementById('btnOpenEdit')?.addEventListener('click', () => openEditModal(group));

        //mở form xác nhận rời nhóm
        document.getElementById('btnLeaveGroup')?.addEventListener('click', () => {
            if (!confirm('Bạn chắc chắn muốn rời nhóm?')) return;
            apiFetch(`/api/v1/groups/${GROUP_ID}/members/leave`, { method: 'DELETE' })
                .then(({ ok, data }) => {
                    if (!ok) throw new Error(data.message);
                    showToast({ type: 'success', title: 'Thành công', message: data.message || 'Đã rời nhóm' });
                    setTimeout(() => { window.location.href = '/groups'; }, 800);
                })
                .catch(err => showToast({ type: 'error', title: 'Lỗi', message: err.message }));
        });

        // ── Visibility toggle (pill) ───────────────────
        document.getElementById('visToggleBtn')?.addEventListener('click', function () {
            // Optimistic UI
            const track  = document.getElementById('pillTrack');
            const thumb  = document.getElementById('pillThumb');
            const badge  = document.getElementById('visStatusBadge');
            const btn    = this;
            const wasOn  = track?.classList.contains('on');
            const nowOn  = !wasOn;

            if (track)  { track.classList.toggle('on', nowOn); }
            if (thumb)  { thumb.classList.toggle('on', nowOn); }
            if (badge)  {
                badge.textContent = nowOn ? 'Đang bật' : 'Đang tắt';
                badge.className = 'vis-status-badge ' + (nowOn ? 'on' : 'off');
            }
            btn.setAttribute('aria-pressed', String(nowOn));

            apiFetch(`/api/v1/groups/${GROUP_ID}/balance-visibility`, { method: 'PATCH' })
                .then(({ ok, data }) => {
                    if (!ok) throw new Error(data.message);
                    const isOn = data.hien_so_du;
                    // Sync with server truth
                    if (track)  { track.classList.toggle('on', isOn); }
                    if (thumb)  { thumb.classList.toggle('on', isOn); }
                    if (badge)  {
                        badge.textContent = isOn ? 'Đang bật' : 'Đang tắt';
                        badge.className = 'vis-status-badge ' + (isOn ? 'on' : 'off');
                    }
                    btn.setAttribute('aria-pressed', String(isOn));
                    showToast({ type: 'success', title: 'Thành công', message: data.message });
                    setTimeout(loadData, 600);
                })
                .catch(err => {
                    // Revert optimistic
                    if (track)  { track.classList.toggle('on', wasOn); }
                    if (thumb)  { thumb.classList.toggle('on', wasOn); }
                    if (badge)  {
                        badge.textContent = wasOn ? 'Đang bật' : 'Đang tắt';
                        badge.className = 'vis-status-badge ' + (wasOn ? 'on' : 'off');
                    }
                    btn.setAttribute('aria-pressed', String(wasOn));
                    showToast({ type: 'error', title: 'Lỗi', message: err.message });
                });
        });


        document.getElementById('btnToggleInvite')?.addEventListener('click', function () {
            const box = document.getElementById('inviteBox');
            if (!box) return;
            const isHidden = box.style.display === 'none' || box.style.display === '';
            box.style.display = isHidden ? 'block' : 'none';
            if (isHidden) setTimeout(() => document.getElementById('inviteSearchInput')?.focus(), 60);
        });

        document.getElementById('membersList')?.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-action]');
            if (!btn) return;
            const action     = btn.dataset.action;
            const memberId   = btn.dataset.memberId;
            const memberName = btn.dataset.memberName;

            const confirmMessages = {
                promote: `Chỉ định ${memberName} làm Admin?`,
                demote:  `Hạ quyền ${memberName} xuống Member?`,
                remove:  `Xóa ${memberName} khỏi nhóm?`,
            };
            if (!confirm(confirmMessages[action])) return;

            let url, method, body;
            if (action === 'promote') {
                url = `/api/v1/groups/${GROUP_ID}/members/${memberId}/role`;
                method = 'PATCH'; body = JSON.stringify({ vai_tro: 'admin' });
            } else if (action === 'demote') {
                url = `/api/v1/groups/${GROUP_ID}/members/${memberId}/role`;
                method = 'PATCH'; body = JSON.stringify({ vai_tro: 'member' });
            } else if (action === 'remove') {
                url = `/api/v1/groups/${GROUP_ID}/members/${memberId}`;
                method = 'DELETE';
            }

            apiFetch(url, { method, body, headers: { 'Content-Type': 'application/json' } })
                .then(({ ok, data }) => {
                    if (!ok) throw new Error(data.message);
                    showToast({ type: 'success', title: 'Thành công', message: data.message });
                    loadData();
                })
                .catch(err => showToast({ type: 'error', title: 'Lỗi', message: err.message }));
        });


        document.getElementById('btnArchiveGroup')?.addEventListener('click', function () {
            if (!confirm(`Lưu trữ nhóm "${group.ten_nhom}"? Dữ liệu sẽ không bị xóa.`)) return;
            apiFetch(`/api/v1/groups/${GROUP_ID}`, { method: 'DELETE' })
                .then(({ ok, data }) => {
                    if (!ok) throw new Error(data.message);
                    showToast({ type: 'success', title: 'Đã lưu trữ', message: data.message });
                    setTimeout(() => { window.location.href = '/groups'; }, 800);
                })
                .catch(err => showToast({ type: 'error', title: 'Lỗi', message: err.message }));
        });

        bindInviteSearch(members);
    }

    // ── Invite search ──────────────────────────────────
    let _inviteSelectedEmail = null;
    let _inviteSearchTimer   = null;

    function bindInviteSearch(members) {
        const input     = document.getElementById('inviteSearchInput');
        const dropdown  = document.getElementById('inviteDropdown');
        const spinner   = document.getElementById('inviteSearchSpinner');
        const submitBtn = document.getElementById('inviteSubmitBtn');
        if (!input) return;

        const memberIds = members.map(m => m.user_id).join(',');
        const avColors  = ['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];

        input.addEventListener('input', function () {
            clearTimeout(_inviteSearchTimer);
            const q = this.value.trim();
            if (q.length < 1) { if (dropdown) dropdown.style.display = 'none'; return; }
            if (spinner) spinner.style.display = 'block';

            _inviteSearchTimer = setTimeout(() => {
                fetch(`/api/v1/groups/search-users?q=${encodeURIComponent(q)}&exclude=${memberIds}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken() },
                    credentials: 'same-origin',
                })
                .then(r => r.json())
                .then(data => {
                    if (spinner) spinner.style.display = 'none';
                    const users = data.users || [];
                    if (!dropdown) return;
                    if (users.length === 0) {
                        dropdown.innerHTML = '<div style="padding:20px;text-align:center;color:#9ca3af;font-size:13px;font-weight:500">Không tìm thấy người dùng nào</div>';
                        dropdown.style.display = 'block';
                        return;
                    }
                    dropdown.innerHTML = users.map(u => {
                        const initials = (u.name || '').substring(0, 2).toUpperCase();
                        const color    = avColors[u.id % avColors.length];
                        const src      = u.avatar ? (u.avatar.startsWith('http') ? u.avatar : '/storage/' + u.avatar) : null;
                        const avHtml   = src
                            ? `<img src="${src}" style="width:38px;height:38px;border-radius:50%;object-fit:cover;flex-shrink:0;" onerror="this.outerHTML='<div style=width:38px;height:38px;border-radius:50%;background:${color};display:flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:800;flex-shrink:0>${esc(initials)}</div>'" alt="">`
                            : `<div style="width:38px;height:38px;border-radius:50%;background:${color};display:flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:800;flex-shrink:0">${esc(initials)}</div>`;
                        return `<div data-invite-user-id="${u.id}" data-invite-email="${esc(u.email)}" data-invite-name="${esc(u.name)}" data-invite-avatar="${esc(u.avatar||'')}" data-invite-color="${color}"
                            style="display:flex;align-items:center;gap:12px;padding:11px 16px;cursor:pointer;transition:background .15s;border-bottom:1px solid #f9fafb;"
                            onmouseover="this.style.background='#f8f9fd'" onmouseout="this.style.background='transparent'">
                            ${avHtml}
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:700;color:#1f2937;margin-bottom:1px;">${esc(u.name)}</div>
                                <div style="font-size:12px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(u.email)}</div>
                            </div>
                            <div style="font-size:11px;color:#4a90e2;font-weight:700;flex-shrink:0;">+ Mời</div>
                        </div>`;
                    }).join('');
                    dropdown.style.display = 'block';
                })
                .catch(() => { if (spinner) spinner.style.display = 'none'; });
            }, 280);
        });

        //lấy user đã chọn ở dropdown để mời vào group
        dropdown?.addEventListener('click', function (e) {
            const item = e.target.closest('[data-invite-user-id]');
            if (!item) return;
            const email  = item.dataset.inviteEmail;
            const name   = item.dataset.inviteName;
            const avatar = item.dataset.inviteAvatar;
            const color  = item.dataset.inviteColor;
            _inviteSelectedEmail = email;

            const initials = name.substring(0, 2).toUpperCase();
            const src = avatar ? (avatar.startsWith('http') ? avatar : '/storage/' + avatar) : null;
            const avHtml = src
                ? `<img src="${src}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">`
                : `<div style="width:36px;height:36px;border-radius:50%;background:${color};display:flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:800;flex-shrink:0">${esc(initials)}</div>`;

            const sel = document.getElementById('inviteSelectedUser');
            if (sel) {
                sel.style.display = 'block';
                sel.innerHTML = `<div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(74,144,226,0.06);border:2px solid rgba(74,144,226,0.3);border-radius:12px;">
                    ${avHtml}
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:700;color:#1f2937">${esc(name)}</div>
                        <div style="font-size:12px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(email)}</div>
                    </div>
                    <button id="btnClearInvite" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:16px;padding:2px 4px;line-height:1;">&#x2715;</button>
                </div>`;
                document.getElementById('btnClearInvite')?.addEventListener('click', clearInviteSelected);
            }

            if (submitBtn) { submitBtn.disabled = false; submitBtn.style.opacity = '1'; submitBtn.style.cursor = 'pointer'; }
            if (input) input.value = '';
            dropdown.style.display = 'none';
        });

        //gửi lời mời
        submitBtn?.addEventListener('click', function () {
            if (!_inviteSelectedEmail) return;
            this.disabled = true;
            this.style.opacity = '0.6';
            this.textContent = 'Đang gửi...';

            //gửi email lời mời
            const body = new FormData();
            body.append('email', _inviteSelectedEmail);//thêm dữ liệu vào data

            apiFetch(`/api/v1/groups/${GROUP_ID}/members`, { method: 'POST', body })
                .then(({ ok, data }) => {
                    if (!ok) throw new Error(data.message);
                    showToast({ type: 'success', title: 'Thành công', message: data.message || 'Đã gửi lời mời' });
                    clearInviteSelected();
                    loadData();
                })
                .catch(err => showToast({ type: 'error', title: 'Lỗi', message: err.message }))
                .finally(() => {
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.style.opacity = '1'; submitBtn.textContent = 'Gửi lời mời'; }
                });
        });

        document.addEventListener('click', function closeInvDrop(e) {
            if (!e.target.closest('#inviteBox')) {
                const d = document.getElementById('inviteDropdown');
                if (d) d.style.display = 'none';
            }
        });
    }

    function clearInviteSelected() {
        _inviteSelectedEmail = null;
        const sel = document.getElementById('inviteSelectedUser');
        const inp = document.getElementById('inviteSearchInput');
        const btn = document.getElementById('inviteSubmitBtn');
        if (sel) sel.style.display = 'none';
        if (inp) inp.value = '';
        if (btn) { btn.disabled = true; btn.style.opacity = '0.45'; btn.style.cursor = 'not-allowed'; }
    }

    // ── Edit modal ─────────────────────────────────────
    function openEditModal(group) {
        const content = document.getElementById('editModalContent');
        if (!content) return;
        const modes = [
            { value: 'balance', label: 'Phân phối số dư' },
            { value: 'expense', label: 'Chia khoản chi' },
            { value: 'both',    label: 'Cả hai chế độ' },
        ];
        const optionsHtml = modes.map(m =>
            `<option value="${m.value}" ${group.che_do === m.value ? 'selected' : ''}>${m.label}</option>`
        ).join('');

        content.innerHTML = `
        <div style="padding:22px 26px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;justify-content:space-between;align-items:center">
            <div style="font-size:17px;font-weight:800;color:white;display:flex;align-items:center;gap:8px">${svgIcon('edit')} Sửa thông tin nhóm</div>
            <button id="btnCloseEdit" style="background:rgba(255,255,255,0.2);border:none;border-radius:8px;width:32px;height:32px;cursor:pointer;color:white;display:flex;align-items:center;justify-content:center">${svgIcon('close')}</button>
        </div>
        <div style="padding:24px 26px">
            <div style="margin-bottom:16px">
                <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px">Tên nhóm *</label>
                <input id="editTenNhom" class="form-ctrl" value="${esc(group.ten_nhom)}" maxlength="100" required>
            </div>
            <div style="margin-bottom:16px">
                <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px">Mô tả</label>
                <input id="editMoTa" class="form-ctrl" value="${esc(group.mo_ta || '')}" maxlength="255">
            </div>
            <div style="margin-bottom:20px">
                <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px">Chế độ</label>
                <select id="editCheDo" class="form-ctrl">${optionsHtml}</select>
            </div>
            <div style="display:flex;gap:10px">
                <button id="btnCancelEdit" style="flex:1;padding:10px;border-radius:10px;background:#f3f4f6;border:2px solid #e5e7eb;color:#6b7280;font-weight:600;cursor:pointer">Hủy</button>
                <button id="btnSaveEdit" style="flex:2;padding:10px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;border:none;font-weight:700;cursor:pointer">Lưu thay đổi</button>
            </div>
        </div>`;

        document.getElementById('editModal').classList.add('active');
        document.getElementById('btnCloseEdit')?.addEventListener('click', closeEditModal);
        document.getElementById('btnCancelEdit')?.addEventListener('click', closeEditModal);
        document.getElementById('editModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeEditModal();
        });

        document.getElementById('btnSaveEdit')?.addEventListener('click', function () {
            const tenNhom = (document.getElementById('editTenNhom')?.value || '').trim();
            const moTa    = (document.getElementById('editMoTa')?.value || '').trim();
            const cheDo   = document.getElementById('editCheDo')?.value || 'both';

            if (!tenNhom) {
                showToast({ type: 'error', title: 'Lỗi', message: 'Vui lòng nhập tên nhóm' });
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Đang lưu...';

            const body = new FormData();
            body.append('ten_nhom', tenNhom);
            body.append('mo_ta', moTa);
            body.append('che_do', cheDo);
            body.append('_method', 'PATCH');

            apiFetch(`/api/v1/groups/${GROUP_ID}`, { method: 'POST', body })
                .then(({ ok, data }) => {
                    if (!ok) throw new Error(data.message);
                    showToast({ type: 'success', title: 'Thành công', message: data.message });
                    closeEditModal();
                    loadData();
                })
                .catch(err => showToast({ type: 'error', title: 'Lỗi', message: err.message }))
                .finally(() => { btn.disabled = false; btn.textContent = 'Lưu thay đổi'; });
        });
    }

    function closeEditModal() {
        document.getElementById('editModal')?.classList.remove('active');
    }

    // ── Load data ──────────────────────────────────────
    function loadData() {
        fetch(`/api/v1/groups/${GROUP_ID}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
        })
        .then(r => {
            if (r.status === 403) { window.location.href = '/groups'; return null; }
            return r.ok ? r.json() : Promise.reject(r);
        })
        .then(data => {
            if (!data) return;
            renderPage(
                data.group,
                data.members || [],
                data.laAdmin,
                data.hienSoDu,
                data.pendingBalance || 0,
                data.pendingExpense || 0,
            );
        })
        .catch(() => {
            showToast({ type: 'error', title: 'Lỗi', message: 'Không thể tải dữ liệu nhóm' });
        });
    }

    function resolveCurrentUser() {
        fetch('/api/v1/profile', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken() },
            credentials: 'same-origin',
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (data?.user?.id) window.__currentUserId = data.user.id;
            else if (data?.id) window.__currentUserId = data.id;
        })
        .catch(() => {})
        .finally(() => loadData());
    }

    if (!window.__currentUserId) {
        resolveCurrentUser();
    } else {
        loadData();
    }

    window.addEventListener('spa:navigated', function cleanup() {
        window.__groupsShowInit = false;
        window.__currentUserId  = null;
        window.removeEventListener('spa:navigated', cleanup);
    }, { once: true });
})();
</script>
@endsection
