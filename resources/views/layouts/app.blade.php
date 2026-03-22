<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Monexa') - Quản lý chi tiêu</title>
    <link rel="icon" type="images/png" href="{{ asset('favicon.png') }}">
    <style>
        /* Giá trị biến toàn cục */
        :root {
            --primary: #4a90e2;
            --primary-dark: #2a5298;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark-bg: #0f1217;
            --dark-card: #191d27;
            --dark-border: rgba(255, 255, 255, 0.06);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf4 100%);
            overflow-x: hidden;
            transition: var(--transition);
        }

        /* Dark Mode */
        body.dark {
            background: var(--dark-bg);
        }

        body.dark .topbar,
        body.dark .sidebar {
            background: rgba(22, 25, 32, 0.95);
            border-color: var(--dark-border);
        }

        body.dark .brand-name {
            -webkit-text-fill-color: #fff;
        }

        body.dark .search-bar,
        body.dark .icon-btn,
        body.dark .user-profile {
            background: #1c212a;
        }

        body.dark .search-bar input {
            color: #e5e7eb;
        }

        body.dark .search-bar input::placeholder {
            color: #868686;
        }

        body.dark .user-profile:hover {
            background: #2d3748;
        }

        body.dark .user-name {
            color: #e5e7eb;
        }

        body.dark .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        body.dark .nav-text {
            background: #1a1f29;
            color: #e5e7eb;
        }

        body.dark .nav-text::before {
            border-color: transparent #1a1f29 transparent transparent;
        }

        body.dark .profile-dropdown {
            background: #1a1f29;
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark .dropdown-item {
            color: #e5e7eb;
        }

        body.dark .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        body.dark .card,
        body.dark .stat-card {
            background: var(--dark-card);
            border-color: var(--dark-border);
        }

        body.dark .card-title,
        body.dark .stat-value {
            color: #e5e7eb;
        }

        body.dark .stat-label {
            color: #9ca3af;
        }

        body.dark .chart-placeholder {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
        }

        /* Topbar */
.topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px 0 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            z-index: 1000;
            transition: var(--transition);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
            flex: 1;
        }

        .brand-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            padding: 9px;
            box-shadow: 0 4px 16px rgba(74, 144, 226, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-name {
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: #f8f9fd;
            padding: 12px 20px;
            border-radius: 12px;
            flex: 1;
            max-width: 500px;
            margin: 0 auto;
            gap: 10px;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .search-bar:focus-within {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.2);
        }

        .search-bar img {
            width: 18px;
            height: 18px;
            opacity: 0.5;
            transition: opacity 0.3s;
        }

        .search-bar:focus-within img {
            opacity: 0.8;
        }

        .search-bar input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
        }

        .search-bar input::placeholder {
            color: #9ca3af;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .icon-btn {
            width: 42px;
            height: 42px;
border-radius: 12px;
            background: #f8f9fd;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .icon-btn:hover {
            background: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(74, 144, 226, 0.25);
        }

        .icon-btn img {
            width: 20px;
            height: 20px;
            opacity: 0.6;
            transition: opacity 0.3s;
        }

        .icon-btn:hover img {
            opacity: 1;
        }

        /* User Profile */
        .user-profile {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 14px 6px 6px;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid transparent;
            background: #f8f9fd;
        }

        .user-profile:hover {
            background: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(74, 144, 226, 0.2);
        }

        .user-profile::after {
            content: "▼";
            font-size: 10px;
            margin-left: 4px;
            transition: transform 0.3s ease;
            color: #9ca3af;
        }

        .user-profile:hover::after {
            color: var(--primary);
        }

        .user-profile.active::after {
            transform: rotate(180deg);
            color: var(--primary);
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }

        .user-avatar-img {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.3;
        }

        /* Dropdown Profile */
        .profile-dropdown {
            position: absolute;
            top: 65px;
            right: 0;
            width: 280px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            z-index: 10000;
            opacity: 0;
visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .profile-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-header {
            padding: 24px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-align: center;
        }

        .dropdown-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin: 0 auto 12px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            border: 3px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }

        .dropdown-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dropdown-name {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .dropdown-email {
            font-size: 13px;
            opacity: 0.9;
        }

        .dropdown-menu {
            padding: 8px 0;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-item:hover {
            background: linear-gradient(90deg, rgba(74, 144, 226, 0.08) 0%, transparent 100%);
            padding-left: 26px;
            color: var(--primary);
        }

        .dropdown-item::before {
            content: '';
            width: 8px;
            height: 8px;
            background: currentColor;
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .dropdown-item:hover::before {
            opacity: 1;
        }

        .dropdown-item.logout {
            color: var(--danger);
            font-weight: 600;
            border-top: 1px solid #f3f4f6;
            margin-top: 4px;
        }

        .dropdown-item.logout:hover {
            background: linear-gradient(90deg, rgba(239, 68, 68, 0.08) 0%, transparent 100%);
            color: #dc2626;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 70px;
            height: calc(100vh - 70px);
            width: 80px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
z-index: 100;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.08);
            border-right: 1px solid rgba(226, 232, 240, 0.8);
            transition: var(--transition);
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .nav-item {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 16px;
            text-decoration: none;
            transition: var(--transition);
            background: transparent;
        }

        .nav-link:hover {
            background: rgba(74, 144, 226, 0.1);
            transform: scale(1.1);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 8px 20px rgba(74, 144, 226, 0.35);
        }

        .nav-icon {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: opacity(0.6);
            transition: var(--transition);
        }

        .nav-link:hover .nav-icon img,
        .nav-link.active .nav-icon img {
            filter: opacity(1);
        }

        .nav-text {
            position: absolute;
            left: 70px;
            background: white;
            color: #1f2937;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transform: translateX(-10px);
            transition: var(--transition);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            pointer-events: none;
            z-index: 1000;
        }

        .nav-text::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 6px 6px 6px 0;
            border-color: transparent white transparent transparent;
        }

        .nav-link:hover .nav-text {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        .nav-link.active .nav-text {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .nav-link.active .nav-text::before {
border-color: transparent var(--primary) transparent transparent;
        }

        /* Main Content */
        .main-content {
            margin-left: 80px;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
        }

        .content {
            padding: 35px 40px 40px;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: 0 10px 32px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 26px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 800;
            color: #1f2937;
            letter-spacing: -0.3px;
        }

        .card-menu {
            width: 36px;
            height: 36px;
            cursor: pointer;
            transition: var(--transition);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fd;
        }

        .card-menu:hover {
            background: rgba(74, 144, 226, 0.1);
            transform: rotate(90deg);
        }

        .card-menu img {
            width: 20px;
            height: 20px;
            opacity: 0.6;
        }

        .card-menu:hover img {
            opacity: 1;
        }

        .chart-placeholder {
            height: 280px;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.05) 0%, rgba(42, 82, 152, 0.05) 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 15px;
            font-weight: 600;
            border: 2px dashed rgba(74, 144, 226, 0.2);
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 28px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            box-shadow: 0 10px 32px rgba(0, 0, 0, 0.12);
            transform: translateY(-6px);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.08);
        }

        .stat-icon img {
            width: 28px;
            height: 28px;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.15) 0%, rgba(74, 144, 226, 0.05) 100%);
        }

        .stat-icon.green {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(34, 197, 94, 0.05) 100%);
        }

        .stat-icon.red {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(239, 68, 68, 0.05) 100%);
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, rgba(251, 146, 60, 0.15) 0%, rgba(251, 146, 60, 0.05) 100%);
        }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .stat-value {
            font-size: 30px;
            font-weight: 900;
            color: #1f2937;
            letter-spacing: -1px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -80px;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding: 0 20px;
            }

            .brand-name,
            .user-info {
                display: none;
            }

            .search-bar {
                max-width: 100%;
            }

            .content {
                padding: 25px 20px;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .sr-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            color: #1f2937;
            transition: background .15s;
            border-bottom: 1px solid #f3f4f6;
        }
        .sr-item:last-child { border-bottom: none; }
        .sr-item:hover { background: #f8f9fd; }

        .sr-dot {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .sr-dot.income  { background: #d1fae5; }
        .sr-dot.expense { background: #fee2e2; }
        .sr-dot.category{ background: #ede9fe; }
        .sr-dot.wallet  { background: #dbeafe; }

        .sr-label { font-size: 14px; font-weight: 600; line-height: 1.3; }
        .sr-sub   { font-size: 12px; color: #9ca3af; margin-top: 1px; }

        .sr-empty {
            padding: 24px;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
            font-weight: 500;
        }

        .sr-header {
            padding: 8px 16px 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            background: #fafafa;
            border-bottom: 1px solid #f3f4f6;
        }

        body.dark #searchDropdown {
            background: #1a1f29;
            border-color: rgba(255,255,255,0.08);
        }
        body.dark .sr-item { color: #e5e7eb; border-color: rgba(255,255,255,0.05); }
        body.dark .sr-item:hover { background: #212736; }
        body.dark .sr-header { background: #151920; color: #6b7280; border-color: rgba(255,255,255,0.05); }

        body.dark .sr-item {
            color: #e5e7eb;
            border-color: rgba(255,255,255,0.05);
            background: #1a1f29;
    }
        body.dark .sr-item:hover { background: #212736; }
        body.dark .sr-empty {
            background: #1a1f29;
            color: #9ca3af;
        }
        body.dark .sr-label { color: #e5e7eb; }
        body.dark .sr-sub { color: #6b7280; }
        body.dark .sr-dot.income   { background: rgba(16,185,129,0.15); }
        body.dark .sr-dot.expense  { background: rgba(239,68,68,0.15); }
        body.dark .sr-dot.category { background: rgba(139,92,246,0.15); }
        body.dark .sr-dot.wallet   { background: rgba(59,130,246,0.15); }

        .ai-bubble {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 9999;
        }

        .ai-bubble-btn {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 28px rgba(74,144,226,0.45);
            transition: all 0.3s ease;
            position: relative;
        }

        .ai-bubble-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 36px rgba(74,144,226,0.55);
        }

        .ai-bubble-btn img {
            width: 28px;
            height: 28px;
            object-fit: contain;
            transition: all 0.3s ease;
        }

        .ai-bubble-btn .close-icon { display: none; }
        .ai-bubble-btn.open .chat-icon { display: none; }
        .ai-bubble-btn.open .close-icon { display: block; }

        .ai-bubble-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            width: 18px;
            height: 18px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
            display: none;
        }

        .ai-bubble-badge.show { display: block; }

        .ai-chat-box {
            position: absolute;
            bottom: 72px;
            right: 0;
            width: 420px;
            height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.18);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.06);
            transform: scale(0.85) translateY(20px);
            transform-origin: bottom right;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ai-chat-box.open {
            transform: scale(1) translateY(0);
            opacity: 1;
            pointer-events: all;
        }

        body.dark .ai-chat-box {
            background: #1a1f29;
            border-color: rgba(255,255,255,0.08);
        }

        /* Chat Header */
        .acb-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .acb-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .acb-avatar img {
            width: 22px;
            height: 22px;
            object-fit: contain;
        }

        .acb-info { flex: 1; }

        .acb-name {
            font-size: 15px;
            font-weight: 700;
            color: white;
            line-height: 1.3;
        }

        .acb-status {
            font-size: 12px;
            color: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 1px;
        }

        .acb-status::before {
            content: '';
            width: 7px;
            height: 7px;
            background: #4ade80;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 2s infinite;
        }

        .acb-clear {
            background: rgba(255,255,255,0.15);
            border: none;
            border-radius: 8px;
            padding: 6px 10px;
            color: white;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .acb-clear:hover { background: rgba(255,255,255,0.25); }

        /* Messages */
        .acb-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #f8fafc;
        }

        body.dark .acb-messages { background: #0f1217; }

        .acb-messages::-webkit-scrollbar { width: 4px; }
        .acb-messages::-webkit-scrollbar-track { background: transparent; }
        .acb-messages::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }

        .acb-msg {
            display: flex;
            gap: 8px;
            animation: msgIn 0.25s ease;
        }

        @keyframes msgIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .acb-msg.user { flex-direction: row-reverse; }

        .acb-msg-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: white;
            margin-top: 2px;
        }

        .acb-msg.ai   .acb-msg-avatar { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .acb-msg.user .acb-msg-avatar { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }

        .acb-msg-bubble {
            max-width: 75%;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 13.5px;
            line-height: 1.6;
            font-weight: 500;
        }

        .acb-msg.ai   .acb-msg-bubble {
            background: white;
            color: #1f2937;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        }

        .acb-msg.user .acb-msg-bubble {
            background: var(--primary);
            color: white;
            border-bottom-right-radius: 4px;
        }

        body.dark .acb-msg.ai .acb-msg-bubble {
            background: #242938;
            color: #e5e7eb;
        }

        .acb-typing {
            display: flex;
            gap: 5px;
            padding: 4px 2px;
            align-items: center;
        }

        .acb-typing span {
            width: 7px;
            height: 7px;
            background: #9ca3af;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .acb-typing span:nth-child(2) { animation-delay: 0.2s; }
        .acb-typing span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-5px); }
        }

        /* Suggestions */
        .acb-suggestions {
            padding: 10px 16px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            flex-shrink: 0;
            background: #f8fafc;
        }

        body.dark .acb-suggestions { background: #0f1217; }

        .acb-chip {
            padding: 6px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #4b5563;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .acb-chip:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        body.dark .acb-chip {
            background: #242938;
            border-color: rgba(255,255,255,0.1);
            color: #9ca3af;
        }

        /* Input */
        .acb-input-wrap {
            padding: 12px 16px;
            border-top: 1px solid #f0f0f0;
            background: white;
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-shrink: 0;
        }

        body.dark .acb-input-wrap {
            background: #1a1f29;
            border-color: rgba(255,255,255,0.08);
        }

        .acb-input {
            flex: 1;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 13.5px;
            font-family: inherit;
            resize: none;
            min-height: 42px;
            max-height: 100px;
            line-height: 1.5;
            outline: none;
            transition: border-color 0.2s;
            background: #f8fafc;
            color: #1f2937;
            font-weight: 500;
        }

        .acb-input:focus {
            border-color: var(--primary);
            background: white;
        }

        body.dark .acb-input {
            background: #242938;
            border-color: rgba(255,255,255,0.1);
            color: #e5e7eb;
        }

        body.dark .acb-input:focus {
            border-color: var(--primary);
            background: #1a1f29;
        }

        .acb-send {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .acb-send:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(74,144,226,0.35);
        }

        .acb-send:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }

        .acb-send img {
            width: 18px;
            height: 18px;
            object-fit: contain;
        }

        /* Welcome state inside bubble */
        .acb-welcome {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 20px;
            text-align: center;
            gap: 8px;
        }

        .acb-welcome-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: white; /* đổi từ gradient xanh */
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            box-shadow: 0 8px 24px rgba(74,144,226,0.3);
            animation: float 3s ease-in-out infinite;
        }

        .acb-welcome-icon img {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }

        .acb-welcome h3 {
            font-size: 17px;
            font-weight: 800;
            color: #1f2937;
            margin: 0;
        }

        body.dark .acb-welcome h3 { color: #e5e7eb; }

        .acb-welcome p {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }

        .acb-quick-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            width: 100%;
            margin-top: 8px;
        }

        .acb-quick-card {
            padding: 12px;
            background: white;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
        }

        body.dark .acb-quick-card {
            background: #242938;
            border-color: rgba(255,255,255,0.1);
            color: #e5e7eb;
        }

        .acb-quick-card:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74,144,226,0.15);
        }

        .acb-quick-card-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: rgba(74,144,226,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 6px;
        }

        .acb-quick-card-icon img {
            width: 16px;
            height: 16px;
            object-fit: contain;
        }

        @media (max-width: 480px) {
            .ai-chat-box { width: calc(100vw - 32px); right: -14px; }
        }
    </style>
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

    

    <script>
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') document.body.classList.add('dark');

        document.getElementById('themeToggle')?.addEventListener('click', () => {
        document.body.classList.toggle('dark');
        const isDark = document.body.classList.contains('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');

    // Đồng bộ vào setting
        try {
            const s = JSON.parse(localStorage.getItem('monexa_settings') || '{}');
            s.darkMode = isDark;
            localStorage.setItem('monexa_settings', JSON.stringify(s));
        } catch {}
    });

        const userProfile = document.getElementById('userProfile');
        const dropdown = document.getElementById('profileDropdown');
        
        userProfile?.addEventListener('click', e => {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
        
        document.addEventListener('click', () => dropdown.classList.remove('show'));
        dropdown?.addEventListener('click', e => e.stopPropagation());

        (function() {
            const input    = document.getElementById('searchInput');
            const dropdown = document.getElementById('searchDropdown');
            const spinner  = document.getElementById('searchSpinner');
            if (!input) return;

            const icons = {
                income:   '<img src="/images/profits.png" style="width:18px;height:18px;object-fit:contain;">',
                expense:  '<img src="/images/budget.png" style="width:18px;height:18px;object-fit:contain;">',
                category: '<img src="/images/category.png" style="width:18px;height:18px;object-fit:contain;">',
                wallet:   '<img src="/images/wallet.png" style="width:18px;height:18px;object-fit:contain;">',
            };

            let timer;

            function closeDropdown() {
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
            }

            input.addEventListener('input', function() {
                clearTimeout(timer);
                const q = this.value.trim();
                if (q.length < 2) { closeDropdown(); spinner.style.display = 'none'; return; }

                spinner.style.display = 'block';
                timer = setTimeout(() => {
                    fetch(`/search?q=${encodeURIComponent(q)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        spinner.style.display = 'none';
                        const results = data.results || [];
                        if (results.length === 0) {
                            dropdown.innerHTML = `<div class="sr-empty">Không tìm thấy kết quả nào cho "<strong>${q}</strong>"</div>`;
                            dropdown.style.display = 'block';
                            return;
                        }

                        const groups = {};
                        results.forEach(r => {
                            const g = r.type === 'transaction' ? 'Giao dịch' : r.type === 'category' ? 'Danh mục' : 'Ngân sách';
                            if (!groups[g]) groups[g] = [];
                            groups[g].push(r);
                        });

                        let html = '';
                        for (const [group, items] of Object.entries(groups)) {
                            html += `<div class="sr-header">${group}</div>`;
                            items.forEach(item => {
                                html += `<a href="${item.url}" class="sr-item">
                                    <div class="sr-dot ${item.badge}">${icons[item.badge] || '•'}</div>
                                    <div>
                                        <div class="sr-label">${item.label}</div>
                                        <div class="sr-sub">${item.sub}</div>
                                    </div>
                                </a>`;
                            });
                        }
                        dropdown.innerHTML = html;
                        dropdown.style.display = 'block';
                    })
                    .catch(() => { spinner.style.display = 'none'; });
                }, 280);
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('#searchBar') && !e.target.closest('#searchDropdown')) {
                    closeDropdown();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeDropdown();
            });
        })();

        (function() {
            const btn       = document.getElementById('aiBubbleBtn');
            const box       = document.getElementById('aiChatBox');
            const welcome   = document.getElementById('acbWelcome');
            const messages  = document.getElementById('acbMessages');
            const chips     = document.getElementById('acbChips');
            const input     = document.getElementById('acbInput');
            const sendBtn   = document.getElementById('acbSendBtn');
            const badge     = document.getElementById('aiBadge');

            let isOpen      = false;
            let chatStarted = false;
            let hasUnread   = false;

            window.toggleAIChat = function() {
                isOpen = !isOpen;
                box.classList.toggle('open', isOpen);
                btn.classList.toggle('open', isOpen);
                if (isOpen && hasUnread) {
                    hasUnread = false;
                    badge.classList.remove('show');
                }
            };

            // Close when click outside
            document.addEventListener('click', function(e) {
                if (isOpen && !document.getElementById('aiBubble').contains(e.target)) {
                    isOpen = false;
                    box.classList.remove('open');
                    btn.classList.remove('open');
                }
            });

            input.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
                sendBtn.disabled = !this.value.trim();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (!sendBtn.disabled) doSend();
                }
            });

            sendBtn.addEventListener('click', doSend);

            function doSend() {
                const text = input.value.trim();
                if (!text) return;
                input.value = '';
                input.style.height = 'auto';
                sendBtn.disabled = true;
                window.acbSend(text);
            }

            window.acbSend = function(text) {
                if (!chatStarted) {
                    chatStarted = true;
                    welcome.style.display = 'none';
                    messages.style.display = 'flex';
                    chips.style.display = 'flex';
                }

                appendMsg('user', text);

                const thinkId = 'think-' + Date.now();
                appendMsg('ai', '<div class="acb-typing"><span></span><span></span><span></span></div>', thinkId);

                fetch('{{ route("ai-assistant.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: text })
                })
                .then(r => r.json())
                .then(data => {
                    document.getElementById(thinkId)?.remove();
                    const reply = data.success ? data.message : 'Xin lỗi, có lỗi xảy ra. Thử lại nhé!';
                    appendMsg('ai', reply);

                    if (!isOpen) {
                        hasUnread = true;
                        badge.classList.add('show');
                    }
                })
                .catch(() => {
                    document.getElementById(thinkId)?.remove();
                    appendMsg('ai', 'Không thể kết nối. Vui lòng thử lại sau.');
                });
            };

            function appendMsg(sender, content, id = null) {
                const div = document.createElement('div');
                div.className = 'acb-msg ' + sender;
                if (id) div.id = id;

                const initials = sender === 'user'
                    ? '{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}'
                    : 'AI';

                div.innerHTML = `
                    <div class="acb-msg-avatar">${initials}</div>
                    <div class="acb-msg-bubble">${content.replace(/\n/g, '<br>')}</div>
                `;
                messages.appendChild(div);
                messages.scrollTop = messages.scrollHeight;
            }

            window.clearAIChat = function() {
                chatStarted = false;
                messages.innerHTML = '';
                messages.style.display = 'none';
                chips.style.display = 'none';
                welcome.style.display = 'flex';
            };
        })();
    </script>
    
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
