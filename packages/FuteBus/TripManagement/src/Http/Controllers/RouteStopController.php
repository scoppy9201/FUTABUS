<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Http\Controllers;

use FuteBus\TripManagement\Services\TripGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RouteStopController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate($this->rules() + ['route_id' => ['required', 'exists:routes,id']]);
        $routeId = (int) $data['route_id'];

        DB::transaction(function () use ($data, $routeId) {
            $id = DB::table('route_stops')->insertGetId($this->payload($data) + [
                'route_id'   => $routeId, 'status' => 'active',
                'stop_order' => 9999, 'offset_minutes' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->reposition($routeId, $id, (int) ($data['position'] ?? 0));
            $this->recalc($routeId);
        });

        return $this->target($request)->with('success', __('TripManagement::app.rs_flash_created'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $stop = DB::table('route_stops')->find($id);
        abort_unless($stop, 404);

        $data = $request->validate($this->rules());
        $routeId = (int) $stop->route_id;

        DB::transaction(function () use ($data, $id, $routeId, $stop) {
            DB::table('route_stops')->where('id', $id)->update(
                $this->payload($data) + ['updated_at' => now()]
            );
            $this->reposition($routeId, $id, (int) ($data['position'] ?? $stop->stop_order));
            $this->recalc($routeId);
        });

        return $this->target($request)->with('success', __('TripManagement::app.rs_flash_updated'));
    }

    /** Xóa hẳn điểm dừng. */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $stop = DB::table('route_stops')->find($id);
        abort_unless($stop, 404);

        $routeId = (int) $stop->route_id;
        $count = DB::table('route_stops')->where('route_id', $routeId)->count();
        $hasSchedule = DB::table('trip_schedules')->where('route_id', $routeId)->where('status', 'active')->exists();

        if ($hasSchedule && $count <= 1) {
            return $this->target($request)->withErrors(['stop' => __('TripManagement::app.rs_err_min')]);
        }

        DB::transaction(function () use ($id, $routeId) {
            DB::table('route_stops')->where('id', $id)->delete();
            $this->recalc($routeId);
        });

        return $this->target($request)->with('warning', __('TripManagement::app.rs_flash_deleted'));
    }

    /** Về trang lịch trình, giữ dòng đang mở. */
    private function target(Request $request): RedirectResponse
    {
        $sid = (int) $request->input('schedule_id');

        return redirect()->route('trip-management.schedules.index', $sid ? ['open' => $sid] : []);
    }

    /** Đặt điểm dừng vào vị trí $pos (0 = cuối danh sách). */
    private function reposition(int $routeId, int $id, int $pos): void
    {
        $ids = DB::table('route_stops')->where('route_id', $routeId)->where('id', '<>', $id)
            ->orderBy('stop_order')->orderBy('id')->pluck('id')->all();

        $pos = $pos > 0 ? min($pos, count($ids) + 1) : count($ids) + 1;
        array_splice($ids, $pos - 1, 0, [$id]);

        foreach ($ids as $i => $sid) {
            DB::table('route_stops')->where('id', $sid)->update(['stop_order' => $i + 1]);
        }
    }

    /** Đánh lại thứ tự, tính giờ cộng dồn và cập nhật giờ đến cho các chuyến. */
    private function recalc(int $routeId): void
    {
        $run = 0;
        $i = 1;

        foreach (DB::table('route_stops')->where('route_id', $routeId)
            ->orderBy('stop_order')->orderBy('id')->get(['id', 'leg_minutes']) as $s) {
            $run += (int) $s->leg_minutes;
            DB::table('route_stops')->where('id', $s->id)
                ->update(['stop_order' => $i++, 'offset_minutes' => $run]);
        }

        TripGenerator::syncArrivals($routeId);
    }

    private function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'address'     => ['nullable', 'string', 'max:255'],
            'leg_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'position'    => ['nullable', 'integer', 'min:1'],
            'stop_type'   => ['required', Rule::in(['pickup', 'dropoff', 'both'])],
            'schedule_id' => ['nullable', 'integer'],
        ];
    }

    private function payload(array $d): array
    {
        return [
            'name'        => trim($d['name']),
            'address'     => $d['address'] ?? null,
            'leg_minutes' => $d['leg_minutes'],
            'stop_type'   => $d['stop_type'],
        ];
    }
}
