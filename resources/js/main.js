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

(function () {
    const input = document.getElementById('searchInput');
    const dropdown = document.getElementById('searchDropdown');
    const spinner = document.getElementById('searchSpinner');

    if (!input || !dropdown || !spinner) {
        return;
    }

    const icons = {
        income: '<img src="/images/profits.png" style="width:18px;height:18px;object-fit:contain;">',
        expense: '<img src="/images/budget.png" style="width:18px;height:18px;object-fit:contain;">',
        category: '<img src="/images/category.png" style="width:18px;height:18px;object-fit:contain;">',
        wallet: '<img src="/images/wallet.png" style="width:18px;height:18px;object-fit:contain;">',
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
            fetch(`/search?q=${encodeURIComponent(query)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((response) => response.json())
                .then((data) => {
                    spinner.style.display = 'none';
                    const results = data.results || [];

                    if (results.length === 0) {
                        dropdown.innerHTML = `<div class="sr-empty">Không tìm thấy kết quả nào cho "<strong>${query}</strong>"</div>`;
                        dropdown.style.display = 'block';
                        return;
                    }

                    const groups = {};
                    results.forEach((result) => {
                        const group = result.type === 'transaction'
                            ? 'Giao dịch'
                            : result.type === 'category'
                                ? 'Danh mục'
                                : 'Ngân sách';

                        if (!groups[group]) {
                            groups[group] = [];
                        }

                        groups[group].push(result);
                    });

                    let html = '';

                    for (const [group, items] of Object.entries(groups)) {
                        html += `<div class="sr-header">${group}</div>`;
                        items.forEach((item) => {
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
                .catch(() => {
                    spinner.style.display = 'none';
                });
        }, 280);
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('#searchBar') && !event.target.closest('#searchDropdown')) {
            closeDropdown();
        }
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdown();
        }
    });
})();

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
        return {
            toastEnabled: true,
            toastPosition: 'top-right',
            toastDuration: 5,
            toastSound: false,
        };
    }
}

function _applyToastPosition(position) {
    const container = document.getElementById('toastContainer');

    if (!container) {
        return;
    }

    container.style.top = position.includes('bottom') ? 'auto' : '84px';
    container.style.bottom = position.includes('bottom') ? '20px' : 'auto';
    container.style.left = position.includes('left') ? '20px' : 'auto';
    container.style.right = position.includes('left') ? 'auto' : '20px';
}

_applyToastPosition(_getToastSettings().toastPosition);

window.showToast = function ({ type = 'info', title, message = '', action = null, id = null, duration = null }) {
    const settings = _getToastSettings();

    if (settings.toastEnabled === false) {
        return;
    }

    const timeout = duration ?? (settings.toastDuration * 1000);

    if (id && document.querySelector(`[data-toast-id="${id}"]`)) {
        return;
    }

    if (id && sessionStorage.getItem('tdismiss_' + id)) {
        return;
    }

    _applyToastPosition(settings.toastPosition);

    const icons = {
        success: '<img src="/images/check.png" style="width:20px;height:20px;object-fit:contain;">',
        error: '<img src="/images/warning.png" style="width:20px;height:20px;object-fit:contain;">',
        warning: '<img src="/images/alert.png" style="width:20px;height:20px;object-fit:contain;">',
        info: '<img src="/images/info.png" style="width:20px;height:20px;object-fit:contain;">',
    };

    const toast = document.createElement('div');
    toast.className = `g-toast ${type}`;

    if (id) {
        toast.dataset.toastId = id;
    }

    toast.innerHTML = `
        <div class="g-toast-icon">${icons[type] || 'i'}</div>
        <div class="g-toast-body">
            <div class="g-toast-title">${title}</div>
            ${message ? `<div class="g-toast-msg">${message}</div>` : ''}
            ${action ? `<a href="${action.url}" class="g-toast-action">${action.label}</a>` : ''}
        </div>
        <button class="g-toast-close" onclick="dismissToast(this,'${id}')">&times;</button>
        <div class="g-toast-progress" style="animation-duration:${timeout}ms"></div>
    `;

    document.getElementById('toastContainer')?.appendChild(toast);

    if (settings.toastSound) {
        try {
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = context.createOscillator();
            const gain = context.createGain();

            oscillator.connect(gain);
            gain.connect(context.destination);
            oscillator.frequency.value = type === 'error' ? 300 : 600;
            gain.gain.setValueAtTime(0.08, context.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, context.currentTime + 0.3);
            oscillator.start();
            oscillator.stop(context.currentTime + 0.3);
        } catch {}
    }

    if (timeout > 0) {
        setTimeout(() => dismissToast(toast.querySelector('.g-toast-close'), id), timeout);
    }
};

window.dismissToast = function (button, id = null) {
    const toast = button?.closest?.('.g-toast');

    if (!toast) {
        return;
    }

    toast.classList.add('hiding');

    if (id && id !== 'null') {
        sessionStorage.setItem('tdismiss_' + id, '1');
    }

    setTimeout(() => toast.remove(), 320);
};
