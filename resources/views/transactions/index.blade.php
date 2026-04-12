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

    <!-- Stats -->
    <div class="stats-grid" id="stats-grid" style="display:none;"></div>

    <!-- Filter -->
    <div class="filter-card">
        <div class="filter-title">
            <img src="{{ asset('images/filter.png') }}" alt="Filter">
            <span>Bộ lọc & Tìm kiếm</span>
        </div>
        <div class="filter-form">
            <div class="form-group">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" id="filter-search" class="form-control" placeholder="Nhập tên hoặc loại giao dịch...">
            </div>
            <div class="form-group">
                <label class="form-label">Danh mục</label>
                <select id="filter-danh-muc" class="form-select">
                    <option value="">Tất cả danh mục</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Loại</label>
                <select id="filter-loai" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="THU">Thu</option>
                    <option value="CHI">Chi</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Từ ngày</label>
                <input type="date" id="filter-tu-ngay" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Đến ngày</label>
                <input type="date" id="filter-den-ngay" class="form-control">
            </div>
            <div class="filter-actions">
                <button type="button" class="btn-filter btn-search" id="btn-search">
                    <img src="{{ asset('images/search.png') }}" alt="Search"> Tìm kiếm
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
                <img src="{{ asset('images/list.png') }}" alt="List"> Danh sách giao dịch
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
                            <th>Danh mục & Mô tả</th>
                            <th style="width:100px;">Loại</th>
                            <th style="width:150px;">Số tiền</th>
                            <th style="width:120px;">Ngày giao dịch</th>
                            <th style="width:100px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-tbody"></tbody>
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
            <h3>Chưa có giao dịch nào</h3>
            <p>Hãy thêm giao dịch đầu tiên để bắt đầu theo dõi thu chi</p>
            <button type="button" class="btn-primary" id="empty-add-btn">
                <img src="{{ asset('images/plus.png') }}" alt="Add"> Thêm giao dịch đầu tiên
            </button>
        </div>
    </div>

    @include('transactions._modal_create')
    @include('transactions._modal_edit')
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {

var API_BASE = '/api/v1/transactions';

let currentPage    = 1;
let searchTimeout  = null;
let allCategories  = [];
let allWallets     = [];

/* HELPERS */
function escHtml(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, c =>
        ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c])
    );
}
function formatMoney(n) {
    return parseInt(n).toLocaleString('vi-VN') + 'đ';
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
    const danhMuc  = document.getElementById('filter-danh-muc').value;
    const loai     = document.getElementById('filter-loai').value;
    const tuNgay   = document.getElementById('filter-tu-ngay').value;
    const denNgay  = document.getElementById('filter-den-ngay').value;
    if (search)   p.set('search',      search);
    if (danhMuc)  p.set('danh_muc_id', danhMuc);
    if (loai)     p.set('loai',        loai);
    if (tuNgay)   p.set('tu_ngay',     tuNgay);
    if (denNgay)  p.set('den_ngay',    denNgay);
    p.set('page', page);
    return p.toString();
}

