<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Services;

use FuteBus\BusManagement\Models\Bus as BusRecord;
use FuteBus\RouteManagement\Models\BusRoute as BusRouteRecord;
use FuteBus\RouteManagement\Models\RouteStop as RouteStopRecord;
use FuteBus\TripManagement\Models\Trip as TripRecord;
use FuteBus\TripManagement\Models\TripSchedule as TripScheduleRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TripGenerator
{
    public const DAYS_AHEAD = 30;

    /** Tuyến cần ít nhất hai điểm dừng đang hoạt động để lập lịch. */
    public static function hasStops(int $routeId): bool
    {
        return RouteStopRecord::query()->toBase()
            ->where('route_id', $routeId)
            ->where('status', 'active')
            ->count() >= 2;
    }

    /** Tổng thời gian chạy (phút) = tổng số phút của các chặng. */
    public static function duration(int $routeId): int
    {
        return (int) RouteStopRecord::query()->toBase()
            ->where('route_id', $routeId)
            ->where('status', 'active')
            ->sum('leg_minutes');
    }

    /** Cập nhật giờ đến cho chuyến chưa khởi hành và chưa có vé. */
    public static function syncArrivals(int $routeId): void
    {
        $minutes = self::duration($routeId);

        $trips = TripRecord::query()->toBase()
            ->where('route_id', $routeId)
            ->where('departure_time', '>', now())
            ->whereIn('status', ['unassigned', 'scheduled'])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('bookings')
                    ->whereColumn('bookings.trip_id', 'trips.id')
                    ->where('bookings.status', '<>', 'cancelled');
            })
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('tickets')->whereColumn('tickets.trip_id', 'trips.id'))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('sepay_payment_intents')
                ->where(fn ($q) => $q->whereColumn('sepay_payment_intents.trip_id', 'trips.id')
                    ->orWhereColumn('sepay_payment_intents.secondary_trip_id', 'trips.id')))
            ->orderBy('id')->lockForUpdate()->get();
        $arrivals = $trips->mapWithKeys(fn ($trip) => [$trip->id => Carbon::parse($trip->departure_time)->addMinutes($minutes)]);
        $busIds = $trips->pluck('bus_id')->filter()->unique()->sort()->values();
        BusRecord::query()->toBase()->whereIn('id', $busIds)->orderBy('id')->lockForUpdate()->get();
        $busTrips = TripRecord::query()->toBase()->whereIn('bus_id', $busIds)->whereIn('status', ['scheduled', 'departed'])
            ->orderBy('id')->lockForUpdate()->get()->groupBy('bus_id');
        foreach ($trips as $trip) {
            if ($trip->bus_id) {
                foreach ($busTrips[$trip->bus_id] ?? [] as $other) {
                    $otherArrival = $arrivals[$other->id] ?? Carbon::parse($other->arrival_time);
                    if ($other->id !== $trip->id && Carbon::parse($other->departure_time)->lt($arrivals[$trip->id])
                        && $otherArrival->gt(Carbon::parse($trip->departure_time))) {
                        throw ValidationException::withMessages(['leg_minutes' => __('RouteManagement::app.bus_conflict')]);
                    }
                }
            }
        }
        foreach ($arrivals as $id => $arrival) {
            TripRecord::query()->toBase()->where('id', $id)->update(['arrival_time' => $arrival, 'updated_at' => now()]);
        }
        TripScheduleRecord::query()->toBase()->where('route_id', $routeId)->where('status', 'active')
            ->update(['duration_minutes' => $minutes, 'updated_at' => now()]);
    }

    public function generate(object $schedule): int
    {
        return DB::transaction(function () use ($schedule) {
            $current = TripScheduleRecord::query()->lockForUpdate()->find($schedule->id);

            return $current ? $this->generateSchedule($current) : 0;
        }, 3);
    }

    private function generateSchedule(object $schedule): int
    {
        $route = BusRouteRecord::query()->toBase()->join('bus_companies', 'bus_companies.id', '=', 'routes.bus_company_id')
            ->where('routes.id', $schedule->route_id)->where('routes.is_active', true)
            ->where('bus_companies.code', 'FUTA')->select('routes.bus_company_id', 'routes.vehicle_type')->first();
        if (! $route || $schedule->status !== 'active' || ! self::hasStops((int) $schedule->route_id)) {
            return 0;
        }

        $days = is_array($schedule->days_of_week) ? $schedule->days_of_week : (json_decode($schedule->days_of_week, true) ?: []);
        $from = Carbon::today()->max(Carbon::parse($schedule->start_date));
        $to = Carbon::today()->addDays(self::DAYS_AHEAD)->min(Carbon::parse($schedule->end_date)->endOfDay());
        $companyId = $route->bus_company_id;
        $duration = self::duration((int) $schedule->route_id);
        $candidates = [];
        $count = 0;

        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            if (! in_array($d->dayOfWeekIso, $days, true)) {
                continue;
            }

            $departure = $d->copy()->setTimeFromTimeString($schedule->departure_time);
            if ($departure->lte(now())) {
                continue;
            }
            $candidates[$d->toDateString()] = [
                'trip_schedule_id' => $schedule->id,
                'route_id'         => $schedule->route_id,
                'bus_company_id'   => $companyId,
                'departure_time'   => $departure,
                'arrival_time'     => $departure->copy()->addMinutes($duration),
                'price'            => $schedule->price,
                'updated_at'       => now(),
            ];
        }

        $existing = TripRecord::query()->toBase()
            ->where('trip_schedule_id', $schedule->id)
            ->where('departure_time', '>', now())
            ->whereIn('status', ['unassigned', 'scheduled'])
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('bookings')
                ->whereColumn('bookings.trip_id', 'trips.id'))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('booked_seats')
                ->whereColumn('booked_seats.trip_id', 'trips.id'))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('tickets')
                ->whereColumn('tickets.trip_id', 'trips.id'))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('sepay_payment_intents')
                ->where(function ($intent) {
                    $intent->whereColumn('sepay_payment_intents.trip_id', 'trips.id')
                        ->orWhereColumn('sepay_payment_intents.secondary_trip_id', 'trips.id');
                }))
            ->lockForUpdate()
            ->get();

        $protectedTimes = TripRecord::query()->toBase()->where('trip_schedule_id', $schedule->id)
            ->whereNotIn('id', $existing->pluck('id'))->pluck('departure_time');
        foreach ($protectedTimes as $time) {
            unset($candidates[Carbon::parse($time)->toDateString()]);
        }
        $this->validateAssignments($existing, $candidates, $route);

        foreach ($existing as $trip) {
            $date = Carbon::parse($trip->departure_time)->toDateString();
            $candidate = $candidates[$date] ?? null;

            if ($candidate === null) {
                TripRecord::query()->toBase()->where('id', $trip->id)->delete();

                continue;
            }

            TripRecord::query()->toBase()->where('id', $trip->id)->update($candidate);
            unset($candidates[$date]);
        }

        foreach ($candidates as $candidate) {
            $count += TripRecord::query()->toBase()->insertOrIgnore($candidate + [
                'bus_id'          => null,
                'status'          => 'unassigned',
                'available_seats' => null,
                'created_at'      => now(),
            ]);
        }

        return $count;
    }

    private function validateAssignments(Collection $existing, array $candidates, object $route): void
    {
        $busIds = $existing->pluck('bus_id')->filter()->unique()->sort()->values();
        $buses = BusRecord::query()->toBase()->whereIn('id', $busIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        $busTrips = TripRecord::query()->toBase()->whereIn('bus_id', $busIds)->whereIn('status', ['scheduled', 'departed'])
            ->orderBy('id')->lockForUpdate()->get()->groupBy('bus_id');
        $changes = $existing->mapWithKeys(fn ($trip) => [$trip->id => $candidates[Carbon::parse($trip->departure_time)->toDateString()] ?? null]);
        $expectedType = ['Limousine' => 'limousine', 'Giường nằm' => 'sleeper', 'Ghế ngồi' => 'standard'][$route->vehicle_type] ?? null;
        foreach ($existing as $trip) {
            $candidate = $changes->get($trip->id);
            if (! $candidate || ! $trip->bus_id) {
                continue;
            }
            if ($expectedType && $buses->get($trip->bus_id)?->bus_type !== $expectedType) {
                throw ValidationException::withMessages(['schedule' => __('RouteManagement::app.vehicle_conflict')]);
            }
            foreach ($busTrips->get($trip->bus_id, collect()) as $other) {
                if ($other->id === $trip->id || ($changes->has($other->id) && $changes->get($other->id) === null)) {
                    continue;
                }
                $otherTimes = $changes->get($other->id) ?? (array) $other;
                if (Carbon::parse($otherTimes['departure_time'])->lt($candidate['arrival_time'])
                    && Carbon::parse($otherTimes['arrival_time'])->gt($candidate['departure_time'])) {
                    throw ValidationException::withMessages(['schedule' => __('RouteManagement::app.bus_conflict')]);
                }
            }
        }
    }

    public function generateAll(): int
    {
        $total = 0;
        foreach (TripScheduleRecord::query()->toBase()->where('status', 'active')->get() as $s) {
            $total += $this->generate($s);
        }

        return $total;
    }
}
