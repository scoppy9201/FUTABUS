<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('report.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'type'   => ['sometimes', 'in:revenue,payments,tickets'],
            'from'   => ['sometimes', 'date_format:Y-m-d'],
            'to'     => ['sometimes', 'date_format:Y-m-d'],
            'status' => ['sometimes', 'in:all,completed,refunded,pending,failed'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $from = $this->input('from', now()->startOfMonth()->toDateString());
            $to = $this->input('to', now()->toDateString());
            if ($from > $to) {
                $validator->errors()->add('from', __('Reporting::app.invalid_range'));
            }
        }];
    }

    public function filters(): array
    {
        return $this->validated() + [
            'type' => 'revenue', 'from' => now()->startOfMonth()->toDateString(),
            'to'   => now()->toDateString(), 'status' => 'all',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->routeIs('reporting.export') && $this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'message' => implode(' ', $validator->errors()->all()),
            ], 422));
        }
        parent::failedValidation($validator);
    }
}
