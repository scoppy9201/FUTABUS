<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers\Api;

use FuteBus\BusManagement\Http\Requests\VehicleTypeInputRequest;
use FuteBus\BusManagement\Http\Requests\VehicleTypeSearchRequest;
use FuteBus\BusManagement\Services\VehicleTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class VehicleTypeController extends Controller
{
    public function index(VehicleTypeSearchRequest $request, VehicleTypeService $types): JsonResponse
    {
        $filters = $request->validated();

        return response()->json($types->paginate($filters['search'] ?? ''));
    }

    public function show(int $vehicleType): JsonResponse
    {
        return response()->json(['data' => $this->find($vehicleType)]);
    }

    public function store(VehicleTypeInputRequest $request, VehicleTypeService $types): JsonResponse
    {
        $id = $types->create($request->validated());

        return response()->json(['data' => $this->find($id)], 201)
            ->header('Location', route('api.v1.admin.vehicle-types.show', ['vehicleType' => $id]));
    }

    public function update(VehicleTypeInputRequest $request, VehicleTypeService $types, int $vehicleType): JsonResponse
    {
        $this->find($vehicleType);
        $types->update($vehicleType, $request->validated());

        return response()->json(['data' => $this->find($vehicleType)]);
    }

    public function destroy(VehicleTypeService $types, int $vehicleType): JsonResponse
    {
        $deleted = $types->remove($vehicleType);

        return response()->json(['data' => [
            'id'     => $vehicleType,
            'status' => $deleted ? 'deleted' : 'inactive',
        ]]);
    }

    private function find(int $id): object
    {
        $type = DB::table('vehicle_types')->where('id', $id)
            ->first(['id', 'name', 'description', 'default_capacity', 'status']);
        abort_if($type === null, 404);

        return $type;
    }
}
