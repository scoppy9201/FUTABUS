<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Requests;

use FuteBus\BusManagement\Services\BusService;
use Illuminate\Foundation\Http\FormRequest;

class BusInputRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()?->isAdmin()) {
            return false;
        }
        if ($this->route('bus') !== null) {
            app(BusService::class)->assertEditable((int) $this->route('bus'));
        }

        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('bus');

        return app(BusService::class)->rules($id === null ? null : (int) $id);
    }
}
