<?php

declare(strict_types=1);

namespace FuteBus\Cancellation\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly array $receipt)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->receipt;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject(__('Cancellation::cancellation.success'))
            ->line(__('Cancellation::cancellation.reference').': '.$this->receipt['reference'])
            ->line($this->receipt['ticket_code'])
            ->line(__('Cancellation::cancellation.refund').': '.number_format($this->receipt['refund'], 0, ',', '.').' VND')
            ->line(__('Cancellation::cancellation.pending_note'))
            ->action(__('Cancellation::cancellation.title'), route('profile.tickets.index'));
    }
}
