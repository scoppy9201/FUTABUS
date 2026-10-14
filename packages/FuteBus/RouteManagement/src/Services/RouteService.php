<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Services;

use FuteBus\BookingManagement\Models\BookedSeat as BookedSeatRecord;
use FuteBus\BookingManagement\Models\Booking as BookingRecord;
use FuteBus\BusCompanyManagement\Models\BusCompany as BusCompanyRecord;
use FuteBus\Payment\Models\SePayPaymentIntent as SePayPaymentIntentRecord;
use FuteBus\RouteManagement\Events\RouteTimingChanged;
use FuteBus\RouteManagement\Models\BusRoute;
use FuteBus\RouteManagement\Models\RouteStop as RouteStopRecord;
use FuteBus\TicketManagement\Models\Ticket as TicketRecord;
use FuteBus\TripManagement\Models\Trip as TripRecord;
use FuteBus\TripManagement\Models\TripSchedule as TripScheduleRecord;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RouteService
{
    public function query(): Builder
    {
        return BusRoute::query()->forFuta()->toBase();
    }

    public function find(int $id, bool $lock = false): object
    {
        $route = BusRoute::query()->forFuta()->whereKey($id)->when($lock, fn ($query) => $query->lockForUpdate())->first();
        abort_unless($route, 404);

        return $route;
    }

    public function save(array $data, ?int $id = null): int
    {
        return DB::transaction(function () use ($data, $id) {
            if ($id !== null) {
                $route = $this->find($id, true);
                $structural = collect(['origin_city', 'destination_city', 'vehicle_type'])
                    ->contains(fn ($key) => $data[$key] !== $route->{$key});
                if ($structural) {
                    $this->assertEditable($id);
                }
                if ($data['vehicle_type'] !== $route->vehicle_type) {
                    $type = ['Limousine' => 'limousine', 'Giường nằm' => 'sleeper', 'Ghế ngồi' => 'standard'][$data['vehicle_type']];
                    if (TripRecord::query()->toBase()->join('buses', 'buses.id', '=', 'trips.bus_id')->where('trips.route_id', $id)
                        ->where('trips.departure_time', '>', now())->where('buses.bus_type', '<>', $type)->exists()) {
                        throw ValidationException::withMessages(['vehicle_type' => __('RouteManagement::app.vehicle_conflict')]);
                    }
                }
                if (! $data['is_active']) {
                    $this->assertUnused($id, false);
                }
            }
            $payload = $data + ['name' => $data['origin_city'].' - '.$data['destination_city'], 'updated_at' => now()];
            $payload['is_public_schedule'] = (bool) $data['is_active']
                && $id !== null && RouteStopRecord::query()->toBase()->where('route_id', $id)->where('status', 'active')->count() >= 2;
            if ($id !== null) {
                $this->query()->where('id', $id)->update($payload);

                return $id;
            }

            $companyId = BusCompanyRecord::query()->toBase()->where('code', 'FUTA')->value('id');
            abort_unless($companyId, 404);

            return (int) BusRoute::query()->create(collect($payload)->except('updated_at')->all() + [
                'bus_company_id' => $companyId, 'duration_minutes' => 0,
            ])->getKey();
        }, 3);
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->find($id, true);
            $this->assertUnused($id, true);
            $this->query()->where('id', $id)->delete();
        }, 3);
    }

    public function assertEditable(int $id): void
    {
        $tripIds = TripRecord::query()->toBase()->where('route_id', $id)->orderBy('id')->lockForUpdate()->pluck('id');
        $referenced = BookingRecord::query()->toBase()->whereIn('trip_id', $tripIds)->exists()
            || TicketRecord::query()->toBase()->whereIn('trip_id', $tripIds)->exists()
            || BookedSeatRecord::query()->toBase()->whereIn('trip_id', $tripIds)->exists()
            || SePayPaymentIntentRecord::query()->toBase()->where(fn ($q) => $q->whereIn('trip_id', $tripIds)
                ->orWhereIn('secondary_trip_id', $tripIds))->exists()
            || TripRecord::query()->toBase()->whereIn('id', $tripIds)->where('departure_time', '<=', now())->exists();
        if ($referenced) {
            throw ValidationException::withMessages(['route' => __('RouteManagement::app.locked')]);
        }
    }

    private function assertUnused(int $id, bool $deleting): void
    {
        $schedules = TripScheduleRecord::query()->toBase()->where('route_id', $id);
        $trips = TripRecord::query()->toBase()->where('route_id', $id);
        if (! $deleting) {
            $schedules->where('status', 'active');
            $trips->where('departure_time', '>', now())->whereIn('status', ['scheduled', 'unassigned', 'departed']);
        }
        if ($schedules->exists() || $trips->exists()) {
            throw ValidationException::withMessages(['route' => __('RouteManagement::app.in_use')]);
        }
    }

    public function saveStop(int $routeId, array $data, ?int $stopId = null): void
    {
        DB::transaction(function () use ($routeId, $data, $stopId) {
            $this->find($routeId, true);
            $this->assertEditable($routeId);
            $firstId = RouteStopRecord::query()->where('route_id', $routeId)->where('status', 'active')
                ->orderBy('stop_order')->orderBy('id')->value('id');
            $ids = RouteStopRecord::query()->toBase()->where('route_id', $routeId)->where('status', 'active')
                ->when($stopId, fn ($q) => $q->where('id', '<>', $stopId))->orderBy('stop_order')->orderBy('id')->pluck('id')->all();
            $position = (int) $data['position'];
            if ($position > count($ids) + 1) {
                throw ValidationException::withMessages(['position' => __('RouteManagement::app.position_rule')]);
            }
            if (($position === 1 && (int) $data['leg_minutes'] !== 0) || ($position > 1 && (int) $data['leg_minutes'] === 0)) {
                throw ValidationException::withMessages(['leg_minutes' => __('RouteManagement::app.leg_rule')]);
            }
            if ($stopId !== null) {
                $this->stop($routeId, $stopId);
            }
            if ($position === 1 && $firstId !== null && (int) $firstId !== $stopId) {
                if (empty($data['next_leg_minutes'])) {
                    throw ValidationException::withMessages(['next_leg_minutes' => __('RouteManagement::app.next_leg_hint')]);
                }
                RouteStopRecord::query()->whereKey($firstId)->update(['leg_minutes' => $data['next_leg_minutes']]);
            }
            unset($data['position'], $data['next_leg_minutes']);
            $payload = $data + ['updated_at' => now()];
            if ($stopId === null) {
                $stopId = (int) RouteStopRecord::query()->create(collect($payload)->except('updated_at')->all() + [
                    'route_id' => $routeId, 'stop_order' => $position, 'status' => 'active',
                ])->getKey();
            } else {
                RouteStopRecord::query()->toBase()->where('id', $stopId)->update($payload);
            }
            array_splice($ids, $position - 1, 0, [$stopId]);
            $this->recalculate($routeId, $ids);
        }, 3);
    }

    public function stop(int $routeId, int $id): object
    {
        $stop = RouteStopRecord::query()->where('route_id', $routeId)->where('status', 'active')->find($id);
        abort_unless($stop, 404);

        return $stop;
    }

    public function deleteStop(int $routeId, int $id): void
    {
        DB::transaction(function () use ($routeId, $id) {
            $this->find($routeId, true);
            $this->stop($routeId, $id);
            $this->assertEditable($routeId);
            $ids = RouteStopRecord::query()->toBase()->where('route_id', $routeId)->where('status', 'active')
                ->where('id', '<>', $id)->orderBy('stop_order')->orderBy('id')->pluck('id')->all();
            if (count($ids) < 2 && (TripScheduleRecord::query()->toBase()->where('route_id', $routeId)->where('status', 'active')->exists()
                || TripRecord::query()->toBase()->where('route_id', $routeId)->where('departure_time', '>', now())->exists())) {
                throw ValidationException::withMessages(['stop' => __('RouteManagement::app.minimum_stops')]);
            }
            RouteStopRecord::query()->toBase()->where('id', $id)->delete();
            $this->recalculate($routeId, $ids);
        }, 3);
    }

    private function recalculate(int $routeId, array $ids): void
    {
        $offset = 0;
        foreach ($ids as $index => $id) {
            $stop = $this->stop($routeId, (int) $id);
            $leg = $index === 0 ? 0 : (int) $stop->leg_minutes;
            if ($index > 0 && $leg < 1) {
                throw ValidationException::withMessages(['leg_minutes' => __('RouteManagement::app.leg_rule')]);
            }
            $offset += $leg;
            if (($index === 0 && $stop->stop_type === 'dropoff')
                || (count($ids) > 1 && $index === count($ids) - 1 && $stop->stop_type === 'pickup')) {
                throw ValidationException::withMessages(['stop_type' => __('RouteManagement::app.endpoint_rule')]);
            }
            RouteStopRecord::query()->toBase()->where('id', $id)->update([
                'stop_order' => $index + 1, 'leg_minutes' => $leg, 'offset_minutes' => $offset, 'updated_at' => now(),
            ]);
        }
        $route = $this->find($routeId);
        $this->query()->where('id', $routeId)->update([
            'duration_minutes'    => $offset,
            'origin_station'      => $ids ? $this->stop($routeId, (int) $ids[0])->name : null,
            'destination_station' => count($ids) >= 2 ? $this->stop($routeId, (int) end($ids))->name : null,
            'is_public_schedule'  => $route->is_active && count($ids) >= 2, 'updated_at' => now(),
        ]);
        event(new RouteTimingChanged($routeId));
    }
}
