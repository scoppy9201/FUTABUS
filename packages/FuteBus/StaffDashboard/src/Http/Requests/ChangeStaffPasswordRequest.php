<?php

declare(strict_types=1);

namespace FuteBus\StaffDashboard\Http\Requests;

use FuteBus\Profile\Http\Requests\ChangeApiPasswordRequest;

class ChangeStaffPasswordRequest extends ChangeApiPasswordRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['password'] = array_values(array_diff($rules['password'], ['confirmed']));
        $rules['password_confirmation'][] = 'same:password';

        return $rules;
    }

    public function messages(): array
    {
        return [
            'current_password.required'      => __('Profile::app.password_change.validation.current_required'),
            'current_password.string'        => __('Profile::app.password_change.validation.current_invalid'),
            'password.required'              => __('Profile::app.password_change.validation.new_required'),
            'password.string'                => __('Profile::app.password_change.validation.new_invalid'),
            'password.min'                   => __('Profile::app.password_change.validation.new_min'),
            'password.different'             => __('Profile::app.password_change.validation.new_different'),
            'password_confirmation.required' => __('Profile::app.password_change.validation.confirm_required'),
            'password_confirmation.string'   => __('Profile::app.password_change.validation.confirmed'),
            'password_confirmation.same'     => __('Profile::app.password_change.validation.confirmed'),
        ];
    }
}
