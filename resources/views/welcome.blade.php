<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monexa — Quản lý chi tiêu thông minh</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:300,400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/welcome-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/monebot.css') }}">
</head>
<body>

{{-- TOPBAR --}}
<div class="topbar">
    <div class="topbar-left">
        <button type="button" class="topbar-select">
            Tiếng Việt
            <span class="topbar-caret">▾</span>
        </button>
        <div class="topbar-toggle">
            <span class="live-dot"></span>
            <span>Tương phản cao</span>
            <span class="toggle-track">
                <span class="toggle-thumb"></span>
            </span>
        </div>
        <a href="#reviews">Khách hàng</a>
        <a href="#cta">Liên hệ bộ phận chăm sóc</a>
    </div>
    <div class="topbar-right">
        <a href="#features" class="topbar-icon-link" aria-label="Tìm kiếm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"></circle>
                <path d="m20 20-3.5-3.5"></path>
            </svg>
        </a>
        @auth
            <a href="{{ route('dashboard') }}">Dashboard</a>
        @else
            <a href="{{ route('login') }}">Đăng nhập</a>
        @endauth
        <a href="#how">
            Về Monexa
            <span class="topbar-caret">▾</span>
        </a>
    </div>
</div>

{{-- NAVBAR --}}
<nav class="navbar" id="navbar">
    <a href="/" class="logo">
        <div class="logo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2L2 12l10 10 10-10L12 2z"></path>
                <path d="M8 12l3-3 5 5"></path>
            </svg>
        </div>
        <span class="logo-wordmark">Mon<em>exa</em></span>
    </a>
    <div class="nav-links">
        <a href="#features">
            Sản phẩm 
            <span class="nav-caret">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"></path>
                </svg>
            </span>
        </a>
        <a href="#how">
            Giải pháp 
            <span class="nav-caret">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"></path>
                </svg>
            </span>
        </a>
        <a href="#reviews">Bảng giá</a>
        <a href="#cta">
            Tài nguyên 
            <span class="nav-caret">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"></path>
                </svg>
            </span>
        </a>
    </div>
    <div class="nav-actions">
        @auth
            <a href="{{ route('dashboard') }}" class="btn-ghost">Dashboard</a>
        @else
            <a href="#cta" class="btn-ghost">Xem bản demo</a>
            <a href="{{ route('register') }}" class="btn-solid">Bắt đầu miễn phí</a>
        @endauth
    </div>
</nav>

