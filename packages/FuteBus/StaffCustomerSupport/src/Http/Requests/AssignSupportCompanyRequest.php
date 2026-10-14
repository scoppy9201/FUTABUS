<?php

declare(strict_types=1);

namespace FuteBus\StaffCustomerSupport\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignSupportCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return ['bus_company_id' => ['required', 'integer', 'min:1'], 'version' => ['required', 'integer', 'min:0']];
    }
}
