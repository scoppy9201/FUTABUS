<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code'             => strtoupper(trim((string) $this->input('code'))),
            'origin_city'      => trim((string) $this->input('origin_city')),
            'destination_city' => trim((string) $this->input('destination_city')),
        ]);
    }

    public function rules(): array
    {
        return [
            'code'             => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/', Rule::unique('routes', 'code')->ignore($this->route('route'))],
            'origin_city'      => ['required', 'string', 'max:100'],
            'destination_city' => ['required', 'string', 'max:100', 'different:origin_city'],
            'distance_km'      => ['required', 'integer', 'between:1,10000'],
            'base_price'       => ['required', 'integer', 'between:1001,99999999'],
            'vehicle_type'     => ['required', Rule::in(['Limousine', 'Giường nằm', 'Ghế ngồi'])],
            'is_active'        => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return __('RouteManagement::app.fields');
    }
}
