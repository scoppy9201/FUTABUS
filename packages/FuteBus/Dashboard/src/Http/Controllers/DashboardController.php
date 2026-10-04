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
        abort_unless($request->user()?->isAdmin(), 403);

        $company = $dashboard->company();

        return view('Dashboard::index', [
            'company'  => $company,
            'overview' => $dashboard->overview($company?->id ?? 0),
        ]);
    }

    public function section(Request $request, OwnerDashboardService $dashboard, string $section): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $company = $dashboard->company();

        return view('Dashboard::section', [
            'company' => $company,
            'section' => $section,
            'rows'    => $dashboard->listing($section, $company?->id ?? 0),
        ]);
    }
}
