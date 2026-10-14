<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return ['is_active' => ['required', 'boolean']];
    }
}
