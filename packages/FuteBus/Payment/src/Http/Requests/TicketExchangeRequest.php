<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class TicketExchangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active === true;
    }

    public function attributes(): array
    {
        return collect(['ticket_id' => 'choose', 'trip_id' => 'new_trip', 'seat_id' => 'seat', 'date' => 'date',
            'pickup'                => 'pickup', 'dropoff' => 'dropoff', 'reason' => 'reason', 'consent' => 'consent', 'code' => 'search'])
            ->map(fn (string $key): string => __('Payment::exchange.'.$key))->all();
    }
}
