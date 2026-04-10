<!DOCTYPE html>
<html lang="vi">
@php
    $authUser = Auth::user();
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Monexa') - Quan ly chi tieu</title>
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
                    <div class="user-avatar" id="userAvatarFallback" hidden>{{ strtoupper(substr($authUser->name, 0, 2)) }}</div>
                @else
                    <img src="" class="user-avatar-img" alt="Avatar" id="userAvatarImage" hidden>
                    <div class="user-avatar" id="userAvatarFallback">{{ $authUser ? strtoupper(substr($authUser->name, 0, 2)) : 'M' }}</div>
                @endif

                <div class="user-info">
                    <div class="user-name" id="userName">{{ $authUser->name ?? 'Tai khoan Monexa' }}</div>
                </div>
            </div>

            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-header">
                    <div class="dropdown-avatar">
                        @if($authUser?->avatar)
                            @if(str_starts_with($authUser->avatar, 'http'))
                                <img src="{{ $authUser->avatar }}" alt="Avatar" id="dropdownAvatarImage">
                            @else
                                <img src="{{ asset('storage/' . $authUser->avatar) }}" alt="Avatar" id="dropdownAvatarImage">
                            @endif
                            <span id="dropdownAvatarFallback" hidden>{{ strtoupper(substr($authUser->name, 0, 2)) }}</span>
                        @else
                            <img src="" alt="Avatar" id="dropdownAvatarImage" hidden>
                            <span id="dropdownAvatarFallback">{{ $authUser ? strtoupper(substr($authUser->name, 0, 2)) : 'M' }}</span>
                        @endif
                    </div>
                    <div class="dropdown-name" id="dropdownName">{{ $authUser->name ?? 'Tai khoan Monexa' }}</div>
                    <div class="dropdown-email" id="dropdownEmail">{{ $authUser->email ?? 'Dang nhap de tiep tuc' }}</div>
                </div>

                <div class="dropdown-menu">
                    @if($authUser)
                        <a href="/profile" class="dropdown-item">Ho so ca nhan</a>
                        @if(!$authUser->google_id)
                            <a href="/change-password" class="dropdown-item">Doi mat khau</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" style="margin:0">
                            @csrf
                            <button type="submit" class="dropdown-item logout">Dang xuat</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="dropdown-item">Dang nhap</a>
                        <a href="{{ route('register') }}" class="dropdown-item">Dang ky</a>
                        <button type="button" id="clientLogoutButton" class="dropdown-item logout" hidden>Dang xuat</button>
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
        @if(session('toast'))
            showToast(@json(session('toast')));
        @endif
        @if(session('success'))
            showToast({ type: 'success', title: 'Thanh cong', message: @json(session('success')) });
        @endif
        @if(session('error'))
            showToast({ type: 'error', title: 'Loi', message: @json(session('error')) });
        @endif
        @if(session('warning'))
            showToast({ type: 'warning', title: 'Canh bao', message: @json(session('warning')) });
        @endif
        @if(session('info'))
            showToast({ type: 'info', title: 'Thong bao', message: @json(session('info')) });
        @endif

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

            const userName = storedUser.name || 'Tai khoan Monexa';
            const userEmail = storedUser.email || 'Dang nhap bang API';
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
