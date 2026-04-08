<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Monexa') - Quản lý chi tiêu</title>
    <link rel="icon" type="images/png" href="{{ asset('favicon.png') }}">
    @vite('resources/js/app.js')
</head>
<body class="{{ cookie('theme', 'light') === 'dark' ? 'dark' : '' }}">
    <div class="topbar">
        <div class="topbar-left">
            <div class="brand-info">
                <div class="brand-logo">
                    <img src="/images/logo.png" alt="Logo">
                </div>
                <span class="brand-name">Monexa</span>
            </div>

            <div style="position:relative; flex:1; max-width:500px; margin:0 auto;">
                <div class="search-bar" id="searchBar">
                    <img src="/images/search.png" alt="Search">
                    <input type="text" id="searchInput" placeholder="Tìm kiếm giao dịch, danh mục, ngân sách..." autocomplete="off">
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
                @include('notifications._dropdown')
            </div>
            <div id="themeToggle" class="icon-btn">
                <img src="/images/dark-mode.png" alt="Theme">
            </div>
            <div class="user-profile" id="userProfile">
                @if(Auth::user()->avatar)
                    @if(str_starts_with(Auth::user()->avatar, 'http'))
                        <img src="{{ Auth::user()->avatar }}" class="user-avatar-img" alt="Avatar">
                    @else
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="user-avatar-img" alt="Avatar">
                    @endif
                @else
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                @endif

                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                </div>
            </div>

            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-header">
                    <div class="dropdown-avatar">
                        @if(Auth::user()->avatar)
                            @if(str_starts_with(Auth::user()->avatar, 'http'))
                                <img src="{{ Auth::user()->avatar }}" alt="Avatar">
                            @else
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar">
                            @endif
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        @endif
                    </div>
                    <div class="dropdown-name">{{ Auth::user()->name }}</div>
                    <div class="dropdown-email">{{ Auth::user()->email }}</div>
                </div>

                <div class="dropdown-menu">
                    <a href="/profile" class="dropdown-item">Hồ sơ cá nhân</a>
                    @if(!Auth::user()->google_id)
                        <a href="/change-password" class="dropdown-item">Đổi mật khẩu</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="margin:0">
                        @csrf
                        <button type="submit" class="dropdown-item logout">Đăng xuất</button>
                    </form>
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
    </script>
</body>
</html>
