<!DOCTYPE html>
<html lang="vi">
@php
    $authUser = Auth::user();
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Monexa') - Quản lý chi tiêu</title>
    <link rel="icon" type="images/png" href="{{ asset('favicon.png') }}">
    @vite('resources/js/app.js')
</head>
<body
    class="{{ cookie('theme', 'light') === 'dark' ? 'dark' : '' }}"
    data-authenticated="{{ $authUser ? '1' : '0' }}"
    data-login-url="{{ route('login') }}"
    data-api-logout-url="{{ url('/api/monaxe/auth/logout') }}"
>
    <div class="topbar">
        <div class="topbar-left">
            <a href="/" class="footer-logo">
                <div class="footer-logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 12l10 10 10-10L12 2z"></path>
                        <path d="M8 12l3-3 5 5"></path>
                    </svg>
                </div>
                <span class="logo-wordmark">Mon<em>exa</em></span>
            </a>

            <div style="position:relative; flex:1; max-width:500px; margin:0 auto;">
                <div class="search-bar" id="searchBar">
                    <img src="/images/search.png" alt="Search">
                    <input type="text" id="searchInput" placeholder="Tim kiem giao dich, danh muc, ngan sach..." autocomplete="off">
                    <span id="searchSpinner" style="display:none; width:16px; height:16px; border:2px solid #e5e7eb; border-top-color:var(--primary); border-radius:50%; animation:spin .6s linear infinite; flex-shrink:0;"></span>
                </div>
                <div id="searchDropdown" style="
                    position:absolute;
                    top:calc(100% + 8px);
                    left:0;
                    right:0;
                    background:white;
                    border-radius:16px;
                    box-shadow:0 12px 40px rgba(0,0,0,0.15);
                    border:1px solid rgba(0,0,0,0.06);
                    overflow:hidden;
                    z-index:9999;
                    display:none;
                "></div>
            </div>
        </div>

        <div class="topbar-right">
            <div class="icon-btn">
                @if($authUser)
                    @include('notifications._dropdown')
                @else
                    <a href="{{ route('login') }}" style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">
                        <img src="/images/bell.png" alt="Notifications">
                    </a>
                @endif
            </div>

            <div id="themeToggle" class="icon-btn">
                <img src="/images/dark-mode.png" alt="Theme">
            </div>

            <div class="user-profile" id="userProfile">
                @if($authUser?->avatar)
                    @if(str_starts_with($authUser->avatar, 'http'))
                        <img src="{{ $authUser->avatar }}" class="user-avatar-img" alt="Avatar" id="userAvatarImage">
                    @else
                        <img src="{{ asset('storage/' . $authUser->avatar) }}" class="user-avatar-img" alt="Avatar" id="userAvatarImage">
                    @endif
                    <div class="user-avatar" id="userAvatarFallback" style="display:none" hidden>{{ strtoupper(substr($authUser->name, 0, 2)) }}</div>
                @else
                    <img src="" class="user-avatar-img" alt="Avatar" id="userAvatarImage" style="display:none" hidden>
                    <div class="user-avatar" id="userAvatarFallback">{{ $authUser ? strtoupper(substr($authUser->name, 0, 2)) : 'M' }}</div>
                @endif

                <div class="user-info">
                    <div class="user-name" id="userName">{{ $authUser->name ?? 'Tai khoan Monexa' }}</div>
                </div>
            </div>

            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-header">
                    <div class="dropdown-avatar">
                        @if($authUser && $authUser->avatar)
                            @php
                                $avatarUrl = str_starts_with($authUser->avatar, 'http') 
                                    ? $authUser->avatar 
                                    : asset('storage/' . $authUser->avatar);
                            @endphp
                            <img src="{{ $avatarUrl }}" alt="Avatar" id="dropdownAvatarImage">
                            {{-- ← Ẩn fallback --}}
                            <span id="dropdownAvatarFallback" style="display:none" hidden></span>
                        @else
                            <img src="" alt="Avatar" id="dropdownAvatarImage" style="display:none" hidden>
                            <span id="dropdownAvatarFallback">
                                {{ $authUser ? strtoupper(substr($authUser->name, 0, 1)) : 'M' }}
                            </span>
                        @endif
                    </div>
                    <div class="dropdown-info">
                        <div class="dropdown-name" id="dropdownName">{{ $authUser->name ?? 'Tài khoản Monexa' }}</div>
                        <div class="dropdown-email" id="dropdownEmail">{{ $authUser->email ?? 'Đăng nhập để tiếp tục' }}</div>
                    </div>
                </div>

                <div class="dropdown-menu">
                    @if($authUser)
                        {{-- Menu khi đã đăng nhập --}}
                        <a href="/profile" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            Hồ sơ cá nhân
                        </a>

                        @if(!$authUser->google_id)
                            <a href="/change-password" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Đổi mật khẩu
                            </a>
                        @endif

                        <form action="{{ route('logout') }}" method="POST" style="margin:0">
                            @csrf
                            <button type="submit" class="dropdown-item logout">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                Đăng xuất
                            </button>
                        </form>
                    @else
                        {{-- Menu khi chưa đăng nhập --}}
                        <a href="{{ route('login') }}" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                            Đăng nhập
                        </a>
                        <a href="{{ route('register') }}" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="17" y1="11" x2="23" y2="11"></line></svg>
                            Đăng ký
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.sidebar')
    @include('layouts.partials.monebot')

    <div class="main-content">
        <div class="content">
            @yield('content')
        </div>
    </div>

    <div id="toastContainer" style="
        position: fixed;
        top: 84px;
        right: 20px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 360px;
        pointer-events: none;
    "></div>

    <script>
        function _getToastSettings() {
    try {
        return {
            toastEnabled: true,
            toastPosition: 'top-right',
            toastDuration: 5,
            toastSound: false,
            ...JSON.parse(localStorage.getItem('monexa_settings') || '{}'),
        };
    } catch {
        return {
            toastEnabled: true,
            toastPosition: 'top-right',
            toastDuration: 5,
            toastSound: false,
        };
    }
}

    function _applyToastPosition(position) {
    const container = document.getElementById('toastContainer');

    if (!container) {
        return;
    }

    container.style.top = position.includes('bottom') ? 'auto' : '84px';
    container.style.bottom = position.includes('bottom') ? '20px' : 'auto';
    container.style.left = position.includes('left') ? '20px' : 'auto';
    container.style.right = position.includes('left') ? 'auto' : '20px';
}

    _applyToastPosition(_getToastSettings().toastPosition);

    window.showToast = function ({ type = 'info', title, message = '', action = null, id = null, duration = null }) {
    const settings = _getToastSettings();

    if (settings.toastEnabled === false) {
        return;
    }

    const timeout = duration ?? (settings.toastDuration * 1000);

    if (id && document.querySelector(`[data-toast-id="${id}"]`)) {
        return;
    }

    if (id && sessionStorage.getItem('tdismiss_' + id)) {
        return;
    }

    _applyToastPosition(settings.toastPosition);

    const icons = {
        success: '<img src="/images/check.png" style="width:20px;height:20px;object-fit:contain;">',
        error: '<img src="/images/warning.png" style="width:20px;height:20px;object-fit:contain;">',
        warning: '<img src="/images/alert.png" style="width:20px;height:20px;object-fit:contain;">',
        info: '<img src="/images/info.png" style="width:20px;height:20px;object-fit:contain;">',
    };

    const toast = document.createElement('div');
    toast.className = `g-toast ${type}`;

    if (id) {
        toast.dataset.toastId = id;
    }

    toast.innerHTML = `
        <div class="g-toast-icon">${icons[type] || 'i'}</div>
        <div class="g-toast-body">
            <div class="g-toast-title">${title}</div>
            ${message ? `<div class="g-toast-msg">${message}</div>` : ''}
            ${action ? `<a href="${action.url}" class="g-toast-action">${action.label}</a>` : ''}
        </div>
        <button class="g-toast-close" onclick="dismissToast(this,'${id}')">&times;</button>
        <div class="g-toast-progress" style="animation-duration:${timeout}ms"></div>
    `;

    document.getElementById('toastContainer')?.appendChild(toast);

    if (settings.toastSound) {
        try {
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = context.createOscillator();
            const gain = context.createGain();

            oscillator.connect(gain);
            gain.connect(context.destination);
            oscillator.frequency.value = type === 'error' ? 300 : 600;
            gain.gain.setValueAtTime(0.08, context.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, context.currentTime + 0.3);
            oscillator.start();
            oscillator.stop(context.currentTime + 0.3);
        } catch {}
    }

    if (timeout > 0) {
        setTimeout(() => dismissToast(toast.querySelector('.g-toast-close'), id), timeout);
    }
};

