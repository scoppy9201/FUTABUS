<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Xác thực mã Monexa</title>
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
                <a href="{{ route('password.request') }}" class="auth-top-link">Đổi email</a>
                <a href="{{ route('login') }}" class="auth-top-link">Đăng nhập</a>
            </div>
        </header>

        <main class="recovery-shell">
            <section class="recovery-layout">
                <aside class="recovery-aside">
                    <div class="recovery-aside-inner">
                        <p class="recovery-kicker">Xác thực bảo mật</p>
                        <h1 class="recovery-title">Kiểm tra email và nhập mã 6 số để tiếp tục đặt lại mật khẩu.</h1>
                        <div class="recovery-hero-icon" aria-hidden="true">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3 4 7v6c0 5 3.4 7.9 8 9 4.6-1.1 8-4 8-9V7l-8-4Z"></path>
                                <path d="m9.5 12 1.7 1.7 3.3-3.3"></path>
                            </svg>
                        </div>
                        <p class="recovery-copy">Bạn có thể nhập từng số hoặc dán cả chuỗi mã xác thực, hệ thống sẽ tự động điền vào từng ô.</p>

                        <div class="recovery-steps">
                            <div class="recovery-step">
                                <div class="recovery-step-badge">1</div>
                                <div class="recovery-step-text">
                                    <strong>Mở email vừa nhận</strong>
                                    <span>Mã xác thực đã được gửi tới hòm thư bạn dùng để khôi phục tài khoản.</span>
                                </div>
                            </div>
                            <div class="recovery-step">
                                <div class="recovery-step-badge">2</div>
                                <div class="recovery-step-text">
                                    <strong>Nhập đủ 6 chữ số</strong>
                                    <span>Bạn có thể gõ nhanh hoặc dán toàn bộ mã vào ô đầu tiên.</span>
                                </div>
                            </div>
                            <div class="recovery-step">
                                <div class="recovery-step-badge">3</div>
                                <div class="recovery-step-text">
                                    <strong>Tiếp tục sang bước đổi mật khẩu</strong>
                                    <span>Sau khi xác thực thành công, Monexa sẽ mở form tạo mật khẩu mới.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="recovery-card">
                    <div class="recovery-card-head">
                        <p class="auth-kicker">Mã xác thực</p>
                        <h2 class="auth-card-title">Nhập mã 6 số</h2>
                        <p class="recovery-subtitle">Mã xác thực đã được gửi tới email của bạn. Hãy nhập chính xác để tiếp tục.</p>
                        <div class="recovery-email-pill">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                <path d="m3 7 9 6 9-6"></path>
                            </svg>
                            <span id="display-email">...</span>
                        </div>
                    </div>

                    <div id="vc-alert" class="auth-notice" hidden></div>

                    <div class="recovery-form">
                        <div class="recovery-code-grid">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="0" autocomplete="one-time-code">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="1">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="2">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="3">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="4">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="5">
                        </div>

                        <p id="vc-code-error" class="auth-error" hidden></p>

                        <button type="button" class="auth-submit" id="vc-submit-btn" disabled>
                            Xác thực và tiếp tục
                        </button>
                    </div>

                    <div class="recovery-resend">
                        <p class="recovery-resend-text">Không nhận được mã?</p>
                        <button type="button" class="recovery-secondary-btn" id="vc-resend-btn">
                            Gửi lại mã
                        </button>
                    </div>
                </section>
            </section>
        </main>
    </div>

    <script>
    (() => {
        const VERIFY_API  = '{{ url("/api/v1/auth/password/verify") }}';
        const FORGOT_API  = '{{ url("/api/v1/auth/password/forgot") }}';
        const RESET_URL   = '{{ route("password.reset.form") }}';
        const FORGOT_URL  = '{{ route("password.request") }}';
        const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const email = sessionStorage.getItem('reset_email');

        // Nếu không có email → quay về bước 1
        if (!email) {
            window.location.href = FORGOT_URL;
        }

        document.getElementById('display-email').textContent = email;

        const inputs     = [...document.querySelectorAll('.recovery-code-input')];
        const submitBtn  = document.getElementById('vc-submit-btn');
        const resendBtn  = document.getElementById('vc-resend-btn');
        const alertBox   = document.getElementById('vc-alert');
        const codeError  = document.getElementById('vc-code-error');
        let code = Array(6).fill('');

        function showAlert(message, isError = true) {
            alertBox.textContent = message;
            alertBox.className   = `auth-notice ${isError ? 'auth-notice--error' : 'auth-notice--success'}`;
            alertBox.hidden      = false;
        }

        function syncCode() {
            const full = code.join('');
            submitBtn.disabled = full.length !== 6;
            codeError.hidden   = true;
        }

        function focusInput(index) {
            const i = Math.max(0, Math.min(index, inputs.length - 1));
            inputs[i].focus();
            inputs[i].select();
        }

        inputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                const val = input.value.replace(/\D/g, '').slice(0, 1);
                input.value = val;
                code[index] = val;
                if (val && index < inputs.length - 1) focusInput(index + 1);
                syncCode();
            });

            input.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    code[index] = '';
                    focusInput(index - 1);
                }
                if (e.key === 'ArrowLeft'  && index > 0)               { e.preventDefault(); focusInput(index - 1); }
                if (e.key === 'ArrowRight' && index < inputs.length - 1){ e.preventDefault(); focusInput(index + 1); }
            });

            input.addEventListener('paste', e => {
                e.preventDefault();
                const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                if (!pasted) return;
                pasted.split('').forEach((digit, offset) => {
                    code[offset]         = digit;
                    inputs[offset].value = digit;
                });
                for (let i = pasted.length; i < inputs.length; i++) {
                    code[i] = '';
                    inputs[i].value = '';
                }
                focusInput(Math.min(pasted.length, inputs.length - 1));
                syncCode();
            });
        });

        submitBtn.addEventListener('click', async () => {
            const fullCode = code.join('');
            if (fullCode.length !== 6) return;

            alertBox.hidden    = true;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang xác thực...';

            try {
                const res  = await fetch(VERIFY_API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({ email, code: fullCode }),
                });

                const data = await res.json();

                if (!res.ok) {
                    const errors = data.errors ?? {};
                    const msg = errors.code?.[0] ?? errors.email?.[0] ?? data.message ?? 'Xác thực thất bại.';
                    showAlert(msg);
                    inputs.forEach(i => i.classList.add('is-error'));
                    return;
                }

                // Lưu code để bước reset dùng
                sessionStorage.setItem('reset_code', fullCode);
                showAlert(data.message, false);
                setTimeout(() => { window.location.href = RESET_URL; }, 800);

            } catch {
                showAlert('Không thể kết nối máy chủ. Vui lòng thử lại.');
            } finally {
                submitBtn.disabled    = false;
                submitBtn.textContent = 'Xác thực và tiếp tục';
            }
        });

        resendBtn.addEventListener('click', async () => {
            alertBox.hidden       = true;
            resendBtn.disabled    = true;
            resendBtn.textContent = 'Đang gửi...';

            try {
                const res  = await fetch(FORGOT_API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({ email }),
                });

                const data = await res.json();
                showAlert(data.message, !res.ok);

                if (res.ok) {
                    // Reset ô nhập
                    code = Array(6).fill('');
                    inputs.forEach(i => { i.value = ''; i.classList.remove('is-error'); });
                    syncCode();
                    focusInput(0);
                }
            } catch {
                showAlert('Không thể kết nối máy chủ. Vui lòng thử lại.');
            } finally {
                resendBtn.disabled    = false;
                resendBtn.textContent = 'Gửi lại mã';
            }
        });

        focusInput(0);
    })();
    </script>
    @include('layouts.partials.monebot')
</body>
</html>