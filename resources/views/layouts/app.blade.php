<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Monexa') - Quản lý chi tiêu</title>
    <link rel="icon" type="images/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
   <script src="{{ asset('js/main.js') }}" defer></script>
</head>
<body class="{{ cookie('theme', 'light') === 'dark' ? 'dark' : '' }}">
    <!-- Topbar -->
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
                <img src="/images/bell.png" alt="Notifications">
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

    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <img src="{{ asset('images/home.png') }}" alt="Home">
                    </span>
                    <span class="nav-text">Trang chủ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <img src="{{ asset('images/transaction.png') }}" alt="Transaction">
                    </span>
                    <span class="nav-text">Giao dịch</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('wallets.index') }}" class="nav-link {{ request()->routeIs('wallets.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <img src="{{ asset('images/asset-allocation.png') }}" alt="Budget">
                    </span>
                    <span class="nav-text">Ngân sách</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <img src="{{ asset('images/category.png') }}" alt="Category">
                    </span>
                    <span class="nav-text">Danh mục</span>
                </a>
            </li> 
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <span class="nav-icon">
                        <img src="{{ asset('images/wallet.png') }}" alt="Ví">
                    </span>
                    <span class="nav-text">Ví</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('currency.index') }}" class="nav-link {{ request()->routeIs('currency.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <img src="{{ asset('images/exchange.png') }}" alt="Quy đổi tiền">
                    </span>
                    <span class="nav-text">Quy đổi tiền</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <span class="nav-icon">
                        <img src="{{ asset('images/coworking.png') }}" alt="Hội nhóm">
                    </span>
                    <span class="nav-text">Hội nhóm</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <span class="nav-icon">
                        <img src="{{ asset('images/settings.png') }}" alt="Cài đặt">
                    </span>
                    <span class="nav-text">Cài đặt</span>
                </a>
            </li>
        </li>
        </ul>
    </aside>

    <!-- Bubble Button -->
    <div class="ai-bubble" id="aiBubble">
        <div class="ai-chat-box" id="aiChatBox">
            <!-- Header -->
            <div class="acb-header">
                <div class="acb-avatar">
                    <img src="{{ asset('images/AI assistant.png') }}" alt="AI">
                </div>
                <div class="acb-info">
                    <div class="acb-name">Trợ lý AI Tài chính</div>
                    <div class="acb-status">Đang hoạt động</div>
                </div>
                <button class="acb-clear" id="acbClear" onclick="clearAIChat()">Xóa chat</button>
            </div>

            <!-- Welcome (hiện khi chưa chat) -->
            <div class="acb-welcome" id="acbWelcome">
                <div class="acb-welcome-icon">
                    <img src="{{ asset('images/AI assistant.png') }}" alt="AI">
                </div>
                <h3>Xin chào, {{ Auth::user()->name }}!</h3>
                <p>Hỏi tôi bất cứ điều gì về tài chính</p>
                <div class="acb-quick-grid">
                    <div class="acb-quick-card" onclick="acbSend('Phân tích chi tiêu tháng này')">
                        <div class="acb-quick-card-icon"><img src="{{ asset('images/chart.png') }}" alt=""></div>
                        Phân tích chi tiêu
                    </div>
                    <div class="acb-quick-card" onclick="acbSend('Tôi nên tiết kiệm như thế nào?')">
                        <div class="acb-quick-card-icon"><img src="{{ asset('images/saving.png') }}" alt=""></div>
                        Gợi ý tiết kiệm
                    </div>
                    <div class="acb-quick-card" onclick="acbSend('Dự báo số dư cuối năm')">
                        <div class="acb-quick-card-icon"><img src="{{ asset('images/target.png') }}" alt=""></div>
                        Dự báo tài chính
                    </div>
                    <div class="acb-quick-card" onclick="acbSend('Danh mục nào tôi chi nhiều nhất?')">
                        <div class="acb-quick-card-icon"><img src="{{ asset('images/statistics.png') }}" alt=""></div>
                        Thống kê chi tiêu
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="acb-messages" id="acbMessages" style="display:none;"></div>

            <!-- Chips -->
            <div class="acb-suggestions" id="acbChips" style="display:none;">
                <div class="acb-chip" onclick="acbSend('Phân tích chi tiêu tháng này')">Phân tích chi tiêu</div>
                <div class="acb-chip" onclick="acbSend('Gợi ý tiết kiệm')">Tiết kiệm</div>
                <div class="acb-chip" onclick="acbSend('Dự báo tài chính')">Dự báo</div>
            </div>

            <!-- Input -->
            <div class="acb-input-wrap">
                <textarea class="acb-input" id="acbInput" placeholder="Nhập tin nhắn..." rows="1"></textarea>
                <button class="acb-send" id="acbSendBtn" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 2L11 13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Bubble Toggle Button -->
        <button class="ai-bubble-btn" id="aiBubbleBtn" onclick="toggleAIChat()">
            <div class="ai-bubble-badge" id="aiBadge"></div>
            <img src="{{ asset('images/AI assistant.png') }}" alt="AI" class="chat-icon">
            <img src="{{ asset('images/close.png') }}" alt="Đóng" class="close-icon">
        </button>
    </div>

    <div class="main-content">
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>
</html>
