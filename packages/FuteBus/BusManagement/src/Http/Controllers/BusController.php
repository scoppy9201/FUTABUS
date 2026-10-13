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
        $typeId = (string) $request->input('vehicle_type_id', '');
        $status = (string) $request->input('status', '');

        return view('BusManagement::buses.index', [
            'buses'        => $buses->paginate($search, $typeId, $status),
            'search'       => $search,
            'typeIdFilter' => $typeId,
            'statusFilter' => $status,
            'vehicleTypes' => DB::table('vehicle_types')->where('status', 'active')->orderBy('name')->get(),
            'vehicleColors' => BusService::COLORS,
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

        $buses->update($id, $request->validate($buses->rules($id)));

        return redirect()->route('bus-management.buses.index')
            ->with('success', __('BusManagement::app.bus_flash_updated'));
    }

    /** Xóa xe hoàn toàn (chỉ khi chưa có hồ sơ giấy tờ và chưa xếp chuyến). */
    public function destroy(Request $request, BusService $buses, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if (! $buses->remove($id)) {
            return redirect()->route('bus-management.buses.index')
                ->with('warning', __('BusManagement::app.bus_err_cannot_delete'));
        }

        return redirect()->route('bus-management.buses.index')
            ->with('success', __('BusManagement::app.bus_flash_deleted'));
    }

    /** Ngừng sử dụng / Kích hoạt lại. */
    public function toggle(Request $request, BusService $buses, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $newStatus = $buses->toggle($id);
        $msgKey = $newStatus === 'active' ? 'bus_flash_activated' : 'bus_flash_deactivated';

        return redirect()->route('bus-management.buses.index')
            ->with('success', __('BusManagement::app.'.$msgKey));
    }
}
