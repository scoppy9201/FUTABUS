<button type="button" class="payment-page__info-button"
    @click.stop="priceTooltipOpen = !priceTooltipOpen; tripTooltipOpen = false"
    :aria-expanded="priceTooltipOpen" aria-controls="payment-price-tooltip"
    aria-label="{{ __('core::trip-search.cancellation_policy') }}">
    <img src="{{ asset('vendor/blade-heroicons/o-information-circle.svg') }}" alt="" aria-hidden="true">
</button>
<div id="payment-price-tooltip" class="payment-page__tooltip"
    x-show="priceTooltipOpen" x-transition.opacity x-cloak
    @click.outside="priceTooltipOpen = false"
    @keydown.escape.window="priceTooltipOpen = false" role="tooltip">
    <h3>{{ __('core::trip-search.cancellation_policy') }}</h3>
    <ul>
        @foreach (__('core::trip-search.cancellation_items') as $item)
            <li>@include('core::partials.booking-linked-text', ['text' => $item])</li>
        @endforeach
    </ul>
</div>
