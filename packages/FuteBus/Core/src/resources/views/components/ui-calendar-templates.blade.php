<template id="futa-ui-calendar-template">
    <div class="futa-control futa-date-control" data-ui-date-control>
        <button type="button" data-ui-trigger aria-haspopup="dialog" aria-expanded="false"
            class="futa-control__trigger futa-date-trigger">
            <span data-ui-value class="futa-control__value"></span>
            <span class="futa-date-trigger__icon" aria-hidden="true">
                <x-heroicon-o-calendar-days data-ui-date-icon class="size-5" />
                <x-heroicon-o-clock data-ui-time-icon class="size-5" hidden />
            </span>
        </button>
        <div data-ui-panel popover="manual" role="dialog" aria-label="{{ __('core::ui.calendar') }}"
            data-choose="{{ __('core::ui.choose') }}" data-invalid-date="{{ __('core::ui.invalid_date') }}"
            data-today="{{ __('core::ui.today') }}" data-current-month="{{ __('core::ui.current_month') }}"
            data-now="{{ __('core::ui.now') }}" data-month-label="{{ __('core::ui.month') }}"
            class="futa-calendar-panel">
            <div data-ui-date-calendar>
                <div class="futa-calendar-heading">
                    <button type="button" data-ui-date-previous class="futa-calendar-nav" aria-label="{{ __('core::ui.previous_month') }}">
                        <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" />
                    </button>
                    <div class="futa-calendar-title">
                        <span data-ui-date-month aria-live="polite"></span>
                        <input data-ui-date-year type="number" min="1" max="9999" step="1" inputmode="numeric" form=""
                            aria-label="{{ __('core::ui.year') }}" class="futa-calendar-year">
                    </div>
                    <button type="button" data-ui-date-next class="futa-calendar-nav" aria-label="{{ __('core::ui.next_month') }}">
                        <x-heroicon-o-chevron-right class="size-4" aria-hidden="true" />
                    </button>
                </div>
                <div data-ui-date-weekdays class="futa-calendar-weekdays" aria-hidden="true"></div>
                <div data-ui-date-days class="futa-calendar-days"></div>
                <div data-ui-date-months class="futa-calendar-months" hidden></div>
            </div>
            <div data-ui-date-time class="futa-calendar-time" hidden>
                @foreach(['hour' => 23, 'minute' => 59, 'second' => 59, 'millisecond' => 999] as $part => $maximum)
                    <label data-ui-time-part="{{ $part }}" class="futa-calendar-time__field">
                        <span>{{ __('core::ui.'.$part) }}</span>
                        <input data-ui-time-value="{{ $part }}" type="number" min="0" max="{{ $maximum }}" step="1" inputmode="numeric" form="">
                    </label>
                @endforeach
            </div>
            <p data-ui-date-status role="status" class="futa-calendar-status" hidden></p>
            <footer class="futa-calendar-footer">
                <button type="button" data-ui-date-today class="futa-calendar-action"></button>
                <button type="button" data-ui-date-clear class="futa-calendar-action">{{ __('core::ui.clear') }}</button>
                <button type="button" data-ui-date-apply class="futa-calendar-apply">{{ __('core::ui.apply') }}</button>
            </footer>
        </div>
        <span data-ui-error role="alert" class="futa-control__error" hidden></span>
    </div>
</template>
