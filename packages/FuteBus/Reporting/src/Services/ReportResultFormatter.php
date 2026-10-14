<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Services;

use Illuminate\Support\Collection;

class ReportResultFormatter
{
    public static function format(Collection $rows, int $count, float $amount, float $refunded, array $status): array
    {
        return [
            'totals' => ['count' => $count, 'amount' => $amount, 'gross' => $amount + $refunded, 'refunded' => $refunded],
            'rows'   => $rows, 'labels' => $rows->pluck('label')->all(),
            'series' => $rows->pluck('amount')->all(), 'refund_series' => $rows->pluck('refund_amount')->all(),
            'status' => $status,
        ];
    }
}
