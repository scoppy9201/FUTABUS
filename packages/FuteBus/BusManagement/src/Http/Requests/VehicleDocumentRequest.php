<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VehicleDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()?->isAdmin()) {
            return false;
        }
        if ($this->route('id')) {
            abort_unless(DB::table('vehicle_documents')->where('id', $this->route('id'))->exists(), 404);
        }

        return true;
    }

    private function documentType(): ?object
    {
        $id = $this->route('id')
            ? DB::table('vehicle_documents')->where('id', $this->route('id'))->value('document_type_id')
            : $this->input('document_type_id');

        return DB::table('document_types')->find($id);
    }

    private function fields(): Collection
    {
        return DB::table('document_type_fields')->where('document_type_id', $this->documentType()?->id)
            ->where('is_active', 1)->orderBy('sort_order')->get();
    }

    public function rules(): array
    {
        $rules = [
            'issued_date' => ['required', 'date', 'before:today'],
            'attachment'  => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ];
        if (! $this->route('id')) {
            $rules += [
                'bus_id'           => ['required', Rule::exists('buses', 'id')->where('status', 'active')],
                'document_type_id' => ['required', Rule::exists('document_types', 'id')->where('status', 'active')],
            ];
        }
        if ($this->routeIs('bus-management.vehicle-documents.renew') || ($this->documentType()?->has_expiry ?? true)) {
            $rules['expiry_date'] = ['required', 'date', 'after:today', 'after:issued_date'];
        }
        foreach ($this->fields() as $field) {
            $rules['values.'.$field->field_key] = [$field->is_required ? 'required' : 'nullable', 'string', 'max:255'];
            if ($field->field_type === 'number') {
                $rules['values.'.$field->field_key][] = 'numeric';
            }
            if ($field->field_type === 'date') {
                $rules['values.'.$field->field_key][] = 'date';
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return $this->fields()->mapWithKeys(fn ($field) => ['values.'.$field->field_key => $field->label])->all();
    }

    public function fieldValues(): array
    {
        $input = $this->validated();

        return $this->fields()->map(fn ($field) => [
            'key'   => $field->field_key, 'label' => $field->label, 'type' => $field->field_type,
            'value' => data_get($input, 'values.'.$field->field_key),
        ])->values()->all();
    }

    public function messages(): array
    {
        return [
            'expiry_date.after'           => __('BusManagement::app.vd_err_expiry'),
            'issued_date.before_or_equal' => __('BusManagement::app.vd_err_issued_future'),
            'issued_date.before'          => __('BusManagement::app.vd_err_issued_future'),
            'attachment.mimes'            => __('BusManagement::app.vd_err_attachment'),
            'attachment.max'              => __('BusManagement::app.vd_err_attachment'),
        ];
    }
}
