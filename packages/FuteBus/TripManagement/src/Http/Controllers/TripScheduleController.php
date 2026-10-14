<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Http\Controllers;

use FuteBus\BusCompanyManagement\Models\BusCompany as BusCompanyRecord;
use FuteBus\RouteManagement\Models\BusRoute as BusRouteRecord;
use FuteBus\RouteManagement\Models\RouteStop as RouteStopRecord;
use FuteBus\TripManagement\Http\Requests\TripScheduleRequest;
use FuteBus\TripManagement\Models\TripSchedule as TripScheduleRecord;
use FuteBus\TripManagement\Services\TripGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TripScheduleController extends Controller
{
    public function __construct(private TripGenerator $generator) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $search = $request->input('search', '');
        $company = BusCompanyRecord::query()->toBase()->where('code', 'FUTA')->first();
        $query = TripScheduleRecord::query()->toBase()
            ->join('routes', 'routes.id', '=', 'trip_schedules.route_id')
            ->where('routes.bus_company_id', $company?->id ?? 0)
            ->select('trip_schedules.*', 'routes.code as route_code',
                'routes.origin_city', 'routes.destination_city')
            ->orderByDesc('trip_schedules.id');

        if ($search) {
            $query->where(fn ($q) => $q->where('routes.code', 'like', "%$search%")
                ->orWhere('routes.origin_city', 'like', "%$search%")
                ->orWhere('routes.destination_city', 'like', "%$search%"));
        }

        $schedules = $query->paginate(10)->withQueryString();

        return view('TripManagement::schedules.index', [
            'schedules'    => $schedules,
            'search'       => $search,
            'routes'       => BusRouteRecord::query()->toBase()
                ->where('bus_company_id', $company?->id ?? 0)
                ->where('is_active', 1)
                ->whereIn('id', RouteStopRecord::query()->toBase()
                    ->select('route_id')
                    ->where('status', 'active')
                    ->groupBy('route_id')
                    ->havingRaw('COUNT(*) >= 2'))
                ->orderBy('code')->get(),
            'company'      => $company,
            'stopsByRoute' => RouteStopRecord::query()->toBase()
                ->whereIn('route_id', $schedules->pluck('route_id')->unique())
                ->where('status', 'active')
                ->orderBy('stop_order')->orderBy('id')->get()->groupBy('route_id'),
            'open'         => $request->integer('open') ?: null,
        ]);
    }

    public function store(TripScheduleRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validated();

        //  tuyến phải có >= 2 điểm dừng đang hoạt động
        if (! TripGenerator::hasStops((int) $data['route_id'])) {
            return back()->withInput()->withErrors(['schedule' => __('TripManagement::app.sch_err_stops')]);
        }

        if ($msg = $this->conflict($data)) {
            return back()->withInput()->withErrors(['schedule' => $msg]);
        }

        try {
            DB::transaction(function () use ($data) {
                $id = TripScheduleRecord::query()->toBase()->insertGetId($this->payload($data) + [
                    'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->generator->generate(TripScheduleRecord::query()->toBase()->find($id));
            });
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['schedule' => __('TripManagement::app.sch_err_generate')]);
        }

        return redirect()->route('trip-management.schedules.index')
            ->with('success', __('TripManagement::app.sch_flash_created'));
    }

    public function update(TripScheduleRequest $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $schedule = TripScheduleRecord::query()->toBase()->find($id);
        abort_unless($schedule, 404);
        $this->assertOwnedSchedule($schedule);

        if ($schedule->status !== 'active') {
            return back()->withErrors(['schedule' => __('TripManagement::app.sch_err_inactive')]);
        }

        $data = $request->validated();

        // tuyến phải có >= 2 điểm dừng đang hoạt động
        if (! TripGenerator::hasStops((int) $data['route_id'])) {
            return back()->withInput()->withErrors(['schedule' => __('TripManagement::app.sch_err_stops')]);
        }

        if ($msg = $this->conflict($data, $id)) {
            return back()->withInput()->withErrors(['schedule' => $msg]);
        }

        try {
            DB::transaction(function () use ($data, $id) {
                TripScheduleRecord::query()->toBase()->where('id', $id)
                    ->update($this->payload($data) + ['updated_at' => now()]);
                $this->generator->generate(TripScheduleRecord::query()->toBase()->find($id));
            });
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
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
        $schedule = TripScheduleRecord::query()->toBase()->find($id);
        abort_unless($schedule, 404);
        $this->assertOwnedSchedule($schedule);

        if ($schedule->status !== 'active') {
            return back()->withErrors(['schedule' => __('TripManagement::app.sch_err_inactive')]);
        }

        TripScheduleRecord::query()->toBase()->where('id', $id)
            ->update(['status' => 'inactive', 'updated_at' => now()]);

        return redirect()->route('trip-management.schedules.index')
            ->with('warning', __('TripManagement::app.sch_flash_deactivated'));
    }

    private function assertOwnedSchedule(object $schedule): void
    {
        abort_unless(BusRouteRecord::query()->toBase()->where('id', $schedule->route_id)
            ->where('bus_company_id', BusCompanyRecord::query()->toBase()->where('code', 'FUTA')->value('id') ?? 0)->exists(), 404);
    }

    private function payload(array $d): array
    {
        $days = array_map('intval', $d['days_of_week']);
        sort($days);

        return [
            'route_id'         => $d['route_id'],
            'duration_minutes' => TripGenerator::duration((int) $d['route_id']),
            'departure_time'   => $d['departure_time'],
            'days_of_week'     => json_encode($days),
            'start_date'       => $d['start_date'],
            'end_date'         => $d['end_date'],
            'price'            => $d['price'],
        ];
    }

    /** 7a: cùng tuyến + cùng giờ + khoảng ngày giao nhau + chung ngày trong tuần. */
    private function conflict(array $d, ?int $ignoreId = null): ?string
    {
        $rows = TripScheduleRecord::query()->toBase()
            ->where('status', 'active')
            ->where('route_id', $d['route_id'])
            ->whereIn('departure_time', [$d['departure_time'], $d['departure_time'].':00'])
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
