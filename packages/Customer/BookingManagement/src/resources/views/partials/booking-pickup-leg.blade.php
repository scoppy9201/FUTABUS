@php
    $isOutbound = $pickupLeg === 'outbound';
    $fieldPrefix = $isOutbound ? 'outbound_' : '';
    $pickupModeModel = $isOutbound ? 'outboundPickupMode' : 'pickupMode';
    $dropoffModeModel = $isOutbound ? 'outboundDropoffMode' : 'dropoffMode';
    $pickupAddressModel = $isOutbound ? 'outboundPickupAddress' : 'pickupAddress';
    $dropoffAddressModel = $isOutbound ? 'outboundDropoffAddress' : 'dropoffAddress';
    $pickupAddressRef = $isOutbound ? 'outboundPickupAddressInput' : 'pickupAddressInput';
    $dropoffAddressRef = $isOutbound ? 'outboundDropoffAddressInput' : 'dropoffAddressInput';
    $pickupDeparture = \Illuminate\Support\Carbon::parse($pickupTrip['departure_time']);
    $legName = __('core::booking.'.($isOutbound ? 'outbound_leg' : 'return_leg'));
@endphp
<div class="booking-page__pickup-leg" aria-labelledby="booking-{{ $pickupLeg }}-pickup-title">
    <div class="booking-page__pickup-leg-heading" x-data="{ infoOpen: false }">
        <h2 id="booking-{{ $pickupLeg }}-pickup-title">{{ __('core::booking.pickup_title') }}</h2>
        <button type="button" class="booking-page__info-button"
            @click.stop="infoOpen = !infoOpen" @keydown.escape.window="infoOpen = false"
            :aria-expanded="infoOpen" aria-controls="booking-{{ $pickupLeg }}-pickup-info"
            aria-label="{{ __('core::booking.pickup_info_label', ['leg' => $legName]) }}">
            <img class="booking-page__info-icon" src="{{ asset('icons/booking-info.png') }}" alt="" aria-hidden="true">
        </button>
        <div id="booking-{{ $pickupLeg }}-pickup-info" class="booking-page__pickup-tooltip"
            x-show="infoOpen" x-transition.opacity x-cloak @click.outside="infoOpen = false" role="tooltip">
            <h3>{{ __('core::booking.pickup_info_title') }}</h3>
            <dl>
                @foreach (__('core::booking.pickup_info_items') as $item)
                    <div>
                        <dt>{{ $item['label'] }}</dt>
                        <dd>{{ $item['detail'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
        <p>{{ $legName }} - {{ $pickupDeparture->locale(app()->getLocale())->translatedFormat('l, d/m') }}</p>
    </div>

    <fieldset>
        <legend>{{ __('core::booking.pickup') }}</legend>
        <div class="booking-page__radio-row">
            <label><input type="radio" name="{{ $fieldPrefix }}pickup_mode" value="station" x-model="{{ $pickupModeModel }}"> {{ __('core::booking.station') }}</label>
            <label><input type="radio" name="{{ $fieldPrefix }}pickup_mode" value="transfer" x-model="{{ $pickupModeModel }}"> {{ __('core::booking.transfer') }}</label>
        </div>
        <div x-show="{{ $pickupModeModel }} === 'station'" class="booking-page__select-wrap">
            @include('BookingManagement::partials.booking-stop-picker', [
                'stopTrip' => $pickupTrip,
                'stopKind' => 'pickup',
                'stopFieldName' => $fieldPrefix.'pickup_station',
                'stopLabel' => $legName.' - '.__('core::booking.pickup'),
            ])
        </div>
        <div x-show="{{ $pickupModeModel }} === 'transfer'" class="booking-page__input-wrap" x-cloak>
            <input :required="{{ $pickupModeModel }} === 'transfer'" name="{{ $fieldPrefix }}pickup_address"
                x-model="{{ $pickupAddressModel }}" x-ref="{{ $pickupAddressRef }}"
                aria-label="{{ $legName }} - {{ __('core::booking.transfer_address') }}"
                placeholder="{{ __('core::booking.transfer_address') }}">
            <button type="button" class="booking-page__clear-input"
                x-show="{{ $pickupAddressModel }}.length > 0" x-cloak
                @click="{{ $pickupAddressModel }} = ''; $refs.{{ $pickupAddressRef }}.focus()"
                aria-label="{{ __('core::booking.clear_field', ['field' => __('core::booking.transfer_address')]) }}">
                <x-heroicon-o-x-mark class="size-4" aria-hidden="true" />
            </button>
        </div>
    </fieldset>

    <fieldset>
        <legend>{{ __('core::booking.dropoff') }}</legend>
        <div class="booking-page__radio-row">
            <label><input type="radio" name="{{ $fieldPrefix }}dropoff_mode" value="station" x-model="{{ $dropoffModeModel }}"> {{ __('core::booking.station') }}</label>
            <label><input type="radio" name="{{ $fieldPrefix }}dropoff_mode" value="transfer" x-model="{{ $dropoffModeModel }}"> {{ __('core::booking.transfer') }}</label>
        </div>
        <div x-show="{{ $dropoffModeModel }} === 'station'" class="booking-page__select-wrap">
            @include('BookingManagement::partials.booking-stop-picker', [
                'stopTrip' => $pickupTrip,
                'stopKind' => 'dropoff',
                'stopFieldName' => $fieldPrefix.'dropoff_station',
                'stopLabel' => $legName.' - '.__('core::booking.dropoff'),
            ])
        </div>
        <div x-show="{{ $dropoffModeModel }} === 'transfer'" class="booking-page__input-wrap" x-cloak>
            <input :required="{{ $dropoffModeModel }} === 'transfer'" name="{{ $fieldPrefix }}dropoff_address"
                x-model="{{ $dropoffAddressModel }}" x-ref="{{ $dropoffAddressRef }}"
                aria-label="{{ $legName }} - {{ __('core::booking.transfer_address') }}"
                placeholder="{{ __('core::booking.transfer_address') }}">
            <button type="button" class="booking-page__clear-input"
                x-show="{{ $dropoffAddressModel }}.length > 0" x-cloak
                @click="{{ $dropoffAddressModel }} = ''; $refs.{{ $dropoffAddressRef }}.focus()"
                aria-label="{{ __('core::booking.clear_field', ['field' => __('core::booking.transfer_address')]) }}">
                <x-heroicon-o-x-mark class="size-4" aria-hidden="true" />
            </button>
        </div>
    </fieldset>
</div>
