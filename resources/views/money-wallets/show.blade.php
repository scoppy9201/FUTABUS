@extends('layouts.app')
@section('title', $moneyWallet->ten_vi)
@section('content')
<style>
:root {
    --primary:#4a90e2;--primary-dark:#2a5298;
    --success:#10b981;--danger:#ef4444;--warning:#f59e0b;
    --radius:16px;--radius-sm:10px;
    --shadow:0 2px 12px rgba(0,0,0,0.06);
    --transition:all 0.25s cubic-bezier(0.4,0,0.2,1);
}

/* Breadcrumb */
.breadcrumb { display:flex;align-items:center;gap:8px;font-size:13px;color:#9ca3af;margin-bottom:20px; }
.breadcrumb a { color:var(--primary);text-decoration:none;font-weight:600; }

/* Hero ví */
.wallet-hero {
    border-radius: 20px; padding: 28px 32px;
    margin-bottom: 24px; position: relative; overflow: hidden;
    box-shadow: 0 8px 28px rgba(0,0,0,0.15);
}
.wallet-hero::before {
    content:''; position:absolute; top:-50px; right:-50px;
    width:200px; height:200px; background:rgba(255,255,255,0.08); border-radius:50%;
}
.wallet-hero::after {
    content:''; position:absolute; bottom:-60px; left:40%;
    width:160px; height:160px; background:rgba(255,255,255,0.05); border-radius:50%;
}
.hero-top { display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:24px; }
.hero-icon { font-size:52px; line-height:1; }
.hero-name { font-size:26px; font-weight:900; color:white; letter-spacing:-0.5px; margin-bottom:4px; }
.hero-type { font-size:13px; color:rgba(255,255,255,0.75); font-weight:600; }
.hero-actions { display:flex; gap:8px; }
.btn-hero {
    padding:8px 16px; border-radius:10px;
    background:rgba(255,255,255,0.18); border:1px solid rgba(255,255,255,0.25);
    color:white; font-size:13px; font-weight:700; cursor:pointer;
    text-decoration:none; display:flex; align-items:center; gap:6px;
    transition:background 0.2s;
}
.btn-hero:hover { background:rgba(255,255,255,0.28); }
.btn-hero.danger { background:rgba(239,68,68,0.25); border-color:rgba(239,68,68,0.4); }

.hero-balance { margin-bottom:20px; }
.hero-balance-label { font-size:12px;color:rgba(255,255,255,0.65);font-weight:700;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px; }
.hero-balance-val { font-size:44px;font-weight:900;color:white;letter-spacing:-2px;line-height:1; }
.hero-balance-currency { font-size:20px;font-weight:600;opacity:.7;margin-left:8px; }

.hero-stats { display:flex;gap:20px;flex-wrap:wrap; }
.hs-item {}
.hs-label { font-size:11px;color:rgba(255,255,255,0.6);font-weight:700;text-transform:uppercase;letter-spacing:0.6px; }
.hs-val { font-size:16px;font-weight:800;color:white;margin-top:3px; }

/* Tabs */
.tab-nav { display:flex;gap:4px;background:#f3f4f6;border-radius:12px;padding:4px;margin-bottom:20px;width:fit-content; }
body.dark .tab-nav { background:rgba(255,255,255,0.06); }
.tab-btn {
    padding:8px 18px;border-radius:10px;font-size:13px;font-weight:700;
    border:none;cursor:pointer;transition:var(--transition);color:#6b7280;background:transparent;
}
.tab-btn.active { background:white;color:var(--primary);box-shadow:0 2px 8px rgba(0,0,0,0.08); }
body.dark .tab-btn.active { background:#191d27;color:var(--primary); }

.tab-content { display:none; }
.tab-content.active { display:block; }

/* Section card */
.section-card { background:white;border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:20px; }
body.dark .section-card { background:#191d27; }
.sc-hdr { padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center; }
body.dark .sc-hdr { border-color:rgba(255,255,255,0.06); }
.sc-title { font-size:15px;font-weight:800;color:#1f2937; }
body.dark .sc-title { color:#e5e7eb; }

/* Transaction item */
.tx-item {
    display:flex;align-items:center;gap:14px;
    padding:14px 20px;border-bottom:1px solid #f9fafb;
    transition:background .15s;
}
body.dark .tx-item { border-color:rgba(255,255,255,0.03); }
.tx-item:last-child { border-bottom:none; }
.tx-item:hover { background:#f9fafb; }
body.dark .tx-item:hover { background:rgba(255,255,255,0.02); }

.tx-icon {
    width:40px;height:40px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px;
}
.tx-icon.income { background:#d1fae5; }
.tx-icon.expense { background:#fee2e2; }
.tx-info { flex:1;min-width:0; }
.tx-cat { font-size:14px;font-weight:700;color:#1f2937; }
body.dark .tx-cat { color:#e5e7eb; }
.tx-note { font-size:12px;color:#9ca3af;margin-top:2px; }
.tx-right { text-align:right;flex-shrink:0; }
.tx-amount { font-size:15px;font-weight:800; }
.tx-amount.income { color:var(--success); }
.tx-amount.expense { color:var(--danger); }
.tx-date { font-size:11px;color:#9ca3af;margin-top:2px; }

/* Transfer item */
.tf-item {
    display:flex;align-items:center;gap:12px;
    padding:14px 20px;border-bottom:1px solid #f9fafb;
    transition:background .15s;
}
body.dark .tf-item { border-color:rgba(255,255,255,0.03); }
.tf-item:last-child { border-bottom:none; }
.tf-item:hover { background:#f9fafb; }
body.dark .tf-item:hover { background:rgba(255,255,255,0.02); }
.tf-av {
    width:40px;height:40px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:18px;background:#dbeafe;flex-shrink:0;
}
.tf-desc { flex:1;min-width:0; }
.tf-names { font-size:13px;font-weight:700;color:#1f2937; }
body.dark .tf-names { color:#e5e7eb; }
.tf-date { font-size:12px;color:#9ca3af;margin-top:2px; }
.tf-amount { font-size:15px;font-weight:800;color:#4a90e2;flex-shrink:0; }

/* Adjustment item */
.adj-item {
    display:flex;align-items:center;gap:12px;
    padding:14px 20px;border-bottom:1px solid #f9fafb;
}
body.dark .adj-item { border-color:rgba(255,255,255,0.03); }
.adj-item:last-child { border-bottom:none; }
.adj-icon {
    width:38px;height:38px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;
}
.adj-icon.pos { background:#d1fae5; }
.adj-icon.neg { background:#fee2e2; }

/* Empty */
.empty-msg { text-align:center;padding:40px;color:#9ca3af;font-size:13px;font-weight:500; }

/* Alert */
.alert { display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:var(--radius-sm);font-size:14px;font-weight:500;margin-bottom:20px; }
.alert-success { background:#d1fae5;color:#065f46;border-left:4px solid var(--success); }
.alert-error   { background:#fee2e2;color:#991b1b;border-left:4px solid var(--danger); }

/* Buttons */
.btn-primary { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-sm);background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;transition:opacity .2s; }
.btn-primary:hover { opacity:.88; }
.btn-sm { padding:6px 12px;font-size:12px; }
.btn-danger { background:linear-gradient(135deg,var(--danger),#dc2626); }

/* Modal */
.modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;visibility:hidden;transition:opacity .22s,visibility .22s; }
.modal-overlay.active { opacity:1;visibility:visible; }
.modal-box { background:white;border-radius:20px;width:100%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);transform:scale(0.95) translateY(10px);transition:transform .25s cubic-bezier(0.4,0,0.2,1);max-height:90vh;overflow-y:auto; }
.modal-overlay.active .modal-box { transform:scale(1) translateY(0); }
body.dark .modal-box { background:#191d27; }
.modal-hdr { padding:20px 24px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;justify-content:space-between;align-items:center; }
.modal-hdr-title { font-size:16px;font-weight:800;color:white; }
.modal-close-btn { background:rgba(255,255,255,0.2);border:none;border-radius:8px;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:white;font-size:16px; }
.modal-body { padding:22px 24px; }
.form-group { margin-bottom:16px; }
.form-label { font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:7px; }
body.dark .form-label { color:#9ca3af; }
.required { color:var(--danger); }
.form-ctrl { width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:var(--radius-sm);font-size:14px;background:#f9fafb;color:#1f2937;outline:none;transition:all .2s; }
.form-ctrl:focus { border-color:var(--primary);background:white; }
body.dark .form-ctrl { background:#141820;border-color:rgba(255,255,255,0.1);color:#e5e7eb; }
.modal-foot { padding:16px 24px;border-top:1px solid #f3f4f6;display:flex;gap:10px; }
body.dark .modal-foot { border-color:rgba(255,255,255,0.06);background:#191d27; }
.btn-cancel { flex:1;padding:10px;border-radius:var(--radius-sm);background:#f3f4f6;border:2px solid #e5e7eb;color:#6b7280;font-size:14px;font-weight:600;cursor:pointer; }
.btn-cancel:hover { background:#e5e7eb; }
.modal-foot .btn-primary { flex:2;justify-content:center;padding:10px; }

/* Pagination */
.pagination-wrap { padding:16px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end; }
body.dark .pagination-wrap { border-color:rgba(255,255,255,0.06); }
</style>

@php $color = $moneyWallet->loai_vi_color; @endphp

<div class="breadcrumb">
    <a href="{{ route('money-wallets.index') }}">← Ví tiền</a>
    <span>/</span>
    <span>{{ $moneyWallet->ten_vi }}</span>
</div>

@if(session('success'))<div class="alert alert-success">✓ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">⚠ {{ session('error') }}</div>@endif

{{-- Hero --}}
<div class="wallet-hero" style="background:linear-gradient(135deg,{{ $color }}dd,{{ $color }});box-shadow:0 8px 28px {{ $color }}55;">
    <div class="hero-top">
        <div>
            <div class="hero-icon">{{ $moneyWallet->bieu_tuong }}</div>
            <div class="hero-name">{{ $moneyWallet->ten_vi }}</div>
            <div class="hero-type">{{ $moneyWallet->loai_vi_label }} · {{ $moneyWallet->don_vi_tien_te }}</div>
        </div>
        <div class="hero-actions">
            <button class="btn-hero" onclick="openAdjust()">⚖️ Điều chỉnh số dư</button>
            <button class="btn-hero" onclick="openEditFromShow()">✏️ Sửa</button>
            <form action="{{ route('money-wallets.destroy', $moneyWallet) }}" method="POST"
                  onsubmit="return confirm('Xóa/ẩn ví này?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-hero danger">🗑 Xóa</button>
            </form>
        </div>
    </div>

    <div class="hero-balance">
        <div class="hero-balance-label">Số dư hiện tại</div>
        <div>
            <span class="hero-balance-val">{{ number_format($moneyWallet->so_du, 0, ',', '.') }}</span>
            <span class="hero-balance-currency">{{ $moneyWallet->don_vi_tien_te }}</span>
        </div>
    </div>

    <div class="hero-stats">
        <div class="hs-item">
            <div class="hs-label">↑ Tổng thu</div>
            <div class="hs-val">+{{ number_format($stats['tong_thu']) }}đ</div>
        </div>
        <div class="hs-item">
            <div class="hs-label">↓ Tổng chi</div>
            <div class="hs-val">-{{ number_format($stats['tong_chi']) }}đ</div>
        </div>
        <div class="hs-item">
            <div class="hs-label">📅 Số dư ban đầu</div>
            <div class="hs-val">{{ number_format($moneyWallet->so_du_ban_dau) }}đ</div>
        </div>
        @if($moneyWallet->mo_ta)
        <div class="hs-item">
            <div class="hs-label">📝 Ghi chú</div>
            <div class="hs-val" style="font-size:13px;">{{ $moneyWallet->mo_ta }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Tabs --}}
<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('transactions',this)">💰 Giao dịch</button>
    <button class="tab-btn" onclick="switchTab('transfers',this)">↔️ Chuyển ví</button>
    <button class="tab-btn" onclick="switchTab('adjustments',this)">⚖️ Điều chỉnh</button>
</div>

{{-- Tab: Giao dịch --}}
<div class="tab-content active" id="tab-transactions">
    <div class="section-card">
        <div class="sc-hdr">
            <div class="sc-title">Lịch sử giao dịch</div>
            <a href="{{ route('transactions.index') }}?money_wallet_id={{ $moneyWallet->id }}" style="font-size:12px;color:var(--primary);font-weight:700;text-decoration:none;">Xem tất cả →</a>
        </div>
        @forelse($transactions as $tx)
        @php $isIncome = $tx->loai_giao_dich === 'THU'; @endphp
        <div class="tx-item">
            <div class="tx-icon {{ $isIncome ? 'income' : 'expense' }}">
                {{ $tx->category?->bieu_tuong ?? ($isIncome ? '💰' : '💸') }}
            </div>
            <div class="tx-info">
                <div class="tx-cat">{{ $tx->category?->ten_danh_muc ?? 'Không rõ' }}</div>
                @if($tx->ghi_chu)
                <div class="tx-note">{{ Str::limit($tx->ghi_chu, 60) }}</div>
                @endif
            </div>
            <div class="tx-right">
                <div class="tx-amount {{ $isIncome ? 'income' : 'expense' }}">
                    {{ $isIncome ? '+' : '-' }}{{ number_format($tx->so_tien) }}đ
                </div>
                <div class="tx-date">{{ \Carbon\Carbon::parse($tx->ngay_giao_dich)->format('d/m/Y') }}</div>
            </div>
        </div>
        @empty
        <div class="empty-msg">📭 Chưa có giao dịch nào trong ví này</div>
        @endforelse
        @if($transactions->hasPages())
        <div class="pagination-wrap">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>

{{-- Tab: Chuyển ví --}}
<div class="tab-content" id="tab-transfers">
    <div class="section-card">
        <div class="sc-hdr">
            <div class="sc-title">Lịch sử chuyển tiền</div>
            <a href="{{ route('wallet-transfers.index') }}" style="font-size:12px;color:var(--primary);font-weight:700;text-decoration:none;">Trang chuyển tiền →</a>
        </div>
        @forelse($transfers as $tf)
        @php
            $isFrom = $tf->from_wallet_id === $moneyWallet->id;
        @endphp
        <div class="tf-item">
            <div class="tf-av">{{ $isFrom ? '↗' : '↙' }}</div>
            <div class="tf-desc">
                <div class="tf-names">
                    @if($isFrom)
                        → {{ $tf->toWallet?->ten_vi ?? '?' }}
                    @else
                        ← {{ $tf->fromWallet?->ten_vi ?? '?' }}
                    @endif
                </div>
                <div class="tf-date">
                    {{ \Carbon\Carbon::parse($tf->ngay_chuyen)->format('d/m/Y') }}
                    @if($tf->ghi_chu) · {{ Str::limit($tf->ghi_chu, 40) }} @endif
                </div>
            </div>
            <div class="tf-amount" style="color:{{ $isFrom ? '#ef4444' : '#10b981' }}">
                {{ $isFrom ? '-' : '+' }}{{ number_format($tf->so_tien) }}đ
            </div>
        </div>
        @empty
        <div class="empty-msg">↔️ Chưa có giao dịch chuyển tiền nào</div>
        @endforelse
    </div>
</div>

{{-- Tab: Điều chỉnh --}}
<div class="tab-content" id="tab-adjustments">
    <div class="section-card">
        <div class="sc-hdr">
            <div class="sc-title">Lịch sử điều chỉnh số dư</div>
        </div>
        @forelse($adjustments as $adj)
        @php $isPos = $adj->chenh_lech > 0; @endphp
        <div class="adj-item">
            <div class="adj-icon {{ $isPos ? 'pos' : 'neg' }}">
                {{ $isPos ? '↑' : '↓' }}
            </div>
            <div style="flex:1;">
                <div style="font-size:13px;font-weight:700;color:#1f2937;">
                    @if($isPos) Tăng số dư @else Giảm số dư @endif
                    <span style="color:{{ $isPos ? '#10b981' : '#ef4444' }}">
                        {{ $isPos ? '+' : '' }}{{ number_format($adj->chenh_lech) }}đ
                    </span>
                </div>
                <div style="font-size:12px;color:#9ca3af;">
                    {{ number_format($adj->so_du_truoc) }}đ → {{ number_format($adj->so_du_sau) }}đ
                    @if($adj->ly_do) · {{ $adj->ly_do }} @endif
                </div>
            </div>
            <div style="font-size:12px;color:#9ca3af;white-space:nowrap;">
                {{ $adj->created_at->format('d/m/Y') }}
            </div>
        </div>
        @empty
        <div class="empty-msg">⚖️ Chưa có điều chỉnh nào</div>
        @endforelse
    </div>
</div>

{{-- ═══ MODAL: Điều chỉnh số dư ═══ --}}
<div class="modal-overlay" id="adjustModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">⚖️ Điều chỉnh số dư</div>
            <button class="modal-close-btn" onclick="closeModal('adjustModal')">✕</button>
        </div>
        <form action="{{ route('money-wallets.adjust', $moneyWallet) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="background:#f0f7ff;border-radius:10px;padding:14px;margin-bottom:16px;font-size:13px;">
                    <div style="font-weight:700;color:#1e40af;margin-bottom:4px;">💡 Cách hoạt động</div>
                    <div style="color:#4b5563;">Nhập số tiền thực tế bạn đang có. Hệ thống sẽ tự động tạo giao dịch bù trừ để khớp số dư.</div>
                    <div style="margin-top:8px;font-weight:700;color:#374151;">
                        Số dư hiện tại: <span style="color:{{ $color }}">{{ number_format($moneyWallet->so_du) }} {{ $moneyWallet->don_vi_tien_te }}</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Số dư thực tế <span class="required">*</span></label>
                    <input name="so_du_thuc_te" type="number" class="form-ctrl"
                        placeholder="Nhập số tiền thực tế đang có" min="0" step="1000"
                        value="{{ old('so_du_thuc_te', $moneyWallet->so_du) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Danh mục giao dịch <span class="required">*</span></label>
                    <select name="category_id" class="form-ctrl" required>
                        <option value="">-- Chọn danh mục --</option>
                        @php
                            $adjCats = \App\Models\Category::where(function($q) {
                                $q->where('user_id', Auth::id())->orWhereNull('user_id');
                            })->whereNotNull('danh_muc_cha_id')
                              ->orderBy('loai_danh_muc')
                              ->orderBy('ten_danh_muc')
                              ->get()
                              ->groupBy('loai_danh_muc');
                        @endphp
                        @foreach($adjCats as $loai => $cats)
                        <optgroup label="{{ $loai === 'THU' ? 'Thu nhập' : 'Chi tiêu' }}">
                            @foreach($cats as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->ten_danh_muc }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Lý do điều chỉnh</label>
                    <input name="ly_do" class="form-ctrl" placeholder="VD: Đếm tiền mặt, đối soát sao kê..." maxlength="255">
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="closeModal('adjustModal')">Hủy</button>
                <button type="submit" class="btn-primary">Xác nhận điều chỉnh</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ MODAL: Sửa ví (inline) ═══ --}}
<div class="modal-overlay" id="editShowModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-title">✏️ Chỉnh sửa ví</div>
            <button class="modal-close-btn" onclick="closeModal('editShowModal')">✕</button>
        </div>
        <form action="{{ route('money-wallets.update', $moneyWallet) }}" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tên ví <span class="required">*</span></label>
                    <input name="ten_vi" class="form-ctrl" value="{{ $moneyWallet->ten_vi }}" required maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Loại ví</label>
                    <select name="loai_vi" class="form-ctrl">
                        @foreach(['tien_mat'=>'💵 Tiền mặt','ngan_hang'=>'🏦 Ngân hàng','vi_dien_tu'=>'📱 Ví điện tử','the_tin_dung'=>'💳 Thẻ tín dụng','dau_tu'=>'📈 Đầu tư','khac'=>'🗂 Khác'] as $val=>$label)
                        <option value="{{ $val }}" {{ $moneyWallet->loai_vi===$val?'selected':'' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Đơn vị tiền tệ</label>
                    <select name="don_vi_tien_te" class="form-ctrl">
                        @foreach(['VND','USD','EUR','JPY','KRW','SGD'] as $c)
                        <option value="{{ $c }}" {{ $moneyWallet->don_vi_tien_te===$c?'selected':'' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Biểu tượng</label>
                    <input name="bieu_tuong" class="form-ctrl" value="{{ $moneyWallet->bieu_tuong }}" maxlength="10" placeholder="Nhập emoji...">
                </div>
                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <input name="mo_ta" class="form-ctrl" value="{{ $moneyWallet->mo_ta }}" maxlength="500">
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="closeModal('editShowModal')">Hủy</button>
                <button type="submit" class="btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}
function openAdjust()     { document.getElementById('adjustModal').classList.add('active'); }
function openEditFromShow(){ document.getElementById('editShowModal').classList.add('active'); }
function closeModal(id)    { document.getElementById(id).classList.remove('active'); }

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if(e.target===o) o.classList.remove('active'); });
});

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition='opacity .3s'; a.style.opacity='0';
        setTimeout(()=>a.remove(),320);
    });
}, 4500);
</script>
@endsection
