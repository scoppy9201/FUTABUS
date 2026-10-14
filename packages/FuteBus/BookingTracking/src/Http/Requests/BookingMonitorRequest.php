<?php

declare(strict_types=1);

namespace FuteBus\BookingTracking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingMonitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active && $this->user()->hasPermissionTo('booking.view');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'completed', 'cancelled', 'expired', 'paid', 'needs_review'])],
            'from'   => ['nullable', 'date_format:Y-m-d'],
            'to'     => array_filter(['nullable', 'date_format:Y-m-d', $this->filled('from') ? 'after_or_equal:from' : null]),
            'page'   => ['nullable', 'integer', 'min:1'],
        ];
    }
}
