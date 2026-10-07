<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Http\Controllers;

use FuteBus\TripManagement\Services\TripGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TripScheduleController extends Controller
{
    public function __construct(private TripGenerator $generator) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $search = $request->input('search', '');
        $query  = DB::table('trip_schedules')
            ->join('routes', 'routes.id', '=', 'trip_schedules.route_id')
            ->select('trip_schedules.*', 'routes.code as route_code',
                     'routes.origin_city', 'routes.destination_city')
            ->orderByDesc('trip_schedules.id');

        if ($search) {
            $query->where(fn ($q) => $q->where('routes.code', 'like', "%$search%")
                ->orWhere('routes.origin_city', 'like', "%$search%")
                ->orWhere('routes.destination_city', 'like', "%$search%"));
        }

        return view('TripManagement::schedules.index', [
            'schedules' => $query->paginate(10)->withQueryString(),
            'search'    => $search,
            'routes'    => DB::table('routes')->where('is_active', 1)->orderBy('code')->get(),
            'company'   => DB::table('bus_companies')->where('code', 'FUTA')->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $this->validated($request);
        if ($msg = $this->conflict($data)) {
            return back()->withInput()->withErrors(['schedule' => $msg]);
        }

        try {
            DB::transaction(function () use ($data) {
                $id = DB::table('trip_schedules')->insertGetId($this->payload($data) + [
                    'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->generator->generate(DB::table('trip_schedules')->find($id));
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['schedule' => __('TripManagement::app.sch_err_generate')]);
        }

        return redirect()->route('trip-management.schedules.index')
            ->with('success', __('TripManagement::app.sch_flash_created'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $schedule = DB::table('trip_schedules')->find($id);
        abort_unless($schedule, 404);

        if ($schedule->status !== 'active') {
            return back()->withErrors(['schedule' => __('TripManagement::app.sch_err_inactive')]);
        }

        $data = $this->validated($request);
        if ($msg = $this->conflict($data, $id)) {
            return back()->withInput()->withErrors(['schedule' => $msg]);
        }

        try {
            DB::transaction(function () use ($data, $id) {
                DB::table('trip_schedules')->where('id', $id)
                    ->update($this->payload($data) + ['updated_at' => now()]);
                $this->generator->generate(DB::table('trip_schedules')->find($id));
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['schedule' => __('TripManagement::app.sch_err_generate')]);
        }

        return redirect()->route('trip-management.schedules.index')
            ->with('success', __('TripManagement::app.sch_flash_updated'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $schedule = DB::table('trip_schedules')->find($id);
        abort_unless($schedule, 404);

        if ($schedule->status !== 'active') {
            return back()->withErrors(['schedule' => __('TripManagement::app.sch_err_inactive')]);
        }

        DB::table('trip_schedules')->where('id', $id)
            ->update(['status' => 'inactive', 'updated_at' => now()]);

        return redirect()->route('trip-management.schedules.index')
            ->with('warning', __('TripManagement::app.sch_flash_deactivated'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'route_id'         => ['required', 'exists:routes,id'],
            'departure_time'   => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:0', 'max:2880'],
            'days_of_week'     => ['required', 'array', 'min:1'],
            'days_of_week.*'   => ['integer', 'between:1,7'],
            'start_date'       => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'after_or_equal:today'],
            'price'            => ['required', 'integer', 'min:0'],
        ], [
            'end_date.after_or_equal' => __('TripManagement::app.sch_err_date'),
            'end_date.after_or_equal:today' => __('TripManagement::app.sch_err_past'),
        ]);
    }

    private function payload(array $d): array
    {
        $days = array_map('intval', $d['days_of_week']);
        sort($days);

        return [
            'route_id'         => $d['route_id'],
            'departure_time'   => $d['departure_time'],
            'duration_minutes' => $d['duration_minutes'],
            'days_of_week'     => json_encode($days),
            'start_date'       => $d['start_date'],
            'end_date'         => $d['end_date'],
            'price'            => $d['price'],
        ];
    }

    /** 7a: cùng tuyến + cùng giờ + khoảng ngày giao nhau + chung ngày trong tuần. */
    private function conflict(array $d, ?int $ignoreId = null): ?string
    {
        $rows = DB::table('trip_schedules')
            ->where('status', 'active')
            ->where('route_id', $d['route_id'])
            ->whereRaw("TIME_FORMAT(departure_time, '%H:%i') = ?", [$d['departure_time']])
            ->where('start_date', '<=', $d['end_date'])
            ->where('end_date', '>=', $d['start_date'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get();

        foreach ($rows as $r) {
            $shared = array_intersect(
                json_decode($r->days_of_week, true) ?: [],
                array_map('intval', $d['days_of_week'])
            );
            if ($shared) {
                return __('TripManagement::app.sch_err_conflict', ['from' => $r->start_date, 'to' => $r->end_date]);
            }
        }

        return null;
    }
}