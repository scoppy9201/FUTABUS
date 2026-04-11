<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đặt lại mật khẩu Monexa</title>
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
                <a href="{{ route('password.verify.form') }}" class="auth-top-link">Quay lại mã xác thực</a>
                <a href="{{ route('login') }}" class="auth-top-link">Đăng nhập</a>
            </div>
        </header>

        <main class="recovery-shell">
            <section class="recovery-layout">
                <aside class="recovery-aside">
                    <div class="recovery-aside-inner">
                        <p class="recovery-kicker">Đặt lại mật khẩu</p>
                        <h1 class="recovery-title">Tạo mật khẩu mới đủ mạnh để bảo vệ tài khoản Monexa của bạn.</h1>
                        <div class="recovery-hero-icon" aria-hidden="true">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="10" rx="3"></rect>
                                <path d="M7 11V8a5 5 0 0 1 10 0v3"></path>
                            </svg>
                        </div>
                        <p class="recovery-copy">Sử dụng mật khẩu khó đoán, dễ nhớ với bạn và khác hoàn toàn với mật khẩu cũ để tăng độ an toàn cho tài khoản.</p>

                        <div class="recovery-rules">
                            <div class="recovery-rule">
                                <div class="recovery-rule-badge">A</div>
                                <div class="recovery-rule-text">
                                    <strong>Tối thiểu 8 ký tự</strong>
                                    <span>Kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt nếu có thể.</span>
                                </div>
                            </div>
                            <div class="recovery-rule">
                                <div class="recovery-rule-badge">B</div>
                                <div class="recovery-rule-text">
                                    <strong>Không dùng lại mật khẩu cũ</strong>
                                    <span>Điều này giúp tránh những rủi ro nếu thông tin cũ từng bị lộ.</span>
                                </div>
                            </div>
                            <div class="recovery-rule">
                                <div class="recovery-rule-badge">C</div>
                                <div class="recovery-rule-text">
                                    <strong>Lưu trong trình quản lý mật khẩu</strong>
                                    <span>Nếu cần, hãy dùng password manager để ghi nhớ an toàn hơn.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="recovery-card">
                    <div class="recovery-card-head">
                        <p class="auth-kicker">Mật khẩu mới</p>
                        <h2 class="auth-card-title">Đặt lại mật khẩu</h2>
                        <p class="recovery-subtitle">Nhập mật khẩu mới cho tài khoản của bạn. Sau khi hoàn tất, bạn có thể đăng nhập lại ngay.</p>
                    </div>

                    <div id="rp-alert" class="auth-notice" hidden></div>

                    <div class="recovery-form">
                        <div class="auth-field">
                            <label for="new-password">Mật khẩu mới</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                                        <path d="M7 11V8a5 5 0 0 1 10 0v3"></path>
                                    </svg>
                                </span>
                                <input id="new-password" type="password" placeholder="Nhập mật khẩu mới" autocomplete="new-password" required autofocus>
                                <button type="button" class="auth-password-toggle" data-password-toggle="new-password" aria-label="Hiện hoặc ẩn mật khẩu mới">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p id="rp-password-error" class="auth-error" hidden></p>
                        </div>

                        <div class="auth-field">
                            <label for="new-password-confirmation">Xác nhận mật khẩu mới</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 12 2 2 4-4"></path>
                                        <path d="M21 12c0 5-4 9-9 9a9 9 0 1 1 9-9Z"></path>
                                    </svg>
                                </span>
                                <input id="new-password-confirmation" type="password" placeholder="Nhập lại mật khẩu mới" autocomplete="new-password" required>
                                <button type="button" class="auth-password-toggle" data-password-toggle="new-password-confirmation" aria-label="Hiện hoặc ẩn xác nhận mật khẩu">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p id="rp-confirm-error" class="auth-error" hidden></p>
                        </div>

                        <div class="recovery-info">
                            <strong>Gợi ý:</strong>
                            <ul class="recovery-rule-list">
                                <li>Tối thiểu 8 ký tự để đảm bảo mức bảo mật cơ bản.</li>
                                <li>Nên kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt.</li>
                                <li>Không dùng lại mật khẩu cũ hoặc mật khẩu quá dễ đoán.</li>
                            </ul>
                        </div>

                        <button type="button" class="auth-submit" id="rp-submit-btn">
                            Đặt lại mật khẩu
                        </button>
                    </div>
                </section>
            </section>
        </main>
    </div>

    <script>
    (() => {
        const RESET_API  = '{{ url("/api/v1/auth/password/reset") }}';
        const LOGIN_URL  = '{{ route("login") }}';
        const FORGOT_URL = '{{ route("password.request") }}';
        const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const email = sessionStorage.getItem('reset_email');
        const code  = sessionStorage.getItem('reset_code');

        // Nếu thiếu email hoặc code → quay về bước 1
        if (!email || !code) {
            window.location.href = FORGOT_URL;
        }

        const passwordInput  = document.getElementById('new-password');
        const confirmInput   = document.getElementById('new-password-confirmation');
        const submitBtn      = document.getElementById('rp-submit-btn');
        const alertBox       = document.getElementById('rp-alert');
        const passwordError  = document.getElementById('rp-password-error');
        const confirmError   = document.getElementById('rp-confirm-error');

        // Toggle hiện/ẩn mật khẩu
        document.querySelectorAll('[data-password-toggle]').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.dataset.passwordToggle);
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                toggle.classList.toggle('is-visible', isPassword);
            });
        });

        function showAlert(message, isError = true) {
            alertBox.textContent = message;
            alertBox.className   = `auth-notice ${isError ? 'auth-notice--error' : 'auth-notice--success'}`;
            alertBox.hidden      = false;
        }

        function validate() {
            let valid = true;

            passwordError.hidden = true;
            confirmError.hidden  = true;

            if (passwordInput.value.length < 8) {
                passwordError.textContent = 'Mật khẩu phải có ít nhất 8 ký tự.';
                passwordError.hidden      = false;
                valid = false;
            }

            if (passwordInput.value !== confirmInput.value) {
                confirmError.textContent = 'Xác nhận mật khẩu không khớp.';
                confirmError.hidden      = false;
                valid = false;
            }

            return valid;
        }

        submitBtn.addEventListener('click', async () => {
            alertBox.hidden = true;
            if (!validate()) return;

            submitBtn.disabled    = true;
            submitBtn.textContent = 'Đang xử lý...';

            try {
                const res  = await fetch(RESET_API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({
                        email,
                        code,
                        password:              passwordInput.value,
                        password_confirmation: confirmInput.value,
                    }),
                });

                const data = await res.json();

                if (!res.ok) {
                    const errors = data.errors ?? {};
                    if (errors.password) {
                        passwordError.textContent = errors.password[0];
                        passwordError.hidden      = false;
                    } else {
                        showAlert(data.message || 'Đặt lại mật khẩu thất bại.');
                    }
                    return;
                }

                // Xóa sessionStorage sau khi thành công
                sessionStorage.removeItem('reset_email');
                sessionStorage.removeItem('reset_code');

                showAlert(data.message, false);
                setTimeout(() => { window.location.href = LOGIN_URL; }, 1500);

            } catch {
                showAlert('Không thể kết nối máy chủ. Vui lòng thử lại.');
            } finally {
                submitBtn.disabled    = false;
                submitBtn.textContent = 'Đặt lại mật khẩu';
            }
        });
    })();
    </script>
    @include('layouts.partials.monebot')
</body>
</html>