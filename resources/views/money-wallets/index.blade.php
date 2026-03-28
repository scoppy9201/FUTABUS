@extends('layouts.app')
@section('title', 'Ví tiền')
@section('content')
<style>
:root {
    --primary: #4a90e2;
    --primary-dark: #2a5298;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --radius: 16px;
    --radius-sm: 10px;
    --shadow: 0 2px 12px rgba(0,0,0,0.06);
    --transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
}

/* ── Page header ── */
.pw-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 24px; padding: 22px 28px;
    background: white; border-radius: var(--radius); box-shadow: var(--shadow);
}
body.dark .pw-header { background: #191d27; }
.pw-title {
    display: flex; align-items: center; gap: 14px;
    font-size: 22px; font-weight: 800; color: #1f2937;
}
body.dark .pw-title { color: #e5e7eb; }
.pw-icon {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex; align-items: center; justify-content: center; font-size: 22px;
    box-shadow: 0 4px 14px rgba(74,144,226,0.35);
}
.pw-actions { display: flex; gap: 10px; }

/* ── Total balance card ── */
.total-card {
    background: linear-gradient(135deg, #1e3a5f 0%, #2a5298 50%, #4a90e2 100%);
    border-radius: var(--radius); padding: 28px 32px;
    margin-bottom: 24px; position: relative; overflow: hidden;
    box-shadow: 0 8px 32px rgba(42,82,152,0.35);
}
.total-card::before {
    content: ''; position: absolute; top: -60px; right: -40px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,0.06); border-radius: 50%;
}
.total-card::after {
    content: ''; position: absolute; bottom: -80px; left: 30%;
    width: 180px; height: 180px;
    background: rgba(255,255,255,0.04); border-radius: 50%;
}
.total-label { font-size: 13px; color: rgba(255,255,255,0.7); font-weight: 600; margin-bottom: 8px; }
.total-amount { font-size: 40px; font-weight: 900; color: white; letter-spacing: -1.5px; line-height: 1; margin-bottom: 20px; }
.total-breakdown { display: flex; gap: 24px; flex-wrap: wrap; }
.breakdown-item { text-align: center; }
.breakdown-label { font-size: 11px; color: rgba(255,255,255,0.6); font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
.breakdown-val { font-size: 15px; font-weight: 800; color: white; }

/* ── Stats strip ── */
.stats-strip { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 24px; }
.ss-card {
    background: white; border-radius: var(--radius); padding: 18px 22px;
    box-shadow: var(--shadow); display: flex; align-items: center; gap: 14px;
    transition: var(--transition);
}
.ss-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
body.dark .ss-card { background: #191d27; }
.ss-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
.ss-info {}
.ss-label { font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.ss-val { font-size: 20px; font-weight: 900; color: #1f2937; letter-spacing: -0.5px; }
body.dark .ss-val { color: #e5e7eb; }

/* ── Wallet grid ── */
.wallet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px,1fr)); gap: 20px; margin-bottom: 28px; }

.wallet-card {
    background: white; border-radius: var(--radius); overflow: hidden;
    box-shadow: var(--shadow); transition: var(--transition);
    border: 1px solid rgba(255,255,255,0.8);
    text-decoration: none; color: inherit; display: flex; flex-direction: column;
    position: relative;
}
.wallet-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.1); }
body.dark .wallet-card { background: #191d27; border-color: rgba(255,255,255,0.06); }

/* Color bar theo loại ví */
.wallet-card-bar { height: 4px; }

.wc-body { padding: 20px 22px; flex: 1; }
.wc-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px; }
.wc-icon-wrap {
    width: 52px; height: 52px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 26px;
}
.wc-type-badge {
    padding: 4px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 700;
}
.wc-name { font-size: 16px; font-weight: 800; color: #1f2937; margin-bottom: 6px; }
body.dark .wc-name { color: #e5e7eb; }
.wc-desc { font-size: 12px; color: #9ca3af; font-weight: 500; }
.wc-balance-section { margin-top: 16px; padding-top: 16px; border-top: 1px solid #f3f4f6; }
body.dark .wc-balance-section { border-color: rgba(255,255,255,0.06); }
.wc-balance-label { font-size: 11px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
.wc-balance { font-size: 26px; font-weight: 900; letter-spacing: -1px; }
.wc-currency { font-size: 14px; font-weight: 600; color: #9ca3af; margin-left: 4px; }

.wc-footer {
    padding: 12px 22px; background: #fafafa;
    border-top: 1px solid #f3f4f6;
    display: flex; align-items: center; justify-content: space-between;
}
body.dark .wc-footer { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.06); }
.wc-date { font-size: 12px; color: #9ca3af; }
.wc-arrow {
    width: 28px; height: 28px; border-radius: 8px;
    background: rgba(74,144,226,0.08); color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    transition: var(--transition); font-size: 14px;
}
.wallet-card:hover .wc-arrow { background: var(--primary); color: white; }

/* ── Empty ── */
.empty-wrap {
    grid-column: 1/-1; text-align: center; padding: 80px 20px;
    background: white; border-radius: var(--radius); box-shadow: var(--shadow);
}
body.dark .empty-wrap { background: #191d27; }
.empty-icon-big { font-size: 56px; margin-bottom: 16px; }
.empty-title { font-size: 20px; font-weight: 800; color: #1f2937; margin-bottom: 8px; }
body.dark .empty-title { color: #e5e7eb; }
.empty-sub { font-size: 14px; color: #9ca3af; margin-bottom: 28px; }

/* ── Inactive wallets ── */
.inactive-section {
    background: white; border-radius: var(--radius); padding: 20px 24px;
    margin-top: 8px; box-shadow: var(--shadow);
}
body.dark .inactive-section { background: #191d27; }
.inactive-hdr {
    display: flex; align-items: center; gap: 8px; cursor: pointer;
    font-size: 14px; font-weight: 700; color: #9ca3af; margin-bottom: 0;
    user-select: none;
}
.inactive-list { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
.inactive-chip {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 14px; border-radius: 10px;
    background: #f3f4f6; border: 1px solid #e5e7eb;
    font-size: 13px; font-weight: 600; color: #6b7280;
}
body.dark .inactive-chip { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.08); color: #9ca3af; }
.inactive-chip-icon { font-size: 18px; }
.restore-btn {
    padding: 3px 10px; border-radius: 8px;
    background: rgba(74,144,226,0.1); border: none;
    color: var(--primary); font-size: 11px; font-weight: 700;
    cursor: pointer; transition: background 0.2s;
}
.restore-btn:hover { background: rgba(74,144,226,0.2); }

/* ── Alert ── */
.alert {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px; border-radius: var(--radius-sm);
    font-size: 14px; font-weight: 500; margin-bottom: 20px;
}
.alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
.alert-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--danger); }
.alert-info    { background: #dbeafe; color: #1e40af; border-left: 4px solid var(--primary); }

/* ── Buttons ── */
.btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; font-size: 14px; font-weight: 700;
    border: none; cursor: pointer; text-decoration: none; transition: opacity 0.2s;
    white-space: nowrap;
}
.btn-primary:hover { opacity: 0.88; }
.btn-outline {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 18px; border-radius: var(--radius-sm);
    border: 2px solid var(--primary); color: var(--primary);
    background: transparent; font-size: 14px; font-weight: 700;
    cursor: pointer; text-decoration: none; transition: all 0.2s; white-space: nowrap;
}
.btn-outline:hover { background: var(--primary); color: white; }

/* ── Modal ── */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);
    z-index: 9999; display: flex; align-items: center; justify-content: center;
    padding: 20px; opacity: 0; visibility: hidden;
    transition: opacity 0.22s, visibility 0.22s;
}
.modal-overlay.active { opacity: 1; visibility: visible; }
.modal-box {
    background: white; border-radius: 20px;
    width: 100%; max-width: 540px; overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    transform: scale(0.95) translateY(10px);
    transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
    max-height: 90vh; overflow-y: auto;
}
.modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
body.dark .modal-box { background: #191d27; }

.modal-hdr {
    padding: 22px 28px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex; justify-content: space-between; align-items: center;
    position: sticky; top: 0; z-index: 1;
}
.modal-hdr-title { font-size: 17px; font-weight: 800; color: white; display: flex; align-items: center; gap: 10px; }
.modal-close-btn {
    background: rgba(255,255,255,0.2); border: none; border-radius: 8px;
    width: 32px; height: 32px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 18px; transition: background 0.2s;
}
.modal-close-btn:hover { background: rgba(255,255,255,0.32); }
.modal-body { padding: 24px 28px; }

.form-group { margin-bottom: 16px; }
.form-label { font-size: 13px; font-weight: 700; color: #374151; display: block; margin-bottom: 7px; }
body.dark .form-label { color: #9ca3af; }
.required { color: var(--danger); }
.form-ctrl {
    width: 100%; padding: 10px 14px;
    border: 2px solid #e5e7eb; border-radius: var(--radius-sm);
    font-size: 14px; background: #f9fafb; color: #1f2937;
    outline: none; transition: all 0.2s;
}
.form-ctrl:focus { border-color: var(--primary); background: white; }
body.dark .form-ctrl { background: #141820; border-color: rgba(255,255,255,0.1); color: #e5e7eb; }

/* Loại ví selector */
.loai-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; margin-top: 6px; }
.loai-card {
    border: 2px solid #e5e7eb; border-radius: 12px;
    padding: 12px 8px; text-align: center; cursor: pointer;
    transition: var(--transition); position: relative;
}
.loai-card:hover { border-color: var(--primary); }
.loai-card.selected { border-color: var(--primary); background: rgba(74,144,226,0.06); }
body.dark .loai-card { border-color: rgba(255,255,255,0.1); }
body.dark .loai-card.selected { background: rgba(74,144,226,0.1); }
.loai-card input { position: absolute; opacity: 0; pointer-events: none; }
.loai-emoji { font-size: 24px; margin-bottom: 5px; }
.loai-name { font-size: 11px; font-weight: 700; color: #374151; }
body.dark .loai-name { color: #e5e7eb; }

.emoji-grid { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.emoji-btn {
    width: 38px; height: 38px; border-radius: 8px; border: 2px solid #e5e7eb;
    background: #f9fafb; cursor: pointer; font-size: 18px;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.15s;
}
.emoji-btn:hover { border-color: var(--primary); background: rgba(74,144,226,0.05); }
.emoji-btn.selected { border-color: var(--primary); background: rgba(74,144,226,0.1); }

.modal-foot {
    padding: 16px 28px; border-top: 1px solid #f3f4f6;
    display: flex; gap: 10px;
    position: sticky; bottom: 0;
    background: white; z-index: 1;
}
body.dark .modal-foot { border-color: rgba(255,255,255,0.06); background: #191d27; }
.btn-cancel {
    flex: 1; padding: 10px; border-radius: var(--radius-sm);
    background: #f3f4f6; border: 2px solid #e5e7eb;
    color: #6b7280; font-size: 14px; font-weight: 600; cursor: pointer;
}
.btn-cancel:hover { background: #e5e7eb; }
.modal-foot .btn-primary { flex: 2; justify-content: center; padding: 10px; }

@media (max-width: 768px) {
    .stats-strip { grid-template-columns: 1fr; }
    .wallet-grid  { grid-template-columns: 1fr; }
    .pw-header    { flex-direction: column; align-items: flex-start; gap: 12px; }
    .loai-grid    { grid-template-columns: repeat(2,1fr); }
}
</style>

<div class="pw-header">
    <div class="pw-title">
        <div class="pw-icon">💳</div>
        <div>
            <div>Ví tiền</div>
            <div style="font-size:13px;font-weight:500;color:#6b7280;margin-top:2px;">Quản lý các nguồn tiền của bạn</div>
        </div>
    </div>
    <div class="pw-actions">
        <a href="{{ route('wallet-transfers.index') }}" class="btn-outline">
            ↔ Chuyển tiền
        </a>
        <button class="btn-primary" onclick="openCreate()">
            + Thêm ví
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">⚠ {{ session('error') }}</div>
@endif
@if(session('info'))
<div class="alert alert-info">ℹ {{ session('info') }}</div>
@endif

{{-- Tổng số dư --}}
<div class="total-card">
    <div class="total-label">TỔNG TÀI SẢN</div>
    <div class="total-amount">
        {{ number_format($stats['tong_so_du'], 0, ',', '.') }}
        <span style="font-size:20px;font-weight:600;opacity:.7;">VND</span>
    </div>
    <div class="total-breakdown">
        @if($stats['vi_tien_mat'] > 0)
        <div class="breakdown-item">
            <div class="breakdown-label">💵 Tiền mặt</div>
            <div class="breakdown-val">{{ number_format($stats['vi_tien_mat']) }}đ</div>
        </div>
        @endif
        @if($stats['vi_ngan_hang'] > 0)
        <div class="breakdown-item">
            <div class="breakdown-label">🏦 Ngân hàng</div>
            <div class="breakdown-val">{{ number_format($stats['vi_ngan_hang']) }}đ</div>
        </div>
        @endif
        @if($stats['vi_dien_tu'] > 0)
        <div class="breakdown-item">
            <div class="breakdown-label">📱 Ví điện tử</div>
            <div class="breakdown-val">{{ number_format($stats['vi_dien_tu']) }}đ</div>
        </div>
        @endif
        <div class="breakdown-item">
            <div class="breakdown-label">📊 Số ví</div>
            <div class="breakdown-val">{{ $stats['tong_vi'] }} ví</div>
        </div>
    </div>
</div>

{{-- Danh sách ví --}}
<div class="wallet-grid">
    @forelse($wallets as $w)
    @php
        $color = $w->loai_vi_color;
        $bgLight = $color . '18';
    @endphp
    <a href="{{ route('money-wallets.show', $w) }}" class="wallet-card">
        <div class="wallet-card-bar" style="background:{{ $color }};"></div>
        <div class="wc-body">
            <div class="wc-top">
                <div class="wc-icon-wrap" style="background:{{ $bgLight }}">
                    <span style="font-size:28px;">{{ $w->bieu_tuong }}</span>
                </div>
                <span class="wc-type-badge" style="background:{{ $bgLight }};color:{{ $color }};">
                    {{ $w->loai_vi_label }}
                </span>
            </div>
            <div class="wc-name">{{ $w->ten_vi }}</div>
            @if($w->mo_ta)
            <div class="wc-desc">{{ Str::limit($w->mo_ta, 50) }}</div>
            @endif
            <div class="wc-balance-section">
                <div class="wc-balance-label">Số dư hiện tại</div>
                <div>
                    <span class="wc-balance" style="color:{{ $w->so_du >= 0 ? $color : '#ef4444' }};">
                        {{ number_format($w->so_du, 0, ',', '.') }}
                    </span>
                    <span class="wc-currency">{{ $w->don_vi_tien_te }}</span>
                </div>
            </div>
        </div>
        <div class="wc-footer">
            <span class="wc-date">Tạo {{ $w->created_at->format('d/m/Y') }}</span>
            <div class="wc-arrow">→</div>
        </div>
    </a>
    @empty
    <div class="empty-wrap">
        <div class="empty-icon-big">💳</div>
        <div class="empty-title">Chưa có ví nào</div>
        <div class="empty-sub">Tạo ví đầu tiên để quản lý tài chính theo từng nguồn tiền</div>
        <button class="btn-primary" onclick="openCreate()">+ Thêm ví đầu tiên</button>
    </div>
    @endforelse
</div>

{{-- Ví không hoạt động --}}
@if($inactiveWallets->count() > 0)
<div class="inactive-section">
    <div class="inactive-hdr" onclick="toggleInactive()">
        <span>🗄</span>
        <span>Ví đã ẩn ({{ $inactiveWallets->count() }})</span>
        <span id="inactive-arrow" style="margin-left:auto;">▼</span>
    </div>
    <div class="inactive-list" id="inactive-list" style="display:none;">
        @foreach($inactiveWallets as $w)
        <div class="inactive-chip">
            <span class="inactive-chip-icon">{{ $w->bieu_tuong }}</span>
            <span>{{ $w->ten_vi }}</span>
            <span style="color:#9ca3af;font-size:12px;">{{ number_format($w->so_du) }}đ</span>
            <form action="{{ route('money-wallets.restore', $w) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="restore-btn">Khôi phục</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ MODAL: Thêm ví mới ═══ --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">💳 Thêm ví mới</div>
            <button class="modal-close-btn" onclick="closeModal('createModal')">✕</button>
        </div>
        <form action="{{ route('money-wallets.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Loại ví <span class="required">*</span></label>
                    <div class="loai-grid">
                        @foreach([
                            ['tien_mat','💵','Tiền mặt'],
                            ['ngan_hang','🏦','Ngân hàng'],
                            ['vi_dien_tu','📱','Ví điện tử'],
                            ['the_tin_dung','💳','Thẻ tín dụng'],
                            ['dau_tu','📈','Đầu tư'],
                            ['khac','🗂','Khác'],
                        ] as [$val,$emoji,$label])
                        <label class="loai-card {{ $val==='tien_mat'?'selected':'' }}" onclick="selectLoai(this)">
                            <input type="radio" name="loai_vi" value="{{ $val }}" {{ $val==='tien_mat'?'checked':'' }}>
                            <div class="loai-emoji">{{ $emoji }}</div>
                            <div class="loai-name">{{ $label }}</div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tên ví <span class="required">*</span></label>
                    <input name="ten_vi" class="form-ctrl" placeholder="Ví dụ: Tiền mặt cá nhân, ACB, MoMo..." required maxlength="100" value="{{ old('ten_vi') }}">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Số dư ban đầu <span class="required">*</span></label>
                        <input name="so_du_ban_dau" type="number" class="form-ctrl" placeholder="0" min="0" step="1000" value="{{ old('so_du_ban_dau', 0) }}" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Đơn vị tiền tệ <span class="required">*</span></label>
                        <select name="don_vi_tien_te" class="form-ctrl" required>
                            <option value="VND" selected>VND — Việt Nam Đồng</option>
                            <option value="USD">USD — Đô la Mỹ</option>
                            <option value="EUR">EUR — Euro</option>
                            <option value="JPY">JPY — Yên Nhật</option>
                            <option value="KRW">KRW — Won Hàn Quốc</option>
                            <option value="SGD">SGD — Đô la Singapore</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label">Biểu tượng</label>
                    <div class="emoji-grid" id="emojiGrid">
                        @foreach(['💰','💵','💴','💶','🏦','💳','📱','💹','📈','🪙','💎','🏧','🛍','🎁','🗂','📊'] as $e)
                        <button type="button" class="emoji-btn {{ $e==='💰'?'selected':'' }}"
                            onclick="selectEmoji(this,'{{ $e }}')">{{ $e }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="bieu_tuong" id="emojiInput" value="💰">
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <input name="mo_ta" class="form-ctrl" placeholder="Ghi chú thêm..." maxlength="500">
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="closeModal('createModal')">Hủy</button>
                <button type="submit" class="btn-primary">Tạo ví</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ MODAL: Sửa ví ═══ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">✏️ Chỉnh sửa ví</div>
            <button class="modal-close-btn" onclick="closeModal('editModal')">✕</button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Loại ví <span class="required">*</span></label>
                    <div class="loai-grid" id="editLoaiGrid">
                        @foreach([
                            ['tien_mat','💵','Tiền mặt'],
                            ['ngan_hang','🏦','Ngân hàng'],
                            ['vi_dien_tu','📱','Ví điện tử'],
                            ['the_tin_dung','💳','Thẻ tín dụng'],
                            ['dau_tu','📈','Đầu tư'],
                            ['khac','🗂','Khác'],
                        ] as [$val,$emoji,$label])
                        <label class="loai-card" onclick="selectLoaiEdit(this)" data-val="{{ $val }}">
                            <input type="radio" name="loai_vi" value="{{ $val }}">
                            <div class="loai-emoji">{{ $emoji }}</div>
                            <div class="loai-name">{{ $label }}</div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tên ví <span class="required">*</span></label>
                    <input name="ten_vi" id="editTenVi" class="form-ctrl" required maxlength="100">
                </div>

                <div class="form-group">
                    <label class="form-label">Đơn vị tiền tệ</label>
                    <select name="don_vi_tien_te" id="editDonVi" class="form-ctrl">
                        <option value="VND">VND</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="JPY">JPY</option>
                        <option value="KRW">KRW</option>
                        <option value="SGD">SGD</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Biểu tượng</label>
                    <div class="emoji-grid" id="editEmojiGrid">
                        @foreach(['💰','💵','💴','💶','🏦','💳','📱','💹','📈','🪙','💎','🏧','🛍','🎁','🗂','📊'] as $e)
                        <button type="button" class="emoji-btn"
                            onclick="selectEmojiEdit(this,'{{ $e }}')">{{ $e }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="bieu_tuong" id="editEmojiInput" value="">
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <input name="mo_ta" id="editMoTa" class="form-ctrl" maxlength="500">
                </div>

                <div style="background:rgba(245,158,11,0.08);border-radius:10px;padding:12px;font-size:12px;color:#92400e;display:flex;align-items:center;gap:8px;">
                    ⚠️ Muốn thay đổi số dư, vào chi tiết ví và dùng <strong>"Điều chỉnh số dư"</strong>.
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Hủy</button>
                <button type="submit" class="btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Modal helpers ──────────────────────────────────────
function openCreate()  { document.getElementById('createModal').classList.add('active'); }
function closeModal(id){ document.getElementById(id).classList.remove('active'); }

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if(e.target===o) o.classList.remove('active'); });
});

// ── Loại ví selector (create) ──────────────────────────
function selectLoai(el) {
    document.querySelectorAll('#createModal .loai-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
}

// ── Emoji selector (create) ───────────────────────────
function selectEmoji(btn, emoji) {
    document.querySelectorAll('#emojiGrid .emoji-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('emojiInput').value = emoji;
}

// ── Edit modal ────────────────────────────────────────
function openEdit(wallet) {
    const form = document.getElementById('editForm');
    form.action = `/money-wallets/${wallet.id}`;

    document.getElementById('editTenVi').value = wallet.ten_vi;
    document.getElementById('editMoTa').value  = wallet.mo_ta || '';
    document.getElementById('editDonVi').value = wallet.don_vi_tien_te;
    document.getElementById('editEmojiInput').value = wallet.bieu_tuong;

    // Loại ví
    document.querySelectorAll('#editLoaiGrid .loai-card').forEach(c => {
        c.classList.toggle('selected', c.dataset.val === wallet.loai_vi);
        if(c.dataset.val === wallet.loai_vi) c.querySelector('input').checked = true;
    });

    // Emoji
    document.querySelectorAll('#editEmojiGrid .emoji-btn').forEach(b => {
        b.classList.toggle('selected', b.textContent.trim() === wallet.bieu_tuong);
    });

    document.getElementById('editModal').classList.add('active');
}

function selectLoaiEdit(el) {
    document.querySelectorAll('#editLoaiGrid .loai-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
}

function selectEmojiEdit(btn, emoji) {
    document.querySelectorAll('#editEmojiGrid .emoji-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('editEmojiInput').value = emoji;
}

// ── Inactive toggle ───────────────────────────────────
function toggleInactive() {
    const list  = document.getElementById('inactive-list');
    const arrow = document.getElementById('inactive-arrow');
    const shown = list.style.display !== 'none';
    list.style.display  = shown ? 'none' : 'flex';
    arrow.textContent   = shown ? '▼' : '▲';
}

// ── Auto-hide alerts ──────────────────────────────────
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition = 'opacity .3s';
        a.style.opacity = '0';
        setTimeout(() => a.remove(), 320);
    });
}, 4500);

@if($errors->any())
openCreate();
@endif
</script>
@endsection
