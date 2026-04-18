@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    echo '<style>' . file_get_contents(resource_path('css/dashboard.css')) . '</style>';
@endphp
<div
    class="dashboard-container"
    id="dashboardPage"
    data-api-url="{{ url('/api/v1/dashboard') }}"
    data-login-url="{{ route('login') }}"
    data-wallet-icon="{{ asset('images/wallet.png') }}"
    data-profits-icon="{{ asset('images/profits.png') }}"
    data-budget-icon="{{ asset('images/budget.png') }}"
    data-saving-icon="{{ asset('images/saving.png') }}"
    data-arrow-up-icon="{{ asset('images/arrow-up.png') }}"
    data-arrow-down-icon="{{ asset('images/arrow-down.png') }}"
    data-empty-icon="{{ asset('images/empty-folder.png') }}"
    data-safe-icon="{{ asset('images/check.png') }}"
    data-category-icon-base="{{ asset('images/category-icons') }}"
>
    <div class="page-header">
        <div class="page-copy">
            <div class="page-title">
                <div class="page-icon">
                    <img src="{{ asset('images/home.png') }}" alt="Dashboard">
                </div>
                <span>Dashboard</span>
            </div>
            <p class="page-subtitle">Theo dõi tổng quan, xu hướng thu chi và ngân sách trong một màn hình.</p>
        </div>

        <div class="date-filter">
            <select id="month-filter">
                <option value="all">Tất cả thời gian</option>
                <option value="this_month" selected>Tháng này</option>
                <option value="last_month">Tháng trước</option>
                <option value="this_year">Năm nay</option>
            </select>

            <button id="export-report-btn" class="export-btn">
                <img src="{{ asset('images/export.png') }}" alt="Export">
                Xuất báo cáo
            </button>
        </div>
    </div>

    <div id="dashboard-alert" hidden></div>
    <div id="toast" class="alert" style="display:none;position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;max-width:400px;"></div>
    <div id="dashboard-loading" class="card dashboard-state">
        <p>Đang tải dữ liệu dashboard...</p>
    </div>

    <div id="dashboard-content" class="dashboard-content" hidden>
        <div id="stats-row" class="stats-row"></div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ asset('images/chart.png') }}" alt="Chart">
                        Biểu đồ thu chi 
                    </h3>
                    <div class="card-menu">
                        <img src="{{ asset('images/plus.png') }}" alt="More">
                    </div>
                </div>
                <div class="chart-shell">
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ asset('images/transaction.png') }}" alt="Category">
                        Phân bố chi tiêu 
                    </h3>
                    <div class="card-menu">
                        <img src="{{ asset('images/plus.png') }}" alt="More">
                    </div>
                </div>
                <div id="expense-pie-chart-shell" class="chart-shell">
                    <canvas id="expensePieChart"></canvas>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ asset('images/transaction.png') }}" alt="Transaction">
                        Giao dịch gần đây 
                    </h3>
                    <a href="{{ route('transactions.index') }}" class="card-menu">
                        <img src="{{ asset('images/plus.png') }}" alt="More">
                    </a>
                </div>
                <div style="padding:10px 0 6px">
                    <input
                        type="text"
                        id="search-transactions"
                        class="dashboard-search"
                        placeholder="Tìm giao dịch..."
                    >
                </div>
                <div id="recent-transactions"></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ asset('images/warning.png') }}" alt="Warning">
                        Cảnh báo ngán sách
                    </h3>
                    <a href="{{ route('budgets.index') }}" class="card-menu">
                        <img src="{{ asset('images/plus.png') }}" alt="More">
                    </a>
                </div>
                <div id="budget-warnings"></div>
            </div>
        </div>

        <div class="bottom-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ asset('images/category.png') }}" alt="Category">
                        Top danh mục chi tiêu
                    </h3>
                    <div class="card-menu">
                        <img src="{{ asset('images/chart.png') }}" alt="Chart">
                    </div>
                </div>
                <div id="top-categories"></div>
                <canvas id="categoryBarChart" style="margin-top:12px;max-height:200px;"></canvas>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ asset('images/asset-allocation.png') }}" alt="Wallet">
                        Tỏng quan ngân sách
                    </h3>
                    <a href="{{ route('budgets.index') }}" class="card-menu">
                        <img src="{{ asset('images/plus.png') }}" alt="More">
                    </a>
                </div>
                <div id="wallet-summary"></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ asset('images/warning.png') }}" alt="Spike">
                        Chi tiêu tăng đột biến
                    </h3>
                </div>
                <div id="spiking-categories"></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ asset('images/chart.png') }}" alt="Day">
                        Ngày chi nhiều nhất
                    </h3>
                </div>
                <div id="expense-by-day"></div>
            </div>
        </div>

        <div class="card" style="grid-column: 1 / -1">
            <div class="card-header">
                <h3 class="card-title">
                    <img src="{{ asset('images/chart.png') }}" alt="Heatmap">
                    Heatmap chi tiêu 30 ngày qua
                </h3>
            </div>
            <div id="expense-heatmap" style="padding:12px 0"></div>
        </div>
    </div>
