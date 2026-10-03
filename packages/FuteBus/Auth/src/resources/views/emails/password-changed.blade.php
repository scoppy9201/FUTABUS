@component('mail::message')
# {{ __('Auth::app.password_recovery.mail.changed_heading') }}

{{ __('Auth::app.password_recovery.mail.changed_body') }}

{{ __('Auth::app.registration_flow.mail.signature') }}
@endcomponent
