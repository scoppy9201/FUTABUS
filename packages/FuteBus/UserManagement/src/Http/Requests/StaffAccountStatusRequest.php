<?php

declare(strict_types=1);

namespace FuteBus\UserManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffAccountStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isBusCompany());
    }

    public function rules(): array
    {
        return ['is_active' => ['required', 'boolean']];
    }
}
