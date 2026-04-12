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

    <!-- Toast -->
    <div id="toast" class="alert" style="display:none;"></div>

    <!-- Filter -->
    <div class="filter-card">
        <div class="filter-title">
            <img src="{{ asset('images/filter.png') }}" alt="Filter">
            <span>Bộ lọc & Tìm kiếm</span>
        </div>
        <div class="filter-form">
            <div class="form-group">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" id="filter-search" class="form-control" placeholder="Nhập tên ngân sách...">
            </div>
            <div class="form-group">
                <label class="form-label">Danh mục</label>
                <select id="filter-category" class="form-select">
                    <option value="">Tất cả danh mục</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Trạng thái</label>
                <select id="filter-status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="1">Hoạt động</option>
                    <option value="0">Vô hiệu hóa</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Sắp xếp</label>
                <select id="filter-sort" class="form-select">
                    <option value="created_at">Ngày tạo</option>
                    <option value="ten_ngan_sach">Tên ngân sách</option>
                    <option value="ngan_sach_goc">Hạn mức</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="button" class="btn-filter btn-search" id="btn-search">
                    <img src="{{ asset('images/search.png') }}" alt="Search"> Áp dụng lọc
                </button>
                <button type="button" class="btn-filter btn-reset" id="btn-reset">
                    <img src="{{ asset('images/refresh.png') }}" alt="Reset"> Đặt lại
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">
                <img src="{{ asset('images/list.png') }}" alt="List"> Danh sách ngân sách
            </h3>
            <div class="table-stats" id="table-stats"></div>
        </div>

        <div id="table-loading" style="text-align:center; padding:40px; color:#9ca3af;">
            Đang tải...
        </div>

        <div id="table-body" style="display:none;">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Tên ngân sách</th>
                            <th>Danh mục</th>
                            <th style="min-width:280px;">Tiến độ sử dụng</th>
                            <th>Hạn mức</th>
                            <th>Trạng thái</th>
                            <th style="width:160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="wallets-tbody"></tbody>
                </table>
            </div>
            <div class="pagination-wrapper">
                <div id="pagination-info" class="pagination-info"></div>
                <div id="pagination-links"></div>
            </div>
        </div>

        <div id="empty-state" style="display:none;" class="empty-state">
            <div class="empty-icon">
                <img src="{{ asset('images/empty-folder.png') }}" alt="Empty">
            </div>
            <h3>Chưa có ngân sách nào</h3>
            <p>Hãy tạo ngân sách đầu tiên để quản lý chi tiêu hiệu quả</p>
            <button type="button" class="btn-primary" id="empty-add-btn">
                <img src="{{ asset('images/plus.png') }}" alt="Add"> Thêm ngân sách đầu tiên
            </button>
        </div>
    </div>

    @include('budgets._modal_create')
    @include('budgets._modal_edit')
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {

var API_BASE = '/api/v1/budgets';

let currentPage   = 1;
let searchTimeout = null;
let allCategories = [];

/* HELPERS */
function escHtml(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, c =>
        ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c])
    );
}

function formatMoney(n) {
    return parseInt(n).toLocaleString('vi-VN') + 'đ';
}

function showToast(msg, type = 'success') {
    const el = document.getElementById('toast');
    el.className = `alert alert-${type === 'success' ? 'success' : 'error'}`;
    el.textContent = msg;
    el.style.display = 'flex';
    el.style.opacity = '1';
    clearTimeout(el._t);
    el._t = setTimeout(() => {
        el.style.opacity = '0';
        setTimeout(() => { el.style.display = 'none'; }, 300);
    }, 4000);
}

/* API */
async function api(method, url, body = null) {
    const opts = {
        method,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        credentials: 'same-origin',
    };
    if (body) opts.body = JSON.stringify(body);
    const res  = await fetch(url, opts);
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw data;
    return data;
}

/* FILTERS */
function getFilters(page = 1) {
    const p = new URLSearchParams();
    const search   = document.getElementById('filter-search').value.trim();
    const category = document.getElementById('filter-category').value;
    const status   = document.getElementById('filter-status').value;
    const sort     = document.getElementById('filter-sort').value;
    if (search)        p.set('search',      search);
    if (category)      p.set('category_id', category);
    if (status !== '') p.set('trang_thai',  status);
    p.set('sort_by', sort);
    p.set('page',    page);
    return p.toString();
}

