<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RouteActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
