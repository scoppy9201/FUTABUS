<button type="button" class="payment-page__info-button"
    @click.stop="tripTooltipOpen = !tripTooltipOpen; priceTooltipOpen = false"
    :aria-expanded="tripTooltipOpen" aria-controls="payment-trip-tooltip"
    aria-label="{{ __('core::trip-search.transfer_information') }}">
    <img src="{{ asset('vendor/blade-heroicons/o-information-circle.svg') }}" alt="" aria-hidden="true">
</button>
<div id="payment-trip-tooltip" class="payment-page__tooltip"
    x-show="tripTooltipOpen" x-transition.opacity x-cloak
    @click.outside="tripTooltipOpen = false"
    @keydown.escape.window="tripTooltipOpen = false" role="tooltip">
    <h3>{{ __('core::trip-search.transfer_information') }}</h3>
    <ul>
        @foreach (__('core::trip-search.transfer_items') as $item)
            <li>
                <strong>{{ $item['label'] }}:</strong>
                <em>{{ $item['detail'] }}</em>
            </li>
        @endforeach
    </ul>
</div>
