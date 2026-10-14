<?php

declare(strict_types=1);

namespace FuteBus\Cancellation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancellationQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['ticket_id' => ['required', 'integer', 'min:1']];
    }
}
