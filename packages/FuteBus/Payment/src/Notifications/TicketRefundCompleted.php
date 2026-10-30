<?php

declare(strict_types=1);

namespace FuteBus\Payment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->receipt;
    }
}
