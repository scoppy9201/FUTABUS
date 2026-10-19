<div x-show="openPanel === 'policy'" class="trip-card__detail-content">
    <h3>{{ __('core::trip-search.cancellation_policy') }}</h3>
    <ul>
        @foreach (__('core::trip-search.cancellation_items') as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    <h3>{{ __('core::trip-search.boarding_requirements') }}</h3>
    <ul>
        @foreach (__('core::trip-search.boarding_items') as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    <h3>{{ __('core::trip-search.carry_on_luggage') }}</h3>
    <ul>
        @foreach (__('core::trip-search.luggage_items') as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    <h3>{{ __('core::trip-search.children_and_pregnancy') }}</h3>
    <ul>
        @foreach (__('core::trip-search.children_and_pregnancy_items') as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    <h3>{{ __('core::trip-search.roadside_pickup') }}</h3>
    <ul>
        @foreach (__('core::trip-search.roadside_pickup_items') as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
</div>
