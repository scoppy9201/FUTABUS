<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehicleTypeController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $search = $request->input('search', '');
        $query  = DB::table('vehicle_types')->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $vehicleTypes = $query->paginate(10)->withQueryString();
        $company      = DB::table('bus_companies')->where('code', 'FUTA')->first();

        return view('BusManagement::vehicle-types.index', [
            'vehicleTypes' => $vehicleTypes,
            'search'       => $search,
            'company'      => $company,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'name'             => 'required|string|max:100|unique:vehicle_types,name',
            'description'      => 'nullable|string|max:500',
            'default_capacity' => 'required|integer|min:1|max:255',
        ]);

        DB::table('vehicle_types')->insert([
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'default_capacity' => $data['default_capacity'],
            'status'           => 'active',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('bus-management.vehicle-types.index')
            ->with('success', __('BusManagement::app.flash_created'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        abort_unless(DB::table('vehicle_types')->where('id', $id)->exists(), 404);

        $data = $request->validate([
            'name'             => 'required|string|max:100|unique:vehicle_types,name,'.$id,
            'description'      => 'nullable|string|max:500',
            'default_capacity' => 'required|integer|min:1|max:255',
        ]);

        DB::table('vehicle_types')->where('id', $id)->update([
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'default_capacity' => $data['default_capacity'],
            'updated_at'       => now(),
        ]);

        return redirect()->route('bus-management.vehicle-types.index')
            ->with('success', __('BusManagement::app.flash_updated'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        // Business rule: Only soft-delete (set inactive) if used in buses
        $usedInBuses = DB::table('buses')->where('vehicle_type_id', $id)->exists();

        if ($usedInBuses) {
            // Soft delete: change status to inactive
            DB::table('vehicle_types')->where('id', $id)->update([
                'status'     => 'inactive',
                'updated_at' => now(),
            ]);

            return redirect()->route('bus-management.vehicle-types.index')
                ->with('warning', __('BusManagement::app.flash_deactivated'));
        }

        // Hard delete if not used
        DB::table('vehicle_types')->where('id', $id)->delete();

        return redirect()->route('bus-management.vehicle-types.index')
            ->with('success', __('BusManagement::app.flash_deleted'));
    }
}
