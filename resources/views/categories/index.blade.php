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

        /* ── Fix Laravel Pagination ── */
    .pagination-wrapper nav {
        display: flex;
        align-items: center;
    }

    .pagination-wrapper nav > div:first-child {
        display: none; /* ẩn "Showing X to Y of Z results" của Laravel vì mình đã có rồi */
    }

    .pagination-wrapper nav svg {
        width: 16px;
        height: 16px;
    }

    .pagination-wrapper nav span[aria-current="page"] span,
    .pagination-wrapper nav button,
    .pagination-wrapper nav a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .pagination-wrapper nav a {
        background: #f9fafb;
        color: #6b7280;
    }

    .pagination-wrapper nav a:hover {
        background: #f3f4f6;
        color: #374151;
    }

    .pagination-wrapper nav span[aria-current="page"] span {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
    }

    .pagination-wrapper nav span:not([aria-current="page"]) span {
        background: #f9fafb;
        color: #d1d5db;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
    }

    /* Dark mode */
    body.dark .pagination-wrapper nav a {
        background: var(--dark-bg);
        color: #e5e7eb;
    }

    body.dark .pagination-wrapper nav a:hover {
        background: var(--dark-card);
    }

    body.dark .pagination-wrapper nav span:not([aria-current="page"]) span {
        background: var(--dark-bg);
        color: #4b5563;
    }

    .pagination-wrapper nav ul,
    .pagination-wrapper nav > div:last-child {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }
</style>

<div class="category-container">
 
    {{-- Page Header --}}
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
 
    {{-- Toast notification --}}
    <div id="toast" class="alert" style="display:none;"></div>
 
    {{-- Filter --}}
    <div class="filter-card">
        <div class="filter-title">
            <img src="{{ asset('images/filter.png') }}" alt="Filter">
            <span>Bộ lọc & Tìm kiếm</span>
        </div>
        <div class="filter-form">
            <div class="form-group">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" id="filter-search" class="form-control" placeholder="Nhập tên danh mục...">
            </div>
            <div class="form-group">
                <label class="form-label">Loại danh mục</label>
                <select id="filter-loai" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="THU">Thu</option>
                    <option value="CHI">Chi</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Trạng thái</label>
                <select id="filter-trang-thai" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="1">Kích hoạt</option>
                    <option value="0">Vô hiệu hóa</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Sắp xếp</label>
                <select id="filter-sort-by" class="form-control">
                    <option value="created_at">Ngày tạo</option>
                    <option value="ten_danh_muc">Tên danh mục</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Thứ tự</label>
                <select id="filter-sort-order" class="form-control">
                    <option value="desc">Giảm dần</option>
                    <option value="asc">Tăng dần</option>
                </select>
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
 
    {{-- Table --}}
    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">
                <img src="{{ asset('images/list.png') }}" alt="List"> Danh sách danh mục
            </h3>
            <div class="table-stats" id="table-stats"></div>
        </div>
 
        {{-- Loading --}}
        <div id="table-loading" style="text-align:center; padding:40px; color:#9ca3af;">
            Đang tải...
        </div>
 
        {{-- Table body (JS render) --}}
        <div id="table-body" style="display:none;">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Tên danh mục</th>
                            <th style="width:100px;">Loại</th>
                            <th>Danh mục cha</th>
                            <th>Mô tả</th>
                            <th style="width:140px;">Trạng thái</th>
                            <th style="width:130px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="categories-tbody"></tbody>
                </table>
            </div>
            <div class="pagination-wrapper">
                <div id="pagination-info" class="pagination-info"></div>
                <div id="pagination-links"></div>
            </div>
        </div>
 
        {{-- Empty state --}}
        <div id="empty-state" style="display:none;" class="empty-state">
            <div class="empty-icon">
                <img src="{{ asset('images/empty-folder.png') }}" alt="Empty">
            </div>
            <h3>Chưa có danh mục nào</h3>
            <p>Hãy tạo danh mục đầu tiên để bắt đầu quản lý thu chi</p>
            <button type="button" class="btn-primary" id="empty-add-btn">
                <img src="{{ asset('images/plus.png') }}" alt="Add"> Thêm danh mục đầu tiên
            </button>
        </div>
    </div>
 
    {{-- Modals --}}
    @include('categories._modal_create')
    @include('categories._modal_edit')
 
