<?php

declare(strict_types=1);

namespace FuteBus\StaffDashboard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff()
            && $this->user()->is_active
            && $this->user()->email_verified_at !== null;
    }

    public function rules(): array
    {
        return [
            'month' => ['sometimes', 'date_format:Y-m', 'regex:/^(19|20)\d{2}-(0[1-9]|1[0-2])$/'],
        ];
    }
}
