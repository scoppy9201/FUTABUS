<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Services;

use App\Models\User;
use FuteBus\Payment\Models\TicketExchange;
use FuteBus\Payment\Services\StaffExchangeHolds;
use FuteBus\Payment\Services\TicketExchangeCatalog;
use FuteBus\Payment\Services\TicketExchangeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StaffTicketExchangeService
{
    public function __construct(
        private readonly TicketExchangeService $exchanges,
        private readonly StaffExchangeHolds $holds,
        private readonly TicketExchangeCatalog $catalog,
        private readonly ExchangePassengerDetails $passengers,
    ) {}

    public function hold(User $actor, int $booking, array $input): TicketExchange
    {
        return DB::transaction(function () use ($actor, $booking, $input): TicketExchange {
            $ticket = $this->catalog->owned($actor, $booking, (int) $input['ticket_id']);
            $passenger = $this->passengers->snapshot($actor, $ticket, $input);
            $transfer = $this->transferDetails($input);
            $hold = $this->holds->hold($actor, $booking, $input);
            // The underlying hold locks trips and the original ticket until this transaction commits.
            $ticket = $this->catalog->owned($actor, $booking, (int) $hold->old_ticket_id);
            $this->passengers->revalidate($actor, $ticket, ['passenger' => $passenger]);
            if (! $hold->wasRecentlyCreated && (($hold->snapshot['passenger'] ?? null) !== $passenger
                || ($hold->snapshot['staff_transfer'] ?? $this->transferDetails([])) !== $transfer)) {
                throw ValidationException::withMessages(['ticket' => __('Payment::exchange.already_held')]);
            }
            $hold->update(['snapshot' => [...$hold->snapshot, 'passenger' => $passenger, 'staff_transfer' => $transfer]]);

            return $hold;
        }, 3);
    }

    public function complete(User $actor, int $booking, string $token): TicketExchange
    {
        abort_unless($this->catalog->canOperate($actor), 403);

        return DB::transaction(function () use ($actor, $booking, $token): TicketExchange {
            $exchange = $this->exchanges->complete($actor, $booking, $token);
            $ticket = $this->catalog->owned($actor, $booking, (int) $exchange->old_ticket_id);
            abort_unless($this->catalog->managedTickets($actor)->where('tickets.id', $ticket->id)->exists(), 404);
            if (! isset($exchange->snapshot['passenger'])) {
                throw ValidationException::withMessages(['ticket' => __('Payment::exchange.changed')]);
            }
            if (! ($exchange->snapshot['staff_passenger_applied'] ?? false)) {
                $this->passengers->revalidate($actor, $ticket, $exchange->snapshot);
                DB::table('tickets')->where('id', $exchange->new_ticket_id)
                    ->update([...$exchange->snapshot['passenger']['after'], 'updated_at' => now()]);
                $snapshot = $exchange->snapshot;
                foreach ($snapshot['staff_transfer'] ?? [] as $kind => $details) {
                    if ($details['mode'] === 'transfer') {
                        $snapshot[$kind] = [...$snapshot[$kind], 'mode' => 'transfer',
                            'name' => __('core::booking.transfer'), 'address' => $details['address']];
                    }
                }
                DB::table('bookings')->where('id', $exchange->new_booking_id)->update([
                    'notes' => json_encode(['pickup' => $snapshot['pickup'], 'dropoff' => $snapshot['dropoff']], JSON_UNESCAPED_UNICODE),
                ]);
                $exchange->update(['snapshot' => [...$snapshot, 'staff_passenger_applied' => true]]);
            }

            // Payment queues email and notifications after the outer commit, with the final passenger details.
            return $exchange;
        }, 3);
    }

    public function release(User $actor, int $booking, string $token): void
    {
        abort_unless($this->catalog->canOperate($actor), 403);
        $exchange = TicketExchange::where('token', $token)->firstOrFail();
        abort_unless($this->catalog->managedTickets($actor)->where('tickets.id', $exchange->old_ticket_id)->exists(), 404);
        $this->exchanges->release($actor, $booking, $token);
    }

    private function transferDetails(array $input): array
    {
        $result = [];
        foreach (['pickup', 'dropoff'] as $kind) {
            $mode = $input[$kind.'_mode'] ?? 'station';
            $data = Validator::make(['mode' => $mode, $kind.'_address' => $input[$kind.'_address'] ?? null], [
                'mode'           => ['required', 'in:station,transfer'],
                $kind.'_address' => ['required_if:mode,transfer', 'nullable', 'string', 'max:500'],
            ])->validate();
            $result[$kind] = ['mode' => $mode, 'address' => $mode === 'transfer' ? trim($data[$kind.'_address']) : null];
        }

        return $result;
    }
}
