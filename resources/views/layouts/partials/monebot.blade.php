@php
    $monebotUser = auth()->user();
    $monebotInitial = $monebotUser
        ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($monebotUser->name, 0, 1))
        : 'M';
    $monebotMode = $monebotUser ? 'auth' : 'guest';
@endphp
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div
    class="monebot"
    id="monebot"
    data-mode="{{ $monebotMode }}"
    data-chat-url="{{ $monebotUser ? route('api.ai.chat') : '' }}"
    data-login-url="{{ route('login') }}"
    data-register-url="{{ route('register') }}"
    data-demo-url="{{ url('/#cta') }}"
    data-features-url="{{ url('/#features') }}"
    data-ai-url="{{ $monebotUser ? route('ai-assistant.index') : route('login') }}"
    data-user-initial="{{ $monebotInitial }}"
    data-user-name="{{ $monebotUser?->name ?? 'bạn' }}"
    @if($monebotUser)
        data-csrf="{{ csrf_token() }}"
    @endif
>
    <div class="monebot-panel" id="monebotPanel" aria-hidden="true">
        <div class="monebot-header">
            <div class="monebot-brand">
                <div class="monebot-brand-icon">
                    <img src="{{ asset('images/AI assistant.png') }}" alt="MoneBot">
                </div>
                <div class="monebot-brand-copy">
                    <div class="monebot-brand-name">MoneBot</div>
                    <div class="monebot-brand-meta">
                        <span class="monebot-brand-dot"></span>
                        <span>Powered by Monexa AI</span>
                    </div>
                </div>
            </div>
            <button type="button" class="monebot-clear" id="monebotClear">Làm mới</button>
        </div>

        <div class="monebot-scroll">
            <div class="monebot-messages" id="monebotMessages">
                @if($monebotUser)
                    <div class="monebot-msg monebot-msg--bot is-static">
                        <div class="monebot-msg-icon">
                            <img src="{{ asset('images/AI assistant.png') }}" alt="">
                        </div>
                        <div class="monebot-msg-bubble">
                            Xin chào {{ $monebotUser->name }}! MoneBot có thể giúp bạn đọc nhanh chi tiêu, ngân sách và xu hướng tài chính ngay trong lúc làm việc.
                        </div>
                    </div>
                    <div class="monebot-msg monebot-msg--bot is-static">
                        <div class="monebot-msg-icon">
                            <img src="{{ asset('images/AI assistant.png') }}" alt="">
                        </div>
                        <div class="monebot-msg-bubble monebot-msg-bubble--soft">
                            Hỏi tự nhiên hoặc chọn một lối tắt bên dưới để bắt đầu nhanh hơn.
                        </div>
                    </div>
                @else
                    <div class="monebot-msg monebot-msg--bot is-static">
                        <div class="monebot-msg-icon">
                            <img src="{{ asset('images/AI assistant.png') }}" alt="">
                        </div>
                        <div class="monebot-msg-bubble">
                            Muốn khám phá Monexa? MoneBot ở đây để giúp bạn tìm đúng tính năng, lộ trình và điểm bắt đầu phù hợp.
                        </div>
                    </div>
                    <div class="monebot-msg monebot-msg--bot is-static">
                        <div class="monebot-msg-icon">
                            <img src="{{ asset('images/AI assistant.png') }}" alt="">
                        </div>
                        <div class="monebot-msg-bubble monebot-msg-bubble--soft">
                            Bạn có thể hỏi tự do hoặc chọn nhanh một mục bên dưới như cách các chatbot định hướng trên site enterprise.
                        </div>
                    </div>
                @endif
            </div>

            <div class="monebot-quick-grid">
                @if($monebotUser)
                    <button type="button" class="monebot-quick" data-message="Phân tích chi tiêu tháng này của tôi">
                        <span class="monebot-quick-icon"><i class="fas fa-chart-pie"></i></span>
                        <span>Chi tiêu tháng này</span>
                    </button>
                    <button type="button" class="monebot-quick" data-message="Gợi ý tối ưu ngân sách cho tôi">
                        <span class="monebot-quick-icon"><i class="fas fa-magic"></i></span>
                        <span>Tối ưu ngân sách</span>
                    </button>
                    <button type="button" class="monebot-quick" data-message="Dự báo dòng tiền tháng tới">
                        <span class="monebot-quick-icon"><i class="fas fa-chart-line"></i></span>
                        <span>Dự báo dòng tiền</span>
                    </button>
                    <button type="button" class="monebot-quick" data-link="{{ route('ai-assistant.index') }}">
                        <span class="monebot-quick-icon"><i class="fas fa-robot"></i></span>
                        <span>Mở trợ lý AI</span>
                    </button>
                @else
                    <button type="button" class="monebot-quick" data-message="Tôi muốn xem bản demo Monexa">
                        <span class="monebot-quick-icon"><i class="fas fa-play-circle"></i></span>
                        <span>Xem bản demo</span>
                    </button>
                    <button type="button" class="monebot-quick" data-message="Monexa có những tính năng nổi bật nào?">
                        <span class="monebot-quick-icon"><i class="fas fa-star"></i></span>
                        <span>Tính năng nổi bật</span>
                    </button>
                    <button type="button" class="monebot-quick" data-message="Tôi muốn bắt đầu miễn phí">
                        <span class="monebot-quick-icon"><i class="fas fa-rocket"></i></span>
                        <span>Bắt đầu miễn phí</span>
                    </button>
                    <button type="button" class="monebot-quick" data-message="Tôi cần được tư vấn thêm về Monexa">
                        <span class="monebot-quick-icon"><i class="fas fa-comments"></i></span>
                        <span>Chat định hướng</span>
                    </button>
                @endif
            </div>

            <div class="monebot-note">
                @if($monebotUser)
                    MoneBot trả lời ngay trong giao diện hiện tại. Với dữ liệu cá nhân, bot sẽ dùng đúng tài khoản bạn đang đăng nhập để phản hồi.
                @else
                    MoneBot hỗ trợ định hướng nhanh trên toàn bộ trải nghiệm Monexa. Đăng nhập để bot phân tích dữ liệu tài chính cá nhân của riêng bạn.
                @endif
            </div>
        </div>

        <div class="monebot-input-wrap">
            <textarea class="monebot-input" id="monebotInput" rows="1" placeholder="Hỏi MoneBot bất kỳ điều gì..."></textarea>
            <button type="button" class="monebot-send" id="monebotSend" disabled aria-label="Gửi tin nhắn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 2 11 13"></path>
                    <path d="M22 2 15 22 11 13 2 9 22 2Z"></path>
                </svg>
            </button>
        </div>
    </div>

    <button type="button" class="monebot-toggle" id="monebotToggle" aria-label="Mở MoneBot">
        <span class="monebot-toggle-badge" id="monebotBadge">1</span>
        <img src="{{ asset('images/AI assistant.png') }}" alt="MoneBot" class="monebot-toggle-icon monebot-toggle-icon--chat">
        <span class="monebot-toggle-icon monebot-toggle-icon--close">×</span>
    </button>
