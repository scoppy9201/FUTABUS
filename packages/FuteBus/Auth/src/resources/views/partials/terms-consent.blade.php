<label class="flex items-start gap-2.25 text-[13px] leading-5 text-gray-900">
    <input class="mt-0.5 size-4.75 shrink-0 accent-[#ef5222]" type="checkbox" name="terms" required>
    <span>
        {{ __('Auth::app.terms.consent_prefix') }}
        <a class="text-futa-green underline underline-offset-2" href="{{ route('privacy') }}">{{ __('Auth::app.terms.privacy_policy') }}</a>
        {{ __('Auth::app.terms.consent_suffix') }}
    </span>
</label>
