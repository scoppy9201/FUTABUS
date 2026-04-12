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
                <div id="recent-transactions"></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ asset('images/warning.png') }}" alt="Warning">
                        Cảnh báo ngán sách
                    </h3>
                    <a href="{{ route('wallets.index') }}" class="card-menu">
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
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ asset('images/asset-allocation.png') }}" alt="Wallet">
                        Tỏng quan ngân sách
                    </h3>
                    <a href="{{ route('wallets.index') }}" class="card-menu">
                        <img src="{{ asset('images/plus.png') }}" alt="More">
                    </a>
                </div>
                <div id="wallet-summary"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
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
        const pieShell = document.getElementById('expense-pie-chart-shell');
        const lineCanvas = document.getElementById('incomeExpenseChart');
        const chartColors = { success: '#10b981', danger: '#ef4444' };
        const state = { currentPeriod: 'this_month', lineChart: null, pieChart: null };

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
            const incomeCount = Number(data.incomeCount || 0);
            const expenseCount = Number(data.expenseCount || 0);
            const balance = Number(data.balance || 0);
            const incomeRate = totalTransactions ? Math.round((incomeCount / totalTransactions) * 100) : 0;
            const expenseRate = totalTransactions ? Math.round((expenseCount / totalTransactions) * 100) : 0;

            stats.innerHTML = `
                <div class="stat-card"><div class="stat-icon blue"><img src="${page.dataset.walletIcon}" alt="Balance"></div><div class="stat-info"><div class="stat-label">Số dư</div><div class="stat-value ${balance < 0 ? 'negative' : ''}">${formatMoney(balance)}</div><div class="stat-change ${balance >= 0 ? 'up' : 'down'}">${balance >= 0 ? '+' : '-'} Thu - Chi</div></div></div>
                <div class="stat-card"><div class="stat-icon green"><img src="${page.dataset.profitsIcon}" alt="Income"></div><div class="stat-info"><div class="stat-label">Thu nhập</div><div class="stat-value positive">${formatMoney(data.totalIncome)}</div><div class="stat-change up"><img src="${page.dataset.arrowUpIcon}" alt="Up" style="width:14px;height:14px;">${incomeRate}% giao dich</div></div></div>
                <div class="stat-card"><div class="stat-icon red"><img src="${page.dataset.budgetIcon}" alt="Expense"></div><div class="stat-info"><div class="stat-label">Chi tiêu</div><div class="stat-value negative">${formatMoney(data.totalExpense)}</div><div class="stat-change down"><img src="${page.dataset.arrowDownIcon}" alt="Down" style="width:14px;height:14px;">${expenseRate}% giao dich</div></div></div>
                <div class="stat-card"><div class="stat-icon orange"><img src="${page.dataset.savingIcon}" alt="Transactions"></div><div class="stat-info"><div class="stat-label">Giao dịch</div><div class="stat-value">${totalTransactions}</div><div class="stat-change up">${incomeCount} thu / ${expenseCount} chi</div></div></div>
            `;
        }

        function renderRecentTransactions(list) {
            if (!list?.length) {
                recent.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có giao dịch nào trong thời gian này');
                return;
            }

            recent.innerHTML = `<div class="transaction-list">${list.map(item => {
                const isIncome = String(item.loai_giao_dich || '').toUpperCase() === 'THU';
                const name = item.category?.ten_danh_muc || 'Khong ro';
                return `<div class="transaction-item"><div class="transaction-icon ${isIncome ? 'income' : 'expense'}"><img src="${iconPath(item.category?.bieu_tuong)}" alt="${escapeHtml(name)}"></div><div class="transaction-details"><div class="transaction-name">${escapeHtml(name)}</div><div class="transaction-date">${formatDate(item.ngay_giao_dich)}</div></div><div class="transaction-amount ${isIncome ? 'income' : 'expense'}">${isIncome ? '+' : '-'}${formatMoney(item.so_tien)}</div></div>`;
            }).join('')}</div>`;
        }

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
                            backgroundColor: 'rgba(16,185,129,.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                        },
                        {
                            label: 'Chi tieu',
                            data: (monthlyData || []).map(item => Number(item.expense || 0)),
                            borderColor: chartColors.danger,
                            backgroundColor: 'rgba(239,68,68,.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label(context) {
                                    return `${context.dataset.label}: ${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback(value) {
                                    return `${new Intl.NumberFormat('vi-VN', { notation: 'compact', compactDisplay: 'short' }).format(value)} VND`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function renderPieChart(list) {
            if (!window.Chart || !pieShell) {
                return;
            }

            if (state.pieChart) {
                state.pieChart.destroy();
                state.pieChart = null;
            }

            if (!list?.length) {
                pieShell.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có dữ liệu chi tiêu');
                return;
            }

            pieShell.innerHTML = '<canvas id="expensePieChart"></canvas>';

            state.pieChart = new Chart(document.getElementById('expensePieChart'), {
                type: 'doughnut',
                data: {
                    labels: list.map(item => item.name),
                    datasets: [{
                        data: list.map(item => Number(item.total || 0)),
                        backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],
                        borderWidth: 3,
                        borderColor: '#fff',
                        hoverOffset: 10,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
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

        function renderDashboard(data) {
            renderStats(data);
            renderRecentTransactions(data.recentTransactions || []);
            renderWarnings(data.warningWallets || []);
            renderTopCategories(data.topCategories || [], data.totalExpense || 0);
            renderWalletSummary(data.activeWallets || []);
            renderLineChart(data.monthlyData || []);
            renderPieChart(data.categoryExpenses || []);
        }

        function exportReport() {
            const period = document.getElementById('month-filter').value;
            const btn = document.getElementById('export-report-btn');
            
            btn.disabled = true;
            btn.innerHTML = `
                <span style="width:14px;height:14px;border:2px solid rgba(255,255,255,0.4);border-top-color:white;border-radius:50%;animation:spin .6s linear infinite;display:inline-block;"></span>
                Đang xuất...
            `;

            // Gọi API xuất báo cáo
            fetch(`/api/v1/dashboard/export?period=${period}`, {
                headers: buildHeaders(),
                credentials: 'same-origin',
            })
            .then(res => {
                if (!res.ok) throw new Error('Xuất thất bại');
                return res.blob();
            })
            .then(blob => {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `baocao_${period}_${new Date().toISOString().slice(0, 10)}.xlsx`;
                a.click();
                URL.revokeObjectURL(url);
            })
            .catch(err => {
                alert('Không thể xuất báo cáo: ' + err.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = `
                    <img src="{{ asset('images/export.png') }}" alt="Export" style="width:16px;height:16px;filter:brightness(10);">
                    Xuất báo cáo
                `;
            });
        }

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
                setAlert(error.message || 'Không thể tải dữ liệu dashboard.');
                stats.innerHTML = '';
                recent.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có giao dịch nào trong thời gian này.');
                warnings.innerHTML = emptyState(page.dataset.safeIcon, 'Tất cả ngân sách đều ổn định.');
                categories.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có dữ liệu chi tiêu.');
                wallets.innerHTML = emptyState(page.dataset.emptyIcon, 'Chưa có ngân sách nào.');
                renderPieChart([]);
            } finally {
                setLoading(false);
            }
        }

        syncTokenFromUrl();

        if (!ensureAuthenticated()) {
            return;
        }

        filter.addEventListener('change', event => loadDashboard(event.target.value));
        loadDashboard(filter.value);

        document.getElementById('export-report-btn').addEventListener('click', exportReport);
    })();
</script>
@endsection