</div>

@include('export-modal')

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    // TOAST GLOBAL
    window.showToast = function(opts) {
        let msg, type;
        if (typeof opts === 'object') {
            msg  = opts.message ?? opts.title ?? '';
            type = opts.type ?? 'success';
        } else {
            msg  = opts;
            type = arguments[1] ?? 'success';
        }

        const el = document.getElementById('toast');
        if (!el) return;
        el.className       = `alert alert-${type === 'success' ? 'success' : 'error'}`;
        el.textContent     = msg;
        el.style.display   = 'flex';
        el.style.opacity   = '1';
        el.style.transform = '';
        clearTimeout(el._t);
        el._t = setTimeout(() => {
            el.style.opacity   = '0';
            el.style.transform = 'translateY(-10px)';
            setTimeout(() => { el.style.display = 'none'; }, 300);
        }, 4000);
    };
    window.__dashboardCleanup?.();
    (() => {
        const page = document.getElementById('dashboardPage');
        const filter = document.getElementById('month-filter');
        const alertBox = document.getElementById('dashboard-alert');
        const loadingBox = document.getElementById('dashboard-loading');
        const content = document.getElementById('dashboard-content');
        const stats = document.getElementById('stats-row');
        const recent = document.getElementById('recent-transactions');
        const warnings = document.getElementById('budget-warnings');
        const categories = document.getElementById('top-categories');
        const wallets = document.getElementById('wallet-summary');
        const spiking = document.getElementById('spiking-categories');
        const expenseByDay = document.getElementById('expense-by-day');
        const heatmap = document.getElementById('expense-heatmap');
        const pieShell = document.getElementById('expense-pie-chart-shell');
        const lineCanvas = document.getElementById('incomeExpenseChart');
        const pieCanvas = document.getElementById('expensePieChart');
        const barCanvas = document.getElementById('categoryBarChart');
        const searchInput = document.getElementById('search-transactions');
        const chartColors = { success: '#10b981', danger: '#ef4444' };
        const state = {
            currentPeriod: 'this_month',
            lineChart: null,
            pieChart: null,
            barChart: null,
            recentTransactions: [],
            lastPayload: null,
            renderToken: 0,
            frameTask: null,
            idleTask: null,
            themeObserver: null,
        };

        if (!page || !filter || !content) {
            return;
        }

        function cancelScheduledWork() {
            if (state.frameTask !== null) {
                cancelAnimationFrame(state.frameTask);
                state.frameTask = null;
            }

            if (state.idleTask !== null) {
                if ('cancelIdleCallback' in window) {
                    window.cancelIdleCallback(state.idleTask);
                } else {
                    clearTimeout(state.idleTask);
                }
                state.idleTask = null;
            }
        }

        function scheduleNextFrame(callback) {
            cancelScheduledWork();
            state.frameTask = requestAnimationFrame(() => {
                state.frameTask = null;
                callback();
            });
        }

        function scheduleWhenIdle(callback, timeout = 220) {
            const runner = () => callback();
            if ('requestIdleCallback' in window) {
                state.idleTask = window.requestIdleCallback(() => {
                    state.idleTask = null;
                    runner();
                }, { timeout });
                return;
            }

            state.idleTask = window.setTimeout(() => {
                state.idleTask = null;
                runner();
            }, 48);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatMoney(value) {
            return `${new Intl.NumberFormat('vi-VN').format(Number(value || 0))} VND`;
        }

        function formatDate(value) {
            const parts = String(value || '').split('-');
            return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : '--/--/----';
        }

        function iconPath(name) {
            return `${page.dataset.categoryIconBase}/${name || 'money.png'}`;
        }

        function emptyState(image, text) {
            return `<div class="empty-state-mini"><img src="${image}" alt="Empty"><p>${escapeHtml(text)}</p></div>`;
        }

        function buildHeaders() {
            const token = localStorage.getItem('token');
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            };

            if (token) {
                headers.Authorization = `Bearer ${token}`;
            }

            return headers;
        }

        function isDarkMode() {
            return document.body.classList.contains('dark');
        }

        function getChartTheme() {
            const dark = isDarkMode();
            return {
                dark,
                text: dark ? '#e5e7eb' : '#1f2937',
                muted: dark ? '#94a3b8' : '#64748b',
                grid: dark ? 'rgba(148, 163, 184, 0.16)' : 'rgba(148, 163, 184, 0.2)',
                border: dark ? '#191d27' : '#ffffff',
                tooltipBg: dark ? 'rgba(15, 23, 42, 0.96)' : 'rgba(255, 255, 255, 0.96)',
                tooltipBorder: dark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(15, 23, 42, 0.08)',
                incomeFill: dark ? 'rgba(16, 185, 129, 0.14)' : 'rgba(16, 185, 129, 0.1)',
                expenseFill: dark ? 'rgba(239, 68, 68, 0.14)' : 'rgba(239, 68, 68, 0.1)',
            };
        }

        function getBaseChartOptions() {
            const theme = getChartTheme();

            return {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                normalized: true,
                devicePixelRatio: Math.min(window.devicePixelRatio || 1, 1.5),
                plugins: {
                    legend: {
                        labels: {
                            color: theme.text,
                            usePointStyle: true,
                            boxWidth: 8,
                            boxHeight: 8,
                            padding: 16,
                            font: {
                                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                                size: 12,
                            },
                        },
                    },
                    tooltip: {
                        backgroundColor: theme.tooltipBg,
                        titleColor: theme.text,
                        bodyColor: theme.text,
                        borderColor: theme.tooltipBorder,
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                    },
                },
            };
        }

        // Modal export
        const exportModal    = document.getElementById('export-modal');
        const emailInputWrap = document.getElementById('email-input-wrap');
        const emailInput     = document.getElementById('export-email-input');
        const xlsxLabel      = document.getElementById('format-xlsx-label');
        const pdfLabel       = document.getElementById('format-pdf-label');
        const exportIcon     = document.getElementById('export-modal-root')?.dataset.exportIcon || '';

        // Mở modal
        document.getElementById('export-report-btn').addEventListener('click', () => {
            exportModal.style.display = 'flex';
        });

        // Đóng modal
        document.getElementById('close-export-modal').addEventListener('click', () => exportModal.style.display = 'none');
        document.getElementById('cancel-export-btn').addEventListener('click',  () => exportModal.style.display = 'none');
        exportModal.addEventListener('click', e => { if (e.target === exportModal) exportModal.style.display = 'none'; });

        // Toggle email theo radio
        document.querySelectorAll('input[name="email-option"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const show = this.value === 'yes';
                emailInputWrap.style.display = show ? 'block' : 'none';
                if (show && !emailInput.value) {
                    const user = JSON.parse(localStorage.getItem('user') || '{}');
                    if (user.email) emailInput.value = user.email;
                }
            });
        });

        document.getElementById('confirm-export-btn').addEventListener('click', async () => {
            const period = document.getElementById('month-filter').value;
            const format = document.getElementById('export-format-select').value;
            const user   = JSON.parse(localStorage.getItem('user') || '{}');
            const email  = user.email || '';
            const btn    = document.getElementById('confirm-export-btn');

            btn.disabled    = true;
            btn.textContent = 'Đang xuất...';

            const url = format === 'pdf'
                ? `/api/v1/dashboard/export-pdf?period=${period}`
                : `/api/v1/dashboard/export?period=${period}`;

            try {
                const res = await fetch(url, {
                    headers: buildHeaders(),
                    credentials: 'same-origin',
                });

                if (!res.ok) throw new Error('Xuất thất bại');

                const blob = await res.blob();
                const link = document.createElement('a');
                link.href     = URL.createObjectURL(blob);
                link.download = `baocao_${period}_${new Date().toISOString().slice(0,10)}.${format}`;
                link.click();
                URL.revokeObjectURL(link.href);
                if (email) {
                    const mailRes = await fetch(`/api/v1/dashboard/send-report`, {
                        method: 'POST',
                        headers: {
                            ...buildHeaders(),
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ period, format, email }),
                    });
                    if (mailRes.ok) {
                        window.showToast({ type: 'success', message: `Đã gửi báo cáo đến ${email}` });
                    }
                }
                exportModal.style.display = 'none';
            } catch (err) {
                window.showToast({ type: 'error', message: 'Không thể xuất: ' + err.message });
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<img src="${exportIcon}" style="width:14px;height:14px;filter:brightness(10)"> Xuất khẩu`;
            }
        });
        function syncTokenFromUrl() {
            const url = new URL(window.location.href);
            const token = url.searchParams.get('token');

            if (!token) {
                return;
            }

            localStorage.setItem('token', token);
            url.searchParams.delete('token');
            window.history.replaceState({}, '', `${url.pathname}${url.search}${url.hash}`);
        }

        function ensureAuthenticated() {
            const serverAuthenticated = document.body.dataset.authenticated === '1';
            const token = localStorage.getItem('token');

            if (serverAuthenticated || token) {
                return true;
            }

            window.location.href = page.dataset.loginUrl;
            return false;
        }

        function setAlert(message) {
            alertBox.hidden = !message;
            alertBox.className = message ? 'dashboard-alert' : '';
            alertBox.textContent = message || '';
        }

        function setLoading(isLoading) {
            loadingBox.hidden = !isLoading;
            content.hidden = isLoading;
            content.classList.toggle('is-loading', isLoading);
        }

        function renderStats(data) {
            const totalTransactions = Number(data.totalTransactions || 0);
            const incomeCount  = Number(data.incomeCount  || 0);
            const expenseCount = Number(data.expenseCount || 0);
            const balance      = Number(data.balance      || 0);
            const savingRate   = Number(data.savingRate   || 0);

            function changeTag(val, inverse = false) {
                if (val === null || val === undefined) return '';
                const isGood = inverse ? val < 0 : val > 0;
                const color  = isGood ? 'up' : 'down';
                const arrow  = val > 0 ? '▲' : '▼';
                return `<div class="stat-change ${color}">${arrow} ${Math.abs(val)}% so kỳ trước</div>`;
            }

            const forecastHtml = data.forecast
                ? `<div class="stat-change down">Dự báo: ${formatMoney(data.forecast)}</div>`
                : '';

            const savingColor = savingRate >= 20 ? 'up' : savingRate >= 0 ? '' : 'down';

            stats.innerHTML = `
                <div class="stat-card">
                    <div class="stat-icon blue"><img src="${page.dataset.walletIcon}" alt="Balance"></div>
                    <div class="stat-info">
                        <div class="stat-label">Số dư</div>
                        <div class="stat-value ${balance < 0 ? 'negative' : ''}">${formatMoney(balance)}</div>
                        <div class="stat-change ${savingColor}">Tiết kiệm: ${savingRate}%</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><img src="${page.dataset.profitsIcon}" alt="Income"></div>
                    <div class="stat-info">
                        <div class="stat-label">Thu nhập</div>
                        <div class="stat-value positive">${formatMoney(data.totalIncome)}</div>
                        ${changeTag(data.incomeChange)}
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><img src="${page.dataset.budgetIcon}" alt="Expense"></div>
                    <div class="stat-info">
                        <div class="stat-label">Chi tiêu</div>
                        <div class="stat-value negative">${formatMoney(data.totalExpense)}</div>
                        ${changeTag(data.expenseChange, true)}
                        ${forecastHtml}
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><img src="${page.dataset.savingIcon}" alt="Transactions"></div>
                    <div class="stat-info">
                        <div class="stat-label">Giao dịch</div>
                        <div class="stat-value">${totalTransactions}</div>
                        <div class="stat-change up">${incomeCount} thu / ${expenseCount} chi</div>
                    </div>
                </div>
            `;
        }

        function renderRecentTransactions(list) {
            state.recentTransactions = list || [];  
            renderTransactionList(list);
        }

        function renderTransactionList(list) {
            if (!list?.length) {
                recent.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có giao dịch nào');
                return;
            }
            recent.innerHTML = `<div class="transaction-list">${list.map(item => {
                const isIncome = String(item.loai_giao_dich || '').toUpperCase() === 'THU';
                const name = item.category?.ten_danh_muc || 'Khong ro';
                return `<div class="transaction-item">
                    <div class="transaction-icon ${isIncome ? 'income' : 'expense'}">
                        <img src="${iconPath(item.category?.bieu_tuong)}" alt="${escapeHtml(name)}">
                    </div>
                    <div class="transaction-details">
                        <div class="transaction-name">${escapeHtml(name)}</div>
                        <div class="transaction-date">${formatDate(item.ngay_giao_dich)}</div>
                    </div>
                    <div class="transaction-amount ${isIncome ? 'income' : 'expense'}">
                        ${isIncome ? '+' : '-'}${formatMoney(item.so_tien)}
                    </div>
                </div>`;
            }).join('')}</div>`;
        }

        // Xử lý tìm kiếm
        searchInput?.addEventListener('input', function () {
            const keyword = this.value.trim().toLowerCase();
            if (!keyword) {
                renderTransactionList(state.recentTransactions);
                return;
            }
            const filtered = state.recentTransactions.filter(item => {
                const name = (item.category?.ten_danh_muc || '').toLowerCase();
                const date = (item.ngay_giao_dich || '').toLowerCase();
                return name.includes(keyword) || date.includes(keyword);
            });
            renderTransactionList(filtered);
        });


        function renderWarnings(list) {
            if (!list?.length) {
                warnings.innerHTML = emptyState(page.dataset.safeIcon, 'Tất cả ngân sách đều ổn định');
                return;
            }

            warnings.innerHTML = `<div class="budget-warnings">${list.map(item => {
                const percent = Number(item.spent_percentage || 0);
                const type = percent >= 90 ? 'danger' : 'warning';
                return `<div class="budget-warning-item ${type}"><div class="budget-warning-header"><span class="budget-warning-name">${escapeHtml(item.ten_ngan_sach)}</span><span class="budget-warning-percent">${Math.round(percent)}%</span></div><div class="progress-bar-mini"><div class="progress-fill-mini" style="width:${Math.min(percent, 100)}%"></div></div></div>`;
            }).join('')}</div>`;
        }

        function renderTopCategories(list, totalExpense) {
            if (!list?.length) {
                categories.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có dữ liệu chi tiêu');
                return;
            }

            const total = Number(totalExpense || 0);

            categories.innerHTML = `<div class="category-list">${list.map(item => {
                const value = Number(item.total_expense || 0);
                const percent = total > 0 ? Math.min((value / total) * 100, 100) : 0;
                return `<div class="category-item"><div class="category-icon"><img src="${iconPath(item.bieu_tuong)}" alt="${escapeHtml(item.ten_danh_muc)}"></div><div class="category-details"><div class="category-name">${escapeHtml(item.ten_danh_muc)}</div><div class="category-bar"><div class="category-bar-fill" style="width:${percent}%"></div></div></div><div style="text-align:right;"><div class="category-amount">${formatMoney(value)}</div><div class="category-percent">${Math.round(percent)}%</div></div></div>`;
            }).join('')}</div>`;
        }

        function renderWalletSummary(list) {
            if (!list?.length) {
                wallets.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có ngân sách nào');
                return;
            }

            wallets.innerHTML = `<div class="category-list">${list.slice(0, 5).map(item => {
                const percent = Number(item.spent_percentage || 0);
                const gradient = percent >= 80 ? 'linear-gradient(90deg,#ef4444,#dc2626)' : 'linear-gradient(90deg,#10b981,#059669)';
                return `<div class="category-item"><div class="category-icon"><img src="${iconPath(item.category?.bieu_tuong)}" alt="${escapeHtml(item.ten_ngan_sach)}"></div><div class="category-details"><div class="category-name">${escapeHtml(item.ten_ngan_sach)}</div><div class="category-bar"><div class="category-bar-fill" style="width:${Math.min(percent, 100)}%; background:${gradient};"></div></div></div><div style="text-align:right;"><div class="category-amount">${formatMoney(item.so_du)}</div><div class="category-percent">${Math.round(percent)}%</div></div></div>`;
            }).join('')}</div>`;
        }

        function renderLineChart(monthlyData) {
            if (!window.Chart || !lineCanvas) {
                return;
            }

            const theme = getChartTheme();
            const baseOptions = getBaseChartOptions();

            if (state.lineChart) {
                state.lineChart.destroy();
            }

            state.lineChart = new Chart(lineCanvas, {
                type: 'line',
                data: {
                    labels: (monthlyData || []).map(item => `Thang ${item.month}`),
                    datasets: [
                        {
                            label: 'Thu nhap',
                            data: (monthlyData || []).map(item => Number(item.income || 0)),
                            borderColor: chartColors.success,
                            backgroundColor: theme.incomeFill,
                            borderWidth: 3,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            pointHitRadius: 14,
                            tension: 0.32,
                            fill: true,
                        },
                        {
                            label: 'Chi tieu',
                            data: (monthlyData || []).map(item => Number(item.expense || 0)),
                            borderColor: chartColors.danger,
                            backgroundColor: theme.expenseFill,
                            borderWidth: 3,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            pointHitRadius: 14,
                            tension: 0.32,
                            fill: true,
                        }
                    ]
                },
                options: {
                    ...baseOptions,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        ...baseOptions.plugins,
                        tooltip: {
                            ...baseOptions.plugins.tooltip,
                            callbacks: {
                                label(context) {
                                    return `${context.dataset.label}: ${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: theme.muted,
                                maxRotation: 0,
                            },
                            grid: {
                                display: false,
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: theme.muted,
                                callback(value) {
                                    return `${new Intl.NumberFormat('vi-VN', { notation: 'compact', compactDisplay: 'short' }).format(value)} VND`;
                                }
                            },
                            grid: {
                                color: theme.grid,
                                drawBorder: false,
                            }
                        }
                    }
                }
            });
        }

        function renderPieChart(list) {
            if (!window.Chart || !pieShell || !pieCanvas) {
                return;
            }

            const theme = getChartTheme();
            const baseOptions = getBaseChartOptions();

            if (state.pieChart) {
                state.pieChart.destroy();
                state.pieChart = null;
            }

            pieCanvas.hidden = !list?.length;
            pieShell.querySelector('.empty-state-mini')?.remove();

            if (!list?.length) {
                pieShell.insertAdjacentHTML('beforeend', emptyState(page.dataset.emptyIcon, 'Chưa có dữ liệu chi tiêu'));
                return;
            }

            state.pieChart = new Chart(pieCanvas, {
                type: 'doughnut',
                data: {
                    labels: list.map(item => item.name),
                    datasets: [{
                        data: list.map(item => Number(item.total || 0)),
                        backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],
                        borderWidth: 2,
                        borderColor: theme.border,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    ...baseOptions,
                    cutout: '62%',
                    plugins: {
                        ...baseOptions.plugins,
                        legend: {
                            position: 'right',
                            labels: {
                                ...baseOptions.plugins.legend.labels,
                                generateLabels(chart) {
                                    const values = chart.data.datasets[0].data;
                                    const total = values.reduce((sum, item) => sum + item, 0);
                                    return chart.data.labels.map((label, index) => ({
                                        text: `${label}: ${total ? ((values[index] / total) * 100).toFixed(1) : '0.0'}%`,
                                        fillStyle: chart.data.datasets[0].backgroundColor[index],
                                        hidden: false,
                                        index,
                                    }));
                                }
                            }
                        }
                    }
                }
            });
        }

        function renderBarChart(list) {
            if (!window.Chart || !list?.length) return;

            if (!barCanvas) return;

            const theme = getChartTheme();
            const baseOptions = getBaseChartOptions();

            if (state.barChart) state.barChart.destroy();

            state.barChart = new Chart(barCanvas, {
                type: 'bar',
                data: {
                    labels: list.map(i => i.ten_danh_muc),
                    datasets: [{
                        label: 'Chi tiêu',
                        data: list.map(i => Number(i.total_expense || 0)),
                        backgroundColor: ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6'],
                        borderRadius: 6,
                    }]
                },
                options: {
                    indexAxis: 'y',   // bar nằm ngang
                    ...baseOptions,
                    plugins: {
                        ...baseOptions.plugins,
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: theme.muted,
                                callback: v => new Intl.NumberFormat('vi-VN', {
                                    notation: 'compact', compactDisplay: 'short'
                                }).format(v)
                            },
                            grid: {
                                color: theme.grid,
                                drawBorder: false,
                            }
                        },
                        y: {
                            ticks: {
                                color: theme.muted,
                            },
                            grid: {
                                display: false,
                                drawBorder: false,
                            }
                        }
                    }
                }
            });
        }

        function renderDashboard(data) {
            state.lastPayload = data;
            const renderToken = ++state.renderToken;
            cancelScheduledWork();

            renderStats(data);
            renderRecentTransactions(data.recentTransactions || []);
            renderWarnings(data.warningWallets || []);
            renderTopCategories(data.topCategories || [], data.totalExpense || 0);
            renderWalletSummary(data.activeWallets || []);

            scheduleNextFrame(() => {
                if (renderToken !== state.renderToken) return;

                renderLineChart(data.monthlyData || []);
                renderPieChart(data.categoryExpenses || []);

                scheduleWhenIdle(() => {
                    if (renderToken !== state.renderToken) return;

                    renderBarChart(data.topCategories || []);
                    renderSpikingCategories(data.spikingCategories || []);
                    renderExpenseByDay(data.expenseByDay || []);
                    renderHeatmap(data.heatmap || []);
                });
            });
        }

        // Cảnh báo tăng đột biến
        function renderSpikingCategories(list) {
            const el = spiking;
            if (!el) return;
            if (!list?.length) {
                el.innerHTML = emptyState(page.dataset.safeIcon, 'Không có danh mục tăng đột biến');
                return;
            }
            el.innerHTML = `<div class="budget-warnings">${list.map(item => {
                return `
                    <div class="budget-warning-item danger">
                        <div class="budget-warning-header">
                            <span class="budget-warning-name">${escapeHtml(item.ten_danh_muc)}</span>
                            <span class="budget-warning-percent" style="color:#ef4444">▲ ${item.change_percent}%</span>
                        </div>
                        <div style="font-size:11px;color:#6b7280;margin-top:2px">
                            Kỳ này: ${formatMoney(item.current_expense)}
                            &nbsp;|&nbsp; Kỳ trước: ${formatMoney(item.prev_expense)}
                        </div>
                    </div>`;
            }).join('')}</div>`;
        }

        // Ngày chi nhiều nhất
        function renderExpenseByDay(list) {
            const el = expenseByDay;
            if (!el) return;
            if (!list?.length) {
                el.innerHTML = emptyState(page.dataset.emptyIcon, 'Chua co du lieu theo ngay');
                return;
            }
            const max = Math.max(...list.map(d => d.total));
            el.innerHTML = `<div class="category-list">${list.map(item => {
                const pct = max > 0 ? Math.round((item.total / max) * 100) : 0;
                return `
                    <div class="category-item">
                        <div class="category-details" style="flex:1">
                            <div class="category-name">${escapeHtml(item.ten_ngay)}</div>
                            <div class="category-bar">
                                <div class="category-bar-fill" style="width:${pct}%;background:linear-gradient(90deg,#f59e0b,#ef4444)"></div>
                            </div>
                        </div>
                        <div class="category-amount" style="min-width:110px;text-align:right">
                            ${formatMoney(item.total)}
                        </div>
                    </div>`;
            }).join('')}</div>`;
        }

        function renderHeatmap(list) {
            const el = heatmap;
            if (!el) return;
            if (!list?.length) {
                el.innerHTML = emptyState(page.dataset.emptyIcon, 'Chua co heatmap chi tieu');
                return;
            }

            const max   = Math.max(...list.map(d => d.total), 1);
            const today = new Date().toISOString().slice(0, 10);

            function getColor(total) {
                if (total <= 0) return 'var(--dashboard-heatmap-empty)';
                const ratio = total / max;
                if (ratio >= 0.75) return '#ef4444';
                if (ratio >= 0.50) return '#f97316';
                if (ratio >= 0.25) return '#fbbf24';
                return '#fde68a';
            }

            const cells = list.map(item => {
                const d     = new Date(item.date);
                const isToday = item.date === today;
                return `
                    <div
                        class="heatmap-cell${isToday ? ' is-today' : ''}"
                        title="${item.date}: ${formatMoney(item.total)}"
                        style="background:${getColor(item.total)}"
                    ></div>`;
            }).join('');

            const labels = list.filter((_, i) => i % 5 === 0).map(item => {
                const d = new Date(item.date);
                return `<span>${d.getDate()}/${d.getMonth()+1}</span>`;
            }).join('');

            el.innerHTML = `
                <div class="heatmap-grid">
                    ${cells}
                </div>
                <div class="heatmap-labels">
                    ${labels}
                </div>
                <div class="heatmap-legend">
                    <span>Ít</span>
                    <div class="heatmap-legend-swatch" style="background:#fde68a"></div>
                    <div class="heatmap-legend-swatch" style="background:#fbbf24"></div>
                    <div class="heatmap-legend-swatch" style="background:#f97316"></div>
                    <div class="heatmap-legend-swatch" style="background:#ef4444"></div>
                    <span>Nhiều</span>
                </div>
            `;
        }

        function destroyCharts() {
            if (state.lineChart) {
                state.lineChart.destroy();
                state.lineChart = null;
            }

            if (state.pieChart) {
                state.pieChart.destroy();
                state.pieChart = null;
            }

            if (state.barChart) {
                state.barChart.destroy();
                state.barChart = null;
            }
        }

        function rerenderThemeSensitiveParts() {
            if (!state.lastPayload) return;

            const renderToken = ++state.renderToken;
            cancelScheduledWork();

            scheduleNextFrame(() => {
                if (renderToken !== state.renderToken) return;

                renderLineChart(state.lastPayload.monthlyData || []);
                renderPieChart(state.lastPayload.categoryExpenses || []);

                scheduleWhenIdle(() => {
                    if (renderToken !== state.renderToken) return;

                    renderBarChart(state.lastPayload.topCategories || []);
                    renderHeatmap(state.lastPayload.heatmap || []);
                });
            });
        }

        function observeThemeChanges() {
            let lastDarkMode = isDarkMode();

            state.themeObserver?.disconnect();
            state.themeObserver = new MutationObserver(() => {
                const nextDarkMode = isDarkMode();
                if (nextDarkMode === lastDarkMode) return;

                lastDarkMode = nextDarkMode;
                rerenderThemeSensitiveParts();
            });

            state.themeObserver.observe(document.body, {
                attributes: true,
                attributeFilter: ['class'],
            });
        }

        window.__dashboardCleanup = function () {
            cancelScheduledWork();
            state.themeObserver?.disconnect();
            destroyCharts();
        };

        async function loadDashboard(period = state.currentPeriod) {
            state.currentPeriod = period;
            filter.value = period;
            setAlert('');
            setLoading(true);

            try {
                const response = await fetch(`${page.dataset.apiUrl}?period=${encodeURIComponent(period)}`, {
                    headers: buildHeaders(),
                    credentials: 'same-origin',
                });

                if (response.status === 401) {
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    throw new Error('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
                }

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Không thể tải dữ liệu dashboard.');
                }

                state.currentPeriod = data.period || period;
                filter.value = state.currentPeriod;
                renderDashboard(data);
            } catch (error) {
                destroyCharts();
                state.lastPayload = null;
                setAlert(error.message || 'Không thể tải dữ liệu dashboard.');
                stats.innerHTML = '';
                recent.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có giao dịch nào trong thời gian này.');
                warnings.innerHTML = emptyState(page.dataset.safeIcon, 'Tất cả ngân sách đều ổn định.');
                categories.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có dữ liệu chi tiêu.');
                wallets.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có ngân sách nào.');
                spiking.innerHTML = emptyState(page.dataset.safeIcon, 'Khong co danh muc tang dot bien.');
                expenseByDay.innerHTML = emptyState(page.dataset.emptyIcon, 'Chua co du lieu theo ngay.');
                heatmap.innerHTML = emptyState(page.dataset.emptyIcon, 'Chua co heatmap chi tieu.');
            } finally {
                setLoading(false);
            }
        }

        syncTokenFromUrl();

        if (!ensureAuthenticated()) {
            return;
        }

        observeThemeChanges();
        filter.addEventListener('change', event => loadDashboard(event.target.value));
        loadDashboard(filter.value);
    })();
</script>
@endsection
