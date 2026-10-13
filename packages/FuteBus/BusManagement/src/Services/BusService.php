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
        return DB::table('buses')
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

        return DB::table('buses')->insertGetId([
            ...$this->payload($data, $plate),
            'bus_company_id' => $companyId,
            'status'         => 'active',
            'doc_status'     => 'ok',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function update(int $id, array $data): void
    {
        $this->assertEditable($id);
        $this->assertChassisNumber($data['chassis_number']);
        $this->assertEngineNumber($data['engine_number']);

        $plate = strtoupper(trim($data['license_plate']));
        $this->assertNotOverMaxAge((int) $data['manufacture_year']);

        DB::table('buses')->where('id', $id)->update([
            ...$this->payload($data, $plate),
            'status'     => $data['status'],
            'updated_at' => now(),
        ]);
    }

    public function deactivate(int $id): void
    {
        $this->assertEditable($id);
        DB::table('buses')->where('id', $id)
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
        DB::table('buses')->where('id', $id)->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        return $newStatus;
    }

    /** Xóa xe: chỉ khi chưa có hồ sơ giấy tờ và chưa xếp chuyến. */
    public function remove(int $id): bool
    {
        $this->find($id);

        $hasDocuments = DB::table('vehicle_documents')->where('bus_id', $id)->exists();
        if ($hasDocuments) {
            return false;
        }

        if (Schema::hasTable('trips') && DB::table('trips')->where('bus_id', $id)->exists()) {
            return false;
        }

        DB::table('buses')->where('id', $id)->delete();

        return true;
    }

    public function find(int $id): object
    {
        $bus = DB::table('buses')
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
        if (Schema::hasTable('trips') && DB::table('trips')->where('bus_id', $id)
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

    private function payload(array $data, string $plate): array
    {
        return [
            'license_plate'    => $plate,
            'chassis_number'   => strtoupper(trim($data['chassis_number'])),
            'engine_number'    => strtoupper(trim($data['engine_number'])),
            'color'            => $data['color'],
            'brand'            => $data['brand'],
            'manufacture_year' => $data['manufacture_year'],
            'vehicle_type_id'  => $data['vehicle_type_id'],
            'capacity'         => (int) DB::table('vehicle_types')
                ->where('id', $data['vehicle_type_id'])
                ->value('default_capacity'),
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
