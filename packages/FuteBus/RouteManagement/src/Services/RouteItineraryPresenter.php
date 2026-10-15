<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RouteItineraryPresenter
{
    public static function present(Collection $stops, Carbon $departure): array
    {
        $active = $stops->where('status', 'active');

        return $stops->map(function ($stop) use ($departure, $active): array {
            $time = $departure->copy()->addMinutes((int) $stop->offset_minutes);

            return [
                'id'             => $stop->id, 'name' => $stop->name, 'address' => $stop->address, 'type' => $stop->stop_type,
                'active'         => $stop->status === 'active', 'is_origin' => $stop->id === $active->first()?->id,
                'is_destination' => $stop->id === $active->last()?->id, 'hour' => $time->format('H:i'),
                'date'           => $time->format('d/m/Y'),
                'day_offset'     => (int) $departure->copy()->startOfDay()->diffInDays($time->copy()->startOfDay()),
                'expires_at'     => $time->getTimestamp() * 1000,
                'expired'        => $time->lessThanOrEqualTo(now()),
                'map_url'        => 'https://www.google.com/maps/search/?api=1&query='
                    .rawurlencode(implode(', ', array_filter([$stop->name, $stop->address, 'Việt Nam']))),
            ];
        })->values()->all();
    }
}
