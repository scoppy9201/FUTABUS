<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Middleware;

use Closure;
use FuteBus\StaffTicketProcessing\Services\TicketAccessService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTicketAccess
{
    public function __construct(private TicketAccessService $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() && $this->access->allows($request->user()), 403, __('StaffDashboard::staff.ticket_access_hint'));

        return $next($request);
    }
}
