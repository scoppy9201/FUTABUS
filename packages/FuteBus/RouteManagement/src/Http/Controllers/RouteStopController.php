<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Http\Controllers;

use FuteBus\RouteManagement\Http\Requests\RouteActionRequest;
use FuteBus\RouteManagement\Http\Requests\RouteFilterRequest;
use FuteBus\RouteManagement\Http\Requests\RouteStopRequest;
use FuteBus\RouteManagement\Models\RouteStop as RouteStopRecord;
use FuteBus\RouteManagement\Services\RouteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class RouteStopController extends Controller
{
    public function __construct(private readonly RouteService $routes) {}

    public function index(RouteFilterRequest $request): View
    {
        $filters = $request->validated();
        $routeId = $filters['route_id'] ?? $this->routes->query()->where('is_active', true)->orderBy('code')->value('id');
        $route = $routeId ? $this->routes->find((int) $routeId) : null;

        return view('RouteManagement::stops', [
            'route' => $route, 'routes' => $this->routes->query()
                ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $route?->id ?? 0))->orderBy('code')->get(),
            'stops' => RouteStopRecord::query()->toBase()->where('route_id', $route?->id ?? 0)->where('status', 'active')->orderBy('stop_order')->get(),
        ]);
    }

    public function store(RouteStopRequest $request, int $route): RedirectResponse
    {
        $this->routes->saveStop($route, $request->validated());

        return $this->target($route);
    }

    public function update(RouteStopRequest $request, int $route, int $stop): RedirectResponse
    {
        $this->routes->saveStop($route, $request->validated(), $stop);

        return $this->target($route);
    }

    public function destroy(RouteActionRequest $request, int $route, int $stop): RedirectResponse
    {
        $this->routes->deleteStop($route, $stop);

        return $this->target($route);
    }

    private function target(int $route): RedirectResponse
    {
        return redirect()->route('route-management.stops.index', ['route_id' => $route])->with('success', __('RouteManagement::app.saved'));
    }
}
