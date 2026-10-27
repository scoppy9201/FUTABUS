<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Services;

use App\Models\User;
use FuteBus\Payment\Services\TicketExchangeCatalog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ExchangePassengerDetails
{
    public static function rules(): array
    {
        return [
            'passenger_name'  => ['required', 'string', 'max:255'],
            'passenger_phone' => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9}$/'],
        ];
    }

    public function snapshot(User $actor, object $ticket, array $input): array
    {
        $this->authorize($actor, $ticket);
        foreach (['passenger_name', 'passenger_phone'] as $field) {
            if (is_string($input[$field] ?? null)) {
                $input[$field] = trim($input[$field]);
            }
        }
        $after = Validator::make($input, self::rules())->validate();

        return ['before' => $this->original($ticket), 'after' => $after, 'actor_id' => $actor->id];
    }

    public function revalidate(User $actor, object $ticket, array $snapshot): void
    {
        if (! isset($snapshot['passenger'])) {
            return;
        }

        $this->authorize($actor, $ticket);
        $passenger = $snapshot['passenger'];
        if (($passenger['before'] ?? null) !== $this->original($ticket) || ($passenger['actor_id'] ?? null) !== $actor->id) {
            throw ValidationException::withMessages(['ticket' => __('Payment::exchange.changed')]);
        }
        Validator::make($passenger['after'] ?? [], self::rules())->validate();
    }

    private function authorize(User $actor, object $ticket): void
    {
        $catalog = app(TicketExchangeCatalog::class);
        abort_unless($catalog->canOperate($actor), 403);
        abort_unless($catalog->managedTickets($actor)->where('tickets.id', $ticket->id)->exists(), 404);
    }

    private function original(object $ticket): array
    {
        return ['passenger_name' => $ticket->passenger_name, 'passenger_phone' => $ticket->passenger_phone];
    }
}
