@extends('layouts.app')
@section('title', 'Quản lý giao dịch')
@section('content')
<style>
    :root {
        --primary: #4a90e2;
        --primary-dark: #2a5298;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #06b6d4;
        --dark-bg: #1a1f29;
        --dark-card: #242936;
        --dark-border: rgba(255, 255, 255, 0.08);
        --gray-100: #f8fafc;
        --gray-200: #e2e8f0;
        --gray-300: #cbd5e0;
        --gray-600: #4a5568;
        --gray-800: #2d3748;
        --gray-900: #1a202c;
        --radius: 12px;
        --radius-sm: 10px;
        --shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        --shadow-lg: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    /* Dark mode */
    body.dark .page-header,
    body.dark .filter-card,
    body.dark .table-card,
    body.dark .stats-grid {
        background: var(--dark-card);
        border-color: var(--dark-border);
    }

    body.dark .page-title,
    body.dark .filter-title,
    body.dark .table-title,
    body.dark .stat-card h3,
    body.dark th,
    body.dark td {
        color: #e5e7eb;
    }

    body.dark .form-control,
    body.dark .form-select {
        background: var(--dark-bg);
        border-color: var(--dark-border);
        color: #e5e7eb;
    }

    body.dark .form-control:focus,
    body.dark .form-select:focus {
        background: var(--dark-card);
        border-color: var(--primary);
    }

    body.dark tbody tr:hover {
        background: rgba(255, 255, 255, 0.03);
    }

    body.dark .stat-card {
        background: var(--dark-bg);
    }

    body.dark .stat-value {
        color: #e5e7eb;
    }

    /* Layout */
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
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .page-icon img {
        width: 100%;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
    }

    .stat-icon.income {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .stat-icon.expense {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .stat-icon.balance {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .stat-icon.total {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    .stat-icon img {
        width: 100%;
    }

    .stat-info {
        flex: 1;
    }

    .stat-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
    }

    .stat-value.positive {
        color: #10b981;
    }

    .stat-value.negative {
        color: #ef4444;
    }

    /* Buttons */
    .btn-primary {
        padding: 10px 20px;
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: opacity 0.2s ease;
        text-decoration: none;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    .btn-primary img {
        width: 16px;
    }

    .btn-filter {
        padding: 10px 20px;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-filter img {
        width: 16px;
    }

    .btn-search {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
    }

    .btn-search:hover {
        opacity: 0.9;
    }

    .btn-reset {
        background: #f3f4f6;
        color: #6b7280;
    }

    .btn-reset:hover {
        background: #e5e7eb;
    }

    /* Filter Card */
    .filter-card {
        background: white;
        border-radius: var(--radius);
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow);
    }

    .filter-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        font-size: 16px;
        font-weight: 600;
        color: #374151;
    }

    .filter-title img {
        width: 20px;
    }

    /* Filter Form - Compact */
    .filter-form {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr 0.8fr 0.8fr 0.8fr auto;
        gap: 12px;
        align-items: flex-end;
    }

    .filter-form .form-group {
        margin-bottom: 0;
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        min-width: 180px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 16px;
    }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
    }

    .form-control,
    .form-select {
        padding: 10px 12px;
        border: 2px solid #e5e7eb;
        border-radius: var(--radius-sm);
        font-size: 14px;
        transition: border-color 0.2s ease;
        background: #f9fafb;
        color: #1f2937;
        width: 100%;
    }

    .form-control:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
    }

    .form-textarea {
        min-height: 80px;
        resize: vertical;
    }

    .required {
        color: var(--danger);
        font-weight: 700;
        margin-left: 2px;
    }

    /* Table */
    .table-card {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .table-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-title img {
        width: 22px;
    }

    .table-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .stat-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .stat-badge img {
        width: 14px;
    }

    .stat-badge.income {
        background: #d1fae5;
        color: #065f46;
    }

    .stat-badge.expense {
        background: #fee2e2;
        color: #991b1b;
    }

    .stat-badge.total {
        background: #dbeafe;
        color: #1e40af;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: #f9fafb;
    }

    body.dark thead {
        background: rgba(255, 255, 255, 0.03);
    }

    th {
        padding: 12px 20px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.2s ease;
    }

    tbody tr:hover {
        background: #f9fafb;
    }

    td {
        padding: 16px 20px;
        font-size: 14px;
        color: #374151;
    }

    .transaction-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .transaction-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
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

    .transaction-category {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .transaction-desc {
        font-size: 12px;
        color: #6b7280;
    }

    .amount {
        font-weight: 700;
        font-size: 15px;
    }

    .amount.income {
        color: #10b981;
    }

    .amount.expense {
        color: #ef4444;
    }

    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        letter-spacing: 0.3px;
    }

    .badge-income {
        background: var(--success);
        color: white;
    }

    .badge-expense {
        background: var(--danger);
        color: white;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 6px;
    }

    .btn-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
        padding: 8px;
    }

    .btn-action img {
        width: 100%;
    }

    .btn-edit {
        background: #dbeafe;
        border: 2px solid var(--info);
    }

    .btn-edit:hover {
        background: var(--info);
    }

    .btn-delete {
        background: #fee2e2;
        border: 2px solid var(--danger);
    }

    .btn-delete:hover {
        background: var(--danger);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: #f3f4f6;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        padding: 20px;
    }

    .empty-icon img {
        width: 100%;
        opacity: 0.5;
    }

    .empty-state h3 {
        color: #6b7280;
        font-size: 20px;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #9ca3af;
        font-size: 14px;
        margin-bottom: 24px;
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 20px 24px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info {
        color: #6b7280;
        font-size: 14px;
    }

    .pagination {
        display: flex;
        gap: 6px;
        list-style: none;
    }

    .pagination a,
    .pagination span {
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }

    .pagination a {
        background: #f9fafb;
        color: #6b7280;
    }

    .pagination a:hover {
        background: #f3f4f6;
        color: #374151;
    }

    .pagination .active span {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
    }

    /* Modal */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        padding: 20px;
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background: white;
        border-radius: var(--radius);
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        box-shadow: var(--shadow-lg);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    body.dark .modal-content {
        background: var(--dark-card);
    }

    .modal-header {
        padding: 24px 32px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
        color: white;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .modal-close img {
        width: 16px;
    }

    .modal-body {
        padding: 28px 32px;
        overflow-y: auto;
        flex: 1;
    }

    .modal-actions {
        padding: 20px 32px;
        background: white;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
    }

    body.dark .modal-actions {
        background: var(--dark-card);
        border-top-color: var(--dark-border);
    }

    .btn-secondary {
        background: #f3f4f6;
        border: 2px solid #e5e7eb;
        color: #4b5563;
        padding: 12px 24px;
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        flex: 1;
        transition: background 0.2s ease;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .modal-actions .btn-primary {
        flex: 1;
        justify-content: center;
        padding: 12px 24px;
    }

    /* Alert */
    .alert {
        padding: 14px 18px;
        border-radius: var(--radius);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 500;
    }

    .alert img {
        width: 20px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid var(--success);
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid var(--danger);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .filter-actions {
            grid-column: 1 / -1;
            width: 100%;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .table-wrapper {
            overflow-x: scroll;
        }

        table {
            min-width: 900px;
        }
    }

    @media (max-width: 640px) {
        .filter-form {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            flex-direction: column;
        }

        .btn-filter {
            width: 100%;
        }
    }
    body.dark .transaction-category {
    color: #e5e7eb; 
    }

    body.dark .transaction-desc {
        color: #9ca3af; 
    }

    body.dark td {
        color: #e5e7eb;
    }
</style>

<div class="transaction-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <div class="page-icon">
                <img src="{{ asset('images/transaction.png') }}" alt="Transaction">
            </div>
            <span>Quản lý giao dịch</span>
        </div>
        <button type="button" class="btn-primary" id="open-create-modal">
            <img src="{{ asset('images/plus.png') }}" alt="Add">
            Thêm giao dịch
        </button>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success">
            <img src="{{ asset('images/check.png') }}" alt="Success">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <img src="{{ asset('images/warning.png') }}" alt="Error">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="stats-grid" style="display: none;">
    </div>

    <!-- Filter Section -->
    <div class="filter-card">
        <div class="filter-title">
            <img src="{{ asset('images/filter.png') }}" alt="Filter">
            <span>Bộ lọc & Tìm kiếm</span>
        </div>
        <form action="{{ route('transactions.index') }}" method="GET" class="filter-form">
            <div class="form-group">
                <label class="form-label">Tìm kiếm</label>
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Nhập tên hoặc loại giao dịch..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="form-group">
                <label class="form-label">Danh mục</label>
                <select name="danh_muc_id" class="form-select">
                    <option value="">Tất cả danh mục</option>
                    
                    @php
                        $thuCategories = $categories->where('loai_danh_muc', 'THU');
                        $chiCategories = $categories->where('loai_danh_muc', 'CHI');
                    @endphp
                    
                    @if($thuCategories->count() > 0)
                        <optgroup label="Thu nhập">
                            @foreach($thuCategories as $category)
                                <option value="{{ $category->id }}" {{ request('danh_muc_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->ten_danh_muc }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    
                    @if($chiCategories->count() > 0)
                        <optgroup label="Chi tiếu">
                            @foreach($chiCategories as $category)
                                <option value="{{ $category->id }}" {{ request('danh_muc_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->ten_danh_muc }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Loại</label>
                <select name="loai" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="THU" {{ request('loai') == 'THU' ? 'selected' : '' }}>Thu</option>
                    <option value="CHI" {{ request('loai') == 'CHI' ? 'selected' : '' }}>Chi</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Từ ngày</label>
                <input 
                    type="date" 
                    name="tu_ngay" 
                    class="form-control"
                    value="{{ request('tu_ngay') }}"
                >
            </div>

            <div class="form-group">
                <label class="form-label">Đến ngày</label>
                <input 
                    type="date" 
                    name="den_ngay" 
                    class="form-control"
                    value="{{ request('den_ngay') }}"
                >
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter btn-search">
                    <img src="{{ asset('images/search.png') }}" alt="Search">
                    Tìm kiếm
                </button>
                <a href="{{ route('transactions.index') }}" class="btn-filter btn-reset">
                    <img src="{{ asset('images/refresh.png') }}" alt="Reset">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">
                <img src="{{ asset('images/list.png') }}" alt="List">
                Danh sách giao dịch
            </h3>
            
            <div class="table-stats">
                <span class="stat-badge income">
                    <img src="{{ asset('images/arrows.png') }}" alt="Income">
                    Thu nhập: {{ number_format($totalIncome ?? 0) }}đ
                </span>
                <span class="stat-badge expense">
                    <img src="{{ asset('images/down.png') }}" alt="Expense">
                    Chi tiêu : {{ number_format($totalExpense ?? 0) }}đ
                </span>
                <span class="stat-badge total">
                    <img src="{{ asset('images/chart.png') }}" alt="Total">
                    Tổng giao dịch: {{ $transactions->total() ?? 0 }}
                </span>
            </div>
        </div>

        @if($transactions->count() > 0)
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Danh mục & Mô tả</th>
                        <th style="width: 100px;">Loại</th>
                        <th style="width: 150px;">Số tiền</th>
                        <th style="width: 120px;">Ngày giao dịch</th>
                        <th style="width: 130px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $index => $transaction)
                    <tr>
                        <td>{{ $transactions->firstItem() + $index }}</td>
                        <td>
                            <div class="transaction-info">
                                <div class="transaction-icon {{ $transaction->loai_giao_dich == 'THU' ? 'income' : 'expense' }}">
                                    <img src="{{ asset('images/category-icons/' . ($transaction->category->bieu_tuong ?? 'money.png')) }}" alt="Category">
                                </div>
                                <div class="transaction-details">
                                    <div class="transaction-category">{{ $transaction->category->ten_danh_muc }}</div>
                                    <div class="transaction-desc">{{ $transaction->ghi_chu ?? 'Không có ghi chú' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $transaction->loai_giao_dich == 'THU' ? 'income' : 'expense' }}">
                                {{ $transaction->loai_giao_dich }}
                            </span>
                        </td>
                        <td>
                            <span class="amount {{ $transaction->loai_giao_dich == 'THU' ? 'income' : 'expense' }}">
                                {{ $transaction->loai_giao_dich == 'THU' ? '+' : '-' }}{{ number_format($transaction->so_tien) }}đ
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($transaction->ngay_giao_dich)->format('d/m/Y') }}</td>
                        <td>
                            <div class="action-buttons">
                                <button type="button" class="btn-action btn-edit" onclick="openEditModal({{ $transaction }})" title="Chỉnh sửa">
                                    <img src="{{ asset('images/edit.png') }}" alt="Edit">
                                </button>

                                <form action="{{ route('transactions.destroy', $transaction) }}" 
                                method="POST" 
                                style="display: inline;"
                                class="form-delete"
                                data-id="{{ $category->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Xóa">
                                        <img src="{{ asset('images/delete.png') }}" alt="Delete">
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            <div class="pagination-info">
                Hiển thị {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} / {{ $transactions->total() }} kết quả
            </div>
            <div>
                {{ $transactions->links() }}
            </div>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-icon">
                <img src="{{ asset('images/empty-folder.png') }}" alt="Empty">
            </div>
            <h3>Chưa có giao dịch nào</h3>
            <p>Hãy thêm giao dịch đầu tiên để bắt đầu theo dõi thu chi</p>
            <button type="button" class="btn-primary" onclick="document.getElementById('create-modal').classList.add('active')">
                <img src="{{ asset('images/plus.png') }}" alt="Add">
                Thêm giao dịch đầu tiên
            </button>
        </div>
        @endif
    </div>

    <!-- Modal Thêm Mới -->
    <div class="modal-overlay" id="create-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <div class="page-icon">
                        <img src="{{ asset('images/plus.png') }}" style="width:24px; filter: brightness(0) invert(1);">
                    </div>
                    Thêm giao dịch mới
                </div>
                <div class="modal-close" onclick="closeModal('create-modal')">
                    <img src="{{ asset('images/close.png') }}" style="width:16px">
                </div>
            </div>

            <form action="{{ route('transactions.store') }}" method="POST" id="create-form">
                @csrf
                
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">
                            <strong>Loại giao dịch</strong> <span class="required">*</span>
                        </label>
                        <select name="loai_giao_dich" id="loai-giao-dich" class="form-select @error('loai_giao_dich') is-invalid @enderror" required onchange="filterCategoriesByType()">
                            <option value="">-- Chọn loại --</option>
                            <option value="THU" {{ old('loai_giao_dich') == 'THU' ? 'selected' : '' }}>Thu nhập</option>
                            <option value="CHI" {{ old('loai_giao_dich') == 'CHI' ? 'selected' : '' }}>Chi tiêu</option>
                        </select>
                        @error('loai_giao_dich')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <strong>Phương thức thanh toán</strong> <span class="required">*</span>
                        </label>
                        <select name="phuong_thuc_thanh_toan" class="form-select @error('phuong_thuc_thanh_toan') is-invalid @enderror" required>
                            <option value="">-- Chọn phương thức --</option>
                            <option value="Tiền mặt" {{ old('phuong_thuc_thanh_toan') == 'Tiền mặt' ? 'selected' : '' }}>Tiền mặt</option>
                            <option value="Chuyển khoản" {{ old('phuong_thuc_thanh_toan') == 'Chuyển khoản' ? 'selected' : '' }}>Chuyển khoản</option>
                        </select>
                        @error('phuong_thuc_thanh_toan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <strong>Danh mục</strong> <span class="required">*</span>
                        </label>
                        <select name="category_id" id="category-select" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Chọn loại giao dịch trước --</option>
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <strong>Số tiền</strong> <span class="required">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="so_tien" 
                            class="form-control @error('so_tien') is-invalid @enderror" 
                            placeholder="Nhập số tiền..."
                            value="{{ old('so_tien') }}"
                            min="0"
                            step="1000"
                            required
                        >
                        @error('so_tien')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <strong>Ngày giao dịch</strong> <span class="required">*</span>
                        </label>
                        <input 
                            type="date" 
                            name="ngay_giao_dich" 
                            class="form-control @error('ngay_giao_dich') is-invalid @enderror" 
                            value="{{ old('ngay_giao_dich', date('Y-m-d')) }}"
                            required
                        >
                        @error('ngay_giao_dich')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label"><strong>Ghi chú</strong></label>
                        <textarea 
                            name="ghi_chu" 
                            class="form-control form-textarea" 
                            placeholder="Ghi chú về giao dịch này..."
                        >{{ old('ghi_chu') }}</textarea>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('create-modal')">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="btn-primary">
                        Lưu giao dịch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Chỉnh Sửa -->
    <div class="modal-overlay" id="edit-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <div class="page-icon">
                        <img src="{{ asset('images/edit.png') }}" style="width:24px; filter: brightness(0) invert(1);">
                    </div>
                    Chỉnh sửa giao dịch
                </div>
                <div class="modal-close" onclick="closeModal('edit-modal')">
                    <img src="{{ asset('images/close.png') }}" style="width:16px">
                </div>
            </div>

            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">
                            <strong>Loại giao dịch</strong> <span class="required">*</span>
                        </label>
                        <select name="loai_giao_dich" id="edit-loai-giao-dich" class="form-select" required onchange="filterEditCategoriesByType()">
                            <option value="">-- Chọn loại --</option>
                            <option value="THU">Thu nhập</option>
                            <option value="CHI">Chi tiêu</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <strong>Phương thức thanh toán</strong> <span class="required">*</span>
                        </label>
                        <select name="phuong_thuc_thanh_toan" id="edit-payment-method" class="form-select" required>
                            <option value="">-- Chọn phương thức --</option>
                            <option value="Tiền mặt">Tiền mặt</option>
                            <option value="Chuyển khoản">Chuyển khoản</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <strong>Danh mục</strong> <span class="required">*</span>
                        </label>
                        <select name="category_id" id="edit-category" class="form-select" required>
                            <option value="">-- Chọn loại giao dịch trước --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <strong>Số tiền</strong> <span class="required">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="so_tien" 
                            id="edit-amount"
                            class="form-control" 
                            placeholder="Nhập số tiền..."
                            min="0"
                            step="1000"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <strong>Ngày giao dịch</strong> <span class="required">*</span>
                        </label>
                        <input 
                            type="date" 
                            name="ngay_giao_dich" 
                            id="edit-date"
                            class="form-control" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label"><strong>Ghi chú</strong></label>
                        <textarea 
                            name="ghi_chu" 
                            id="edit-desc"
                            class="form-control form-textarea" 
                            placeholder="Ghi chú về giao dịch này..."
                        ></textarea>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('edit-modal')">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="btn-primary">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const closeModal = modalId => document.getElementById(modalId)?.classList.remove('active');

const showError = (input, message) => {
    clearError(input);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'input-error';
    errorDiv.style.cssText = 'color: #dc2626; font-size: 12px; margin-top: 6px; display: flex; align-items: center; gap: 6px;';
    errorDiv.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>${message}</span>
    `;
    input.style.borderColor = '#dc2626';
    input.parentElement.appendChild(errorDiv);
};

const clearError = input => {
    input.parentElement.querySelector('.input-error')?.remove();
    input.style.borderColor = '';
};

const formatCurrencyOnBlur = input => {
    let value = input.value.replace(/\D/g, '');
    const num = parseInt(value) || 0;
    
    if (num > 100000000) {
        value = '100000000';
        showError(input, 'Số tiền không được vượt quá 100,000,000 VNĐ');
    } else if (num > 0 && num < 1000) {
        showError(input, 'Số tiền phải từ 1,000 VNĐ trở lên');
    } else {
        clearError(input);
    }
    
    input.value = value ? parseInt(value).toLocaleString('vi-VN') : '';
    
    const hiddenInput = input.parentElement.querySelector('.real-value');
    if (hiddenInput) hiddenInput.value = value;
};

const removeFormatOnFocus = input => {
    const value = input.value.replace(/\D/g, '');
    input.value = value;
    clearError(input);
};

const validateTransactionForm = form => {
    const amountInput = form.querySelector('[name="so_tien"]');
    const displayInput = amountInput.previousElementSibling;
    const value = amountInput.value.replace(/\D/g, '');
    
    if (!value) {
        showError(displayInput, 'Vui lòng nhập số tiền');
        displayInput.focus();
        return false;
    }
    
    const num = parseInt(value);
    if (num < 1000) {
        showError(displayInput, 'Số tiền phải từ 1,000 VNĐ trở lên');
        displayInput.focus();
        return false;
    }
    if (num > 100000000) {
        showError(displayInput, 'Số tiền không được vượt quá 100,000,000 VNĐ');
        displayInput.focus();
        return false;
    }
    
    amountInput.value = value;
    return true;
};

const setupCurrencyInput = input => {
    const wrapper = document.createElement('div');
    wrapper.style.position = 'relative';
    
    const displayInput = document.createElement('input');
    displayInput.type = 'text';
    displayInput.className = input.className;
    displayInput.placeholder = 'Ví dụ: 10,000,000';
    displayInput.value = input.value ? parseInt(input.value).toLocaleString('vi-VN') : '';
    
    input.type = 'hidden';
    input.className = 'real-value';
    
    displayInput.addEventListener('focus', () => removeFormatOnFocus(displayInput));
    displayInput.addEventListener('blur', () => formatCurrencyOnBlur(displayInput));
    displayInput.addEventListener('keypress', e => {
        if (!/[0-9]/.test(e.key)) e.preventDefault();
    });
    displayInput.addEventListener('paste', e => {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const numbers = pastedText.replace(/\D/g, '');
        if (numbers) displayInput.value = numbers;
    });
    
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(displayInput);
    wrapper.appendChild(input);
};

const categories = @json($categories);

function filterCategoriesByType() {
    const loaiGiaoDich = document.getElementById('loai-giao-dich').value;
    const categorySelect = document.getElementById('category-select');
    
    categorySelect.innerHTML = '<option value="">-- Chọn danh mục --</option>';
    
    if (loaiGiaoDich) {
        const filteredCategories = categories.filter(cat => cat.loai_danh_muc === loaiGiaoDich);
        
        filteredCategories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.ten_danh_muc;
            categorySelect.appendChild(option);
        });
    }
}

function filterEditCategoriesByType() {
    const loaiGiaoDich = document.getElementById('edit-loai-giao-dich').value;
    const categorySelect = document.getElementById('edit-category');
    
    const currentValue = categorySelect.value;
    categorySelect.innerHTML = '<option value="">-- Chọn danh mục --</option>';
    
    if (loaiGiaoDich) {
        const filteredCategories = categories.filter(cat => cat.loai_danh_muc === loaiGiaoDich);
        
        filteredCategories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.ten_danh_muc;
            if (cat.id == currentValue) option.selected = true;
            categorySelect.appendChild(option);
        });
    }
}

function openEditModal(transaction) {
    const form = document.getElementById('edit-form');
    form.action = `/transactions/${transaction.id}`;
    
    document.getElementById('edit-loai-giao-dich').value = transaction.loai_giao_dich;
    document.getElementById('edit-payment-method').value = transaction.phuong_thuc_thanh_toan;
    document.getElementById('edit-date').value = transaction.ngay_giao_dich;
    document.getElementById('edit-desc').value = transaction.ghi_chu || '';
    
    // Setup amount with format
    const amountInput = document.getElementById('edit-amount');
    const displayInput = amountInput.previousElementSibling;
    if (displayInput) {
        displayInput.value = parseInt(transaction.so_tien).toLocaleString('vi-VN');
        amountInput.value = transaction.so_tien;
    }
    
    // Filter and set category
    filterEditCategoriesByType();
    setTimeout(() => {
        document.getElementById('edit-category').value = transaction.category_id;
    }, 100);
    
    document.getElementById('edit-modal').classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => {
    // Setup currency inputs
    document.querySelectorAll('input[name="so_tien"]').forEach(setupCurrencyInput);
    
    // Form validation
    document.getElementById('create-form')?.addEventListener('submit', e => {
        if (!validateTransactionForm(e.target)) e.preventDefault();
    });
    document.getElementById('edit-form')?.addEventListener('submit', e => {
        if (!validateTransactionForm(e.target)) e.preventDefault();
    });
    
    // Modal controls
    document.getElementById('open-create-modal')?.addEventListener('click', () => {
        document.getElementById('create-modal').classList.add('active');
    });
    
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });
    
    // Close on ESC
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
        }
    });
    
    // Auto hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);

    @if($errors->any() && !$errors->has('id'))
        document.getElementById('create-modal')?.classList.add('active');
    @endif
});
// Xác nhận xóa 
document.querySelectorAll('.form-delete').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // chặn submit mặc định

        Swal.fire({
            title: 'Bạn chắc chưa?',
            text: 'Xóa rồi không khôi phục được đâu!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy',
            background: '#1E2937',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // submit lại nếu đồng ý
            }
        });
    });
});
</script>
@endsection