@extends('layouts.app')
@section('title', 'Chuyển tiền giữa ví')
@section('content')
<style>
:root {
    --primary:#4a90e2;--primary-dark:#2a5298;
    --success:#10b981;--danger:#ef4444;--warning:#f59e0b;
    --radius:16px;--radius-sm:10px;
    --shadow:0 2px 12px rgba(0,0,0,0.06);
    --transition:all 0.25s cubic-bezier(0.4,0,0.2,1);
}

.page-hdr { display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;padding:20px 26px;background:white;border-radius:var(--radius);box-shadow:var(--shadow); }
body.dark .page-hdr { background:#191d27; }
.page-title { font-size:20px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:10px; }
body.dark .page-title { color:#e5e7eb; }

.alert { display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:var(--radius-sm);font-size:14px;font-weight:500;margin-bottom:20px; }
.alert-success { background:#d1fae5;color:#065f46;border-left:4px solid var(--success); }
.alert-error   { background:#fee2e2;color:#991b1b;border-left:4px solid var(--danger); }

/* Main grid */
.main-grid { display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start; }

/* Transfer form */
.form-card { background:white;border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden; }
body.dark .form-card { background:#191d27; }
.fc-hdr { padding:18px 22px;border-bottom:1px solid #f3f4f6;background:linear-gradient(135deg,rgba(74,144,226,0.06),transparent); }
body.dark .fc-hdr { border-color:rgba(255,255,255,0.06); }
.fc-title { font-size:15px;font-weight:800;color:#1f2937;display:flex;align-items:center;gap:8px; }
body.dark .fc-title { color:#e5e7eb; }
.fc-body { padding:22px; }

.form-group { margin-bottom:16px; }
.form-label { font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:7px; }
body.dark .form-label { color:#9ca3af; }
.required { color:var(--danger); }
.form-ctrl { width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:var(--radius-sm);font-size:14px;background:#f9fafb;color:#1f2937;outline:none;transition:all .2s; }
.form-ctrl:focus { border-color:var(--primary);background:white; }
body.dark .form-ctrl { background:#141820;border-color:rgba(255,255,255,0.1);color:#e5e7eb; }

/* Wallet select cards */
.wallet-select-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:6px; }
.ws-card {
    border:2px solid #e5e7eb;border-radius:12px;padding:12px 14px;
    cursor:pointer;transition:var(--transition);display:flex;align-items:center;gap:10px;
}
.ws-card:hover { border-color:var(--primary); }
.ws-card.selected { border-color:var(--primary);background:rgba(74,144,226,0.05); }
.ws-card.disabled-card { opacity:.4;pointer-events:none; }
body.dark .ws-card { border-color:rgba(255,255,255,0.1); }
body.dark .ws-card.selected { background:rgba(74,144,226,0.1); }
.ws-card input { display:none; }
.ws-icon { font-size:22px;flex-shrink:0; }
.ws-info {}
.ws-name { font-size:13px;font-weight:700;color:#1f2937; }
body.dark .ws-name { color:#e5e7eb; }
.ws-balance { font-size:11px;font-weight:600;color:#9ca3af; }

/* Transfer preview */
.transfer-preview {
    background:linear-gradient(135deg,rgba(74,144,226,0.06),rgba(42,82,152,0.04));
    border:1px solid rgba(74,144,226,0.2);border-radius:12px;
    padding:16px;margin-bottom:16px;display:flex;align-items:center;
    gap:12px;min-height:60px;
}
.preview-from,.preview-to { text-align:center;flex:1;min-width:0; }
.preview-wallet-name { font-size:13px;font-weight:700;color:#1f2937; }
body.dark .preview-wallet-name { color:#e5e7eb; }
.preview-wallet-bal { font-size:11px;color:#9ca3af; }
.preview-arrow { font-size:28px;color:var(--primary);flex-shrink:0; }
.preview-amount { font-size:14px;font-weight:900;color:var(--primary); }
.preview-placeholder { font-size:13px;color:#9ca3af;text-align:center;width:100%; }

/* Section card */
.section-card { background:white;border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden; }
body.dark .section-card { background:#191d27; }
.sc-hdr { padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center; }
body.dark .sc-hdr { border-color:rgba(255,255,255,0.06); }
.sc-title { font-size:15px;font-weight:800;color:#1f2937; }
body.dark .sc-title { color:#e5e7eb; }

/* Transfer history item */
.tf-item { display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid #f9fafb;transition:background .15s; }
body.dark .tf-item { border-color:rgba(255,255,255,0.03); }
.tf-item:last-child { border-bottom:none; }
.tf-item:hover { background:#f9fafb; }
body.dark .tf-item:hover { background:rgba(255,255,255,0.02); }
.tf-icon { width:40px;height:40px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
.tf-info { flex:1;min-width:0; }
.tf-route { font-size:14px;font-weight:700;color:#1f2937; }
body.dark .tf-route { color:#e5e7eb; }
.tf-meta { font-size:12px;color:#9ca3af;margin-top:2px; }
.tf-right { text-align:right;flex-shrink:0; }
.tf-amount { font-size:16px;font-weight:900;color:var(--primary); }
.tf-date { font-size:11px;color:#9ca3af;margin-top:2px; }
.tf-cancel-btn {
    padding:4px 10px;border-radius:8px;border:1px solid #e5e7eb;
    background:white;color:#9ca3af;font-size:11px;font-weight:600;cursor:pointer;
    transition:all .15s;margin-top:4px;
}
.tf-cancel-btn:hover { border-color:var(--danger);color:var(--danger);background:#fee2e2; }

.empty-msg { text-align:center;padding:40px;color:#9ca3af;font-size:13px; }

/* Buttons */
.btn-primary { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-sm);background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;transition:opacity .2s; }
.btn-primary:hover { opacity:.88; }
.btn-outline { display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:var(--radius-sm);border:2px solid var(--primary);color:var(--primary);background:transparent;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;transition:all .2s; }
.btn-outline:hover { background:var(--primary);color:white; }

/* Phi hint */
.phi-hint { font-size:12px;color:#9ca3af;margin-top:5px;display:flex;align-items:center;gap:4px; }

@media (max-width:1100px) { .main-grid { grid-template-columns:1fr; } }
@media (max-width:768px) { .wallet-select-grid { grid-template-columns:1fr; } }
</style>

<div class="page-hdr">
    <div class="page-title">↔️ Chuyển tiền giữa ví</div>
    <a href="{{ route('money-wallets.index') }}" class="btn-outline">← Quay lại ví</a>
</div>

@if(session('success'))<div class="alert alert-success">✓ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">⚠ {{ session('error') }}</div>@endif

<div class="main-grid">
    {{-- LEFT: Form chuyển tiền --}}
    <div>
        <div class="form-card">
            <div class="fc-hdr"><div class="fc-title">↔️ Tạo giao dịch chuyển tiền</div></div>
            <div class="fc-body">
                @if($wallets->count() < 2)
                <div style="text-align:center;padding:40px;color:#9ca3af;">
                    <div style="font-size:40px;margin-bottom:12px;">💳</div>
                    <div style="font-weight:700;margin-bottom:8px;">Cần ít nhất 2 ví</div>
                    <div style="font-size:13px;margin-bottom:20px;">Tạo thêm ví để chuyển tiền giữa các nguồn</div>
                    <a href="{{ route('money-wallets.index') }}" class="btn-primary">+ Thêm ví</a>
                </div>
                @else
                <form action="{{ route('wallet-transfers.store') }}" method="POST" id="transferForm">
                    @csrf

                    {{-- Ví nguồn --}}
                    <div class="form-group">
                        <label class="form-label">Từ ví <span class="required">*</span></label>
                        <div class="wallet-select-grid" id="fromGrid">
                            @foreach($wallets as $w)
                            <label class="ws-card" onclick="selectWallet('from', {{ $w->id }}, this)" data-wallet-id="{{ $w->id }}">
                                <input type="radio" name="from_wallet_id" value="{{ $w->id }}" {{ old('from_wallet_id')==$w->id?'checked':'' }}>
                                <div class="ws-icon">{{ $w->bieu_tuong }}</div>
                                <div class="ws-info">
                                    <div class="ws-name">{{ $w->ten_vi }}</div>
                                    <div class="ws-balance">{{ number_format($w->so_du) }} {{ $w->don_vi_tien_te }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Ví đích --}}
                    <div class="form-group">
                        <label class="form-label">Đến ví <span class="required">*</span></label>
                        <div class="wallet-select-grid" id="toGrid">
                            @foreach($wallets as $w)
                            <label class="ws-card" onclick="selectWallet('to', {{ $w->id }}, this)" data-wallet-id="{{ $w->id }}">
                                <input type="radio" name="to_wallet_id" value="{{ $w->id }}" {{ old('to_wallet_id')==$w->id?'checked':'' }}>
                                <div class="ws-icon">{{ $w->bieu_tuong }}</div>
                                <div class="ws-info">
                                    <div class="ws-name">{{ $w->ten_vi }}</div>
                                    <div class="ws-balance">{{ number_format($w->so_du) }} {{ $w->don_vi_tien_te }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div class="transfer-preview" id="transferPreview">
                        <div class="preview-placeholder">Chọn ví nguồn và ví đích để xem trước</div>
                    </div>

                    {{-- Số tiền --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Số tiền <span class="required">*</span></label>
                            <input name="so_tien" type="number" class="form-ctrl"
                                placeholder="0" min="1000" step="1000"
                                value="{{ old('so_tien') }}" required
                                oninput="updatePreviewAmount(this.value)">
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Phí chuyển</label>
                            <input name="phi_chuyen" type="number" class="form-ctrl"
                                placeholder="0 (nếu có)" min="0" step="1000"
                                value="{{ old('phi_chuyen', 0) }}">
                            <div class="phi-hint">💡 Phí sẽ bị trừ thêm từ ví nguồn</div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px;">
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Ngày <span class="required">*</span></label>
                            <input name="ngay_chuyen" type="date" class="form-ctrl"
                                value="{{ old('ngay_chuyen', date('Y-m-d')) }}" required max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Danh mục <span class="required">*</span></label>
                            <select name="category_id" class="form-ctrl" required>
                                <option value="">-- Chọn --</option>
                                @foreach($categories->groupBy('loai_danh_muc') as $loai => $cats)
                                <optgroup label="{{ $loai === 'THU' ? 'Thu nhập' : 'Chi tiêu' }}">
                                    @foreach($cats as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'selected':'' }}>
                                        {{ $cat->ten_danh_muc }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:16px;">
                        <label class="form-label">Ghi chú</label>
                        <input name="ghi_chu" class="form-ctrl" placeholder="Lý do chuyển tiền..." maxlength="500">
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px;margin-top:4px;">
                        ↔️ Chuyển tiền
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- RIGHT: Lịch sử --}}
    <div>
        <div class="section-card">
            <div class="sc-hdr">
                <div class="sc-title">Lịch sử chuyển tiền</div>
            </div>
            @forelse($transfers as $tf)
            <div class="tf-item">
                <div class="tf-icon">↔️</div>
                <div class="tf-info">
                    <div class="tf-route">
                        {{ $tf->fromWallet?->bieu_tuong }} {{ $tf->fromWallet?->ten_vi ?? '?' }}
                        → {{ $tf->toWallet?->bieu_tuong }} {{ $tf->toWallet?->ten_vi ?? '?' }}
                    </div>
                    <div class="tf-meta">
                        {{ \Carbon\Carbon::parse($tf->ngay_chuyen)->format('d/m/Y') }}
                        @if($tf->phi_chuyen > 0) · Phí: {{ number_format($tf->phi_chuyen) }}đ @endif
                        @if($tf->ghi_chu) · {{ Str::limit($tf->ghi_chu, 30) }} @endif
                    </div>
                    <form action="{{ route('wallet-transfers.destroy', $tf) }}" method="POST"
                          onsubmit="return confirm('Hoàn tác giao dịch chuyển tiền này?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="tf-cancel-btn">↩ Hoàn tác</button>
                    </form>
                </div>
                <div class="tf-right">
                    <div class="tf-amount">{{ number_format($tf->so_tien) }}đ</div>
                    <div class="tf-date">{{ $tf->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div class="empty-msg">↔️ Chưa có giao dịch chuyển tiền nào</div>
            @endforelse
            @if($transfers->hasPages())
            <div style="padding:14px 20px;border-top:1px solid #f3f4f6;">{{ $transfers->links() }}</div>
            @endif
        </div>
    </div>
</div>

<script>
@php $walletsJson = $wallets->map(fn($w) => ['id'=>$w->id,'ten_vi'=>$w->ten_vi,'bieu_tuong'=>$w->bieu_tuong,'so_du'=>$w->so_du,'don_vi_tien_te'=>$w->don_vi_tien_te])->values(); @endphp
const wallets = @json($walletsJson);
let selectedFrom = null, selectedTo = null, previewAmount = 0;

function selectWallet(type, walletId, el) {
    const gridId = type === 'from' ? 'fromGrid' : 'toGrid';
    document.querySelectorAll(`#${gridId} .ws-card`).forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;

    if (type === 'from') selectedFrom = walletId;
    else selectedTo = walletId;

    // Disable ví đã chọn ở grid kia
    const otherGrid = type === 'from' ? 'toGrid' : 'fromGrid';
    document.querySelectorAll(`#${otherGrid} .ws-card`).forEach(c => {
        const same = parseInt(c.dataset.walletId) === walletId;
        c.classList.toggle('disabled-card', same);
        if (same) { c.classList.remove('selected'); c.querySelector('input').checked = false; if(type==='from') selectedTo=null; else selectedFrom=null; }
    });

    updatePreview();
}

function updatePreviewAmount(val) {
    previewAmount = parseFloat(val) || 0;
    updatePreview();
}

function updatePreview() {
    const preview = document.getElementById('transferPreview');
    const from = wallets.find(w => w.id === selectedFrom);
    const to   = wallets.find(w => w.id === selectedTo);

    if (!from && !to) {
        preview.innerHTML = '<div class="preview-placeholder">Chọn ví nguồn và ví đích để xem trước</div>';
        return;
    }

    const fromHtml = from
        ? `<div class="preview-from"><div style="font-size:28px;">${from.bieu_tuong}</div><div class="preview-wallet-name">${from.ten_vi}</div><div class="preview-wallet-bal">${from.so_du.toLocaleString('vi-VN')}đ</div></div>`
        : `<div class="preview-from" style="opacity:.4;">Chọn ví nguồn</div>`;

    const toHtml = to
        ? `<div class="preview-to"><div style="font-size:28px;">${to.bieu_tuong}</div><div class="preview-wallet-name">${to.ten_vi}</div><div class="preview-wallet-bal">${to.so_du.toLocaleString('vi-VN')}đ</div></div>`
        : `<div class="preview-to" style="opacity:.4;">Chọn ví đích</div>`;

    const amountHtml = previewAmount > 0
        ? `<div><div class="preview-arrow">→</div><div class="preview-amount">${previewAmount.toLocaleString('vi-VN')}đ</div></div>`
        : `<div class="preview-arrow">→</div>`;

    preview.innerHTML = fromHtml + amountHtml + toHtml;
}

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition='opacity .3s'; a.style.opacity='0';
        setTimeout(()=>a.remove(),320);
    });
}, 4500);
</script>
@endsection
