<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Services;

use App\Models\User;
use FuteBus\BookingManagement\Models\BookedSeat as BookedSeatRecord;
use FuteBus\BookingManagement\Models\Booking as BookingRecord;
use FuteBus\BusManagement\Models\Bus as BusRecord;
use FuteBus\Payment\Models\Payment as PaymentRecord;
use FuteBus\Payment\Models\SePayPaymentIntent as SePayPaymentIntentRecord;
use FuteBus\Payment\Services\SeatReservationService;
use FuteBus\Payment\Services\TicketIssuer;
use FuteBus\Payment\Services\TicketQrService;
use FuteBus\RouteManagement\Models\BusRoute as BusRouteRecord;
use FuteBus\SeatManagement\Models\SeatLayout as SeatLayoutRecord;
use FuteBus\TicketManagement\Models\Ticket as TicketRecord;
use FuteBus\TripManagement\Models\Trip as TripRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashSaleService
{
    public function __construct(private readonly TicketIssuer $issuer, private readonly SeatReservationService $seats, private readonly TicketQrService $qr) {}

    public function show(User $operator, string $token): ?array
    {
        $payment = PaymentRecord::query()->toBase()->where('payment_code', 'CASH'.$token)->where('method', 'cash')->where('status', 'completed')->first();
        if ($payment === null) {
            return null;
        }
        $receipt = json_decode($payment->payload, true, 512, JSON_THROW_ON_ERROR);
        abort_unless($receipt['operator_id'] === $operator->id && $receipt['company_id'] === $operator->bus_company_id, 404);
        $preview = $receipt['preview'];
        $tickets = TicketRecord::query()->toBase()->whereIn('booking_id', $receipt['booking_ids'])->orderBy('id')->get()
            ->map(fn (object $ticket): array => ['code' => $ticket->ticket_code, 'seat' => $ticket->seat_code, 'price' => $ticket->price,
                'trip'                                  => $ticket->trip_id === $preview['trip']['id'] ? $preview['trip'] : $preview['outbound_trip'], 'qr' => $this->qr->dataUri($ticket->ticket_code)])->all();

        return ['id'     => $token, 'status' => 'paid', 'amount' => (int) $payment->amount, 'reference' => $payment->payment_code,
            'expires_at' => null, 'qr_url' => null, 'preview' => $preview,
            'booking'    => BookingRecord::query()->toBase()->where('id', $payment->booking_id)->first(['booking_code']), 'tickets' => $tickets];
    }

    public function collect(User $operator, string $token, array $preview): array
    {
        return DB::transaction(function () use ($operator, $token, $preview): array {
            $legs = [['trip' => $preview['trip'], 'ids' => $preview['seat_ids']]];
            if (isset($preview['outbound_trip'])) {
                $legs[] = ['trip' => $preview['outbound_trip'], 'ids' => $preview['outbound_seat_ids']];
            }
            usort($legs, fn (array $a, array $b): int => $a['trip']['id'] <=> $b['trip']['id']);
            $trips = [];
            foreach ($legs as $leg) {
                $trips[$leg['trip']['id']] = TripRecord::query()->toBase()->where('id', $leg['trip']['id'])->lockForUpdate()->first();
            }
            if (($receipt = $this->show($operator, $token)) !== null) {
                return $receipt;
            }
            $intent = SePayPaymentIntentRecord::query()->toBase()->where('token', $token)->lockForUpdate()->first();
            if ($intent !== null) {
                $snapshot = json_decode($intent->snapshot, true);
                abort_unless(($snapshot['operator']['id'] ?? null) === $operator->id && ($snapshot['operator']['company_id'] ?? null) === $operator->bus_company_id, 404);
                abort_unless($intent->status === 'pending' && $intent->expires_at > now()->toDateTimeString(), 409);
            } else {
                abort_if(now()->timestamp >= $preview['created_at'] + 600, 410);
            }
            $amount = 0;
            foreach ($legs as $leg) {
                $trip = $trips[$leg['trip']['id']];
                abort_unless($trip !== null && $trip->bus_company_id === $operator->bus_company_id
                    && BusRecord::query()->toBase()->where('id', $trip->bus_id)->where('bus_company_id', $operator->bus_company_id)->exists()
                    && BusRouteRecord::query()->toBase()->where('id', $trip->route_id)->where('bus_company_id', $operator->bus_company_id)->where('code', 'not like', 'DEMO-%')->exists(), 404);
                $ids = array_values(array_unique(array_map('intval', $leg['ids'])));
                $this->seats->releaseCancelled((int) $trip->id, $ids);
                $held = SePayPaymentIntentRecord::query()->toBase()->where('token', '!=', $token)->where('status', 'pending')->where('expires_at', '>', now())
                    ->where(fn ($q) => $q->where('trip_id', $trip->id)->orWhere('secondary_trip_id', $trip->id))->get()
                    ->contains(fn (object $hold): bool => count(array_intersect($ids, json_decode((int) $hold->trip_id === (int) $trip->id ? $hold->seat_ids : $hold->secondary_seat_ids, true) ?: [])) > 0);
                if ($trip->status !== 'scheduled' || $trip->departure_time <= now()->toDateTimeString()
                    || (int) $trip->price !== (int) $leg['trip']['fare'] || count($ids) !== count($leg['ids'])
                    || SeatLayoutRecord::query()->toBase()->where('bus_id', $trip->bus_id)->whereIn('id', $ids)->where('is_available', true)->count() !== count($ids)
                    || BookedSeatRecord::query()->toBase()->where('trip_id', $trip->id)->whereIn('seat_layout_id', $ids)->exists() || $held) {
                    throw ValidationException::withMessages(['seats' => __('core::booking.seats_unavailable')]);
                }
                $amount += count($ids) * (int) $trip->price;
            }
            $code = 'CASH'.$token;
            $bookingIds = [$this->issuer->issue((object) ['code' => $code], $preview, $preview['seat_ids'])];
            if (isset($preview['outbound_trip'])) {
                $bookingIds[] = $this->issuer->issue((object) ['code' => $code], $preview, $preview['outbound_seat_ids'], true);
            }
            PaymentRecord::query()->toBase()->insert(['payment_code' => $code, 'booking_id' => $bookingIds[0], 'amount' => $amount,
                'method'                                             => 'cash', 'status' => 'completed', 'transaction_id' => null, 'paid_at' => now(),
                'payload'                                            => json_encode(['operator_id' => $operator->id, 'company_id' => $operator->bus_company_id, 'preview' => $preview, 'booking_ids' => $bookingIds], JSON_THROW_ON_ERROR),
                'created_at'                                         => now(), 'updated_at' => now()]);
            if ($intent !== null) {
                SePayPaymentIntentRecord::query()->toBase()->where('id', $intent->id)->update(['status' => 'expired', 'updated_at' => now()]);
            }

            return $this->show($operator, $token);
        });
    }
}
