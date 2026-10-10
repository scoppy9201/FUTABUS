<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers\Api;

use FuteBus\BusManagement\Services\BusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BusController extends Controller
{
    public function index(Request $request, BusService $buses): JsonResponse
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100']]);

        return response()->json($buses->paginate($filters['search'] ?? ''));
    }

    public function show(BusService $buses, int $bus): JsonResponse
    {
        return response()->json(['data' => $buses->find($bus)]);
    }

    public function store(Request $request, BusService $buses): JsonResponse
    {
        $id = $buses->create($request->validate($buses->rules()));

        return response()->json(['data' => $buses->find($id)], 201)
            ->header('Location', route('api.v1.admin.buses.show', ['bus' => $id]));
    }

    public function update(Request $request, BusService $buses, int $bus): JsonResponse
    {
        $buses->find($bus);
        $buses->update($bus, $request->validate($buses->rules($bus)));

        return response()->json(['data' => $buses->find($bus)]);
    }

    public function destroy(BusService $buses, int $bus): JsonResponse
    {
        $buses->deactivate($bus);

        return response()->json(['data' => ['id' => $bus, 'status' => 'inactive']]);
    }
}
