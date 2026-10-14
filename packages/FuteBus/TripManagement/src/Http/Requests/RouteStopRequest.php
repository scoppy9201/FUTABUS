<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RouteStopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return ($this->isMethod('post') ? ['route_id' => ['required', 'exists:routes,id']] : []) + [
            'name'        => ['required', 'string', 'max:150'],
            'address'     => ['nullable', 'string', 'max:255'],
            'leg_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'position'    => ['nullable', 'integer', 'min:1'],
            'stop_type'   => ['required', Rule::in(['pickup', 'dropoff', 'both'])],
            'schedule_id' => ['nullable', 'integer'],
        ];
    }
}
