<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RouteFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'search'   => ['nullable', 'string', 'max:150'],
            'status'   => ['nullable', Rule::in(['active', 'inactive', 'all'])],
            'route_id' => ['nullable', 'integer', 'min:1'],
            'page'     => ['nullable', 'integer', 'min:1'],
        ];
    }
}
