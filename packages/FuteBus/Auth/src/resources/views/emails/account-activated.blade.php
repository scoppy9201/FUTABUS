@component('mail::message')
# {{ __('Auth::app.registration_flow.mail.activated_heading') }}

{{ __('Auth::app.registration_flow.mail.activated_body') }}

@component('mail::button', ['url' => route('login')])
{{ __('Auth::app.registration_flow.mail.login_button') }}
@endcomponent

{{ __('Auth::app.registration_flow.mail.signature') }}
@endcomponent