{{-- HERO --}}
<section class="hero">
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>
    <div class="hero-grid-lines"></div>
    <div class="hero-inner">
        <div class="hero-left">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                Quản lý chi tiêu thông minh
            </div>
            <h1 class="hero-title">
                Kiểm soát<br>
                <em>tài chính</em> của bạn<br>
                <span class="line-2">một cách dễ dàng</span>
            </h1>
            <p class="hero-desc">
                Theo dõi thu chi, quản lý ngân sách, chia sẻ chi phí nhóm và nhận phân tích thông minh — tất cả trong một nền tảng duy nhất.
            </p>
            <div class="hero-btns">
                <a href="{{ route('register') }}" class="btn-hero-primary">
                    <span>Bắt đầu miễn phí</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px;">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
                <a href="#features" class="btn-hero-secondary">Khám phá tính năng</a>
            </div>
            <div class="hero-trust">
                <div class="trust-avatars">
                    <div class="trust-avatar">A</div>
                    <div class="trust-avatar">B</div>
                    <div class="trust-avatar">C</div>
                    <div class="trust-avatar">D</div>
                </div>
                <div>
                    <div class="trust-stars">★★★★★</div>
                    <span>Hơn 10,000+ người dùng tin tưởng</span>
                </div>
            </div>
        </div>

        <!-- DASHBOARD MOCKUP -->
        <div class="hero-right">
            <div class="mockup-card">
                <div class="mockup-header">
                    <div class="mockup-title">Tổng quan tháng này</div>
                    <div class="mockup-month">Tháng 4 / 2026</div>
                </div>
                <div class="mockup-balance">
                    <div class="mockup-balance-label">Số dư hiện tại</div>
                    <div class="mockup-balance-val"><span class="currency">₫</span>12,450,000</div>
                    <div class="mockup-balance-change">↑ +8.2% so với tháng trước</div>
                </div>
                <div class="mockup-stats">
                    <div class="mstat">
                        <div class="mstat-icon mstat-icon--income">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 19V5M5 12l7-7 7 7"/>
                            </svg>
                        </div>
                        <div class="mstat-label">Thu nhập</div>
                        <div class="mstat-val green">+18.5M</div>
                    </div>

                    <div class="mstat">
                        <div class="mstat-icon mstat-icon--expense">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12l7 7 7-7"/>
                            </svg>
                        </div>
                        <div class="mstat-label">Chi tiêu</div>
                        <div class="mstat-val red">-6.05M</div>
                    </div>

                    <div class="mstat">
                        <div class="mstat-icon mstat-icon--muted">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="m16 8-8 8"/><path d="m8 8 8 8"/>
                            </svg>
                        </div>
                        <div class="mstat-label">Ngân sách</div>
                        <div class="mstat-val">78%</div>
                    </div>

                    <div class="mstat">
                        <div class="mstat-icon mstat-icon--muted">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div class="mstat-label">Nhóm</div>
                        <div class="mstat-val">3</div>
                    </div>
                </div>
                <div class="mockup-chart">
                    <div class="chart-label">Chi tiêu 7 ngày qua</div>
                    <div class="bars" id="barsDemo">
                        <div class="bar-wrap"><div class="bar bar--35"></div><div class="bar-lbl">T2</div></div>
                        <div class="bar-wrap"><div class="bar bar--55"></div><div class="bar-lbl">T3</div></div>
                        <div class="bar-wrap"><div class="bar bar--40"></div><div class="bar-lbl">T4</div></div>
                        <div class="bar-wrap"><div class="bar bar--70"></div><div class="bar-lbl">T5</div></div>
                        <div class="bar-wrap"><div class="bar bar--45"></div><div class="bar-lbl">T6</div></div>
                        <div class="bar-wrap"><div class="bar bar--90"></div><div class="bar-lbl">T7</div></div>
                        <div class="bar-wrap"><div class="bar bar--60 active"></div><div class="bar-lbl">CN</div></div>
                    </div>
                </div>
                <div class="mockup-txns">
                    <div class="txns-label">Giao dịch gần đây</div>
                    
                    <div class="txn-item">
                        <div class="txn-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                                <line x1="6" y1="1" x2="6" y2="4"></line>
                                <line x1="10" y1="1" x2="10" y2="4"></line>
                                <line x1="14" y1="1" x2="14" y2="4"></line>
                            </svg>
                        </div>
                        <div class="txn-info">
                            <div class="txn-name">Bún bò Huế</div>
                            <div class="txn-cat">Ăn uống · Hôm nay</div>
                        </div>
                        <div class="txn-amount minus">-65,000₫</div>
                    </div>

                    <div class="txn-item">
                        <div class="txn-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--emerald-l)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </div>
                        <div class="txn-info">
                            <div class="txn-name">Lương tháng 4</div>
                            <div class="txn-cat">Thu nhập · 01/04</div>
                        </div>
                        <div class="txn-amount plus">+18,500,000₫</div>
                    </div>

                    <div class="txn-item">
                        <div class="txn-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                            </svg>
                        </div>
                        <div class="txn-info">
                            <div class="txn-name">Tiền điện nước</div>
                            <div class="txn-cat">Hóa đơn · 02/04</div>
                        </div>
                        <div class="txn-amount minus">-420,000₫</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- STATS --}}
