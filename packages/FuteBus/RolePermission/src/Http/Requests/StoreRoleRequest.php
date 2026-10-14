<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Http\Requests;

use FuteBus\RolePermission\Services\AccessControlService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isBusCompany()) && $this->user()->hasPermissionTo('role.create') && app(AccessControlService::class)->companyFor($this->user()) !== null;
    }

    public function rules(): array
    {
        $company = app(AccessControlService::class)->companyFor($this->user());

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')->where(fn ($query) => $query->where('bus_company_id', $company->id)),
            ],
            'description'            => ['nullable', 'string', 'max:1000'],
            'permission_ids_present' => ['required', 'accepted'],
            'permission_ids'         => ['sometimes', 'array'],
            'permission_ids.*'       => ['integer', 'distinct', Rule::exists('permissions', 'id')->where('is_active', true)],
        ];
    }
}