/* LOAD */
async function loadWallets(page = 1) {
    currentPage = page;
    document.getElementById('table-loading').style.display = 'block';
    document.getElementById('table-body').style.display    = 'none';
    document.getElementById('empty-state').style.display   = 'none';

    try {
        const data = await api('GET', `${API_BASE}?${getFilters(page)}`);
        allCategories = data.categories ?? [];
        renderTable(data.wallets);
        renderStats(data.wallets);
        renderCategoryFilter(allCategories);
        renderCategoryOptions(allCategories);
    } catch {
        window.showToast({ type: 'error', title: 'Lỗi', message: 'Không thể tải danh sách ngân sách.' });
    } finally {
        document.getElementById('table-loading').style.display = 'none';
    }
}

/* RENDER TABLE */
function renderTable(p) {
    if (!p.data.length) {
        document.getElementById('empty-state').style.display = 'block';
        return;
    }
    document.getElementById('table-body').style.display = 'block';
    const offset = p.from ?? 1;

    document.getElementById('wallets-tbody').innerHTML = p.data.map((w, i) => {
        const pct      = w.spent_percentage ?? 0;
        const fillClass = pct > 100 ? 'over' : pct >= 80 ? 'high' : pct >= 50 ? 'medium' : 'low';
        const statusClass = w.is_over_budget ? 'over' : pct >= 80 ? 'danger' : pct >= 50 ? 'warning' : 'safe';
        const statusLabel = w.is_over_budget ? 'Vượt mức' : pct >= 80 ? 'Sắp hết' : pct >= 50 ? 'Cảnh báo' : 'An toàn';
        const statusIcon  = w.is_over_budget ? 'warning' : pct >= 80 ? 'alert' : pct >= 50 ? 'caution' : 'check';

        return `
        <tr>
            <td>${offset + i}</td>
            <td>
                <div class="wallet-name">
                    <div class="wallet-icon">
                        <img src="/images/category-icons/${escHtml(w.category?.bieu_tuong ?? 'money.png')}" alt="icon">
                    </div>
                    <div>
                        <strong>${escHtml(w.ten_ngan_sach)}</strong>
                        ${w.mo_ta ? `<div style="font-size:12px;color:#9ca3af;margin-top:2px;">${escHtml(w.mo_ta.substring(0,40))}${w.mo_ta.length > 40 ? '…' : ''}</div>` : ''}
                    </div>
                </div>
            </td>
            <td>${escHtml(w.category?.ten_danh_muc ?? '---')}</td>
            <td>
                <div class="budget-info">
                    <div class="budget-amounts">
                        <span class="budget-spent">Đã chi: ${formatMoney(w.spent_amount ?? 0)}</span>
                        <span class="budget-limit">/ ${formatMoney(w.ngan_sach_goc)}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill ${fillClass}" style="width:${Math.min(pct, 100)}%"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div class="progress-percentage">${parseFloat(pct).toFixed(1)}%</div>
                        <div class="budget-status ${statusClass}">
                            <img src="/images/${statusIcon}.png" alt="${statusLabel}">
                            ${statusLabel}
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <div style="font-weight:600;">${formatMoney(w.ngan_sach_goc)}</div>
                <div style="font-size:12px;color:#9ca3af;">Còn: ${formatMoney(w.so_du)}</div>
            </td>
            <td>
                <span class="badge badge-${w.trang_thai ? 'active' : 'inactive'}">
                    <span class="status-dot ${w.trang_thai ? 'active' : 'inactive'}"></span>
                    ${w.trang_thai ? 'Hoạt động' : 'Vô hiệu hóa'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-sync"
                        onclick="handleSync(${w.id})" title="Đồng bộ số dư">
                        <img src="/images/refresh.png" alt="Sync">
                    </button>
                    <button type="button" class="btn-action btn-toggle"
                        onclick="handleToggle(${w.id})"
                        title="${w.trang_thai ? 'Vô hiệu hóa' : 'Kích hoạt'}">
                        <img src="/images/${w.trang_thai ? 'lock' : 'unlock'}.png" alt="toggle">
                    </button>
                    <button type="button" class="btn-action btn-edit"
                        onclick='openEditModal(${JSON.stringify(w)})'
                        title="Chỉnh sửa">
                        <img src="/images/edit.png" alt="edit">
                    </button>
                    <button type="button" class="btn-action btn-delete"
                        onclick="handleDelete(${w.id})"
                        title="Xóa">
                        <img src="/images/delete.png" alt="delete">
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');

    document.getElementById('pagination-info').textContent =
        `Hiển thị ${p.from ?? 0} - ${p.to ?? 0} / ${p.total} kết quả`;
    renderPagination(p);
}

function renderPagination(p) {
    const el   = document.getElementById('pagination-links');
    const cur  = p.current_page;
    const last = p.last_page;
    let html   = '<div style="display:flex;gap:6px;">';
    if (cur > 1)
        html += `<button class="btn-filter btn-reset" style="min-width:36px;padding:0 10px;height:36px;" onclick="loadWallets(${cur-1})">‹</button>`;
    for (let i = Math.max(1, cur-2); i <= Math.min(last, cur+2); i++)
        html += `<button class="btn-filter ${i===cur?'btn-search':'btn-reset'}" style="min-width:36px;padding:0 10px;height:36px;" onclick="loadWallets(${i})">${i}</button>`;
    if (cur < last)
        html += `<button class="btn-filter btn-reset" style="min-width:36px;padding:0 10px;height:36px;" onclick="loadWallets(${cur+1})">›</button>`;
    html += '</div>';
    el.innerHTML = html;
}

function renderStats(p) {
    const active   = p.data.filter(w => w.trang_thai).length;
    const inactive = p.data.filter(w => !w.trang_thai).length;
    document.getElementById('table-stats').innerHTML = `
        <span class="stat-badge total"><img src="/images/chart.png" alt=""> Tổng: ${p.total}</span>
        <span class="stat-badge active"><img src="/images/check.png" alt=""> Hoạt động: ${active}</span>
        <span class="stat-badge inactive"><img src="/images/lock.png" alt=""> Vô hiệu: ${inactive}</span>
    `;
}

function renderCategoryFilter(categories) {
    const el  = document.getElementById('filter-category');
    const val = el.value;
    el.innerHTML = '<option value="">Tất cả danh mục</option>' +
        categories.map(c => `<option value="${c.id}" ${c.id == val ? 'selected':''}>${escHtml(c.ten_danh_muc)}</option>`).join('');
}

function renderCategoryOptions(categories) {
    ['create-category', 'edit-category'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        const val = el.value;
        el.innerHTML = '<option value="">-- Chọn danh mục --</option>' +
            categories.map(c => `<option value="${c.id}" ${c.id == val ? 'selected':''}>${escHtml(c.ten_danh_muc)} (Chi)</option>`).join('');
    });
}

/* CREATE */
async function handleCreate(e) {
    e.preventDefault();
    clearFormErrors('create');
    const form   = e.target;
    const amount = form.querySelector('.amount-display')?.value.replace(/\D/g, '') ?? '';
    form.querySelector('[name="ngan_sach_goc"]').value = amount;

    const body = {
        ten_ngan_sach: form.querySelector('[name="ten_ngan_sach"]').value,
        category_id:   form.querySelector('[name="category_id"]').value,
        ngan_sach_goc: amount,
        mo_ta:         form.querySelector('[name="mo_ta"]').value,
    };

    try {
        await api('POST', API_BASE, body);
        window.showToast({ type: 'success', title: 'Thành công', message: 'Thêm ngân sách thành công!' });
        document.getElementById('create-modal').classList.remove('active');
        form.reset();
        form.querySelector('.amount-display').value = '';
        loadWallets(currentPage);
    } catch (err) {
        if (err.errors)  showFormErrors(err.errors, 'create');
        if (err.message) window.showToast({ type: 'error', title: 'Lỗi', message: err.message });
    }
}

/* UPDATE */
async function handleUpdate(e) {
    e.preventDefault();
    clearFormErrors('edit');
    const form   = e.target;
    const id     = form.dataset.id;
    const amount = form.querySelector('.amount-display')?.value.replace(/\D/g, '') ?? '';
    form.querySelector('[name="ngan_sach_goc"]').value = amount;

    const body = {
        ten_ngan_sach: form.querySelector('[name="ten_ngan_sach"]').value,
        category_id:   form.querySelector('[name="category_id"]').value,
        ngan_sach_goc: amount,
        mo_ta:         form.querySelector('[name="mo_ta"]').value,
    };

    try {
        await api('PATCH', `${API_BASE}/${id}`, body);
        window.showToast({ type: 'success', title: 'Thành công', message: 'Cập nhật ngân sách thành công!' });
        document.getElementById('edit-modal').classList.remove('active');
        loadWallets(currentPage);
    } catch (err) {
        if (err.errors)  showFormErrors(err.errors, 'edit');
        if (err.message) window.showToast({ type: 'error', title: 'Lỗi', message: err.message });
    }
}

/* Modal xác nhận xóa giao dịch */
function showDeleteModal(onConfirm) {
  const overlay = document.createElement('div');
  overlay.style.cssText = `
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.45);
    display: flex; align-items: center; justify-content: center;
  `;

  const isDark = document.body.classList.contains('dark');

  overlay.innerHTML = `
    <div style="
      background: ${isDark ? '#1E2937' : '#ffffff'};
      color: ${isDark ? '#e5e7eb' : '#1f2937'};
      border-radius: 16px;
      padding: 2rem 2rem 1.5rem;
      max-width: 380px; width: 90%;
      box-sizing: border-box;
    ">
      <div style="
        width: 48px; height: 48px; border-radius: 50%;
        background: #FCEBEB;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1.25rem;
      ">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
          <path d="M10 6v4m0 4h.01M10 2a8 8 0 100 16A8 8 0 0010 2z"
            stroke="#E24B4A" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
      </div>
      <p style="font-size: 17px; font-weight: 500; margin: 0 0 10px;">
        Xác nhận xóa ngấn sách
      </p>
      <p style="font-size: 14px; opacity: 0.7; margin: 0 0 1.75rem; line-height: 1.6;">
        Hành động này sẽ xóa dữ liệu vĩnh viễn. Bạn sẽ không thể khôi phục lại sau khi xác nhận.
      </p>
      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button id="btn-cancel" style="
          padding: 8px 20px; border-radius: 8px;
          border: 1px solid #d1d5db;
          background: transparent;
          color: inherit; font-size: 14px; cursor: pointer;
        ">Hủy</button>
        <button id="btn-confirm" style="
          padding: 8px 20px; border-radius: 8px;
          border: none; background: #ef4444;
          color: #fff; font-size: 14px; font-weight: 500; cursor: pointer;
        ">Xóa</button>
      </div>
    </div>
  `;

  document.body.appendChild(overlay);

  const close = () => document.body.removeChild(overlay);

  overlay.querySelector('#btn-cancel').onclick = close;
  overlay.querySelector('#btn-confirm').onclick = () => { close(); onConfirm(); };
  overlay.onclick = (e) => { if (e.target === overlay) close(); };
}

/* Dùng trong handleDelete */
function handleDelete(id) {
  showDeleteModal(async () => {
    try {
      await api('DELETE', `${API_BASE}/${id}`);
      window.showToast({ type: 'success', title: 'Thành công', message: 'Xóa ngấn sách thành công!' });
      loadWallets(currentPage);
    } catch (err) {
      window.showToast({ type: 'error', title: 'Lỗi', message: err.message ?? 'Không thể xóa.' });
    }
  });
}

/* TOGGLE STATUS */
async function handleToggle(id) {
    try {
        const data = await api('PATCH', `${API_BASE}/${id}/status`);
        window.showToast({ type: 'success', title: 'Thành công', message: data.message ?? 'Cập nhật trạng thái thành công!' });
        loadWallets(currentPage);
    } catch (err) {
        window.showToast({ type: 'error', title: 'Lỗi', message: err.message ?? 'Có lỗi xảy ra.' });
    }
}

async function handleSync(id) {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,0.45);
        display: flex; align-items: center; justify-content: center;
    `;

    const isDark = document.body.classList.contains('dark');

    overlay.innerHTML = `
        <div style="
            background: ${isDark ? '#1E2937' : '#ffffff'};
            color: ${isDark ? '#e5e7eb' : '#1f2937'};
            border-radius: 16px;
            padding: 2rem 2rem 1.5rem;
            max-width: 380px; width: 90%;
            box-sizing: border-box;
        ">
            <div style="
                width: 48px; height: 48px; border-radius: 50%;
                background: #EFF6FF;
                display: flex; align-items: center; justify-content: center;
                margin-bottom: 1.25rem;
            ">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 3a7 7 0 100 14A7 7 0 0010 3zm0 3v4m0 2h.01"
                        stroke="#4a90e2" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
            <p style="font-size: 17px; font-weight: 500; margin: 0 0 10px;">
                Đồng bộ số dư?
            </p>
            <p style="font-size: 14px; opacity: 0.7; margin: 0 0 1.75rem; line-height: 1.6;">
                Tính lại số dư dựa trên tất cả giao dịch của ví này.
            </p>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button id="btn-cancel" style="
                    padding: 8px 20px; border-radius: 8px;
                    border: 1px solid #d1d5db;
                    background: transparent;
                    color: inherit; font-size: 14px; cursor: pointer;
                ">Hủy</button>
                <button id="btn-confirm" style="
                    padding: 8px 20px; border-radius: 8px;
                    border: none; background: #4a90e2;
                    color: #fff; font-size: 14px; font-weight: 500; cursor: pointer;
                ">Đồng bộ</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);
    const close = () => document.body.removeChild(overlay);
    overlay.querySelector('#btn-cancel').onclick = close;
    overlay.onclick = (e) => { if (e.target === overlay) close(); };

    overlay.querySelector('#btn-confirm').onclick = async () => {
        close();
        try {
            const data = await api('POST', `${API_BASE}/${id}/sync`);
            window.showToast({ type: 'success', title: 'Thành công', message: data.message ?? 'Đồng bộ thành công!' });
            loadWallets(currentPage);
        } catch (err) {
            window.showToast({ type: 'error', title: 'Lỗi', message: err.message ?? 'Có lỗi xảy ra.' });
        }
    };
}

/* EDIT MODAL */
function openEditModal(w) {
    const form = document.getElementById('edit-form');
    form.dataset.id = w.id;
    form.querySelector('[name="ten_ngan_sach"]').value = w.ten_ngan_sach ?? '';
    form.querySelector('[name="mo_ta"]').value         = w.mo_ta ?? '';

    // Amount
    const display = form.querySelector('.amount-display');
    const hidden  = form.querySelector('[name="ngan_sach_goc"]');
    if (display) display.value = parseInt(w.ngan_sach_goc).toLocaleString('vi-VN');
    if (hidden)  hidden.value  = w.ngan_sach_goc;

    // Category
    renderCategoryOptions(allCategories);
    setTimeout(() => {
        form.querySelector('[name="category_id"]').value = w.category_id;
    }, 50);

    document.getElementById('edit-modal').classList.add('active');
}

/* FORM ERRORS */
function showFormErrors(errors, prefix) {
    Object.keys(errors).forEach(field => {
        const el = document.getElementById(`${prefix}-error-${field}`);
        if (el) { el.textContent = errors[field][0]; el.style.display = 'block'; }
    });
}
function clearFormErrors(prefix) {
    document.querySelectorAll(`[id^="${prefix}-error-"]`).forEach(el => {
        el.textContent = ''; el.style.display = 'none';
    });
}

/* AMOUNT INPUT */
function setupAmountInput(displayId, form) {
    const display = document.getElementById(displayId);
    if (!display) return;
    display.addEventListener('focus', () => {
        display.value = display.value.replace(/\D/g, '');
    });
    display.addEventListener('blur', () => {
        const num = parseInt(display.value.replace(/\D/g, '')) || 0;
        display.value = num ? num.toLocaleString('vi-VN') : '';
    });
    display.addEventListener('keypress', e => {
        if (!/[0-9]/.test(e.key)) e.preventDefault();
    });
}

/* INIT */
function initPage() {
    loadWallets();

    document.getElementById('btn-search').addEventListener('click', () => loadWallets(1));
    document.getElementById('btn-reset').addEventListener('click', () => {
        document.getElementById('filter-search').value   = '';
        document.getElementById('filter-category').value = '';
        document.getElementById('filter-status').value   = '';
        document.getElementById('filter-sort').value     = 'created_at';
        loadWallets(1);
    });

    document.getElementById('filter-search').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadWallets(1), 500);
    });

    document.getElementById('open-create-modal').addEventListener('click', () =>
        document.getElementById('create-modal').classList.add('active'));
    document.getElementById('empty-add-btn')?.addEventListener('click', () =>
        document.getElementById('create-modal').classList.add('active'));

    document.querySelectorAll('.modal-overlay').forEach(overlay =>
        overlay.addEventListener('click', e => {
            if (e.target === overlay) overlay.classList.remove('active');
        })
    );

    document.getElementById('create-form')?.addEventListener('submit', handleCreate);
    document.getElementById('edit-form')?.addEventListener('submit', handleUpdate);

    setupAmountInput('create-amount-display', document.getElementById('create-form'));
    setupAmountInput('edit-amount-display',   document.getElementById('edit-form'));

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
    });
}

window.openEditModal  = openEditModal;
window.handleDelete   = handleDelete;
window.handleToggle   = handleToggle;
window.handleSync     = handleSync;
window.loadWallets    = loadWallets;

initPage();

})();
</script>
@endsection