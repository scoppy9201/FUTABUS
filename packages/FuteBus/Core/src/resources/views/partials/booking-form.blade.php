<form class="booking-page__form" x-ref="bookingForm" novalidate method="POST"
    action="{{ route('trip-booking.payment.store', array_merge(['trip' => $trip['id']], $criteria, ['direction' => request('direction')])) }}"
    @submit.prevent="continueBooking()">
    @csrf
    <template x-for="id in selectedIds" :key="id">
        <input type="hidden" name="seats[]" :value="id">
    </template>
    <section class="booking-panel" aria-labelledby="booking-customer-title">
        <div class="booking-page__customer-grid">
            <div class="booking-page__fields">
                <h2 id="booking-customer-title">{{ __('core::booking.customer_info') }}</h2>
                @foreach ([
                    ['key' => 'full_name', 'name' => 'name', 'model' => 'customerName', 'ref' => 'customerNameInput', 'type' => 'text', 'autocomplete' => 'name'],
                    ['key' => 'phone', 'name' => 'phone', 'model' => 'customerPhone', 'ref' => 'customerPhoneInput', 'type' => 'tel', 'autocomplete' => 'tel'],
                    ['key' => 'email', 'name' => 'email', 'model' => 'customerEmail', 'ref' => 'customerEmailInput', 'type' => 'email', 'autocomplete' => 'email'],
                ] as $field)
                    <div class="booking-page__field">
                        <label for="booking-customer-{{ $field['name'] }}">
                            {{ __('core::booking.'.$field['key']) }} <b>*</b>
                        </label>
                        <div class="booking-page__input-wrap">
                            <input id="booking-customer-{{ $field['name'] }}"
                                name="{{ $field['name'] }}" type="{{ $field['type'] }}"
                                autocomplete="{{ $field['autocomplete'] }}" required
                                x-model="{{ $field['model'] }}" x-ref="{{ $field['ref'] }}"
                                @if ($field['name'] === 'phone')
                                    :class="{ 'is-invalid': phoneError() }"
                                    :aria-invalid="phoneError()"
                                    aria-describedby="booking-phone-error"
                                @elseif ($field['name'] === 'email')
                                    :class="{ 'is-invalid': emailError() }"
                                    :aria-invalid="emailError()"
                                    aria-describedby="booking-email-error"
                                @endif>
                            <button type="button" class="booking-page__clear-input"
                                x-show="{{ $field['model'] }}.length > 0" x-cloak
                                @click="{{ $field['model'] }} = ''; $refs.{{ $field['ref'] }}.focus()"
                                aria-label="{{ __('core::booking.clear_field', ['field' => __('core::booking.'.$field['key'])]) }}">
                                <x-heroicon-o-x-mark class="size-4" aria-hidden="true" />
                            </button>
                        </div>
                        @if ($field['name'] === 'phone')
                            <p id="booking-phone-error" class="booking-page__field-error"
                                x-show="phoneError()" x-cloak>{{ __('core::booking.invalid_phone') }}</p>
                        @elseif ($field['name'] === 'email')
                            <p id="booking-email-error" class="booking-page__field-error"
                                x-show="emailError()" x-cloak>{{ __('core::booking.invalid_email') }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="booking-page__terms">
                <h3>{{ __('core::booking.terms_title') }}</h3>
                <p class="booking-page__member-note">{{ __('core::booking.member_note') }}</p>
                @foreach (__('core::booking.terms_notes') as $note)
                    <p class="booking-page__terms-note"><span>(*) </span>@include('core::partials.booking-linked-text', ['text' => $note])</p>
                @endforeach
            </div>
        </div>
        <div class="booking-page__accept">
            <input id="booking-accept-terms" name="accept_terms" value="1" type="checkbox" x-ref="acceptTerms" required>
            <span>
                <button type="button" class="booking-page__terms-link"
                    x-ref="termsModalTrigger" @click="openTermsModal()"
                    aria-haspopup="dialog" aria-controls="booking-terms-modal">
                    {{ __('core::booking.terms_accept_link') }}
                </button>
                <label for="booking-accept-terms">{{ __('core::booking.terms_accept_rest') }}</label>
            </span>
        </div>
    </section>

    <section class="booking-panel" aria-labelledby="booking-pickup-title">
        <div class="booking-panel__header">
            <h2 id="booking-pickup-title">{{ __('core::booking.pickup_title') }}</h2>
            <img class="booking-page__info-icon" src="{{ asset('icons/booking-info.png') }}" alt="" aria-hidden="true">
        </div>
        <div class="booking-page__pickup-grid">
            <fieldset>
                <legend>{{ __('core::booking.pickup') }}</legend>
                <div class="booking-page__radio-row">
                    <label><input type="radio" name="pickup_mode" value="station" x-model="pickupMode"> {{ __('core::booking.station') }}</label>
                    <label><input type="radio" name="pickup_mode" value="transfer" x-model="pickupMode"> {{ __('core::booking.transfer') }}</label>
                </div>
                <div x-show="pickupMode === 'station'" class="booking-page__select-wrap">
                    <select name="pickup_station" aria-label="{{ __('core::booking.pickup') }}">
                        <option>{{ $origin }}</option>
                    </select>
                </div>
                <div x-show="pickupMode === 'transfer'" class="booking-page__input-wrap">
                    <input :required="pickupMode === 'transfer'" name="pickup_address"
                        x-model="pickupAddress" x-ref="pickupAddressInput"
                        placeholder="{{ __('core::booking.transfer_address') }}">
                    <button type="button" class="booking-page__clear-input"
                        x-show="pickupAddress.length > 0" x-cloak
                        @click="pickupAddress = ''; $refs.pickupAddressInput.focus()"
                        aria-label="{{ __('core::booking.clear_field', ['field' => __('core::booking.transfer_address')]) }}">
                        <x-heroicon-o-x-mark class="size-4" aria-hidden="true" />
                    </button>
                </div>
                <p x-show="pickupMode === 'station'" class="booking-page__boarding-reminder">
                    {{ __('core::booking.arrive_at') }} <strong>{{ $origin }}</strong>
                    <em>{{ __('core::booking.before_time') }} {{ $departure->copy()->subMinutes(15)->format('H:i d/m/Y') }}</em>
                    {{ __('core::booking.boarding_help') }}
                </p>
            </fieldset>
            <fieldset>
                <legend>{{ __('core::booking.dropoff') }}</legend>
                <div class="booking-page__radio-row">
                    <label><input type="radio" name="dropoff_mode" value="station" x-model="dropoffMode"> {{ __('core::booking.station') }}</label>
                    <label><input type="radio" name="dropoff_mode" value="transfer" x-model="dropoffMode"> {{ __('core::booking.transfer') }}</label>
                </div>
                <div x-show="dropoffMode === 'station'" class="booking-page__select-wrap">
                    <select name="dropoff_station" aria-label="{{ __('core::booking.dropoff') }}">
                        <option>{{ $destination }}</option>
                    </select>
                </div>
                <div x-show="dropoffMode === 'transfer'" class="booking-page__input-wrap">
                    <input :required="dropoffMode === 'transfer'" name="dropoff_address"
                        x-model="dropoffAddress" x-ref="dropoffAddressInput"
                        placeholder="{{ __('core::booking.transfer_address') }}">
                    <button type="button" class="booking-page__clear-input"
                        x-show="dropoffAddress.length > 0" x-cloak
                        @click="dropoffAddress = ''; $refs.dropoffAddressInput.focus()"
                        aria-label="{{ __('core::booking.clear_field', ['field' => __('core::booking.transfer_address')]) }}">
                        <x-heroicon-o-x-mark class="size-4" aria-hidden="true" />
                    </button>
                </div>
            </fieldset>
        </div>
    </section>
    <div class="booking-page__actions">
        <div><span>FUTAPAY</span><strong x-text="money(selectedIds.length * fare)"></strong></div>
        <a href="{{ route('trip-search', $criteria) }}">{{ __('core::booking.cancel') }}</a>
        <button type="submit" x-ref="captchaTrigger">{{ __('core::booking.pay') }}</button>
    </div>
</form>
