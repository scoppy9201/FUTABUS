<?php

declare(strict_types=1);

namespace FuteBus\StaffDashboard\Http\Controllers\Api;

use FuteBus\StaffDashboard\Http\Requests\StaffActivityRequest;
use FuteBus\StaffDashboard\Services\StaffActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class StaffActivityController extends Controller
{
    public function __invoke(StaffActivityRequest $request, StaffActivityService $service): JsonResponse
    {
        return response()->json(['data' => $service->data($request->user(), $request->validated('month', now()->format('Y-m')))])
            ->header('Cache-Control', 'private, no-store');
    }
}
