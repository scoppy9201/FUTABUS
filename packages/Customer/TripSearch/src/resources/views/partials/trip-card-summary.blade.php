<div class="trip-card__body">
    <div class="trip-card__journey">
        <div class="trip-card__timeline">
            <time class="trip-card__time" x-text="trip.departure_hour"></time>
            <div class="trip-card__track" aria-hidden="true">
                <span class="trip-card__origin-mark"></span>
                <span class="trip-card__track-line"></span>
                <span class="trip-card__duration">
                    <span x-text="clockDuration(trip.duration_minutes) + (trip.distance_km ? ' - ' + trip.distance_km + 'Km' : '')"></span>
                    <small>(Asia/Ho_Chi_Minh)</small>
                </span>
                <img class="trip-card__destination-mark" src="{{ asset('images/trip-destination-pin.png') }}" alt="">
            </div>
            <time class="trip-card__time" x-text="trip.arrival_hour"></time>
        </div>
        <div class="trip-card__stations">
            <p x-text="stationLabel(trip.origin)"></p>
            <p x-text="stationLabel(trip.destination)"></p>
        </div>
        <p class="trip-card__notice">{{ __('core::trip-search.trip_notice') }}</p>
    </div>
    <div class="trip-card__summary">
        <p class="trip-card__meta">
            <span x-text="vehicleLabel(trip.vehicle_type)"></span>
            <span class="trip-card__seats" x-text="trip.available_seats + ' ' + @js(__('core::trip-search.available_seats'))"></span>
        </p>
        <p class="trip-card__price" x-text="money(trip.price)"></p>
    </div>
</div>
