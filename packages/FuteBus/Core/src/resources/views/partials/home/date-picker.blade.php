@php
    $isReturnDate = $picker === 'return';
    $initialValue = $isReturnDate ? '' : now()->format('Y-m-d');
    $minimumDate = now()->format('Y-m-d');
    $weekdays = app()->getLocale() === 'vi'
        ? ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']
        : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
@endphp

<div
    class="relative"
    :class="open ? 'z-50' : 'z-auto'"
    x-data="{
        open: false,
        value: @js($initialValue),
        minDate: @js($minimumDate),
        viewYear: {{ now()->year }},
        viewMonth: {{ now()->month - 1 }},
        weekdays: @js($weekdays),
        locale: @js(app()->getLocale()),
        toIso(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        openPicker() {
            if (this.open) {
                this.open = false;
                return;
            }
            const source = this.value || this.minDate;
            const [year, month] = source.split('-').map(Number);
            this.viewYear = year;
            this.viewMonth = month - 1;
            this.open = true;
            this.$nextTick(() => this.$refs.calendar.focus({ preventScroll: true }));
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
        choose(day) {
            if (day.iso < this.minDate) return;
            this.value = day.iso;
            this.open = false;
            this.$nextTick(() => this.$refs.trigger.focus({ preventScroll: true }));
            if (@js(!$isReturnDate)) this.$dispatch('hero-departure-date', day.iso);
        },
        format(value) {
            if (!value) return '';
            const [year, month, day] = value.split('-');
            return `${day}/${month}/${year}`;
        },
        weekday(value) {
            if (!value) return '';
            const [year, month, day] = value.split('-').map(Number);
            return new Intl.DateTimeFormat(this.locale === 'vi' ? 'vi-VN' : 'en-US', {
                weekday: 'short',
            }).format(new Date(year, month - 1, day));
        },
    }"
    @if($isReturnDate)
        @hero-departure-date.window="
            minDate = $event.detail;
            if (value && value < minDate) value = '';
        "
    @endif
    @click.outside="open = false"
    @keydown.escape.window="if (open) { open = false; $refs.trigger.focus({ preventScroll: true }); }"
>
    <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ $label }}</label>
    <input
        type="hidden"
        name="{{ $name }}"
        :value="value"
        @if($isReturnDate) :disabled="!roundTrip" @endif
    >
    <button
        x-ref="trigger"
        type="button"
        @click="openPicker"
        :aria-expanded="open"
        aria-haspopup="dialog"
        class="flex h-16.75 w-full items-center justify-between rounded-[10px] border border-gray-300 bg-white px-4.5 text-left outline-none transition hover:border-[#ff8a65] focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10"
    >
        <span class="min-w-0">
            <span
                class="block truncate font-bold leading-tight"
                :class="value ? 'text-[22px] text-gray-900' : 'text-base text-gray-400'"
                x-text="value ? format(value) : @js($placeholder)"
            ></span>
            <span
                x-show="value"
                class="mt-0.5 block text-[13px] font-medium capitalize leading-tight text-gray-600"
                x-text="weekday(value)"
            ></span>
        </span>
        <x-heroicon-o-calendar-days class="size-5 shrink-0 text-gray-400" />
    </button>

    <div
        x-ref="calendar"
        x-cloak
        x-show="open"
        x-transition:enter="transition-[opacity,transform] duration-220 ease-out"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-[.96]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition-[opacity,transform] duration-150 ease-in"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-[.98]"
        role="dialog"
        aria-label="{{ $label }}"
        tabindex="-1"
        class="absolute {{ $isReturnDate ? 'right-0 origin-top-right' : 'left-0 origin-top-left' }} top-0 z-50 w-96 max-w-[calc(100vw-32px)] rounded-xl border border-gray-200 bg-white p-3 shadow-[0_18px_42px_rgba(15,23,42,.22)] outline-none"
    >
        <div class="rounded-lg border border-[#ff8a65] bg-[#fffaf7] px-4 py-3 ring-3 ring-[#ef5222]/10">
            <span class="text-xs font-bold text-gray-600">{{ $label }}</span>
            <span class="mt-1 block text-base font-semibold" :class="value ? 'text-gray-950' : 'text-gray-400'" x-text="value ? format(value) : @js($placeholder)"></span>
        </div>

        <div class="mt-4 flex items-center justify-between px-2">
            <button type="button" @click="moveMonth(-1)" class="grid size-9 place-items-center rounded-full text-gray-500 transition hover:bg-orange-50 hover:text-[#ef5222]">
                <x-heroicon-o-chevron-left class="size-5" />
            </button>
            <p class="font-extrabold uppercase text-gray-800" x-text="title()"></p>
            <button type="button" @click="moveMonth(1)" class="grid size-9 place-items-center rounded-full text-gray-500 transition hover:bg-orange-50 hover:text-[#ef5222]">
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
                    :disabled="day.iso < minDate"
                    class="relative grid aspect-square place-items-center border-b border-r border-gray-200 text-sm font-semibold transition"
                    :class="{
                        'bg-[#fff3ed] font-extrabold text-[#ef5222]': day.iso === value,
                        'text-gray-900 hover:bg-orange-50 hover:text-[#ef5222]': day.current && day.iso >= minDate && day.iso !== value,
                        'text-gray-300': !day.current || day.iso < minDate,
                        'cursor-not-allowed bg-gray-50/70': day.iso < minDate,
                    }"
                >
                    <span x-text="day.number"></span>
                </button>
            </template>
        </div>
    </div>
</div>
