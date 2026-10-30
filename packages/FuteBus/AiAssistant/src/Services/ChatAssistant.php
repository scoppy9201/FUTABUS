<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Services;

use App\Models\User;
use FuteBus\AiAssistant\Models\AiChatSession;
use FuteBus\TripSearch\Services\TripSearchService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * AI chat assistant with:
 *  - LLM-based intent recognition (falls back to regex when LLM unavailable)
 *  - Tool-based routing (navigation is a "pending_action" shown as a confirm card)
 *  - Persistent multi-turn conversation history (via AiChatSession)
 *  - Structured response format for rich frontend rendering
 */
class ChatAssistant
{
    public function __construct(
        private readonly TripSearchService $trips,
        private readonly ChatIntentMatcher $intents,
        private readonly ChatPromptBuilder $prompts,
        private readonly TripCriteriaParser $criteria,
        private readonly LlmService $llm,
        private readonly ChatReportService $reports,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  Public entry point
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Process one user turn and return a structured response array.
     *
     * @return array{
     *   message: string,
     *   session_id?: string,
     *   context?: array,
     *   trips?: array,
     *   ticket?: array,
     *   metrics?: array,
     *   links?: array,
     *   pending_action?: array{label: string, url: string, description: string},
     * }
     */
    public function reply(string $userMessage, ?string $sessionId, ?User $user = null, ?array $customer = null): array
    {
        $previousLocale = app()->getLocale();
        $text = $this->intents->normalizeIntentText($userMessage);
        $english = preg_match_all('/\b(how|where|show|find|want|my|ticket|book|cancel|change|reschedule|exchange|feedback|branches|schedules|revenue|report|trip|please|pls|plz)\b/u', $text);
        $vietnamese = preg_match_all('/\b(minh|toi|ban|ve|dat|doi|huy|tra|cuu|giup|cho|cach|huong|dan|xem|bao|cao|doanh|thu)\b/u', $text);
        if ($english >= 2 && $english > $vietnamese) {
            app()->setLocale('en');
        } elseif ($vietnamese >= 2) {
            app()->setLocale('vi');
        }
        try {
            return $this->replyInLocale($userMessage, $sessionId, $user, $customer);
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    private function replyInLocale(string $userMessage, ?string $sessionId, ?User $user, ?array $customer): array
    {
        // 1. Load or create the persistent session
        $session = $this->loadSession($sessionId, $user);
        if ($customer !== null) {
            $session->context = array_merge($session->context ?? [], ['customer' => $customer]);
        }
        $context = $session->context ?? [];

        // 2. Append the user turn to history
        $session->appendTurn('user', $userMessage);

        // 3. Try LLM-based dispatch; fall back to regex if LLM is unavailable
        try {
            if ($this->intents->isEnglishTicketChangeRequest($userMessage)) {
                $result = $this->ticketChangeHandoff($user, 'en');
            } elseif ($this->intents->isBookingGuideRequest($userMessage)) {
                $result = $this->bookingGuideReply();
            } elseif ($this->intents->isTicketLookupRequest($userMessage)) {
                $result = $this->ticketLookupHandoff($session);
            } else {
                $result = $this->dispatchWithLlm($userMessage, $session, $user, $context);
            }
        } catch (\Throwable $e) {
            report($e);
            $result = $this->dispatchWithRegex($userMessage, $session, $user, $context);
        }

        // 4. Persist assistant reply to history
        $assistantText = $result['message'] ?? '';
        $session->appendTurn('assistant', $assistantText);

        // 5. Persist updated context
        if (isset($result['context'])) {
            $session->context = array_merge($context, $result['context']);
            unset($result['context']); // don't expose raw context in response
        }

        $session->save();

        // 6. Always return session_id so JS can send it back next turn
        $result['session_id'] = $session->id;

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Session management
    // ─────────────────────────────────────────────────────────────────────────

    private function loadSession(?string $sessionId, ?User $user): AiChatSession
    {
        if ($sessionId !== null) {
            $session = AiChatSession::find($sessionId);
            if ($session !== null) {
                return $session;
            }
        }

        return AiChatSession::create([
            'user_id'        => $user?->id,
            'history'        => [],
            'context'        => [],
            'last_active_at' => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  LLM dispatch with tool calling
    // ─────────────────────────────────────────────────────────────────────────

    private function dispatchWithLlm(string $userMessage, AiChatSession $session, ?User $user, array $context): array
    {
        $systemPrompt = $this->prompts->buildSystemPrompt($user, $context);

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $session->recentHistory(10),
        );

        $tools = $this->prompts->buildToolDefinitions();

        $response = $this->llm->chat($messages, $tools, 0.7);

        // Tool call branch
        if (! empty($response['tool_calls'])) {
            $toolCall = $response['tool_calls'][0]; // handle first tool
            $funcName = $toolCall['function']['name'] ?? '';
            $funcArgs = json_decode($toolCall['function']['arguments'] ?? '{}', true);

            return $this->executeTool($funcName, $funcArgs, $session, $user, $context);
        }

        // Plain text response from LLM
        $text = trim($response['text'] ?? '');
        if ($text === '') {
            return $this->helpReply();
        }

        return ['message' => $text];
    }

    /**
     * Execute the tool the LLM chose and return a response array.
     *
     * @param  array<string, mixed>  $args
     */
    private function executeTool(string $tool, array $args, AiChatSession $session, ?User $user, array $context): array
    {
        return match ($tool) {
            'search_trips'       => $this->toolSearchTrips($args, $session),
            'lookup_ticket'      => $this->ticketLookupHandoff($session),
            'confirm_navigation' => $this->toolConfirmNavigation($args),
            'get_report'         => $this->reports->reply($user),
            default              => $this->helpReply(),
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Tool implementations
    // ─────────────────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $args */
    private function toolSearchTrips(array $args, AiChatSession $session): array
    {
        $departure = (string) ($args['departure'] ?? '');
        $destination = (string) ($args['destination'] ?? '');
        $date = (string) ($args['date'] ?? '');
        $budget = isset($args['budget']) ? (int) $args['budget'] : null;

        // Persist partial criteria to context so follow-up turns remember them
        $session->context = array_merge($session->context ?? [], array_filter([
            'departure'   => $departure ?: null,
            'destination' => $destination ?: null,
            'date'        => $date ?: null,
            'budget'      => $budget,
        ]));

        if ($departure === '' || $destination === '' || $date === '') {
            return ['message' => __('AiAssistant::app.trip_details_required')];
        }

        try {
            $results = $this->trips->search($departure, $destination, $date, 1);
        } catch (\Throwable $e) {
            report($e);

            return [
                'message'        => __('AiAssistant::app.trip_search_failed'),
                'pending_action' => [
                    'label'       => __('AiAssistant::app.search_trips'),
                    'url'         => route('trip-search'),
                    'description' => __('AiAssistant::app.trip_search_failed'),
                ],
            ];
        }

        usort($results, static fn (array $a, array $b): int => $a['price'] <=> $b['price']);
        $eligible = $budget === null ? $results : array_values(array_filter($results, static fn (array $t): bool => $t['price'] <= $budget));
        $noBudgetMatch = $budget !== null && $eligible === [];
        $selected = array_slice($eligible ?: $results, 0, 3);

        if ($selected === []) {
            return [
                'message'        => __('AiAssistant::app.no_trips'),
                'pending_action' => [
                    'label'       => __('AiAssistant::app.search_trips'),
                    'url'         => route('trip-search'),
                    'description' => __('AiAssistant::app.no_trips'),
                ],
            ];
        }

        $locale = app()->getLocale();

        return [
            'message' => $noBudgetMatch
                ? __('AiAssistant::app.no_budget_match')
                : __('AiAssistant::app.available_trips'),
            'trips' => array_map(static fn (array $trip): array => [
                'id'               => $trip['id'],
                'origin'           => $trip['origin'],
                'destination'      => $trip['destination'],
                'departure'        => Carbon::parse($trip['departure_time'])->locale($locale)->isoFormat('L HH:mm'),
                'arrival'          => Carbon::parse($trip['arrival_time'])->locale($locale)->isoFormat('L HH:mm'),
                'duration_minutes' => $trip['duration_minutes'],
                'type'             => $trip['vehicle_type'],
                'available_seats'  => $trip['available_seats'],
                'price'            => $trip['price'],
                'url'              => route('trip-booking.show', $trip['id']),
            ], $selected),
        ];
    }

    private function ticketChangeHandoff(?User $user, ?string $locale = null): array
    {
        return $this->toolConfirmNavigation([
            'label'       => __('AiAssistant::app.continue_change', [], $locale),
            'url'         => $user ? route('profile.tickets.index') : route('ticket-lookup'),
            'description' => __('AiAssistant::app.change_handoff', [], $locale),
        ]);
    }

    private function bookingGuideReply(): array
    {
        return $this->toolConfirmNavigation([
            'label'       => __('AiAssistant::app.start_booking'),
            'url'         => route('trip-search'),
            'description' => __('AiAssistant::app.booking_guide'),
        ]);
    }

    private function ticketLookupHandoff(AiChatSession $session): array
    {
        $session->context = array_merge($session->context ?? [], ['intent' => 'ticket_lookup']);

        return $this->toolConfirmNavigation([
            'label'       => __('AiAssistant::app.open_ticket_lookup'),
            'url'         => route('ticket-lookup'),
            'description' => __('AiAssistant::app.ticket_lookup_guidance'),
        ]);
    }

    /** @param array<string, mixed> $args */
    private function toolConfirmNavigation(array $args): array
    {
        $allowedUrls = array_map(static fn (string $name): string => route($name), [
            'trip-search', 'ticket-lookup', 'contact', 'profile.tickets.index',
            'profile.feedback.index', 'reporting.index', 'branches', 'schedules',
        ]);
        if (! in_array($args['url'] ?? '', $allowedUrls, true)) {
            return $this->helpReply();
        }

        return [
            'message'        => (string) ($args['description'] ?? ''),
            'pending_action' => [
                'label'       => (string) ($args['label'] ?? __('AiAssistant::app.open_page')),
                'url'         => (string) ($args['url'] ?? ''),
                'description' => (string) ($args['description'] ?? ''),
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Regex fallback (used when LLM is unavailable or API key not set)
    // ─────────────────────────────────────────────────────────────────────────

    private function dispatchWithRegex(string $message, AiChatSession $session, ?User $user, array $context): array
    {
        $text = $this->intents->normalizeIntentText($message);
        $normalized = Str::ascii($text);

        // ── "Who am I / what is my role" ──────────────────────────────────────
        if (preg_match('/\b(minh la ai|toi la ai|vai tro|tai khoan cua toi|dang nhap chua|toi dang la|minh dang la)\b/u', $normalized)) {
            if ($user) {
                $name = $user->name ?? '';
                $email = $user->email ?? '';

                return ['message' => "Bạn đang đăng nhập với tên \"{$name}\" (email: {$email})."];
            }

            return ['message' => 'Bạn hiện là khách vãng lai (chưa đăng nhập). Bạn có thể đăng nhập để xem vé và quản lý đặt chỗ dễ hơn.'];
        }

        // ── "What can you do" ─────────────────────────────────────────────────
        if (preg_match('/\b(ban co the lam gi|ban ho tro gi|ban giup gi|tro ly nay|ban lam duoc gi|chuc nang|tinh nang gi|ban biet gi)\b/u', $normalized)) {
            return ['message' => "Mình có thể giúp bạn:\n• Tìm và so sánh chuyến xe còn chỗ\n• Tra cứu thông tin vé theo mã và số điện thoại\n• Hướng dẫn hủy vé, đổi vé\n• Gửi phản hồi hoặc khiếu nại\n\nBạn cần hỗ trợ điều gì?"];
        }

        // ── Report intent ─────────────────────────────────────────────────────
        if (preg_match('/\b(doanh thu|bao cao|ti le lap day|so ghe|revenue|report|occupancy)\b/u', $normalized)) {
            return $this->reports->reply($user);
        }

        // ── Cancellation / change / feedback ──────────────────────────────────
        if (preg_match('/\b(huy ve|refund|cancel|cancellation)\b/u', $normalized)) {
            return $this->toolConfirmNavigation([
                'label'       => __('AiAssistant::app.continue_cancellation'),
                'url'         => $user ? route('profile.tickets.index') : route('ticket-lookup'),
                'description' => __('AiAssistant::app.cancellation_handoff'),
            ]);
        }
        if (preg_match('/\b(doi ve|doi chuyen)\b/u', $normalized)) {
            return $this->ticketChangeHandoff($user);
        }
        if (preg_match('/\b(khieu nai|phan anh|phan hoi|gop y|complaint|feedback)\b/u', $normalized)) {
            return $this->toolConfirmNavigation([
                'label'       => __('AiAssistant::app.customer_support'),
                'url'         => $user ? route('profile.feedback.index') : route('contact'),
                'description' => __('AiAssistant::app.feedback_handoff'),
            ]);
        }

        // Ticket lookup takes priority over trip search, even without a ticket code.
        if ($this->intents->isTicketLookupRequest($message)) {
            return $this->ticketLookupHandoff($session);
        }

        if (preg_match('/\b(diem giao dich|chi nhanh|van phong|phong ve|branches|ticket office)\b/u', $normalized)) {
            return $this->toolConfirmNavigation([
                'label'       => __('AiAssistant::app.open_branches'),
                'url'         => route('branches'),
                'description' => __('AiAssistant::app.branches_guidance'),
            ]);
        }
        if (preg_match('/\b(thong tin khoi hanh|lich trinh|lich chay|schedules|timetable)\b/u', $normalized)) {
            return $this->toolConfirmNavigation([
                'label'       => __('AiAssistant::app.open_schedules'),
                'url'         => route('schedules'),
                'description' => __('AiAssistant::app.schedules_guidance'),
            ]);
        }

        // ── Greetings ─────────────────────────────────────────────────────────
        if (preg_match('/\b(xin chao|chao ban|chao anh|chao chi|hello|hi\b|hey\b|good morning|chao buoi|alo\b)\b/u', $normalized)) {
            $name = $user?->name ? " {$user->name}" : '';
            $greetings = [
                "Xin chào{$name}! Bạn muốn tìm chuyến xe, tra cứu vé hay cần hỗ trợ gì không?",
                "Chào{$name}! Mình có thể giúp gì cho bạn hôm nay?",
                'Xin chào! Rất vui được hỗ trợ bạn. Bạn cần tìm chuyến đi đâu không?',
            ];

            return ['message' => $greetings[array_rand($greetings)]];
        }

        // ── Farewells ─────────────────────────────────────────────────────────
        if (preg_match('/\b(tam biet|thoat|bye\b|goodbye|see you|hen gap lai|chao nhe|chao anh nhe|chao chi nhe)\b/u', $normalized)) {
            $farewells = [
                'Tạm biệt! Chúc bạn có chuyến đi vui vẻ và bình an.',
                'Hẹn gặp lại! Nếu cần hỗ trợ, bạn nhắn mình bất cứ lúc nào nhé.',
                'Tạm biệt bạn! Mình luôn ở đây nếu bạn cần tìm chuyến hay tra cứu vé.',
            ];

            return ['message' => $farewells[array_rand($farewells)]];
        }

        // ── Thank you ─────────────────────────────────────────────────────────
        if (preg_match('/\b(cam on|thank you|thanks\b|tks\b|cảm ơn)\b/u', $normalized)) {
            $responses = [
                'Không có gì! Bạn cần thêm gì nữa không?',
                'Mình rất vui khi giúp được bạn! Bạn còn cần hỗ trợ gì không?',
                'Cảm ơn bạn đã liên hệ FUTABUS! Chúc bạn có chuyến đi vui vẻ.',
            ];

            return ['message' => $responses[array_rand($responses)]];
        }

        // ── Trip search — try to extract criteria ──────────────────────────────
        $criteria = $this->criteria->extractTripCriteria($message, $normalized, $context);
        if ($criteria !== null) {
            $from = $criteria['departure'];
            $to = $criteria['destination'];
            $date = $criteria['date'];

            // Persist whatever we found so the next turn can skip re-asking
            $partial = array_filter(['departure' => $from, 'destination' => $to, 'date' => $date]);
            if ($partial !== []) {
                $session->context = array_merge($context, $partial);
            }

            // Ask only for the single missing piece
            if ($from !== null && $to !== null && $date === null) {
                return ['message' => "Bạn muốn đi từ {$from} đến {$to} — bạn muốn đi ngày nào?"];
            }
            if ($from !== null && $to === null && $date !== null) {
                return ['message' => "Bạn muốn đi từ {$from} vào ngày {$date} — bạn muốn đến đâu?"];
            }
            if ($from === null && $to !== null && $date !== null) {
                return ['message' => "Bạn muốn đến {$to} vào ngày {$date} — bạn xuất phát từ đâu?"];
            }
            if ($from !== null && $to === null && $date === null) {
                return ['message' => "Bạn xuất phát từ {$from} — bạn muốn đến đâu và đi ngày nào?"];
            }
            if ($from === null || $to === null || $date === null) {
                return ['message' => __('AiAssistant::app.trip_details_required')];
            }

            return $this->toolSearchTrips([
                'departure'   => $from,
                'destination' => $to,
                'date'        => $date,
                'budget'      => $context['budget'] ?? null,
            ], $session);
        }

        return $this->helpReply();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Generic fallback
    // ─────────────────────────────────────────────────────────────────────────

    private function helpReply(): array
    {
        return [
            'message' => __('AiAssistant::app.help_prompt'),
        ];
    }
}
