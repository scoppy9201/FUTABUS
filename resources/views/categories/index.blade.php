@extends('layouts.app')
@section('title', 'Quản lý danh mục')
@section('content')
<style>
    :root {
        /* Màu chính */
        --primary: #4a90e2;
        --primary-dark: #2a5298;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #06b6d4;
        
        /* Dark mode colors */
        --dark-bg: #1a1f29;
        --dark-card: #242936;
        --dark-border: rgba(255, 255, 255, 0.08);
        
        /* Gray scale */
        --gray-100: #f8fafc;
        --gray-200: #e2e8f0;
        --gray-300: #cbd5e0;
        --gray-600: #4a5568;
        --gray-800: #2d3748;
        --gray-900: #1a202c;
        
        /* Border radius */
        --radius: 12px;
        --radius-sm: 10px;
        
        /* Shadow */
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
    body.dark .form-select,
    body.dark .form-textarea {
        background: var(--dark-bg);
        border-color: var(--dark-border);
        color: #e5e7eb;
    }

    body.dark .form-control:focus,
    body.dark .form-select:focus,
    body.dark .form-textarea:focus {
        background: var(--dark-card);
        border-color: var(--primary);
    }

    body.dark .form-label {
        color: #9ca3af;
    }

    body.dark tbody tr:hover {
        background: rgba(255, 255, 255, 0.03);
    }

    body.dark .empty-state h3 {
        color: #e5e7eb;
    }

    body.dark .empty-state p {
        color: #9ca3af;
    }

    body.dark .pagination a {
        background: var(--dark-bg);
        color: #e5e7eb;
    }

    body.dark .pagination a:hover {
        background: var(--dark-card);
    }

    body.dark .btn-reset {
        background: var(--dark-bg);
        color: #e5e7eb;
    }

    body.dark .btn-reset:hover {
        background: var(--dark-card);
    }

    body.dark .remove-upload-compact {
        background: var(--dark-card);
        border-color: var(--dark-border);
    }

    body.dark .remove-upload-compact:hover {
        background: var(--dark-bg);
        border-color: var(--danger);
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
        grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr auto;
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

    .category-name {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .category-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
    }

    .category-icon img {
        width: 100%;
    }

    .category-icon.income {
        background: #d1fae5;
    }

    .category-icon.expense {
        background: #fee2e2;
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

    .badge-active {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-active,
    .badge-inactive {
        white-space: nowrap;
        min-width: fit-content;
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

    .btn-toggle:hover img {
        filter: brightness(0) invert(1);
    }

    .btn-edit {
        background: #dbeafe;
        border: 2px solid var(--info);
    }

    .btn-edit:hover {
        background: var(--info);
    }

    .btn-edit:hover img {
        filter: brightness(0) invert(1);
    }

    .btn-delete {
        background: #fee2e2;
        border: 2px solid var(--danger);
    }

    .btn-delete:hover {
        background: var(--danger);
    }

    .btn-delete:hover img {
        filter: brightness(0) invert(1);
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
        max-width: 900px;
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
        filter: brightness(0) invert(1);
    }

    .modal-body {
        padding: 0;
        display: flex;
        flex: 1;
        overflow: hidden;
        min-height: 0;
    }

    .modal-left {
        flex: 1.3;
        padding: 28px 32px;
        overflow-y: auto;
        background: white;
    }

    .modal-right {
        flex: 0.9;
        padding: 28px 24px;
        background: #f8f9fa;
        border-left: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        gap: 20px;
        overflow-y: auto;
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

    .form-group-compact .form-control,
    .form-group-compact .form-select,
    .form-group-compact .form-textarea {
        width: 100%;
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

    .radio-group-compact {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 8px;
    }

    .radio-item {
        position: relative;
    }

    .radio-input {
        display: none;
    }

    .radio-label-compact {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 14px 16px;
        border: 2px solid #e5e7eb;
        border-radius: var(--radius-sm);
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        font-size: 14px;
        color: #6b7280;
    }

    .radio-label-compact img {
        width: 28px;
        height: 28px;
        opacity: 0.5;
        transition: opacity 0.2s ease;
    }

    .radio-input:checked + .radio-label-compact {
        border-color: var(--primary);
        background: #eff6ff;
        color: var(--primary);
    }

    .radio-input:checked + .radio-label-compact img {
        opacity: 1;
    }

    .radio-label-compact:hover {
        border-color: var(--primary);
    }

    .upload-section {
        background: white;
        border-radius: var(--radius);
        padding: 24px;
        border: 2px dashed #cbd5e0;
        text-align: center;
    }

    .upload-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .upload-section-title img {
        width: 20px;
    }

    .icon-select-btn {
        width: 100%;
        padding: 16px;
        background: #f8f9fa;
        border: 2px dashed #cbd5e0;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .icon-select-btn:hover {
        border-color: var(--primary);
        background: #eff6ff;
    }

    .icon-select-preview {
        width: 48px;
        height: 48px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
    }

    .icon-select-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .icon-select-text {
        flex: 1;
        text-align: left;
    }

    .icon-select-name {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .icon-select-hint {
        font-size: 12px;
        color: #6b7280;
    }

    .icon-select-arrow {
        transition: transform 0.2s;
    }

    .icon-select-btn:hover .icon-select-arrow {
        transform: translateX(2px);
    }

    .icon-picker-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .icon-picker-modal.active {
        display: flex;
    }

    .icon-picker-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
    }

    .icon-picker-content {
        position: relative;
        background: white;
        border-radius: var(--radius);
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .icon-picker-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .icon-picker-header-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .icon-picker-close {
        width: 32px;
        height: 32px;
        background: #f3f4f6;
        border: none;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }

    .icon-picker-close:hover {
        background: #e5e7eb;
    }

    .icon-picker-body {
        padding: 20px 24px;
        overflow-y: auto;
        flex: 1;
    }

    .icon-search {
        position: relative;
        margin-bottom: 16px;
    }

    .icon-search input {
        width: 100%;
        padding: 10px 12px 10px 38px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
    }

    .icon-search input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .icon-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        opacity: 0.5;
    }

    .icon-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
        max-height: 360px;
        overflow-y: auto;
        padding: 4px;
    }

    .icon-grid::-webkit-scrollbar {
        width: 6px;
    }

    .icon-grid::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 10px;
    }

    .icon-grid::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 10px;
    }

    .icon-item {
        position: relative;
        aspect-ratio: 1;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        background: #f9fafb;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
    }

    .icon-item:hover {
        border-color: var(--primary);
        background: #eff6ff;
        transform: scale(1.05);
    }

    .icon-item.selected {
        border-color: var(--primary);
        background: linear-gradient(135deg, rgba(74, 144, 226, 0.15), rgba(42, 82, 152, 0.15));
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
    }

    .icon-item img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .icon-item:hover img,
    .icon-item.selected img {
        opacity: 1;
    }

    .icon-item-check {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 20px;
        height: 20px;
        background: var(--success);
        border: 2px solid white;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .icon-item.selected .icon-item-check {
        display: flex;
    }

    .icon-item-check img {
        width: 10px;
        filter: brightness(0) invert(1);
    }

    .icon-picker-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
    }

    .icon-picker-footer .btn-primary,
    .icon-picker-footer .btn-secondary {
        flex: 1;
        justify-content: center;
        padding: 12px;
    }
    .preview-card {
        background: white;
        border-radius: var(--radius);
        padding: 20px;
        border: 1px solid #e5e7eb;
    }

    .preview-title {
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .preview-title img {
        width: 18px;
    }

    .category-preview {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: #f8f9fa;
        border-radius: var(--radius-sm);
        border: 1px solid #e5e7eb;
    }

    .category-preview-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }

    .category-preview-icon img {
        width: 28px;
    }

    .category-preview-text {
        flex: 1;
    }

    .category-preview-name {
        font-weight: 700;
        font-size: 15px;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .category-preview-type {
        font-size: 12px;
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

    .modal-actions-fixed .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
    }

    .modal-actions-fixed .btn-primary:hover {
        opacity: 0.9;
    }

    .modal-actions-fixed .btn-secondary {
        background: #f3f4f6;
        border: 2px solid #e5e7eb;
        color: #4b5563;
    }

    .modal-actions-fixed .btn-secondary:hover {
        background: #e5e7eb;
    }

    .table-wrapper::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }

    .table-wrapper::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: var(--radius-sm);
    }

    .table-wrapper::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: var(--radius-sm);
    }

    .table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    .modal-left::-webkit-scrollbar,
    .modal-right::-webkit-scrollbar {
        width: 6px;
    }

    .modal-left::-webkit-scrollbar-track,
    .modal-right::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-left::-webkit-scrollbar-thumb,
    .modal-right::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: var(--radius-sm);
    }

    .modal-left::-webkit-scrollbar-thumb:hover,
    .modal-right::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    body.dark .modal-content {
        background: var(--dark-card);
    }

    body.dark .modal-left {
        background: var(--dark-card);
    }

    body.dark .modal-right {
        background: var(--dark-bg);
        border-left-color: var(--dark-border);
    }

    body.dark .upload-section {
        background: var(--dark-bg);
        border-color: var(--dark-border);
    }

    body.dark .upload-area-compact {
        background: var(--dark-card);
    }

    body.dark .upload-section-title,
    body.dark .upload-text-compact {
        color: #e5e7eb;
    }

    body.dark .upload-hint-compact {
        color: #9ca3af;
    }

    body.dark .radio-label-compact {
        background: var(--dark-bg);
        border-color: var(--dark-border);
        color: #9ca3af;
    }

    body.dark .radio-input:checked + .radio-label-compact {
        background: rgba(74, 144, 226, 0.15);
        border-color: var(--primary);
        color: var(--primary);
    }

    body.dark .preview-card {
        background: var(--dark-bg);
        border-color: var(--dark-border);
    }

    body.dark .preview-title {
        color: #e5e7eb;
    }

    body.dark .category-preview {
        background: var(--dark-card);
        border-color: var(--dark-border);
    }

    body.dark .category-preview-name {
        color: #e5e7eb;
    }

    body.dark .modal-actions-fixed {
        background: var(--dark-card);
        border-top-color: var(--dark-border);
    }

    body.dark .modal-actions-fixed .btn-secondary {
        background: var(--dark-bg);
        border-color: var(--dark-border);
        color: #9ca3af;
    }

    body.dark .modal-actions-fixed .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    body.dark .upload-preview-compact img {
        border-color: var(--dark-border);
        background: var(--dark-card);
    }

    body.dark .form-group-compact .form-label {
        color: #9ca3af;
    }

    body.dark .form-group-compact .form-label strong {
        color: #e5e7eb;
    }

    body.dark .icon-select-btn {
        background: var(--dark-card);
        border-color: var(--dark-border);
    }

    body.dark .icon-select-btn:hover {
        background: rgba(74, 144, 226, 0.1);
    }

    body.dark .icon-select-preview {
        background: var(--dark-bg);
        border-color: var(--dark-border);
    }

    body.dark .icon-select-name {
        color: #e5e7eb;
    }

    body.dark .icon-select-hint {
        color: #9ca3af;
    }

    body.dark .icon-picker-content {
        background: var(--dark-card);
    }

    body.dark .icon-picker-header {
        border-bottom-color: var(--dark-border);
    }

    body.dark .icon-picker-header-title {
        color: #e5e7eb;
    }

    body.dark .icon-picker-close {
        background: var(--dark-bg);
    }

    body.dark .icon-picker-close:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    body.dark .icon-search input {
        background: var(--dark-bg);
        border-color: var(--dark-border);
        color: #e5e7eb;
    }

    body.dark .icon-item {
        background: var(--dark-bg);
        border-color: var(--dark-border);
    }

    body.dark .icon-item:hover {
        background: rgba(74, 144, 226, 0.15);
    }

    body.dark .icon-picker-footer {
        border-top-color: var(--dark-border);
    }

    @media (max-width: 1024px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .filter-actions {
            grid-column: 1 / -1;
            width: 100%;
        }

        .table-wrapper {
            overflow-x: scroll;
        }

        table {
            min-width: 900px;
        }

        .pagination-wrapper {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 768px) {
        /* Modal Create/Edit */
        .modal-body {
            flex-direction: column;
        }

        .modal-left,
        .modal-right {
            flex: 1;
            padding: 24px 28px;
        }

        .modal-right {
            border-left: none;
            border-top: 1px solid #e5e7eb;
        }

        body.dark .modal-right {
            border-top-color: var(--dark-border);
        }

        .modal-content {
            max-width: 96%;
            max-height: 92vh;
        }

        .modal-header {
            padding: 20px 24px;
        }

        .modal-title {
            font-size: 18px;
        }

        .modal-actions-fixed {
            padding: 18px 24px;
            flex-direction: column-reverse;
        }

        .modal-actions-fixed .btn-primary,
        .modal-actions-fixed .btn-secondary {
            width: 100%;
        }

        .radio-group-compact {
            grid-template-columns: 1fr;
        }

        /* Icon Picker Modal */
        .icon-grid {
            grid-template-columns: repeat(5, 1fr);
        }
        
        .icon-picker-content {
            max-width: 96%;
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

    @media (max-width: 480px) {
        /* Modal Create/Edit */
        .modal-content {
            max-width: 98%;
        }

        .modal-left,
        .modal-right {
            padding: 20px;
        }

        .modal-header {
            padding: 18px 20px;
        }

        .upload-section {
            padding: 20px;
        }

        .preview-card {
            padding: 16px;
        }

        /* Icon Picker Modal */
        .icon-grid {
            grid-template-columns: repeat(4, 1fr);
        }
        
        .icon-picker-header {
            padding: 16px 20px;
        }
        
        .icon-picker-body {
            padding: 16px 20px;
        }
        
        .icon-picker-footer {
            padding: 14px 20px;
            flex-direction: column-reverse;
        }
        
        .icon-picker-footer .btn-primary,
        .icon-picker-footer .btn-secondary {
            width: 100%;
        }
    }
</style>

<div class="category-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <div class="page-icon">
                <img src="{{ asset('images/category.png') }}" alt="Folder">
            </div>
            <span>Quản lý danh mục</span>
        </div>
        <button type="button" class="btn-primary" id="open-create-modal">
            <img src="{{ asset('images/plus.png') }}" alt="Add">
            Thêm danh mục
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
        <form action="{{ route('categories.index') }}" method="GET" class="filter-form">
            <div class="form-group">
                <label class="form-label">Tìm kiếm</label>
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Nhập tên danh mục..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="form-group">
                <label class="form-label">Loại danh mục</label>
                <select name="loai" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="THU" {{ request('loai') == 'THU' ? 'selected' : '' }}>Thu</option>
                    <option value="CHI" {{ request('loai') == 'CHI' ? 'selected' : '' }}>Chi</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Trạng thái</label>
                <select name="trang_thai" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="1" {{ request('trang_thai') === '1' ? 'selected' : '' }}>Kích hoạt</option>
                    <option value="0" {{ request('trang_thai') === '0' ? 'selected' : '' }}>Vô hiệu hóa</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Sắp xếp</label>
                <select name="sort_by" class="form-control">
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                    <option value="ten_danh_muc" {{ request('sort_by') == 'ten_danh_muc' ? 'selected' : '' }}>Tên danh mục</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Thứ tự</label>
                <select name="sort_order" class="form-control">
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Giảm dần</option>
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Tăng dần</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter btn-search">
                    <img src="{{ asset('images/search.png') }}" alt="Search">
                    Tìm kiếm
                </button>
                <a href="{{ route('categories.index') }}" class="btn-filter btn-reset">
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
                Danh sách danh mục
            </h3>
            <div class="table-stats">
                <span class="stat-badge income">
                    <img src="{{ asset('images/arrows.png') }}" alt="Income">
                    Thu: {{ $categories->where('loai_danh_muc', 'THU')->count() }}
                </span>
                <span class="stat-badge expense">
                    <img src="{{ asset('images/down.png') }}" alt="Expense">
                    Chi: {{ $categories->where('loai_danh_muc', 'CHI')->count() }}
                </span>
                <span class="stat-badge total">
                    <img src="{{ asset('images/chart.png') }}" alt="Total">
                    Tổng: {{ $categories->total() }}
                </span>
            </div>
        </div>

        @if($categories->count() > 0)
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Tên danh mục</th>
                        <th style="width: 100px;">Loại</th>
                        <th>Danh mục cha</th>
                        <th>Mô tả</th>
                        <th style="width: 140px;">Trạng thái</th>
                        <th style="width: 130px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $index => $category)
                    <tr>
                        <td>{{ $categories->firstItem() + $index }}</td>
                        <td>
                            <div class="category-name">
                                <div class="category-icon {{ $category->loai_danh_muc == 'THU' ? 'income' : 'expense' }}">
                                    <img src="{{ asset('images/category-icons/' . ($category->bieu_tuong ?? 'money.png')) }}" alt="Category">
                                </div>
                                <strong>{{ $category->ten_danh_muc }}</strong>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $category->loai_danh_muc == 'THU' ? 'income' : 'expense' }}">
                                {{ $category->loai_danh_muc }}
                            </span>
                        </td>
                        <td>
                            {{ $category->parent ? $category->parent->ten_danh_muc : '---' }}
                        </td>
                        <td>
                            {{ $category->mo_ta ? Str::limit($category->mo_ta, 50) : 'Không có mô tả' }}
                        </td>
                        <td>
                            <span class="badge badge-{{ $category->trang_thai ? 'active' : 'inactive' }}">
                                <span class="status-dot {{ $category->trang_thai ? 'active' : 'inactive' }}"></span>
                                {{ $category->trang_thai ? 'Hoạt động' : 'Vô hiệu hóa' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <form action="{{ route('categories.toggle-status', $category) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-toggle" title="{{ $category->trang_thai ? 'Vô hiệu hóa' : 'Kích hoạt' }}">
                                        <img src="{{ asset('images/' . ($category->trang_thai ? 'lock' : 'unlock') . '.png') }}" alt="Toggle">
                                    </button>
                                </form>

                                <button type="button" class="btn-action btn-edit" onclick="openEditModal({{ $category }})" title="Chỉnh sửa">
                                    <img src="{{ asset('images/edit.png') }}" alt="Edit">
                                </button>

                                <form action="{{ route('categories.destroy', $category) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?')">
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
                Hiển thị {{ $categories->firstItem() }} - {{ $categories->lastItem() }} / {{ $categories->total() }} kết quả
            </div>
            <div>
                {{ $categories->links() }}
            </div>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-icon">
                <img src="{{ asset('images/empty-folder.png') }}" alt="Empty">
            </div>
            <h3>Chưa có danh mục nào</h3>
            <p>Hãy tạo danh mục đầu tiên để bắt đầu quản lý thu chi</p>
            <button type="button" class="btn-primary" onclick="document.getElementById('create-modal').classList.add('active')">
                <img src="{{ asset('images/plus.png') }}" alt="Add">
                Thêm danh mục đầu tiên
            </button>
        </div>
        @endif
    </div>

    <!-- Modal Thêm Mới - Modern 2 Column Layout -->
    <div class="modal-overlay" id="create-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <div class="page-icon">
                        <img src="{{ asset('images/add-category.png') }}" style="width:24px">
                    </div>
                    Thêm danh mục mới
                </div>
                <div class="modal-close" onclick="closeModal('create-modal')">
                    <img src="{{ asset('images/close.png') }}" style="width:16px">
                </div>
            </div>

            <form action="{{ route('categories.store') }}" method="POST" id="create-form" enctype="multipart/form-data">
                @csrf
                
                <div class="modal-body">
                    <!-- Left side: Form Fields -->
                    <div class="modal-left">
                        <!-- Tên danh mục -->
                        <div class="form-group-compact">
                            <label class="form-label">
                                <strong>Tên danh mục</strong> <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="ten_danh_muc" 
                                class="form-control @error('ten_danh_muc') is-invalid @enderror" 
                                placeholder="Ví dụ: Lương tháng, Ăn uống, Du lịch..."
                                value="{{ old('ten_danh_muc') }}"
                                id="category-name-input"
                                required
                            >
                            @error('ten_danh_muc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group-compact">
                            <label class="form-label">
                                <strong>Loại danh mục</strong> <span class="required">*</span>
                            </label>
                            <div class="radio-group-compact">
                                <div class="radio-item">
                                    <input type="radio" id="thu-create" name="loai_danh_muc" value="THU" class="radio-input"
                                        {{ old('loai_danh_muc', 'THU') == 'THU' ? 'checked' : '' }}>
                                    <label for="thu-create" class="radio-label-compact">
                                        <img src="{{ asset('images/icome.png') }}" alt="Thu nhập">
                                        Thu nhập
                                    </label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="chi-create" name="loai_danh_muc" value="CHI" class="radio-input"
                                        {{ old('loai_danh_muc') == 'CHI' ? 'checked' : '' }}>
                                    <label for="chi-create" class="radio-label-compact">
                                        <img src="{{ asset('images/expense.png') }}" alt="Chi tiêu">
                                        Chi tiêu
                                    </label>
                                </div>
                            </div>
                            @error('loai_danh_muc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Danh mục cha -->
                        <div class="form-group-compact">
                            <label class="form-label"><strong>Danh mục cha</strong></label>
                            <select name="danh_muc_cha_id" class="form-select">
                                <option value="">-- Không chọn danh mục cha --</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('danh_muc_cha_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->ten_danh_muc }} ({{ $parent->loai_danh_muc == 'THU' ? 'Thu nhập' : 'Chi tiêu' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-help-compact">Dùng để tạo danh mục con</div>
                        </div>

                        <!-- Mô tả -->
                        <div class="form-group-compact">
                            <label class="form-label"><strong>Mô tả</strong></label>
                            <textarea name="mo_ta" class="form-textarea" placeholder="Ghi chú thêm về danh mục này..." style="min-height: 90px;">{{ old('mo_ta') }}</textarea>
                        </div>
                    </div>

                    <!-- Right side: Upload & Preview -->
                    <div class="modal-right">
                        <!-- Upload Section -->
                        <div class="upload-section">
                            <div class="upload-section-title">
                                <img src="{{ asset('images/image.png') }}" alt="Icon">
                                Biểu tượng danh mục
                            </div>
                            <input type="hidden" name="bieu_tuong" id="selected-icon-input" value="money.png">
                            <button type="button" class="icon-select-btn" onclick="openIconPicker()">
                                <div class="icon-select-preview">
                                    <img src="{{ asset('images/category-icons/money.png') }}" alt="Icon" id="current-icon-preview">
                                </div>
                                <div class="icon-select-text">
                                    <div class="icon-select-name" id="current-icon-name">Tiền mặt</div>
                                    <div class="icon-select-hint">Nhấp để thay đổi biểu tượng</div>
                                </div>
                                <img src="{{ asset('images/edit.png') }}" 
                                    alt="Change" 
                                    class="icon-select-arrow"
                                    style="width: 16px; opacity: 0.5;">
                            </button>
                        </div>

                        <!-- Live Preview -->
                        <div class="preview-card">
                            <div class="preview-title">
                                <img src="{{ asset('images/eye.png') }}" alt="Preview">
                                Xem trước
                            </div>

                            <div class="category-preview">
                                <div class="category-preview-icon" id="preview-icon">
                                    <img src="{{ asset('images/category-icons/money.png') }}" alt="Icon" id="preview-icon-img">
                                </div>
                                <div class="category-preview-text">
                                    <div class="category-preview-name" id="preview-name">Tên danh mục</div>
                                    <div class="category-preview-type">
                                        <span class="badge badge-income" id="preview-badge">THU NHẬP</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions-fixed">
                    <button type="button" class="btn-secondary" onclick="closeModal('create-modal')">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="btn-primary">
                        Lưu danh mục
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
                    Chỉnh sửa danh mục
                </div>
                <div class="modal-close" onclick="closeModal('edit-modal')">
                    <img src="{{ asset('images/close.png') }}" style="width:16px">
                </div>
            </div>

            <form id="edit-form" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="modal-body">
                    <!-- Left side: Form Fields -->
                    <div class="modal-left">
                        <!-- Tên danh mục -->
                        <div class="form-group-compact">
                            <label class="form-label">
                                <strong>Tên danh mục</strong> <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="ten_danh_muc" 
                                id="edit-ten"
                                class="form-control" 
                                placeholder="Ví dụ: Lương tháng, Ăn uống, Du lịch..."
                                required
                            >
                        </div>

                        <!-- Loại danh mục -->
                        <div class="form-group-compact">
                            <label class="form-label">
                                <strong>Loại danh mục</strong> <span class="required">*</span>
                            </label>
                            <div class="radio-group-compact">
                                <div class="radio-item">
                                    <input type="radio" id="thu-edit" name="loai_danh_muc" value="THU" class="radio-input">
                                    <label for="thu-edit" class="radio-label-compact">
                                        <img src="{{ asset('images/icome.png') }}" alt="Thu nhập">
                                        Thu nhập
                                    </label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="chi-edit" name="loai_danh_muc" value="CHI" class="radio-input">
                                    <label for="chi-edit" class="radio-label-compact">
                                        <img src="{{ asset('images/expense.png') }}" alt="Chi tiêu">
                                        Chi tiêu
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Danh mục cha -->
                        <div class="form-group-compact">
                            <label class="form-label"><strong>Danh mục cha</strong></label>
                            <select name="danh_muc_cha_id" id="edit-parent" class="form-select">
                                <option value="">-- Không chọn danh mục cha --</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}">
                                        {{ $parent->ten_danh_muc }} ({{ $parent->loai_danh_muc == 'THU' ? 'Thu nhập' : 'Chi tiêu' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-help-compact">Dùng để tạo danh mục con</div>
                        </div>

                        <!-- Mô tả -->
                        <div class="form-group-compact">
                            <label class="form-label"><strong>Mô tả</strong></label>
                            <textarea name="mo_ta" id="edit-mota" class="form-textarea" placeholder="Ghi chú thêm về danh mục này..." style="min-height: 90px;"></textarea>
                        </div>
                    </div>

                    <!-- Right side: Upload & Preview -->
                    <div class="modal-right">
                        <!-- Upload Section -->
                        <div class="upload-section">
                            <div class="upload-section-title">
                                <img src="{{ asset('images/image.png') }}" alt="Icon">
                                Biểu tượng danh mục
                            </div>
                            <input type="hidden" name="bieu_tuong" id="edit-selected-icon-input" value="">
                            <button type="button" class="icon-select-btn" onclick="openEditIconPicker()">
                                <div class="icon-select-preview">
                                    <img src="{{ asset('images/category-icons/money.png') }}" alt="Icon" id="edit-current-icon-preview">
                                </div>
                                <div class="icon-select-text">
                                    <div class="icon-select-name" id="edit-current-icon-name">Tiền mặt</div>
                                    <div class="icon-select-hint">Nhấp để thay đổi biểu tượng</div>
                                </div>
                                <img src="{{ asset('images/edit.png') }}" 
                                    alt="Change" 
                                    class="icon-select-arrow"
                                    style="width: 16px; opacity: 0.5;">
                            </button>
                        </div>

                        <!-- Live Preview -->
                        <div class="preview-card">
                            <div class="preview-title">
                                <img src="{{ asset('images/eye.png') }}" alt="Preview">
                                Xem trước
                            </div>

                            <div class="category-preview">
                                <div class="category-preview-icon" id="edit-preview-icon">
                                    <img src="{{ asset('images/category-icons/money.png') }}" alt="Icon" id="edit-preview-icon-img">
                                </div>
                                <div class="category-preview-text">
                                    <div class="category-preview-name" id="edit-preview-name">Tên danh mục</div>
                                    <div class="category-preview-type">
                                        <span class="badge badge-income" id="edit-preview-badge">THU NHẬP</span>
                                    </div>
                                </div>
                            </div>
                        </div>
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

    <!-- Icon Picker Modal cho Edit -->
    <div class="icon-picker-modal" id="edit-icon-picker-modal">
        <div class="icon-picker-overlay" onclick="closeEditIconPicker()"></div>
        <div class="icon-picker-content">
            <div class="icon-picker-header">
                <div class="icon-picker-header-title">
                    <img src="{{ asset('images/image.png') }}" alt="Icon" style="width: 20px;">
                    Chọn biểu tượng
                </div>
                <button type="button" class="icon-picker-close" onclick="closeEditIconPicker()">
                    <img src="{{ asset('images/close.png') }}" alt="Close" style="width: 16px;">
                </button>
            </div>

            <div class="icon-picker-body">
                <div class="icon-search">
                    <img src="{{ asset('images/search.png') }}" class="icon-search-icon" alt="Search">
                    <input 
                        type="text" 
                        id="edit-icon-search-input" 
                        placeholder="Tìm kiếm biểu tượng đẹp cho danh mục..."
                        autocomplete="off"
                    >
                </div>
                <div class="icon-grid" id="edit-icon-grid"></div>
            </div>
            <div class="icon-picker-footer">
                <button type="button" class="btn-secondary" onclick="closeEditIconPicker()">
                    Hủy bỏ
                </button>
                <button type="button" class="btn-primary" onclick="confirmEditIconSelection()">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>

    <!-- Icon Picker Modal -->
    <div class="icon-picker-modal" id="icon-picker-modal">
        <div class="icon-picker-overlay" onclick="closeIconPicker()"></div>
        <div class="icon-picker-content">
            <div class="icon-picker-header">
                <div class="icon-picker-header-title">
                    <img src="{{ asset('images/image.png') }}" alt="Icon" style="width: 20px;">
                    Chọn biểu tượng
                </div>
                <button type="button" class="icon-picker-close" onclick="closeIconPicker()">
                    <img src="{{ asset('images/close.png') }}" alt="Close" style="width: 16px;">
                </button>
            </div>

            <div class="icon-picker-body">
                <div class="icon-search">
                    <img src="{{ asset('images/search.png') }}" class="icon-search-icon" alt="Search">
                    <input 
                        type="text" 
                        id="icon-search-input" 
                        placeholder="Tìm kiếm biểu tượng đẹp cho danh mục..."
                        autocomplete="off"
                    >
                </div>
                <div class="icon-grid" id="icon-grid"></div>
            </div>
            <div class="icon-picker-footer">
                <button type="button" class="btn-secondary" onclick="closeIconPicker()">
                    Hủy bỏ
                </button>
                <button type="button" class="btn-primary" onclick="confirmIconSelection()">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const CATEGORY_ICONS = [
    { file: 'money.png', name: 'Tiền mặt', tags: 'tien mat cash money' },
    { file: 'salary.png', name: 'Lương', tags: 'luong salary wage income' },
    { file: 'gift.png', name: 'Quà tặng', tags: 'qua tang gift present' },
    { file: 'investment.png', name: 'Đầu tư', tags: 'dau tu investment stock' },
    { file: 'food.png', name: 'Ăn uống', tags: 'an uong food eat drink' },
    { file: 'shopping.png', name: 'Mua sắm', tags: 'mua sam shopping shop cart' },
    { file: 'transport.png', name: 'Di chuyển', tags: 'di chuyen transport car bus' },
    { file: 'house.png', name: 'Nhà cửa', tags: 'nha cua house home rent' },
    { file: 'health.png', name: 'Sức khỏe', tags: 'suc khoe health medical hospital' },
    { file: 'education.png', name: 'Giáo dục', tags: 'giao duc education school book' },
    { file: 'entertainment.png', name: 'Giải trí', tags: 'giai tri entertainment fun game' },
    { file: 'travel.png', name: 'Du lịch', tags: 'du lich travel vacation trip' },
    { file: 'bills.png', name: 'Hóa đơn', tags: 'hoa don bills utility payment' },
    { file: 'phone.png', name: 'Điện thoại', tags: 'dien thoai phone mobile' },
    { file: 'internet.png', name: 'Internet', tags: 'internet wifi network' },
    { file: 'insurance.png', name: 'Bảo hiểm', tags: 'bao hiem insurance protection' },
    { file: 'sports.png', name: 'Thể thao', tags: 'the thao sport fitness gym' },
    { file: 'beauty.png', name: 'Làm đẹp', tags: 'lam dep beauty cosmetic spa' },
    { file: 'pet.png', name: 'Thú cưng', tags: 'thu cung pet dog cat animal' },
    { file: 'other.png', name: 'Khác', tags: 'khac other misc' }
];

// Biến oàn cục
let searchTimeout;
let tempSelectedIcon = null;
let editTempSelectedIcon = null;

// Lấy tên icon từ file 
const getIconName = file => CATEGORY_ICONS.find(i => i.file === file)?.name || 'Tiền mặt';

// Đóng modal (hàm dùng chung) 
const closeModal = modalId => document.getElementById(modalId)?.classList.remove('active');

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => alert.remove(), 300);
    });
}, 5000);

const searchInput = document.querySelector('input[name="search"]');
const searchForm = searchInput?.closest('form'); // Lấy form chứa input
let searchTimeout;

if (searchInput && searchForm) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchValue = this.value.trim();
        
        searchTimeout = setTimeout(() => {
            // Submit form để search trong database
            searchForm.submit();
        }, 500); 
    });
}

// Hiển thị không có kết quả 
function showNoResultMessage(count) {
    const tbody = document.querySelector('tbody');
    let noResultRow = tbody.querySelector('.no-result-row');
    const pagination = document.querySelector('.pagination-wrapper');
    
    if (count === 0) {
        if (!noResultRow) {
            noResultRow = document.createElement('tr');
            noResultRow.className = 'no-result-row';
            noResultRow.innerHTML = `
                <td colspan="7">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <img src="{{ asset('images/empty-folder.png') }}" alt="Empty">
                        </div>
                        <h3>Không tìm thấy kết quả</h3>
                        <p>Thử thay đổi từ khóa tìm kiếm</p>
                    </div>
                </td>
            `;
            tbody.appendChild(noResultRow);
        }
        noResultRow.style.display = '';
        if (pagination) pagination.style.display = 'none';
    } else {
        if (noResultRow) noResultRow.style.display = 'none';
        if (pagination && !searchInput.value.trim()) pagination.style.display = 'flex';
    }
}

const filterForm = document.querySelector('.filter-form');
const btnApply = filterForm?.querySelector('.btn-search');

if (btnApply) {
    const imgElement = btnApply.querySelector('img');
    btnApply.innerHTML = '';
    btnApply.appendChild(imgElement);
    btnApply.appendChild(document.createTextNode(' Áp dụng'));
}

document.getElementById('open-create-modal')?.addEventListener('click', () => {
    document.getElementById('create-modal').classList.add('active');
});

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
});

