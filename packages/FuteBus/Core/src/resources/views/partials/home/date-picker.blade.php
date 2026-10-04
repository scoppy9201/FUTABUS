@php
    $today = now()->format('Y-m-d');
    $weekdays = app()->getLocale() === 'vi'
        ? ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']
        : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
@endphp

<div
    class="contents"
    x-data="{
        calendarOpen: false,
        activeField: 'departure',
        departureDate: @js($initialSearch['departure_date'] ?? $today),
        returnDate: @js($initialSearch['return_date'] ?? ''),
        today: @js($today),
        viewYear: {{ now()->year }},
        viewMonth: {{ now()->month - 1 }},
        weekdays: @js($weekdays),
        locale: @js(app()->getLocale()),
        init() {
            this.$watch('roundTrip', (enabled) => {
                if (!enabled) {
                    this.calendarOpen = false;
                    this.activeField = 'departure';
                }
            });
        },
        toIso(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return year + '-' + month + '-' + day;
        },
        openCalendar(field) {
            if (this.calendarOpen && this.activeField === field) {
                this.calendarOpen = false;
                return;
            }
            this.focusCalendar(field);
        },
        focusCalendar(field) {
            this.selectField(field);
            this.calendarOpen = true;
            this.$nextTick(() => this.$refs.calendar.focus({ preventScroll: true }));
        },
        restoreDates(detail) {
            this.departureDate = detail.departureDate;
            this.returnDate = detail.returnDate;
            this.calendarOpen = false;
            this.selectField('departure');
        },
        selectField(field) {
            this.activeField = field;
            const source = field === 'return'
                ? (this.returnDate || this.departureDate)
                : this.departureDate;
            const [year, month] = source.split('-').map(Number);
            this.viewYear = year;
            this.viewMonth = month - 1;
        },
        moveMonth(offset) {
            const date = new Date(this.viewYear, this.viewMonth + offset, 1);
            this.viewYear = date.getFullYear();
            this.viewMonth = date.getMonth();
        },
        title() {
            return new Intl.DateTimeFormat(this.locale === 'vi' ? 'vi-VN' : 'en-US', {
                month: 'long',
                year: 'numeric',
            }).format(new Date(this.viewYear, this.viewMonth, 1));
        },
        days() {
            const first = new Date(this.viewYear, this.viewMonth, 1);
            const offset = (first.getDay() + 6) % 7;
            const start = new Date(this.viewYear, this.viewMonth, 1 - offset);
            return Array.from({ length: 42 }, (_, index) => {
                const date = new Date(start);
                date.setDate(start.getDate() + index);
                return {
                    iso: this.toIso(date),
                    number: date.getDate(),
                    current: date.getMonth() === this.viewMonth,
                };
            });
        },
        minimumDate() {
            return this.activeField === 'return' ? this.departureDate : this.today;
        },
        selectedDate() {
            return this.activeField === 'return' ? this.returnDate : this.departureDate;
        },
        choose(day) {
            if (day.iso < this.minimumDate()) return;
            if (this.activeField === 'departure') {
                this.departureDate = day.iso;
                if (this.returnDate && this.returnDate < day.iso) this.returnDate = '';
                if (this.roundTrip) {
                    this.activeField = 'return';
                    return;
                }
                this.calendarOpen = false;
                this.$nextTick(() => this.$refs.departureTrigger.focus({ preventScroll: true }));
                return;
            }
            this.returnDate = day.iso;
            this.calendarOpen = false;
            this.$nextTick(() => this.$refs.returnTrigger.focus({ preventScroll: true }));
        },
        format(value) {
            if (!value) return '';
            const [year, month, day] = value.split('-');
            return day + '/' + month + '/' + year;
        },
        weekday(value) {
            if (!value) return '';
            const [year, month, day] = value.split('-').map(Number);
            return new Intl.DateTimeFormat(this.locale === 'vi' ? 'vi-VN' : 'en-US', {
                weekday: 'short',
            }).format(new Date(year, month - 1, day));
        },
    }"
    @hero-open-calendar.window="focusCalendar($event.detail.field)"
    @hero-restore-dates.window="restoreDates($event.detail)"
    @click.document="if (calendarOpen && !$el.contains($event.target)) calendarOpen = false"
    @keydown.escape.window="if (calendarOpen) { calendarOpen = false; (activeField === 'return' ? $refs.returnTrigger : $refs.departureTrigger).focus({ preventScroll: true }); }"
