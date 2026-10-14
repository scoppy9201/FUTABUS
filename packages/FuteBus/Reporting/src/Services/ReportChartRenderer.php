<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Services;

use RuntimeException;

class ReportChartRenderer
{
    private const ORANGE = [249, 115, 22];

    private const BLUE = [59, 130, 246];

    private const GREEN = [22, 163, 74];

    private const RED = [220, 38, 38];

    private const TEAL = [15, 118, 110];

    private const GRID = [226, 232, 240];

    private const MUTED = [100, 116, 139];

    private const INK = [23, 32, 51];

    public function render(array $report, array $filters): array
    {
        if (! function_exists('imagecreatetruecolor')) {
            throw new RuntimeException('GD is required to render PDF report charts.');
        }

        return [
            'bar'   => $this->bar($report, $filters),
            'line'  => $this->line($report),
            'donut' => $this->donut($report, $filters),
        ];
    }

    private function bar(array $report, array $filters): string
    {
        $width = 1400;
        $height = 380;
        $left = 86;
        $top = 24;
        $right = 28;
        $bottom = 58;
        $plotWidth = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;
        $values = array_map('floatval', $report['series']);
        $refunds = array_map(static fn ($value): float => (float) ($value ?? 0), $report['refund_series']);
        $maximum = max(array_merge([1.0], $values, $refunds));
        $image = $this->image($width, $height);
        $orange = $this->color($image, self::ORANGE);
        $red = $this->color($image, self::RED);
        $grid = $this->color($image, self::GRID);
        $muted = $this->color($image, self::MUTED);
        $hasRefunds = max($refunds ?: [0]) > 0;
        $step = $plotWidth / max(count($values), 1);

        for ($tick = 0; $tick <= 4; $tick++) {
            $y = (int) round($top + $plotHeight * $tick / 4);
            imageline($image, $left, $y, $width - $right, $y, $grid);
            imagestring($image, 3, 4, $y - 7, number_format($maximum * (4 - $tick) / 4, 0), $muted);
        }

        foreach ($values as $index => $value) {
            $center = $left + $step * ($index + .5);
            $groupWidth = min(46, $step * .68);
            $barWidth = max(2, (int) round($hasRefunds ? ($groupWidth / 2) - 2 : $groupWidth));
            $barX = (int) round($center - ($hasRefunds ? $groupWidth / 2 : $barWidth / 2));
            $barHeight = (int) round($plotHeight * ($value / $maximum));
            imagefilledrectangle($image, $barX, $top + $plotHeight - $barHeight, $barX + $barWidth, $top + $plotHeight, $orange);

            if ($hasRefunds) {
                $refundHeight = (int) round($plotHeight * ($refunds[$index] / $maximum));
                $refundX = (int) round($center + 2);
                imagefilledrectangle($image, $refundX, $top + $plotHeight - $refundHeight, $refundX + $barWidth, $top + $plotHeight, $red);
            }

            $label = $report['labels'][$index] ?? '';
            $label = preg_match('/^\d{4}-\d{2}-\d{2}$/', $label) ? substr($label, 5) : $label;
            $stride = max(1, (int) ceil(count($values) / 12));
            if ($index % $stride === 0 || $index === count($values) - 1) {
                imagestring($image, 3, (int) ($center - (imagefontwidth(3) * strlen($label) / 2)), $height - 34, $label, $muted);
            }
        }

        return $this->encode($image);
    }

