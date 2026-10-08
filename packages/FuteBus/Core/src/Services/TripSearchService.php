<?php

declare(strict_types=1);

namespace FuteBus\Core\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class TripSearchService
{
    public function __construct(private readonly BookingLocationCatalog $locations) {}

    public function search(string $from, string $to, string $date, int $quantity): array
    {
        $trips = DB::table('trips')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('buses', 'buses.id', '=', 'trips.bus_id')
            ->join('bus_companies', 'bus_companies.id', '=', 'trips.bus_company_id')
            ->where('bus_companies.code', 'FUTA')
            ->where('routes.is_active', true)
            ->where('trips.status', 'scheduled')
            ->whereDate('trips.departure_time', $date)
            ->where('trips.departure_time', '>=', now())
            ->orderBy('trips.departure_time')
            ->select(
                'trips.id', 'trips.departure_time', 'trips.arrival_time', 'trips.price', 'trips.available_seats',
                'routes.origin_city', 'routes.origin_station', 'routes.destination_city', 'routes.destination_station',
                'routes.distance_km', 'routes.code as route_code', 'buses.id as bus_id', 'buses.capacity', 'buses.bus_type', 'buses.seat_rows',
            )
            ->get();

        $bookedSeats = DB::table('booked_seats')
            ->join('bookings', 'bookings.id', '=', 'booked_seats.booking_id')
            ->whereIn('bookings.status', ['pending', 'confirmed', 'completed'])
            ->whereIn('booked_seats.trip_id', $trips->pluck('id'))
            ->select('booked_seats.trip_id', 'booked_seats.seat_layout_id')
            ->get()
            ->groupBy('trip_id');

        $seatLayouts = DB::table('seat_layouts')
            ->whereIn('bus_id', $trips->pluck('bus_id'))
            ->orderBy('row_number')
            ->orderBy('column_number')
            ->select('id', 'bus_id', 'seat_code', 'row_number', 'column_number', 'deck', 'is_available')
            ->get()
            ->groupBy('bus_id');

        $catalog = $this->locations->all();
        $allLocations = [
            'areas' => array_merge($catalog['departure']['areas'], $catalog['destination']['areas']),
            'directory_offices' => $catalog['departure']['directory_offices'],
        ];
        $fromLocation = $this->describeLocation($from, $allLocations);
        $toLocation = $this->describeLocation($to, $allLocations);

        return $trips
            ->filter(function ($trip) use ($fromLocation, $toLocation, $quantity, $bookedSeats): bool {
                $remaining = max(0, (int) $trip->capacity - count($bookedSeats[$trip->id] ?? []));
                $available = $trip->available_seats === null
                    ? $remaining
                    : min((int) $trip->available_seats, $remaining);

                return $available >= $quantity
                    && $this->matchesLocation($fromLocation, $trip->origin_city, $trip->origin_station)
                    && $this->matchesLocation($toLocation, $trip->destination_city, $trip->destination_station);
            })
            ->map(function ($trip) use ($bookedSeats, $seatLayouts): array {
                $occupied = collect($bookedSeats[$trip->id] ?? [])->pluck('seat_layout_id')->all();
                $remaining = max(0, (int) $trip->capacity - count($occupied));
                $departure = Carbon::parse($trip->departure_time);
                $arrival = Carbon::parse($trip->arrival_time);
                $layouts = collect($seatLayouts[$trip->bus_id] ?? []);
                $availableLayouts = $layouts->filter(fn ($seat) => $seat->is_available
                    && ! in_array($seat->id, $occupied, true));
                $isDemo = str_starts_with($trip->route_code, 'DEMO-');
                $demoLayouts = $layouts->take(34)->values();
                $demoSelectedIds = $isDemo
                    ? $demoLayouts->only([2, 6])->pluck('id')->values()->all()
                    : [];
                $seats = $isDemo
                    ? $demoLayouts->map(function ($seat, int $index) use ($occupied): array {
                        $number = ($index % 17) + 1;
                        return [
                            'id' => (int) $seat->id,
                            'code' => ($index < 17 ? 'A' : 'B').str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                            'row' => $number <= 2 ? 1 : intdiv($number - 3, 3) + 2,
                            'column' => $number === 1 ? 1 : ($number === 2 ? 3 : (($number - 3) % 3) + 1),
                            'deck' => $index < 17 ? 'lower' : 'upper',
                            'sold' => ! (bool) $seat->is_available
                                || in_array($seat->id, $occupied, true)
                                || ($index < 17 && $number <= 2)
                                || ($index >= 17 && $number === 2),
                        ];
                    })->all()
                    : $layouts->map(fn ($seat): array => [
                        'id' => (int) $seat->id,
                        'code' => $seat->seat_code,
                        'row' => (int) $seat->row_number,
                        'column' => (int) $seat->column_number,
                        'deck' => $seat->deck,
                        'sold' => ! (bool) $seat->is_available || in_array($seat->id, $occupied, true),
                    ])->values()->all();
                $rowOptions = $availableLayouts->map(function ($seat) use ($trip): string {
                    $rowCount = max(1, (int) $trip->seat_rows);
                    if ($seat->row_number <= (int) ceil($rowCount / 3)) return 'front';
                    if ($seat->row_number > (int) floor($rowCount * 2 / 3)) return 'back';

                    return 'middle';
                })->unique()->values()->all();

                return [
                    'id' => (int) $trip->id,
                    'departure_time' => $trip->departure_time,
                    'arrival_time' => $trip->arrival_time,
                    'departure_hour' => $departure->format('H:i'),
                    'arrival_hour' => $arrival->format('H:i'),
                    'duration_minutes' => $departure->diffInMinutes($arrival),
                    'origin' => $trip->origin_station ?: $trip->origin_city,
                    'destination' => $trip->destination_station ?: $trip->destination_city,
                    'distance_km' => $trip->distance_km,
                    'vehicle_type' => $trip->bus_type,
                    'available_seats' => $trip->available_seats === null ? $remaining : min((int) $trip->available_seats, $remaining),
                    'row_options' => $rowOptions,
                    'deck_options' => $availableLayouts->pluck('deck')->unique()->values()->all(),
                    'seat_decks' => collect($seats)->pluck('deck')->unique()->values()->all(),
                    'demo_seat_map' => $isDemo,
                    'seats' => $seats,
                    'demo_selected_seat_ids' => $demoSelectedIds,
                    'price' => (int) $trip->price,
                ];
            })
            ->values()
            ->all();
    }

