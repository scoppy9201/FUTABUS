<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketQrService
{
    public function dataUri(string $ticketCode): string
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

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
