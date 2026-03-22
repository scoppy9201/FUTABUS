<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lời mời tham gia nhóm - Monexa</title>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:40px 20px;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;">

    {{-- LOGO --}}
    <tr><td align="center" style="padding-bottom:24px;">
        <div style="background:linear-gradient(135deg,#4a90e2,#2a5298);border-radius:16px;padding:14px 28px;display:inline-block;">
            <span style="font-size:22px;font-weight:900;color:white;letter-spacing:-0.5px;">💰 Monexa</span>
        </div>
    </td></tr>

    {{-- MAIN CARD --}}
    <tr><td style="background:white;border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,0.08);overflow:hidden;">

        {{-- Banner --}}
        <div style="background:linear-gradient(135deg,#4a90e2 0%,#2a5298 100%);padding:36px 40px 32px;text-align:center;">
            <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.2);margin:0 auto 16px;font-size:30px;line-height:64px;">👥</div>
            <p style="margin:0;font-size:22px;font-weight:800;color:white;letter-spacing:-0.3px;">Bạn được mời vào nhóm!</p>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.75);">Quản lý chi tiêu chung trên Monexa</p>
        </div>

        {{-- Body --}}
        <div style="padding:36px 40px;">

            <p style="margin:0 0 20px;font-size:15px;color:#374151;line-height:1.6;">Xin chào,</p>
            <p style="margin:0 0 24px;font-size:15px;color:#374151;line-height:1.6;">
                <strong style="color:#1f2937;">{{ $inviterName }}</strong>
                đã mời bạn tham gia nhóm chi tiêu chung trên Monexa.
            </p>

            {{-- Group box --}}
            <div style="background:linear-gradient(135deg,rgba(74,144,226,0.06),rgba(42,82,152,0.04));border:2px solid rgba(74,144,226,0.2);border-radius:14px;padding:20px 24px;margin-bottom:28px;">
                <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.8px;">Tên nhóm</p>
                <p style="margin:0;font-size:20px;font-weight:800;color:#2a5298;">{{ $groupName }}</p>
                <p style="margin:8px 0 0;font-size:13px;color:#6b7280;">🧾 Chia sẻ và quản lý chi tiêu cùng nhau</p>
            </div>

            {{-- Buttons --}}
            <div style="text-align:center;margin-bottom:28px;">
                <a href="{{ $acceptUrl }}" style="display:block;width:100%;max-width:360px;margin:0 auto 12px;padding:15px 32px;border-radius:12px;background:linear-gradient(135deg,#4a90e2,#2a5298);color:white;font-size:15px;font-weight:700;text-decoration:none;text-align:center;box-shadow:0 4px 14px rgba(74,144,226,0.35);box-sizing:border-box;">
                    ✓ Chấp nhận lời mời
                </a>
                <a href="{{ $declineUrl }}" style="display:block;width:100%;max-width:360px;margin:0 auto;padding:13px 32px;border-radius:12px;background:white;color:#6b7280;font-size:14px;font-weight:600;text-decoration:none;text-align:center;border:2px solid #e5e7eb;box-sizing:border-box;">
                    Từ chối
                </a>
            </div>

            {{-- Info chips --}}
            <div style="border-top:1px solid #f3f4f6;padding-top:20px;display:flex;gap:12px;margin-bottom:20px;">
                <div style="flex:1;background:#f9fafb;border-radius:10px;padding:12px 16px;text-align:center;">
                    <p style="margin:0 0 2px;font-size:18px;">⏰</p>
                    <p style="margin:0;font-size:11px;font-weight:700;color:#6b7280;">Hết hạn lúc</p>
                    <p style="margin:2px 0 0;font-size:12px;font-weight:800;color:#1f2937;">{{ $expiresAt }}</p>
                </div>
                <div style="flex:1;background:#f9fafb;border-radius:10px;padding:12px 16px;text-align:center;">
                    <p style="margin:0 0 2px;font-size:18px;">🔒</p>
                    <p style="margin:0;font-size:11px;font-weight:700;color:#6b7280;">Bảo mật</p>
                    <p style="margin:2px 0 0;font-size:12px;font-weight:800;color:#1f2937;">Link 1 lần dùng</p>
                </div>
                <div style="flex:1;background:#f9fafb;border-radius:10px;padding:12px 16px;text-align:center;">
                    <p style="margin:0 0 2px;font-size:18px;">✅</p>
                    <p style="margin:0;font-size:11px;font-weight:700;color:#6b7280;">Miễn phí</p>
                    <p style="margin:2px 0 0;font-size:12px;font-weight:800;color:#1f2937;">Không mất phí</p>
                </div>
            </div>

            <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.6;text-align:center;">
                Nếu bạn không biết về lời mời này, hãy bỏ qua email này.<br>
                Không ai có thể vào nhóm thay bạn nếu bạn không nhấn chấp nhận.
            </p>
        </div>
    </td></tr>

    {{-- FOOTER --}}
    <tr><td style="padding:24px 0 0;text-align:center;">
        <p style="margin:0 0 4px;font-size:13px;font-weight:700;color:#4a90e2;">Monexa</p>
        <p style="margin:0;font-size:12px;color:#9ca3af;">Ứng dụng quản lý chi tiêu cá nhân và nhóm</p>
    </td></tr>

</table>
</td></tr>
</table>

</body>
</html>
