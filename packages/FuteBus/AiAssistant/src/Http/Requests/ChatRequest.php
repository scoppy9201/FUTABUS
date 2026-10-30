<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Http\Requests;

use FuteBus\AiAssistant\Rules\CustomerPhone;
use FuteBus\AiAssistant\Support\PhoneNumbers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $country = $this->input('customer.phone_country');

        return [
            'message'                => ['required', 'string', 'max:500'],
            'customer'               => [$this->user()?->hasPermissionTo('report.view') ? 'sometimes' : 'required', 'array:salutation,name,email,phone,phone_country,topic,content'],
            'customer.salutation'    => ['nullable', 'in:mr,ms'],
            'customer.name'          => ['required_with:customer', 'string', 'max:100', 'regex:/\S/u'],
            'customer.email'         => ['nullable', 'email', 'max:254'],
            'customer.content'       => ['required_with:customer', 'string', 'max:500', 'regex:/\S/u'],
            'customer.phone_country' => ['required_with:customer.phone', 'nullable', Rule::in(PhoneNumbers::util()->getSupportedRegions())],
            'customer.phone'         => ['required_with:customer', 'string', 'max:30', new CustomerPhone(is_string($country) ? $country : '')],
            'customer.topic'         => ['required_with:customer', 'in:booking,feedback,locations,schedule,other'],
            'session_id'             => ['sometimes', 'nullable', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'                     => __('AiAssistant::app.required_field'),
            'required_with'                => __('AiAssistant::app.required_field'),
            'customer.phone.required_with' => __('AiAssistant::app.phone_incomplete'),
            'customer.email.email'         => __('AiAssistant::app.email_invalid'),
        ];
    }
}
