<?php

declare(strict_types=1);

namespace FuteBus\BookingManagement\Services;

use FuteBus\RouteManagement\Models\RouteStop as RouteStopRecord;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BookingTripStops
{
    public function forTrip(array $trip): array
    {
        $stops = RouteStopRecord::query()->toBase()
            ->where('route_id', $trip['route_id'])
            ->where('status', 'active')
            ->orderBy('stop_order')
            ->get(['id', 'name', 'address', 'offset_minutes', 'stop_type']);
        $departure = Carbon::parse($trip['departure_time']);
        $options = [];

        foreach (['pickup', 'dropoff'] as $kind) {
            $options[$kind] = $stops
                ->filter(fn (object $stop): bool => in_array($stop->stop_type, [$kind, 'both'], true))
                ->map(function (object $stop) use ($departure): array {
                    $time = $departure->copy()->addMinutes((int) $stop->offset_minutes);

                    return [
                        'id'             => (string) $stop->id,
                        'name'           => $stop->name,
                        'address'        => $stop->address,
                        'time'           => $time->format('H:i d/m/Y'),
                        'boarding_time'  => $time->copy()->subMinutes(15)->toDateTimeString(),
                        'boarding_label' => $time->copy()->subMinutes(15)->format('H:i d/m/Y'),
                        'expires_at'     => $time->getTimestamp() * 1000,
                        'expired'        => $time->lessThanOrEqualTo(now()),
                    ];
                })->values()->all();

            if ($options[$kind] === []) {
                $time = $kind === 'pickup' ? $departure : Carbon::parse($trip['arrival_time']);
                $options[$kind][] = [
                    'id'             => $kind,
                    'name'           => $kind === 'pickup' ? $trip['origin'] : $trip['destination'],
                    'address'        => null,
                    'time'           => $time->format('H:i d/m/Y'),
                    'boarding_time'  => $time->copy()->subMinutes(15)->toDateTimeString(),
                    'boarding_label' => $time->copy()->subMinutes(15)->format('H:i d/m/Y'),
                    'expires_at'     => $time->getTimestamp() * 1000,
                    'expired'        => $time->lessThanOrEqualTo(now()),
                ];
            }
        }

        return $options;
    }

    public function selected(array $trip, string $kind, ?string $id, string $field): array
    {
        $options = $this->forTrip($trip)[$kind];
        $selected = collect($options)->firstWhere('id', $id)
            ?? ($id === null ? collect($options)->firstWhere('expired', false) : null);

        if ($selected === null || $selected['expired']) {
            throw ValidationException::withMessages([$field => __('core::booking.stop_unavailable')]);
        }

        return $selected;
    }
}
