<?php

declare(strict_types=1);

namespace FuteBus\Payment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketRefundCompleted extends Notification implements ShouldQueue
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
        return (new MailMessage)->subject(__('Payment::refunds.statuses.refunded'))
            ->line(__('Cancellation::cancellation.reference').': '.$this->receipt['reference'])
            ->line(__('Cancellation::cancellation.refund').': '.number_format($this->receipt['amount'], 0, ',', '.').' VND')
            ->action(__('Profile::tickets.title'), route('profile.tickets.index'));
    }
}