    private function describeLocation(string $selected, array $side): array
    {
        foreach ($side['directory_offices'] ?? [] as $office) {
            if ($this->normalize($selected) === $this->normalize($office['name'])) {
                return ['kind' => 'office', 'names' => [$selected]];
            }
        }

        foreach ($side['areas'] as $area) {
            foreach ($area['offices'] as $office) {
                if ($this->normalize($selected) === $this->normalize($office['name'])) {
                    return ['kind' => 'office', 'names' => [$selected]];
                }
            }
        }

        $names = [$selected];
        foreach ($side['areas'] as $area) {
            if ($this->normalize($selected) === $this->normalize($area['province'])) {
                $names[] = $area['name'];
            } elseif ($this->normalize($selected) === $this->normalize($area['name'])) {
                if (($area['kind'] ?? null) === 'station') {
                    return ['kind' => 'office', 'names' => [$selected]];
                }
                return ['kind' => 'specific', 'names' => [$this->normalize($selected)]];
            }
        }

        return ['kind' => 'area', 'names' => array_unique(array_map($this->normalize(...), $names))];
    }

    private function matchesLocation(array $selected, string $city, ?string $station): bool
    {
        if ($selected['kind'] === 'office') {
            if (! $station) {
                return false;
            }

            $wanted = $this->normalize($selected['names'][0]);
            $actual = $this->normalize($station);

            return $wanted === $actual || str_contains($wanted, $actual) || str_contains($actual, $wanted);
        }

        if ($selected['kind'] === 'specific') {
            $wanted = $selected['names'][0];

            return $this->normalize($city) === $wanted
                || ($station && $this->normalize($station) === $wanted);
        }

        $actual = $this->normalize($city);

        return in_array($actual, $selected['names'], true);
    }

    private function normalize(string $value): string
    {
        $ascii = strtolower(Str::ascii($value));
        $ascii = preg_replace('/^(tp|thanh pho|tinh|bx|ben xe)\.?\s+/i', '', $ascii) ?? $ascii;

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $ascii) ?? $ascii);
    }
}
