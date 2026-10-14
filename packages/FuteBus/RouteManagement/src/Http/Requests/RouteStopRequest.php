<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Http\Requests;

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
        return [
            'name'             => ['required', 'string', 'max:150'],
            'address'          => ['required', 'string', 'max:255'],
            'leg_minutes'      => ['required', 'integer', 'between:0,1440'],
            'position'         => ['required', 'integer', 'between:1,1000'],
            'stop_type'        => ['required', Rule::in(['pickup', 'dropoff', 'both'])],
            'next_leg_minutes' => ['nullable', 'integer', 'between:1,1440'],
        ];
    }

    public function attributes(): array
    {
        return __('RouteManagement::app.fields');
    }
}
