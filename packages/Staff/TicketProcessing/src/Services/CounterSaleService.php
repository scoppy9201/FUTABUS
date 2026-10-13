<?php

declare(strict_types=1);

namespace Staff\TicketProcessing\Services;

use App\Models\User;
use FuteBus\Core\Services\TripSearchService;
use FuteBus\Payment\Services\PaymentPreviewBuilder;
use FuteBus\Payment\Services\SePayPaymentService;
use FuteBus\Payment\Services\SePayQrService;
use FuteBus\Payment\Services\TicketQrService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CounterSaleService
{
    public function __construct(
        private readonly TripSearchService $search,
        private readonly PaymentPreviewBuilder $previews,
        private readonly SePayPaymentService $payments,
        private readonly SePayQrService $qr,
        private readonly TicketQrService $ticketQr,
    ) {}

    public function trips(User $operator, array $criteria): array
    {
        $ids = $this->scope($operator)->pluck('trips.id')->all();
        $isReturn = $criteria['trip_type'] === 'round_trip' && ($criteria['direction'] ?? 'outbound') === 'return';

        $trips = $this->search->search(
            $isReturn ? $criteria['destination'] : $criteria['departure'],
            $isReturn ? $criteria['departure'] : $criteria['destination'],
            $isReturn ? $criteria['return_date'] : $criteria['departure_date'],
            (int) $criteria['quantity'],
        );

        return array_values(array_filter($trips, fn (array $trip): bool => in_array($trip['id'], $ids, true)));
    }

    public function quote(User $operator, array $data): array
    {
        $trip = collect($this->trips($operator, $data))->firstWhere('id', (int) $data['trip_id']);
        abort_if($trip === null, 404);
        $seats = collect($trip['seats'])->whereIn('id', $data['seats']);
        if ($seats->count() !== count($data['seats']) || $seats->contains('sold', true)) {
            throw ValidationException::withMessages(['seats' => __('core::booking.seats_unavailable')]);
        }

        $leg = [...$data, 'trip_type' => 'one_way', 'name' => '', 'phone' => '', 'email' => '', 'pickup_mode' => 'station', 'dropoff_mode' => 'station'];
        if (($data['direction'] ?? 'outbound') === 'return') {
            $leg = [...$leg, 'departure' => $data['destination'], 'destination' => $data['departure'], 'departure_date' => $data['return_date']];
        }
        $preview = $this->previews->build($leg, (int) $trip['id'], null);

        return ['amount' => count($preview['seat_ids']) * (int) $preview['trip']['fare'], 'fare' => (int) $preview['trip']['fare'], 'boarding_time' => $preview['pickup']['arrival_time']];
    }

    public function preview(User $operator, array $data): array
    {
        $this->quote($operator, $data);
        if ($data['trip_type'] === 'round_trip') {
            abort_unless($this->scope($operator)->where('trips.id', $data['outbound_trip'])->exists(), 404);
        }
        $preview = $this->previews->build($data, (int) $data['trip_id'], null);
        unset($preview['user_id'], $preview['token']);

        return ['id'     => null, 'status' => 'draft', 'amount' => count($preview['seat_ids']) * (int) $preview['trip']['fare'] + count($preview['outbound_seat_ids'] ?? []) * (int) ($preview['outbound_trip']['fare'] ?? 0),
            'expires_at' => null, 'qr_url' => null, 'reference' => null, 'preview' => $preview, 'booking' => null, 'tickets' => []];
    }

    public function create(User $operator, array $data): array
    {
        if (! $this->payments->ready()) {
            throw new HttpResponseException(response()->json(['message' => __('StaffTickets::sale.unavailable'), 'code' => 'payment_unavailable'], 503));
        }

        return DB::transaction(function () use ($operator, $data): array {
            $trip = $this->scope($operator)->where('trips.id', $data['trip_id'])->lockForUpdate()->first(['trips.id', 'trips.price']);
            abort_if($trip === null, 404);
            $fingerprint = hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
            $existing = DB::table('sepay_payment_intents')->where('token', $data['idempotency_key'])->first();
            if ($existing !== null) {
                $snapshot = json_decode($existing->snapshot, true);
                abort_unless(($snapshot['operator']['id'] ?? null) === $operator->id && ($snapshot['operator']['fingerprint'] ?? null) === $fingerprint, 409);

                return $this->show($operator, $existing->token);
            }
            abort_if(isset($data['quoted_fare']) && (int) $data['quoted_fare'] !== (int) $trip->price, 409);
            if (! collect($this->trips($operator, $data))->contains('id', (int) $data['trip_id'])) {
                throw ValidationException::withMessages(['seats' => __('core::booking.seats_unavailable')]);
            }
            $preview = $this->previews->build($data, (int) $data['trip_id'], null);
            if (isset($preview['outbound_trip'])) {
                abort_unless($this->scope($operator)->where('trips.id', $preview['outbound_trip']['id'])->exists(), 404);
            }
            $preview['token'] = $data['idempotency_key'];
            $preview['operator'] = ['id' => $operator->id, 'company_id' => $operator->bus_company_id, 'fingerprint' => $fingerprint];
            $this->payments->createIntent($preview);

            return $this->show($operator, $preview['token']);
        });
    }

    public function show(User $operator, string $token): array
    {
        $intent = DB::table('sepay_payment_intents')->where('token', $token)->first();
        abort_if($intent === null, 404);
        $preview = json_decode($intent->snapshot, true);
        abort_unless(($preview['operator']['id'] ?? null) === $operator->id && ($preview['operator']['company_id'] ?? null) === $operator->bus_company_id && $this->scope($operator)->where('trips.id', $intent->trip_id)->exists(), 404);
        $status = $intent->status === 'pending' && $intent->expires_at <= now()->toDateTimeString() ? 'expired' : $intent->status;
        unset($preview['operator'], $preview['user_id'], $preview['token']);
        $result = ['id' => $token, 'status' => $status, 'amount' => (int) $intent->amount, 'reference' => $intent->code, 'expires_at' => $intent->expires_at,
            'qr_url'    => $status === 'pending' ? $this->qr->generate((int) $intent->amount, $intent->code) : null, 'preview' => $preview, 'booking' => null, 'tickets' => []];
        if ($status === 'paid' && DB::table('payments')->where('booking_id', $intent->booking_id)->where('payment_code', $intent->code)->where('status', 'completed')->exists()) {
            $result['booking'] = DB::table('bookings')->where('id', $intent->booking_id)->first(['booking_code']);
            $result['tickets'] = DB::table('tickets')->whereIn('booking_id', array_filter([$intent->booking_id, $intent->secondary_booking_id]))->orderBy('id')->get(['ticket_code', 'trip_id', 'seat_code', 'price'])->map(fn (object $ticket): array => [...(array) $ticket,
                'code' => $ticket->ticket_code, 'seat' => $ticket->seat_code,
                'trip' => isset($preview['outbound_trip']) && $ticket->trip_id === $preview['outbound_trip']['id'] ? $preview['outbound_trip'] : $preview['trip'],
                'qr'   => $this->ticketQr->dataUri($ticket->ticket_code)])->all();
        }

        return $result;
    }

    public function cancelPreview(User $operator, string $token): array
    {
        return DB::transaction(function () use ($operator, $token): array {
            DB::table('sepay_payment_intents')->where('token', $token)->lockForUpdate()->first();
            $sale = $this->show($operator, $token);
            abort_if(in_array($sale['status'], ['paid', 'needs_review'], true), 409);
            DB::table('sepay_payment_intents')->where('token', $token)->where('status', 'pending')->update(['status' => 'expired', 'updated_at' => now()]);

            return $this->show($operator, $token);
        });
    }

    private function scope(User $operator): Builder
    {
        return DB::table('trips')->join('buses', 'buses.id', '=', 'trips.bus_id')->join('routes', 'routes.id', '=', 'trips.route_id')
            ->where('trips.bus_company_id', $operator->bus_company_id)->where('buses.bus_company_id', $operator->bus_company_id)->where('routes.bus_company_id', $operator->bus_company_id)
            ->where('routes.code', 'not like', 'DEMO-%');
    }
}
