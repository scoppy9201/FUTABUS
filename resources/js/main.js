const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark') {
    document.body.classList.add('dark');
}

document.getElementById('themeToggle')?.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    const isDark = document.body.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');

    try {
        const settings = JSON.parse(localStorage.getItem('monexa_settings') || '{}');
        settings.darkMode = isDark;
        localStorage.setItem('monexa_settings', JSON.stringify(settings));
    } catch {}
});

const userProfile = document.getElementById('userProfile');
const profileDropdown = document.getElementById('profileDropdown');

userProfile?.addEventListener('click', (event) => {
    event.stopPropagation();
    profileDropdown?.classList.toggle('show');
});

document.addEventListener('click', () => {
    profileDropdown?.classList.remove('show');
});

profileDropdown?.addEventListener('click', (event) => {
    event.stopPropagation();
});

// SEARCH
(function () {
    const input = document.getElementById('searchInput');
    const dropdown = document.getElementById('searchDropdown');
    const spinner = document.getElementById('searchSpinner');

    if (!input || !dropdown || !spinner) return;

    const icons = {
        income:   '<img src="/images/profits.png"  style="width:18px;height:18px;object-fit:contain;">',
        expense:  '<img src="/images/budget.png"   style="width:18px;height:18px;object-fit:contain;">',
        category: '<img src="/images/category.png" style="width:18px;height:18px;object-fit:contain;">',
        wallet:   '<img src="/images/wallet.png"   style="width:18px;height:18px;object-fit:contain;">',
    };

    let timer;

    function closeDropdown() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const query = this.value.trim();

        if (query.length < 2) {
            closeDropdown();
            spinner.style.display = 'none';
            return;
        }

        spinner.style.display = 'block';

        timer = setTimeout(() => {
            const bearerToken = localStorage.getItem('token') ?? '';
            const headers = { 'X-Requested-With': 'XMLHttpRequest' };
            if (bearerToken) headers['Authorization'] = `Bearer ${bearerToken}`;

            fetch(`/api/v1/search?q=${encodeURIComponent(query)}`, { headers })
                .then(res => res.json())
                .then(data => {
                    spinner.style.display = 'none';
                    const results = data.results || [];

                    if (results.length === 0) {
                        dropdown.innerHTML = `<div class="sr-empty">Không tìm thấy kết quả nào cho "<strong>${query}</strong>"</div>`;
                        dropdown.style.display = 'block';
                        return;
                    }

                    const groups = {};
                    results.forEach(result => {
                        const group = result.type === 'transaction' ? 'Giao dịch'
                            : result.type === 'category' ? 'Danh mục' : 'Ngân sách';
                        if (!groups[group]) groups[group] = [];
                        groups[group].push(result);
                    });

                    let html = '';
                    for (const [group, items] of Object.entries(groups)) {
                        html += `<div class="sr-header">${group}</div>`;
                        items.forEach(item => {
                            html += `<a href="${item.url}" class="sr-item">
                                <div class="sr-dot ${item.badge}">${icons[item.badge] || '*'}</div>
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

    document.addEventListener('click', event => {
        if (!event.target.closest('#searchBar') && !event.target.closest('#searchDropdown')) {
            closeDropdown();
        }
    });

    input.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeDropdown();
    });
})();

(function () {
    const contentEl = document.getElementById('mainContent');
    if (!contentEl) return;

    // Các path không dùng SPA → hard reload bình thường
    const excludePatterns = [
        '/login', '/register', '/logout',
        '/auth/google', '/forgot-password',
        '/verify-code', '/reset-password',
    ];

    function shouldExclude(url) {
        return excludePatterns.some(p => url.includes(p));
    }

    // Lưu state trang hiện tại
    history.replaceState({ url: location.href }, '', location.href);

    // Cập nhật active state trong sidebar 
    function updateActiveMenu(url) {
        const currentPath = new URL(url, location.origin).pathname;

        document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
        document.querySelectorAll('.sub-menu li').forEach(i => i.classList.remove('active'));

        document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href');
            if (!href || href === 'javascript:void(0)') return;
            try {
                const linkPath = new URL(href, location.origin).pathname;
                if (linkPath === currentPath) {
                    link.closest('.nav-item')?.classList.add('active');
                }
            } catch {}
        });

        document.querySelectorAll('.sub-menu a').forEach(link => {
            const href = link.getAttribute('href');
            if (!href || href === 'javascript:void(0)') return;
            try {
                const linkPath = new URL(href, location.origin).pathname;
                if (linkPath === currentPath) {
                    link.closest('li')?.classList.add('active');
                    link.closest('.has-sub')?.classList.add('active');
                }
            } catch {}
        });
    }

    function navigateTo(url, pushState = true) {
        if (shouldExclude(url)) {
            window.location.href = url;
            return;
        }

        // Fade out
        contentEl.style.opacity = '0.4';
        contentEl.style.transition = 'opacity 0.12s ease';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-SPA-Request': 'true',
            },
        })
            .then(res => {
                // Nếu server redirect sang login → hard redirect
                if (res.redirected && shouldExclude(res.url)) {
                    window.location.href = res.url;
                    return null;
                }
                return res.text();
            })
            .then(html => {
                if (!html) return;

                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Lấy phần nội dung bên trong #mainContent
                const newContent = doc.getElementById('mainContent');
                contentEl.innerHTML = newContent
                    ? newContent.innerHTML
                    : (doc.querySelector('.content')?.innerHTML ?? html);

                // Cập nhật <title> tab trình duyệt
                const newTitle = doc.querySelector('title');
                if (newTitle) document.title = newTitle.textContent;

                // Push URL
                if (pushState) history.pushState({ url }, '', url);

                // Cập nhật active menu
                updateActiveMenu(url);

                // Chạy lại các <script> inline trong content mới
                contentEl.querySelectorAll('script').forEach(old => {
                    // Bỏ qua script external đã load rồi (sweetalert, cdn...)
                    if (old.src) {
                        if (!document.querySelector(`script[src="${old.src}"]`)) {
                            const s = document.createElement('script');
                            s.src = old.src;
                            s.async = false;
                            old.replaceWith(s);
                        } else {
                            old.remove(); 
                        }
                        return;
                    }

                    const s = document.createElement('script');
                    s.textContent = `(function(){ ${old.textContent} })();`;
                    old.replaceWith(s);
                });

                // Scroll lên đầu trang
                window.scrollTo({ top: 0, behavior: 'smooth' });

                // Fade in
                contentEl.style.opacity = '1';

                // Thông báo cho các module khác (chart, datatable, v.v.)
                window.dispatchEvent(new CustomEvent('spa:navigated', { detail: { url } }));
            })
            .catch(() => {
                // Lỗi mạng → fallback hard reload
                window.location.href = url;
            });
    }

    //Bắt click trên toàn sidebar 
    function interceptLinks() {
        document.querySelectorAll('.nav-link, .sub-menu a, .dropdown-item').forEach(link => {
            if (link.dataset.spabound === '1') return; // tránh bind 2 lần
            link.dataset.spabound = '1';

            link.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (!href || href === 'javascript:void(0)' || href.startsWith('#')) return;
                if (this.target === '_blank') return;
                if (shouldExclude(href)) return;

                e.preventDefault();
                navigateTo(href);
            });
        });
    }

    // Nút Back / Forward của trình duyệt 
    window.addEventListener('popstate', e => {
        if (e.state?.url) navigateTo(e.state.url, false);
        else window.location.reload();
    });

    // Khởi chạy
    interceptLinks();

    // Re-intercept nếu sidebar DOM thay đổi
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        new MutationObserver(() => interceptLinks())
            .observe(sidebar, { childList: true, subtree: true });
    }

    const profileDropdown = document.getElementById('profileDropdown');
    if (profileDropdown) {
        new MutationObserver(() => interceptLinks())
            .observe(profileDropdown, { childList: true, subtree: true });
    }

    interceptLinks();
})();

// TOAST
    function _getToastSettings() {
        try {
            return {
                toastEnabled: true,
                toastPosition: 'top-right',
                toastDuration: 5,
                toastSound: false,
                ...JSON.parse(localStorage.getItem('monexa_settings') || '{}'),
            };
        } catch {
            return { toastEnabled: true, toastPosition: 'top-right', toastDuration: 5, toastSound: false };
        }
    }

    function _applyToastPosition(position) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        container.style.top    = position.includes('bottom') ? 'auto' : '84px';
        container.style.bottom = position.includes('bottom') ? '20px' : 'auto';
        container.style.left   = position.includes('left')   ? '20px' : 'auto';
        container.style.right  = position.includes('left')   ? 'auto' : '20px';
    }

    _applyToastPosition(_getToastSettings().toastPosition);

    window.showToast = function ({ type = 'info', title, message = '', action = null, id = null, duration = null }) {
        const settings = _getToastSettings();
        if (settings.toastEnabled === false) return;

        const timeout = duration ?? (settings.toastDuration * 1000);
        if (id && document.querySelector(`[data-toast-id="${id}"]`)) return;
        if (id && sessionStorage.getItem('tdismiss_' + id)) return;

        _applyToastPosition(settings.toastPosition);

        const icons = {
            success: '<img src="/images/check.png"   style="width:20px;height:20px;object-fit:contain;">',
            error:   '<img src="/images/warning.png" style="width:20px;height:20px;object-fit:contain;">',
            warning: '<img src="/images/alert.png"   style="width:20px;height:20px;object-fit:contain;">',
            info:    '<img src="/images/info.png"    style="width:20px;height:20px;object-fit:contain;">',
        };

        const toast = document.createElement('div');
        toast.className = `g-toast ${type}`;
        if (id) toast.dataset.toastId = id;

        toast.innerHTML = `
            <div class="g-toast-icon">${icons[type] || 'i'}</div>
            <div class="g-toast-body">
                <div class="g-toast-title">${title}</div>
                ${message ? `<div class="g-toast-msg">${message}</div>` : ''}
                ${action  ? `<a href="${action.url}" class="g-toast-action">${action.label}</a>` : ''}
            </div>
            <button class="g-toast-close" onclick="dismissToast(this,'${id}')">&times;</button>
            <div class="g-toast-progress" style="animation-duration:${timeout}ms"></div>
        `;

        document.getElementById('toastContainer')?.appendChild(toast);

        if (settings.toastSound) {
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

        if (timeout > 0) {
            setTimeout(() => dismissToast(toast.querySelector('.g-toast-close'), id), timeout);
        }
    };

    window.dismissToast = function (button, id = null) {
        const toast = button?.closest?.('.g-toast');
        if (!toast) return;
        toast.classList.add('hiding');
        if (id && id !== 'null') sessionStorage.setItem('tdismiss_' + id, '1');
        setTimeout(() => toast.remove(), 320);
    };