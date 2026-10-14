<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Requests;

use FuteBus\BusManagement\Services\BusPresentationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusPresentationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['amenities' => $this->input('amenities', [])]);
    }

    public function rules(): array
    {
        return [
            'amenities'     => ['array', 'max:5'],
            'amenities.*'   => ['string', 'distinct', Rule::in(BusPresentationService::AMENITIES)],
            'wifi_name'     => ['nullable', 'string', 'max:100'],
            'wifi_password' => ['nullable', 'string', 'max:100'],
        ];
    }
}