</div>
 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
    
var API_BASE = '/api/v1/categories';
 
const CATEGORY_ICONS = [
    { file: 'money.png',         name: 'Tiền mặt',   tags: 'tien mat cash money' },
    { file: 'salary.png',        name: 'Lương',       tags: 'luong salary wage income' },
    { file: 'gift.png',          name: 'Quà tặng',   tags: 'qua tang gift present' },
    { file: 'investment.png',    name: 'Đầu tư',     tags: 'dau tu investment stock' },
    { file: 'food.png',          name: 'Ăn uống',    tags: 'an uong food eat drink' },
    { file: 'shopping.png',      name: 'Mua sắm',    tags: 'mua sam shopping shop cart' },
    { file: 'transport.png',     name: 'Di chuyển',  tags: 'di chuyen transport car bus' },
    { file: 'house.png',         name: 'Nhà cửa',    tags: 'nha cua house home rent' },
    { file: 'health.png',        name: 'Sức khỏe',   tags: 'suc khoe health medical hospital' },
    { file: 'education.png',     name: 'Giáo dục',   tags: 'giao duc education school book' },
    { file: 'entertainment.png', name: 'Giải trí',   tags: 'giai tri entertainment fun game' },
    { file: 'travel.png',        name: 'Du lịch',    tags: 'du lich travel vacation trip' },
    { file: 'bills.png',         name: 'Hóa đơn',    tags: 'hoa don bills utility payment' },
    { file: 'phone.png',         name: 'Điện thoại', tags: 'dien thoai phone mobile' },
    { file: 'internet.png',      name: 'Internet',   tags: 'internet wifi network' },
    { file: 'insurance.png',     name: 'Bảo hiểm',   tags: 'bao hiem insurance protection' },
    { file: 'sports.png',        name: 'Thể thao',   tags: 'the thao sport fitness gym' },
    { file: 'beauty.png',        name: 'Làm đẹp',    tags: 'lam dep beauty cosmetic spa' },
    { file: 'pet.png',           name: 'Thú cưng',   tags: 'thu cung pet dog cat animal' },
    { file: 'other.png',         name: 'Khác',       tags: 'khac other misc' },
];
 
let currentPage          = 1;
let searchTimeout        = null;
let tempSelectedIcon     = null;
let editTempSelectedIcon = null;
 
/* HELPERS*/
function escHtml(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, c =>
        ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c])
    );
}
const getIconName = file => CATEGORY_ICONS.find(i => i.file === file)?.name ?? 'Tiền mặt';
const closeModal  = id   => document.getElementById(id)?.classList.remove('active');
 
