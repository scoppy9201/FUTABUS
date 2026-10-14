<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Http\Requests;

use FuteBus\TripManagement\Models\Trip as TripRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class TripInputRequest extends FormRequest
{
    private const DT = 'Y-m-d\TH:i';

    public function authorize(): bool
    {
        if (! $this->user()?->isAdmin()) {
            return false;
        }
        if ($this->route('id')) {
            abort_unless(TripRecord::query()->toBase()->where('id', $this->route('id'))->exists(), 404);
        }

        return true;
    }

    public function rules(): array
    {
        $editing = $this->route('id') !== null;
        $manual = ! $editing || TripRecord::query()->toBase()->where('id', $this->route('id'))->value('trip_schedule_id') === null;
        $rules = [

            'bus_id'         => [$editing ? 'nullable' : 'required', 'exists:buses,id'],

            'price'          => ['required', 'numeric', 'gt:0'],
        ];
        if ($manual) {
            $rules += [
                'route_id'       => ['required', 'exists:routes,id'],
                'departure_time' => ['bail', 'required', 'date_format:'.self::DT, $this->futureRule()],
            ];
        }

        return $rules;
    }

    private function futureRule(): \Closure
    {
        return function ($attr, $value, $fail) {
            try {
                $d = Carbon::createFromFormat(self::DT, (string) $value);
            } catch (\Throwable) {
                $d = false;
            }

            if ($d === false || $d->lte(now())) {
                $fail(__('TripManagement::app.tr_err_past'));
            }
        };
    }

    public function messages(): array
    {
        return [
            'price.gt' => __('TripManagement::app.tr_err_price'),
        ];
    }
}
