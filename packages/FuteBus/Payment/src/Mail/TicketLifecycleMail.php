<?php

declare(strict_types=1);

namespace FuteBus\Payment\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class TicketLifecycleMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $event,
        public readonly string $customerName,
        public readonly array $tickets,
        public readonly array $details = [],
    ) {
        $this->afterCommit();
    }

    public function build(): static
    {
        $this->withSymfonyMessage(function (Email $message): void {
            $message->addPart(DataPart::fromPath(
                public_path('images/banners/futabus-ticket-email-banner.png'),
                'futabus-ticket-email-banner.png',
                'image/png',
            )->asInline()->setContentId('futa-ticket-banner@futabus.vn'));

            foreach ($this->tickets as $index => $ticket) {
                if (! empty($ticket['qr_png'])) {
                    $message->addPart((new DataPart($ticket['qr_png'], null, 'image/png'))
                        ->asInline()->setContentId('futa-ticket-qr-'.$index.'@futabus.vn'));
                    $message->addPart(new DataPart($ticket['qr_png'], $ticket['code'].'-QR.png', 'image/png'));
                }
            }
        });

        return $this->subject(__('Payment::ticket-email.subjects.'.$this->event))
            ->view('Payment::emails.ticket-lifecycle')
            ->with([
                'event'        => $this->event,
                'customerName' => $this->customerName,
                'tickets'      => $this->tickets,
                'details'      => $this->details,
            ]);
    }
}
