<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Đổi mật khẩu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e94c5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            background: white;
            border-radius: 25px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 580px;
            display: flex;
        }

        .form-section {
            width: 50%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        h2 {
            color: #2a5298;
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 8px;
            text-align: center;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
            text-align: center;
            line-height: 1.6;
        }

        .user-info {
            background: #f8f9fd;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4a90e2 0%, #2a5298 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
        }

        .user-details {
            flex: 1;
        }

        .user-details .name {
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }

        .user-details .email {
            color: #666;
            font-size: 13px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            padding: 13px 45px 13px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
            background: #f8f9fa;
        }

        .input-wrapper input:focus {
            border-color: #4a90e2;
            background: white;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .input-wrapper.error input {
            border-color: #dc3545;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            object-fit: contain;
            opacity: 0.6;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .eye-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
            opacity: 0.6;
            transition: opacity 0.3s;
        }

        .toggle-password:hover .eye-icon {
            opacity: 1;
        }

        .error-message {
            color: #dc3545;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }

        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            display: none;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }

        .strength-weak { 
            width: 33%; 
            background: #dc3545; 
        }

        .strength-medium { 
            width: 66%; 
            background: #ffc107; 
        }

        .strength-strong { 
            width: 100%; 
            background: #28a745; 
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4a90e2 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(42, 82, 152, 0.4);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .back-link a {
            display: inline-flex;      
            align-items: center;       
            gap: 8px;            
            color: #4a90e2;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #2a5298;
        }

        .back-link a img {
            width: 16px;       
            height: 16px;
            object-fit: contain;
            display: inline-block;
            vertical-align: middle;
        }

        /* Overlay Panel */
        .overlay-panel {
            width: 50%;
            background: linear-gradient(135deg, #4a90e2 0%, #2a5298 50%, #1e3c72 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 45px;
            text-align: center;
        }

        .overlay-panel h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .overlay-panel p {
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .icon-wrapper {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            padding: 30px;
        }

        .icon-wrapper img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .security-features {
            width: 100%;
            max-width: 320px;
            margin-top: 10px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 18px;
            font-size: 15px;
            text-align: left;
        }

        .feature-icon {
            min-width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-icon img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                max-width: 400px;
                min-height: auto;
                flex-direction: column;
            }

            .form-section {
                width: 100%;
                padding: 40px 30px;
            }

            .overlay-panel {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h2>Đổi mật khẩu</h2>
            <p class="subtitle">Cập nhật mật khẩu mới cho tài khoản của bạn</p>

            <div class="user-info">
                <div class="user-avatar">
                    @if(Auth::user()->avatar)
                        @if(str_starts_with(Auth::user()->avatar, 'http'))
                            <img src="{{ Auth::user()->avatar }}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" alt="Avatar">
                         @else
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" alt="Avatar">
                        @endif
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    @endif
                </div>
                <div class="user-details">
                    <div class="name">{{ Auth::user()->name }}</div>
                    <div class="email">{{ Auth::user()->email }}</div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form id="changePasswordForm">
                @csrf

                <div class="form-group">
                    <label>Mật khẩu hiện tại</label>
                    <div class="input-wrapper" id="wrap-current_password">
                        <img src="{{ asset('images/lock.png') }}" class="input-icon" alt="Lock">
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            placeholder="Nhập mật khẩu hiện tại"
                            required autofocus
                        >
                        <span class="toggle-password" onclick="togglePassword('current_password', this)">
                            <img src="{{ asset('images/eye.png') }}" class="eye-icon" alt="Toggle">
                        </span>
                    </div>
                    <span class="error-message" id="err-current_password"></span>
                </div>

                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <div class="input-wrapper" id="wrap-password">
                        <img src="{{ asset('images/password.png') }}" class="input-icon" alt="Password">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Nhập mật khẩu mới"
                            required
                            oninput="checkPasswordStrength(this.value)"
                        >
                        <span class="toggle-password" onclick="togglePassword('password', this)">
                            <img src="{{ asset('images/eye.png') }}" class="eye-icon" alt="Toggle">
                        </span>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <span class="error-message" id="err-password"></span>
                </div>

                <div class="form-group">
                    <label>Xác nhận mật khẩu mới</label>
                    <div class="input-wrapper" id="wrap-password_confirmation">
                        <img src="{{ asset('images/check.png') }}" class="input-icon" alt="Confirm">
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="Nhập lại mật khẩu mới"
                            required
                        >
                        <span class="toggle-password" onclick="togglePassword('password_confirmation', this)">
                            <img src="{{ asset('images/eye.png') }}" class="eye-icon" alt="Toggle">
                        </span>
                    </div>
                    <span class="error-message" id="err-password_confirmation"></span>
                </div>

                <div class="alert alert-success" id="alertSuccess" style="display:none;"></div>
                <div class="alert alert-error"   id="alertError"   style="display:none;"></div>

                <button type="submit" class="submit-btn" id="submitBtn">Cập nhật mật khẩu</button>
            </form>

            <div class="back-link">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <img src="/images/arrow.png" alt="Quay lại">
                    <span>Quay lại trang chủ</span>
                </a>
            </div>
        </div>

        <div class="overlay-panel">
            <div class="icon-wrapper">
                <img src="{{ asset('images/insurance.png') }}" alt="Security">
            </div>
            <h1>Bảo mật tài khoản</h1>
            <p>Tạo mật khẩu mạnh để bảo vệ tài khoản của bạn khỏi các truy cập trái phép.</p>
            
            <div class="security-features">
                <div class="feature">
                    <div class="feature-icon">
                        <img src="{{ asset('images/lock.png') }}" alt="Encrypt">
                    </div>
                    <div>Bảo mật nâng cao-Tăng cường an toàn tài khoản bằng các lớp bảo mật</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <img src="{{ asset('images/check.png') }}" alt="2FA">
                    </div>
                    <div>Quyền truy cập thông minh-Kiểm soát quyền truy cập, hạn chế rủi ro</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <img src="{{ asset('images/technology.png') }}" alt="Protection">
                    </div>
                    <div>Giám sát mọi hoạt động - Theo dõi mọi truy cập và thay đổi để bảo vệ dữ liệu</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Đọc XSRF-TOKEN cookie (Sanctum stateful)
        function getXsrfToken() {
            return decodeURIComponent(
                document.cookie.match('(^|;)\\s*XSRF-TOKEN\\s*=\\s*([^;]+)')?.pop() || ''
            );
        }

        function clearErrors() {
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.querySelectorAll('.input-wrapper').forEach(el => el.classList.remove('error'));
            document.getElementById('alertSuccess').style.display = 'none';
            document.getElementById('alertError').style.display   = 'none';
        }

        function showFieldError(field, message) {
            const err  = document.getElementById(`err-${field}`);
            const wrap = document.getElementById(`wrap-${field}`);
            if (err)  err.textContent = message;
            if (wrap) wrap.classList.add('error');
        }

        document.getElementById('changePasswordForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            clearErrors();

            const btn = document.getElementById('submitBtn');
            btn.disabled    = true;
            btn.textContent = 'Đang cập nhật...';

            try {
                // Sanctum stateful: lấy CSRF cookie trước
                await fetch('/sanctum/csrf-cookie', { credentials: 'include' });

                const res = await fetch('/api/v1/password/change', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-XSRF-TOKEN': getXsrfToken(),
                    },
                    body: JSON.stringify({
                        current_password:      document.getElementById('current_password').value,
                        password:              document.getElementById('password').value,
                        password_confirmation: document.getElementById('password_confirmation').value,
                    }),
                });

                const data = await res.json();

                if (res.ok) {
                    const alertSuccess = document.getElementById('alertSuccess');
                    alertSuccess.textContent = data.message;
                    alertSuccess.style.display = 'block';
                    this.reset();
                    document.getElementById('passwordStrength').style.display = 'none';
                } else {
                    if (data.errors) {
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            showFieldError(field, messages[0]);
                        });
                    } else {
                        const alertError = document.getElementById('alertError');
                        alertError.textContent = data.message || 'Đã có lỗi xảy ra!';
                        alertError.style.display = 'block';
                    }
                }
            } catch {
                const alertError = document.getElementById('alertError');
                alertError.textContent = 'Lỗi kết nối, vui lòng thử lại!';
                alertError.style.display = 'block';
            } finally {
                btn.disabled    = false;
                btn.textContent = 'Cập nhật mật khẩu';
            }
        });

        function togglePassword(fieldId, toggleIcon) {
            const field   = document.getElementById(fieldId);
            const iconImg = toggleIcon.querySelector('img');
            const isHidden = field.type === 'password';
            field.type    = isHidden ? 'text' : 'password';
            iconImg.src   = isHidden 
                ? "{{ asset('images/eye_off.png') }}" 
                : "{{ asset('images/eye.png') }}";
        }

        function checkPasswordStrength(password) {
            const bar       = document.getElementById('strengthBar');
            const container = document.getElementById('passwordStrength');

            if (!password.length) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'block';

            let strength = 0;
            if (password.length >= 8)                            strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password))                             strength++;
            if (/[^a-zA-Z\d]/.test(password))                   strength++;

            bar.className = 'password-strength-bar';
            if (strength <= 1)      bar.classList.add('strength-weak');
            else if (strength <= 3) bar.classList.add('strength-medium');
            else                    bar.classList.add('strength-strong');
        }
    </script>
</body>
</html>