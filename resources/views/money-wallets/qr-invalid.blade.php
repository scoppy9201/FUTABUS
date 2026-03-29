@extends('layouts.app')
@section('title', 'QR không hợp lệ')
@section('content')
<div style="max-width:400px;margin:60px auto;text-align:center;background:white;border-radius:20px;padding:48px 32px;box-shadow:0 8px 32px rgba(0,0,0,.1);">
    <div style="font-size:64px;margin-bottom:16px;">❌</div>
    <h2 style="font-size:20px;font-weight:800;color:#1f2937;margin-bottom:12px;">QR không hợp lệ</h2>
    <p style="color:#9ca3af;font-size:14px;line-height:1.7;margin-bottom:28px;">{{ $msg }}</p>
    <a href="{{ route('money-wallets.qr.index') }}"
       style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:10px;background:linear-gradient(135deg,#4a90e2,#2a5298);color:white;text-decoration:none;font-weight:700;font-size:14px;">
        Về trang QR Transfer
    </a>
</div>
@endsection
