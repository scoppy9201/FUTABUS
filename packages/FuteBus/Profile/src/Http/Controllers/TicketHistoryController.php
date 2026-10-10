<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Controllers;

use App\Models\User;
use FuteBus\Profile\Http\Requests\TicketHistoryRequest;
use FuteBus\Profile\Services\TicketHistoryActionPolicy;
use FuteBus\Profile\Services\TicketHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TicketHistoryController extends Controller
{
    public function index(TicketHistoryRequest $request, TicketHistoryService $history, TicketHistoryActionPolicy $actions): View
    {
        /** @var User $user */
        $user = $request->user();
        $filters = $request->validated();

        $bookings = $history->search($user, $filters);
        $bookings->getCollection()->each(function (object $booking) use ($actions): void {
            $booking->can_contact_for_change = $actions->canContactForChange($booking);
        });

        return view('Profile::tickets', [
            'bookings'   => $bookings,
            'filters'    => $filters,
            'hasFilters' => count(array_filter($filters, fn ($value) => filled($value))) > 0,
        ]);
    }

    public function show(Request $request, TicketHistoryService $history, int $booking): View
    {
        /** @var User $user */
        $user = $request->user();
        $ticket = $history->findForUser($user, $booking);

        abort_if($ticket === null, 404);

        return view('Profile::ticket-detail', ['booking' => $ticket]);
    }
}
