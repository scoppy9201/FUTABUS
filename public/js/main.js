        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') document.body.classList.add('dark');

        document.getElementById('themeToggle')?.addEventListener('click', () => {
        document.body.classList.toggle('dark');
        const isDark = document.body.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            try {
                const s = JSON.parse(localStorage.getItem('monexa_settings') || '{}');
                s.darkMode = isDark;
                localStorage.setItem('monexa_settings', JSON.stringify(s));
            } catch {}
        });

        const userProfile = document.getElementById('userProfile');
        const dropdown = document.getElementById('profileDropdown');

        userProfile?.addEventListener('click', e => {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });

        document.addEventListener('click', () => dropdown.classList.remove('show'));
        dropdown?.addEventListener('click', e => e.stopPropagation());

        (function() {
            const input    = document.getElementById('searchInput');
            const dropdown = document.getElementById('searchDropdown');
            const spinner  = document.getElementById('searchSpinner');
            if (!input) return;

            const icons = {
                income:   '<img src="/images/profits.png" style="width:18px;height:18px;object-fit:contain;">',
                expense:  '<img src="/images/budget.png" style="width:18px;height:18px;object-fit:contain;">',
                category: '<img src="/images/category.png" style="width:18px;height:18px;object-fit:contain;">',
                wallet:   '<img src="/images/wallet.png" style="width:18px;height:18px;object-fit:contain;">',
            };

            let timer;

            function closeDropdown() {
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
            }

            input.addEventListener('input', function() {
                clearTimeout(timer);
                const q = this.value.trim();
                if (q.length < 2) { closeDropdown(); spinner.style.display = 'none'; return; }

                spinner.style.display = 'block';
                timer = setTimeout(() => {
                    fetch(`/search?q=${encodeURIComponent(q)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        spinner.style.display = 'none';
                        const results = data.results || [];
                        if (results.length === 0) {
                            dropdown.innerHTML = `<div class="sr-empty">Không tìm thấy kết quả nào cho "<strong>${q}</strong>"</div>`;
                            dropdown.style.display = 'block';
                            return;
                        }

                        const groups = {};
                        results.forEach(r => {
                            const g = r.type === 'transaction' ? 'Giao dịch' : r.type === 'category' ? 'Danh mục' : 'Ngân sách';
                            if (!groups[g]) groups[g] = [];
                            groups[g].push(r);
                        });

                        let html = '';
                        for (const [group, items] of Object.entries(groups)) {
                            html += `<div class="sr-header">${group}</div>`;
                            items.forEach(item => {
                                html += `<a href="${item.url}" class="sr-item">
                                    <div class="sr-dot ${item.badge}">${icons[item.badge] || '•'}</div>
                                    <div>
                                        <div class="sr-label">${item.label}</div>
                                        <div class="sr-sub">${item.sub}</div>
                                    </div>
                                </a>`;
                            });
                        }
                        dropdown.innerHTML = html;
                        dropdown.style.display = 'block';
                    })
                    .catch(() => { spinner.style.display = 'none'; });
                }, 280);
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('#searchBar') && !e.target.closest('#searchDropdown')) {
                    closeDropdown();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeDropdown();
            });
        })();

        (function() {
            const btn       = document.getElementById('aiBubbleBtn');
            const box       = document.getElementById('aiChatBox');
            const welcome   = document.getElementById('acbWelcome');
            const messages  = document.getElementById('acbMessages');
            const chips     = document.getElementById('acbChips');
            const input     = document.getElementById('acbInput');
            const sendBtn   = document.getElementById('acbSendBtn');
            const badge     = document.getElementById('aiBadge');

            let isOpen      = false;
            let chatStarted = false;
            let hasUnread   = false;

            window.toggleAIChat = function() {
                isOpen = !isOpen;
                box.classList.toggle('open', isOpen);
                btn.classList.toggle('open', isOpen);
                if (isOpen && hasUnread) {
                    hasUnread = false;
                    badge.classList.remove('show');
                }
            };

            // Close when click outside
            document.addEventListener('click', function(e) {
                if (isOpen && !document.getElementById('aiBubble').contains(e.target)) {
                    isOpen = false;
                    box.classList.remove('open');
                    btn.classList.remove('open');
                }
            });

            input.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
                sendBtn.disabled = !this.value.trim();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (!sendBtn.disabled) doSend();
                }
            });

            sendBtn.addEventListener('click', doSend);

            function doSend() {
                const text = input.value.trim();
                if (!text) return;
                input.value = '';
                input.style.height = 'auto';
                sendBtn.disabled = true;
                window.acbSend(text);
            }

            window.acbSend = function(text) {
                if (!chatStarted) {
                    chatStarted = true;
                    welcome.style.display = 'none';
                    messages.style.display = 'flex';
                    chips.style.display = 'flex';
                }

                appendMsg('user', text);

                const thinkId = 'think-' + Date.now();
                appendMsg('ai', '<div class="acb-typing"><span></span><span></span><span></span></div>', thinkId);

                fetch('{{ route("ai-assistant.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: text })
                })
                .then(r => r.json())
                .then(data => {
                    document.getElementById(thinkId)?.remove();
                    const reply = data.success ? data.message : 'Xin lỗi, có lỗi xảy ra. Thử lại nhé!';
                    appendMsg('ai', reply);

                    if (!isOpen) {
                        hasUnread = true;
                        badge.classList.add('show');
                    }
                })
                .catch(() => {
                    document.getElementById(thinkId)?.remove();
                    appendMsg('ai', 'Không thể kết nối. Vui lòng thử lại sau.');
                });
            };

            function appendMsg(sender, content, id = null) {
                const div = document.createElement('div');
                div.className = 'acb-msg ' + sender;
                if (id) div.id = id;

                const initials = sender === 'user'
                    ? '{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}'
                    : 'AI';

                div.innerHTML = `
                    <div class="acb-msg-avatar">${initials}</div>
                    <div class="acb-msg-bubble">${content.replace(/\n/g, '<br>')}</div>
                `;
                messages.appendChild(div);
                messages.scrollTop = messages.scrollHeight;
            }

            window.clearAIChat = function() {
                chatStarted = false;
                messages.innerHTML = '';
                messages.style.display = 'none';
                chips.style.display = 'none';
                welcome.style.display = 'flex';
            };
        })();
        // Đọc settings từ localStorage
        function _getToastSettings() {
            try { return { toastEnabled: true, toastPosition: 'top-right', toastDuration: 5, toastSound: false, ...JSON.parse(localStorage.getItem('monexa_settings') || '{}') }; }
            catch { return { toastEnabled: true, toastPosition: 'top-right', toastDuration: 5, toastSound: false }; }
        }

        function _applyToastPosition(pos) {
            const c = document.getElementById('toastContainer');
            if (!c) return;
            c.style.top    = pos.includes('bottom') ? 'auto' : '84px';
            c.style.bottom = pos.includes('bottom') ? '20px' : 'auto';
            c.style.left   = pos.includes('left')   ? '20px' : 'auto';
            c.style.right  = pos.includes('left')   ? 'auto' : '20px';
        }

        // Áp dụng vị trí ngay khi load
        _applyToastPosition(_getToastSettings().toastPosition);

        window.showToast = function({ type = 'info', title, message = '', action = null, id = null, duration = null }) {
            const s = _getToastSettings();
            if (s.toastEnabled === false) return;

            const ms = duration ?? (s.toastDuration * 1000);
            if (id && document.querySelector(`[data-toast-id="${id}"]`)) return;
            if (id && sessionStorage.getItem('tdismiss_' + id)) return;

            _applyToastPosition(s.toastPosition);

            const icons = {
            success: '<img src="/images/check.png"   style="width:20px;height:20px;object-fit:contain;">',
            error:   '<img src="/images/warning.png" style="width:20px;height:20px;object-fit:contain;">',
            warning: '<img src="/images/alert.png"   style="width:20px;height:20px;object-fit:contain;">',
            info:    '<img src="/images/info.png"    style="width:20px;height:20px;object-fit:contain;">',
        };
            const el = document.createElement('div');
            el.className = `g-toast ${type}`;
            if (id) el.dataset.toastId = id;

            el.innerHTML = `
                <div class="g-toast-icon">${icons[type] || 'ℹ️'}</div>
                <div class="g-toast-body">
                    <div class="g-toast-title">${title}</div>
                    ${message ? `<div class="g-toast-msg">${message}</div>` : ''}
                    ${action ? `<a href="${action.url}" class="g-toast-action">${action.label}</a>` : ''}
                </div>
                <button class="g-toast-close" onclick="dismissToast(this,'${id}')">&times;</button>
                <div class="g-toast-progress" style="animation-duration:${ms}ms"></div>
            `;

            document.getElementById('toastContainer')?.appendChild(el);

            // Sound
            if (s.toastSound) {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.frequency.value = type === 'error' ? 300 : 600;
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                    osc.start(); osc.stop(ctx.currentTime + 0.3);
                } catch {}
            }

            if (ms > 0) setTimeout(() => dismissToast(el.querySelector('.g-toast-close'), id), ms);
        };

        window.dismissToast = function(btn, id = null) {
            const toast = btn?.closest?.('.g-toast');
            if (!toast) return;
            toast.classList.add('hiding');
            if (id && id !== 'null') sessionStorage.setItem('tdismiss_' + id, '1');
            setTimeout(() => toast.remove(), 320);
        };