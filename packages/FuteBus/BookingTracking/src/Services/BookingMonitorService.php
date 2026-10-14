<?php

declare(strict_types=1);

namespace FuteBus\BookingTracking\Services;

use App\Models\User;
use FuteBus\BusCompanyManagement\Models\BusCompany as BusCompanyRecord;
use FuteBus\Payment\Models\Payment as PaymentRecord;
use FuteBus\TicketManagement\Models\Ticket as TicketRecord;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingMonitorService
{
    public function visible(User $actor): Builder
    {
        abort_unless($actor->is_active && $actor->email_verified_at !== null
            && ($actor->isAdmin() || $actor->isBusCompany() || $actor->isStaff())
            && $actor->hasPermissionTo('booking.view'), 403);
        $company = $actor->bus_company_id;
        if (! $company && $actor->isBusCompany()) {
            $company = BusCompanyRecord::query()->toBase()->whereRaw('LOWER(email) = ?', [mb_strtolower($actor->email)])->value('id');
        }
        abort_unless($actor->isAdmin() || $company, 403);

        $bookings = DB::table('bookings as b')->join('trips as t', 't.id', '=', 'b.trip_id')
            ->join('routes as r', 'r.id', '=', 't.route_id')->join('customers as c', 'c.id', '=', 'b.customer_id')
            ->selectRaw("b.id, 'booking' as kind, b.booking_code as code, b.status, b.total_amount as amount,
                b.created_at, t.departure_time, r.origin_city, r.destination_city,
                c.full_name as customer_name, c.phone as customer_phone, c.email as customer_email,
                NULL as snapshot, NULL as expires_at, b.seat_count, b.notes, t.status as trip_status, t.id as trip_id")
            ->whereColumn('r.bus_company_id', 't.bus_company_id')
            ->when(! $actor->isAdmin(), fn (Builder $query) => $query->where('t.bus_company_id', $company));

        foreach (['intent' => 'trip_id', 'outbound' => 'secondary_trip_id'] as $kind => $tripColumn) {
            $intent = DB::table('sepay_payment_intents as i')->join('trips as t', 't.id', '=', 'i.'.$tripColumn)
                ->join('routes as r', 'r.id', '=', 't.route_id')
                ->whereNull($kind === 'intent' ? 'i.booking_id' : 'i.secondary_booking_id')
                ->selectRaw("i.id, ? as kind, i.code, CASE WHEN i.status = 'pending' AND i.expires_at <= ?
                    THEN 'expired' ELSE i.status END as status, i.amount, i.created_at,
                    t.departure_time, r.origin_city, r.destination_city,
                    NULL as customer_name, NULL as customer_phone, NULL as customer_email,
                    i.snapshot, i.expires_at, NULL as seat_count, NULL as notes, t.status as trip_status, t.id as trip_id",
                    [$kind, now()->toDateTimeString()])
                ->whereColumn('r.bus_company_id', 't.bus_company_id')
                ->when(! $actor->isAdmin(), fn (Builder $query) => $query->where('t.bus_company_id', $company));
            $bookings->unionAll($intent);
        }

        return DB::query()->fromSub($bookings, 'records');
    }

    public function filter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(fn (Builder $query) => $query->where('code', 'like', $term)
                ->orWhere('customer_name', 'like', $term)->orWhere('customer_phone', 'like', $term)
                ->orWhereRaw("CAST(JSON_EXTRACT(snapshot, '$.customer.name') AS CHAR) LIKE ?", [$term])
                ->orWhereRaw("CAST(JSON_EXTRACT(snapshot, '$.customer.phone') AS CHAR) LIKE ?", [$term]));
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['from'])) {
            $query->where('departure_time', '>=', $filters['from'].' 00:00:00');
        }
        if (! empty($filters['to'])) {
            $query->where('departure_time', '<=', $filters['to'].' 23:59:59');
        }

        return $query;
    }

    public function present(object $record): object
    {
        if ($record->kind !== 'booking') {
            $snapshot = json_decode($record->snapshot, true) ?: [];
            $outbound = $record->kind === 'outbound';
            $record->customer_name = $snapshot['customer']['name'] ?? null;
            $record->customer_phone = $snapshot['customer']['phone'] ?? null;
            $record->customer_email = $snapshot['customer']['email'] ?? null;
            $record->seats = $snapshot[$outbound ? 'outbound_seats' : 'seats'] ?? [];
            $record->seat_count = count($record->seats);
            $fare = $snapshot[$outbound ? 'outbound_trip' : 'trip']['fare'] ?? null;
            $record->amount = $fare === null ? null : (int) $fare * $record->seat_count;
            $record->notes = json_encode([
                'pickup'  => $snapshot[$outbound ? 'outbound_pickup' : 'pickup'] ?? [],
                'dropoff' => $snapshot[$outbound ? 'outbound_dropoff' : 'dropoff'] ?? [],
            ]);
        }
        unset($record->snapshot);

        return $record;
    }

    public function payments(int $booking): Collection
    {
        // A paid round trip has one payment, attached to its primary booking.
        $original = $this->originalPaymentBookings(collect([$booking]))->get($booking);

        return PaymentRecord::query()->toBase()->whereIn('booking_id', array_filter([$booking, $original]))
            ->orderByDesc('id')->get(['payment_code', 'transaction_id', 'amount', 'method', 'status', 'paid_at']);
    }

    public function attachPaymentStatuses(Collection $records): void
    {
        $ids = $records->where('kind', 'booking')->pluck('id');
        $originals = $this->originalPaymentBookings($ids);
        $payments = PaymentRecord::query()->toBase()->whereIn('booking_id', $ids->merge($originals->values()))
            ->orderByDesc('id')->get(['booking_id', 'status'])->unique('booking_id')->keyBy('booking_id');
        foreach ($records as $record) {
            $record->payment_status = $record->kind === 'booking'
                ? ($payments->get($record->id)?->status ?? $payments->get($originals->get($record->id))?->status)
                : null;
        }
    }

    private function originalPaymentBookings(Collection $ids): Collection
    {
        return DB::table('sepay_payment_intents as i')
            ->join('bookings as primary_booking', 'primary_booking.id', '=', 'i.booking_id')
            ->join('bookings as secondary_booking', 'secondary_booking.id', '=', 'i.secondary_booking_id')
            ->whereIn('i.secondary_booking_id', $ids)->where('i.status', 'paid')
            ->whereColumn('primary_booking.trip_id', 'i.trip_id')
            ->whereColumn('secondary_booking.trip_id', 'i.secondary_trip_id')
            ->pluck('i.booking_id', 'i.secondary_booking_id');
    }

    public function detail(User $actor, string $kind, int $id): array
    {
        $record = $this->visible($actor)->where('kind', $kind)->where('id', $id)->first();
        abort_if($record === null, 404);
        $record = $this->present($record);
        $tickets = $kind === 'booking' ? TicketRecord::query()->toBase()->where('booking_id', $id)->where('trip_id', $record->trip_id)
            ->orderBy('seat_code')->get(['id', 'ticket_code', 'seat_code', 'passenger_name', 'passenger_phone', 'price', 'status', 'used_at']) : collect();
        $refunds = $tickets->isEmpty() ? collect() : DB::table('ticket_cancellations as c')
            ->leftJoin('ticket_refunds as r', 'r.cancellation_id', '=', 'c.id')->whereIn('c.ticket_id', $tickets->pluck('id'))
            ->get(['c.ticket_id', 'c.reference', 'c.reason', 'c.note', 'c.fee_amount', 'c.refund_amount', 'c.created_at', 'r.status', 'r.bank_paid_at']);

        return ['record' => $record, 'tickets' => $tickets, 'refunds' => $refunds,
            'payments'   => $kind === 'booking' ? $this->payments($id) : collect(),
            'stops'      => json_decode($record->notes ?? '', true) ?: []];
    }
}
