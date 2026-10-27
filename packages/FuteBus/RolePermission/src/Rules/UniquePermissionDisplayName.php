<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Rules;

use Closure;
use FuteBus\RolePermission\Services\PermissionCatalogService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class UniquePermissionDisplayName implements ValidationRule
{
    public function __construct(private readonly ?int $exceptId = null, private readonly ?string $exceptSlug = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }
        $candidate = mb_strtolower(Str::squish($value));
        $defaults = [Lang::get('RolePermission::app.permissions', [], 'vi'), Lang::get('RolePermission::app.permissions', [], 'en')];
        foreach (app(PermissionCatalogService::class)->rows() as $permission) {
            if (($this->exceptId !== null && (int) $permission->id === $this->exceptId)
                || ($this->exceptSlug !== null && $permission->slug === $this->exceptSlug)) {
                continue;
            }
            $names = [$permission->label, $permission->display_name];
            foreach ($defaults as $labels) {
                $names[] = is_array($labels) ? ($labels[$permission->slug] ?? null) : null;
            }
            foreach ($names as $name) {
                if (is_string($name) && mb_strtolower(Str::squish($name)) === $candidate) {
                    $fail(__('RolePermission::app.permission_name_taken'));

                    return;
                }
            }
        }
    }
}
