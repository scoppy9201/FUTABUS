<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

use FuteBus\TripSearch\Http\Requests\TripSearchRequest;
use Illuminate\Validation\Rule;

class PaymentPreviewRequest extends TripSearchRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'direction'                => ['nullable', Rule::in(['outbound', 'return'])],
            'name'                     => ['required', 'string', 'max:120'],
            'phone'                    => ['required', 'regex:/^(?:0[35789]\\d{8}|\\+84[35789]\\d{8})$/'],
            'email'                    => ['required', 'email', 'max:255'],
            'accept_terms'             => ['accepted'],
            'seats'                    => ['required', 'array', 'min:1', 'max:5'],
            'seats.*'                  => ['required', 'integer', 'distinct'],
            'outbound_trip'            => ['required_if:trip_type,round_trip', 'nullable', 'integer', 'min:1'],
            'outbound_seats'           => ['required_if:trip_type,round_trip', 'nullable', 'array', 'min:1', 'max:5'],
            'outbound_seats.*'         => ['required', 'integer', 'distinct'],
            'pickup_mode'              => ['required', Rule::in(['station', 'transfer'])],
            'dropoff_mode'             => ['required', Rule::in(['station', 'transfer'])],
            'pickup_address'           => ['required_if:pickup_mode,transfer', 'nullable', 'string', 'max:255'],
            'dropoff_address'          => ['required_if:dropoff_mode,transfer', 'nullable', 'string', 'max:255'],
            'pickup_station'           => ['nullable', 'string', 'max:30'],
            'dropoff_station'          => ['nullable', 'string', 'max:30'],
            'outbound_pickup_mode'     => ['required_if:trip_type,round_trip', 'nullable', Rule::in(['station', 'transfer'])],
            'outbound_dropoff_mode'    => ['required_if:trip_type,round_trip', 'nullable', Rule::in(['station', 'transfer'])],
            'outbound_pickup_address'  => ['required_if:outbound_pickup_mode,transfer', 'nullable', 'string', 'max:255'],
            'outbound_dropoff_address' => ['required_if:outbound_dropoff_mode,transfer', 'nullable', 'string', 'max:255'],
            'outbound_pickup_station'  => ['nullable', 'string', 'max:30'],
            'outbound_dropoff_station' => ['nullable', 'string', 'max:30'],
        ];
    }
}
