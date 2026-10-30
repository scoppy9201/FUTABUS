<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Http\Requests;

use FuteBus\RolePermission\Rules\UniquePermissionDisplayName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdatePermissionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('display_name'))) {
            $this->merge(['display_name' => Str::squish($this->input('display_name'))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'display_name'  => ['bail', 'required', 'string', 'max:120', new UniquePermissionDisplayName((int) $this->route('permission'))],
            'display_group' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.required' => __('RolePermission::app.permission_name_required'),
            'display_name.string'   => __('RolePermission::app.permission_name_invalid'),
            'display_name.max'      => __('RolePermission::app.permission_name_max'),
        ];
    }
}
