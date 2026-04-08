@php
    $isRegister = request()->routeIs('register');
    $initialMode = old('auth_mode', $isRegister ? 'register' : 'login');
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isRegister ? 'Tạo tài khoản Monexa' : 'Đăng nhập Monexa' }}</title>
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
                <a href="{{ route('password.request') }}" class="auth-top-link">Quên mật khẩu</a>
            </div>
        </header>

        <main class="auth-stage">
            @if (session('success'))
                <div class="auth-notice auth-notice--success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="auth-notice auth-notice--error">{{ session('error') }}</div>
            @endif

            <div class="auth-mobile-tabs" role="tablist" aria-label="Chuyển đổi biểu mẫu">
                <button type="button" class="auth-tab {{ $initialMode === 'login' ? 'is-active' : '' }}" data-mode="login">Đăng nhập</button>
                <button type="button" class="auth-tab {{ $initialMode === 'register' ? 'is-active' : '' }}" data-mode="register">Đăng ký</button>
            </div>

            <section class="auth-frame {{ $initialMode === 'register' ? 'is-register' : '' }}" id="authFrame">
                <div class="auth-pane auth-pane--signin">
                    <form action="{{ route('login') }}" method="POST" class="auth-form auth-form--pane" novalidate>
                        @csrf
                        <input type="hidden" name="auth_mode" value="login">
                        <div class="auth-form-head">
                            <p class="auth-kicker">Tài khoản Monexa</p>
                            <h1 class="auth-card-title">Đăng nhập</h1>
                            <p class="auth-pane-subtitle">Tiếp tục quản lý tài chính cá nhân trên cùng một hệ giao diện với landing page.</p>
                        </div>

                        <div class="auth-field">
                            <label for="login-email">Email</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                        <path d="m3 7 9 6 9-6"></path>
                                    </svg>
                                </span>
                                <input id="login-email" type="email" name="email" value="{{ old('email', !$isRegister ? request('email') : '') }}" placeholder="example@gmail.com" autocomplete="email" @if(!$isRegister) autofocus @endif required>
                            </div>
                            @if (!$isRegister)
                                @error('email')
                                    <p class="auth-error">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <div class="auth-field">
                            <label for="login-password">Mật khẩu</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                                        <path d="M7 11V8a5 5 0 0 1 10 0v3"></path>
                                    </svg>
                                </span>
                                <input id="login-password" type="password" name="password" placeholder="Nhập mật khẩu" autocomplete="current-password" required>
                                <button type="button" class="auth-password-toggle" data-password-toggle="login-password" aria-label="Hiện hoặc ẩn mật khẩu">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            @if (!$isRegister)
                                @error('password')
                                    <p class="auth-error">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <div class="auth-row">
                            <label class="auth-check">
                                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <span>Ghi nhớ đăng nhập</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="auth-link">Quên mật khẩu?</a>
                        </div>

                        <button type="submit" class="auth-submit">Đăng nhập</button>

                        <div class="auth-divider"><span>hoặc</span></div>

                        <a href="{{ route('google.redirect') }}" class="auth-social">
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.3-1.5 3.9-5.5 3.9-3.3 0-6-2.7-6-6s2.7-6 6-6c1.9 0 3.1.8 3.8 1.4l2.6-2.5C16.8 3.4 14.6 2.5 12 2.5 6.8 2.5 2.5 6.8 2.5 12s4.3 9.5 9.5 9.5c5.5 0 9.1-3.9 9.1-9.3 0-.6-.1-1.1-.2-1.6H12Z"/>
                            </svg>
                            <span>Tiếp tục với Google</span>
                        </a>

                        <p class="auth-mobile-switch">Chưa có tài khoản? <button type="button" class="auth-inline-button" data-mode="register">Đăng ký ngay</button></p>
                    </form>
                </div>

                <div class="auth-pane auth-pane--signup">
                    <form action="{{ route('register') }}" method="POST" class="auth-form auth-form--pane" novalidate>
                        @csrf
                        <input type="hidden" name="auth_mode" value="register">
                        <div class="auth-form-head">
                            <p class="auth-kicker">Khởi tạo tài khoản</p>
                            <h1 class="auth-card-title">Đăng ký</h1>
                            <p class="auth-pane-subtitle">Thiết lập tài khoản mới để bắt đầu theo dõi thu chi, ngân sách và báo cáo thông minh.</p>
                        </div>

                        <div class="auth-field">
                            <label for="register-name">Họ và tên</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21a8 8 0 0 0-16 0"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </span>
                                <input id="register-name" type="text" name="name" value="{{ old('name') }}" placeholder="Nhập họ và tên của bạn" autocomplete="name" @if($isRegister) autofocus @endif required>
                            </div>
                            @if ($isRegister)
                                @error('name')
                                    <p class="auth-error">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <div class="auth-field">
                            <label for="register-email">Email Gmail</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                        <path d="m3 7 9 6 9-6"></path>
                                    </svg>
                                </span>
                                <input id="register-email" type="email" name="email" value="{{ old('email', $isRegister ? request('email') : '') }}" placeholder="example@gmail.com" autocomplete="email" required>
                            </div>
                            @if ($isRegister)
                                @error('email')
                                    <p class="auth-error">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <div class="auth-field">
                            <label for="register-password">Mật khẩu</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                                        <path d="M7 11V8a5 5 0 0 1 10 0v3"></path>
                                    </svg>
                                </span>
                                <input id="register-password" type="password" name="password" placeholder="Tối thiểu 8 ký tự, có chữ hoa, số và ký tự đặc biệt" autocomplete="new-password" required>
                                <button type="button" class="auth-password-toggle" data-password-toggle="register-password" aria-label="Hiện hoặc ẩn mật khẩu">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            @if ($isRegister)
                                @error('password')
                                    <p class="auth-error">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <div class="auth-field">
                            <label for="register-password-confirmation">Xác nhận mật khẩu</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 12 2 2 4-4"></path>
                                        <path d="M21 12c0 5-4 9-9 9a9 9 0 1 1 9-9Z"></path>
                                    </svg>
                                </span>
                                <input id="register-password-confirmation" type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu" autocomplete="new-password" required>
                                <button type="button" class="auth-password-toggle" data-password-toggle="register-password-confirmation" aria-label="Hiện hoặc ẩn xác nhận mật khẩu">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="auth-submit">Tạo tài khoản</button>

                        <div class="auth-divider"><span>hoặc</span></div>

                        <a href="{{ route('google.redirect') }}" class="auth-social">
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.3-1.5 3.9-5.5 3.9-3.3 0-6-2.7-6-6s2.7-6 6-6c1.9 0 3.1.8 3.8 1.4l2.6-2.5C16.8 3.4 14.6 2.5 12 2.5 6.8 2.5 2.5 6.8 2.5 12s4.3 9.5 9.5 9.5c5.5 0 9.1-3.9 9.1-9.3 0-.6-.1-1.1-.2-1.6H12Z"/>
                            </svg>
                            <span>Đăng ký nhanh với Google</span>
                        </a>

                        <p class="auth-mobile-switch">Đã có tài khoản? <button type="button" class="auth-inline-button" data-mode="login">Đăng nhập</button></p>
                    </form>
                </div>

                <div class="auth-overlay-shell">
                    <div class="auth-overlay">
                        <div class="auth-overlay-panel auth-overlay-panel--left">
                            <p class="auth-overlay-kicker">Chào mừng quay lại</p>
                            <h2>Đã có tài khoản Monexa?</h2>
                            <p>Đăng nhập để tiếp tục xem dashboard, ngân sách và các gợi ý AI dành riêng cho bạn.</p>
                            <button type="button" class="auth-ghost-btn" data-mode="login">Đăng nhập</button>
                        </div>

                        <div class="auth-overlay-panel auth-overlay-panel--right">
                            <p class="auth-overlay-kicker">Bắt đầu miễn phí</p>
                            <h2>Chưa có tài khoản?</h2>
                            <p>Tạo tài khoản mới để lưu giao dịch, theo dõi ví tiền và làm chủ tài chính cá nhân mỗi ngày.</p>
                            <button type="button" class="auth-ghost-btn" data-mode="register">Đăng ký</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const authFrame = document.getElementById('authFrame');
        const modeSwitchers = document.querySelectorAll('[data-mode]');
        const passwordToggles = document.querySelectorAll('[data-password-toggle]');

        function setAuthMode(mode) {
            authFrame.classList.toggle('is-register', mode === 'register');
            document.querySelectorAll('.auth-tab').forEach(tab => {
                tab.classList.toggle('is-active', tab.dataset.mode === mode);
            });
        }

        modeSwitchers.forEach(control => {
            control.addEventListener('click', () => {
                setAuthMode(control.dataset.mode);
            });
        });

        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.dataset.passwordToggle);
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                toggle.classList.toggle('is-visible', isPassword);
            });
        });

        setAuthMode(authFrame.classList.contains('is-register') ? 'register' : 'login');
    </script>
    @include('layouts.partials.monebot')
</body>
</html>
