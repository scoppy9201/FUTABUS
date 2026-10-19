<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketQrService
{
    public function dataUri(string $ticketCode): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->svg($ticketCode));
    }

    public function svg(string $ticketCode): string
    {
        $svg = (string) QrCode::format('svg')
            ->size(320)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($ticketCode);

        $logo = base64_encode(file_get_contents(public_path('images/booking-guide/futa-logo.png')));
        $overlay = '<rect x="128" y="128" width="64" height="64" rx="7" fill="#fff"/>'
            .'<image x="136" y="133" width="48" height="52" href="data:image/png;base64,'.$logo.'"/>';
        $svg = substr_replace($svg, $overlay, strrpos($svg, '</svg>'), 0);

        return $svg;
    }

    public function png(string $ticketCode): string
    {
        $matrix = Encoder::encode($ticketCode, ErrorCorrectionLevel::H())->getMatrix();
        $modules = $matrix->getWidth();
        $scale = intdiv(360, $modules + 4);
        $offset = intdiv(360 - ($modules * $scale), 2);
        $image = imagecreatetruecolor(360, 360);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);

        for ($y = 0; $y < $matrix->getHeight(); $y++) {
            for ($x = 0; $x < $modules; $x++) {
                if ($matrix->get($x, $y) > 0) {
                    $left = $offset + $x * $scale;
                    $top = $offset + $y * $scale;
                    imagefilledrectangle($image, $left, $top, $left + $scale - 1, $top + $scale - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        return $png;
    }
}
