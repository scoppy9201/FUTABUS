<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $search = $request->input('search', '');
        $query  = DB::table('buses')
            ->leftJoin('vehicle_types', 'vehicle_types.id', '=', 'buses.vehicle_type_id')
            ->select('buses.*', 'vehicle_types.name as vehicle_type_name')
            ->orderBy('buses.license_plate');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('buses.license_plate', 'like', "%$search%")
                  ->orWhere('buses.chassis_number', 'like', "%$search%")
                  ->orWhere('buses.brand', 'like', "%$search%");
            });
        }

        return view('BusManagement::buses.index', [
            'buses'        => $query->paginate(10)->withQueryString(),
            'search'       => $search,
            'vehicleTypes' => DB::table('vehicle_types')->where('status', 'active')->orderBy('name')->get(),
            'company'      => DB::table('bus_companies')->where('code', 'FUTA')->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate($this->rules());
        $company = DB::table('bus_companies')->where('code', 'FUTA')->first();

        DB::table('buses')->insert($this->payload($data) + [
            'bus_company_id' => $company?->id,
            'status'         => 'active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->route('bus-management.buses.index')
            ->with('success', __('BusManagement::app.bus_flash_created'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless(DB::table('buses')->where('id', $id)->exists(), 404);

        if ($this->hasActiveTrip($id)) {
            return back()->withErrors(['bus' => __('BusManagement::app.bus_err_active_trip')]);
        }

        $data = $request->validate($this->rules($id) + [
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        DB::table('buses')->where('id', $id)->update(
            $this->payload($data) + ['status' => $data['status'], 'updated_at' => now()]
        );

        return redirect()->route('bus-management.buses.index')
            ->with('success', __('BusManagement::app.bus_flash_updated'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless(DB::table('buses')->where('id', $id)->exists(), 404);

        if ($this->hasActiveTrip($id)) {
            return back()->withErrors(['bus' => __('BusManagement::app.bus_err_active_trip')]);
        }

        DB::table('buses')->where('id', $id)->update(['status' => 'inactive', 'updated_at' => now()]);

        return redirect()->route('bus-management.buses.index')
            ->with('success', __('BusManagement::app.bus_flash_deactivated'));
    }

    private function rules(?int $id = null): array
    {
        return [
            'license_plate'    => ['required', 'string', 'max:20', Rule::unique('buses', 'license_plate')->ignore($id)],
            'chassis_number'   => ['required', 'string', 'max:50', Rule::unique('buses', 'chassis_number')->ignore($id)],
            'color'            => ['required', 'string', 'max:50'],
            'brand'            => ['required', 'string', 'max:100'],
            'manufacture_year' => ['required', 'integer', 'min:1990', 'max:'.((int) date('Y') + 1)],
            'vehicle_type_id'  => ['required', Rule::exists('vehicle_types', 'id')->where('status', 'active')],
            'seat_rows'        => ['required', 'integer', 'min:1', 'max:30'],
            'seat_columns'     => ['required', 'integer', 'min:1', 'max:10'],
            'description'      => ['nullable', 'string', 'max:500'],
        ];
    }

    private function payload(array $d): array
    {
        return [
            'license_plate'    => strtoupper(trim($d['license_plate'])),
            'chassis_number'   => strtoupper(trim($d['chassis_number'])),
            'color'            => $d['color'],
            'brand'            => $d['brand'],
            'manufacture_year' => $d['manufacture_year'],
            'vehicle_type_id'  => $d['vehicle_type_id'],
            'seat_rows'        => $d['seat_rows'],
            'seat_columns'     => $d['seat_columns'],
            'capacity'         => $d['seat_rows'] * $d['seat_columns'],
            'name'             => $d['brand'].' '.strtoupper(trim($d['license_plate'])),
            'description'      => $d['description'] ?? null,
        ];
    }

    /** Xe đang gán cho chuyến chưa kết thúc  */
    private function hasActiveTrip(int $busId): bool
    {
        return Schema::hasTable('trips')
        && DB::table('trips')->where('bus_id', $busId)
        ->whereIn('status', ['scheduled', 'departed'])->exists();
    }
}