<style>
.notif-wrap {
    position: relative;
}
.notif-bell {
    position: relative;
    width: 40px; height: 40px;
    border-radius: 50%;
    background: transparent;
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .18s;
    color: var(--topbar-icon-color, #6b7280);
}
.notif-bell:hover { background: rgba(0,0,0,0.05); }
body.dark .notif-bell:hover { background: rgba(255,255,255,0.08); }
.notif-bell svg { width: 20px; height: 20px; }

/* Badge đỏ số chưa đọc */
.notif-badge {
    position: absolute;
    top: 4px; right: 4px;
    min-width: 17px; height: 17px;
    background: #ef4444;
    border-radius: 10px;
    border: 2px solid white;
    font-size: 10px; font-weight: 800;
    color: white;
    display: flex; align-items: center; justify-content: center;
    padding: 0 3px;
    line-height: 1;
    transition: transform .2s;
}
body.dark .notif-badge { border-color: #141820; }
.notif-badge.hidden { display: none; }
.notif-badge.pop { animation: badge-pop .25s ease; }
@keyframes badge-pop { 0%{transform:scale(.6)} 60%{transform:scale(1.15)} 100%{transform:scale(1)} }

.notif-panel {
    position: absolute;
    top: calc(100% + 10px);
    right: -8px;
    width: 380px;
    background: white;
    border-radius: 18px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.14), 0 0 0 1px rgba(0,0,0,0.06);
    z-index: 9999;
    opacity: 0; visibility: hidden;
    transform: translateY(-8px) scale(.97);
    transform-origin: top right;
    transition: opacity .18s, visibility .18s, transform .18s;
    overflow: hidden;
}
.notif-panel.open {
    opacity: 1; visibility: visible; transform: translateY(0) scale(1);
}
body.dark .notif-panel {
    background: #1a1f2b;
    box-shadow: 0 8px 40px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.08);
}

