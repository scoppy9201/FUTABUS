<div data-auth-email-field>
    <label class="auth-email-field flex h-10 items-center rounded-md border border-futa-orange/40 bg-futa-orange-soft text-[#999] transition focus-within:border-futa-orange focus-within:ring-3 focus-within:ring-futa-orange/10 data-[invalid=true]:border-[#ff3b30] data-[invalid=true]:bg-white data-[invalid=true]:focus-within:border-[#ff3b30] data-[invalid=true]:focus-within:ring-[#ff3b30]/10">
        <span class="sr-only">{{ __('Auth::app.fields.email') }}</span>
        <x-heroicon-o-envelope class="ml-3 size-5.25 shrink-0" />
        <input
            class="h-full min-w-0 flex-1 bg-transparent px-3 text-base text-gray-900 outline-none placeholder:text-[#b9b9b9]"
            type="email"
            name="email"
            value="{{ old('email') }}"
            autocomplete="email"
            placeholder="{{ __('Auth::app.fields.email_placeholder') }}"
            aria-describedby="auth-email-error"
            data-auth-email-input
            required
        >
    </label>
    <p id="auth-email-error" class="auth-email-error mt-1.5 hidden text-[13px] leading-4.5 text-[#ff3b30]" role="alert" hidden
        data-required-message="{{ __('Auth::app.registration_flow.validation.email_required') }}"
        data-invalid-message="{{ __('Auth::app.registration_flow.validation.invalid_email') }}"
        data-max-message="{{ __('Auth::app.registration_flow.validation.email_max') }}"></p>
</div>
