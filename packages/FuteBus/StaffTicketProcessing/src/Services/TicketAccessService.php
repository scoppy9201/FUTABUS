<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Services;

use App\Models\User;

class TicketAccessService
{
    public function allows(User $user): bool
    {
        return $user->bus_company_id !== null
            && $user->hasPermissionTo(['booking.create', 'ticket.view']);
    }
}
