<?php

declare(strict_types=1);

namespace Staff\Dashboard\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Staff\Dashboard\Services\StaffDashboardService;

class StaffDashboardController extends Controller
{
    public function show(Request $request, StaffDashboardService $dashboard): JsonResponse
    {
        return response()->json(['data' => $dashboard->data($request->user())])->header('Cache-Control', 'private, no-store');
    }
}
