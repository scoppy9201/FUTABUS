<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TripSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'departure' => ['required', 'string', 'max:120'],
            'destination' => ['required', 'string', 'max:120', 'different:departure'],
            'departure_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'return_date' => [Rule::requiredIf($this->input('trip_type') === 'round_trip'), 'nullable', 'date_format:Y-m-d', 'after_or_equal:departure_date'],
            'trip_type' => ['required', Rule::in(['one_way', 'round_trip'])],
            'quantity' => ['required', 'integer', 'between:1,5'],
        ];
    }
}
