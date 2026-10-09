<div id="booking-terms-modal" class="booking-page__modal-backdrop booking-page__terms-backdrop"
    x-show="termsModalOpen" x-transition.opacity x-cloak
    @click.self="closeTermsModal()" @keydown.escape.window="if (termsModalOpen) closeTermsModal()"
    role="presentation">
    <div class="booking-page__terms-dialog" role="dialog" aria-modal="true"
        aria-labelledby="booking-terms-title">
        <div class="booking-page__terms-dialog-heading">
            <h2 id="booking-terms-title">{{ __('core::booking.customer_rights_title') }}</h2>
            <button type="button" class="booking-page__modal-close" x-ref="termsModalClose"
                @click="closeTermsModal()" aria-label="{{ __('core::booking.close') }}">
                <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
            </button>
        </div>
        <div class="booking-page__terms-dialog-body" tabindex="0"
            :class="{ 'is-scrolling': termsScrolling }" @scroll.passive="showTermsScrollbar()">
            <ol>
                @foreach (__('core::booking.customer_rights') as $clause)
                    <li>
                        @include('core::partials.booking-linked-text', ['text' => $clause['body']])
                        @if (!empty($clause['note']))
                            <p>@include('core::partials.booking-linked-text', ['text' => $clause['note']])</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
