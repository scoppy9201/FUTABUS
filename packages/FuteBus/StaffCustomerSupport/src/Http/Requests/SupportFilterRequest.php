<?php

declare(strict_types=1);

namespace FuteBus\StaffCustomerSupport\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'search'   => ['nullable', 'string', 'max:150'], 'status' => ['nullable', Rule::in(['received', 'reviewing', 'waiting', 'resolved'])],
            'category' => ['nullable', Rule::in(['staff', 'trip_service', 'schedule', 'booking_payment', 'lost_property', 'pickup', 'other'])],
            'queue'    => ['nullable', Rule::in(['all', 'unassigned', 'mine', 'waiting', 'resolved'])],
            'urgency'  => ['nullable', Rule::in(['suggestion', 'complaint', 'urgent'])], 'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
