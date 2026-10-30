<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Http\Requests;

use FuteBus\RolePermission\Rules\UniquePermissionDisplayName;
use FuteBus\RolePermission\Services\PermissionCatalogService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
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
        $allowedSlugs = app(PermissionCatalogService::class)->slugs();

        return [
            'slug'          => ['required', 'string', Rule::in($allowedSlugs)],
            'display_name'  => ['bail', 'required', 'string', 'max:120', new UniquePermissionDisplayName(exceptSlug: is_string($this->input('slug')) ? $this->input('slug') : null)],
            'display_group' => ['nullable', 'string', 'max:80'],
        ];
    }
}
