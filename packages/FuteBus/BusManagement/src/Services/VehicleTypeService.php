<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VehicleTypeService
{
    public function paginate(string $search = ''): LengthAwarePaginator
    {
        return DB::table('vehicle_types')
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->orderBy('name')->paginate(10)->withQueryString();
    }

    public function rules(?int $id = null): array
    {
        return [
            'name'             => ['required', 'string', 'max:100', Rule::unique('vehicle_types', 'name')->ignore($id)],
            'description'      => ['nullable', 'string', 'max:500'],
            'default_capacity' => ['required', 'integer', 'min:1', 'max:255'],
        ];
    }

    public function create(array $data): int
    {
        return DB::table('vehicle_types')->insertGetId([
            ...$this->attributes($data),
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function update(int $id, array $data): void
    {
        abort_unless(DB::table('vehicle_types')->where('id', $id)->exists(), 404);
        DB::table('vehicle_types')->where('id', $id)->update([
            ...$this->attributes($data),
            'updated_at' => now(),
        ]);
    }

    public function remove(int $id): bool
    {
        abort_unless(DB::table('vehicle_types')->where('id', $id)->exists(), 404);
        if (DB::table('buses')->where('vehicle_type_id', $id)->exists()) {
            DB::table('vehicle_types')->where('id', $id)
                ->update(['status' => 'inactive', 'updated_at' => now()]);

            return false;
        }

        DB::table('vehicle_types')->where('id', $id)->delete();

        return true;
    }

    private function attributes(array $data): array
    {
        return [
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'default_capacity' => $data['default_capacity'],
        ];
    }
}