>
    <div class="relative" :class="calendarOpen ? 'z-50' : 'z-auto'">
        <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ __('core::app.home.hero.date') }}</label>
        <input type="hidden" name="departure_date" :value="departureDate">
        <button
            x-ref="departureTrigger"
            type="button"
            @click="openCalendar('departure')"
            :aria-expanded="calendarOpen && activeField === 'departure'"
            aria-haspopup="dialog"
            class="flex h-16.75 w-full items-center justify-between rounded-[10px] border border-gray-300 bg-white px-4.5 text-left outline-none transition hover:border-[#ff8a65] focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10"
        >
            <span class="min-w-0">
                <span class="block truncate text-[22px] font-bold leading-tight text-gray-900" x-text="format(departureDate)"></span>
                <span class="mt-0.5 block text-[13px] font-medium capitalize leading-tight text-gray-600" x-text="weekday(departureDate)"></span>
            </span>
            <x-heroicon-o-calendar-days class="size-5 shrink-0 text-gray-400" />
        </button>

        <div
            x-ref="calendar"
            x-cloak
            x-show="calendarOpen"
            x-transition:enter="transition-[opacity,transform] duration-220 ease-out"
            x-transition:enter-start="opacity-0 -translate-y-2 scale-[.96]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition-[opacity,transform] duration-150 ease-in"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-1 scale-[.98]"
            role="dialog"
            :aria-label="activeField === 'return' ? @js(__('core::app.home.hero.return_date')) : @js(__('core::app.home.hero.date'))"
            tabindex="-1"
            class="absolute left-0 top-0 z-50 max-w-[calc(100vw-32px)] origin-top-left rounded-xl border border-gray-200 bg-white p-3 shadow-[0_18px_42px_rgba(15,23,42,.22)] outline-none"
            :class="roundTrip ? 'w-[430px]' : 'w-96'"
        >
            <div class="grid gap-3" :class="roundTrip ? 'grid-cols-2' : 'grid-cols-1'">
                <button
                    type="button"
                    @click="selectField('departure')"
                    class="min-w-0 rounded-lg border px-3 py-3 text-left transition-colors"
                    :class="activeField === 'departure' ? 'border-[#ff8a65] bg-[#fffaf7] ring-3 ring-[#ef5222]/10' : 'border-gray-200 bg-white hover:border-[#ff8a65]'"
                >
                    <span class="block text-xs font-bold text-gray-700">{{ __('core::app.home.hero.date') }}</span>
                    <span class="mt-1 block truncate text-sm font-semibold" :class="departureDate ? 'text-gray-950' : 'text-gray-400'" x-text="departureDate ? format(departureDate) : @js(__('core::app.home.hero.date'))"></span>
                </button>
                <button
                    x-show="roundTrip"
                    type="button"
                    @click="selectField('return')"
                    class="min-w-0 rounded-lg border px-3 py-3 text-left transition-colors"
                    :class="activeField === 'return' ? 'border-[#ff8a65] bg-[#fffaf7] ring-3 ring-[#ef5222]/10' : 'border-gray-200 bg-white hover:border-[#ff8a65]'"
                >
                    <span class="block text-xs font-bold text-gray-700">{{ __('core::app.home.hero.return_date') }}</span>
                    <span class="mt-1 block truncate text-sm font-semibold" :class="returnDate ? 'text-gray-950' : 'text-gray-400'" x-text="returnDate ? format(returnDate) : @js(__('core::app.home.hero.return_placeholder'))"></span>
                </button>
            </div>

            <div class="mt-4 flex items-center justify-between px-2">
                <button type="button" @click="moveMonth(-1)" class="grid size-9 place-items-center rounded-full text-gray-500 transition hover:bg-orange-50 hover:text-[#ef5222]" aria-label="{{ __('core::app.home.hero.calendar_previous_month') }}">
                    <x-heroicon-o-chevron-left class="size-5" />
                </button>
                <p class="font-extrabold uppercase text-gray-800" x-text="title()"></p>
                <button type="button" @click="moveMonth(1)" class="grid size-9 place-items-center rounded-full text-gray-500 transition hover:bg-orange-50 hover:text-[#ef5222]" aria-label="{{ __('core::app.home.hero.calendar_next_month') }}">
                    <x-heroicon-o-chevron-right class="size-5" />
                </button>
            </div>

            <div class="mt-3 grid grid-cols-7 text-center text-sm font-bold text-gray-600">
                <template x-for="weekdayName in weekdays" :key="weekdayName">
                    <span class="py-2" x-text="weekdayName"></span>
                </template>
            </div>

            <div class="grid grid-cols-7 overflow-hidden rounded-lg border-l border-t border-gray-200">
                <template x-for="day in days()" :key="day.iso">
                    <button
                        type="button"
                        @click="choose(day)"
                        :disabled="day.iso < minimumDate()"
                        class="relative grid aspect-square place-items-center border-b border-r border-gray-200 text-sm font-semibold transition"
                        :class="{
                            'bg-[#fff3ed] font-extrabold text-[#ef5222]': day.iso === selectedDate(),
                            'bg-orange-50 text-[#ef5222]': roundTrip && day.iso === departureDate && activeField === 'return',
                            'text-gray-900 hover:bg-orange-50 hover:text-[#ef5222]': day.current && day.iso >= minimumDate() && day.iso !== selectedDate(),
                            'text-gray-300': !day.current || day.iso < minimumDate(),
                            'cursor-not-allowed bg-gray-50/70': day.iso < minimumDate(),
                        }"
                    >
                        <span x-text="day.number"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <div class="hero-return-field" :aria-hidden="(!roundTrip).toString()" x-cloak>
        <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ __('core::app.home.hero.return_date') }}</label>
        <input type="hidden" name="return_date" :value="returnDate" :disabled="!roundTrip">
        <button
            x-ref="returnTrigger"
            type="button"
            @click="openCalendar('return')"
            :aria-expanded="calendarOpen && activeField === 'return'"
            :tabindex="roundTrip ? 0 : -1"
            aria-haspopup="dialog"
            class="flex h-16.75 w-full items-center justify-between rounded-[10px] border border-gray-300 bg-white px-4.5 text-left outline-none transition hover:border-[#ff8a65] focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10"
        >
            <span class="min-w-0">
                <span class="block truncate font-bold leading-tight" :class="returnDate ? 'text-[22px] text-gray-900' : 'text-base text-gray-400'" x-text="returnDate ? format(returnDate) : @js(__('core::app.home.hero.return_placeholder'))"></span>
                <span x-show="returnDate" class="mt-0.5 block text-[13px] font-medium capitalize leading-tight text-gray-600" x-text="weekday(returnDate)"></span>
            </span>
            <x-heroicon-o-calendar-days class="size-5 shrink-0 text-gray-400" />
        </button>
    </div>
</div>
