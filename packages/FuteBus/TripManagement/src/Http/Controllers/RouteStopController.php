<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RouteStopController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $routes  = DB::table('routes')->where('is_active', 1)->orderBy('code')->get();
        $routeId = (int) $request->input('route_id', $routes->first()->id ?? 0);

        return view('TripManagement::route-stops.index', [
            'routes'  => $routes,
            'route'   => $routes->firstWhere('id', $routeId),
            'routeId' => $routeId,
            'stops'   => DB::table('route_stops')->where('route_id', $routeId)
                            ->orderBy('stop_order')->get(),
            'company' => DB::table('bus_companies')->where('code', 'FUTA')->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate($this->rules() + ['route_id' => ['required', 'exists:routes,id']]);

        DB::table('route_stops')->insert($this->payload($data) + [
        'route_id' => $data['route_id'], 'status' => 'active', 'stop_order' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ]);
        $this->reorder((int) $data['route_id']);

        return $this->back((int) $data['route_id'], 'success', __('TripManagement::app.rs_flash_created'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $stop = DB::table('route_stops')->find($id);
        abort_unless($stop, 404);

        $data = $request->validate($this->rules() + ['status' => ['required', Rule::in(['active', 'inactive'])]]);

        if ($data['status'] === 'inactive' && $stop->status === 'active' && ($msg = $this->cannotDrop((int) $stop->route_id))) {
            return back()->withErrors(['stop' => $msg]);
        }

        DB::table('route_stops')->where('id', $id)->update(
            $this->payload($data) + ['status' => $data['status'], 'updated_at' => now()]
        );
        $this->reorder((int) $stop->route_id);

        return $this->back((int) $stop->route_id, 'success', __('TripManagement::app.rs_flash_updated'));
    }

    /** Ngưng hoạt động (không xóa để giữ lịch sử). */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $stop = DB::table('route_stops')->find($id);
        abort_unless($stop, 404);

        if ($stop->status !== 'active') {
            return back()->withErrors(['stop' => __('TripManagement::app.rs_err_inactive')]);
        }
        if ($msg = $this->cannotDrop((int) $stop->route_id)) {
            return back()->withErrors(['stop' => $msg]);
        }

        DB::table('route_stops')->where('id', $id)->update(['status' => 'inactive', 'updated_at' => now()]);

        return $this->back((int) $stop->route_id, 'warning', __('TripManagement::app.rs_flash_deactivated'));
    }

    /** Tuyến đang có lịch trình áp dụng thì phải còn >= 2 điểm hoạt động. */
    private function cannotDrop(int $routeId): ?string
    {
        $active   = DB::table('route_stops')->where('route_id', $routeId)->where('status', 'active')->count();
        $hasSched = DB::table('trip_schedules')->where('route_id', $routeId)->where('status', 'active')->exists();

        return ($hasSched && $active <= 2) ? __('TripManagement::app.rs_err_min') : null;
    }

    private function reorder(int $routeId): void
    {
        $i = 1;
        foreach (DB::table('route_stops')->where('route_id', $routeId)
                     ->orderBy('offset_minutes')->orderBy('id')->pluck('id') as $sid) {
            DB::table('route_stops')->where('id', $sid)->update(['stop_order' => $i++]);
        }
    }

    private function back(int $routeId, string $key, string $msg): RedirectResponse
    {
        return redirect()->route('trip-management.stops.index', ['route_id' => $routeId])->with($key, $msg);
    }

    private function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:150'],
            'address'        => ['nullable', 'string', 'max:255'],
            'offset_minutes' => ['required', 'integer', 'min:0', 'max:4320'],
            'stop_type'      => ['required', Rule::in(['pickup', 'dropoff', 'both'])],
        ];
    }

    private function payload(array $d): array
    {
        return [
            'name'           => trim($d['name']),
            'address'        => $d['address'] ?? null,
            'offset_minutes' => $d['offset_minutes'],
            'stop_type'      => $d['stop_type'],
        ];
    }
}