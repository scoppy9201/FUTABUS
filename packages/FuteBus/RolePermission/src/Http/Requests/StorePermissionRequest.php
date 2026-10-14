<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $allowedSlugs = collect(config('permission_catalog', []))
            ->flatMap(fn (array $actions, string $group): array => collect($actions)->map(fn (string $action): string => $group.'.'.$action)->all())
            ->all();

        return [
            'slug'          => ['required', 'string', Rule::in($allowedSlugs)],
            'display_name'  => ['required', 'string', 'max:120'],
            'display_group' => ['nullable', 'string', 'max:80'],
        ];
    }
}
