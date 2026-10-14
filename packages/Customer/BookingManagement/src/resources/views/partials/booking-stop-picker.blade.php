@php
    $options = $stopOptions[$stopTrip['id']][$stopKind];
    $initial = collect($options)->firstWhere('expired', false);
    $pickerId = 'booking-'.str_replace('_', '-', $stopFieldName);
@endphp
<div class="booking-page__stop-picker"
    x-data="{
        options: @js($options),
        selectedId: @js($initial['id'] ?? ''),
        search: '',
        open: false,
        init() {
            this.timer = setInterval(() => {
                this.options.forEach(option => option.expired = Date.now() >= option.expires_at);
                if (this.selected()?.expired) this.selectedId = '';
            }, 15000);
        },
        destroy() { clearInterval(this.timer) },
        selected() { return this.options.find(option => option.id === this.selectedId) },
        toggle() {
            this.open = !this.open;
            if (this.open) this.$nextTick(() => {
                this.$refs.search.focus();
            });
        },
        matches() {
            const normalize = value => String(value ?? '').normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '').replace(/\u0111/g, 'd').toLowerCase();
            const query = normalize(this.search.trim());
            return this.options.filter(option =>
                normalize(`${option.name} ${option.address ?? ''} ${option.time}`).includes(query)
            );
        },
        choose(option) {
            if (option.expired) return;
            this.selectedId = option.id;
            this.open = false;
            this.search = '';
        }
    }" @keydown.escape="open = false" @click.outside="open = false">
    <input type="hidden" name="{{ $stopFieldName }}" :value="selectedId">
    <button x-ref="trigger" type="button" class="booking-page__stop-trigger" :class="{ 'is-open': open }"
        @click="toggle()"
        :aria-expanded="open" aria-controls="{{ $pickerId }}-list"
        aria-label="{{ $stopLabel }}">
        <span x-text="selected()?.name ?? @js(__('core::booking.stop_select'))"></span>
        <span class="booking-page__stop-chevron" aria-hidden="true"></span>
    </button>
    <div id="{{ $pickerId }}-list" class="booking-page__stop-menu" x-show="open" x-cloak>
        <div class="booking-page__stop-search">
            <x-heroicon-o-magnifying-glass class="size-5" aria-hidden="true" />
            <input x-ref="search" type="search" x-model="search"
                aria-label="{{ __('core::booking.stop_search') }}"
                placeholder="{{ __('core::booking.stop_search') }}">
        </div>
        <div class="booking-page__stop-results">
            <template x-for="option in matches()" :key="option.id">
                <div class="booking-page__stop-option"
                    :class="{ 'is-selected': selectedId === option.id, 'is-expired': option.expired }">
                    <button type="button" class="booking-page__stop-choice" :disabled="option.expired"
                        @click="choose(option)">
                        <span class="booking-page__stop-radio" aria-hidden="true"></span>
                        <span class="booking-page__stop-copy">
                            <span class="booking-page__stop-option-title" x-text="option.time + ' - ' + option.name"></span>
                            <span class="booking-page__stop-option-address" x-text="option.address"></span>
                            <small x-show="option.expired">{{ __('core::booking.stop_expired') }}</small>
                        </span>
                    </button>
                    <a x-show="option.address && !option.expired"
                        :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(option.name + ', ' + option.address)"
                        target="_blank" rel="noopener noreferrer">{{ __('core::booking.stop_view_location') }}</a>
                    <span x-show="option.address && option.expired" class="booking-page__stop-location-disabled">
                        {{ __('core::booking.stop_view_location') }}
                    </span>
                </div>
            </template>
            <x-core::empty-state class="booking-page__stop-empty" x-show="matches().length === 0" :title="__('core::booking.stop_no_results')" compact />
        </div>
    </div>
    @if ($stopKind === 'pickup')
        <p class="booking-page__boarding-reminder" x-show="selected()">
            {{ __('core::booking.arrive_at') }} <strong x-text="selected()?.name"></strong>
            <em>{{ __('core::booking.before_time') }} <span x-text="selected()?.boarding_label"></span></em>
            {{ __('core::booking.boarding_help') }}
        </p>
    @endif
</div>