</div>

<script>
    (function () {
        const root = document.getElementById('monebot');

        if (!root || root.dataset.initialized === 'true') {
            return;
        }

        root.dataset.initialized = 'true';

        const mode = root.dataset.mode || 'guest';
        const chatUrl = root.dataset.chatUrl || '';
        const csrf = root.dataset.csrf || '';
        const userInitial = root.dataset.userInitial || 'M';
        const panel = document.getElementById('monebotPanel');
        const toggle = document.getElementById('monebotToggle');
        const badge = document.getElementById('monebotBadge');
        const input = document.getElementById('monebotInput');
        const send = document.getElementById('monebotSend');
        const clear = document.getElementById('monebotClear');
        const messages = document.getElementById('monebotMessages');
        const quickButtons = root.querySelectorAll('.monebot-quick');

        let isOpen = false;
        let unreadCount = 0;

        function escapeHtml(text) {
            return text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function updateUnread(count) {
            unreadCount = count;
            if (unreadCount > 0 && !isOpen) {
                badge.textContent = String(unreadCount);
                badge.classList.add('is-visible');
            } else {
                badge.classList.remove('is-visible');
            }
        }

        function setOpen(nextState) {
            isOpen = nextState;
            panel.classList.toggle('is-open', isOpen);
            toggle.classList.toggle('is-open', isOpen);
            panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

            if (isOpen) {
                updateUnread(0);
                window.setTimeout(function () {
                    input.focus();
                }, 120);
            }
        }

        function appendMessage(sender, content, options = {}) {
            const item = document.createElement('div');
            item.className = 'monebot-msg ' + (sender === 'user' ? 'monebot-msg--user' : 'monebot-msg--bot');

            if (options.id) {
                item.id = options.id;
            }

            const iconHtml = sender === 'user'
                ? '<div class="monebot-msg-icon monebot-msg-icon--user">' + escapeHtml(userInitial) + '</div>'
                : '<div class="monebot-msg-icon"><img src="{{ asset('images/AI assistant.png') }}" alt=""></div>';

            const bubbleContent = options.allowHtml
                ? content
                : escapeHtml(content).replace(/\n/g, '<br>');

            item.innerHTML = iconHtml + '<div class="monebot-msg-bubble">' + bubbleContent + '</div>';
            messages.appendChild(item);
            messages.scrollTop = messages.scrollHeight;

            if (!isOpen && sender === 'bot') {
                updateUnread(Math.min(unreadCount + 1, 9));
            }
        }

        function appendTyping(id) {
            appendMessage('bot', '<span class="monebot-typing"><span></span><span></span><span></span></span>', {
                id: id,
                allowHtml: true,
            });
        }

        function resizeInput() {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
            send.disabled = !input.value.trim();
        }

        function buildGuestReply(message) {
            const text = message.toLowerCase();
            const loginUrl = root.dataset.loginUrl;
            const registerUrl = root.dataset.registerUrl;
            const demoUrl = root.dataset.demoUrl;
            const featuresUrl = root.dataset.featuresUrl;

            if (text.includes('demo')) {
                return 'Bạn có thể xem ngay phần giới thiệu sản phẩm tại <a class="monebot-inline-link" href="' + demoUrl + '">khu vực demo</a>. Nếu muốn trải nghiệm đầy đủ, hãy <a class="monebot-inline-link" href="' + registerUrl + '">tạo tài khoản miễn phí</a>.';
            }

            if (text.includes('tính năng') || text.includes('nổi bật') || text.includes('feature')) {
                return 'Monexa nổi bật ở theo dõi thu chi, quản lý ngân sách, chia nhóm chi tiêu và trợ lý AI MoneBot. Bạn có thể xem nhanh tại <a class="monebot-inline-link" href="' + featuresUrl + '">phần tính năng</a>.';
            }

            if (text.includes('bắt đầu') || text.includes('miễn phí') || text.includes('đăng ký')) {
                return 'Bạn có thể bắt đầu ngay với gói miễn phí tại <a class="monebot-inline-link" href="' + registerUrl + '">trang đăng ký</a>. Sau khi đăng nhập, MoneBot sẽ phân tích được dữ liệu cá nhân của riêng bạn.';
            }

            if (text.includes('tư vấn') || text.includes('sales') || text.includes('hỗ trợ')) {
                return 'Nếu bạn cần tư vấn nhanh, hãy dùng các nút điều hướng trên landing page hoặc đi tới <a class="monebot-inline-link" href="' + demoUrl + '">khu vực liên hệ</a>. MoneBot hiện đang giúp bạn định hướng đúng trang và đúng bước tiếp theo.';
            }

            return 'MoneBot đang hiện diện trên toàn hệ thống để giúp bạn định hướng nhanh. Bạn có thể <a class="monebot-inline-link" href="' + loginUrl + '">đăng nhập</a> để hỏi sâu về dữ liệu tài chính, hoặc <a class="monebot-inline-link" href="' + registerUrl + '">tạo tài khoản miễn phí</a> để bắt đầu ngay.';
        }

        function sendMessage(text) {
            const trimmed = text.trim();

            if (!trimmed) {
                return;
            }

            setOpen(true);
            appendMessage('user', trimmed);
            input.value = '';
            resizeInput();

            const typingId = 'monebot-thinking-' + Date.now();
            appendTyping(typingId);

            if (mode !== 'auth' || !chatUrl) {
                window.setTimeout(function () {
                    document.getElementById(typingId)?.remove();
                    appendMessage('bot', buildGuestReply(trimmed), { allowHtml: true });
                }, 650);
                return;
            }

            const bearerToken = localStorage.getItem('token') ?? '';
            const authHeaders = bearerToken
                ? { 'Authorization': 'Bearer ' + bearerToken }
                : { 'X-CSRF-TOKEN': csrf };

            fetch(chatUrl, {
                method: 'POST',
                headers: Object.assign({
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }, authHeaders),
                body: JSON.stringify({ message: trimmed }),
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    document.getElementById(typingId)?.remove();

                    if (data && data.success) {
                        appendMessage('bot', data.message || 'MoneBot đã nhận được yêu cầu và đang chuẩn bị phản hồi chi tiết hơn.');
                        return;
                    }

                    appendMessage('bot', 'MoneBot chưa thể trả lời ngay lúc này. Bạn thử lại sau vài giây nhé.');
                })
                .catch(function () {
                    document.getElementById(typingId)?.remove();
                    appendMessage('bot', 'Mất kết nối tạm thời rồi. Bạn thử lại sau hoặc mở trang trợ lý AI đầy đủ để tiếp tục nhé.');
                });
        }

        toggle.addEventListener('click', function () {
            setOpen(!isOpen);
        });

        clear.addEventListener('click', function () {
            messages.querySelectorAll('.monebot-msg:not(.is-static)').forEach(function (item) {
                item.remove();
            });
            updateUnread(0);
            setOpen(true);
        });

        input.addEventListener('input', resizeInput);

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();

                if (!send.disabled) {
                    sendMessage(input.value);
                }
            }
        });

        send.addEventListener('click', function () {
            sendMessage(input.value);
        });

        quickButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const link = button.dataset.link;
                const message = button.dataset.message;

                setOpen(true);

                if (link) {
                    window.location.href = link;
                    return;
                }

                if (message) {
                    sendMessage(message);
                }
            });
        });

        document.addEventListener('click', function (event) {
            if (isOpen && !root.contains(event.target)) {
                setOpen(false);
            }
        });

        resizeInput();
    })();
</script>
