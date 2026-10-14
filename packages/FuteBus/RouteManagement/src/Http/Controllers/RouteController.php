<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Http\Controllers;

use FuteBus\RouteManagement\Http\Requests\RouteActionRequest;
use FuteBus\RouteManagement\Http\Requests\RouteFilterRequest;
use FuteBus\RouteManagement\Http\Requests\RouteRequest;
use FuteBus\RouteManagement\Services\RouteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class RouteController extends Controller
{
    public function __construct(private readonly RouteService $routes) {}

    public function index(RouteFilterRequest $request): View
    {
        $filters = $request->validated();
        $status = $filters['status'] ?? 'active';
        $query = $this->routes->query()
            ->select('routes.*')
            ->selectRaw("(select count(*) from route_stops where route_stops.route_id = routes.id and status = 'active') as stop_count")
            ->selectRaw('(select count(*) from trips where trips.route_id = routes.id) as trip_count')
            ->selectRaw('(select count(*) from trip_schedules where trip_schedules.route_id = routes.id) as schedule_total')
            ->selectRaw("(select count(*) from trip_schedules where trip_schedules.route_id = routes.id and status = 'active') as schedule_count");
        if ($status !== 'all') {
            $query->where('is_active', $status === 'active');
        }
        if ($search = $filters['search'] ?? '') {
            $query->where(fn ($q) => $q->where('code', 'like', "%{$search}%")
                ->orWhere('origin_city', 'like', "%{$search}%")->orWhere('destination_city', 'like', "%{$search}%"));
        }

        return view('RouteManagement::index', [
            'routes'  => $query->orderBy('code')->paginate(10)->withQueryString(),
            'filters' => $filters + ['status' => $status],
        ]);
    }

    public function store(RouteRequest $request): RedirectResponse
    {
        $id = $this->routes->save($request->validated());

        return redirect()->route('route-management.stops.index', ['route_id' => $id])->with('success', __('RouteManagement::app.created'));
    }

    public function update(RouteRequest $request, int $route): RedirectResponse
    {
        $this->routes->save($request->validated(), $route);

        return redirect()->route('route-management.index')->with('success', __('RouteManagement::app.saved'));
    }

    public function destroy(RouteActionRequest $request, int $route): RedirectResponse
    {
        $this->routes->delete($route);

        return redirect()->route('route-management.index')->with('success', __('RouteManagement::app.deleted'));
    }
}
