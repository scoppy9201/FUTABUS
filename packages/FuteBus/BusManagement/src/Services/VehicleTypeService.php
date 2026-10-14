<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Services;

use FuteBus\BusManagement\Models\Bus as BusRecord;
use FuteBus\BusManagement\Models\VehicleType as VehicleTypeRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VehicleTypeService
{
    /** Nhóm dòng xe cố định. */
    public const CLASSES = [
        'seater'            => 'seater',
        'sleeper'           => 'sleeper',
        'limousine'         => 'limousine',
        'limousine_sleeper' => 'limousine_sleeper',
        'vip_cabin'         => 'vip_cabin',
    ];

    /** Giới hạn sức chứa theo dòng xe. */
    private const CAPACITY_RANGES = [
        'seater'            => ['min' => 8,  'max' => 50],
        'sleeper'           => ['min' => 20, 'max' => 46],
        'limousine'         => ['min' => 8,  'max' => 22],
        'limousine_sleeper' => ['min' => 20, 'max' => 46],
        'vip_cabin'         => ['min' => 20, 'max' => 24],
    ];

    /**
     * Số tầng tối đa theo dòng xe.
     * seater và limousine chỉ được 1 tầng.
     */
    private const FLOOR_LIMITS = [
        'seater'            => 1,
        'limousine'         => 1,
        'sleeper'           => 2,
        'limousine_sleeper' => 2,
        'vip_cabin'         => 2,
    ];

    public function paginate(string $search = '', string $vehicleClass = '', string $status = ''): LengthAwarePaginator
    {
        return VehicleTypeRecord::query()->toBase()
            ->selectRaw('vehicle_types.*,
                (select count(*) from buses where buses.vehicle_type_id = vehicle_types.id) as bus_count')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%$search%"))
            ->when($vehicleClass !== '', fn ($q) => $q->where('vehicle_class', $vehicleClass))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderBy('name')->paginate(15)->withQueryString();
    }

    public function rules(?int $id = null): array
    {
        return [
            'name'             => [
                'required', 'string', 'min:5', 'max:100',
                'regex:/^[^<>\/@#$%\^*]+$/',
                Rule::unique('vehicle_types', 'name')->ignore($id),
            ],
            'description'      => ['nullable', 'string', 'max:500'],
            'vehicle_class'    => ['nullable', Rule::in(array_keys(self::CLASSES))],
            'floor_count'      => ['nullable', 'integer', Rule::in([1, 2])],
            'seat_rows'        => ['nullable', 'integer', 'min:1', 'max:30'],
            'seat_columns'     => ['nullable', 'integer', 'min:1', 'max:10'],
            'unused_seats'     => ['nullable', 'integer', 'min:0', 'max:255'],
            'default_capacity' => ['required', 'integer', 'min:1', 'max:255'],
        ];
    }

    /** Validate capacity range theo dòng xe. */
    public function assertCapacityRange(string $vehicleClass, int $capacity): void
    {
        $range = self::CAPACITY_RANGES[$vehicleClass] ?? null;
        if ($range && ($capacity < $range['min'] || $capacity > $range['max'])) {
            throw ValidationException::withMessages([
                'default_capacity' => __('BusManagement::app.vt_err_capacity_range', [
                    'class' => __('BusManagement::app.vt_class_'.$vehicleClass),
                    'min'   => $range['min'], 'max' => $range['max'],
                    'unit'  => __('BusManagement::app.vt_unit_'.$vehicleClass),
                ]),
            ]);
        }
    }

    /**
     * Kiểm tra sức chứa khớp công thức: Hàng × Cột × Tầng − Không dùng.
     * Chỉ áp dụng khi cả 3 trường sơ đồ đều được cung cấp.
     */
    public function assertCapacityFormula(array $data): void
    {
        $rows = isset($data['seat_rows']) && $data['seat_rows'] !== '' ? (int) $data['seat_rows'] : null;
        $cols = isset($data['seat_columns']) && $data['seat_columns'] !== '' ? (int) $data['seat_columns'] : null;
        $floors = (int) ($data['floor_count'] ?? 1);
        $unused = (int) ($data['unused_seats'] ?? 0);
        $capacity = (int) $data['default_capacity'];

        if ($rows === null || $cols === null) {
            if ($unused > 0) {
                throw ValidationException::withMessages([
                    'unused_seats' => __('BusManagement::app.vt_err_rows_columns'),
                ]);
            }

            return; // Không bắt buộc nhập sơ đồ
        }

        $expected = $rows * $cols * $floors - $unused;

        if ($unused >= $rows * $cols * $floors) {
            throw ValidationException::withMessages([
                'unused_seats' => __('BusManagement::app.vt_err_unused_seats', ['total' => $rows * $cols * $floors]),
            ]);
        }

        if ($capacity !== $expected) {
            throw ValidationException::withMessages([
                'default_capacity' => __('BusManagement::app.vt_err_capacity_formula', compact('rows', 'cols', 'floors', 'unused', 'expected', 'capacity')),
            ]);
        }
    }

    /**
     * Kiểm tra số tầng hợp lệ theo dòng xe.
     */
    public function assertFloorLimit(string $vehicleClass, int $floorCount): void
    {
        $max = self::FLOOR_LIMITS[$vehicleClass] ?? 2;
        if ($floorCount > $max) {
            throw ValidationException::withMessages([
                'floor_count' => __('BusManagement::app.vt_err_floor_limit', ['class' => __('BusManagement::app.vt_class_'.$vehicleClass), 'max' => $max]),
            ]);
        }
    }

    public function create(array $data): int
    {
        $vehicleClass = $data['vehicle_class'] ?? null;
        $floorCount = (int) ($data['floor_count'] ?? 1);

        if (empty($vehicleClass)) {
            throw ValidationException::withMessages(['vehicle_class' => __('BusManagement::app.vt_err_class_required')]);
        }

        if (! empty($vehicleClass)) {
            $this->assertFloorLimit($vehicleClass, $floorCount);
            $this->assertCapacityRange($vehicleClass, (int) $data['default_capacity']);
        }

        $this->assertCapacityFormula($data);

        return VehicleTypeRecord::query()->toBase()->insertGetId([
            ...$this->attributes($data),
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function update(int $id, array $data): void
    {
        $type = VehicleTypeRecord::query()->toBase()->where('id', $id)->first();
        abort_unless($type, 404);

        $isInUse = (int) BusRecord::query()->toBase()->where('vehicle_type_id', $id)->count() > 0;

        // Nếu đã gán xe: chỉ cho sửa name và description
        if ($isInUse) {
            VehicleTypeRecord::query()->toBase()->where('id', $id)->update([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'updated_at'  => now(),
            ]);

            return;
        }

        $vehicleClass = $data['vehicle_class'] ?? null;
        $floorCount = (int) ($data['floor_count'] ?? 1);

        if (empty($vehicleClass)) {
            throw ValidationException::withMessages(['vehicle_class' => __('BusManagement::app.vt_err_class_required')]);
        }

        if (! empty($vehicleClass)) {
            $this->assertFloorLimit($vehicleClass, $floorCount);
            $this->assertCapacityRange($vehicleClass, (int) $data['default_capacity']);
        }

        $this->assertCapacityFormula($data);

        VehicleTypeRecord::query()->toBase()->where('id', $id)->update([
            ...$this->attributes($data),
            'updated_at' => now(),
        ]);
    }

    /** Xóa hoàn toàn nếu chưa gán xe; ngừng sử dụng nếu đã gán. */
    public function remove(int $id): bool
    {
        abort_unless(VehicleTypeRecord::query()->toBase()->where('id', $id)->exists(), 404);

        if (BusRecord::query()->toBase()->where('vehicle_type_id', $id)->exists()) {
            VehicleTypeRecord::query()->toBase()->where('id', $id)
                ->update(['status' => 'inactive', 'updated_at' => now()]);

            return false; // Không cho xóa cứng, chuyển sang ngừng sử dụng
        }

        VehicleTypeRecord::query()->toBase()->where('id', $id)->delete();

        return true;
    }

    /** Đổi trạng thái active <-> inactive. */
    public function toggle(int $id): string
    {
        $type = VehicleTypeRecord::query()->toBase()->where('id', $id)->first();
        abort_unless($type, 404);

        $newStatus = $type->status === 'active' ? 'inactive' : 'active';
        VehicleTypeRecord::query()->toBase()->where('id', $id)->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        return $newStatus;
    }

    private function attributes(array $data): array
    {
        return [
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'vehicle_class'    => $data['vehicle_class'] ?? null,
            'floor_count'      => (int) ($data['floor_count'] ?? 1),
            'seat_rows'        => isset($data['seat_rows']) && $data['seat_rows'] !== '' ? (int) $data['seat_rows'] : null,
            'seat_columns'     => isset($data['seat_columns']) && $data['seat_columns'] !== '' ? (int) $data['seat_columns'] : null,
            'unused_seats'     => (int) ($data['unused_seats'] ?? 0),
            'default_capacity' => (int) $data['default_capacity'],
        ];
    }
}
