<?php

declare(strict_types=1);

namespace FuteBus\Dashboard\Http\Controllers;

use FuteBus\Dashboard\Services\OwnerDashboardService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, OwnerDashboardService $dashboard): View
    {
        abort_unless($request->user()?->hasPermissionTo('dashboard.view'), 403);

        $company = $dashboard->company($request->user());

        return view('Dashboard::index', [
            'company'  => $company,
            'overview' => $dashboard->overview($company?->id ?? 0),
        ]);
    }

    public function section(Request $request, OwnerDashboardService $dashboard, string $section): View
    {
        $permission = match ($section) {
            'trips' => 'trip.view',
            'routes' => 'route.view',
            'buses' => 'bus.view',
            'bookings' => 'booking.view',
            'customers' => 'customer.view',
            'reports' => 'report.view',
        };
        abort_unless($request->user()?->hasPermissionTo($permission), 403);

        $company = $dashboard->company($request->user());

        return view('Dashboard::section', [
            'company' => $company,
            'section' => $section,
            'rows'    => $dashboard->listing($section, $company?->id ?? 0),
        ]);
    }
}
