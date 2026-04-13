<div id="monebot-history-overlay" style="display:none">
    <div id="monebot-history-modal">
        <div id="mbh-header">
            <div id="mbh-header-left">
                <div id="mbh-header-icon"><i class="fas fa-clock-rotate-left"></i></div>
                <div>
                    <div id="mbh-header-title">Lịch sử hội thoại</div>
                    <div id="mbh-header-sub">Chọn cuộc trò chuyện để xem lại</div>
                </div>
            </div>
            <button id="mbh-close" title="Đóng">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="mbh-body">
            {{-- Cột trái: danh sách ngày --}}
            <div id="mbh-sidebar">
                <div id="mbh-search-wrap">
                    <i class="fas fa-search" id="mbh-search-icon"></i>
                    <input type="text" id="mbh-search" placeholder="Tìm kiếm hội thoại...">
                </div>
                <div id="mbh-list">
                    {{-- Render by JS --}}
                </div>
                <div id="mbh-empty" style="display:none">
                    <i class="fas fa-comments"></i>
                    <p>Chưa có hội thoại nào</p>
                </div>
                <div id="mbh-loading">
                    <div class="mbh-spinner"></div>
                    <p>Đang tải...</p>
                </div>
            </div>

            {{-- Cột phải: xem nội dung --}}
            <div id="mbh-preview">
                <div id="mbh-placeholder">
                    <div id="mbh-placeholder-icon"><i class="fas fa-hand-pointer"></i></div>
                    <p id="mbh-placeholder-title">Chọn một cuộc hội thoại</p>
                    <p id="mbh-placeholder-sub">Nội dung sẽ hiển thị ở đây</p>
                </div>
                <div id="mbh-messages" style="display:none">
                    {{-- Render by JS --}}
                </div>
            </div>
        </div>

        <div id="mbh-footer">
            <div id="mbh-selected-info">
                <i class="fas fa-circle-info"></i>
                <span id="mbh-selected-label">Chưa chọn cuộc hội thoại nào</span>
            </div>
            <div id="mbh-footer-actions">
                <button class="mbh-btn mbh-btn-secondary" id="mbh-cancel">Đóng</button>
                <button class="mbh-btn mbh-btn-primary" id="mbh-apply" disabled>
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    Áp dụng vào chat
                </button>
            </div>
        </div>

    </div>
</div>

<style>
#monebot-history-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(4px);
    z-index: 99998;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: mbhFadeIn 0.18s ease;
}
#monebot-history-overlay.mbh-hidden { display: none !important; }

@keyframes mbhFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

