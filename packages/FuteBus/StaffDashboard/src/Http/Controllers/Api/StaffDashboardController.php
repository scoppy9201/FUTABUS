<?php

declare(strict_types=1);

namespace FuteBus\StaffDashboard\Http\Controllers\Api;

use FuteBus\StaffDashboard\Services\StaffDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StaffDashboardController extends Controller
{
    public function show(Request $request, StaffDashboardService $dashboard): JsonResponse
    {
        return response()->json(['data' => $dashboard->data($request->user())])->header('Cache-Control', 'private, no-store');
    }
}
