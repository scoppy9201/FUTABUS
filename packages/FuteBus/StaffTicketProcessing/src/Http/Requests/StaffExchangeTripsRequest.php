<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffExchangeTripsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today']];
    }
}