#monebot-history-modal {
    background: var(--color-background, #fff);
    border-radius: 18px;
    width: 100%;
    max-width: 860px;
    height: 580px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 24px 64px rgba(0,0,0,0.18);
    overflow: hidden;
    animation: mbhSlideUp 0.22s cubic-bezier(.22,.68,0,1.2);
}
@keyframes mbhSlideUp {
    from { transform: translateY(24px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

#mbh-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    border-bottom: 1px solid var(--color-border, #f0f0f0);
    flex-shrink: 0;
}
#mbh-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
#mbh-header-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: var(--primary, #4f6ef7);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
#mbh-header-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--color-text-primary, #111);
}
#mbh-header-sub {
    font-size: 12px;
    color: var(--color-text-secondary, #888);
    margin-top: 1px;
}
#mbh-close {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: none;
    background: none;
    cursor: pointer;
    color: var(--color-text-secondary, #888);
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s, color 0.15s;
}
#mbh-close:hover {
    background: var(--color-background-secondary, #f5f5f5);
    color: var(--color-text-primary, #111);
}

#mbh-body {
    display: flex;
    flex: 1;
    overflow: hidden;
}

#mbh-sidebar {
    width: 280px;
    flex-shrink: 0;
    border-right: 1px solid var(--color-border, #f0f0f0);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
#mbh-search-wrap {
    position: relative;
    padding: 12px 14px;
    border-bottom: 1px solid var(--color-border, #f0f0f0);
    flex-shrink: 0;
}
#mbh-search-icon {
    position: absolute;
    left: 26px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-text-secondary, #aaa);
    font-size: 12px;
    pointer-events: none;
}
#mbh-search {
    width: 100%;
    padding: 8px 10px 8px 32px;
    border: 1px solid var(--color-border, #e8e8e8);
    border-radius: 8px;
    font-size: 13px;
    background: var(--color-background-secondary, #f8f8f8);
    color: var(--color-text-primary, #111);
    outline: none;
    transition: border-color 0.15s;
    box-sizing: border-box;
}
#mbh-search:focus {
    border-color: var(--primary, #4f6ef7);
    background: #fff;
}
#mbh-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0;
}
#mbh-list::-webkit-scrollbar { width: 4px; }
#mbh-list::-webkit-scrollbar-track { background: transparent; }
#mbh-list::-webkit-scrollbar-thumb { background: var(--color-border, #e0e0e0); border-radius: 4px; }

/* Group item (theo ngày) */
.mbh-group-label {
    padding: 8px 16px 4px;
    font-size: 11px;
    font-weight: 700;
    color: var(--color-text-secondary, #aaa);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.mbh-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 16px;
    cursor: pointer;
    border-radius: 0;
    transition: background 0.12s;
    position: relative;
}
.mbh-item:hover { background: var(--color-background-secondary, #f8f8f8); }
.mbh-item.is-active {
    background: var(--color-primary-light, #eef3ff);
}
.mbh-item.is-active::before {
    content: '';
    position: absolute;
    left: 0; top: 6px; bottom: 6px;
    width: 3px;
    background: var(--primary, #4f6ef7);
    border-radius: 0 3px 3px 0;
}
.mbh-item-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--color-background-secondary, #f0f0f0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    color: var(--primary, #4f6ef7);
    flex-shrink: 0;
    margin-top: 1px;
}
.mbh-item-body { flex: 1; min-width: 0; }
.mbh-item-preview {
    font-size: 13px;
    font-weight: 500;
    color: var(--color-text-primary, #111);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4;
}
.mbh-item-meta {
    font-size: 11px;
    color: var(--color-text-secondary, #aaa);
    margin-top: 2px;
}
.mbh-item-badge {
    font-size: 11px;
    background: var(--color-background-secondary, #f0f0f0);
    color: var(--color-text-secondary, #888);
    border-radius: 20px;
    padding: 2px 7px;
    flex-shrink: 0;
    font-weight: 600;
}
.mbh-item.is-active .mbh-item-badge {
    background: var(--primary, #4f6ef7);
    color: #fff;
}

/* Loading / Empty */
#mbh-loading, #mbh-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    gap: 10px;
    color: var(--color-text-secondary, #aaa);
    font-size: 13px;
}
#mbh-empty i { font-size: 28px; opacity: 0.3; }
.mbh-spinner {
    width: 24px; height: 24px;
    border: 2px solid var(--color-border, #e0e0e0);
    border-top-color: var(--primary, #4f6ef7);
    border-radius: 50%;
    animation: mbhSpin 0.7s linear infinite;
}
@keyframes mbhSpin { to { transform: rotate(360deg); } }

#mbh-preview {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--color-background-secondary, #fafafa);
}

/* Placeholder */
#mbh-placeholder {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 40px;
    text-align: center;
}
#mbh-placeholder-icon {
    width: 56px; height: 56px;
    border-radius: 16px;
    background: var(--color-background, #fff);
    border: 1px solid var(--color-border, #e8e8e8);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    color: var(--color-text-secondary, #ccc);
    margin-bottom: 4px;
}
#mbh-placeholder-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--color-text-primary, #111);
    margin: 0;
}
#mbh-placeholder-sub {
    font-size: 12px;
    color: var(--color-text-secondary, #aaa);
    margin: 0;
}

/* Messages */
#mbh-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
#mbh-messages::-webkit-scrollbar { width: 4px; }
#mbh-messages::-webkit-scrollbar-track { background: transparent; }
#mbh-messages::-webkit-scrollbar-thumb { background: var(--color-border, #e0e0e0); border-radius: 4px; }

.mbh-msg {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.mbh-msg--user { flex-direction: row-reverse; }

.mbh-msg-avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
    overflow: hidden;
}
.mbh-msg-avatar img { width: 100%; height: 100%; object-fit: cover; }
.mbh-msg-avatar--user {
    background: var(--primary, #4f6ef7);
    color: #fff;
}
.mbh-msg-avatar--bot {
    background: var(--color-background, #fff);
    border: 1px solid var(--color-border, #e8e8e8);
}

.mbh-msg-bubble {
    max-width: 75%;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 13px;
    line-height: 1.6;
}
.mbh-msg--bot .mbh-msg-bubble {
    background: var(--color-background, #fff);
    color: var(--color-text-primary, #111);
    border: 1px solid var(--color-border, #e8e8e8);
    border-top-left-radius: 4px;
}
.mbh-msg--user .mbh-msg-bubble {
    background: var(--primary, #4f6ef7);
    color: #fff;
    border-top-right-radius: 4px;
}

#mbh-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 22px;
    border-top: 1px solid var(--color-border, #f0f0f0);
    background: var(--color-background, #fff);
    flex-shrink: 0;
    gap: 12px;
}
#mbh-selected-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--color-text-secondary, #888);
    flex: 1;
    min-width: 0;
}
#mbh-selected-info i { flex-shrink: 0; }
#mbh-selected-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
#mbh-footer-actions { display: flex; gap: 8px; flex-shrink: 0; }

.mbh-btn {
    padding: 9px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: all 0.15s;
    white-space: nowrap;
}
.mbh-btn-secondary {
    background: var(--color-background-secondary, #f5f5f5);
    color: var(--color-text-primary, #333);
}
.mbh-btn-secondary:hover { background: var(--color-border, #e8e8e8); }
.mbh-btn-primary {
    background: var(--primary, #4f6ef7);
    color: #fff;
}
.mbh-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.mbh-btn-primary:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

@media (max-width: 620px) {
    #monebot-history-modal { height: 92vh; border-radius: 16px; }
    #mbh-sidebar { width: 200px; }
    .mbh-msg-bubble { max-width: 85%; }
}
@media (max-width: 480px) {
    #mbh-body { flex-direction: column; }
    #mbh-sidebar { width: 100%; height: 200px; border-right: none; border-bottom: 1px solid var(--color-border, #f0f0f0); }
}
</style>

<script>
(function () {
    const API_URL     = '/api/v1/ai/history';
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content
                     || document.getElementById('monebot')?.dataset.csrf
                     || '';
    const BOT_AVATAR  = '{{ asset("images/AI assistant.png") }}';
    const USER_LETTER = '{{ $monebotInitial ?? "U" }}';

    const overlay     = document.getElementById('monebot-history-overlay');
    const list        = document.getElementById('mbh-list');
    const loadingEl   = document.getElementById('mbh-loading');
    const emptyEl     = document.getElementById('mbh-empty');
    const placeholder = document.getElementById('mbh-placeholder');
    const messagesEl  = document.getElementById('mbh-messages');
    const selectedLbl = document.getElementById('mbh-selected-label');
    const applyBtn    = document.getElementById('mbh-apply');
    const searchInput = document.getElementById('mbh-search');

    let allGroups    = [];
    let selectedGroup = null;

    overlay.style.display = 'none';

    window.openMonebotHistory = function () {
        overlay.style.display = 'flex';
        loadHistory();
    };

    window.closeMonebotHistory = function (reopenChat = true) {
        overlay.style.display = 'none';
        selectedGroup = null;
        applyBtn.disabled = true;
        selectedLbl.textContent = 'Chưa chọn cuộc hội thoại nào';
        if (reopenChat && typeof window.monebotSetOpen === 'function') {
            window.monebotSetOpen(true);
        }
    };

    // Nút Đóng/X → mở lại chat
    document.getElementById('mbh-close').addEventListener('click', function () { closeMonebotHistory(true); });
    document.getElementById('mbh-cancel').addEventListener('click', function () { closeMonebotHistory(true); });
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeMonebotHistory(true);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.style.display !== 'none') closeMonebotHistory();
    });

    function loadHistory() {
        list.innerHTML = '';
        emptyEl.style.display     = 'none';
        loadingEl.style.display   = 'flex';
        placeholder.style.display = 'flex';
        messagesEl.style.display  = 'none';
        selectedGroup             = null;
        applyBtn.disabled         = true;
        selectedLbl.textContent   = 'Chưa chọn cuộc hội thoại nào';

        const token = localStorage.getItem('token') || '';
        const headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        };
        if (token) headers['Authorization'] = 'Bearer ' + token;

        fetch(API_URL, { headers })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                loadingEl.style.display = 'none';
                if (!data.success || !data.history || data.history.length === 0) {
                    emptyEl.style.display = 'flex';
                    return;
                }
                allGroups = data.history;
                renderList(allGroups);
            })
            .catch(function () {
                loadingEl.style.display = 'none';
                emptyEl.style.display   = 'flex';
            });
    }

    function renderList(groups) {
        list.innerHTML = '';
        if (groups.length === 0) {
            emptyEl.style.display = 'flex';
            return;
        }
        emptyEl.style.display = 'none';

        groups.forEach(function (group) {
            const labelEl = document.createElement('div');
            labelEl.className   = 'mbh-group-label';
            labelEl.textContent = group.label;
            list.appendChild(labelEl);

            const item = document.createElement('div');
            item.className = 'mbh-item';
            item.innerHTML = `
                <div class="mbh-item-icon"><i class="fas fa-comments"></i></div>
                <div class="mbh-item-body">
                    <div class="mbh-item-preview">${escHtml(group.preview)}</div>
                    <div class="mbh-item-meta">${group.label}</div>
                </div>
                <div class="mbh-item-badge">${group.count}</div>
            `;

            item.addEventListener('click', function () {
                list.querySelectorAll('.mbh-item').forEach(el => el.classList.remove('is-active'));
                item.classList.add('is-active');
                selectGroup(group);
            });

            list.appendChild(item);
        });
    }

    function selectGroup(group) {
        selectedGroup = group;
        selectedLbl.textContent   = group.label + ' · ' + group.count + ' tin nhắn';
        applyBtn.disabled         = false;
        placeholder.style.display = 'none';
        messagesEl.style.display  = 'flex';
        messagesEl.innerHTML      = '';

        group.messages.forEach(function (msg) {
            const userEl = document.createElement('div');
            userEl.className = 'mbh-msg mbh-msg--user';
            userEl.innerHTML = `
                <div class="mbh-msg-avatar mbh-msg-avatar--user">${escHtml(USER_LETTER)}</div>
                <div class="mbh-msg-bubble">${escHtml(msg.user_message).replace(/\n/g,'<br>')}</div>
            `;
            messagesEl.appendChild(userEl);

            const botEl = document.createElement('div');
            botEl.className = 'mbh-msg mbh-msg--bot';
            botEl.innerHTML = `
                <div class="mbh-msg-avatar mbh-msg-avatar--bot">
                    <img src="${BOT_AVATAR}" alt="MoneBot">
                </div>
                <div class="mbh-msg-bubble">${escHtml(msg.ai_response).replace(/\n/g,'<br>')}</div>
            `;
            messagesEl.appendChild(botEl);
        });

        messagesEl.scrollTop = 0;
    }

    applyBtn.addEventListener('click', function () {
        if (!selectedGroup) return;
        const groupToLoad = selectedGroup; // lưu trước
        closeMonebotHistory(false);        // đóng modal, không mở chat

        if (typeof window.monebotSetOpen === 'function')   window.monebotSetOpen(true);
        if (typeof window.monebotClearMsgs === 'function') window.monebotClearMsgs();
        if (typeof window.monebotAppendMsg === 'function') {
            groupToLoad.messages.forEach(function (msg) {
                window.monebotAppendMsg('user', msg.user_message, { skipSave: true });
                window.monebotAppendMsg('bot',  msg.ai_response,  { skipSave: true });
            });
        }
    });

    searchInput.addEventListener('input', function () {
        const q = searchInput.value.trim().toLowerCase();
        if (!q) { renderList(allGroups); return; }
        const filtered = allGroups.filter(function (g) {
            return g.preview.toLowerCase().includes(q) ||
                g.messages.some(function (m) {
                    return m.user_message.toLowerCase().includes(q) ||
                           m.ai_response.toLowerCase().includes(q);
                });
        });
        renderList(filtered);
    });

    function escHtml(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
            .replace(/'/g,'&#39;');
    }

})();
</script>