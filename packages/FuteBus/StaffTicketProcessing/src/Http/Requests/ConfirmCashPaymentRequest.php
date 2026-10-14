<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmCashPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['received' => ['required', 'accepted']];
    }
}
