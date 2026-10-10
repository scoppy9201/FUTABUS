<div id="booking-trip-detail-modal" class="booking-page__modal-backdrop"
    x-show="tripDetailOpen" x-transition.opacity x-cloak
    @click.self="closeTripDetail()" @keydown.escape.window="if (tripDetailOpen) closeTripDetail()"
    role="presentation">
    <div class="booking-page__modal" role="dialog" aria-modal="true"
        aria-labelledby="booking-trip-detail-title">
        <div class="booking-page__modal-heading">
            <h2 id="booking-trip-detail-title">
                {{ __('core::booking.trip_details_title', ['count' => 1]) }}
            </h2>
            <button type="button" class="booking-page__info-button"
                @click.stop="policyTooltipOpen = !policyTooltipOpen"
                :aria-expanded="policyTooltipOpen" aria-controls="booking-policy-tooltip"
                aria-label="{{ __('core::trip-search.cancellation_policy') }}">
                <img class="booking-page__info-icon" src="{{ asset('icons/booking-info.png') }}"
                    alt="" aria-hidden="true">
            </button>
            <div id="booking-policy-tooltip" class="booking-page__policy-tooltip"
                x-show="policyTooltipOpen" x-transition.opacity x-cloak
                @click.outside="policyTooltipOpen = false" role="tooltip">
                <h3>{{ __('core::trip-search.cancellation_policy') }}</h3>
                <ul>
                    @foreach (__('core::trip-search.cancellation_items') as $item)
                        <li>@include('BookingManagement::partials.booking-linked-text', ['text' => $item])</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="booking-page__modal-close" x-ref="tripDetailClose"
                @click="closeTripDetail()" aria-label="{{ __('core::booking.close') }}">
                <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
            </button>
        </div>
        <div class="booking-panel booking-page__summary booking-page__modal-summary">
            @include('BookingManagement::partials.booking-trip-summary')
        </div>
    </div>
</div>
