@php
    $formValue = static function (string $key, mixed $default = ''): string {
        $value = old($key, $default);
        return is_scalar($value) ? (string) $value : '';
    };
    $categoryIcons = [
        'staff' => 'heroicon-o-user-group', 'trip_service' => 'heroicon-o-truck',
        'schedule' => 'heroicon-o-clock', 'booking_payment' => 'heroicon-o-credit-card',
        'lost_property' => 'heroicon-o-shopping-bag', 'other' => 'heroicon-o-chat-bubble-left-right',
    ];
    $contactFields = [
        'contact_name' => ['type' => 'text', 'value' => $user->name, 'limit' => 120, 'autocomplete' => 'name'],
        'contact_phone' => ['type' => 'tel', 'value' => $user->phone, 'limit' => 30, 'autocomplete' => 'tel'],
        'contact_email' => ['type' => 'email', 'value' => $user->email, 'limit' => 255, 'autocomplete' => 'email'],
    ];
    $hasImageErrors = collect($errors->keys())->contains(static fn (string $key): bool => $key === 'attachments' || str_starts_with($key, 'attachments.'));
@endphp
<form action="{{ route('profile.feedback.store') }}" method="post" enctype="multipart/form-data"
    data-feedback-form data-feedback-wizard data-upload-error="{{ __('Profile::feedback.upload_error') }}"
    data-remove-image="{{ __('Profile::feedback.remove_image') }}" class="flex min-h-0 min-w-0 flex-col">
    @csrf
    <div data-feedback-wizard-progress hidden class="mb-5 shrink-0">
        <ol class="grid grid-cols-4 gap-2" aria-label="{{ __('Profile::feedback.wizard.progress') }}">
            @foreach(__('Profile::feedback.wizard.steps') as $step => $label)
                <li data-feedback-step-indicator="{{ $step }}" class="relative min-w-0 text-center">
                    @unless($loop->last)
                        <span data-feedback-step-connector aria-hidden="true"
                            class="pointer-events-none absolute start-[calc(50%+1.25rem)] top-4 w-[calc(100%-2rem)] border-t border-dashed border-futa-green/25"></span>
                    @endunless
                    <span data-feedback-step-number
                        class="relative mx-auto grid size-8 place-items-center rounded-full border border-futa-green/25 bg-futa-green/5 text-xs font-bold text-futa-green">{{ $loop->iteration }}</span>
                    <span data-feedback-step-label class="mt-1.5 block text-[11px] font-semibold text-slate-500">{{ $label }}</span>
                </li>
            @endforeach
        </ol>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
            <p data-feedback-step-status data-template="{{ __('Profile::feedback.wizard.step_count') }}" aria-live="polite"></p>
            <a href="tel:19006067" class="inline-flex items-center gap-1.5 font-semibold text-futa-green lg:hidden">
                <x-heroicon-o-phone class="size-3.5" aria-hidden="true" />1900 6067
            </a>
        </div>
    </div>
    <div data-feedback-wizard-viewport class="ticket-history-scroll min-h-0 space-y-6 overflow-y-auto pr-1">
        <div data-feedback-step="0" class="space-y-5" role="group" aria-labelledby="feedback-category-title">
            <fieldset>
                <legend id="feedback-category-title" data-feedback-step-heading tabindex="-1" class="flex items-center gap-2 text-sm font-bold text-gray-950 outline-none">
                    <span class="grid size-6 place-items-center rounded-full bg-futa-orange-soft text-xs text-futa-orange">1</span>
                    {{ __('Profile::feedback.category_section') }}
                </legend>
                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach($categoryIcons as $value => $icon)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="category" value="{{ $value }}" required
                                @checked($formValue('category', 'trip_service') === $value)
                                aria-invalid="{{ $errors->has('category') ? 'true' : 'false' }}"
                                aria-describedby="feedback-category-error" class="peer sr-only">
                            <span class="block h-full rounded-xl border border-gray-200 p-3 transition-colors hover:bg-futa-orange-soft/30
                                peer-checked:border-futa-orange peer-checked:bg-futa-orange-soft peer-focus-visible:ring-2 peer-focus-visible:ring-futa-orange">
                                <x-dynamic-component :component="$icon" class="size-5 text-futa-orange" aria-hidden="true" />
                                <span class="mt-2 block text-xs font-bold text-gray-900">{{ __('Profile::feedback.categories.'.$value) }}</span>
                                <span class="mt-1 block text-[11px] leading-4 text-slate-500">{{ __('Profile::feedback.category_hints.'.$value) }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <p id="feedback-category-error" class="mt-1 text-xs text-red-600">{{ $errors->first('category') }}</p>
            </fieldset>
            <fieldset>
                <legend class="text-xs font-semibold text-slate-600">{{ __('Profile::feedback.fields.urgency') }}</legend>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach(__('Profile::feedback.urgencies') as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="urgency" value="{{ $value }}" required
                                @checked($formValue('urgency', 'suggestion') === $value)
                                aria-invalid="{{ $errors->has('urgency') ? 'true' : 'false' }}"
                                aria-describedby="feedback-urgency-error" class="peer sr-only">
                            <span class="block rounded-full border border-gray-200 px-3.5 py-2 text-xs font-bold text-slate-600
                                peer-checked:border-futa-orange peer-checked:bg-futa-orange peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-futa-orange">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <p id="feedback-urgency-error" class="mt-1 text-xs text-red-600">{{ $errors->first('urgency') }}</p>
            </fieldset>
        </div>
        <section data-feedback-step="1" aria-labelledby="feedback-trip-title">
            <h3 id="feedback-trip-title" data-feedback-step-heading tabindex="-1" class="flex items-center gap-2 text-sm font-bold text-gray-950 outline-none">
                <span class="grid size-6 place-items-center rounded-full bg-futa-orange-soft text-xs text-futa-orange">2</span>
                {{ __('Profile::feedback.trip_section') }}
            </h3>
            <label for="feedback-booking" class="mt-3 block text-xs font-semibold text-slate-600">
                {{ __('Profile::feedback.fields.booking_id') }} <span class="font-normal">({{ __('Profile::feedback.optional') }})</span>
            </label>
            <select id="feedback-booking" name="booking_id" aria-describedby="feedback-booking-error"
                aria-invalid="{{ $errors->has('booking_id') ? 'true' : 'false' }}"
                class="mt-1.5 h-11 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm outline-none focus:border-futa-orange">
                <option value="">{{ __('Profile::feedback.no_booking') }}</option>
                @foreach($bookings as $booking)
                    <option value="{{ $booking->id }}" @selected($formValue('booking_id') === (string) $booking->id)>
                        {{ $booking->origin_city }} → {{ $booking->destination_city }} · {{ \Illuminate\Support\Carbon::parse($booking->departure_time)->format('d/m/Y H:i') }} · {{ $booking->booking_code }}
                    </option>
                @endforeach
            </select>
            <p id="feedback-booking-error" class="mt-1 text-xs text-red-600">{{ $errors->first('booking_id') }}</p>
        </section>
        <section data-feedback-step="2" aria-labelledby="feedback-content-title" class="space-y-3">
            <h3 id="feedback-content-title" data-feedback-step-heading tabindex="-1" class="flex items-center gap-2 text-sm font-bold text-gray-950 outline-none">
                <span class="grid size-6 place-items-center rounded-full bg-futa-orange-soft text-xs text-futa-orange">3</span>
                {{ __('Profile::feedback.content_section') }}
            </h3>
            <div>
                <label for="feedback-subject" class="block text-xs font-semibold text-slate-600">{{ __('Profile::feedback.fields.subject') }} <span class="text-red-600">*</span></label>
                <input id="feedback-subject" name="subject" required minlength="5" maxlength="150" value="{{ $formValue('subject') }}"
                    placeholder="{{ __('Profile::feedback.subject_placeholder') }}" aria-describedby="feedback-subject-error"
                    aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}"
                    class="mt-1.5 h-11 w-full rounded-lg border border-gray-200 px-3 text-sm outline-none placeholder:text-slate-400 focus:border-futa-orange">
                <p id="feedback-subject-error" class="mt-1 text-xs text-red-600">{{ $errors->first('subject') }}</p>
            </div>
            <div>
                <label for="feedback-message" class="block text-xs font-semibold text-slate-600">{{ __('Profile::feedback.fields.message') }} <span class="text-red-600">*</span></label>
                <textarea id="feedback-message" name="message" required minlength="20" maxlength="3000" rows="4" data-feedback-message
                    placeholder="{{ __('Profile::feedback.message_placeholder') }}" aria-describedby="feedback-message-error feedback-message-count"
                    aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}"
                    class="mt-1.5 max-h-40 w-full resize-y rounded-lg border border-gray-200 p-3 text-sm outline-none placeholder:text-slate-400 focus:border-futa-orange">{{ $formValue('message') }}</textarea>
                <div class="flex items-start justify-between gap-3">
                    <p id="feedback-message-error" class="text-xs text-red-600">{{ $errors->first('message') }}</p>
                    <span id="feedback-message-count" data-feedback-count class="shrink-0 text-xs text-slate-400">{{ mb_strlen($formValue('message')) }}/3000</span>
                </div>
            </div>
            <div>
                <label for="feedback-images" class="block text-xs font-semibold text-slate-600">
                    {{ __('Profile::feedback.fields.attachments') }} <span class="font-normal">({{ __('Profile::feedback.optional') }})</span>
                </label>
                <label data-feedback-dropzone class="relative mt-2 flex cursor-pointer flex-col items-center rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-center hover:border-futa-orange focus-within:ring-2 focus-within:ring-futa-orange">
                    <input id="feedback-images" name="attachments[]" type="file" multiple accept="image/jpeg,image/png,image/webp"
                        aria-invalid="{{ $hasImageErrors ? 'true' : 'false' }}"
                        aria-describedby="feedback-images-hint feedback-images-error" class="sr-only" data-feedback-images>
                    <x-heroicon-o-arrow-up-tray class="size-6 text-futa-orange" aria-hidden="true" />
                    <span class="mt-2 text-xs font-bold text-futa-orange">{{ __('Profile::feedback.upload_title') }}</span>
                    <span id="feedback-images-hint" class="mt-1 text-[11px] text-slate-500">{{ __('Profile::feedback.upload_hint') }}</span>
                </label>
                <p id="feedback-images-error" data-feedback-image-error role="alert" class="mt-1 text-xs text-red-600">
                    {{ implode(' ', $errors->get('attachments')) }}
                    @foreach($errors->get('attachments.*') as $messages) {{ implode(' ', $messages) }} @endforeach
                </p>
                @if($hasFormErrors)<p class="text-xs text-slate-500">{{ __('Profile::feedback.reselect_images') }}</p>@endif
                <div data-feedback-previews class="mt-2 grid grid-cols-3 gap-2"></div>
                <template data-feedback-preview-template>
                    <figure class="relative min-w-0 rounded-lg border border-gray-200 p-1.5">
                        <img class="h-24 w-full rounded-md object-cover" alt="">
                        <figcaption class="mt-1 truncate text-[11px] text-slate-500"></figcaption>
                        <button type="button" class="absolute right-2 top-2 grid size-7 place-items-center rounded-full bg-white text-red-600 shadow">
                            <x-heroicon-o-x-mark class="size-4" aria-hidden="true" />
                        </button>
                    </figure>
                </template>
            </div>
        </section>
        <section data-feedback-step="3" aria-labelledby="feedback-contact-title">
            <h3 id="feedback-contact-title" data-feedback-step-heading tabindex="-1" class="flex items-center gap-2 text-sm font-bold text-gray-950 outline-none">
                <span class="grid size-6 place-items-center rounded-full bg-futa-orange-soft text-xs text-futa-orange">4</span>
                {{ __('Profile::feedback.contact_section') }}
            </h3>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach($contactFields as $key => $field)
                    <div @class(['sm:col-span-2' => $key === 'contact_email'])>
                        <label for="feedback-{{ $key }}" class="block text-xs font-semibold text-slate-600">
                            {{ __('Profile::feedback.fields.'.$key) }}
                            @if($key === 'contact_email')<span class="font-normal">({{ __('Profile::feedback.optional') }})</span>@else<span class="text-red-600">*</span>@endif
                        </label>
                        <input id="feedback-{{ $key }}" name="{{ $key }}" type="{{ $field['type'] }}" maxlength="{{ $field['limit'] }}"
                            value="{{ $formValue($key, $field['value']) }}" autocomplete="{{ $field['autocomplete'] }}" @required($key !== 'contact_email')
                            aria-describedby="feedback-{{ $key }}-error" aria-invalid="{{ $errors->has($key) ? 'true' : 'false' }}"
                            class="mt-1.5 h-11 w-full rounded-lg border border-gray-200 px-3 text-sm outline-none focus:border-futa-orange">
                        <p id="feedback-{{ $key }}-error" class="mt-1 text-xs text-red-600">{{ $errors->first($key) }}</p>
                    </div>
                @endforeach
            </div>
            <label class="mt-4 flex items-start gap-2 text-xs leading-5 text-slate-600">
                <input type="checkbox" name="truth_confirm" value="1" required @checked($formValue('truth_confirm') === '1')
                    aria-invalid="{{ $errors->has('truth_confirm') ? 'true' : 'false' }}"
                    aria-describedby="feedback-truth-error" class="mt-1 size-4 shrink-0 accent-futa-orange">
                <span>{{ __('Profile::feedback.truth_label') }} <span class="text-red-600">*</span></span>
            </label>
            <p id="feedback-truth-error" class="text-xs text-red-600">{{ $errors->first('truth_confirm') }}</p>
            <input type="hidden" name="allow_contact" value="0">
            <label class="mt-2 flex items-start gap-2 text-xs leading-5 text-slate-600">
                <input type="checkbox" name="allow_contact" value="1" @checked($formValue('allow_contact', $hasFormErrors ? '0' : '1') === '1')
                    aria-invalid="{{ $errors->has('allow_contact') ? 'true' : 'false' }}"
                    aria-describedby="feedback-consent-error" class="mt-1 size-4 shrink-0 accent-futa-orange">
                <span>{{ __('Profile::feedback.contact_label') }}</span>
            </label>
            <p id="feedback-consent-error" class="text-xs text-red-600">{{ $errors->first('allow_contact') }}</p>
        </section>
    </div>
    <div class="sticky bottom-0 mt-5 flex shrink-0 flex-wrap items-center justify-between gap-2 border-t border-gray-100 bg-white pt-4">
        <button type="button" data-feedback-close class="min-h-11 rounded-full border border-gray-200 px-4 text-sm font-semibold text-slate-600 hover:bg-gray-50">{{ __('Profile::feedback.cancel') }}</button>
        <div class="flex flex-wrap justify-end gap-2">
            <button type="button" data-feedback-step-back hidden class="min-h-11 rounded-full border border-gray-200 px-4 text-sm font-semibold text-slate-600 hover:bg-gray-50">{{ __('Profile::feedback.wizard.previous') }}</button>
            <button type="button" data-feedback-step-next hidden class="inline-flex min-h-11 items-center gap-2 rounded-full bg-futa-orange px-5 text-sm font-bold text-white hover:bg-futa-orange-dark">
                {{ __('Profile::feedback.wizard.next') }}<x-heroicon-o-arrow-right class="size-4" aria-hidden="true" />
            </button>
            <button type="submit" data-feedback-step-submit class="min-h-11 rounded-full bg-futa-orange px-5 text-sm font-bold text-white hover:bg-futa-orange-dark disabled:opacity-50">{{ __('Profile::feedback.send') }}</button>
        </div>
    </div>
</form>
