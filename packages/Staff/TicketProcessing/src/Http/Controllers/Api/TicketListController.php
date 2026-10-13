<?php

declare(strict_types=1);

namespace Staff\TicketProcessing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TicketListController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'code'   => ['nullable', 'string', 'max:100'], 'phone' => ['nullable', 'string', 'max:20'],
            'name'   => ['nullable', 'string', 'max:255'], 'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', Rule::in(['active', 'expired', 'used', 'cancelled', 'refunded'])], 'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $checkedAt = now();
        $company = $request->user()->bus_company_id;
        $query = DB::table('tickets')->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('trips', 'trips.id', '=', 'tickets.trip_id')->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('buses', 'buses.id', '=', 'trips.bus_id')
            ->whereColumn('bookings.trip_id', 'tickets.trip_id')
            ->where('trips.bus_company_id', $company)->where('routes.bus_company_id', $company)->where('buses.bus_company_id', $company);
        if (! empty($filters['code'])) {
            $query->where(fn ($q) => $q->where('tickets.ticket_code', trim($filters['code']))->orWhere('bookings.booking_code', trim($filters['code'])));
        }
        if (! empty($filters['phone'])) {
            $phone = trim($filters['phone']);
            $phones = [$phone];
            if (preg_match('/^0\d{9}$/', $phone) === 1) {
                $phones = [$phone, '+84'.substr($phone, 1), '84'.substr($phone, 1)];
            }
            $query->whereIn('tickets.passenger_phone', $phones);
        }
        if (! empty($filters['name'])) {
            $query->where('tickets.passenger_name', 'like', '%'.trim($filters['name']).'%');
        }
        if (! empty($filters['date'])) {
            $query->whereDate('trips.departure_time', $filters['date']);
        }
        if (! empty($filters['status'])) {
            $status = $filters['status'];
            $query->where('tickets.status', $status === 'expired' ? 'active' : $status);
            if (in_array($status, ['active', 'expired'], true)) {
                $query->where('trips.departure_time', $status === 'expired' ? '<=' : '>', $checkedAt);
            }
        }
        $page = $query->orderByDesc('tickets.id')->paginate(15, [
            'tickets.id', 'tickets.ticket_code', 'tickets.passenger_name', 'tickets.passenger_phone', 'tickets.seat_code', 'tickets.status',
            'tickets.price', 'bookings.booking_code', 'trips.departure_time', 'routes.origin_city', 'routes.destination_city',
        ]);
        $rows = $page->getCollection()->map(function (object $ticket) use ($checkedAt): array {
            $departure = Carbon::parse($ticket->departure_time);
            $expired = $ticket->status === 'active' && $departure->lessThanOrEqualTo($checkedAt);

            return ['id'         => $ticket->id, 'code' => $ticket->ticket_code, 'booking_code' => $ticket->booking_code,
                'customer'       => $ticket->passenger_name, 'phone' => $ticket->passenger_phone, 'seat' => $ticket->seat_code,
                'route'          => $ticket->origin_city.' → '.$ticket->destination_city, 'date' => $departure->format('d/m/Y'),
                'time'           => $departure->format('H:i'), 'status' => $ticket->status,
                'display_status' => $expired ? 'expired' : $ticket->status,
                'usable'         => $ticket->status === 'active' && ! $expired,
                'status_label'   => $expired ? __('StaffTickets::tickets.expired') : __('core::ticket-lookup.ticket_status.'.$ticket->status),
                'price'          => (int) $ticket->price];
        });

        return response()->json(['data' => $rows, 'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
            'total'                                                         => $page->total(), 'from' => $page->firstItem(), 'to' => $page->lastItem()]])->header('Cache-Control', 'no-store');
    }
}