/* Panel header */
.np-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 18px 12px;
    border-bottom: 1px solid #f3f4f6;
}
body.dark .np-header { border-color: rgba(255,255,255,0.07); }
.np-title { font-size: 16px; font-weight: 800; color: #1f2937; }
body.dark .np-title { color: #e5e7eb; }
.np-mark-all {
    font-size: 12px; font-weight: 700; color: #4a90e2;
    background: none; border: none; cursor: pointer; padding: 4px 8px;
    border-radius: 8px; transition: background .15s;
}
.np-mark-all:hover { background: rgba(74,144,226,0.08); }

/* Tabs */
.np-tabs {
    display: flex; gap: 4px; padding: 10px 12px 0;
    border-bottom: 1px solid #f3f4f6;
}
body.dark .np-tabs { border-color: rgba(255,255,255,0.07); }
.np-tab {
    padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700;
    color: #9ca3af; background: none; border: none; cursor: pointer;
    transition: all .15s;
}
.np-tab.active { background: #4a90e2; color: white; }
body.dark .np-tab { color: #6b7280; }
body.dark .np-tab.active { background: #4a90e2; color: white; }

/* List */
.np-list {
    max-height: 400px; overflow-y: auto;
    overscroll-behavior: contain;
}
.np-list::-webkit-scrollbar { width: 4px; }
.np-list::-webkit-scrollbar-track { background: transparent; }
.np-list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
body.dark .np-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); }

/* Notification item */
.np-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 12px 18px;
    cursor: pointer; transition: background .15s;
    border-bottom: 1px solid rgba(0,0,0,0.03);
    text-decoration: none;
    position: relative;
}
.np-item:hover { background: #f9fafb; }
body.dark .np-item:hover { background: rgba(255,255,255,0.04); }
.np-item.unread { background: rgba(74,144,226,0.04); }
body.dark .np-item.unread { background: rgba(74,144,226,0.08); }

/* Unread dot */
.np-item.unread::after {
    content: '';
    position: absolute;
    right: 16px; top: 50%;
    transform: translateY(-50%);
    width: 8px; height: 8px;
    background: #4a90e2; border-radius: 50%;
}

/* Avatar trong item */
.np-av {
    width: 40px; height: 40px; border-radius: 50%;
    flex-shrink: 0; position: relative;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 800; color: white;
}
.np-av img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
.np-av-icon {
    position: absolute; bottom: -2px; right: -2px;
    width: 18px; height: 18px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px;
    border: 2px solid white;
}
body.dark .np-av-icon { border-color: #1a1f2b; }

/* Content */
.np-content { flex: 1; min-width: 0; }
.np-text {
    font-size: 13px; color: #374151; line-height: 1.45;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
body.dark .np-text { color: #9ca3af; }
.np-text strong { color: #1f2937; font-weight: 700; }
body.dark .np-text strong { color: #e5e7eb; }
.np-time { font-size: 11px; color: #9ca3af; margin-top: 3px; font-weight: 600; }
.np-time.fresh { color: #4a90e2; }

/* Empty state */
.np-empty {
    text-align: center; padding: 40px 20px;
    color: #9ca3af;
}
.np-empty-icon { font-size: 40px; margin-bottom: 10px; }
.np-empty-text { font-size: 13px; font-weight: 600; }

/* Loading spinner */
.np-loading {
    display: flex; align-items: center; justify-content: center;
    padding: 30px;
}
.np-spinner {
    width: 24px; height: 24px;
    border: 3px solid #e5e7eb;
    border-top-color: #4a90e2;
    border-radius: 50%;
    animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Footer */
.np-footer { border-top: 1px solid #f3f4f6; }
body.dark .np-footer { border-color: rgba(255,255,255,0.07); }

.np-footer-btns {
    display: flex; gap: 0;
}
.np-footer-btn {
    flex: 1; padding: 12px 8px;
    font-size: 12px; font-weight: 700; color: #6b7280;
    background: none; border: none; cursor: pointer;
    transition: background .15s, color .15s;
    text-align: center; text-decoration: none;
    display: flex; align-items: center; justify-content: center; gap: 5px;
}
.np-footer-btn:hover { background: #f9fafb; color: #4a90e2; }
body.dark .np-footer-btn { color: #6b7280; }
body.dark .np-footer-btn:hover { background: rgba(255,255,255,0.04); color: #4a90e2; }
.np-footer-btn + .np-footer-btn { border-left: 1px solid #f3f4f6; }
body.dark .np-footer-btn + .np-footer-btn { border-color: rgba(255,255,255,0.07); }

/* Calendar picker overlay */
.np-calendar-overlay {
    position: absolute; inset: 0;
    background: white;
    border-radius: 18px;
    z-index: 10;
    opacity: 0; visibility: hidden;
    transform: translateX(20px);
    transition: all .2s;
    overflow: hidden;
    display: flex; flex-direction: column;
}
.np-calendar-overlay.open {
    opacity: 1; visibility: visible; transform: translateX(0);
}
body.dark .np-calendar-overlay { background: #1a1f2b; }

.np-cal-header {
    display: flex; align-items: center; gap: 10px;
    padding: 16px 18px 12px;
    border-bottom: 1px solid #f3f4f6;
}
body.dark .np-cal-header { border-color: rgba(255,255,255,0.07); }
.np-cal-back {
    background: none; border: none; cursor: pointer;
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #6b7280; font-size: 16px; transition: background .15s;
}
.np-cal-back:hover { background: #f3f4f6; }
.np-cal-title { font-size: 14px; font-weight: 800; color: #1f2937; flex: 1; }
body.dark .np-cal-title { color: #e5e7eb; }

.np-cal-picker {
    padding: 16px 18px;
    border-bottom: 1px solid #f3f4f6;
}
body.dark .np-cal-picker { border-color: rgba(255,255,255,0.07); }
.np-cal-picker input[type="date"] {
    width: 100%; padding: 10px 14px;
    border: 2px solid #e5e7eb; border-radius: 10px;
    font-size: 14px; background: #f9fafb; color: #1f2937;
    outline: none; transition: border-color .2s;
}
.np-cal-picker input:focus { border-color: #4a90e2; background: white; }
body.dark .np-cal-picker input { background: #141820; border-color: rgba(255,255,255,0.1); color: #e5e7eb; }

.np-cal-result { flex: 1; overflow-y: auto; }
.np-cal-date-label {
    padding: 10px 18px 6px;
    font-size: 11px; font-weight: 700; color: #9ca3af;
    text-transform: uppercase; letter-spacing: 0.6px;
}
</style>

{{-- ════ HTML: Thêm vào topbar, thay icon bell cũ ════ --}}
<div class="notif-wrap" id="notifWrap">
    {{-- Bell button --}}
    <button class="notif-bell" id="notifBell" onclick="toggleNotifPanel()" aria-label="Thông báo">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span class="notif-badge hidden" id="notifBadge"></span>
    </button>

    {{-- Dropdown Panel --}}
    <div class="notif-panel" id="notifPanel">

        {{-- Header --}}
        <div class="np-header">
            <div class="np-title" style="display: flex; align-items: center; gap: 6px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                Thông báo
            </div>
            <button class="np-mark-all" onclick="markAllRead()">Đánh dấu đã đọc</button>
        </div>

        {{-- Tabs --}}
        <div class="np-tabs">
            <button class="np-tab active" onclick="switchTab('all', this)">Tất cả</button>
            <button class="np-tab" onclick="switchTab('unread', this)">Chưa đọc</button>
        </div>

        {{-- List --}}
        <div class="np-list" id="notifList">
            <div class="np-loading"><div class="np-spinner"></div></div>
        </div>

        {{-- Footer --}}
        <div class="np-footer">
            <div class="np-footer-btns">
                <a href="{{ route('notifications.index') }}" class="np-footer-btn" style="display: flex; align-items: center; gap: 5px; justify-content: center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        <line x1="8" y1="11" x2="8" y2="11"></line>
                        <line x1="8" y1="16" x2="8" y2="16"></line>
                        <line x1="12" y1="11" x2="16" y2="11"></line>
                        <line x1="12" y1="16" x2="16" y2="16"></line>
                    </svg>
                    Xem tất cả
                </a>
                <button class="np-footer-btn" onclick="openCalendar()" style="display: flex; align-items: center; gap: 5px; justify-content: center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Theo ngày
                </button>
            </div>
        </div>

        {{-- Calendar overlay --}}
        <div class="np-calendar-overlay" id="notifCalendar">
            <div class="np-cal-header">
                <button class="np-cal-back" onclick="closeCalendar()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </button>
                <div class="np-cal-title" style="display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Thông báo theo ngày
                </div>
            </div>
            <div class="np-cal-picker">
                <input type="date" id="calDatePicker"
                    max="{{ date('Y-m-d') }}"
                    value="{{ date('Y-m-d') }}"
                    onchange="loadByDate(this.value)">
            </div>
            <div class="np-cal-result" id="calResult">
                <div style="text-align:center;padding:30px;color:#9ca3af;font-size:13px;">
                    Chọn ngày để xem thông báo
                </div>
            </div>
        </div>
    </div>
</div>
<script>
let notifData       = [];
let currentTab      = 'all';
let notifOpen       = false;
let notifLoaded     = false;
let badgePollingTimer = null;
let timeAgoTimer    = null;

function calcTimeAgo(unixTimestamp) {
    if (!unixTimestamp) return 'Không rõ';
    const now  = Math.floor(Date.now() / 1000);
    const diff = now - unixTimestamp;

    if (diff < 10)   return 'Vừa xong';
    if (diff < 60)   return diff + ' giây trước';
    if (diff < 3600) return Math.floor(diff / 60) + ' phút trước';
    if (diff < 86400)return Math.floor(diff / 3600) + ' giờ trước';
    if (diff < 604800) return Math.floor(diff / 86400) + ' ngày trước';
    if (diff < 2592000) return Math.floor(diff / 604800) + ' tuần trước';

    const d = new Date(unixTimestamp * 1000);
    return d.toLocaleDateString('vi-VN', { day:'2-digit', month:'2-digit', year:'numeric' });
}

function isFresh(unixTimestamp) {
    if (!unixTimestamp) return false;
    const diff = Math.floor(Date.now() / 1000) - unixTimestamp;
    return diff < 3600;
}

function updateAllTimeAgo() {
    document.querySelectorAll('[data-unix]').forEach(el => {
        const unix = parseInt(el.dataset.unix);
        if (!unix) return;
        const text = calcTimeAgo(unix);
        const fresh = isFresh(unix);
        el.textContent = text;
        el.className = 'np-time' + (fresh ? ' fresh' : '');
    });
}

function toggleNotifPanel() {
    notifOpen = !notifOpen;
    document.getElementById('notifPanel').classList.toggle('open', notifOpen);
    if (notifOpen && !notifLoaded) {
        loadNotifications();
    }
}

document.addEventListener('click', e => {
    if (!e.target.closest('#notifWrap') && notifOpen) {
        notifOpen = false;
        document.getElementById('notifPanel').classList.remove('open');
    }
});

async function loadNotifications() {
    try {
        const res  = await fetch('{{ route("api.notifications.dropdown") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        notifData  = data.notifications;
        notifLoaded = true;
        renderList();
        updateBadge(data.unread_count);
        window._notifHasOlder = data.has_older;

        if (timeAgoTimer) clearInterval(timeAgoTimer);
        timeAgoTimer = setInterval(updateAllTimeAgo, 30000);

    } catch (e) {
        document.getElementById('notifList').innerHTML = `
            <div class="np-empty">
                <div class="np-empty-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: #9ca3af;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="np-empty-text">Không thể tải thông báo</div>
            </div>`;
    }
}

function renderList() {
    const list = document.getElementById('notifList');
    let items = currentTab === 'unread'
        ? notifData.filter(n => !n.da_doc)
        : notifData;

    if (items.length === 0) {
        const isEmptyUnread = currentTab === 'unread';

        const iconSvg = isEmptyUnread
            ? `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>`
            : `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>`;

        const message = isEmptyUnread
            ? 'Không có thông báo chưa đọc'
            : 'Chưa có thông báo nào';

        list.innerHTML = `
            <div class="np-empty" style="text-align: center; padding: 40px 20px;">
                <div class="np-empty-icon" style="margin-bottom: 12px; display: flex; justify-content: center;">
                    ${iconSvg}
                </div>
                <div class="np-empty-text" style="color: #6b7280; font-size: 14px;">
                    ${message}
                </div>
            </div>`;
        return;
    }

    const groups = {};
    items.forEach(n => {
        const date = n.date;
        if (!groups[date]) groups[date] = [];
        groups[date].push(n);
    });

    let html = '';
    const now       = new Date();
    const today     = now.toISOString().split('T')[0];
    const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];

    Object.entries(groups).forEach(([date, notifs]) => {
        let label = date === today ? 'Hôm nay'
                  : date === yesterday ? 'Hôm qua'
                  : formatDate(date);
        html += `<div style="padding:8px 18px 4px;font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.6px;">${label}</div>`;
        notifs.forEach(n => {
            html += renderItem(n);
        });
    });

    list.innerHTML = html;
}

function renderItem(n) {
    const unix     = n.created_at_unix || 0;
    const timeText = unix ? calcTimeAgo(unix) : (n.time_ago || '');
    const fresh    = unix ? isFresh(unix) : (n.time_ago === 'Vừa xong' || (n.time_ago || '').includes('phút'));
    const unreadCls = n.da_doc ? '' : 'unread';

    let avHtml;
    if (n.actor_avatar) {
        const src = n.actor_avatar.startsWith('http')
            ? n.actor_avatar
            : `/storage/${n.actor_avatar}`;
        avHtml = `<img src="${src}" alt="" onerror="this.parentElement.innerHTML='<span>${escHtml(n.actor_name||'?').substring(0,2).toUpperCase()}</span>'">`;
    } else if (n.actor_name) {
        const initials = n.actor_name.substring(0, 2).toUpperCase();
        avHtml = `<span style="font-size:14px;font-weight:800;">${initials}</span>`;
    } else {
        avHtml = `
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #6b7280;">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>`;
    }

    const avBg = n.actor_name
        ? (n.actor_avatar ? 'transparent' : (() => {
            const colors = ['#4a90e2','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
            return colors[n.id % colors.length];
          })())
        : '#f3f4f6';

    const href = n.url || '#';

    let inviteButtons = '';
    if (n.loai === 'group_invited' && !n.da_doc) {
        const tokenMatch = n.url ? n.url.match(/invitations\/([^/]+)\/accept/) : null;
        if (tokenMatch) {
            const token = tokenMatch[1];
            inviteButtons = `
                <div style="display:flex;gap:6px;margin-top:8px;" onclick="event.preventDefault();event.stopPropagation();">
                    <button onclick="handleInvite('${token}','accept',${n.id},this)"
                        style="flex:1;padding:6px 10px;border-radius:8px;border:none;
                               background:linear-gradient(135deg,#10b981,#059669);
                               color:white;font-size:12px;font-weight:700;cursor:pointer;">
                        ✓ Chấp nhận
                    </button>
                    <button onclick="handleInvite('${token}','decline',${n.id},this)"
                        style="flex:1;padding:6px 10px;border-radius:8px;
                               border:2px solid #e5e7eb;background:white;
                               color:#6b7280;font-size:12px;font-weight:700;cursor:pointer;">
                        ✗ Từ chối
                    </button>
                </div>`;
        }
    }

    return `<div class="np-item ${unreadCls}"
        onclick="onItemClick(event, ${n.id}, '${escHtml(href)}')"
        style="cursor:pointer;">
        <div class="np-av" style="background:${avBg}">
            ${avHtml}
            <div class="np-av-icon" style="background:${n.color};">${n.icon}</div>
        </div>
        <div class="np-content" style="flex:1;min-width:0;">
            <div class="np-text">
                <strong>${escHtml(n.tieu_de)}</strong> ${escHtml(n.noi_dung)}
            </div>
            <div class="np-time ${fresh ? 'fresh' : ''}" data-unix="${unix}">${timeText}</div>
            ${inviteButtons}
        </div>
    </div>`;
}

async function onItemClick(e, id, url) {
    e.preventDefault();
    try {
        await fetch(`{{ url('/notifications') }}/${id}/read`, {
            method: 'PATCH',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });
    } catch(_) {}

    const n = notifData.find(n => n.id === id);
    if (n) {
        n.da_doc = true;
        const count = notifData.filter(n => !n.da_doc).length;
        updateBadge(count);
        renderList();
    }

    if (url && url !== '#') window.location.href = url;
}

async function handleInvite(token, action, notifId, btn) {
    const wrap = btn.closest('div[style*="display:flex"]');
    wrap.innerHTML = '<span style="font-size:12px;color:#9ca3af;padding:4px 0;">Đang xử lý...</span>';

    try {
        const res  = await fetch(`/notifications/invite-action/${token}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ action })
        });
        const data = await res.json();

        if (data.ok) {
            wrap.innerHTML = `<span style="font-size:12px;color:${action==='accept'?'#10b981':'#6b7280'};padding:4px 0;font-weight:700;">
                ${action === 'accept' ? '✓ Đã tham gia nhóm' : '✗ Đã từ chối'}
            </span>`;
            const n = notifData.find(n => n.id === notifId);
            if (n) n.da_doc = true;
            if (action === 'accept' && data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1200);
            }
        } else {
            wrap.innerHTML = `<span style="font-size:12px;color:#ef4444;">${data.message}</span>`;
        }
    } catch(e) {
        wrap.innerHTML = '<span style="font-size:12px;color:#ef4444;">Lỗi kết nối</span>';
    }
}

function switchTab(tab, el) {
    currentTab = tab;
    document.querySelectorAll('.np-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    renderList();
}

async function markAllRead() {
    try {
        await fetch('{{ route("api.notifications.mark-all-read") }}', {
            method: 'PATCH',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });
        notifData.forEach(n => n.da_doc = true);
        updateBadge(0);
        renderList();
    } catch(_) {}
}

function updateBadge(count) {
    const badge = document.getElementById('notifBadge');
    const safeCount = Number(count);
    if (!Number.isFinite(safeCount) || safeCount <= 0) {
        badge.classList.add('hidden');
        badge.textContent = '';
        return;
    }
    badge.classList.remove('hidden');
    badge.textContent = safeCount > 9 ? '9+' : String(safeCount);
    badge.classList.remove('pop');
    void badge.offsetWidth;
    badge.classList.add('pop');
}

async function pollBadge() {
    try {
        const res  = await fetch('{{ route("api.notifications.badge") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        updateBadge(data.count ?? data.unread_count ?? 0);
        if (notifOpen && notifLoaded) {
            notifLoaded = false;
            loadNotifications();
        }
    } catch(_) {}
}

pollBadge();
badgePollingTimer = setInterval(pollBadge, 30000);

function openCalendar() {
    document.getElementById('notifCalendar').classList.add('open');
    loadByDate(document.getElementById('calDatePicker').value);
}

function closeCalendar() {
    document.getElementById('notifCalendar').classList.remove('open');
}

async function loadByDate(date) {
    const result = document.getElementById('calResult');
    result.innerHTML = '<div class="np-loading"><div class="np-spinner"></div></div>';

    try {
        const res  = await fetch(`{{ route('api.notifications.by-date') }}?date=${date}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        const items = data.notifications || [];

        if (items.length === 0) {
            result.innerHTML = `<div class="np-empty">
                <div class="np-empty-icon">📭</div>
                <div class="np-empty-text">Không có thông báo ngày ${data.date}</div>
            </div>`;
            return;
        }

        // ✅ FIX: gán toàn bộ vào 1 assignment, bỏ dấu ; sai chỗ
        result.innerHTML = `<div class="np-cal-date-label" style="display: flex; align-items: center; gap: 8px; font-weight: 500; color: #374151;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>${data.date} — ${items.length} thông báo</span>
            </div>`
            + items.map(n => `
                <a href="${escHtml(n.url || '#')}" class="np-item ${n.da_doc ? '' : 'unread'}" style="text-decoration:none;">
                    <div class="np-av" style="background:#f3f4f6;font-size:20px;display:flex;align-items:center;justify-content:center;">
                        ${n.icon}
                    </div>
                    <div class="np-content">
                        <div class="np-text"><strong>${escHtml(n.tieu_de)}</strong> ${escHtml(n.noi_dung)}</div>
                        <div class="np-time">${n.time_ago}</div>
                    </div>
                </a>`).join('');

    } catch(e) {
        result.innerHTML = '<div class="np-empty"><div class="np-empty-text">Lỗi tải dữ liệu</div></div>';
    }
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('vi-VN', { day:'2-digit', month:'2-digit', year:'numeric' });
}
</script>