<div class="stats-strip">
    <div class="sstat reveal"><div class="sstat-num">10K+</div><div class="sstat-lbl">Người dùng active</div></div>
    <div class="sstat reveal d1"><div class="sstat-num">50M+</div><div class="sstat-lbl">Giao dịch đã ghi nhận</div></div>
    <div class="sstat reveal d2"><div class="sstat-num">99.9%</div><div class="sstat-lbl">Uptime đảm bảo</div></div>
    <div class="sstat reveal d3"><div class="sstat-num">4.9★</div><div class="sstat-lbl">Đánh giá người dùng</div></div>
</div>

{{-- FEATURES --}}
<section class="section" id="features">
    <div class="container">
        <div class="section-header reveal">
            <div class="s-eyebrow">// Tính năng</div>
            <h2 class="s-title">Mọi thứ bạn cần để<br>làm chủ tài chính cá nhân</h2>
            <p class="s-sub">Từ ghi chép thu chi đến phân tích AI thông minh — Monexa có tất cả những gì bạn cần.</p>
        </div>
        <div class="features-grid">
        <div class="feat-card reveal d1">
            <div class="feat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
            </div>
            <div class="feat-title">Theo dõi thu chi</div>
            <div class="feat-desc">Ghi nhận mọi giao dịch nhanh chóng, phân loại theo danh mục và xem lịch sử chi tiết theo thời gian.</div>
        </div>

        <div class="feat-card reveal d2">
            <div class="feat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/>
                    <circle cx="12" cy="12" r="2"/>
                </svg>
            </div>
            <div class="feat-title">Quản lý ngân sách</div>
            <div class="feat-desc">Đặt mục tiêu ngân sách theo danh mục, nhận cảnh báo khi sắp vượt chi và theo dõi tiến độ theo tháng.</div>
        </div>

        <div class="feat-card reveal d3">
            <div class="feat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="feat-title">Chia sẻ nhóm</div>
            <div class="feat-desc">Tạo nhóm chi tiêu, chia sẻ hóa đơn nhà hàng hay du lịch và tự động tính toán ai nợ ai bao nhiêu.</div>
        </div>

        <div class="feat-card reveal d1">
            <div class="feat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/>
                    <path d="M12 7v4"/><line x1="8" y1="16" x2="8" y2="16"/><line x1="16" y1="16" x2="16" y2="16"/>
                </svg>
            </div>
            <div class="feat-title">MoneBot phân tích thông minh</div>
            <div class="feat-desc">MoneBot phân tích thói quen chi tiêu, đưa ra gợi ý tiết kiệm và dự đoán chi phí tháng tiếp theo.</div>
        </div>

        <div class="feat-card reveal d2">
            <div class="feat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
            </div>
            <div class="feat-title">Quản lý ví tiền</div>
            <div class="feat-desc">Quản lý nhiều ví (tiền mặt, ngân hàng, ví điện tử), chuyển tiền giữa các ví và đồng bộ số dư tự động.</div>
        </div>

        <div class="feat-card reveal d3">
            <div class="feat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                    <rect x="7" y="7" width="3" height="3"/><rect x="14" y="7" width="3" height="3"/><rect x="7" y="14" width="3" height="3"/>
                </svg>
            </div>
            <div class="feat-title">Chuyển tiền QR</div>
            <div class="feat-desc">Tạo mã QR để chuyển tiền nhanh giữa các ví trong hệ thống, đơn giản và bảo mật tuyệt đối.</div>
        </div>
    </div>
    </div>
</section>

