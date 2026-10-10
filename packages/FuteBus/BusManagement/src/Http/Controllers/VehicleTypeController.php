<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

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
        $vehicleTypes = $types->paginate($search);
        $company = DB::table('bus_companies')->where('code', 'FUTA')->first();

        return view('BusManagement::vehicle-types.index', [
            'vehicleTypes' => $vehicleTypes,
            'search'       => $search,
            'company'      => $company,
        ]);
    }

    public function store(Request $request, VehicleTypeService $types): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $types->create($request->validate($types->rules()));

        return redirect()->route('bus-management.vehicle-types.index')
            ->with('success', __('BusManagement::app.flash_created'));
    }

    public function update(Request $request, VehicleTypeService $types, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $types->update($id, $request->validate($types->rules($id)));

        return redirect()->route('bus-management.vehicle-types.index')
            ->with('success', __('BusManagement::app.flash_updated'));
    }

    public function destroy(Request $request, VehicleTypeService $types, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if (! $types->remove($id)) {
            return redirect()->route('bus-management.vehicle-types.index')
                ->with('warning', __('BusManagement::app.flash_deactivated'));
        }

        return redirect()->route('bus-management.vehicle-types.index')
            ->with('success', __('BusManagement::app.flash_deleted'));
    }
}
