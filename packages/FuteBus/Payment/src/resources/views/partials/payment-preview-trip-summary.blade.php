<dl>
    <div>
        <dt>{{ __('core::booking.route') }}</dt>
        <dd>{{ $trip['origin'] }} - {{ $trip['destination'] }}</dd>
    </div>
    <div>
        <dt>{{ __('core::booking.departure_time') }}</dt>
        <dd class="payment-page__green">{{ $departure->format('H:i d/m/Y') }}</dd>
    </div>
    <div>
        <dt>{{ __('core::booking.seat_count') }}</dt>
        <dd>{{ count($preview['seats']) }} {{ __('core::booking.seat_unit') }}</dd>
    </div>
    <div>
        <dt>{{ __('core::booking.seats') }}</dt>
        <dd class="payment-page__green">{{ implode(', ', $preview['seats']) }}</dd>
    </div>
    <div>
        <dt>{{ __('Payment::payment.payment_pickup') }}</dt>
        <dd>{{ $preview['pickup']['name'] }}</dd>
    </div>
    <div class="payment-page__address-row">
        <dd>{{ $preview['pickup']['address'] ?: __('Payment::payment.payment_address_pending') }}</dd>
    </div>
    <div>
        <dt>{{ __('Payment::payment.payment_boarding_time') }}</dt>
        <dd class="payment-page__boarding-time">
            {{ __('core::booking.before_time') }}
            {{ \Illuminate\Support\Carbon::parse($preview['pickup']['arrival_time'])->format('H:i d/m/Y') }}
        </dd>
    </div>
    <div>
        <dt>{{ __('Payment::payment.payment_dropoff') }}</dt>
        <dd>{{ $preview['dropoff']['name'] }}</dd>
    </div>
    <div class="payment-page__address-row">
        <dd>{{ $preview['dropoff']['address'] ?: __('Payment::payment.payment_address_pending') }}</dd>
    </div>
    <div class="payment-page__total-row payment-page__trip-total">
        <dt>{{ __('core::booking.trip_total') }}</dt>
        <dd>{{ $formattedTotal }}</dd>
    </div>
</dl>
