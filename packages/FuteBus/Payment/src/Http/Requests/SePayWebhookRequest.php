<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

use FuteBus\Payment\Services\SePayPaymentService;
use FuteBus\Payment\Services\SePayWebhookAuthenticator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SePayWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! app(SePayPaymentService::class)->ready()) {
            throw new HttpResponseException(response()->json(['success' => false], 503));
        }
        if (! app(SePayWebhookAuthenticator::class)->verify($this)) {
            throw new HttpResponseException(response()->json(['success' => false], 401));
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'id'             => ['required', 'integer', 'min:1'],
            'transferType'   => ['required', 'in:in,out'],
            'transferAmount' => ['required', 'integer', 'min:0'],
            'accountNumber'  => ['required', 'string', 'max:32'],
            'code'           => ['nullable', 'string', 'max:64'],
        ];
    }
}