/* LOAD */
async function loadTransactions(page = 1) {
    currentPage = page;
    document.getElementById('table-loading').style.display = 'block';
    document.getElementById('table-body').style.display    = 'none';
    document.getElementById('empty-state').style.display   = 'none';

    try {
        const data = await api('GET', `${API_BASE}?${getFilters(page)}`);

        allCategories = data.categories ?? [];
        allWallets    = data.wallets    ?? [];

        renderTable(data.transactions);
        renderStats(data.totalIncome, data.totalExpense, data.transactions.total);
        renderCategoryFilter(allCategories);
    } catch {
        window.showToast({ type: 'error', title: 'Lỗi', message: 'Không thể tải danh sách giao dịch.' });
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

    document.getElementById('transactions-tbody').innerHTML = p.data.map((t, i) => `
        <tr>
            <td>${offset + i}</td>
            <td>
                <div class="transaction-info">
                    <div class="transaction-icon ${t.loai_giao_dich === 'THU' ? 'income' : 'expense'}">
                        <img src="/images/category-icons/${escHtml(t.category?.bieu_tuong ?? 'money.png')}" alt="icon">
                    </div>
                    <div class="transaction-details">
                        <div class="transaction-category">${escHtml(t.category?.ten_danh_muc ?? '')}</div>
                        <div class="transaction-desc">${escHtml(t.ghi_chu ?? 'Không có ghi chú')}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge badge-${t.loai_giao_dich === 'THU' ? 'income' : 'expense'}">
                    ${escHtml(t.loai_giao_dich)}
                </span>
            </td>
            <td>
                <span class="amount ${t.loai_giao_dich === 'THU' ? 'income' : 'expense'}">
                    ${t.loai_giao_dich === 'THU' ? '+' : '-'}${formatMoney(t.so_tien)}
                </span>
            </td>
            <td>${formatDate(t.ngay_giao_dich)}</td>
            <td>
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit"
                        onclick='openEditModal(${JSON.stringify(t)})'
                        title="Chỉnh sửa">
                        <img src="/images/edit.png" alt="edit">
                    </button>
                    <button type="button" class="btn-action btn-delete"
                        onclick="handleDelete(${t.id})"
                        title="Xóa">
                        <img src="/images/delete.png" alt="delete">
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

    document.getElementById('pagination-info').textContent =
        `Hiển thị ${p.from ?? 0} - ${p.to ?? 0} / ${p.total} kết quả`;

    renderPagination(p);
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`;
}

function renderPagination(p) {
    const el   = document.getElementById('pagination-links');
    const cur  = p.current_page;
    const last = p.last_page;
    let html   = '<div style="display:flex;gap:6px;">';
    if (cur > 1)
        html += `<button class="btn-filter btn-reset" style="min-width:36px;padding:0 10px;height:36px;" onclick="loadTransactions(${cur-1})">‹</button>`;
    for (let i = Math.max(1, cur-2); i <= Math.min(last, cur+2); i++)
        html += `<button class="btn-filter ${i===cur?'btn-search':'btn-reset'}" style="min-width:36px;padding:0 10px;height:36px;" onclick="loadTransactions(${i})">${i}</button>`;
    if (cur < last)
        html += `<button class="btn-filter btn-reset" style="min-width:36px;padding:0 10px;height:36px;" onclick="loadTransactions(${cur+1})">›</button>`;
    html += '</div>';
    el.innerHTML = html;
}

function renderStats(income, expense, total) {
    document.getElementById('table-stats').innerHTML = `
        <span class="stat-badge income"><img src="/images/arrows.png" alt=""> Thu: ${formatMoney(income)}</span>
        <span class="stat-badge expense"><img src="/images/down.png" alt=""> Chi: ${formatMoney(expense)}</span>
        <span class="stat-badge total"><img src="/images/chart.png" alt=""> Tổng: ${total}</span>
    `;
}

function renderCategoryFilter(categories) {
    const el = document.getElementById('filter-danh-muc');
    const val = el.value;
    el.innerHTML = '<option value="">Tất cả danh mục</option>';

    const thu = categories.filter(c => c.loai_danh_muc === 'THU');
    const chi = categories.filter(c => c.loai_danh_muc === 'CHI');

    if (thu.length) {
        el.innerHTML += '<optgroup label="Thu nhập">' +
            thu.map(c => `<option value="${c.id}" ${c.id == val ? 'selected':''}>${escHtml(c.ten_danh_muc)}</option>`).join('') +
            '</optgroup>';
    }
    if (chi.length) {
        el.innerHTML += '<optgroup label="Chi tiêu">' +
            chi.map(c => `<option value="${c.id}" ${c.id == val ? 'selected':''}>${escHtml(c.ten_danh_muc)}</option>`).join('') +
            '</optgroup>';
    }
}

/* CREATE */
async function handleCreate(e) {
    e.preventDefault();
    const form = e.target;
    const amountDisplay = form.querySelector('.amount-display');
    const amountHidden  = form.querySelector('[name="so_tien"]');
    if (amountDisplay) amountHidden.value = amountDisplay.value.replace(/\D/g, '');

    const body = {
        loai_giao_dich:         form.querySelector('[name="loai_giao_dich"]').value,
        phuong_thuc_thanh_toan: form.querySelector('[name="phuong_thuc_thanh_toan"]').value,
        category_id:            form.querySelector('[name="category_id"]').value,
        so_tien:                amountHidden.value,
        ngay_giao_dich:         form.querySelector('[name="ngay_giao_dich"]').value,
        ghi_chu:                form.querySelector('[name="ghi_chu"]').value,
        money_wallet_id:        form.querySelector('[name="money_wallet_id"]').value || null,
    };

    try {
        await api('POST', API_BASE, body);
        window.showToast({ type: 'success', title: 'Thành công', message: 'Thêm giao dịch thành công!' });
        document.getElementById('create-modal').classList.remove('active');
        form.reset();
        loadTransactions(currentPage);
    } catch (err) {
        if (err.errors) showFormErrors(err.errors, 'create');
        if (err.message) showToast(err.message, 'error');
    }
}

/* UPDATE */
async function handleUpdate(e) {
    e.preventDefault();
    const form = e.target;
    const id   = form.dataset.id;
    const amountDisplay = form.querySelector('.amount-display');
    const amountHidden  = form.querySelector('[name="so_tien"]');
    if (amountDisplay) amountHidden.value = amountDisplay.value.replace(/\D/g, '');

    const body = {
        loai_giao_dich:         form.querySelector('[name="loai_giao_dich"]').value,
        phuong_thuc_thanh_toan: form.querySelector('[name="phuong_thuc_thanh_toan"]').value,
        category_id:            form.querySelector('[name="category_id"]').value,
        so_tien:                amountHidden.value,
        ngay_giao_dich:         form.querySelector('[name="ngay_giao_dich"]').value,
        ghi_chu:                form.querySelector('[name="ghi_chu"]').value,
        money_wallet_id:        form.querySelector('[name="money_wallet_id"]').value || null,
    };

    try {
        await api('PATCH', `${API_BASE}/${id}`, body);
        window.showToast({ type: 'success', title: 'Thành công', message: 'Cập nhật giao dịch thành công!' });
        document.getElementById('edit-modal').classList.remove('active');
        loadTransactions(currentPage);
        } catch (err) {
            if (err.errors) showFormErrors(err.errors, 'edit');
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
        Xác nhận xóa giao dịch
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
      window.showToast({ type: 'success', title: 'Thành công', message: 'Xóa giao dịch thành công!' });
      loadTransactions(currentPage);
    } catch (err) {
      window.showToast({ type: 'error', title: 'Lỗi', message: err.message ?? 'Không thể xóa.' });
    }
  });
}

