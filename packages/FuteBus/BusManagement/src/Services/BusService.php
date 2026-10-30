<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Services;

use FuteBus\BookingManagement\Models\BookedSeat as BookedSeatRecord;
use FuteBus\BookingManagement\Models\Booking as BookingRecord;
use FuteBus\BusCompanyManagement\Models\BusCompany as BusCompanyRecord;
use FuteBus\BusManagement\Models\Bus as BusRecord;
use FuteBus\BusManagement\Models\VehicleDocument as VehicleDocumentRecord;
use FuteBus\BusManagement\Models\VehicleType as VehicleTypeRecord;
use FuteBus\Payment\Models\SePayPaymentIntent as SePayPaymentIntentRecord;
use FuteBus\SeatManagement\Models\SeatLayout as SeatLayoutRecord;
use FuteBus\TicketManagement\Models\Ticket as TicketRecord;
use FuteBus\TripManagement\Models\Trip as TripRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BusService
{
    public const COLORS = ['white', 'black', 'silver', 'gray', 'red', 'blue', 'green', 'yellow', 'orange', 'other'];

    /**
     * Regex biển số hợp lệ:
     *  Biển 5 số mới: 51B-123.45
     *  Biển 4 số cũ: 29B-6973
     */
    private const PLATE_REGEX = '/^([0-9]{2}[A-Z]{1,2}-[0-9]{3}\.[0-9]{2}|[0-9]{2}[A-Z]-[0-9]{4})$/';

    /** Ký tự không hợp lệ trong VIN. */
    private const VIN_FORBIDDEN = '/[IOQ]/i';

    /** Regex số máy: 6–20 ký tự chữ hoa và số. */
    private const ENGINE_REGEX = '/^[A-Z0-9]{6,20}$/';

    public function paginate(string $search = '', string $typeId = '', string $status = ''): LengthAwarePaginator
    {
        return BusRecord::query()->toBase()
            ->leftJoin('vehicle_types', 'vehicle_types.id', '=', 'buses.vehicle_type_id')
            ->where('buses.bus_company_id', $this->futaCompanyId())
            ->select(
                'buses.*',
                'vehicle_types.name as vehicle_type_name',
                'vehicle_types.default_capacity as vt_capacity',
                'vehicle_types.vehicle_class as vehicle_class',
            )
            ->when($search !== '', fn ($q) => $q->where(function ($b) use ($search): void {
                $b->where('buses.license_plate', 'like', "%$search%")
                    ->orWhere('buses.chassis_number', 'like', "%$search%")
                    ->orWhere('buses.engine_number', 'like', "%$search%")
                    ->orWhere('buses.brand', 'like', "%$search%");
            }))
            ->when($typeId !== '', fn ($q) => $q->where('buses.vehicle_type_id', $typeId))
            ->when($status !== '', fn ($q) => $q->where('buses.status', $status))
            ->orderBy('buses.license_plate')->paginate(15)->withQueryString();
    }

    public function rules(?int $id = null): array
    {
        return [
            'license_plate'    => [
                'required', 'string', 'max:20',
                'regex:'.self::PLATE_REGEX,
                Rule::unique('buses', 'license_plate')->ignore($id),
            ],
            'chassis_number'   => [
                'required', 'string', 'min:10', 'max:17',
                // Loại trừ ký tự I, O, Q (VIN chuẩn)
                'regex:/^[A-HJ-NPR-Z0-9]{10,17}$/',
                Rule::unique('buses', 'chassis_number')->ignore($id),
            ],
            'engine_number'    => [
                'required', 'string',
                'regex:'.self::ENGINE_REGEX,
            ],
            'color'            => ['required', Rule::in(self::COLORS)],
            'brand'            => ['required', 'string', 'max:100'],
            'manufacture_year' => ['required', 'integer', 'min:1990', 'max:'.((int) date('Y'))],
            'vehicle_type_id'  => ['required', Rule::exists('vehicle_types', 'id')->where('status', 'active')],
            'description'      => ['nullable', 'string', 'max:500'],
            ...($id !== null ? ['status' => ['required', Rule::in(['active', 'inactive'])]] : []),
        ];
    }

    public function create(array $data): int
    {
        $companyId = $this->futaCompanyId();
        $this->assertChassisNumber($data['chassis_number']);
        $this->assertEngineNumber($data['engine_number']);

        $plate = strtoupper(trim($data['license_plate']));
        $this->assertNotOverMaxAge((int) $data['manufacture_year']);

        return DB::transaction(function () use ($data, $plate, $companyId): int {
            $type = $this->vehicleType((int) $data['vehicle_type_id']);
            $now = now();
            $busId = BusRecord::query()->toBase()->insertGetId([
                ...$this->payload($data, $plate, $type),
                'bus_company_id' => $companyId,
                'status'         => 'active',
                'doc_status'     => 'ok',
                'amenities'      => json_encode($type->vehicle_class === 'limousine_sleeper' ? BusPresentationService::AMENITIES : []),
                'wifi_password'  => $type->vehicle_class === 'limousine_sleeper' ? '19006067' : null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            $this->replaceSeatLayout($busId, $type);

            return $busId;
        });
    }

    public function update(int $id, array $data): void
    {
        $bus = $this->find($id);
        $this->assertEditable($id);
        $this->assertChassisNumber($data['chassis_number']);
        $this->assertEngineNumber($data['engine_number']);

        $plate = strtoupper(trim($data['license_plate']));
        $this->assertNotOverMaxAge((int) $data['manufacture_year']);
        $typeChanged = (int) $bus->vehicle_type_id !== (int) $data['vehicle_type_id'];

        if ($typeChanged && $this->hasTripHistory($id)) {
            throw ValidationException::withMessages([
                'vehicle_type_id' => __('BusManagement::app.bus_err_type_history'),
            ]);
        }

        DB::transaction(function () use ($id, $data, $plate, $typeChanged): void {
            $type = $this->vehicleType((int) $data['vehicle_type_id']);
            BusRecord::query()->toBase()->where('id', $id)->update([
                ...$this->payload($data, $plate, $type),
                'status'     => $data['status'],
                'updated_at' => now(),
            ]);

            if ($typeChanged) {
                $this->replaceSeatLayout($id, $type);
            }
        });
    }

    public function deactivate(int $id): void
    {
        $this->assertEditable($id);
        BusRecord::query()->toBase()->where('id', $id)
            ->update(['status' => 'inactive', 'updated_at' => now()]);
    }

    public function toggle(int $id): string
    {
        $bus = $this->find($id);
        // Xe bị khóa do quá hạn không có nút mở khóa thủ công
        if (in_array($bus->doc_status, ['age_expired'], true)) {
            throw ValidationException::withMessages(['bus' => __('BusManagement::app.bus_err_age_lock')]);
        }

        $newStatus = $bus->status === 'active' ? 'inactive' : 'active';
        BusRecord::query()->toBase()->where('id', $id)->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        return $newStatus;
    }

    /** Xóa xe: chỉ khi chưa có hồ sơ giấy tờ và chưa xếp chuyến. */
    public function remove(int $id): bool
    {
        $this->find($id);

        $hasDocuments = VehicleDocumentRecord::query()->toBase()->where('bus_id', $id)->exists();
        if ($hasDocuments) {
            return false;
        }

        if (Schema::hasTable('trips') && TripRecord::query()->toBase()->where('bus_id', $id)->exists()) {
            return false;
        }

        BusRecord::query()->toBase()->where('id', $id)->delete();

        return true;
    }

    public function find(int $id): object
    {
        $bus = BusRecord::query()->toBase()
            ->where('id', $id)
            ->where('bus_company_id', $this->futaCompanyId())->first();
        abort_if($bus === null, 404);

        return $bus;
    }

    public function assertEditable(int $id): void
    {
        $this->find($id);
        $this->assertNotAssignedToActiveTrip($id);
    }

    private function assertNotAssignedToActiveTrip(int $id): void
    {
        if (Schema::hasTable('trips') && TripRecord::query()->toBase()->where('bus_id', $id)
            ->whereIn('status', ['scheduled', 'departed'])->exists()) {
            throw ValidationException::withMessages(['bus' => __('BusManagement::app.bus_err_active_trip')]);
        }
    }

    private function assertChassisNumber(string $chassisNumber): void
    {
        if (preg_match(self::VIN_FORBIDDEN, $chassisNumber)) {
            throw ValidationException::withMessages([
                'chassis_number' => __('BusManagement::app.bus_err_chassis_ioq'),
            ]);
        }
    }

    private function assertEngineNumber(string $engineNumber): void
    {
        if (! preg_match(self::ENGINE_REGEX, strtoupper(trim($engineNumber)))) {
            throw ValidationException::withMessages([
                'engine_number' => __('BusManagement::app.bus_err_engine_number'),
            ]);
        }
    }

    /**
     * Chặn lưu nếu xe đã quá niên hạn tối đa 20 năm.
     * Niên hạn thực tế theo loại chuyến được kiểm tra khi điều xe, không lưu vào DB.
     */
    private function assertNotOverMaxAge(int $manufactureYear): void
    {
        if ((int) date('Y') > $manufactureYear + 20) {
            throw ValidationException::withMessages([
                'manufacture_year' => __('BusManagement::app.bus_err_over_max_age'),
            ]);
        }
    }

    private function payload(array $data, string $plate, ?object $type = null): array
    {
        $type ??= $this->vehicleType((int) $data['vehicle_type_id']);

        return [
            'license_plate'    => $plate,
            'chassis_number'   => strtoupper(trim($data['chassis_number'])),
            'engine_number'    => strtoupper(trim($data['engine_number'])),
            'color'            => $data['color'],
            'brand'            => $data['brand'],
            'manufacture_year' => $data['manufacture_year'],
            'vehicle_type_id'  => $data['vehicle_type_id'],
            'capacity'         => (int) $type->default_capacity,
            'bus_type'         => $this->busType((string) $type->vehicle_class),
            'seat_rows'        => (int) ($type->seat_rows ?: 11),
            'seat_columns'     => (int) ($type->seat_columns ?: 4),
            'name'             => $data['brand'].' '.$plate,
            'description'      => $data['description'] ?? null,
        ];
    }

    private function vehicleType(int $id): object
    {
        $type = VehicleTypeRecord::query()->toBase()->where('id', $id)->first();
        abort_if($type === null, 422);

        return $type;
    }

    private function busType(string $vehicleClass): string
    {
        return match ($vehicleClass) {
            'seater' => 'standard',
            'limousine', 'limousine_sleeper' => 'limousine',
            'sleeper', 'vip_cabin' => 'sleeper',
            default => 'sleeper',
        };
    }

    private function replaceSeatLayout(int $busId, object $type): void
    {
        SeatLayoutRecord::query()->toBase()->where('bus_id', $busId)->delete();

        $rows = (int) ($type->seat_rows ?? 0);
        $columns = (int) ($type->seat_columns ?? 0);
        $floors = (int) ($type->floor_count ?? 1);
        if ($rows < 1 || $columns < 1) {
            return;
        }

        $capacity = (int) $type->default_capacity;
        $seatsPerFloor = intdiv($capacity, $floors);
        $extraSeats = $capacity % $floors;
        $timestamp = now();
        $seatRows = [];

        for ($floor = 0; $floor < $floors; $floor++) {
            $deck = $floor === 0 ? 'lower' : 'upper';
            $prefix = $floor === 0 ? 'A' : 'B';
            $seatCount = $seatsPerFloor + ($floor < $extraSeats ? 1 : 0);

            foreach ($this->seatPositions($type, $seatCount, $rows, $columns) as $index => [$row, $column]) {
                $seatRows[] = [
                    'bus_id'           => $busId,
                    'seat_code'        => $prefix.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'row_number'       => $row,
                    'column_number'    => $column,
                    'seat_type'        => in_array($type->vehicle_class, ['seater', 'limousine'], true) ? 'seat' : 'sleeper',
                    'deck'             => $deck,
                    'price_multiplier' => 1.00,
                    'is_available'     => true,
                    'created_at'       => $timestamp,
                    'updated_at'       => $timestamp,
                ];
            }
        }

        if ($seatRows !== []) {
            SeatLayoutRecord::query()->toBase()->insert($seatRows);
        }
    }

    /** @return array<int, array{0: int, 1: int}> */
    private function seatPositions(object $type, int $seatCount, int $rows, int $columns): array
    {
        if ($seatCount < 1) {
            return [];
        }

        if ($type->vehicle_class === 'limousine_sleeper'
            && (int) $type->floor_count === 2
            && $rows === 6
            && $columns === 3
            && $seatCount === 17) {
            return [[1, 1], [1, 3], ...array_merge(...array_map(
                fn (int $row): array => [[$row, 1], [$row, 2], [$row, 3]],
                range(2, 6),
            ))];
        }

        return array_map(fn (int $index): array => [
            intdiv($index, $columns) + 1,
            ($index % $columns) + 1,
        ], range(0, $seatCount - 1));
    }

    private function hasTripHistory(int $busId): bool
    {
        $tripIds = TripRecord::query()->toBase()->where('bus_id', $busId)->pluck('id');
        if ($tripIds->isEmpty()) {
            return false;
        }

        return BookingRecord::query()->toBase()->whereIn('trip_id', $tripIds)->exists()
            || TicketRecord::query()->toBase()->whereIn('trip_id', $tripIds)->exists()
            || BookedSeatRecord::query()->toBase()->whereIn('trip_id', $tripIds)->exists()
            || SePayPaymentIntentRecord::query()->toBase()
                ->where(fn ($query) => $query->whereIn('trip_id', $tripIds)->orWhereIn('secondary_trip_id', $tripIds))
                ->exists();
    }

    private function futaCompanyId(): int
    {
        $id = BusCompanyRecord::query()->toBase()->where('code', 'FUTA')->value('id');
        abort_if($id === null, 503);

        return (int) $id;
    }
}
