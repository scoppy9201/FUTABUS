<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q'        => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }
}
