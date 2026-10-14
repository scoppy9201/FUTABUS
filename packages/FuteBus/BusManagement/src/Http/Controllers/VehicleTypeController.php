<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

use FuteBus\BusManagement\Http\Requests\VehicleTypeInputRequest;
use FuteBus\BusManagement\Services\VehicleTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehicleTypeController extends Controller
{
    public function index(Request $request, VehicleTypeService $types): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $search = (string) $request->input('search', '');
        $vehicleClass = (string) $request->input('vehicle_class', '');
        $status = (string) $request->input('status', '');

        $vehicleTypes = $types->paginate($search, $vehicleClass, $status);
        $company = DB::table('bus_companies')->where('code', 'FUTA')->first();

        return view('BusManagement::vehicle-types.index', [
            'vehicleTypes'  => $vehicleTypes,
            'search'        => $search,
            'vehicleClass'  => $vehicleClass,
            'statusFilter'  => $status,
            'company'       => $company,
            'classes'       => VehicleTypeService::CLASSES,
        ]);
    }

    public function store(VehicleTypeInputRequest $request, VehicleTypeService $types): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validated();
        $types->create($data);

        return redirect()->route('bus-management.vehicle-types.index')
            ->with('success', __('BusManagement::app.flash_created'));
    }

    public function update(VehicleTypeInputRequest $request, VehicleTypeService $types, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validated();
        $types->update($id, $data);

        return redirect()->route('bus-management.vehicle-types.index')
            ->with('success', __('BusManagement::app.flash_updated'));
    }

    public function destroy(Request $request, VehicleTypeService $types, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if (! $types->remove($id)) {
            return redirect()->route('bus-management.vehicle-types.index')
                ->with('warning', __('BusManagement::app.flash_cannot_delete_in_use'));
        }

        return redirect()->route('bus-management.vehicle-types.index')
            ->with('success', __('BusManagement::app.flash_deleted'));
    }

    public function toggle(Request $request, VehicleTypeService $types, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $newStatus = $types->toggle($id);
        $msgKey = $newStatus === 'active' ? 'flash_activated' : 'flash_deactivated';

        return redirect()->route('bus-management.vehicle-types.index')
            ->with('success', __('BusManagement::app.'.$msgKey));
    }
}
