@php
    $summaryIsOutbound = ($summaryLeg ?? '') === 'outbound';
    $summaryTrip = $summaryIsOutbound ? $preview['outbound_trip'] : $preview['trip'];
    $summarySeats = $summaryIsOutbound ? $preview['outbound_seats'] : $preview['seats'];
    $summaryDeparture = \Illuminate\Support\Carbon::parse($summaryTrip['departure_time']);
    $summaryPickup = $summaryIsOutbound ? ['name' => $summaryTrip['origin'], 'address' => null] : $preview['pickup'];
    $summaryDropoff = $summaryIsOutbound ? ['name' => $summaryTrip['destination'], 'address' => null] : $preview['dropoff'];
    $summaryTotal = number_format(count($summarySeats) * $summaryTrip['fare'], 0, ',', '.').'đ';
@endphp
<dl>
    <div>
        <dt>{{ __('core::booking.route') }}</dt>
        <dd>{{ $summaryTrip['origin'] }} - {{ $summaryTrip['destination'] }}</dd>
    </div>
    <div>
        <dt>{{ __('core::booking.departure_time') }}</dt>
        <dd class="payment-page__green">{{ $summaryDeparture->format('H:i d/m/Y') }}</dd>
    </div>
    <div>
        <dt>{{ __('core::booking.seat_count') }}</dt>
        <dd>{{ count($summarySeats) }} {{ __('core::booking.seat_unit') }}</dd>
    </div>
    <div>
        <dt>{{ __('core::booking.seats') }}</dt>
        <dd class="payment-page__green">{{ implode(', ', $summarySeats) }}</dd>
    </div>
    <div>
        <dt>{{ __('Payment::payment.payment_pickup') }}</dt>
        <dd>{{ $summaryPickup['name'] }}</dd>
    </div>
    <div class="payment-page__address-row">
        <dd>{{ $summaryPickup['address'] ?: __('Payment::payment.payment_address_pending') }}</dd>
    </div>
    <div>
        <dt>{{ __('Payment::payment.payment_boarding_time') }}</dt>
        <dd class="payment-page__boarding-time">
            {{ __('core::booking.before_time') }}
            {{ ($summaryIsOutbound ? $summaryDeparture->copy()->subMinutes(15) : \Illuminate\Support\Carbon::parse($preview['pickup']['arrival_time']))->format('H:i d/m/Y') }}
        </dd>
    </div>
    <div>
        <dt>{{ __('Payment::payment.payment_dropoff') }}</dt>
        <dd>{{ $summaryDropoff['name'] }}</dd>
    </div>
    <div class="payment-page__address-row">
        <dd>{{ $summaryDropoff['address'] ?: __('Payment::payment.payment_address_pending') }}</dd>
    </div>
    <div class="payment-page__total-row payment-page__trip-total">
        <dt>{{ __('core::booking.trip_total') }}</dt>
        <dd>{{ $summaryTotal }}</dd>
    </div>
</dl>
