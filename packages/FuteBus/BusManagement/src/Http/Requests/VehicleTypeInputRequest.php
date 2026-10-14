<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Requests;

use FuteBus\BusManagement\Services\VehicleTypeService;
use Illuminate\Foundation\Http\FormRequest;

class VehicleTypeInputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('vehicleType');

        return app(VehicleTypeService::class)->rules($id === null ? null : (int) $id);
    }
}
