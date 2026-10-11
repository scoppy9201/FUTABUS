<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category' => [
                'required',
                Rule::in(['trip_service', 'staff', 'schedule', 'booking_payment', 'lost_property', 'pickup', 'other']),
            ],
            'urgency'    => ['required', Rule::in(['suggestion', 'complaint', 'urgent'])],
            'booking_id' => [
                'nullable', 'integer',
                Rule::exists('bookings', 'id')->where(function (Builder $query): void {
                    $query->where(function (Builder $owner): void {
                        $owner->where('user_id', $this->user()->id)
                            ->orWhere(function (Builder $guest): void {
                                $guest->whereNull('user_id')->whereIn('customer_id', function (Builder $customer): void {
                                    $customer->select('id')->from('customers')->where('user_id', $this->user()->id);
                                });
                            });
                    });
                }),
            ],
            'subject'       => ['required', 'string', 'min:5', 'max:150'],
            'message'       => ['required', 'string', 'min:20', 'max:3000'],
            'attachments'   => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['bail', 'file', 'image', 'mimes:jpeg,png,webp', 'dimensions:min_width=1,min_height=1', 'max:5120'],
            'contact_name'  => ['required', 'string', 'max:120'],
            'contact_phone' => ['required', 'string', 'regex:/^\+84(?:[35789]\d{8}|2\d{9})$/'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'truth_confirm' => ['required', 'accepted'],
            'allow_contact' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $fields = [];
        foreach (['subject', 'message', 'contact_name', 'contact_email'] as $field) {
            if (is_string($this->input($field))) {
                $fields[$field] = trim($this->input($field));
            }
        }

        if (is_string($this->input('contact_phone'))) {
            $phone = preg_replace('/[\s.()\-]+/', '', $this->input('contact_phone'));
            if (str_starts_with($phone, '0')) {
                $phone = '+84'.substr($phone, 1);
            } elseif (str_starts_with($phone, '84')) {
                $phone = '+'.$phone;
            }
            $fields['contact_phone'] = $phone;
        }

        $this->merge($fields);
    }

    public function attributes(): array
    {
        return __('Profile::feedback.fields');
    }

    public function messages(): array
    {
        return [
            'booking_id.exists'        => __('Profile::feedback.validation.booking_owned'),
            'contact_phone.regex'      => __('Profile::feedback.validation.phone_invalid'),
            'attachments.max'          => __('Profile::feedback.validation.attachments_count'),
            'attachments.*.image'      => __('Profile::feedback.validation.attachment_image'),
            'attachments.*.mimes'      => __('Profile::feedback.validation.attachment_image'),
            'attachments.*.dimensions' => __('Profile::feedback.validation.attachment_image'),
            'attachments.*.max'        => __('Profile::feedback.validation.attachment_size'),
            'truth_confirm.accepted'   => __('Profile::feedback.validation.truth_required'),
        ];
    }
}