/* EDIT MODAL */
function openEditModal(t) {
    const form = document.getElementById('edit-form');
    form.dataset.id = t.id;

    form.querySelector('[name="loai_giao_dich"]').value         = t.loai_giao_dich;
    form.querySelector('[name="phuong_thuc_thanh_toan"]').value = t.phuong_thuc_thanh_toan;
    form.querySelector('[name="ngay_giao_dich"]').value         = t.ngay_giao_dich;
    form.querySelector('[name="ghi_chu"]').value                = t.ghi_chu ?? '';
    form.querySelector('[name="money_wallet_id"]').value        = t.money_wallet_id ?? '';

    // Set amount
    const amountDisplay = form.querySelector('.amount-display');
    const amountHidden  = form.querySelector('[name="so_tien"]');
    if (amountDisplay) {
        amountDisplay.value = parseInt(t.so_tien).toLocaleString('vi-VN');
        amountHidden.value  = t.so_tien;
    }

    // Filter categories by type then set value
    filterCategoriesByType('edit');
    setTimeout(() => {
        form.querySelector('[name="category_id"]').value = t.category_id;
    }, 50);

    // Render wallet options
    renderWalletOptions('edit-wallet', t.money_wallet_id);

    document.getElementById('edit-modal').classList.add('active');
}

function renderWalletOptions(selectId, selectedId = null) {
    const el = document.getElementById(selectId);
    if (!el) return;
    el.innerHTML = '<option value="">-- Không chọn ví (tùy chọn) --</option>' +
        allWallets.map(w => `<option value="${w.id}" ${w.id == selectedId ? 'selected' : ''}>${escHtml(w.ten_ngan_sach ?? w.ten_vi ?? '')}</option>`).join('');
}

function filterCategoriesByType(prefix = 'create') {
    const loaiEl    = document.getElementById(`${prefix}-loai-giao-dich`);
    const catEl     = document.getElementById(`${prefix}-category`);
    if (!loaiEl || !catEl) return;

    const loai = loaiEl.value;
    catEl.innerHTML = '<option value="">-- Chọn danh mục --</option>';

    if (loai) {
        allCategories.filter(c => c.loai_danh_muc === loai).forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.ten_danh_muc;
            catEl.appendChild(opt);
        });
    }
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

/* CURRENCY INPUT */
function setupAmountInput(displayId, hiddenName, form) {
    const display = document.getElementById(displayId);
    if (!display) return;

    display.addEventListener('focus', () => {
        display.value = display.value.replace(/\D/g, '');
    });
    display.addEventListener('blur', () => {
        const num = parseInt(display.value.replace(/\D/g, '')) || 0;
        const hidden = form.querySelector(`[name="${hiddenName}"]`);
        if (hidden) hidden.value = num;
        display.value = num ? num.toLocaleString('vi-VN') : '';
    });
    display.addEventListener('keypress', e => {
        if (!/[0-9]/.test(e.key)) e.preventDefault();
    });
}

/* INIT */
function initPage() {
    loadTransactions();

    document.getElementById('btn-search').addEventListener('click', () => loadTransactions(1));
    document.getElementById('btn-reset').addEventListener('click', () => {
        document.getElementById('filter-search').value    = '';
        document.getElementById('filter-danh-muc').value = '';
        document.getElementById('filter-loai').value     = '';
        document.getElementById('filter-tu-ngay').value  = '';
        document.getElementById('filter-den-ngay').value = '';
        loadTransactions(1);
    });

    document.getElementById('filter-search').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadTransactions(1), 500);
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

    // Category filter by type
    document.getElementById('create-loai-giao-dich')?.addEventListener('change', () => filterCategoriesByType('create'));
    document.getElementById('edit-loai-giao-dich')?.addEventListener('change', () => filterCategoriesByType('edit'));

    // Amount inputs
    setupAmountInput('create-amount-display', 'so_tien', document.getElementById('create-form'));
    setupAmountInput('edit-amount-display',   'so_tien', document.getElementById('edit-form'));

    // Set default date
    document.getElementById('create-ngay-giao-dich').value = new Date().toISOString().split('T')[0];

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
    });
}

window.openEditModal   = openEditModal;
window.handleDelete    = handleDelete;
window.loadTransactions = loadTransactions;

initPage();

})();
</script>
@endsection