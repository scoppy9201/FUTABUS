@extends('layouts.app')
@section('title', 'Xác nhận nhận tiền')
@section('content')
<style>
.scan-confirm-wrap {
    max-width: 460px; margin: 0 auto;
    background: white; border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;
}
body.dark .scan-confirm-wrap { background: #191d27; }
.sc-hdr {
    padding: 24px 28px;
    background: linear-gradient(135deg, #065f46, #10b981);
    text-align: center; color: white;
}
.sc-hdr .amount { font-size: 40px; font-weight: 900; margin: 12px 0 4px; letter-spacing: -2px; }
.sc-hdr .from   { font-size: 14px; opacity: .9; }
.sc-body { padding: 28px; }
.info-box { background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 20px; }
body.dark .info-box { background: rgba(255,255,255,.04); }
.info-row { display: flex; justify-content: space-between; padding: 7px 0; font-size: 14px; }
.info-row + .info-row { border-top: 1px solid #f0f0f0; }
body.dark .info-row + .info-row { border-color: rgba(255,255,255,.06); }
.info-row .lbl { color: #9ca3af; font-weight: 600; }
.info-row .val { font-weight: 700; color: #1f2937; }
body.dark .info-row .val { color: #e5e7eb; }
.form-group { margin-bottom: 20px; }
.form-label { font-size: 13px; font-weight: 700; color: #374151; display: block; margin-bottom: 7px; }
body.dark .form-label { color: #9ca3af; }
.form-ctrl {
    width: 100%; padding: 11px 14px;
    border: 2px solid #e5e7eb; border-radius: 10px;
    font-size: 14px; background: #f9fafb; color: #1f2937; outline: none; transition: all .2s;
}
.form-ctrl:focus { border-color: #10b981; background: white; }
body.dark .form-ctrl { background: #141820; border-color: rgba(255,255,255,.1); color: #e5e7eb; }
.btn-confirm {
    width: 100%; padding: 14px; border-radius: 12px;
    background: linear-gradient(135deg, #059669, #10b981);
    color: white; font-size: 16px; font-weight: 800;
    border: none; cursor: pointer; transition: opacity .2s;
}
.btn-confirm:hover { opacity: .9; }
.btn-back-link {
    display: block; text-align: center; margin-top: 16px;
    color: #9ca3af; font-size: 13px; text-decoration: none;
}
.btn-back-link:hover { color: var(--primary); }
</style>

<div style="max-width:460px;margin:0 auto;">
    <h1 style="font-size:20px;font-weight:800;color:#1f2937;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
        📥 Nhận tiền QR
    </h1>
</div>

<div class="scan-confirm-wrap">
    <div class="sc-hdr">
        <div style="font-size:13px;opacity:.85;">Bạn được chuyển</div>
        <div class="amount">+{{ number_format($qrTransfer->so_tien) }}đ</div>
        <div class="from">từ {{ $qrTransfer->sender->name }}</div>
    </div>
    <div class="sc-body">
        <div class="info-box">
            <div class="info-row">
                <span class="lbl">Người gửi</span>
                <span class="val">{{ $qrTransfer->sender->name }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Ví gửi</span>
                <span class="val">{{ $qrTransfer->senderWallet->bieu_tuong }} {{ $qrTransfer->senderWallet->ten_vi }}</span>
            </div>
            @if($qrTransfer->ghi_chu)
            <div class="info-row">
                <span class="lbl">Ghi chú</span>
                <span class="val">{{ $qrTransfer->ghi_chu }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="lbl">Hết hạn lúc</span>
                <span class="val">{{ $qrTransfer->expires_at->format('H:i d/m/Y') }}</span>
            </div>
        </div>

        @if($myWallets->isEmpty())
            <div style="text-align:center;color:#9ca3af;padding:20px;">
                Bạn chưa có ví nào để nhận tiền. <a href="{{ route('money-wallets.index') }}">Tạo ví ngay</a>
            </div>
        @else
        <form action="{{ route('money-wallets.qr.confirm', $token) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Chọn ví nhận <span style="color:#ef4444;">*</span></label>
                <select name="receiver_wallet_id" class="form-ctrl" required>
                    <option value="">-- Chọn ví --</option>
                    @foreach($myWallets as $w)
                    <option value="{{ $w->id }}">
                        {{ $w->bieu_tuong }} {{ $w->ten_vi }} — {{ number_format($w->so_du) }}đ
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-confirm">✅ Xác nhận nhận tiền</button>
        </form>
        @endif

        <a href="{{ route('money-wallets.qr.index') }}" class="btn-back-link">← Quay lại</a>
    </div>
</div>
@endsection
