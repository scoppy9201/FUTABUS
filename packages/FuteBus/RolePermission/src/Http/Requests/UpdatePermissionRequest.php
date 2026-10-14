<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'display_name'  => ['required', 'string', 'max:120'],
            'display_group' => ['nullable', 'string', 'max:80'],
        ];
    }
}
