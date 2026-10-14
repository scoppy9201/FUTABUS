<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Requests;

use FuteBus\Profile\Services\FeedbackService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedbackFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in([...FeedbackService::STATUSES, 'waiting'])],
            'search' => ['nullable', 'string', 'max:150'],
        ];
    }
}
