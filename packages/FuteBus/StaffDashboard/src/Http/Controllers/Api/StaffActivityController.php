<?php

declare(strict_types=1);

namespace FuteBus\StaffDashboard\Http\Controllers\Api;

use FuteBus\StaffDashboard\Services\StaffActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StaffActivityController extends Controller
{
    public function __invoke(Request $request, StaffActivityService $service): JsonResponse
    {
        $validated = $request->validate(['month' => ['sometimes', 'date_format:Y-m', 'regex:/^(19|20)\d{2}-(0[1-9]|1[0-2])$/']]);

        return response()->json(['data' => $service->data($request->user(), $validated['month'] ?? now()->format('Y-m'))])
            ->header('Cache-Control', 'private, no-store');
    }
}
