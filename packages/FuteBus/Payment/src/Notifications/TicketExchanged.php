<?php

declare(strict_types=1);

namespace FuteBus\Payment\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketExchanged extends Notification
{
    public function __construct(private readonly array $data) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => __('Payment::exchange.completed'), 'message' => __('Payment::exchange.notification', $this->data),
            'url'       => route('profile.tickets.show', $this->data['booking']), ...$this->data];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject(__('Payment::exchange.completed'))->line(__('Payment::exchange.notification', $this->data))
            ->action(__('Payment::exchange.view_ticket'), route('profile.tickets.show', $this->data['booking']));
    }
}
