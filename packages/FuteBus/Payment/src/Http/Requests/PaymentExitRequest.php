<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentExitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['confirm_exit' => ['required', 'accepted']];
    }
}
