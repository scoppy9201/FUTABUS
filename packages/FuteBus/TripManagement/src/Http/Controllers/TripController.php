<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Http\Controllers;

use FuteBus\TripManagement\Events\TripCancelled;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TripController extends Controller
{
    private const EDITABLE = ['unassigned', 'scheduled'];
    private const DT = 'Y-m-d\TH:i';

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $company   = DB::table('bus_companies')->where('code', 'FUTA')->first();
        $search    = (string) $request->input('search', '');
        $status    = (string) $request->input('status', '');
        $date      = (string) $request->input('date', '');

        $query = DB::table('trips')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->leftJoin('buses', 'buses.id', '=', 'trips.bus_id')
            ->where('trips.bus_company_id', $company?->id ?? 0)
            ->select('trips.*', 'routes.origin_city', 'routes.destination_city',
                'routes.code as route_code', 'buses.license_plate', 'buses.capacity')
            ->selectRaw("(select count(*) from bookings where bookings.trip_id = trips.id
                          and bookings.status <> 'cancelled') as ticket_count")
            ->orderByRaw('trips.departure_time < now()')   // chuyến sắp tới lên trước
            ->orderBy('trips.departure_time');

        if ($search !== '') {
            $query->where(fn ($q) => $q->where('routes.origin_city', 'like', "%$search%")
                ->orWhere('routes.destination_city', 'like', "%$search%")
                ->orWhere('routes.code', 'like', "%$search%")
                ->orWhere('buses.license_plate', 'like', "%$search%"));
        }
        if ($status !== '') {
            $query->where('trips.status', $status);
        }
        if ($date !== '') {
            $query->whereDate('trips.departure_time', $date);
        }

        return view('TripManagement::trips.index', [
            'trips'   => $query->paginate(10)->withQueryString(),
            'search'  => $search,
            'status'  => $status,
            'date'    => $date,
            'routes'  => DB::table('routes')->where('is_active', 1)->orderBy('code')->get(),
            'buses'   => DB::table('buses')->where('status', 'active')
                            ->where('bus_company_id', $company?->id ?? 0)
                            ->orderBy('license_plate')->get(['id', 'license_plate', 'capacity']),
            'company' => $company,
        ]);
    }

    /** Thêm chuyến lẻ (không thuộc lịch trình). */
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'route_id'       => ['required', 'exists:routes,id'],
            'bus_id'         => ['required', 'exists:buses,id'],
            'departure_time' => ['required', 'date_format:'.self::DT, $this->futureRule()],
            'arrival_time'   => ['required', 'date_format:'.self::DT, 'after:departure_time'],
            'price'          => ['required', 'numeric', 'gt:0'],                   // 7c
        ], $this->messages());

        $bus = DB::table('buses')->where('id', $data['bus_id'])->where('status', 'active')->first();
        if (! $bus) {
            return back()->withInput()->withErrors(['trip' => __('TripManagement::app.tr_err_bus')]);
        }

        $dep = Carbon::createFromFormat(self::DT, $data['departure_time']);
        $arr = Carbon::createFromFormat(self::DT, $data['arrival_time']);

        if ($this->busConflict((int) $bus->id, $dep, $arr)) {                      // 7a
            return back()->withInput()->withErrors(['trip' => __('TripManagement::app.tr_err_conflict')]);
        }

        DB::table('trips')->insert([
            'route_id'        => $data['route_id'],
            'bus_id'          => $bus->id,
            'bus_company_id'  => $bus->bus_company_id,
            'departure_time'  => $dep,
            'arrival_time'    => $arr,
            'price'           => $data['price'],
            'status'          => 'scheduled',
            'available_seats' => $bus->capacity,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->route('trip-management.trips.index')
            ->with('success', __('TripManagement::app.tr_flash_created'));
    }

    /** Gán/đổi xe, sửa giá; chuyến lẻ sửa thêm tuyến + giờ. */
    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $trip = DB::table('trips')->find($id);
        abort_unless($trip, 404);

        if (! in_array($trip->status, self::EDITABLE, true)) {
            return back()->withErrors(['trip' => __('TripManagement::app.tr_err_status')]);
        }
        if ($this->ticketCount($id) > 0) {
            return back()->withErrors(['trip' => __('TripManagement::app.tr_err_has_tickets')]);
        }

        $manual = $trip->trip_schedule_id === null;

        $rules = [
            'bus_id' => ['nullable', 'exists:buses,id'],
            'price'  => ['required', 'numeric', 'gt:0'],
        ];
        if ($manual) {
            $rules += [
                'route_id'       => ['required', 'exists:routes,id'],
                'departure_time' => ['required', 'date_format:'.self::DT, $this->futureRule()],
                'arrival_time'   => ['required', 'date_format:'.self::DT, 'after:departure_time'],
            ];
        }
        $data = $request->validate($rules, $this->messages());

        $dep = $manual ? Carbon::createFromFormat(self::DT, $data['departure_time']) : Carbon::parse($trip->departure_time);
        $arr = $manual ? Carbon::createFromFormat(self::DT, $data['arrival_time']) : Carbon::parse($trip->arrival_time);

        $bus = null;
        if (! empty($data['bus_id'])) {
            $bus = DB::table('buses')->where('id', $data['bus_id'])->where('status', 'active')->first();
            if (! $bus) {
                return back()->withInput()->withErrors(['trip' => __('TripManagement::app.tr_err_bus')]);
            }
            if ($dep->lte(now())) {                                                // 7b khi gán xe
                return back()->withInput()->withErrors(['trip' => __('TripManagement::app.tr_err_past')]);
            }
            if ($this->busConflict((int) $bus->id, $dep, $arr, $id)) {             // 7a
                return back()->withInput()->withErrors(['trip' => __('TripManagement::app.tr_err_conflict')]);
            }
        }

        $update = [
            'bus_id'          => $bus?->id,
            'price'           => $data['price'],
            'status'          => $bus ? 'scheduled' : 'unassigned',
            'available_seats' => $bus?->capacity,
            'updated_at'      => now(),
        ];
        if ($manual) {
            $update += ['route_id' => $data['route_id'], 'departure_time' => $dep, 'arrival_time' => $arr];
        }

        DB::table('trips')->where('id', $id)->update($update);

        return redirect()->route('trip-management.trips.index')
            ->with('success', __('TripManagement::app.tr_flash_updated'));
    }

    /** Hủy chuyến. */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $trip = DB::table('trips')->find($id);
        abort_unless($trip, 404);

        if (! in_array($trip->status, self::EDITABLE, true)) {
            return back()->withErrors(['trip' => __('TripManagement::app.tr_err_status')]);
        }

        $dep     = Carbon::parse($trip->departure_time);
        $tickets = $this->ticketCount($id);

        if ($dep->lte(now())) {
            return back()->withErrors(['trip' => __('TripManagement::app.tr_err_cancel_past')]);
        }
        if ($tickets > 0 && now()->addHours(48)->gt($dep)) {                       // 48h chỉ với chuyến có vé
            return back()->withErrors(['trip' => __('TripManagement::app.tr_err_cancel_48h')]);
        }

        DB::transaction(function () use ($id, $tickets) {
            DB::table('trips')->where('id', $id)->update(['status' => 'cancelled', 'updated_at' => now()]);
            event(new TripCancelled($id, $tickets));
        });

        return redirect()->route('trip-management.trips.index')
            ->with($tickets > 0 ? 'warning' : 'success',
                $tickets > 0 ? __('TripManagement::app.tr_flash_cancelled_tickets', ['n' => $tickets])
                             : __('TripManagement::app.tr_flash_cancelled'));
    }

    private function ticketCount(int $tripId): int
    {
        return DB::table('bookings')->where('trip_id', $tripId)->where('status', '<>', 'cancelled')->count();
    }

    
    private function busConflict(int $busId, Carbon $dep, Carbon $arr, ?int $ignoreId = null): bool
    {
        return DB::table('trips')
            ->where('bus_id', $busId)
            ->whereIn('status', ['scheduled', 'departed'])
            ->where('departure_time', '<', $arr)
            ->where('arrival_time', '>', $dep)
            ->when($ignoreId, fn ($q) => $q->where('id', '<>', $ignoreId))
            ->exists();
    }

    
    private function futureRule(): \Closure
    {
        return function ($attr, $value, $fail) {
            $d = Carbon::createFromFormat(self::DT, $value);
            if ($d === false || $d->lte(now())) {
                $fail(__('TripManagement::app.tr_err_past'));
            }
        };
    }

    private function messages(): array
    {
        return [
            'price.gt'            => __('TripManagement::app.tr_err_price'),
            'arrival_time.after'  => __('TripManagement::app.tr_err_arrival'),
        ];
    }
}