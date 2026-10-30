<?php

declare(strict_types=1);

namespace FuteBus\Payment\Notifications;

use Illuminate\Notifications\Notification;

class TicketExchanged extends Notification
{
    public function __construct(private readonly array $data) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => __('Payment::exchange.completed'), 'message' => __('Payment::exchange.notification', $this->data),
            'url'       => route('profile.tickets.show', $this->data['booking']), ...$this->data];
    }
}
