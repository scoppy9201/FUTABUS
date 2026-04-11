@extends('layouts.app')
@section('title', 'Quản lý ngân sách')
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

    body.dark .page-header,
    body.dark .filter-card,
    body.dark .table-card {
        background: var(--dark-card);
        border-color: var(--dark-border);
    }

    body.dark .page-title,
    body.dark .filter-title,
    body.dark .table-title,
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

    body.dark .form-label {
        color: #9ca3af;
    }

    body.dark tbody tr:hover {
        background: rgba(255, 255, 255, 0.03);
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
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .page-icon img {
        width: 100%;
    }

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

    .btn-secondary {
        background: var(--gray-200);
        color: #6b7280;
        border: 2px solid var(--gray-300);
        padding: 10px 20px;
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s ease;
        flex: 1;
        justify-content: center;
    }

    .btn-secondary:hover {
        background: var(--gray-300);
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

    .filter-form {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr 1fr auto;
        gap: 16px;
        align-items: flex-end;
    }

    .filter-form .form-group {
        margin-bottom: 0;
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        min-width: 220px;
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
    .form-select,
    .form-textarea {
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
    .form-select:focus,
    .form-textarea:focus {
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

    .stat-badge.total {
        background: #dbeafe;
        color: #1e40af;
    }

    .stat-badge.active {
        background: #d1fae5;
        color: #065f46;
    }

    .stat-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
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

    body.dark th {
        color: #9ca3af !important;
    }

    tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.2s ease;
    }

    body.dark tbody tr {
        border-bottom-color: rgba(255, 255, 255, 0.05);
    }

    tbody tr:hover {
        background: #f9fafb;
    }

    body.dark tbody tr:hover {
        background: rgba(255, 255, 255, 0.03);
    }

    td {
        padding: 16px 20px;
        font-size: 14px;
        color: #374151;
    }

    body.dark td {
        color: #e5e7eb;
    }

    .wallet-name {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .wallet-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    }

    .wallet-icon img {
        width: 100%;
    }

    .btn-sync {
        background: #fef3c7; 
        border: 2px solid #f59e0b;
    }

    .btn-sync:hover {
        background: #f59e0b;
    }

    .budget-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 250px;
    }

    .budget-amounts {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .budget-spent {
        font-weight: 600;
        color: #1f2937;
    }

    body.dark .budget-spent {
        color: #e5e7eb;
    }

    .budget-limit {
        color: #9ca3af;
    }

    .progress-bar {
        height: 8px;
        background: #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }

    body.dark .progress-bar {
        background: rgba(255, 255, 255, 0.1);
    }

    .progress-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease, background 0.3s ease;
        position: relative;
    }

    .progress-fill.low {
        background: linear-gradient(90deg, var(--success), #34d399);
    }

    .progress-fill.medium {
        background: linear-gradient(90deg, var(--warning), #fbbf24);
    }

    .progress-fill.high {
        background: linear-gradient(90deg, var(--danger), #f87171);
    }

    .progress-fill.over {
        background: linear-gradient(90deg, #dc2626, #991b1b);
    }

    .progress-percentage {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        margin-top: 4px;
    }

    body.dark .progress-percentage {
        color: #9ca3af;
    }

    .budget-status {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 12px;
    }

    .budget-status.safe {
        background: #d1fae5;
        color: #065f46;
    }

    .budget-status.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .budget-status.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .budget-status.over {
        background: #7f1d1d;
        color: white;
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

    .badge-active {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-dot.active {
        background: #059669;
    }

    .status-dot.inactive {
        background: #dc2626;
    }

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

    .btn-toggle {
        background: #fef3c7;
        border: 2px solid var(--warning);
    }

    .btn-toggle:hover {
        background: var(--warning);
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

    .pagination .disabled span {
        background: #f9fafb;
        color: #d1d5db;
        cursor: not-allowed;
    }

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

    .modal-title .page-icon {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
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

    .form-group-compact {
        margin-bottom: 20px;
    }

    .form-group-compact:last-child {
        margin-bottom: 0;
    }

    .form-group-compact .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        display: block;
    }

    .form-group-compact .form-label strong {
        color: #1f2937;
    }

    .form-help-compact {
        font-size: 12px;
        color: #6b7280;
        margin-top: 6px;
        display: flex;
        align-items: flex-start;
        gap: 6px;
        line-height: 1.4;
    }

    .form-help-compact::before {
        content: "💡";
        font-size: 14px;
    }

    /* Budget Status Badges */
    .budget-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .budget-status img {
        width: 16px;
        height: 16px;
        object-fit: contain;
    }

    .budget-status.over {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .budget-status.danger {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .budget-status.warning {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fbbf24;
    }

    .budget-status.safe {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .modal-actions-fixed {
        padding: 20px 32px;
        background: white;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
    }

    .modal-actions-fixed .btn-primary,
    .modal-actions-fixed .btn-secondary {
        flex: 1;
        min-height: 44px;
        font-size: 14px;
        border-radius: 8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
    }

    body.dark .modal-content {
        background: var(--dark-card);
    }

    body.dark .modal-body {
        background: var(--dark-card);
    }

    body.dark .modal-actions-fixed {
        background: var(--dark-card);
        border-top-color: var(--dark-border);
    }

    body.dark .form-group-compact .form-label {
        color: #9ca3af;
    }

    body.dark .form-group-compact .form-label strong {
        color: #e5e7eb;
    }

    @media (max-width: 1024px) {
        .filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .filter-actions {
            grid-column: 1 / -1;
            width: 100%;
        }

        table {
            min-width: 1000px;
        }
    }

    @media (max-width: 768px) {
        .modal-content {
            max-width: 96%;
        }

        .modal-header {
            padding: 20px 24px;
        }

        .modal-body {
            padding: 24px 28px;
        }
    }

    @media (max-width: 640px) {
        .filter-form {
            grid-template-columns: 1fr;
        }
    }

        /* ── Fix Laravel Pagination ── */
    .pagination-wrapper nav > div:first-child { display: none; }

    .pagination-wrapper nav ul {
        display: flex;
        gap: 6px;
        align-items: center;
        list-style: none;
        padding: 0; margin: 0;
    }

    .pagination-wrapper nav svg {
        width: 16px; height: 16px;
    }

    .pagination-wrapper nav button,
    .pagination-wrapper nav a {
        display: inline-flex;
        align-items: center; justify-content: center;
        min-width: 36px; height: 36px;
        padding: 0 12px; border-radius: 8px;
        font-size: 14px; font-weight: 600;
        text-decoration: none;
        transition: background 0.2s ease;
        border: none; cursor: pointer;
        background: #f9fafb; color: #6b7280;
    }

    .pagination-wrapper nav a:hover,
    .pagination-wrapper nav button:hover {
        background: #f3f4f6; color: #374151;
    }

    .pagination-wrapper nav span[aria-current="page"] span {
        display: inline-flex;
        align-items: center; justify-content: center;
        min-width: 36px; height: 36px;
        padding: 0 12px; border-radius: 8px;
        font-size: 14px; font-weight: 600;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
    }

    .pagination-wrapper nav span:not([aria-current="page"]) > span {
        display: inline-flex;
        align-items: center; justify-content: center;
        min-width: 36px; height: 36px;
        padding: 0 12px; border-radius: 8px;
        font-size: 14px; font-weight: 600;
        background: #f9fafb; color: #d1d5db;
    }

    /* Dark mode */
    body.dark .pagination-wrapper nav a,
    body.dark .pagination-wrapper nav button {
        background: var(--dark-bg); color: #e5e7eb;
    }

    body.dark .pagination-wrapper nav a:hover,
    body.dark .pagination-wrapper nav button:hover {
        background: var(--dark-card);
    }

    body.dark .pagination-wrapper nav span:not([aria-current="page"]) > span {
        background: var(--dark-bg); color: #4b5563;
    }
</style>

<div class="wallet-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <div class="page-icon">
                <img src="{{ asset('images/asset-allocation.png') }}" alt="Wallet">
            </div>
            <span>Quản lý ngân sách</span>
        </div>
        <button type="button" class="btn-primary" id="open-create-modal">
            <img src="{{ asset('images/plus.png') }}" alt="Add">
            Thêm ngân sách
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

    <!-- Filter Section -->
    <div class="filter-card">
        <div class="filter-title">
            <img src="{{ asset('images/filter.png') }}" alt="Filter">
            <span>Bộ lọc & Tìm kiếm</span>
        </div>
        <form action="{{ route('wallets.index') }}" method="GET" class="filter-form">
            <div class="form-group">
                <label class="form-label">Tìm kiếm</label>
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Nhập tên ngân sách..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="form-group">
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->ten_danh_muc }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Trạng thái</label>
                <select name="trang_thai" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="1" {{ request('trang_thai') === '1' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="0" {{ request('trang_thai') === '0' ? 'selected' : '' }}>Vô hiệu hóa</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Sắp xếp</label>
                <select name="sort_by" class="form-select">
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                    <option value="ten_ngan_sach" {{ request('sort_by') == 'ten_ngan_sach' ? 'selected' : '' }}>Tên ngân sách</option>
                    <option value="ngan_sach_goc" {{ request('sort_by') == 'ngan_sach_goc' ? 'selected' : '' }}>Hạn mức</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter btn-search">
                    <img src="{{ asset('images/search.png') }}" alt="Search">
                    Tìm kiếm
                </button>
                <a href="{{ route('wallets.index') }}" class="btn-filter btn-reset">
                    <img src="{{ asset('images/refresh.png') }}" alt="Reset">
                    Đặt lại
                </a>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">
                <img src="{{ asset('images/list.png') }}" alt="List">
                Danh sách ngân sách
            </h3>
            <div class="table-stats">
                <span class="stat-badge total">
                    <img src="{{ asset('images/chart.png') }}" alt="Total">
                    Tổng: {{ $wallets->total() }}
                </span>
                <span class="stat-badge active">
                    <img src="{{ asset('images/check.png') }}" alt="Active">
                    Hoạt động: {{ $wallets->where('trang_thai', 1)->count() }}
                </span>
                <span class="stat-badge inactive">
                    <img src="{{ asset('images/lock.png') }}" alt="Inactive">
                    Vô hiệu: {{ $wallets->where('trang_thai', 0)->count() }}
                </span>
            </div>
        </div>

        @if($wallets->count() > 0)
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Tên ngân sách</th>
                        <th>Danh mục</th>
                        <th style="min-width: 280px;">Tiến độ sử dụng</th>
                        <th>Hạn mức</th>
                        <th>Trạng thái</th>
                        <th style="width: 130px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wallets as $index => $wallet)
                    <tr>
                        <td>{{ $wallets->firstItem() + $index }}</td>
                        <td>
                            <div class="wallet-name">
                                <div class="wallet-icon">
                                    <img src="{{ asset('images/category-icons/' . ($wallet->category->bieu_tuong ?? 'money.png')) }}" alt="Wallet">
                                </div>
                                <div>
                                    <strong>{{ $wallet->ten_ngan_sach }}</strong>
                                    @if($wallet->mo_ta)
                                        <div style="font-size: 12px; color: #9ca3af; margin-top: 2px;">
                                            {{ Str::limit($wallet->mo_ta, 40) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $wallet->category->ten_danh_muc ?? '---' }}</td>
                        <td>
                            <div class="budget-info">
                                <div class="budget-amounts">
                                    <span class="budget-spent">
                                        Đã chi: {{ number_format($wallet->spent_amount, 0, ',', '.') }}đ
                                    </span>
                                    <span class="budget-limit">
                                        / {{ number_format($wallet->ngan_sach_goc, 0, ',', '.') }}đ
                                    </span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill 
                                        @if($wallet->spent_percentage > 100) over
                                        @elseif($wallet->spent_percentage >= 80) high
                                        @elseif($wallet->spent_percentage >= 50) medium
                                        @else low
                                        @endif"
                                        style="width: {{ min($wallet->spent_percentage, 100) }}%">
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="progress-percentage">
                                        {{ number_format($wallet->spent_percentage, 1) }}%
                                    </div>
                                    @if($wallet->is_over_budget)
                                        <div class="budget-status over">
                                            <img src="{{ asset('images/warning.png') }}" alt="Warning">
                                            Vượt mức
                                        </div>
                                    @elseif($wallet->spent_percentage >= 80)
                                        <div class="budget-status danger">
                                            <img src="{{ asset('images/alert.png') }}" alt="Alert">
                                            Sắp hết
                                        </div>
                                    @elseif($wallet->spent_percentage >= 50)
                                        <div class="budget-status warning">
                                            <img src="{{ asset('images/caution.png') }}" alt="Caution">
                                            Cảnh báo
                                        </div>
                                    @else
                                        <div class="budget-status safe">
                                            <img src="{{ asset('images/check.png') }}" alt="Check">
                                            An toàn
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ number_format($wallet->ngan_sach_goc, 0, ',', '.') }}đ</div>
                            <div style="font-size: 12px; color: #9ca3af;">Còn: {{ number_format($wallet->so_du, 0, ',', '.') }}đ</div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $wallet->trang_thai ? 'active' : 'inactive' }}">
                                <span class="status-dot {{ $wallet->trang_thai ? 'active' : 'inactive' }}"></span>
                                {{ $wallet->trang_thai ? 'Hoạt động' : 'Vô hiệu hóa' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <!-- Button đồng bộ số dư -->
                                <form action="{{ route('wallets.sync-balance', $wallet) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="button" class="btn-action btn-sync" 
                                    data-id="{{ $wallet->id }}"
                                    onclick="syncWallet({{ $wallet->id }})" 
                                    title="Đồng bộ số dư">
                                    <img src="{{ asset('images/refresh.png') }}" alt="Sync">
                                    </button>
                                </form>

                                <!-- Button toggle status -->
                                <form action="{{ route('wallets.toggle-status', $wallet) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="button" class="btn-action btn-toggle"
                                    data-id="{{ $wallet->id }}"
                                    onclick="toggleWallet({{ $wallet->id }})"
                                    title="{{ $wallet->trang_thai ? 'Vô hiệu hóa' : 'Kích hoạt' }}">
                                        <img src="{{ asset('images/' . ($wallet->trang_thai ? 'lock' : 'unlock') . '.png') }}" alt="Toggle">
                                    </button>
                                </form>

                                <!-- Button edit -->
                                <button type="button" class="btn-action btn-edit" onclick="openEditModal({{ json_encode($wallet) }})" title="Chỉnh sửa">
                                    <img src="{{ asset('images/edit.png') }}" alt="Edit">
                                </button>

                                <!-- Button delete -->
                                <form action="{{ route('wallets.destroy', $wallet) }}"
                                 method="POST" style="display: inline;" 
                                 class="form-delete"
                                 data-id="{{ $wallet->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-action btn-delete"
                                    onclick="deleteWallet({{ $wallet->id }})"
                                    title="Xóa">
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
                Hiển thị {{ $wallets->firstItem() }} - {{ $wallets->lastItem() }} / {{ $wallets->total() }} kết quả
            </div>
            <div>
                {{ $wallets->links() }}
            </div>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-icon">
                <img src="{{ asset('images/empty-folder.png') }}" alt="Empty">
            </div>
            <h3>Chưa có ngân sách nào</h3>
            <p>Hãy tạo ngân sách đầu tiên để quản lý chi tiêu hiệu quả</p>
            <button type="button" class="btn-primary" onclick="document.getElementById('create-modal').classList.add('active')">
                <img src="{{ asset('images/plus.png') }}" alt="Add">
                Thêm ngân sách đầu tiên
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
                        <img src="{{ asset('images/wallet.png') }}" alt="Wallet">
                    </div>
                    Thêm ngân sách mới
                </div>
                <div class="modal-close" onclick="closeModal('create-modal')">
                    <img src="{{ asset('images/close.png') }}" alt="Close">
                </div>
            </div>

            <form id="create-form">
                @csrf
                <div class="modal-body">
                    <!-- Tên ngân sách -->
                    <div class="form-group-compact">
                        <label class="form-label">
                            <strong>Tên ngân sách</strong> <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="ten_ngan_sach" 
                            class="form-control @error('ten_ngan_sach') is-invalid @enderror" 
                            placeholder="Ví dụ: Ngân sách ăn uống tháng 1"
                            value="{{ old('ten_ngan_sach') }}"
                            required
                        >
                        @error('ten_ngan_sach')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Danh mục -->
                    <div class="form-group-compact">
                        <label class="form-label">
                            <strong>Danh mục</strong> <span class="required">*</span>
                        </label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->ten_danh_muc }} ({{ $category->loai_danh_muc == 'THU' ? 'Thu' : 'Chi' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-help-compact">Chọn danh mục phù hợp với mục đích sử dụng</div>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Hạn mức ngân sách -->
                    <div class="form-group-compact">
                        <label class="form-label">
                            <strong>Hạn mức ngân sách</strong> <span class="required">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="ngan_sach_goc" 
                            class="form-control @error('ngan_sach_goc') is-invalid @enderror" 
                            placeholder="Nhập số tiền"
                            value="{{ old('ngan_sach_goc') }}"
                            min="0"
                            step="1000"
                            required
                        >
                        <div class="form-help-compact">Số tiền tối đa bạn muốn chi tiêu</div>
                        @error('ngan_sach_goc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Mô tả -->
                    <div class="form-group-compact">
                        <label class="form-label"><strong>Mô tả</strong></label>
                        <textarea 
                            name="mo_ta" 
                            class="form-textarea" 
                            placeholder="Ghi chú thêm về ngân sách này..."
                            style="min-height: 90px;">{{ old('mo_ta') }}</textarea>
                    </div>
                </div>
                
                <div class="modal-actions-fixed">
                    <button type="button" class="btn-secondary" onclick="closeModal('create-modal')">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="btn-primary">
                        Lưu ngân sách
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
                        <img src="{{ asset('images/edit.png') }}" alt="Edit">
                    </div>
                    Chỉnh sửa ngân sách
                </div>
                <div class="modal-close" onclick="closeModal('edit-modal')">
                    <img src="{{ asset('images/close.png') }}" style="width:16px">
                </div>
            </div>

            <form id="edit-form">
                @csrf
                @method('PUT')
                
                <div class="modal-body">
                    <!-- Tên ngân sách -->
                    <div class="form-group-compact">
                        <label class="form-label">
                            <strong>Tên ngân sách</strong> <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="ten_ngan_sach" 
                            id="edit-ten"
                            class="form-control" 
                            placeholder="Ví dụ: Ngân sách ăn uống tháng 1"
                            required
                        >
                    </div>

                    <!-- Danh mục -->
                    <div class="form-group-compact">
                        <label class="form-label">
                            <strong>Danh mục</strong> <span class="required">*</span>
                        </label>
                        <select name="category_id" id="edit-category" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->ten_danh_muc }} ({{ $category->loai_danh_muc == 'THU' ? 'Thu' : 'Chi' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-help-compact">Chọn danh mục phù hợp với mục đích sử dụng</div>
                    </div>

                    <!-- Hạn mức ngân sách -->
                    <div class="form-group-compact">
                        <label class="form-label">
                            <strong>Hạn mức ngân sách</strong> <span class="required">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="ngan_sach_goc" 
                            id="edit-budget"
                            class="form-control" 
                            placeholder="Nhập số tiền"
                            min="0"
                            step="1000"
                            required
                        >
                        <div class="form-help-compact">Số tiền tối đa bạn muốn chi tiêu</div>
                    </div>

                    <!-- Mô tả -->
                    <div class="form-group-compact">
                        <label class="form-label"><strong>Mô tả</strong></label>
                        <textarea 
                            name="mo_ta" 
                            id="edit-mota"
                            class="form-textarea" 
                            placeholder="Ghi chú thêm về ngân sách này..."
                            style="min-height: 90px;"></textarea>
                    </div>
                </div>
                
                <div class="modal-actions-fixed">
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
    const API_BASE = '/budgets';

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    async function apiRequest(method, url, data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
        };
        if (data) options.body = JSON.stringify(data);
        return await fetch(url, options);
    }

    function showSuccess(message) {
    // Lưu vào sessionStorage để hiển thị sau reload
    sessionStorage.setItem('pending_toast', JSON.stringify({
        type: 'success',
        title: 'Thành công',
        message: message
    }));
    location.reload();
}

function showError(message) {
    // Lỗi không reload, hiển thị trực tiếp
    showToast({ type: 'error', title: 'Lỗi', message: message });
}

    // ── STORE ─────────────────────────────────────────────────────────────────────
    document.getElementById('create-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const payload = {
            ten_ngan_sach: form.ten_ngan_sach.value,
            category_id:   form.category_id.value,
            ngan_sach_goc: form.ngan_sach_goc.value,
            mo_ta:         form.mo_ta.value || null,
        };

        const res  = await apiRequest('POST', API_BASE, payload);
        const json = await res.json();

        if (res.ok) {
            showSuccess(json.message);
        } else {
            const errors = json.errors
                ? Object.values(json.errors).flat().join('\n')
                : json.message ?? 'Có lỗi xảy ra!';
            showError(errors);
        }
    });

    // ── UPDATE ────────────────────────────────────────────────────────────────────
    document.getElementById('edit-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const id   = form.dataset.id;
        const payload = {
            ten_ngan_sach: form.ten_ngan_sach.value,
            category_id:   form.category_id.value,
            ngan_sach_goc: form.ngan_sach_goc.value,
            mo_ta:         form.mo_ta.value || null,
        };

        const res  = await apiRequest('PATCH', `${API_BASE}/${id}`, payload);
        const json = await res.json();

        if (res.ok) {
            showSuccess(json.message);
        } else {
            const errors = json.errors
                ? Object.values(json.errors).flat().join('\n')
                : json.message ?? 'Có lỗi xảy ra!';
            showError(errors);
        }
    });

    // ── DELETE ────────────────────────────────────────────────────────────────────
    async function deleteWallet(id) {
        const result = await Swal.fire({
            title: 'Bạn chắc chưa?',
            text: 'Xóa rồi không khôi phục được đâu!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy',
            background: '#1E2937', color: '#fff',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
        });

        if (result.isConfirmed) {
            const res  = await apiRequest('DELETE', `${API_BASE}/${id}`);
            const json = await res.json();
            res.ok ? showSuccess(json.message) : showError(json.message ?? 'Không thể xóa!');
        }
    }

    // ── TOGGLE STATUS ─────────────────────────────────────────────────────────────
    async function toggleWallet(id) {
        const res  = await apiRequest('PATCH', `${API_BASE}/${id}/status`);
        const json = await res.json();
        res.ok ? showSuccess(json.message) : showError(json.message ?? 'Có lỗi xảy ra!');
    }

    // ── SYNC BALANCE ──────────────────────────────────────────────────────────────
    async function syncWallet(id) {
        const result = await Swal.fire({
            title: 'Đồng bộ số dư?',
            text: 'Tính lại số dư dựa trên tất cả giao dịch?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Đồng bộ',
            cancelButtonText: 'Hủy',
            background: '#1E2937', color: '#fff',
            confirmButtonColor: '#4a90e2',
            cancelButtonColor: '#6b7280',
        });

        if (result.isConfirmed) {
            const res  = await apiRequest('POST', `${API_BASE}/${id}/sync`);
            const json = await res.json();
            res.ok ? showSuccess(json.message) : showError(json.message ?? 'Có lỗi xảy ra!');
        }
    }

    // ── OPEN EDIT MODAL ───────────────────────────────────────────────────────────
    const openEditModal = wallet => {
        const form = document.getElementById('edit-form');
        form.dataset.id = wallet.id;

        document.getElementById('edit-ten').value      = wallet.ten_ngan_sach;
        document.getElementById('edit-category').value = wallet.category_id;
        document.getElementById('edit-mota').value     = wallet.mo_ta || '';

        const budgetInput    = document.getElementById('edit-budget');
        const displayInput   = budgetInput.previousElementSibling;
        if (displayInput) {
            displayInput.value = parseInt(wallet.ngan_sach_goc).toLocaleString('vi-VN');
            budgetInput.value  = wallet.ngan_sach_goc;
        }

        document.getElementById('edit-modal').classList.add('active');
    };

    // ── CLOSE MODAL ───────────────────────────────────────────────────────────────
    const closeModal = modalId => document.getElementById(modalId)?.classList.remove('active');

    // ── CURRENCY INPUT SETUP ──────────────────────────────────────────────────────
    const removeFormatOnFocus = input => { input.value = input.value.replace(/\D/g, ''); };

    const formatCurrencyOnBlur = input => {
        const num = parseInt(input.value.replace(/\D/g, '')) || 0;
        const hiddenInput = input.parentElement.querySelector('.real-value');
        if (hiddenInput) hiddenInput.value = num || '';
        input.value = num ? num.toLocaleString('vi-VN') : '';
    };

    const setupCurrencyInput = input => {
        const wrapper      = document.createElement('div');
        wrapper.style.position = 'relative';
        const displayInput = document.createElement('input');
        displayInput.type        = 'text';
        displayInput.className   = input.className;
        displayInput.placeholder = 'Ví dụ: 10,000,000';
        displayInput.value       = input.value ? parseInt(input.value).toLocaleString('vi-VN') : '';
        input.type      = 'hidden';
        input.className = 'real-value';
        displayInput.addEventListener('focus', () => removeFormatOnFocus(displayInput));
        displayInput.addEventListener('blur',  () => formatCurrencyOnBlur(displayInput));
        displayInput.addEventListener('keypress', e => { if (!/[0-9]/.test(e.key)) e.preventDefault(); });
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(displayInput);
        wrapper.appendChild(input);
    };

    // ── DOM READY ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[name="ngan_sach_goc"]').forEach(setupCurrencyInput);

        document.getElementById('open-create-modal')?.addEventListener('click', () => {
            document.getElementById('create-modal').classList.add('active');
        });

        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) overlay.classList.remove('active');
            });
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active')
                        .forEach(m => m.classList.remove('active'));
            }
        });

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity   = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    });
</script>
@endsection