<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Services;

use Illuminate\Support\Str;

class ChatIntentMatcher
{
    /** Normalize intent phrases only; never replace the original message or identifiers. */
    public function normalizeIntentText(string $message): string
    {
        $text = Str::ascii(mb_strtolower(trim($message)));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $phrases = [
            'hd dat ve' => 'huong dan dat ve',
            'tra cu ve' => 'tra cuu ve',
            'check ve'  => 'tra cuu ve',
            'bc dt'     => 'bao cao doanh thu',
            'tkt'       => 'ticket',
            'tickt'     => 'ticket',
            'reshedule' => 'reschedule',
            'feedbak'   => 'feedback',
            'branchs'   => 'branches',
            'revnue'    => 'revenue',
            'rpt'       => 'report',
        ];
        foreach ($phrases as $phrase => $replacement) {
            $text = preg_replace('/\b'.preg_quote($phrase, '/').'\b/u', $replacement, $text) ?? $text;
        }

        return $text;
    }

    public function isEnglishTicketChangeRequest(string $message): bool
    {
        $text = $this->normalizeIntentText($message);

        return (bool) preg_match('/\b(?:(?:change|reschedule|exchange)\s+(?:(?:my|a|the|our)\s+)?(?:bus\s+)?(?:ticket|booking|trip|journey|travel date|departure date)|change\s+(?:the\s+)?date\s+(?:of|on)\s+(?:(?:my|a|the)\s+)?ticket)\b/u', $text);
    }

    public function isBookingGuideRequest(string $message): bool
    {
        $text = $this->normalizeIntentText($message);

        return (bool) preg_match('/\b(?:(?:cach|huong dan|lam (?:sao|the nao)|cac buoc|quy trinh).*?(?:dat|mua)\s+ve|(?:dat|mua)\s+ve.*?(?:nhu the nao|the nao|ra sao|lam sao)|how (?:do i|can i|to).*?(?:book|buy)|booking (?:guide|instructions))\b/u', $text);
    }

    public function isTicketLookupRequest(string $message): bool
    {
        $text = $this->normalizeIntentText($message);

        return (bool) preg_match('/\b(tra cuu(?:\s+ve)?|ma (?:so )?ve|kiem tra ve|xem (?:lai )?ve|ve (?:da mua|da dat|cua toi|cua minh)|ticket lookup|look up (?:my |a )?ticket|check (?:my |a )?ticket|booking code|ticket code)\b/u', $text);
    }
}
