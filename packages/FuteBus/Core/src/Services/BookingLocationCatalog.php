<?php

declare(strict_types=1);

namespace FuteBus\Core\Services;

use FuteBus\Core\Models\BranchOffice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingLocationCatalog
{
    public function all(): array
    {
        $catalog = json_decode(
            file_get_contents(app_path('Data/futa_booking_locations.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        // Active departures extend the operator-supplied examples without inventing stops.
        $routes = DB::table('routes')
            ->join('trips', 'trips.route_id', '=', 'routes.id')
            ->join('bus_companies', 'bus_companies.id', '=', 'routes.bus_company_id')
            ->where('bus_companies.code', 'FUTA')
            ->where('routes.is_active', true)
            ->where('trips.status', 'scheduled')
            ->where('trips.departure_time', '>=', now())
            ->select('routes.origin_city', 'routes.destination_city', 'routes.origin_station', 'routes.destination_station')
            ->distinct()
            ->get();

        foreach ($routes as $route) {
            foreach (['departure' => ['origin_city', 'origin_station'], 'destination' => ['destination_city', 'destination_station']] as $side => [$cityField, $stationField]) {
                $city = trim((string) $route->{$cityField});
                $station = trim((string) ($route->{$stationField} ?? ''));

                if ($city !== '' && ! collect($catalog[$side]['provinces'])->contains(
                    fn (string $known): bool => $this->normalize($known) === $this->normalize($city),
                )) {
                    $catalog[$side]['provinces'][] = $city;
                }

                if ($station === '' || $station === $city) {
                    continue;
                }

                $known = collect($catalog[$side]['areas'])->contains(function (array $area) use ($station): bool {
                    return $area['name'] === $station
                        || collect($area['offices'])->contains(fn (array $office): bool => $office['name'] === $station);
                });

                if (! $known) {
                    $catalog[$side]['areas'][] = [
                        'name' => $station,
                        'province' => $city,
                        'kind' => 'station',
                        'office_count' => 0,
                        'offices' => [],
                    ];
                }
            }
        }

        $knownOfficeNames = collect($catalog)
            ->flatMap(fn (array $side) => $side['areas'])
            ->flatMap(fn (array $area) => $area['offices'])
            ->pluck('name')
            ->map(fn (string $name) => mb_strtolower($name))
            ->all();

        $directoryOffices = BranchOffice::active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (BranchOffice $office) => [
                'name' => $office->localized('name'),
                'address' => $office->localized('address'),
            ])
            ->filter(fn (array $office) => $office['name'] !== ''
                && ! in_array(mb_strtolower($office['name']), $knownOfficeNames, true))
            ->unique(fn (array $office) => mb_strtolower($office['name']).'|'.mb_strtolower($office['address']))
            ->values()
            ->all();

        $catalog['departure']['directory_offices'] = $directoryOffices;
        $catalog['destination']['directory_offices'] = $directoryOffices;

        return $catalog;
    }

    private function normalize(string $value): string
    {
        $value = strtolower(Str::ascii($value));
        $value = preg_replace('/^(tp|thanh pho|tinh)\.?\s+/i', '', $value) ?? $value;

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value);
    }
}
