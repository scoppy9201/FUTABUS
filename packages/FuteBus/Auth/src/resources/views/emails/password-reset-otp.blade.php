@component('mail::message')
# {{ __('Auth::app.password_recovery.mail.heading') }}

{{ __('Auth::app.password_recovery.mail.code', ['code' => $code]) }}

{{ __('Auth::app.password_recovery.mail.notice') }}

{{ __('Auth::app.registration_flow.mail.signature') }}
@endcomponent
