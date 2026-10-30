<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Services;

use FuteBus\TripSearch\Services\BookingLocationCatalog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TripCriteriaParser
{
    public function __construct(private readonly BookingLocationCatalog $locations) {}

    /** @return array{departure: string|null, destination: string|null, date: string|null}|null */
    public function extractTripCriteria(string $message, string $normalized, array $context): ?array
    {
        // City abbreviation map (normalised ASCII keys → full name for search)
        $abbrevMap = [
            'hn'      => 'Hà Nội',
            'hcm'     => 'Hồ Chí Minh',
            'tphcm'   => 'Hồ Chí Minh',
            'sai gon' => 'Hồ Chí Minh',
            'sg'      => 'Hồ Chí Minh',
            'dn'      => 'Đà Nẵng',
            'ct'      => 'Cần Thơ',
            'hp'      => 'Hải Phòng',
            'hue'     => 'Huế',
            'vt'      => 'Vũng Tàu',
            'dl'      => 'Đà Lạt',
            'nt'      => 'Nha Trang',
            'qn'      => 'Quảng Ngãi',
            'bd'      => 'Bình Dương',
            'bn'      => 'Bình Định',
            'la'      => 'Long An',
            'tg'      => 'Tiền Giang',
            'brvt'    => 'Bà Rịa - Vũng Tàu',
        ];

        // Recognize abbreviated departure and destination names.
        $routePattern = '/(?:tu\s+)?([a-z]{2,6})\s*(?:-|→|den|toi|di den|to)\s*([a-z]{2,6})/u';
        $fromAbbrev = null;
        $toAbbrev = null;
        if (preg_match($routePattern, $normalized, $rm)) {
            $fromAbbrev = $abbrevMap[$rm[1]] ?? null;
            $toAbbrev = $abbrevMap[$rm[2]] ?? null;
        }

        $hasTripContext = isset($context['departure']) || isset($context['destination']) || isset($context['date']);
        $hasRouteAbbrev = $fromAbbrev !== null || $toAbbrev !== null;

        if (! $hasTripContext && ! $hasRouteAbbrev
            && ! preg_match('/\b(di|di chuyen|tim|ve|chuyen|trip|travel|from|to|bus|compare|so sanh|budget|ngan sach|tim chuyen|dat ve|dat chuyen|chon chuyen)\b/u', $normalized)
        ) {
            return null;
        }

        $catalog = $this->locations->all();
        $locations = collect($catalog)->flatMap(fn (array $side): array => array_merge(
            $side['provinces'] ?? [],
            collect($side['areas'] ?? [])->pluck('name')->all(),
            collect($side['areas'] ?? [])->flatMap(fn (array $a): array => collect($a['offices'] ?? [])->pluck('name')->all())->all(),
        ))->filter()->unique()->values()->all();

        $mentions = collect($locations)->filter(function (string $loc) use ($normalized): bool {
            $needle = trim(Str::ascii(mb_strtolower($loc)));

            return $needle !== '' && str_contains($normalized, $needle);
        })->sortBy(function (string $loc) use ($normalized): int {
            $pos = mb_strpos($normalized, trim(Str::ascii(mb_strtolower($loc))));

            return $pos === false ? PHP_INT_MAX : ($pos * 1000) - mb_strlen($loc);
        })->values()->all();

        // Prefer full names, then abbreviations, then the saved conversation criteria.
        $from = $mentions[0] ?? $fromAbbrev ?? $context['departure'] ?? null;
        $to = collect($mentions)->first(fn (string $l): bool => $l !== $from)
            ?? $toAbbrev
            ?? $context['destination'] ?? null;

        $date = null;
        if (preg_match('/\b(\d{1,2})[\/.-](\d{1,2})(?:[\/.-](\d{4}))?\b/', $message, $dm)) {
            $year = (int) ($dm[3] ?? now()->year);
            $date = checkdate((int) $dm[2], (int) $dm[1], $year)
                ? Carbon::createFromDate($year, (int) $dm[2], (int) $dm[1])->toDateString()
                : null;
        } elseif (preg_match('/\b(\d{4}-\d{2}-\d{2})\b/', $message, $dm)) {
            $date = $dm[1];
        } elseif (str_contains($normalized, 'tomorrow') || str_contains($normalized, 'ngay mai')) {
            $date = now()->addDay()->toDateString();
        } elseif (str_contains($normalized, 'today') || str_contains($normalized, 'hom nay')) {
            $date = now()->toDateString();
        }
        $date ??= $context['date'] ?? null;

        return ['departure' => $from, 'destination' => $to, 'date' => $date];
    }
}
