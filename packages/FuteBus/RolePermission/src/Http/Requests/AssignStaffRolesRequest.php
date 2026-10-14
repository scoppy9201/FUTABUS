<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Http\Requests;

use FuteBus\RolePermission\Services\AccessControlService;
use Illuminate\Foundation\Http\FormRequest;

class AssignStaffRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $allowed = $this->user() && ($this->user()->isAdmin() || $this->user()->isBusCompany()) && $this->user()->hasPermissionTo('user.update') && app(AccessControlService::class)->companyFor($this->user()) !== null;
        if ($allowed) {
            $access = app(AccessControlService::class);
            $company = $access->companyFor($this->user());
            $access->staffForCompany($company->id, (int) $this->route('staff'));
        }

        return $allowed;
    }

    public function rules(): array
    {
        return [
            'role_ids'   => ['sometimes', 'array'],
            'role_ids.*' => ['integer', 'distinct'],
        ];
    }
}
