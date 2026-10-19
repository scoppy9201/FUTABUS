<?php

namespace Tests\Feature\Payment;

use FuteBus\Payment\Mail\TicketLifecycleMail;
use FuteBus\Payment\Services\TicketQrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class TicketLifecycleMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_receipt_places_embedded_qr_after_banner_and_before_ticket_details(): void
    {
        config(['mail.default' => 'array']);
        $mail = new TicketLifecycleMail('booking_confirmed', 'Nguyen Van A', [
            $this->ticket('TICKET-01', 0),
            $this->ticket('TICKET-02', 1),
        ]);
        $mail->to('customer@example.com');

        Mail::mailer('array')->sendNow($mail);

        $sent = Mail::mailer('array')->getSymfonyTransport()->messages()->first();
        $message = $sent->getOriginalMessage();
        $this->assertInstanceOf(Email::class, $message);
        $html = $message->getHtmlBody();
        $bannerPosition = strpos($html, 'cid:futa-ticket-banner@futabus.vn');
        $qrPosition = strpos($html, 'cid:futa-ticket-qr-0@futabus.vn');
        $detailsPosition = strpos($html, __('Payment::ticket-email.ticket_title'));
        $this->assertIsInt($bannerPosition);
        $this->assertIsInt($qrPosition);
        $this->assertIsInt($detailsPosition);
        $this->assertLessThan($qrPosition, $bannerPosition);
        $this->assertLessThan($detailsPosition, $qrPosition);
        $this->assertStringContainsString('TICKET-01', $html);
        $this->assertStringContainsString('TICKET-02', $html);

        $parts = $message->getAttachments();
        $this->assertCount(5, $parts);
        $this->assertSame('futa-ticket-banner@futabus.vn', $parts[0]->getContentId());
        $this->assertSame('futa-ticket-qr-0@futabus.vn', $parts[1]->getContentId());
        $this->assertSame('futa-ticket-qr-1@futabus.vn', $parts[3]->getContentId());
        $this->assertSame('image/png', $parts[1]->getMediaType().'/'.$parts[1]->getMediaSubtype());
        $banner = getimagesize(public_path('images/banners/futabus-ticket-email-banner.png'));
        $this->assertSame([1600, 470], [$banner[0], $banner[1]]);
    }

    public function test_cancellation_and_refund_emails_never_embed_a_cancelled_ticket_qr(): void
    {
        config(['mail.default' => 'array']);
        $mail = new TicketLifecycleMail('cancellation_requested', 'Nguyen Van A', [
            $this->ticket('CANCELLED-01', 0, false),
        ], ['reference' => 'CANCEL-REF', 'refund' => 315000, 'fee' => 35000]);
        $mail->to('customer@example.com');

        Mail::mailer('array')->sendNow($mail);

        $message = Mail::mailer('array')->getSymfonyTransport()->messages()->first()->getOriginalMessage();
        $html = $message->getHtmlBody();
        $this->assertStringContainsString('cid:futa-ticket-banner@futabus.vn', $html);
        $this->assertStringNotContainsString('futa-ticket-qr-', $html);
        $this->assertStringContainsString('CANCEL-REF', $html);
        $this->assertStringContainsString(number_format(315000, 0, ',', '.'), $html);
        $this->assertCount(1, $message->getAttachments());
    }

    public function test_exchange_email_uses_the_new_ticket_qr_and_identifies_the_invalidated_ticket(): void
    {
        config(['mail.default' => 'array']);
        $mail = new TicketLifecycleMail('exchange_completed', 'Nguyen Van A', [
            $this->ticket('NEW-TICKET-01', 0),
        ], ['old_ticket_code' => 'OLD-TICKET-01']);
        $mail->to('customer@example.com');

        Mail::mailer('array')->sendNow($mail);

        $message = Mail::mailer('array')->getSymfonyTransport()->messages()->first()->getOriginalMessage();
        $html = $message->getHtmlBody();
        $this->assertStringContainsString('OLD-TICKET-01', $html);
        $this->assertStringContainsString('NEW-TICKET-01', $html);
        $this->assertStringContainsString('cid:futa-ticket-qr-0@futabus.vn', $html);
        $this->assertCount(3, $message->getAttachments());
    }

    public function test_refund_receipt_includes_the_reconciled_bank_reference_without_a_ticket_qr(): void
    {
        config(['mail.default' => 'array']);
        $mail = new TicketLifecycleMail('refund_completed', 'Nguyen Van A', [
            $this->ticket('REFUNDED-01', 0, false),
        ], [
            'reference'      => 'CANCEL-REF-01',
            'refund'         => 315000,
            'bank_reference' => 'BANK-REF-01',
            'bank_paid_at'   => now()->toDateTimeString(),
        ]);
        $mail->to('customer@example.com');

        Mail::mailer('array')->sendNow($mail);

        $message = Mail::mailer('array')->getSymfonyTransport()->messages()->first()->getOriginalMessage();
        $html = $message->getHtmlBody();
        $this->assertStringContainsString('CANCEL-REF-01', $html);
        $this->assertStringContainsString('BANK-REF-01', $html);
        $this->assertStringContainsString(number_format(315000, 0, ',', '.'), $html);
        $this->assertStringNotContainsString('futa-ticket-qr-', $html);
        $this->assertCount(1, $message->getAttachments());
    }

    public function test_ticket_qr_is_a_valid_png_for_email_clients(): void
    {
        $png = app(TicketQrService::class)->png('FUTA-EMAIL-QR-01');

        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $png);
    }

    private function ticket(string $code, int $index, bool $includeQr = true): array
    {
        return [
            'code'            => $code,
            'booking_code'    => 'BOOKING-01',
            'passenger_name'  => 'Nguyen Van A',
            'passenger_phone' => '0900000000',
            'seat'            => 'A'.($index + 1),
            'price'           => 300000,
            'departure_at'    => now()->addDay()->toDateTimeString(),
            'arrival_at'      => now()->addDay()->addHours(8)->toDateTimeString(),
            'origin'          => 'TP. Hồ Chí Minh',
            'destination'     => 'Đà Lạt',
            'bus_type'        => 'Limousine',
            'pickup_name'     => 'Bến xe Miền Đông Mới',
            'pickup_address'  => 'Long Bình, TP. HCM',
            'dropoff_name'    => 'Bến xe Đà Lạt',
            'dropoff_address' => 'Đà Lạt, Lâm Đồng',
            'qr_png'          => $includeQr ? 'png-bytes-'.$index : null,
        ];
    }
}