{{-- HOW IT WORKS --}}
<section class="section section-dark" id="how">
    <div class="hiw-grid">
        <div class="hiw-copy">
            <div class="hiw-copy-intro">
                <div class="reveal"><div class="s-eyebrow">// Quy trình</div></div>
                <div class="reveal d1"><h2 class="s-title s-title--wide">Bắt đầu kiểm soát tài chính chỉ trong 3 bước đơn giản</h2></div>
                <div class="reveal d2"><p class="s-sub s-sub--left">Không cần kiến thức tài chính phức tạp — chỉ cần vài phút để thiết lập và bạn đã sẵn sàng.</p></div>
            </div>
            <div class="steps">
                <div class="step reveal d2">
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <div class="step-label">Bước 1</div>
                        <div class="step-title">Tạo tài khoản & Thiết lập ví</div>
                        <div class="step-desc">Đăng ký miễn phí, thêm các ví tiền của bạn (tiền mặt, ngân hàng) và đặt ngân sách hàng tháng ban đầu.</div>
                    </div>
                </div>
                <div class="step reveal d3">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <div class="step-label">Bước 2</div>
                        <div class="step-title">Ghi nhận giao dịch hàng ngày</div>
                        <div class="step-desc">Ghi lại thu nhập và chi tiêu theo danh mục. Thao tác nhanh gọn, chỉ mất vài giây mỗi giao dịch.</div>
                    </div>
                </div>
                <div class="step reveal d4">
                    <div class="step-num">3</div>
                    <div class="step-body">
                        <div class="step-label">Bước 3</div>
                        <div class="step-title">Xem phân tích & Tối ưu chi tiêu</div>
                        <div class="step-desc">Dashboard thời gian thực và MoneBot sẽ giúp bạn hiểu rõ thói quen và đưa ra quyết định tài chính tốt hơn.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI CHAT MOCKUP -->
        <div class="ai-box reveal d2">
            <div class="ai-box-top">
                <div class="ai-header">
                    <div class="ai-avatar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                            <circle cx="12" cy="5" r="2"></circle>
                            <path d="M12 7v4"></path>
                            <line x1="8" y1="16" x2="8" y2="16"></line>
                            <line x1="16" y1="16" x2="16" y2="16"></line>
                        </svg>
                    </div>
                    <div>
                        <div class="ai-name">MoneBot</div>
                        <div class="ai-status">● Đang hoạt động</div>
                    </div>
                </div>
                <div class="ai-chip">Tư vấn tài chính theo thời gian thực</div>
            </div>

            <div class="ai-messages">
                <div class="msg-user">Chi tiêu tháng này của tôi như thế nào?</div>
                <div class="msg-ai msg-ai--system">Tháng này bạn đã chi <strong class="text-danger">6,050,000₫</strong>, tăng 12% so với tháng trước. Danh mục ăn uống chiếm nhiều nhất với 2.1 triệu đồng.</div>
                
                <div class="msg-user">Tôi cần tiết kiệm thêm được không?</div>
                <div class="msg-ai msg-ai--system">
                    Hoàn toàn được! Nếu bạn giảm chi tiêu ăn uống ngoài xuống 1.5 triệu và hạn chế mua sắm, bạn có thể tiết kiệm thêm khoảng <strong class="text-primary">1.2 triệu/tháng</strong>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                    </svg>
                </div>
            </div>

            <div class="ai-insight ai-insight--system">
                <div class="ai-insight-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                        <path d="M9 18h6"></path>
                        <path d="M10 22h4"></path>
                        <path d="M12 2a7 7 0 0 0-7 7c0 2.38 1.19 4.47 3 5.74V17a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-2.26c1.81-1.27 3-3.36 3-5.74a7 7 0 0 0-7-7Z"></path>
                    </svg>
                    Phân tích tự động
                </div>
                <div class="ai-insight-text">Bạn có xu hướng chi tiêu nhiều hơn vào cuối tuần. Đặt ngân sách riêng cho cuối tuần có thể giúp bạn tiết kiệm tốt hơn.</div>
            </div>
        </div>
    </div>
</section>

