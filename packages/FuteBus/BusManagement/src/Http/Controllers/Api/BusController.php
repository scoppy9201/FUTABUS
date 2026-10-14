<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers\Api;

use FuteBus\BusManagement\Http\Requests\BusInputRequest;
use FuteBus\BusManagement\Http\Requests\BusSearchRequest;
use FuteBus\BusManagement\Services\BusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BusController extends Controller
{
    public function index(BusSearchRequest $request, BusService $buses): JsonResponse
    {
        $filters = $request->validated();

        return response()->json($buses->paginate($filters['search'] ?? ''));
    }

    public function show(BusService $buses, int $bus): JsonResponse
    {
        return response()->json(['data' => $buses->find($bus)]);
    }

    public function store(BusInputRequest $request, BusService $buses): JsonResponse
    {
        $id = $buses->create($request->validated());

        return response()->json(['data' => $buses->find($id)], 201)
            ->header('Location', route('api.v1.admin.buses.show', ['bus' => $id]));
    }

    public function update(BusInputRequest $request, BusService $buses, int $bus): JsonResponse
    {
        $buses->assertEditable($bus);
        $buses->update($bus, $request->validated());

        return response()->json(['data' => $buses->find($bus)]);
    }

    public function destroy(BusService $buses, int $bus): JsonResponse
    {
        $buses->deactivate($bus);

        return response()->json(['data' => ['id' => $bus, 'status' => 'inactive']]);
    }
}
