@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<style>
    :root {
        --primary: #4a90e2;
        --primary-dark: #2a5298;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #06b6d4;
        --radius: 12px;
        --shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding: 20px;
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }

    .page-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
    }

    .page-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .page-icon img {
        width: 100%;
    }

    .date-filter {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .date-filter select {
        padding: 10px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        background: #f9fafb;
        color: #1f2937;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 160px;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px;
        padding-right: 36px;
    }

    .date-filter select:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
    }

    /* Stats Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 32px rgba(0, 0, 0, 0.12);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
        transition: transform 0.3s ease;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.08);
    }

    .stat-icon img {
        width: 100%;
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, rgba(74, 144, 226, 0.15), rgba(74, 144, 226, 0.05));
    }

    .stat-icon.green {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
    }

    .stat-icon.red {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.05));
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, rgba(251, 146, 60, 0.15), rgba(251, 146, 60, 0.05));
    }

    .stat-info {
        flex: 1;
    }

    .stat-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 900;
        color: #1f2937;
        letter-spacing: -0.5px;
    }

    .stat-value.positive {
        color: var(--success);
    }

    .stat-value.negative {
        color: var(--danger);
    }

    .stat-change {
        font-size: 12px;
        font-weight: 600;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stat-change.up {
        color: var(--success);
    }

    .stat-change.down {
        color: var(--danger);
    }

    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .card {
        background: white;
        border-radius: var(--radius);
        padding: 24px;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 10px 32px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .card-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-title img {
        width: 20px;
    }

    .card-menu {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .card-menu:hover {
        background: #e5e7eb;
        transform: rotate(90deg);
    }

    .card-menu img {
        width: 16px;
        height: 16px;
        object-fit: contain;
        opacity: 0.6;
    }

    /* Recent Transactions */
    .transaction-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .transaction-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 10px;
        background: #f9fafb;
        transition: all 0.2s ease;
    }

    .transaction-item:hover {
        background: #f3f4f6;
        transform: translateX(4px);
    }

    .transaction-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
    }

    .transaction-icon.income {
        background: #d1fae5;
    }

    .transaction-icon.expense {
        background: #fee2e2;
    }

    .transaction-icon img {
        width: 100%;
    }

    .transaction-details {
        flex: 1;
    }

    .transaction-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .transaction-date {
        font-size: 12px;
        color: #9ca3af;
    }

    .transaction-amount {
        font-weight: 700;
        font-size: 15px;
    }

    .transaction-amount.income {
        color: var(--success);
    }

    .transaction-amount.expense {
        color: var(--danger);
    }

    /* Budget Warnings */
    .budget-warnings {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .budget-warning-item {
        padding: 12px;
        border-radius: 10px;
        border-left: 4px solid;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .budget-warning-item:hover {
        transform: translateX(4px);
    }

    .budget-warning-item.danger {
        background: #fee2e2;
        border-left-color: var(--danger);
    }

    .budget-warning-item.warning {
        background: #fef3c7;
        border-left-color: var(--warning);
    }

    .budget-warning-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .budget-warning-name {
        font-weight: 600;
        font-size: 14px;
        color: #1f2937;
    }

    .budget-warning-percent {
        font-weight: 700;
        font-size: 13px;
    }

    .budget-warning-item.danger .budget-warning-percent {
        color: var(--danger);
    }

    .budget-warning-item.warning .budget-warning-percent {
        color: var(--warning);
    }

    .progress-bar-mini {
        height: 6px;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-fill-mini {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    .budget-warning-item.danger .progress-fill-mini {
        background: var(--danger);
    }

    .budget-warning-item.warning .progress-fill-mini {
        background: var(--warning);
    }

    .empty-state-mini {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
    }

    .empty-state-mini img {
        width: 60px;
        opacity: 0.3;
        margin: 0 auto 12px;
    }

    .empty-state-mini p {
        font-size: 13px;
        font-weight: 500;
    }

    /* Bottom Section */
    .bottom-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    /* Category Stats */
    .category-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .category-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 10px;
        background: #f9fafb;
        transition: all 0.2s ease;
    }

    .category-item:hover {
        background: #f3f4f6;
        transform: translateX(4px);
    }

    .category-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
    }

    .category-icon img {
        width: 100%;
    }

    .category-details {
        flex: 1;
    }

    .category-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .category-bar {
        height: 4px;
        background: #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }

    .category-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        border-radius: 10px;
    }

    .category-amount {
        font-weight: 700;
        font-size: 15px;
        color: var(--danger);
    }

    .category-percent {
        font-size: 12px;
        color: #6b7280;
    }

    .stat-change {
        font-size: 12px;
        font-weight: 600;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stat-change img {
        width: 14px;
        height: 14px;
        object-fit: contain;
    }

    .stat-change.up {
        color: var(--success);
    }

    .stat-change.down {
        color: var(--danger);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .bottom-section {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: 1fr;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
    }

        body.dark .page-header {
        background: var(--dark-card);
    }

        body.dark .page-title,
        body.dark .card-title,
        body.dark .transaction-name,
        body.dark .category-name,
        body.dark .budget-warning-name,
        body.dark .stat-value {
            color: #e5e7eb;
        }

        body.dark .stat-label,
        body.dark .transaction-date,
        body.dark .category-percent {
            color: #9ca3af;
        }

        body.dark .transaction-item,
        body.dark .category-item {
            background: #212736;
        }

        body.dark .transaction-item:hover,
        body.dark .category-item:hover {
            background: #2a3147;
        }

        body.dark .date-filter select {
            background-color: #1c212a !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ca3af' d='M6 8L1 3h10z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 12px !important;
            border-color: rgba(255,255,255,0.08) !important;
            color: #e5e7eb !important;
        }

        body.dark .date-filter select:focus {
            background-color: #1c212a !important;
            border-color: var(--primary) !important;
        }

        body.dark .date-filter select option {
        background-color: #1c212a;
        color: #e5e7eb;

        body.dark .card-menu {
            background: #212736;
        }

        body.dark .card-menu:hover {
            background: #2a3147;
        }

        body.dark .category-bar {
            background: rgba(255,255,255,0.08);
        }

        body.dark .budget-warning-item.danger {
            background: rgba(239, 68, 68, 0.12);
        }

        body.dark .budget-warning-item.warning {
            background: rgba(245, 158, 11, 0.12);
        }

        body.dark .progress-bar-mini {
            background: rgba(255,255,255,0.08);
        }

        body.dark .empty-state-mini p {
            color: #6b7280;
        }

        /* Fix inline style color cứng trong Wallet Summary */
        body.dark .category-amount[style*="color: #1f2937"],
        body.dark .category-amount[style*="color:#1f2937"] {
            color: #e5e7eb !important;
    }

    
</style>

<div class="dashboard-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <div class="page-icon">
                <img src="{{ asset('images/home.png') }}" alt="Dashboard">
            </div>
            <span>Dashboard</span>
        </div>
        <div class="date-filter">
            <select id="month-filter" onchange="filterByMonth(this.value)">
            <option value="all"        {{ $period == 'all'        ? 'selected' : '' }}>Tất cả thời gian</option>
            <option value="this_month" {{ $period == 'this_month' ? 'selected' : '' }}>Tháng này</option>
            <option value="last_month" {{ $period == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
            <option value="this_year"  {{ $period == 'this_year'  ? 'selected' : '' }}>Năm nay</option>
</select>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue">
                <img src="{{ asset('images/wallet.png') }}" alt="Balance">
            </div>
            <div class="stat-info">
                <div class="stat-label">Số dư</div>
                <div class="stat-value">{{ number_format($balance, 0, ',', '.') }}đ</div>
                <div class="stat-change {{ $balance >= 0 ? 'up' : 'down' }}">
                    {{ $balance >= 0 ? '↑' : '↓' }} Thu - Chi
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <img src="{{ asset('images/profits.png') }}" alt="Income">
            </div>
            <div class="stat-info">
                <div class="stat-label">Thu nhập</div>
                <div class="stat-value positive">{{ number_format($totalIncome, 0, ',', '.') }}đ</div>
                <div class="stat-change up">
                    <img src="{{ asset('images/arrow-up.png') }}" alt="Up" style="width: 14px; height: 14px;">
                    {{ $totalTransactions > 0 ? number_format(($incomeCount / $totalTransactions) * 100, 0) : 0 }}% giao dịch
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                <img src="{{ asset('images/budget.png') }}" alt="Expense">
            </div>
            <div class="stat-info">
                <div class="stat-label">Chi tiêu</div>
                <div class="stat-value negative">{{ number_format($totalExpense, 0, ',', '.') }}đ</div>
                <div class="stat-change down">
                    <img src="{{ asset('images/arrow-down.png') }}" alt="Down" style="width: 14px; height: 14px;">
                    {{ $totalTransactions > 0 ? number_format(($expenseCount / $totalTransactions) * 100, 0) : 0 }}% giao dịch
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <img src="{{ asset('images/saving.png') }}" alt="Transactions">
            </div>
            <div class="stat-info">
                <div class="stat-label">Giao dịch</div>
                <div class="stat-value">{{ $totalTransactions }}</div>
                <div class="stat-change up">
                    {{ $incomeCount }} thu / {{ $expenseCount }} chi
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="dashboard-grid">
        <!-- Line Chart -->
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
            <canvas id="incomeExpenseChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Pie Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <img src="{{ asset('images/transaction.png') }}" alt="Category">
                    Phân bổ chi tiêu
                </h3>
                <div class="card-menu">
                    <img src="{{ asset('images/plus.png') }}" alt="More">
                </div>
            </div>
            <canvas id="expensePieChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Recent Transactions -->
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

            @if($recentTransactions->count() > 0)
                <div class="transaction-list">
                    @foreach($recentTransactions as $transaction)
                        <div class="transaction-item">
                            <div class="transaction-icon {{ strtolower($transaction->loai_giao_dich) == 'thu' ? 'income' : 'expense' }}">
                                <img src="{{ asset('images/category-icons/' . ($transaction->category->bieu_tuong ?? 'money.png')) }}" alt="Icon">
                            </div>
                            <div class="transaction-details">
                                <div class="transaction-name">{{ $transaction->category->ten_danh_muc ?? 'Không rõ' }}</div>
                                <div class="transaction-date">{{ $transaction->ngay_giao_dich->format('d/m/Y') }}</div>
                            </div>
                            <div class="transaction-amount {{ strtolower($transaction->loai_giao_dich) == 'thu' ? 'income' : 'expense' }}">
                                {{ strtolower($transaction->loai_giao_dich) == 'thu' ? '+' : '-' }}{{ number_format($transaction->so_tien, 0, ',', '.') }}đ
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state-mini">
                    <img src="{{ asset('images/empty-folder.png') }}" alt="Empty">
                    <p>Chưa có giao dịch nào</p>
                </div>
            @endif
        </div>

        <!-- Budget Warnings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <img src="{{ asset('images/warning.png') }}" alt="Warning">
                    Cảnh báo ngân sách
                </h3>
                <a href="{{ route('wallets.index') }}" class="card-menu">
                    <img src="{{ asset('images/plus.png') }}" alt="More">
                </a>
            </div>

            @if($warningWallets->count() > 0)
                <div class="budget-warnings">
                    @foreach($warningWallets as $wallet)
                        <div class="budget-warning-item {{ $wallet->spent_percentage >= 90 ? 'danger' : 'warning' }}">
                            <div class="budget-warning-header">
                                <span class="budget-warning-name">{{ $wallet->ten_ngan_sach }}</span>
                                <span class="budget-warning-percent">{{ number_format($wallet->spent_percentage, 0) }}%</span>
                            </div>
                            <div class="progress-bar-mini">
                                <div class="progress-fill-mini" style="width: {{ min($wallet->spent_percentage, 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state-mini">
                    <img src="{{ asset('images/check.png') }}" alt="Safe">
                    <p>Tất cả ngân sách đều ổn định</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="bottom-section">
        <!-- Top Categories -->
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

            @if($topCategories->count() > 0)
                <div class="category-list">
                    @foreach($topCategories as $category)
                        <div class="category-item">
                            <div class="category-icon">
                                <img src="{{ asset('images/category-icons/' . ($category->bieu_tuong ?? 'money.png')) }}" alt="Icon">
                            </div>
                            <div class="category-details">
                                <div class="category-name">{{ $category->ten_danh_muc }}</div>
                                <div class="category-bar">
                                    <div class="category-bar-fill" style="width: {{ ($category->total_expense / $totalExpense) * 100 }}%"></div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="category-amount">{{ number_format($category->total_expense, 0, ',', '.') }}đ</div>
                                <div class="category-percent">{{ number_format(($category->total_expense / $totalExpense) * 100, 0) }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state-mini">
                    <img src="{{ asset('images/empty-folder.png') }}" alt="Empty">
                    <p>Chưa có dữ liệu chi tiêu</p>
                </div>
            @endif
        </div>

        <!-- Wallet Summary -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <img src="{{ asset('images/asset-allocation.png') }}" alt="Wallet">
                    Tổng quan ngân sách
                </h3>
                <a href="{{ route('wallets.index') }}" class="card-menu">
                    <img src="{{ asset('images/plus.png') }}" alt="More">
                </a>
            </div>

            @if($activeWallets->count() > 0)
                <div class="category-list">
                    @foreach($activeWallets->take(5) as $wallet)
                        <div class="category-item">
                            <div class="category-icon">
                                <img src="{{ asset('images/category-icons/' . ($wallet->category->bieu_tuong ?? 'money.png')) }}" alt="Icon">
                            </div>
                            <div class="category-details">
                                <div class="category-name">{{ $wallet->ten_ngan_sach }}</div>
                                <div class="category-bar">
                                    <div class="category-bar-fill" style="width: {{ min($wallet->spent_percentage, 100) }}%; background: {{ $wallet->spent_percentage >= 80 ? 'linear-gradient(90deg, #ef4444, #dc2626)' : 'linear-gradient(90deg, #10b981, #059669)' }}"></div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="category-amount" style="color: #1f2937;">{{ number_format($wallet->so_du, 0, ',', '.') }}đ</div>
                                <div class="category-percent">{{ number_format($wallet->spent_percentage, 0) }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state-mini">
                    <img src="{{ asset('images/empty-folder.png') }}" alt="Empty">
                    <p>Chưa có ngân sách nào</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    const params = new URLSearchParams(window.location.search);
    const token  = params.get('token');
    if (token) {
        localStorage.setItem('token', token);
        window.history.replaceState({}, '', '/dashboard');
    }
</script>
<script>
function filterByMonth(period) {
    loadDashboard(period); 
}

// Chart.js Configuration
const chartColors = {
    primary: '#4a90e2',
    primaryDark: '#2a5298',
    success: '#10b981',
    danger: '#ef4444',
    warning: '#f59e0b',
    info: '#06b6d4'
};

// 1. LINE CHART - Thu Chi theo tháng
const lineChartData = @json($monthlyData);
const lineCtx = document.getElementById('incomeExpenseChart');

if (lineCtx) {
    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: lineChartData.map(item => `Tháng ${item.month}`),
            datasets: [
                {
                    label: 'Thu nhập',
                    data: lineChartData.map(item => parseFloat(item.income) || 0), 
                    borderColor: chartColors.success,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBorderWidth: 3
                },
                {
                    label: 'Chi tiêu',
                    data: lineChartData.map(item => parseFloat(item.expense) || 0),
                    borderColor: chartColors.danger,
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBorderWidth: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 13,
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += new Intl.NumberFormat('vi-VN', { 
                                style: 'currency', 
                                currency: 'VND' 
                            }).format(context.parsed.y);
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {
                                notation: 'compact',
                                compactDisplay: 'short'
                            }).format(value) + 'đ';
                        },
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                }
            }
        }
    });
}

// 2. PIE CHART - Phân bổ chi tiêu theo danh mục
const pieChartData = @json($categoryExpenses);
const pieCtx = document.getElementById('expensePieChart');

if (pieCtx && pieChartData.length > 0) {
    const pieColors = [
        '#ef4444', '#f59e0b', '#10b981', '#3b82f6', 
        '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'
    ];

    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: pieChartData.map(item => item.name),
            datasets: [{
                data: pieChartData.map(item => parseFloat(item.total) || 0), 
                backgroundColor: pieColors.slice(0, pieChartData.length),
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 13,
                            weight: '600'
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return {
                                        text: `${label}: ${percentage}%`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            const formatted = new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND'
                            }).format(value);
                            return `${label}: ${formatted} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
} else if (pieCtx) {
    pieCtx.parentElement.innerHTML = `
        <div class="empty-state-mini">
            <img src="{{ asset('images/empty-folder.png') }}" alt="Empty">
            <p>Chưa có dữ liệu chi tiêu</p>
        </div>
    `;
}
</script>
@endsection