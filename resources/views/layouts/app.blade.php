<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Monexa') - Quản lý chi tiêu</title>
    <link rel="icon" type="images/png" href="{{ asset('favicon.png') }}">
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
                <a href="{{ route('groups.index') }}" class="nav-link {{ request()->routeIs('groups.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <img src="{{ asset('images/coworking.png') }}" alt="Hội nhóm">
                    </span>
                    <span class="nav-text">Hội nhóm</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('settings.index') }}" 
                  class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
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
<!-- ===== GLOBAL TOAST SYSTEM ===== -->
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

    <style>
        .g-toast {
            background: white;
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.13);
            display: flex;
            gap: 12px;
            align-items: flex-start;
            border-left: 4px solid;
            pointer-events: all;
            position: relative;
            overflow: hidden;
            animation: toastIn 0.35s cubic-bezier(0.4,0,0.2,1);
            transition: opacity 0.3s, transform 0.3s;
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(50px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .g-toast.hiding { opacity: 0; transform: translateX(50px); pointer-events: none; }
        .g-toast-progress {
            position: absolute;
            bottom: 0; left: 0;
            height: 3px;
            border-radius: 0;
            animation: progressShrink linear forwards;
            opacity: 0.35;
        }
        @keyframes progressShrink {
            from { width: 100%; }
            to   { width: 0%; }
        }
        .g-toast.success { border-left-color: #10b981; }
        .g-toast.error   { border-left-color: #ef4444; }
        .g-toast.warning { border-left-color: #f59e0b; }
        .g-toast.info    { border-left-color: #4a90e2; }
        .g-toast.success .g-toast-progress { background: #10b981; }
        .g-toast.error   .g-toast-progress { background: #ef4444; }
        .g-toast.warning .g-toast-progress { background: #f59e0b; }
        .g-toast.info    .g-toast-progress { background: #4a90e2; }
        .g-toast-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .g-toast.success .g-toast-icon { background: #d1fae5; }
        .g-toast.error   .g-toast-icon { background: #fee2e2; }
        .g-toast.warning .g-toast-icon { background: #fef3c7; }
        .g-toast.info    .g-toast-icon { background: #dbeafe; }
        .g-toast-body { flex: 1; min-width: 0; }
        .g-toast-title { font-size: 14px; font-weight: 700; color: #1f2937; margin-bottom: 2px; line-height: 1.3; }
        .g-toast-msg   { font-size: 13px; color: #6b7280; line-height: 1.5; font-weight: 500; }
        .g-toast-action {
            display: inline-block; margin-top: 7px; padding: 4px 12px;
            border-radius: 8px; font-size: 12px; font-weight: 700;
            text-decoration: none; background: var(--primary); color: white !important;
            transition: opacity 0.2s;
        }
        .g-toast-action:hover { opacity: 0.85; }
        .g-toast-close {
            background: none; border: none; font-size: 20px; color: #9ca3af;
            cursor: pointer; padding: 0; line-height: 1; flex-shrink: 0;
            transition: color 0.2s; margin-top: -2px;
        }
        .g-toast-close:hover { color: #374151; }

        /* Dark mode */
        body.dark .g-toast         { background: #1e2433; box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
        body.dark .g-toast-title   { color: #f3f4f6; }
        body.dark .g-toast-msg     { color: #9ca3af; }
        body.dark .g-toast-close:hover { color: #e5e7eb; }
        body.dark .g-toast.success .g-toast-icon { background: rgba(16,185,129,0.15); }
        body.dark .g-toast.error   .g-toast-icon { background: rgba(239,68,68,0.15); }
        body.dark .g-toast.warning .g-toast-icon { background: rgba(245,158,11,0.15); }
        body.dark .g-toast.info    .g-toast-icon { background: rgba(74,144,226,0.15); }
        </style>

        <script>
        // Đọc settings từ localStorage
        function _getToastSettings() {
            try { return { toastEnabled: true, toastPosition: 'top-right', toastDuration: 5, toastSound: false, ...JSON.parse(localStorage.getItem('monexa_settings') || '{}') }; }
            catch { return { toastEnabled: true, toastPosition: 'top-right', toastDuration: 5, toastSound: false }; }
        }

        function _applyToastPosition(pos) {
            const c = document.getElementById('toastContainer');
            if (!c) return;
            c.style.top    = pos.includes('bottom') ? 'auto' : '84px';
            c.style.bottom = pos.includes('bottom') ? '20px' : 'auto';
            c.style.left   = pos.includes('left')   ? '20px' : 'auto';
            c.style.right  = pos.includes('left')   ? 'auto' : '20px';
        }

        // Áp dụng vị trí ngay khi load
        _applyToastPosition(_getToastSettings().toastPosition);

        window.showToast = function({ type = 'info', title, message = '', action = null, id = null, duration = null }) {
            const s = _getToastSettings();
            if (s.toastEnabled === false) return;

            const ms = duration ?? (s.toastDuration * 1000);
            if (id && document.querySelector(`[data-toast-id="${id}"]`)) return;
            if (id && sessionStorage.getItem('tdismiss_' + id)) return;

            _applyToastPosition(s.toastPosition);

            const icons = {
            success: '<img src="/images/check.png"   style="width:20px;height:20px;object-fit:contain;">',
            error:   '<img src="/images/warning.png" style="width:20px;height:20px;object-fit:contain;">',
            warning: '<img src="/images/alert.png"   style="width:20px;height:20px;object-fit:contain;">',
            info:    '<img src="/images/info.png"    style="width:20px;height:20px;object-fit:contain;">',
        };
            const el = document.createElement('div');
            el.className = `g-toast ${type}`;
            if (id) el.dataset.toastId = id;

            el.innerHTML = `
                <div class="g-toast-icon">${icons[type] || 'ℹ️'}</div>
                <div class="g-toast-body">
                    <div class="g-toast-title">${title}</div>
                    ${message ? `<div class="g-toast-msg">${message}</div>` : ''}
                    ${action ? `<a href="${action.url}" class="g-toast-action">${action.label}</a>` : ''}
                </div>
                <button class="g-toast-close" onclick="dismissToast(this,'${id}')">&times;</button>
                <div class="g-toast-progress" style="animation-duration:${ms}ms"></div>
            `;

            document.getElementById('toastContainer')?.appendChild(el);

            // Sound
            if (s.toastSound) {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.frequency.value = type === 'error' ? 300 : 600;
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                    osc.start(); osc.stop(ctx.currentTime + 0.3);
                } catch {}
            }

            if (ms > 0) setTimeout(() => dismissToast(el.querySelector('.g-toast-close'), id), ms);
        };

        window.dismissToast = function(btn, id = null) {
            const toast = btn?.closest?.('.g-toast');
            if (!toast) return;
            toast.classList.add('hiding');
            if (id && id !== 'null') sessionStorage.setItem('tdismiss_' + id, '1');
            setTimeout(() => toast.remove(), 320);
        };

        // Đọc flash session từ Laravel
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
</html>
