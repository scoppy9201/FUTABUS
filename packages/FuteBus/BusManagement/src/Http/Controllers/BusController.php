<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

use FuteBus\BusManagement\Services\BusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BusController extends Controller
{
    public function index(Request $request, BusService $buses): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $search = (string) $request->input('search', '');

        return view('BusManagement::buses.index', [
            'buses'        => $buses->paginate($search),
            'search'       => $search,
            'vehicleTypes' => DB::table('vehicle_types')->where('status', 'active')->orderBy('name')->get(),
            'company'      => DB::table('bus_companies')->where('code', 'FUTA')->first(),
        ]);
    }

    public function store(Request $request, BusService $buses): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $buses->create($request->validate($buses->rules()));

        return redirect()->route('bus-management.buses.index')
            ->with('success', __('BusManagement::app.bus_flash_created'));
    }

    public function update(Request $request, BusService $buses, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $buses->find($id);
        $buses->update($id, $request->validate($buses->rules($id)));

        return redirect()->route('bus-management.buses.index')
            ->with('success', __('BusManagement::app.bus_flash_updated'));
    }

    public function destroy(Request $request, BusService $buses, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $buses->deactivate($id);

        return redirect()->route('bus-management.buses.index')
            ->with('success', __('BusManagement::app.bus_flash_deactivated'));
    }
}
