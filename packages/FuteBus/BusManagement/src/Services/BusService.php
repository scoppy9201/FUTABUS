<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BusService
{
    public function paginate(string $search = ''): LengthAwarePaginator
    {
        return DB::table('buses')
            ->leftJoin('vehicle_types', 'vehicle_types.id', '=', 'buses.vehicle_type_id')
            ->where('buses.bus_company_id', $this->futaCompanyId())
            ->select('buses.*', 'vehicle_types.name as vehicle_type_name')
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                $builder->where('buses.license_plate', 'like', '%'.$search.'%')
                    ->orWhere('buses.chassis_number', 'like', '%'.$search.'%')
                    ->orWhere('buses.brand', 'like', '%'.$search.'%');
            }))
            ->orderBy('buses.license_plate')->paginate(10)->withQueryString();
    }

    public function rules(?int $id = null): array
    {
        return [
            'license_plate'    => ['required', 'string', 'max:20', Rule::unique('buses', 'license_plate')->ignore($id)],
            'chassis_number'   => ['required', 'string', 'max:50', Rule::unique('buses', 'chassis_number')->ignore($id)],
            'color'            => ['required', 'string', 'max:50'],
            'brand'            => ['required', 'string', 'max:100'],
            'manufacture_year' => ['required', 'integer', 'min:1990', 'max:'.((int) date('Y') + 1)],
            'vehicle_type_id'  => ['required', Rule::exists('vehicle_types', 'id')->where('status', 'active')],
            'seat_rows'        => ['required', 'integer', 'min:1', 'max:30'],
            'seat_columns'     => ['required', 'integer', 'min:1', 'max:10'],
            'description'      => ['nullable', 'string', 'max:500'],
            ...($id !== null ? ['status' => ['required', Rule::in(['active', 'inactive'])]] : []),
        ];
    }

    public function create(array $data): int
    {
        $companyId = $this->futaCompanyId();

        return DB::table('buses')->insertGetId([
            ...$this->payload($data),
            'bus_company_id' => $companyId,
            'status'         => 'active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function update(int $id, array $data): void
    {
        $this->find($id);
        $this->assertNotAssignedToActiveTrip($id);
        DB::table('buses')->where('id', $id)->update([
            ...$this->payload($data),
            'status'     => $data['status'],
            'updated_at' => now(),
        ]);
    }

    public function deactivate(int $id): void
    {
        $this->find($id);
        $this->assertNotAssignedToActiveTrip($id);
        DB::table('buses')->where('id', $id)
            ->update(['status' => 'inactive', 'updated_at' => now()]);
    }

    public function find(int $id): object
    {
        $bus = DB::table('buses')->where('id', $id)
            ->where('bus_company_id', $this->futaCompanyId())->first();
        abort_if($bus === null, 404);

        return $bus;
    }

    private function assertNotAssignedToActiveTrip(int $id): void
    {
        if (Schema::hasTable('trips') && DB::table('trips')->where('bus_id', $id)
            ->whereIn('status', ['scheduled', 'departed'])->exists()) {
            throw ValidationException::withMessages(['bus' => __('BusManagement::app.bus_err_active_trip')]);
        }
    }

    private function payload(array $data): array
    {
        $plate = strtoupper(trim($data['license_plate']));

        return [
            'license_plate'    => $plate,
            'chassis_number'   => strtoupper(trim($data['chassis_number'])),
            'color'            => $data['color'],
            'brand'            => $data['brand'],
            'manufacture_year' => $data['manufacture_year'],
            'vehicle_type_id'  => $data['vehicle_type_id'],
            'seat_rows'        => $data['seat_rows'],
            'seat_columns'     => $data['seat_columns'],
            'capacity'         => $data['seat_rows'] * $data['seat_columns'],
            'name'             => $data['brand'].' '.$plate,
            'description'      => $data['description'] ?? null,
        ];
    }

    private function futaCompanyId(): int
    {
        $id = DB::table('bus_companies')->where('code', 'FUTA')->value('id');
        abort_if($id === null, 503);

        return (int) $id;
    }
}
