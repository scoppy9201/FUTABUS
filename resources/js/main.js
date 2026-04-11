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