/* API HELPER */
async function api(method, url, body = null) {
    const opts = {
        method,
        headers: {
            'Accept':       'application/json',
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
 
/* TOAST */
function showToast(msg, type = 'success') {
    const el       = document.getElementById('toast');
    el.className   = `alert alert-${type === 'success' ? 'success' : 'error'}`;
    el.textContent = msg;
    el.style.display   = 'flex';
    el.style.opacity   = '1';
    el.style.transform = '';
    clearTimeout(el._t);
    el._t = setTimeout(() => {
        el.style.opacity   = '0';
        el.style.transform = 'translateY(-10px)';
        setTimeout(() => { el.style.display = 'none'; }, 300);
    }, 4000);
}
 
/* BUILD QUERY STRING*/
function getFilters(page = 1) {
    const p = new URLSearchParams();
    const search  = document.getElementById('filter-search').value.trim();
    const loai    = document.getElementById('filter-loai').value;
    const status  = document.getElementById('filter-trang-thai').value;
    const sortBy  = document.getElementById('filter-sort-by').value;
    const sortOrd = document.getElementById('filter-sort-order').value;
    if (search)        p.set('search',     search);
    if (loai)          p.set('loai',       loai);
    if (status !== '') p.set('trang_thai', status);
    p.set('sort_by',    sortBy);
    p.set('sort_order', sortOrd);
    p.set('page',       page);
    return p.toString();
}
 
/* LOAD CATEGORIES */
async function loadCategories(page = 1) {
    currentPage = page;
    document.getElementById('table-loading').style.display = 'block';
    document.getElementById('table-body').style.display    = 'none';
    document.getElementById('empty-state').style.display   = 'none';
 
    try {
        const data = await api('GET', `${API_BASE}?${getFilters(page)}`);
        renderTable(data.categories);
        renderStats(data.categories);
        renderParentOptions(data.parentCategories);
    } catch {
        window.showToast({ type: 'error', title: 'Lỗi', message: 'Không thể tải danh sách danh mục.' });
    } finally {
        document.getElementById('table-loading').style.display = 'none';
    }
}
 
/* RENDER TABLE= */
function renderTable(p) {
    const items = p.data;
    if (!items.length) {
        document.getElementById('empty-state').style.display = 'block';
        return;
    }
    document.getElementById('table-body').style.display = 'block';
    const offset = p.from ?? 1;
 
    document.getElementById('categories-tbody').innerHTML = items.map((cat, i) => `
        <tr data-id="${cat.id}">
            <td>${offset + i}</td>
            <td>
                <div class="category-name">
                    <div class="category-icon ${cat.loai_danh_muc === 'THU' ? 'income' : 'expense'}">
                        <img src="/images/category-icons/${escHtml(cat.bieu_tuong ?? 'money.png')}" alt="icon">
                    </div>
                    <strong>${escHtml(cat.ten_danh_muc)}</strong>
                </div>
            </td>
            <td>
                <span class="badge badge-${cat.loai_danh_muc === 'THU' ? 'income' : 'expense'}">
                    ${escHtml(cat.loai_danh_muc)}
                </span>
            </td>
            <td>${cat.parent ? escHtml(cat.parent.ten_danh_muc) : '---'}</td>
            <td>${cat.mo_ta ? escHtml(cat.mo_ta.substring(0,50)) + (cat.mo_ta.length > 50 ? '…' : '') : 'Không có mô tả'}</td>
            <td>
                <span class="badge badge-${cat.trang_thai ? 'active' : 'inactive'}">
                    <span class="status-dot ${cat.trang_thai ? 'active' : 'inactive'}"></span>
                    ${cat.trang_thai ? 'Hoạt động' : 'Vô hiệu hóa'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-toggle"
                        onclick="handleToggleStatus(${cat.id})"
                        title="${cat.trang_thai ? 'Vô hiệu hóa' : 'Kích hoạt'}">
                        <img src="/images/${cat.trang_thai ? 'lock' : 'unlock'}.png" alt="toggle">
                    </button>
                    <button type="button" class="btn-action btn-edit"
                        onclick='openEditModal(${JSON.stringify(cat)})'
                        title="Chỉnh sửa">
                        <img src="/images/edit.png" alt="edit">
                    </button>
                    <button type="button" class="btn-action btn-delete"
                        onclick="handleDelete(${cat.id})"
                        title="Xóa">
                        <img src="/images/delete.png" alt="delete">
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
 
    document.getElementById('pagination-info').textContent =
        `Hiển thị ${p.from ?? 0} - ${p.to ?? 0} / ${p.total} kết quả`;
 
    renderPaginationLinks(p);
}
 
function renderPaginationLinks(p) {
    const el   = document.getElementById('pagination-links');
    const cur  = p.current_page;
    const last = p.last_page;
    let html   = '<div style="display:flex;gap:6px;">';
    if (cur > 1)
        html += `<button class="btn-filter btn-reset" style="min-width:36px;padding:0 10px;height:36px;" onclick="loadCategories(${cur-1})">‹</button>`;
    for (let i = Math.max(1, cur-2); i <= Math.min(last, cur+2); i++)
        html += `<button class="btn-filter ${i===cur?'btn-search':'btn-reset'}" style="min-width:36px;padding:0 10px;height:36px;" onclick="loadCategories(${i})">${i}</button>`;
    if (cur < last)
        html += `<button class="btn-filter btn-reset" style="min-width:36px;padding:0 10px;height:36px;" onclick="loadCategories(${cur+1})">›</button>`;
    html += '</div>';
    el.innerHTML = html;
}
 
function renderStats(p) {
    const thu = p.data.filter(c => c.loai_danh_muc === 'THU').length;
    const chi = p.data.filter(c => c.loai_danh_muc === 'CHI').length;
    document.getElementById('table-stats').innerHTML = `
        <span class="stat-badge income"><img src="/images/arrows.png" alt=""> Thu: ${thu}</span>
        <span class="stat-badge expense"><img src="/images/down.png" alt=""> Chi: ${chi}</span>
        <span class="stat-badge total"><img src="/images/chart.png" alt=""> Tổng: ${p.total}</span>
    `;
}
 
function renderParentOptions(parents) {
    ['create-parent', 'edit-parent'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        const val = el.value;
        el.innerHTML = '<option value="">-- Không chọn danh mục cha --</option>'
            + parents.map(p => `<option value="${p.id}" ${p.id == val ? 'selected' : ''}>${escHtml(p.ten_danh_muc)}</option>`).join('');
    });
}
 
/* CREATE= */
async function handleCreate(e) {
    e.preventDefault();
    clearErrors('create');
    const body = {
        ten_danh_muc:    document.getElementById('category-name-input').value,
        loai_danh_muc:   document.querySelector('input[name="loai_danh_muc"]:checked')?.value,
        danh_muc_cha_id: document.getElementById('create-parent').value || null,
        bieu_tuong:      document.getElementById('selected-icon-input').value,
        mo_ta:           document.getElementById('category-desc-input')?.value || '',
    };
    try {
        await api('POST', API_BASE, body);
        window.showToast({ type: 'success', title: 'Thành công', message: 'Thêm danh mục thành công!' });
        closeModal('create-modal');
        e.target.reset();
        document.getElementById('current-icon-preview').src      = '/images/category-icons/money.png';
        document.getElementById('current-icon-name').textContent = 'Tiền mặt';
        document.getElementById('selected-icon-input').value     = 'money.png';
        document.getElementById('preview-icon-img').src          = '/images/category-icons/money.png';
        loadCategories(currentPage);
    } catch (err) {
        if (err.errors)  showErrors(err.errors, 'create');
        if (err.message) window.showToast({ type: 'error', title: 'Lỗi', message: err.message });
    }
}
 
/* UPDATE */
async function handleUpdate(e) {
    e.preventDefault();
    clearErrors('edit');
    const id   = document.getElementById('edit-form').dataset.id;
    const body = {
        ten_danh_muc:    document.getElementById('edit-ten').value,
        loai_danh_muc:   document.querySelector('input[name="edit_loai"]:checked')?.value,
        danh_muc_cha_id: document.getElementById('edit-parent').value || null,
        bieu_tuong:      document.getElementById('edit-selected-icon-input').value,
        mo_ta:           document.getElementById('edit-mota').value || '',
    };
    try {
        await api('PATCH', `${API_BASE}/${id}`, body);
        window.showToast({ type: 'success', title: 'Thành công', message: 'Cập nhật danh mục thành công!' });
        closeModal('edit-modal');
        loadCategories(currentPage);
    } catch (err) {
        if (err.errors)  showErrors(err.errors, 'edit');
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
        Xác nhận xóa danh mục
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
      window.showToast({ type: 'success', title: 'Thành công', message: 'Xóa danh mục thành công!' });
      loadCategories(currentPage);
    } catch (err) {
      window.showToast({ type: 'error', title: 'Lỗi', message: err.message ?? 'Không thể xóa.' });
    }
  });
}
 
/* TOGGLE STATUS */
async function handleToggleStatus(id) {
    try {
        const data = await api('PATCH', `${API_BASE}/${id}/status`);
        window.showToast({ type: 'success', title: 'Thành công', message: data.message });
        loadCategories(currentPage);
    } catch (err) {
        window.showToast({ type: 'error', title: 'Lỗi', message: err.message ?? 'Có lỗi xảy ra.' });
    }
}
 
/* ERRORS*/
function showErrors(errors, prefix) {
    Object.keys(errors).forEach(field => {
        const el = document.getElementById(`${prefix}-error-${field}`);
        if (el) { el.textContent = errors[field][0]; el.style.display = 'block'; }
    });
}
function clearErrors(prefix) {
    document.querySelectorAll(`[id^="${prefix}-error-"]`).forEach(el => {
        el.textContent = ''; el.style.display = 'none';
    });
}
 
/* ICON PICKER*/
function initIconPicker(gridId, searchId, isEdit = false) {
    const grid     = document.getElementById(gridId);
    const searchEl = document.getElementById(searchId);
    if (!grid || !searchEl) return;
 
    const render = (icons = CATEGORY_ICONS) => {
        const selected = isEdit ? editTempSelectedIcon : tempSelectedIcon;
        grid.innerHTML = icons.map(icon => `
            <div class="icon-item ${icon.file === selected?.file ? 'selected' : ''}"
                data-icon="${icon.file}" data-name="${icon.name}"
                onclick="${isEdit ? 'selectEditTempIcon' : 'selectTempIcon'}('${icon.file}', '${icon.name}')">
                <img src="/images/category-icons/${icon.file}" alt="${icon.name}">
                <div class="icon-item-check"><img src="/images/check.png" alt="ok"></div>
            </div>
        `).join('');
    };
 
    searchEl.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        if (!q) { render(); return; }
        const f = CATEGORY_ICONS.filter(i => i.name.toLowerCase().includes(q) || i.tags.toLowerCase().includes(q));
        if (!f.length) {
            grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af;font-size:14px;">Không tìm thấy icon phù hợp</div>`;
        } else { render(f); }
    });
 
    render();
}
 
function openIconPicker() {
    const cur = document.getElementById('selected-icon-input').value;
    tempSelectedIcon = { file: cur, name: getIconName(cur) };
    document.getElementById('icon-picker-modal').classList.add('active');
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
    document.querySelectorAll('#icon-grid .icon-item').forEach(el => el.classList.remove('selected'));
    document.querySelector(`#icon-grid [data-icon="${file}"]`)?.classList.add('selected');
    tempSelectedIcon = { file, name };
}
function confirmIconSelection() {
    if (!tempSelectedIcon) return closeIconPicker();
    const { file, name } = tempSelectedIcon;
    document.getElementById('selected-icon-input').value     = file;
    document.getElementById('current-icon-preview').src      = `/images/category-icons/${file}`;
    document.getElementById('current-icon-name').textContent = name;
    document.getElementById('preview-icon-img').src          = `/images/category-icons/${file}`;
    closeIconPicker();
}
 
function openEditIconPicker() {
    const cur = document.getElementById('edit-selected-icon-input').value;
    editTempSelectedIcon = { file: cur, name: getIconName(cur) };
    document.getElementById('edit-icon-picker-modal').classList.add('active');
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
    document.querySelectorAll('#edit-icon-grid .icon-item').forEach(el => el.classList.remove('selected'));
    document.querySelector(`#edit-icon-grid [data-icon="${file}"]`)?.classList.add('selected');
    editTempSelectedIcon = { file, name };
}
function confirmEditIconSelection() {
    if (!editTempSelectedIcon) return closeEditIconPicker();
    const { file, name } = editTempSelectedIcon;
    document.getElementById('edit-selected-icon-input').value     = file;
    document.getElementById('edit-current-icon-preview').src       = `/images/category-icons/${file}`;
    document.getElementById('edit-current-icon-name').textContent  = name;
    document.getElementById('edit-preview-icon-img').src           = `/images/category-icons/${file}`;
    closeEditIconPicker();
}
 
/* EDIT MODAL*/
function openEditModal(cat) {
    const form = document.getElementById('edit-form');
    form.dataset.id = cat.id;
    document.getElementById('edit-ten').value    = cat.ten_danh_muc    ?? '';
    document.getElementById('edit-mota').value   = cat.mo_ta           ?? '';
    document.getElementById('edit-parent').value = cat.danh_muc_cha_id ?? '';
    document.getElementById('thu-edit').checked  = (cat.loai_danh_muc === 'THU');
    document.getElementById('chi-edit').checked  = (cat.loai_danh_muc === 'CHI');
 
    const iconFile = cat.bieu_tuong || 'money.png';
    document.getElementById('edit-selected-icon-input').value     = iconFile;
    document.getElementById('edit-current-icon-preview').src      = `/images/category-icons/${iconFile}`;
    document.getElementById('edit-current-icon-name').textContent = getIconName(iconFile);
 
    updateEditPreview(cat.ten_danh_muc, cat.loai_danh_muc, iconFile);
    document.getElementById('edit-modal').classList.add('active');
    initEditLivePreview();
}
 
function updateEditPreview(name, type, icon) {
    document.getElementById('edit-preview-name').textContent = name || 'Tên danh mục';
    document.getElementById('edit-preview-icon-img').src     = `/images/category-icons/${icon}`;
    const badge = document.getElementById('edit-preview-badge');
    badge.textContent = type === 'THU' ? 'THU NHẬP' : 'CHI TIÊU';
    badge.className   = `badge badge-${type === 'THU' ? 'income' : 'expense'}`;
}
 
function initEditLivePreview() {
    ['edit-ten', 'thu-edit', 'chi-edit'].forEach(id => {
        const el = document.getElementById(id);
        const clone = el.cloneNode(true);
        el.parentNode.replaceChild(clone, el);
    });
    document.getElementById('edit-ten').addEventListener('input', function () {
        document.getElementById('edit-preview-name').textContent = this.value.trim() || 'Tên danh mục';
    });
    const syncBadge = () => {
        const inc   = document.getElementById('thu-edit').checked;
        const badge = document.getElementById('edit-preview-badge');
        badge.textContent = inc ? 'THU NHẬP' : 'CHI TIÊU';
        badge.className   = `badge badge-${inc ? 'income' : 'expense'}`;
    };
    document.getElementById('thu-edit').addEventListener('change', syncBadge);
    document.getElementById('chi-edit').addEventListener('change', syncBadge);
}
 
/* CREATE LIVE PREVIEW */
function initLivePreview() {
    document.getElementById('category-name-input')?.addEventListener('input', function () {
        document.getElementById('preview-name').textContent = this.value.trim() || 'Tên danh mục';
    });
    const syncBadge = () => {
        const badge = document.getElementById('preview-badge');
        if (!badge) return;
        const inc = document.getElementById('thu-create')?.checked;
        badge.textContent = inc ? 'THU NHẬP' : 'CHI TIÊU';
        badge.className   = `badge badge-${inc ? 'income' : 'expense'}`;
    };
    document.getElementById('thu-create')?.addEventListener('change', syncBadge);
    document.getElementById('chi-create')?.addEventListener('change', syncBadge);
    syncBadge();
}
 
/* INIT= */
function initPage() {
    loadCategories();
    initLivePreview();

    document.getElementById('btn-search').addEventListener('click', () => loadCategories(1));
    document.getElementById('btn-reset').addEventListener('click', () => {
        document.getElementById('filter-search').value     = '';
        document.getElementById('filter-loai').value       = '';
        document.getElementById('filter-trang-thai').value = '';
        document.getElementById('filter-sort-by').value    = 'created_at';
        document.getElementById('filter-sort-order').value = 'desc';
        loadCategories(1);
    });

    document.getElementById('filter-search').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadCategories(1), 500);
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

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        ['icon-picker-modal', 'edit-icon-picker-modal'].forEach(id => {
            const m = document.getElementById(id);
            if (m?.classList.contains('active')) {
                m.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
}

window.openEditModal            = openEditModal;
window.handleDelete             = handleDelete;
window.handleToggleStatus       = handleToggleStatus;
window.openIconPicker           = openIconPicker;
window.closeIconPicker          = closeIconPicker;
window.selectTempIcon           = selectTempIcon;
window.confirmIconSelection     = confirmIconSelection;
window.openEditIconPicker       = openEditIconPicker;
window.closeEditIconPicker      = closeEditIconPicker;
window.selectEditTempIcon       = selectEditTempIcon;
window.confirmEditIconSelection = confirmEditIconSelection;
window.loadCategories           = loadCategories;
window.closeModal               = closeModal;
// Gọi ngay lập tức, không chờ DOMContentLoaded
initPage();
})();
</script>
@endsection