<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Http\Requests;

use FuteBus\RolePermission\Services\AccessControlService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('report.view') ?? false;
    }

    public function rules(): array
    {
        $companyId = app(AccessControlService::class)->companyFor($this->user())?->id;

        return [
            'type'   => ['sometimes', Rule::in(['overview', 'revenue', 'payments', 'tickets', 'trips'])],
            'period' => ['sometimes', Rule::in(['today', '7days', '30days', 'custom'])],
            'from'   => ['sometimes', 'date_format:Y-m-d'], 'to' => ['sometimes', 'date_format:Y-m-d'],
            'format' => ['sometimes', Rule::in(['pdf'])],
            'status' => ['sometimes', Rule::in([
                'all', 'completed', 'refunded', 'pending', 'failed', 'active', 'used', 'cancelled', 'scheduled', 'departed', 'arrived',
            ])],
            'trip_status' => ['sometimes', Rule::in(['all', 'scheduled', 'departed', 'arrived', 'cancelled'])],
            'route_id'    => ['nullable', 'integer', $companyId ? Rule::exists('routes', 'id')->where('bus_company_id', $companyId) : 'prohibited'],
            'trip_id'     => ['nullable', 'integer', $companyId ? Rule::exists('trips', 'id')->where('bus_company_id', $companyId) : 'prohibited'],
            'bus_id'      => ['nullable', 'integer', $companyId ? Rule::exists('buses', 'id')->where('bus_company_id', $companyId) : 'prohibited'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            [$defaultFrom, $defaultTo] = $this->dateDefaults((string) $this->input('period', '7days'));
            $from = $this->input('from', $defaultFrom);
            $to = $this->input('to', $defaultTo);
            if ($from > $to) {
                $validator->errors()->add('from', __('Reporting::app.invalid_range'));
            }
        }];
    }

    public function filters(): array
    {
        $period = $this->validated('period', '7days');
        [$from, $to] = $this->dateDefaults($period);

        return $this->validated() + [
            'type'     => 'overview', 'period' => $period, 'from' => $from, 'to' => $to,
            'format'   => 'pdf', 'status' => 'all', 'trip_status' => 'all',
            'route_id' => null, 'trip_id' => null, 'bus_id' => null,
        ];
    }

    private function dateDefaults(string $period): array
    {
        $now = now('Asia/Ho_Chi_Minh');
        $today = $now->toDateString();
        $from = match ($period) {
            'today'  => $today,
            '30days' => $now->copy()->subDays(29)->toDateString(),
            'custom' => $now->copy()->startOfMonth()->toDateString(),
            default  => $now->copy()->subDays(6)->toDateString(),
        };

        return [$from, $today];
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
