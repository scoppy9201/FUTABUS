<?php

declare(strict_types=1);

namespace FuteBus\Profile\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class TicketHistoryService
{
    public function search(User $user, array $filters): LengthAwarePaginator
    {
        $query = $this->ownedBookings($user);

        if (! empty($filters['code'])) {
            $query->where(function (Builder $query) use ($filters): void {
                $query->where('bookings.booking_code', $filters['code'])
                    ->orWhereExists(function (Builder $tickets) use ($filters): void {
                        $tickets->selectRaw('1')
                            ->from('tickets')
                            ->whereColumn('tickets.booking_id', 'bookings.id')
                            ->where('tickets.ticket_code', $filters['code']);
                    });
            });
        }

        if (! empty($filters['date'])) {
            $query->whereDate('trips.departure_time', $filters['date']);
        }

        if (! empty($filters['route'])) {
            $keyword = '%'.$filters['route'].'%';
            $query->where(function (Builder $query) use ($keyword): void {
                $query->where('routes.origin_city', 'like', $keyword)
                    ->orWhere('routes.destination_city', 'like', $keyword)
                    ->orWhere('routes.name', 'like', $keyword);
            });
        }

        if (! empty($filters['status'])) {
            $status = $filters['status'];

            if ($status === 'payment:unpaid') {
                $query->whereNotExists(function (Builder $payments): void {
                    $payments->selectRaw('1')
                        ->from('payments')
                        ->whereColumn('payments.booking_id', 'bookings.id');
                });
            } elseif (str_starts_with($status, 'payment:')) {
                $query->where($this->latestPaymentStatus(), '=', substr($status, 8));
            } else {
                $query->where('bookings.status', $status);
            }
        }

        return $query->orderByDesc('bookings.created_at')
            ->orderByDesc('bookings.id')
            ->paginate(10)
            ->withQueryString();
    }

    public function findForUser(User $user, int $bookingId): ?stdClass
    {
        return $this->ownedBookings($user)
            ->where('bookings.id', $bookingId)
            ->first();
    }

    private function ownedBookings(User $user): Builder
    {
        return DB::table('bookings as bookings')
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->leftJoin('customers', 'customers.id', '=', 'bookings.customer_id')
            ->where(function (Builder $query) use ($user): void {
                $query->where('bookings.user_id', $user->id)
                    ->orWhere(function (Builder $guestBooking) use ($user): void {
                        $guestBooking->whereNull('bookings.user_id')
                            ->where('customers.user_id', $user->id);
                    });
            })
            ->select([
                'bookings.id',
                'bookings.booking_code',
                'bookings.seat_count',
                'bookings.total_amount',
                'bookings.status',
                'bookings.created_at',
                'trips.departure_time',
                'routes.origin_city',
                'routes.destination_city',
            ])
            ->selectSub($this->latestPaymentStatus(), 'payment_status');
    }

    private function latestPaymentStatus(): Builder
    {
        return DB::table('payments')
            ->select('status')
            ->whereColumn('payments.booking_id', 'bookings.id')
            ->orderByDesc('payments.id')
            ->limit(1);
    }
}
