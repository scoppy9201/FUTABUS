<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100']];
    }
}
