<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quên mật khẩu Monexa</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:300,400,500,600,700,800" rel="stylesheet" />
    @vite('resources/js/app.js')
</head>
<body class="auth-page">
    <div class="auth-shell">
        <div class="auth-backdrop auth-backdrop--one"></div>
        <div class="auth-backdrop auth-backdrop--two"></div>
        <div class="auth-grid-lines"></div>

        <header class="auth-topbar">
            <a href="{{ url('/') }}" class="footer-logo">
                <div class="footer-logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 12l10 10 10-10L12 2z"></path>
                        <path d="M8 12l3-3 5 5"></path>
                    </svg>
                </div>
                <span class="logo-wordmark">Mon<em>exa</em></span>
            </a>

            <div class="auth-topbar-actions">
                <a href="{{ url('/') }}" class="auth-top-link">Về trang chủ</a>
                <a href="{{ route('login') }}" class="auth-top-link">Đăng nhập</a>
            </div>
        </header>

        <main class="recovery-shell">
            @if (session('success'))
                <div class="auth-notice auth-notice--success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="auth-notice auth-notice--error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <section class="recovery-layout">
                <aside class="recovery-aside">
                    <div class="recovery-aside-inner">
                        <p class="recovery-kicker">Khôi phục tài khoản</p>
                        <h1 class="recovery-title">Lấy lại quyền truy cập vào Monexa chỉ với email đã đăng ký.</h1>
                        <div class="recovery-hero-icon" aria-hidden="true">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="14" rx="3"></rect>
                                <path d="m3 7 9 6 9-6"></path>
                            </svg>
                        </div>
                        <p class="recovery-copy">Nhập email của bạn, hệ thống sẽ gửi mã xác thực 6 số để tiếp tục quá trình đặt lại mật khẩu theo flow bảo mật hiện tại.</p>

                        <div class="recovery-points">
                            <div class="recovery-point">
                                <div class="recovery-point-badge">01</div>
                                <div class="recovery-point-text">
                                    <strong>Nhập email đăng ký</strong>
                                    <span>Hệ thống kiểm tra tài khoản và tạo mã xác thực riêng cho bạn.</span>
                                </div>
                            </div>
                            <div class="recovery-point">
                                <div class="recovery-point-badge">02</div>
                                <div class="recovery-point-text">
                                    <strong>Nhận mã 6 số qua email</strong>
                                    <span>Mã có hiệu lực ngắn để đảm bảo luồng khôi phục an toàn hơn.</span>
                                </div>
                            </div>
                            <div class="recovery-point">
                                <div class="recovery-point-badge">03</div>
                                <div class="recovery-point-text">
                                    <strong>Đặt mật khẩu mới</strong>
                                    <span>Sau khi xác thực thành công, bạn có thể tạo mật khẩu mới ngay.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="recovery-card">
                    <div class="recovery-card-head">
                        <p class="auth-kicker">Quên mật khẩu</p>
                        <h2 class="auth-card-title">Gửi mã xác thực</h2>
                        <p class="recovery-subtitle">Nhập email của bạn và Monexa sẽ gửi mã xác thực để tiếp tục đặt lại mật khẩu.</p>
                    </div>

                    <form action="{{ route('password.email') }}" method="POST" class="recovery-form" novalidate>
                        @csrf

                        <div class="auth-field">
                            <label for="recovery-email">Email đã đăng ký</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                        <path d="m3 7 9 6 9-6"></path>
                                    </svg>
                                </span>
                                <input id="recovery-email" type="email" name="email" value="{{ old('email') }}" placeholder="example@gmail.com" autocomplete="email" required autofocus>
                            </div>
                            @error('email')
                                <p class="auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="auth-submit">Gửi mã xác thực</button>

                        <div class="recovery-info">
                            <strong>Lưu ý:</strong> Mã xác thực sẽ được gửi tới email của bạn và chỉ có hiệu lực trong 3 phút.
                        </div>

                        <div class="recovery-inline-actions">
                            <a href="{{ route('login') }}" class="recovery-back-link">Quay lại đăng nhập</a>
                        </div>
                    </form>
                </section>
            </section>
        </main>
    </div>
    @include('layouts.partials.monebot')
</body>
</html>
