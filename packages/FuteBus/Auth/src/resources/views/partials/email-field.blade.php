<label class="flex h-10 items-center rounded-md border border-[#ffab92] bg-[#fff7f5] text-[#999] transition focus-within:border-[#ef5222] focus-within:ring-3 focus-within:ring-[#ef5222]/10">
    <span class="sr-only">{{ __('Auth::app.fields.email') }}</span>
    <x-heroicon-o-envelope class="ml-3 size-5.25 shrink-0" />
    <input
        class="h-full min-w-0 flex-1 bg-transparent px-3 text-base text-gray-900 outline-none placeholder:text-[#b9b9b9]"
        type="email"
        name="email"
        value="{{ old('email') }}"
        autocomplete="email"
        placeholder="{{ __('Auth::app.fields.email_placeholder') }}"
        required
    >
</label>
