<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Controllers;

use App\Models\User;
use FuteBus\Payment\Services\TicketQrService;
use FuteBus\Profile\Http\Requests\TicketHistoryRequest;
use FuteBus\Profile\Services\TicketHistoryActionPolicy;
use FuteBus\Profile\Services\TicketHistoryService;
use FuteBus\Profile\Services\TicketReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class TicketHistoryController extends Controller
{
    public function index(TicketHistoryRequest $request, TicketHistoryService $history, TicketHistoryActionPolicy $actions): View
    {
        /** @var User $user */
        $user = $request->user();
        $filters = $request->validated();

        $bookings = $history->search($user, $filters);
        $bookings->getCollection()->each(function (object $booking) use ($actions, $history): void {
            $booking->can_contact_for_change = $actions->canContactForChange($booking);
            $booking->journey_stage = $history->stageForBooking($booking);
        });

        return view('Profile::tickets', [
            'bookings'    => $bookings,
            'filters'     => $filters,
            'hasFilters'  => count(array_filter($filters, fn ($value) => filled($value))) > 0,
        ]);
    }

    public function show(Request $request, TicketHistoryService $history, TicketQrService $ticketQr, int $booking): Response
    {
        /** @var User $user */
        $user = $request->user();
        $detail = $history->detailForUser($user, $booking);

        abort_if($detail === null, 404);

        $qrCodes = [];
        if ($detail['payment']?->status === 'completed'
            && $detail['stage'] !== 'cancelled'
            && ! str_starts_with($detail['booking']->booking_code, 'DEMOHIST')) {
            foreach ($detail['tickets'] as $ticket) {
                if (in_array($ticket->status, ['active', 'used'], true)) {
                    $qrCodes[$ticket->ticket_code] = $ticketQr->dataUri($ticket->ticket_code);
                }
            }
        }

        $data = [...$detail, 'qrCodes' => $qrCodes];

        if ($request->query('modal') === '1') {
            return response()->view('Profile::partials.ticket-journey', [...$data, 'inDialog' => true])
                ->header('Cache-Control', 'private, no-store');
        }

        return response()->view('Profile::ticket-detail', $data)
            ->header('Cache-Control', 'private, no-store');
    }

    public function review(Request $request, TicketReviewService $reviews, int $booking): JsonResponse|RedirectResponse
    {
        $input = $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:300'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $reviews->submit($user, $booking, (int) $input['rating'], $input['comment'] ?? null);

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Profile::tickets.journey.review_saved')]);
        }

        return redirect()->route('profile.tickets.show', $booking);
    }
}
