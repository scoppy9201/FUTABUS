<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentTypeRequest extends FormRequest
{
    private const FIELD_TYPES = ['text', 'number', 'date'];

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('id') ? (int) $this->route('id') : null;

        return [
            'name'              => ['required', 'string', 'min:3', 'max:50', Rule::unique('document_types', 'name')->ignore($id)],
            'description'       => ['nullable', 'string', 'max:500'],
            'is_required'       => ['required', 'boolean'],
            'has_expiry'        => ['required', 'boolean'],
            'blocks_dispatch'   => ['required', 'boolean'],
            'warning_days'      => ['nullable', 'integer', 'min:1', 'max:365'],
            'fields'            => ['required', 'array', 'min:1'],
            'fields.*.id'       => ['nullable', 'integer'],
            'fields.*.label'    => ['required', 'string', 'max:100'],
            'fields.*.type'     => ['nullable', Rule::in(self::FIELD_TYPES)],
            'fields.*.required' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'     => __('BusManagement::app.dt_err_name'),
            'fields.required' => __('BusManagement::app.dt_err_fields'),
            'fields.min'      => __('BusManagement::app.dt_err_fields'),
        ];
    }
}
