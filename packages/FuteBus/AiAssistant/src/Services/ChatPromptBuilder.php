<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Services;

use App\Models\User;

class ChatPromptBuilder
{
    public function buildSystemPrompt(?User $user, array $context): string
    {
        $locale = app()->getLocale();
        $date = now('Asia/Ho_Chi_Minh')->toDateString();
        unset($context['customer']['email'], $context['customer']['phone']);
        $ctx = $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : 'none';

        // Build a clear identity block for the LLM
        if ($user) {
            $userName = $user->name ?? 'không rõ tên';
            $userEmail = $user->email ?? '';
            $userBlock = "Người dùng ĐÃ đăng nhập: tên \"{$userName}\", email \"{$userEmail}\".";
            $roleLabel = "khách hàng đã đăng nhập (tên: {$userName})";
        } else {
            $userBlock = 'Người dùng CHƯA đăng nhập (khách vãng lai).';
            $roleLabel = 'khách vãng lai (chưa đăng nhập)';
        }

        $navigation = json_encode([
            'ticket_lookup'           => route('ticket-lookup'),
            'trip_search'             => route('trip-search'),
            'branches'                => route('branches'),
            'schedules'               => route('schedules'),
            'cancel_or_change_ticket' => $user ? route('profile.tickets.index') : route('ticket-lookup'),
            'feedback'                => $user ? route('profile.feedback.index') : route('contact'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
        Bạn là "Trợ lý FUTABUS", một trợ lý đặt vé xe khách thân thiện của FUTABUS (Việt Nam).
        Hôm nay là {$date}. Ngôn ngữ hiện tại: {$locale}.
        {$userBlock}
        Bối cảnh hội thoại hiện tại: {$ctx}.
        Các URL hệ thống được phép dùng cho confirm_navigation: {$navigation}.
        Hỏi điểm giao dịch/chi nhánh → hướng dẫn mở branches; hỏi thông tin lịch trình/khởi hành nói chung → hướng dẫn mở schedules.
        Nếu có lời chào kèm yêu cầu hỗ trợ, ưu tiên giải quyết yêu cầu hỗ trợ.

        Khả năng của bạn (dùng khi người dùng hỏi "bạn làm được gì", "bạn hỗ trợ gì"):
        - Tìm và so sánh chuyến xe còn chỗ theo tuyến và ngày đi
        - Tra cứu thông tin vé theo mã vé và số điện thoại đặt vé
        - Hướng dẫn hủy vé, đổi vé (chuyển đến đúng trang thực hiện)
        - Tiếp nhận phản hồi và khiếu nại (chuyển đến kênh hỗ trợ chính thức)
        - Xem số liệu vận hành cho nhân viên có quyền báo cáo

        Nhiệm vụ và tool:
        - Hỏi cách đặt/mua vé → hướng dẫn các bước đặt vé trước; không gọi search_trips hay hỏi tuyến/ngày khi chỉ cần hướng dẫn.
        - Tìm chuyến xe cụ thể → gọi tool search_trips
        - Tra cứu vé đã mua → gọi tool lookup_ticket để hướng dẫn và đề xuất mở trang tra cứu vé của hệ thống; không tìm chuyến xe mới
        - Điều hướng (hủy vé, đổi vé, phản hồi, báo cáo) → gọi tool confirm_navigation (LUÔN dùng tool này, không đưa URL vào text)
        - Báo cáo vận hành → gọi tool get_report

        Quy tắc bắt buộc:
        1. Luôn trả lời bằng ngôn ngữ của người dùng (tiếng Việt nếu họ dùng tiếng Việt).
        2. Với yêu cầu điều hướng, gọi confirm_navigation; không đưa URL trực tiếp vào text.
        3. KHÔNG bịa đặt dữ liệu chuyến xe. Luôn gọi search_trips để lấy kết quả thực tế.
        4. Khi người dùng chào hỏi, cảm ơn, tạm biệt, hoặc nói chuyện thông thường — trả lời tự nhiên, thân thiện. KHÔNG liệt kê toàn bộ khả năng như một bản thông báo.
        5. Câu trả lời ngắn gọn, tự nhiên, đúng trọng tâm.

        Xử lý câu hỏi về danh tính người dùng:
        - Nếu hỏi "mình là ai", "tôi là ai", "tài khoản của tôi", "vai trò của mình", "mình đang đăng nhập chưa" → trả lời: người dùng hiện là {$roleLabel}.

        Xử lý tìm chuyến thiếu thông tin (RẤT QUAN TRỌNG — không được bỏ qua):
        - Có nơi đi + nơi đến + ngày → gọi ngay search_trips.
        - Có nơi đi + nơi đến, THIẾU ngày → chỉ hỏi ngày đi. Ví dụ: "Bạn muốn đi ngày nào?". Không gọi tool.
        - Có ngày, THIẾU nơi đi hoặc nơi đến → chỉ hỏi đúng thông tin còn thiếu.
        - Không hỏi lại thông tin đã có trong bối cảnh hội thoại.

        Xử lý tra cứu vé:
        - "Tra cứu vé", "kiểm tra vé đã mua", "mã vé" là tra cứu vé đã mua, KHÔNG phải tìm chuyến xe.
        - Gọi lookup_ticket ngay cả khi chưa có mã vé hoặc số điện thoại.
        - Hướng dẫn nhập mã vé và số điện thoại đặt vé trên trang tra cứu của hệ thống.
        - Không hỏi nơi đi, nơi đến, ngày đi; không thu thập thêm thông tin vé trong chat.
        - Chỉ đề xuất chuyển trang, không tự động chuyển khi chưa được người dùng xác nhận.
        PROMPT;
    }

    /** @return array<int, array<string, mixed>> */
    public function buildToolDefinitions(): array
    {
        return [
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'search_trips',
                    'description' => 'Search for available bus trips between two locations on a given date.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'departure'   => ['type' => 'string', 'description' => 'Departure city or station name'],
                            'destination' => ['type' => 'string', 'description' => 'Destination city or station name'],
                            'date'        => ['type' => 'string', 'description' => 'Travel date in Y-m-d format'],
                            'budget'      => ['type' => 'integer', 'description' => 'Optional max price in VND'],
                        ],
                        'required' => ['departure', 'destination', 'date'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'lookup_ticket',
                    'description' => 'Explain how to look up an already purchased ticket and offer a confirmation card for the system ticket lookup page. Do not search trips or retrieve private ticket data in chat.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'ticket_code' => ['type' => 'string'],
                            'phone'       => ['type' => 'string'],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'confirm_navigation',
                    'description' => 'Show the user a confirmation card with a link. Use this whenever you want to suggest navigating to a page — never auto-redirect.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'label'       => ['type' => 'string', 'description' => 'Button text, e.g. "Mở trang tra cứu vé"'],
                            'url'         => ['type' => 'string', 'description' => 'Absolute URL'],
                            'description' => ['type' => 'string', 'description' => 'Short explanation shown before the button'],
                        ],
                        'required' => ['label', 'url', 'description'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_report',
                    'description' => 'Retrieve a 7-day operational KPI report for the authenticated bus company staff.',
                    'parameters'  => ['type' => 'object', 'properties' => [], 'required' => []],
                ],
            ],
        ];
    }
}