window.dismissToast = function (button, id = null) {
    const toast = button?.closest?.('.g-toast');

    if (!toast) {
        return;
    }

    toast.classList.add('hiding');

    if (id && id !== 'null') {
        sessionStorage.setItem('tdismiss_' + id, '1');
    }

    setTimeout(() => toast.remove(), 320);
};
      document.addEventListener('DOMContentLoaded', function () {
    // ── Session toasts từ server (redirect) ──
    @if(session('toast'))
        showToast(@json(session('toast')));
    @endif
    @if(session('success'))
        showToast({ type: 'success', title: 'Thành công', message: @json(session('success')) });
    @endif
    @if(session('error'))
        showToast({ type: 'error', title: 'Lỗi', message: @json(session('error')) });
    @endif
    @if(session('warning'))
        showToast({ type: 'warning', title: 'Cảnh báo', message: @json(session('warning')) });
    @endif
    @if(session('info'))
        showToast({ type: 'info', title: 'Thông báo', message: @json(session('info')) });
    @endif

    // ── Pending toast từ AJAX (lưu trước reload) ──
    const pending = sessionStorage.getItem('pending_toast');
    if (pending) {
        sessionStorage.removeItem('pending_toast');
        try {
            showToast(JSON.parse(pending));
        } catch(e) {}
    }
});

        (() => {
            const serverAuthenticated = document.body.dataset.authenticated === '1';

            if (serverAuthenticated) {
                return;
            }

            let storedUser = null;

            try {
                storedUser = JSON.parse(localStorage.getItem('user') || 'null');
            } catch (error) {
                storedUser = null;
            }

            if (!storedUser) {
                return;
            }

            const userName = storedUser.name || 'Tài khoản Monexa';
            const userEmail = storedUser.email || 'Đăng nhập bằng API';
            const initials = userName.slice(0, 2).toUpperCase();
            const avatar = storedUser.avatar
                ? (String(storedUser.avatar).startsWith('http') ? storedUser.avatar : `/storage/${storedUser.avatar}`)
                : '';

            const userNameEl = document.getElementById('userName');
            const dropdownNameEl = document.getElementById('dropdownName');
            const dropdownEmailEl = document.getElementById('dropdownEmail');
            const userAvatarFallbackEl = document.getElementById('userAvatarFallback');
            const dropdownAvatarFallbackEl = document.getElementById('dropdownAvatarFallback');
            const userAvatarImageEl = document.getElementById('userAvatarImage');
            const dropdownAvatarImageEl = document.getElementById('dropdownAvatarImage');
            const clientLogoutButton = document.getElementById('clientLogoutButton');

            if (userNameEl) userNameEl.textContent = userName;
            if (dropdownNameEl) dropdownNameEl.textContent = userName;
            if (dropdownEmailEl) dropdownEmailEl.textContent = userEmail;
            if (userAvatarFallbackEl) userAvatarFallbackEl.textContent = initials;
            if (dropdownAvatarFallbackEl) dropdownAvatarFallbackEl.textContent = initials;

            if (avatar) {
                if (userAvatarImageEl) {
                    userAvatarImageEl.src = avatar;
                    userAvatarImageEl.hidden = false;
                }

                if (dropdownAvatarImageEl) {
                    dropdownAvatarImageEl.src = avatar;
                    dropdownAvatarImageEl.hidden = false;
                }

                if (userAvatarFallbackEl) userAvatarFallbackEl.hidden = true;
                if (dropdownAvatarFallbackEl) dropdownAvatarFallbackEl.hidden = true;
            }

            if (clientLogoutButton) {
                clientLogoutButton.hidden = false;
                clientLogoutButton.addEventListener('click', async () => {
                    const token = localStorage.getItem('token');

                    try {
                        if (token) {
                            await fetch(document.body.dataset.apiLogoutUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Authorization': `Bearer ${token}`,
                                },
                            });
                        }
                    } catch (error) {
                        console.warn('Client logout failed', error);
                    }

                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    window.location.href = document.body.dataset.loginUrl;
                });
            }
        })();
    </script>
</body>
</html>
