<dl>
    <div><dt>{{ __('core::booking.route') }}</dt><dd>{{ $origin }} - {{ $destination }}</dd></div>
    <div><dt>{{ __('core::booking.departure_time') }}</dt><dd>{{ $departure->format('H:i d/m/Y') }}</dd></div>
    <div>
        <dt>{{ __('core::booking.seat_count') }}</dt>
        <dd x-text="selectedIds.length + ' ' + @js(__('core::booking.seat_unit'))"></dd>
    </div>
    <div>
        <dt>{{ __('core::booking.seats') }}</dt>
        <dd x-text="selectedSeats().map(seat => seat.code).join(', ') || '—'"></dd>
    </div>
    <div class="booking-page__total">
        <dt>{{ __('core::booking.trip_total') }}</dt>
        <dd x-text="money(selectedIds.length * fare)"></dd>
    </div>
</dl>
