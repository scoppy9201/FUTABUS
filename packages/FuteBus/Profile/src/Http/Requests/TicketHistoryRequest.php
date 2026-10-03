<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketHistoryRequest extends FormRequest
{
    public const STATUS_OPTIONS = [
        'pending',
        'confirmed',
        'completed',
        'cancelled',
        'payment:unpaid',
        'payment:pending',
        'payment:completed',
        'payment:failed',
        'payment:refunded',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'code'   => ['nullable', 'string', 'max:100'],
            'date'   => ['nullable', 'date_format:Y-m-d'],
            'route'  => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(self::STATUS_OPTIONS)],
        ];
    }

    public function messages(): array
    {
        return [
            'code.max'         => __('Profile::tickets.validation.code'),
            'date.date_format' => __('Profile::tickets.validation.date'),
            'route.max'        => __('Profile::tickets.validation.route'),
            'status.in'        => __('Profile::tickets.validation.status'),
        ];
    }
}