{{-- TESTIMONIALS --}}
<section class="section" id="reviews">
    <div class="container">
        <div class="section-header reveal">
            <div class="s-eyebrow">// Đánh giá</div>
            <h2 class="s-title">Người dùng nói gì<br>về Monexa?</h2>
        </div>
        <div class="testi-grid">
            <div class="testi-card reveal d1">
                <div class="testi-stars">★★★★★</div>
                <div class="testi-text">"Từ khi dùng Monexa, tôi mới thực sự biết mình tiêu tiền vào đâu. Tính năng chia nhóm chi tiêu rất tiện khi đi du lịch cùng bạn bè."</div>
                <div class="testi-author">
                    <div class="testi-avatar">M</div>
                    <div><div class="testi-name">Minh Tuấn</div><div class="testi-role">Kỹ sư phần mềm, Hà Nội</div></div>
                </div>
            </div>
            <div class="testi-card reveal d2">
                <div class="testi-stars">★★★★★</div>
                <div class="testi-text">"MoneBot phân tích chi tiêu rất hay, giúp tôi tiết kiệm được gần 2 triệu mỗi tháng chỉ bằng cách thay đổi vài thói quen nhỏ."</div>
                <div class="testi-author">
                    <div class="testi-avatar">X</div>
                    <div><div class="testi-name">Xuân Huyèn</div><div class="testi-role">Giáo viên, TP. Hà Nội</div></div>
                </div>
            </div>
            <div class="testi-card reveal d3">
                <div class="testi-stars">★★★★★</div>
                <div class="testi-text">"Giao diện đẹp, dễ dùng. Tôi đặc biệt thích tính năng quản lý nhiều ví và chuyển tiền QR nhanh chóng. Highly recommended!"</div>
                <div class="testi-author">
                    <div class="testi-avatar">H</div>
                    <div><div class="testi-name">Hoàng Nam</div><div class="testi-role">Freelancer, Đà Nẵng</div></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="cta-section" id="cta">
    <div class="cta-glow"></div>
    <div class="cta-inner">
        <div class="reveal"><div class="cta-badge">// Miễn phí · Không cần thẻ tín dụng</div></div>
        <div class="reveal d1"><h2 class="cta-title">Bắt đầu làm chủ<br><em>tài chính cá nhân</em> ngay hôm nay</h2></div>
        <div class="reveal d2"><p class="cta-sub">Hơn 10,000 người đã tin dùng Monexa để quản lý tài chính thông minh hơn.<br>Đến lượt bạn trải nghiệm sự khác biệt.</p></div>
        <div class="cta-btns reveal d3">
            <a href="{{ route('register') }}" class="btn-cta-fill">                    
                <span>Đăng ký miễn phí</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px;">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                </svg></a>
            <a href="{{ route('login') }}" class="btn-cta-ghost">Đăng nhập</a>
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="footer footer-omnicom">
    <div class="footer-accent"></div>
    <div class="footer-shell">
        <div class="footer-columns">
            <div class="footer-col">
                <h4>Tính năng phổ biến</h4>
                <ul>
                    <li><a href="#features">Tất cả tính năng tài chính cá nhân</a></li>
                    <li><a href="#features">Theo dõi thu chi theo danh mục</a></li>
                    <li><a href="#features">Quản lý ngân sách theo tháng</a></li>
                    <li><a href="#features">Phân tích thói quen chi tiêu</a></li>
                    <li><a href="#how">MoneBot gợi ý tối ưu chi tiêu</a></li>
                    <li><a href="#reviews">Báo cáo trực quan dễ theo dõi</a></li>
                    <li><a href="#cta">Bắt đầu miễn phí ngay hôm nay</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Công cụ miễn phí</h4>
                <ul>
                    <li><a href="{{ route('register') }}">Tạo tài khoản miễn phí</a></li>
                    <li><a href="#how">Thiết lập ví cá nhân nhanh</a></li>
                    <li><a href="#features">Mẫu phân bổ ngân sách 50/30/20</a></li>
                    <li><a href="#features">Theo dõi mục tiêu tiết kiệm</a></li>
                    <li><a href="#reviews">Xem đánh giá từ người dùng thật</a></li>
                    <li><a href="{{ route('login') }}">Truy cập lại bảng điều khiển</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Công ty</h4>
                <ul>
                    <li><a href="/">Về Monexa</a></li>
                    <li><a href="#features">Giải pháp cho cá nhân hiện đại</a></li>
                    <li><a href="#how">Quy trình bắt đầu trong 3 bước</a></li>
                    <li><a href="#cta">Liên hệ tư vấn triển khai</a></li>
                    <li><a href="mailto:support@monexa.vn">support@monexa.vn</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Khách hàng</h4>
                <ul>
                    <li><a href="{{ route('login') }}">Đăng nhập tài khoản</a></li>
                    <li><a href="{{ route('password.request') }}">Khôi phục mật khẩu</a></li>
                    <li><a href="#reviews">Câu chuyện người dùng</a></li>
                    <li><a href="#cta">Nhận demo sản phẩm</a></li>
                    <li><a href="mailto:sales@monexa.vn">Liên hệ đội ngũ hỗ trợ</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-social-row">
            <span class="footer-divider-line"></span>
            <div class="footer-socials footer-socials--omnicom">
                <a href="#" class="footer-social-badge" aria-label="Facebook">f</a>
                <a href="#" class="footer-social-badge" aria-label="Instagram">IG</a>
                <a href="#" class="footer-social-badge" aria-label="YouTube">YT</a>
                <a href="#" class="footer-social-badge" aria-label="X">X</a>
                <a href="#" class="footer-social-badge" aria-label="Threads">TH</a>
                <a href="#" class="footer-social-badge" aria-label="TikTok">TT</a>
            </div>
            <span class="footer-divider-line"></span>
        </div>

        <div class="footer-brand-block">
            <a href="/" class="footer-logo">
                <div class="footer-logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 12l10 10 10-10L12 2z"></path>
                        <path d="M8 12l3-3 5 5"></path>
                    </svg>
                </div>
                <span class="logo-wordmark">Mon<em>exa</em></span>
            </a>
            <p class="footer-copyright">Bản quyền © {{ date('Y') }} Monexa, Inc.</p>
            <div class="footer-meta-links">
                <a href="mailto:legal@monexa.vn">Trung tâm pháp lý</a>
                <a href="mailto:privacy@monexa.vn">Chính sách bảo mật</a>
                <a href="mailto:security@monexa.vn">Bảo vệ dữ liệu</a>
                <a href="#cta">Khả năng truy cập</a>
                <a href="#cta">Quản lý cookie</a>
            </div>
        </div>
    </div>
