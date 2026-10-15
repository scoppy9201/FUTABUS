<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use App\Models\User;
use FuteBus\BookingManagement\Services\BookingTripStops;
use FuteBus\Payment\Models\TicketExchange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketExchangeService
{
    public function __construct(
        private readonly TicketExchangeCatalog $catalog,
        private readonly TicketExchangePolicy $policy,
        private readonly BookingPaymentResolver $payments,
        private readonly TicketExchangeIssuer $issuer,
    ) {}

    public function hold(User $actor, int $booking, array $input): TicketExchange
    {
        $candidate = $this->catalog->owned($actor, $booking, (int) $input['ticket_id']);

        return DB::transaction(function () use ($actor, $candidate, $input): TicketExchange {
            $this->lockTrips([(int) $candidate->trip_id, (int) $input['trip_id']]);
            DB::table('bookings')->where('id', $candidate->booking_id)->lockForUpdate()->first();
            DB::table('tickets')->where('id', $candidate->id)->lockForUpdate()->first();
            $ticket = $this->catalog->owned($actor, (int) $candidate->booking_id, (int) $candidate->id);
            $this->policy->eligible($ticket, $this->catalog->hasExchanged((int) $ticket->id));
            $payment = $this->payments->verified((int) $ticket->booking_id, true);
            $trip = $this->catalog->tripQuery()->where('trips.id', $input['trip_id'])->first();
            $this->validateTarget($ticket, $trip);
            // A live hold belongs to one actor; a second tab cannot steal it.
            $existing = TicketExchange::where('old_ticket_id', $ticket->id)->where('status', 'held')->where('expires_at', '>', now())->first();
            if ($existing) {
                if ((int) $existing->actor_id === $actor->id && (int) $existing->target_trip_id === (int) $trip->id
                    && (int) $existing->seat_layout_id === (int) $input['seat_id']
                    && (string) $existing->snapshot['pickup']['id'] === (string) $input['pickup']
                    && (string) $existing->snapshot['dropoff']['id'] === (string) $input['dropoff']) {
                    return $existing;
                }
                $this->invalid('ticket', 'already_held');
            }
            $seat = $this->availableSeat($trip, (int) $input['seat_id']);
            $stops = $this->stops($trip, $input);
            $owner = $ticket->user_id ?: DB::table('customers')->where('id', $ticket->customer_id)->value('user_id');
            abort_if(! $owner, 422, __('Payment::exchange.owner_required'));

            return TicketExchange::create([
                'token'                 => (string) Str::uuid(), 'user_id' => $owner, 'actor_id' => $actor->id, 'old_ticket_id' => $ticket->id,
                'payment_id'            => $payment->id, 'target_trip_id' => $trip->id, 'seat_layout_id' => $seat->id, 'status' => 'held',
                'fare'                  => (int) $ticket->price, 'fee' => 0, 'policy_version' => TicketExchangePolicy::VERSION,
                'original_departure_at' => $ticket->departure_time, 'expires_at' => now()->addMinutes(5),
                'snapshot'              => ['old' => (array) $ticket, 'new' => (array) $trip, 'seat' => $seat->seat_code,
                    'reason'                      => $input['reason'] ?? null, ...$stops],
            ]);
        }, 3);
    }

    public function complete(User $actor, int $booking, string $token): TicketExchange
    {
        $candidate = TicketExchange::where('token', $token)->firstOrFail();
        $this->catalog->owned($actor, $booking, (int) $candidate->old_ticket_id);
        abort_unless((int) $candidate->actor_id === $actor->id, 403);

        return DB::transaction(function () use ($actor, $booking, $candidate): TicketExchange {
            $this->lockTrips([(int) $candidate->snapshot['old']['trip_id'], (int) $candidate->target_trip_id]);
            DB::table('bookings')->where('id', $booking)->lockForUpdate()->first();
            DB::table('tickets')->where('id', $candidate->old_ticket_id)->lockForUpdate()->first();
            $exchange = TicketExchange::whereKey($candidate->id)->lockForUpdate()->firstOrFail();
            if ($exchange->status === 'completed') {
                return $exchange;
            }
            if ($exchange->status !== 'held' || $exchange->expires_at->lte(now())) {
                $this->invalid('ticket', 'expired');
            }
            $ticket = $this->catalog->owned($actor, $booking, (int) $exchange->old_ticket_id);
            $this->policy->eligible($ticket, $this->catalog->hasExchanged((int) $ticket->id));
            $payment = $this->payments->verified($booking, true);
            if ((int) $payment->id !== (int) $exchange->payment_id || (int) $ticket->price !== $exchange->fare) {
                $this->invalid('ticket', 'changed');
            }
            $trip = $this->catalog->tripQuery()->where('trips.id', $exchange->target_trip_id)->first();
            $this->validateTarget($ticket, $trip);
            $seat = $this->availableSeat($trip, (int) $exchange->seat_layout_id, $exchange->token);
            $stops = $this->stops($trip, ['pickup' => $exchange->snapshot['pickup']['id'], 'dropoff' => $exchange->snapshot['dropoff']['id']]);

            return $this->issuer->issue($exchange, $ticket, $trip, $seat, $stops);
        }, 3);
    }

    public function release(User $actor, int $booking, string $token): void
    {
        $exchange = TicketExchange::where('token', $token)->firstOrFail();
        $this->catalog->owned($actor, $booking, (int) $exchange->old_ticket_id);
        abort_unless((int) $exchange->actor_id === $actor->id, 403);
        TicketExchange::whereKey($exchange->id)->where('status', 'held')->update(['status' => 'released']);
    }

    private function lockTrips(array $ids): void
    {
        sort($ids);
        foreach (array_unique($ids) as $id) {
            DB::table('trips')->where('id', $id)->lockForUpdate()->first();
        }
    }

    private function validateTarget(object $ticket, ?object $trip): void
    {
        if (! $trip || ! $this->policy->equivalent($ticket, $trip)) {
            $this->invalid('trip_id', 'not_equivalent');
        }
        $intent = DB::table('sepay_payment_intents')->where('status', 'paid')
            ->where(fn ($q) => $q->where('booking_id', $ticket->booking_id)->orWhere('secondary_booking_id', $ticket->booking_id))->first();
        $other = $intent?->secondary_booking_id
            ? ((int) $intent->booking_id === (int) $ticket->booking_id ? $intent->secondary_booking_id : $intent->booking_id) : null;
        if (! $other) {
            $payment = $this->payments->verified((int) $ticket->booking_id);
            $payload = json_decode($payment->payload ?? '', true) ?: [];
            $other = $payment->method === 'cash' ? collect($payload['booking_ids'] ?? [])->first(fn ($id) => (int) $id !== (int) $ticket->booking_id) : null;
        }
        if (! $other) {
            return;
        }
        $trips = DB::table('tickets')->join('trips', 'trips.id', '=', 'tickets.trip_id')
            ->where('booking_id', $other)->whereIn('tickets.status', ['active', 'used'])->get(['trips.departure_time', 'trips.arrival_time']);
        $changed = DB::table('ticket_exchanges')->join('tickets as old', 'old.id', '=', 'ticket_exchanges.old_ticket_id')
            ->join('tickets as replacement', 'replacement.id', '=', 'ticket_exchanges.new_ticket_id')
            ->join('trips', 'trips.id', '=', 'ticket_exchanges.target_trip_id')->where('old.booking_id', $other)
            ->where('ticket_exchanges.status', 'completed')->whereIn('replacement.status', ['active', 'used'])
            ->get(['trips.departure_time', 'trips.arrival_time']);
        foreach ($trips->concat($changed) as $leg) {
            $valid = $ticket->departure_time < $leg->departure_time ? $trip->arrival_time < $leg->departure_time : $trip->departure_time > $leg->arrival_time;
            if (! $valid) {
                $this->invalid('trip_id', 'round_trip_conflict');
            }
        }
    }

    private function availableSeat(object $trip, int $id, ?string $ignore = null): object
    {
        app(SeatReservationService::class)->releaseCancelled((int) $trip->id, [$id]);
        $details = $this->catalog->describe($trip, $ignore);
        $option = collect($details['seats'])->firstWhere('id', $id);
        if (! $option || $option['sold'] || $details['available'] < 1) {
            $this->invalid('seat_id', 'seat_taken');
        }

        return DB::table('seat_layouts')->where('id', $id)->first();
    }

    private function stops(object $trip, array $input): array
    {
        if (DB::table('route_stops')->where('route_id', $trip->route_id)->exists()) {
            foreach (['pickup', 'dropoff'] as $kind) {
                if (! ctype_digit((string) $input[$kind])) {
                    throw ValidationException::withMessages([$kind => __('core::booking.stop_unavailable')]);
                }
            }
        }
        $service = app(BookingTripStops::class);
        $pickup = $service->selected((array) $trip, 'pickup', (string) $input['pickup'], 'pickup');
        $dropoff = $service->selected((array) $trip, 'dropoff', (string) $input['dropoff'], 'dropoff');
        if ($pickup['expires_at'] >= $dropoff['expires_at']) {
            $this->invalid('dropoff', 'stop_order');
        }

        return compact('pickup', 'dropoff');
    }

    private function invalid(string $field, string $key): never
    {
        throw ValidationException::withMessages([$field => __('Payment::exchange.'.$key)]);
    }
}
