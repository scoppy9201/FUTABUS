<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 32px; }
        .title { font-size: 20px; font-weight: bold; color: #1a1a1a; margin-bottom: 8px; }
        .text { color: #444; line-height: 1.6; margin-bottom: 16px; }
        .btn { display: inline-block; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-right: 12px; }
        .btn-accept  { background: #16a34a; color: #fff; }
        .btn-decline { background: #f5f5f5; color: #555; border: 1px solid #ddd; }
        .note { font-size: 12px; color: #888; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <p class="title">Lời mời tham gia nhóm</p>
        <p class="text">
            <strong>{{ $inviterName }}</strong> mời bạn tham gia nhóm
            <strong>"{{ $groupName }}"</strong> trên Monexa.
        </p>
        <p class="text">
            Nhóm này dùng để quản lý và chia sẻ chi tiêu cùng nhau.
            Bạn có muốn tham gia không?
        </p>

        <a href="{{ $acceptUrl }}"  class="btn btn-accept">Chấp nhận</a>
        <a href="{{ $declineUrl }}" class="btn btn-decline">Từ chối</a>

        <p class="note">
            Lời mời hết hạn lúc {{ $expiresAt }}.<br>
            Nếu bạn không biết về lời mời này, hãy bỏ qua email này.
        </p>
    </div>
</body>
</html>
