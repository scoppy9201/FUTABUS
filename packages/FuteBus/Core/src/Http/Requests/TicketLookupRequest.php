<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketLookupRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone', '');
        $code = $this->input('ticket_code', '');

        if (! is_string($phone) || ! is_string($code)) {
            return;
        }

        $phone = trim($phone);

        if (preg_match('/^[+0-9\s().-]+$/', $phone)) {
            $digits = preg_replace('/\D+/', '', $phone);
            $phone = str_starts_with($digits, '84') && strlen($digits) === 11
                ? '0'.substr($digits, 2)
                : $digits;
        }

        $this->merge([
            'phone'       => $phone,
            'ticket_code' => strtoupper(trim($code)),
        ]);
    }

    public function rules(): array
    {
        return [
            'phone'       => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/'],
            'ticket_code' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required'       => __('core::ticket-lookup.validation.phone'),
            'phone.string'         => __('core::ticket-lookup.validation.phone'),
            'phone.regex'          => __('core::ticket-lookup.validation.phone'),
            'ticket_code.required' => __('core::ticket-lookup.validation.code'),
            'ticket_code.string'   => __('core::ticket-lookup.validation.code'),
            'ticket_code.max'      => __('core::ticket-lookup.validation.code_length'),
        ];
    }
}
