<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Http\Requests;

use FuteBus\RolePermission\Services\AccessControlService;
use Illuminate\Foundation\Http\FormRequest;

class StaffRoleFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isBusCompany()) && $this->user()->hasPermissionTo('user.view') && app(AccessControlService::class)->companyFor($this->user()) !== null;
    }

    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:100']];
    }
}
