@component('mail::message')
# {{ __('Auth::app.registration_flow.mail.otp_heading') }}

{{ __('Auth::app.registration_flow.mail.otp_code', ['code' => $code]) }}

{{ __('Auth::app.registration_flow.mail.otp_notice') }}

{{ __('Auth::app.registration_flow.mail.signature') }}
@endcomponent