function initIconPicker(gridId, searchId, isEdit = false) {
    const iconGrid = document.getElementById(gridId);
    const searchInput = document.getElementById(searchId);
    if (!iconGrid || !searchInput) return;

    const renderIcons = (icons = CATEGORY_ICONS) => {
        const tempIcon = isEdit ? editTempSelectedIcon : tempSelectedIcon;
        iconGrid.innerHTML = icons.map(icon => `
            <div class="icon-item ${icon.file === tempIcon?.file ? 'selected' : ''}" 
                data-icon="${icon.file}" 
                data-name="${icon.name}"
                onclick="${isEdit ? 'selectEditTempIcon' : 'selectTempIcon'}('${icon.file}', '${icon.name}')">
                <img src="/images/category-icons/${icon.file}" alt="${icon.name}">
                <div class="icon-item-check">
                    <img src="/images/check.png" alt="Selected">
                </div>
            </div>
        `).join('');
    };

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        if (!query) {
            renderIcons();
            return;
        }

        const filtered = CATEGORY_ICONS.filter(icon => 
            icon.name.toLowerCase().includes(query) || icon.tags.toLowerCase().includes(query)
        );

        if (filtered.length === 0) {
            iconGrid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 40px 20px; color: #9ca3af;">
                    <img src="/images/search.png" style="width: 40px; opacity: 0.3; margin-bottom: 12px;">
                    <div style="font-size: 14px;">Không tìm thấy icon phù hợp</div>
                </div>
            `;
        } else {
            renderIcons(filtered);
        }
    });

    renderIcons();
}

function openIconPicker() {
    const modal = document.getElementById('icon-picker-modal');
    const currentIcon = document.getElementById('selected-icon-input').value;
    const currentName = document.getElementById('current-icon-name').textContent;
    
    tempSelectedIcon = { file: currentIcon, name: currentName };
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    document.getElementById('icon-search-input').value = '';
    initIconPicker('icon-grid', 'icon-search-input', false);
}

function closeIconPicker() {
    document.getElementById('icon-picker-modal').classList.remove('active');
    document.body.style.overflow = '';
    tempSelectedIcon = null;
}

function selectTempIcon(file, name) {
    document.querySelectorAll('#icon-grid .icon-item').forEach(item => item.classList.remove('selected'));
    document.querySelector(`#icon-grid [data-icon="${file}"]`)?.classList.add('selected');
    tempSelectedIcon = { file, name };
}

function confirmIconSelection() {
    if (!tempSelectedIcon) return closeIconPicker();
    
    const { file, name } = tempSelectedIcon;
    const iconPath = `/images/category-icons/${file}`;
    
    document.getElementById('selected-icon-input').value = file;
    document.getElementById('current-icon-preview').src = iconPath;
    document.getElementById('current-icon-name').textContent = name;
    document.getElementById('preview-icon-img').src = iconPath;
    
    closeIconPicker();
}

function openEditModal(cat) {
    const form = document.getElementById('edit-form');
    form.action = `/categories/${cat.id}`;
    
    // Fill data
    const fields = {
        'edit-ten': cat.ten_danh_muc,
        'edit-mota': cat.mo_ta,
        'edit-parent': cat.danh_muc_cha_id
    };
    
    Object.entries(fields).forEach(([id, value]) => {
        const el = document.getElementById(id);
        if (el) el.value = value || '';
    });
    
    // Radio buttons
    document.getElementById('thu-edit').checked = (cat.loai_danh_muc === 'THU');
    document.getElementById('chi-edit').checked = (cat.loai_danh_muc === 'CHI');
    
    // Icon
    const iconFile = cat.bieu_tuong || 'money.png';
    document.getElementById('edit-selected-icon-input').value = iconFile;
    document.getElementById('edit-current-icon-preview').src = `/images/category-icons/${iconFile}`;
    document.getElementById('edit-current-icon-name').textContent = getIconName(iconFile);
    
    // Preview
    updateEditPreview(cat.ten_danh_muc, cat.loai_danh_muc, iconFile);
    
    document.getElementById('edit-modal').classList.add('active');
    initEditLivePreview();
}

function updateEditPreview(name, type, icon) {
    document.getElementById('edit-preview-name').textContent = name || 'Tên danh mục';
    document.getElementById('edit-preview-icon-img').src = `/images/category-icons/${icon}`;
    
    const badge = document.getElementById('edit-preview-badge');
    badge.textContent = type === 'THU' ? 'THU NHẬP' : 'CHI TIÊU';
    badge.className = `badge badge-${type === 'THU' ? 'income' : 'expense'}`;
}

function initEditLivePreview() {
    const elements = {
        name: document.getElementById('edit-ten'),
        thu: document.getElementById('thu-edit'),
        chi: document.getElementById('chi-edit')
    };
    
    // Clone to remove old listeners
    Object.keys(elements).forEach(key => {
        const el = elements[key];
        const clone = el.cloneNode(true);
        el.parentNode.replaceChild(clone, el);
        elements[key] = clone;
    });
    
    // Name input
    elements.name.addEventListener('input', function() {
        document.getElementById('edit-preview-name').textContent = this.value.trim() || 'Tên danh mục';
    });
    
    // Type change
    const updateType = () => {
        const isIncome = document.getElementById('thu-edit').checked;
        const badge = document.getElementById('edit-preview-badge');
        badge.textContent = isIncome ? 'THU NHẬP' : 'CHI TIÊU';
        badge.className = `badge badge-${isIncome ? 'income' : 'expense'}`;
    };
    
    elements.thu.addEventListener('change', updateType);
    elements.chi.addEventListener('change', updateType);
}

function openEditIconPicker() {
    const modal = document.getElementById('edit-icon-picker-modal');
    const currentIcon = document.getElementById('edit-selected-icon-input').value;
    const currentName = document.getElementById('edit-current-icon-name').textContent;
    
    editTempSelectedIcon = { file: currentIcon, name: currentName };
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    document.getElementById('edit-icon-search-input').value = '';
    initIconPicker('edit-icon-grid', 'edit-icon-search-input', true);
}

function closeEditIconPicker() {
    document.getElementById('edit-icon-picker-modal').classList.remove('active');
    document.body.style.overflow = '';
    editTempSelectedIcon = null;
}

function selectEditTempIcon(file, name) {
    document.querySelectorAll('#edit-icon-grid .icon-item').forEach(item => item.classList.remove('selected'));
    document.querySelector(`#edit-icon-grid [data-icon="${file}"]`)?.classList.add('selected');
    editTempSelectedIcon = { file, name };
}

function confirmEditIconSelection() {
    if (!editTempSelectedIcon) return closeEditIconPicker();
    
    const { file, name } = editTempSelectedIcon;
    const iconPath = `/images/category-icons/${file}`;
    
    document.getElementById('edit-selected-icon-input').value = file;
    document.getElementById('edit-current-icon-preview').src = iconPath;
    document.getElementById('edit-current-icon-name').textContent = name;
    document.getElementById('edit-preview-icon-img').src = iconPath;
    
    closeEditIconPicker();
}

function initLivePreview() {
    const nameInput = document.getElementById('category-name-input');
    const previewName = document.getElementById('preview-name');
    const radioThu = document.getElementById('thu-create');
    const radioChi = document.getElementById('chi-create');

    if (!nameInput || !previewName) return;

    nameInput.addEventListener('input', function() {
        previewName.textContent = this.value.trim() || 'Tên danh mục';
    });

    const updateType = () => {
        const badge = document.getElementById('preview-badge');
        const isIncome = radioThu?.checked;
        badge.textContent = isIncome ? 'THU NHẬP' : 'CHI TIÊU';
        badge.className = `badge badge-${isIncome ? 'income' : 'expense'}`;
    };
    
    radioThu?.addEventListener('change', updateType);
    radioChi?.addEventListener('change', updateType);
    updateType();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        ['icon-picker-modal', 'edit-icon-picker-modal'].forEach(id => {
            const modal = document.getElementById(id);
            if (modal?.classList.contains('active')) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    initLivePreview();
});

@if($errors->any() && !$errors->has('id'))
    document.getElementById('create-modal')?.classList.add('active');
@endif
</script>
@endsection