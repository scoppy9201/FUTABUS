<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Services;

use FuteBus\BusManagement\Models\Bus;
use Illuminate\Support\Facades\DB;

class BusPresentationService
{
    public const AMENITIES = ['blanket', 'water', 'wet_towel', 'charging', 'wifi'];

    public function update(int $id, array $data): void
    {
        DB::transaction(function () use ($id, $data): void {
            $bus = Bus::whereHas('busCompany', fn ($q) => $q->where('code', 'FUTA'))->lockForUpdate()->findOrFail($id);
            $amenities = array_values($data['amenities']);
            $wifi = in_array('wifi', $amenities, true);
            $bus->update([
                'amenities'     => $amenities,
                'wifi_name'     => $wifi ? ($data['wifi_name'] ?? null) : null,
                'wifi_password' => $wifi ? ($data['wifi_password'] ?? null) : null,
            ]);
        });
    }

    public static function publicData(Bus $bus): array
    {
        $amenities = array_values(array_intersect($bus->amenities ?? [], self::AMENITIES));

        return [
            'license_plate'    => $bus->license_plate,
            'brand'            => $bus->brand,
            'color'            => $bus->color,
            'manufacture_year' => $bus->manufacture_year,
            'vehicle_type'     => $bus->vehicleType?->name,
            'capacity'         => $bus->capacity,
            'capacity_unit'    => in_array($bus->vehicleType?->vehicle_class, ['sleeper', 'limousine_sleeper', 'vip_cabin'], true) ? 'beds' : 'seats',
            'floors'           => $bus->vehicleType?->floor_count,
            'amenities'        => $amenities,
            'wifi_name'        => in_array('wifi', $amenities, true) ? $bus->wifi_name : null,
            'wifi_password'    => in_array('wifi', $amenities, true) ? $bus->wifi_password : null,
        ];
    }
}
