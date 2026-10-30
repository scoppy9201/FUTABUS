<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Services;

use FuteBus\RolePermission\Models\Permission;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use ReflectionClass;

class PermissionCatalogService
{
    private ?Collection $definitions = null;

    public function __construct(private Application $app, private Router $router) {}

    public function definitions(): Collection
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        $definitions = collect();
        $modules = [];
        foreach ($this->app->getLoadedProviders() as $provider => $loaded) {
            if (! $loaded || ! str_starts_with($provider, 'FuteBus\\')) {
                continue;
            }
            $module = explode('\\', $provider)[1];
            $modules[$module] = true;
            $path = dirname((new ReflectionClass($provider))->getFileName(), 2).'/config/permissions.php';
            if (is_file($path)) {
                foreach (require $path as $group => $actions) {
                    foreach ($actions as $action) {
                        $this->add($definitions, $group.'.'.$action, $module);
                    }
                }
            }
        }

        foreach ($this->router->getRoutes() as $route) {
            $controller = $route->getAction('controller');
            if (! is_string($controller) || ! str_starts_with($controller, 'FuteBus\\')) {
                continue;
            }
            $module = explode('\\', $controller)[1];
            if (! isset($modules[$module])) {
                continue;
            }
            foreach ($route->gatherMiddleware() as $middleware) {
                if (is_string($middleware) && str_starts_with($middleware, 'permission:')) {
                    foreach (explode(',', substr($middleware, strlen('permission:'))) as $slug) {
                        $this->add($definitions, $slug, $module);
                    }
                }
            }
        }

        return $this->definitions = $definitions->sortKeys();
    }

    public function slugs(): array
    {
        $prefixes = collect($this->catalogGroups())->flatten()->all();

        return $this->definitions()->filter(fn (array $definition): bool => in_array($definition['group'], $prefixes, true))->keys()->all();
    }

    public function rows(): Collection
    {
        $stored = Permission::query()->toBase()->whereIn('slug', $this->slugs())->get()->keyBy('slug');

        $groups = $this->catalogGroups();

        return $this->definitions()->only($this->slugs())->map(function (array $definition, string $slug) use ($stored, $groups): object {
            $permission = $stored->get($slug);
            $group = collect($groups)->search(fn (array $prefixes): bool => in_array($definition['group'], $prefixes, true));

            return (object) [
                'id'            => $permission?->id,
                'slug'          => $slug,
                'group'         => $group,
                'name'          => $permission?->name ?? $slug,
                'display_name'  => $permission?->display_name,
                'display_group' => $permission?->display_group,
                'label'         => $this->label('permissions.'.$slug, $permission?->display_name !== $permission?->name ? $permission?->display_name : null, $permission?->name ?? $slug),
                'group_label'   => $this->label('groups.'.$group, null, $group),
                'admin_only'    => in_array($group, ['bus', 'trip', 'trip_schedule'], true),
                'is_active'     => $permission !== null && (bool) $permission->is_active,
                'modules'       => $definition['modules'],
            ];
        })->sortBy(fn (object $permission): int => array_search($permission->group, array_keys($groups), true))->values();
    }

    public function synchronize(): int
    {
        return DB::transaction(function (): int {
            $created = 0;
            foreach ($this->definitions()->only($this->slugs()) as $slug => $definition) {
                $created += Permission::query()->toBase()->insertOrIgnore([
                    'slug'       => $slug,
                    'name'       => $slug,
                    'group'      => $definition['group'],
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $created;
        });
    }

    private function add(Collection $definitions, string $slug, string $module): void
    {
        if (preg_match('/^[a-z][a-z_]*\.[a-z][a-z_]*$/D', $slug) !== 1) {
            throw new \LogicException('Invalid module permission: '.$module);
        }
        $definition = $definitions->get($slug, ['group' => explode('.', $slug)[0], 'modules' => []]);
        $definition['modules'] = array_values(array_unique([...$definition['modules'], $module]));
        sort($definition['modules']);
        $definitions->put($slug, $definition);
    }

    private function catalogGroups(): array
    {
        return require __DIR__.'/../config/catalog-groups.php';
    }

    private function label(string $key, ?string $custom, string $fallback): string
    {
        [$section, $item] = explode('.', $key, 2);
        $key = 'RolePermission::app.'.$section;
        $defaults = [Lang::get($key, [], 'vi')[$item] ?? null, Lang::get($key, [], 'en')[$item] ?? null];
        // Recognize former built-in labels without overriding administrator customizations.
        $legacyLabels = [
            'permissions.support.respond' => ['Phản hồi yêu cầu hỗ trợ', 'Respond to support requests'],
            'permissions.ticket.exchange' => ['Đổi vé', 'Exchange tickets'],
            'permissions.ticket.cancel'   => ['Hủy vé / Cancel tickets'],
        ];
        $defaults = [...$defaults, ...($legacyLabels[$section.'.'.$item] ?? [])];
        if (filled($custom) && ! in_array($custom, $defaults, true)) {
            return $custom;
        }

        return Lang::get($key)[$item] ?? $fallback;
    }
}