    private function line(array $report): string
    {
        $width = 700;
        $height = 380;
        $left = 70;
        $top = 24;
        $right = 20;
        $bottom = 58;
        $plotWidth = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;
        $rows = $report['rows'];
        $values = array_map(static fn ($row): float => (float) $row->count, $rows->all());
        $maximum = max(array_merge([1.0], $values));
        $image = $this->image($width, $height);
        $orange = $this->color($image, self::ORANGE);
        $grid = $this->color($image, self::GRID);
        $muted = $this->color($image, self::MUTED);
        $step = $plotWidth / max(count($values), 1);
        $points = [];

        for ($tick = 0; $tick <= 4; $tick++) {
            $y = (int) round($top + $plotHeight * $tick / 4);
            imageline($image, $left, $y, $width - $right, $y, $grid);
            imagestring($image, 3, 4, $y - 7, number_format($maximum * (4 - $tick) / 4, 2), $muted);
        }

        foreach ($values as $index => $value) {
            $points[] = [
                (int) round($left + $step * ($index + .5)),
                (int) round($top + $plotHeight * (1 - ($value / $maximum))),
            ];
        }

        imagesetthickness($image, 4);
        for ($index = 1; $index < count($points); $index++) {
            imageline($image, $points[$index - 1][0], $points[$index - 1][1], $points[$index][0], $points[$index][1], $orange);
        }
        imagesetthickness($image, 1);

        foreach ($points as $index => [$x, $y]) {
            imagefilledellipse($image, $x, $y, 13, 13, $orange);
            imagefilledellipse($image, $x, $y, 7, 7, $this->color($image, [255, 255, 255]));
            $label = $report['labels'][$index] ?? '';
            $label = preg_match('/^\d{4}-\d{2}-\d{2}$/', $label) ? substr($label, 5) : $label;
            $stride = max(1, (int) ceil(count($points) / 6));
            if ($index % $stride === 0 || $index === count($points) - 1) {
                imagestring($image, 3, (int) ($x - (imagefontwidth(3) * strlen($label) / 2)), $height - 34, $label, $muted);
            }
        }

        return $this->encode($image);
    }

    private function donut(array $report, array $filters): string
    {
        $width = 700;
        $height = 380;
        $image = $this->image($width, $height);
        $centerX = (int) ($width / 2);
        $centerY = (int) ($height / 2);
        $diameter = 280;
        $values = array_filter($report['status'], static fn ($value): bool => (float) $value > 0);
        $total = array_sum($values);
        $isRevenue = in_array($filters['type'], ['overview', 'revenue'], true);

        if ($total > 0) {
            $palette = $isRevenue
                ? [self::ORANGE, self::BLUE, self::GREEN, [100, 116, 139]]
                : ['completed' => self::ORANGE, 'pending' => self::TEAL, 'failed' => self::BLUE, 'refunded' => self::RED, 'active' => self::TEAL, 'used' => self::BLUE, 'cancelled' => self::MUTED, 'scheduled' => self::BLUE, 'departed' => self::ORANGE, 'arrived' => self::GREEN];
            $angle = -90;
            foreach (array_values($values) as $index => $value) {
                $sweep = (int) round(((float) $value / $total) * 360);
                if ($sweep < 1) {
                    continue;
                }

                $key = array_keys($values)[$index];
                $rgb = $isRevenue ? ($palette[$index] ?? [100, 116, 139]) : ($palette[$key] ?? [124, 58, 237]);
                imagefilledarc($image, $centerX, $centerY, $diameter, $diameter, $angle, $angle + $sweep, $this->color($image, $rgb), IMG_ARC_PIE);
                $angle += $sweep;
            }

            imagefilledellipse($image, $centerX, $centerY, 166, 166, $this->color($image, [255, 255, 255]));
            $centerLabel = $isRevenue ? number_format($total / 1_000_000, 2).'M' : number_format($total);
            $ink = $this->color($image, self::INK);
            imagestring($image, 5, (int) ($centerX - imagefontwidth(5) * strlen($centerLabel) / 2), $centerY - 8, $centerLabel, $ink);
        }

        return $this->encode($image);
    }

    private function image(int $width, int $height)
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));

        return $image;
    }

    private function color($image, array $rgb): int
    {
        return imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
    }

    private function encode($image): string
    {
        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        return base64_encode($png);
    }
}
