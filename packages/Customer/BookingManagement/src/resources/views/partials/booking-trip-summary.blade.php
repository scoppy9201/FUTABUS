@php
    $summaryTrip = $summaryTrip ?? $trip;
    $summaryIsOutbound = ($summaryLeg ?? '') === 'outbound';
    $summaryDeparture = \Illuminate\Support\Carbon::parse($summaryTrip['departure_time']);
@endphp
<dl>
    <div><dt>{{ __('core::booking.route') }}</dt><dd>{{ $summaryTrip['origin'] }} - {{ $summaryTrip['destination'] }}</dd></div>
    <div><dt>{{ __('core::booking.departure_time') }}</dt><dd>{{ $summaryDeparture->format('H:i d/m/Y') }}</dd></div>
    <div>
        <dt>{{ __('core::booking.seat_count') }}</dt>
        <dd x-text="{{ $summaryIsOutbound ? 'outboundSelectedIds' : 'selectedIds' }}.length + ' ' + @js(__('core::booking.seat_unit'))"></dd>
    </div>
    <div>
        <dt>{{ __('core::booking.seats') }}</dt>
        <dd x-text="{{ $summaryIsOutbound ? 'selectedOutboundSeats' : 'selectedSeats' }}().map(seat => seat.code).join(', ') || '—'"></dd>
    </div>
    <div class="booking-page__total">
        <dt>{{ __('core::booking.trip_total') }}</dt>
        <dd x-text="money({{ $summaryIsOutbound ? 'outboundSelectedIds.length * outboundFare' : 'selectedIds.length * fare' }})"></dd>
    </div>
</dl>
