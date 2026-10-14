<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Http\Requests;

use FuteBus\BusCompanyManagement\Models\BusCompany as BusCompanyRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class TripScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->boolean('apply_all_days') || ! $this->filled('days_of_week')) {
            $this->merge(['days_of_week' => range(1, 7)]);
        }
    }

    public function rules(): array
    {
        return [
            'route_id'       => ['required', Rule::exists('routes', 'id')->where('is_active', true)
                ->where('bus_company_id', BusCompanyRecord::query()->toBase()->where('code', 'FUTA')->value('id') ?? 0)],
            'departure_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail): void {
                    if ($this->input('start_date') === today()->toDateString()
                        && is_string($value) && preg_match('/^\d{2}:\d{2}$/D', $value) === 1
                        && $value <= now()->format('H:i')) {
                        $fail(__('TripManagement::app.sch_err_time_past'));
                    }
                },
            ],
            'days_of_week'   => ['required', 'array', 'min:1'],
            'days_of_week.*' => ['integer', 'between:1,7'],
            'start_date'     => [
                'bail',
                'required',
                'date',
                function ($attribute, $value, $fail): void {
                    if (is_string($value) && Carbon::parse($value)->lt(today())) {
                        $fail(__('TripManagement::app.sch_err_past'));
                    }
                },
            ],
            'end_date'       => [
                'bail',
                'required', 'date', 'after_or_equal:start_date',
                function ($attribute, $value, $fail) {
                    if (is_string($value) && Carbon::parse($value)->lt(today())) {
                        $fail(__('TripManagement::app.sch_err_past'));
                    }
                },
            ],
            'price'          => ['required', 'integer', 'gt:1000', 'lt:100000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => __('TripManagement::app.sch_err_date'),
            'price.integer'           => __('TripManagement::app.sch_err_price'),
            'price.gt'                => __('TripManagement::app.sch_err_price'),
            'price.lt'                => __('TripManagement::app.sch_err_price'),
        ];
    }
}
