<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                            <span>{{ session('email') }}</span>
                        </div>
                    </div>

                    <form action="{{ route('password.verify') }}" method="POST" class="recovery-form" id="verifyForm" novalidate>
                        @csrf

                        <div class="recovery-code-grid">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="0" autocomplete="one-time-code">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="1">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="2">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="3">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="4">
                            <input type="text" maxlength="1" inputmode="numeric" class="recovery-code-input" data-index="5">
                        </div>

                        <input type="hidden" name="code" id="codeInput">

                        @error('code')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="auth-submit" id="submitBtn" disabled>Xác thực và tiếp tục</button>
                    </form>

                    <div class="recovery-resend">
                        <p class="recovery-resend-text">Không nhận được mã?</p>
                        <form action="{{ route('password.email') }}" method="POST" class="recovery-inline-form">
                            @csrf
                            <input type="hidden" name="email" value="{{ session('email') }}">
                            <button type="submit" class="recovery-secondary-btn">Gửi lại mã</button>
                        </form>
                    </div>
                </section>
            </section>
        </main>
    </div>

    <script>
        const inputs = [...document.querySelectorAll('.recovery-code-input')];
        const hiddenCodeInput = document.getElementById('codeInput');
        const submitBtn = document.getElementById('submitBtn');
        const verifyForm = document.getElementById('verifyForm');
        let code = Array(6).fill('');

        function syncCode() {
            const fullCode = code.join('');
            hiddenCodeInput.value = fullCode;
            submitBtn.disabled = fullCode.length !== 6;
        }

        function focusInput(index) {
            const safeIndex = Math.max(0, Math.min(index, inputs.length - 1));
            inputs[safeIndex].focus();
            inputs[safeIndex].select();
        }

        inputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                const value = input.value.replace(/\D/g, '').slice(0, 1);
                input.value = value;
                code[index] = value;

                if (value && index < inputs.length - 1) {
                    focusInput(index + 1);
                }

                syncCode();
            });

            input.addEventListener('keydown', event => {
                if (event.key === 'Backspace' && !input.value && index > 0) {
                    code[index] = '';
                    focusInput(index - 1);
                }

                if (event.key === 'ArrowLeft' && index > 0) {
                    event.preventDefault();
                    focusInput(index - 1);
                }

                if (event.key === 'ArrowRight' && index < inputs.length - 1) {
                    event.preventDefault();
                    focusInput(index + 1);
                }
            });

            input.addEventListener('paste', event => {
                event.preventDefault();
                const pasted = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                if (!pasted) return;

                pasted.split('').forEach((digit, offset) => {
                    code[offset] = digit;
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

        verifyForm.addEventListener('submit', event => {
            if (hiddenCodeInput.value.length !== 6) {
                event.preventDefault();
                inputs.forEach(input => input.classList.add('is-error'));
            }
        });

        focusInput(0);
    </script>
    @include('layouts.partials.monebot')
</body>
</html>