</footer>

@include('layouts.partials.monebot')

<script>
    // Navbar scroll
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    });

    // Scroll reveal
    const reveals = [...document.querySelectorAll('.reveal')];
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const revealGroups = document.querySelectorAll(
        '.stats-strip, .features-grid, .steps, .testi-grid, .footer-columns, .hero-btns, .cta-btns'
    );

    revealGroups.forEach(group => {
        const items = [...group.querySelectorAll(':scope > .reveal')];
        items.forEach((item, index) => {
            if (!item.style.getPropertyValue('--reveal-delay')) {
                item.style.setProperty('--reveal-delay', `${Math.min(index * 90, 360)}ms`);
            }
        });
    });

    if (reduceMotion) {
        reveals.forEach(el => el.classList.add('visible'));
    } else {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    requestAnimationFrame(() => {
                        entry.target.classList.add('visible');
                    });
                } else {
                    entry.target.classList.remove('visible');
                }
            });
        }, {
            threshold: 0.14,
            rootMargin: '0px 0px -8% 0px',
        });

        reveals.forEach(el => observer.observe(el));
    }

    // Animate bars on load
    setTimeout(() => {
        document.querySelectorAll('.bar').forEach(bar => {
            bar.style.transition = 'height .6s ease';
        });
    }, 300);
</script>
</body>
</html>
