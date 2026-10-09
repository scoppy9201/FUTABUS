<section
    aria-label="{{ $from }} - {{ $to }}"
    x-data="{
        trips: @js($trips),
        timeFilters: [],
        vehicleFilters: [],
        rowFilters: [],
        deckFilters: [],
        sortBy: 'departure',
        selectedTrip: null,
        expandedTrip: null,
        toggleFilter(group, value) {
            this[group] = this[group].includes(value)
                ? this[group].filter(item => item !== value)
                : [...this[group], value];
        },
        resetFilters() {
            this.timeFilters = [];
            this.vehicleFilters = [];
            this.rowFilters = [];
            this.deckFilters = [];
        },
        hasOption(field, value) {
            return this.trips.some(trip => trip[field].includes(value));
        },
        timeSlot(hour) {
            const time = Number(hour.slice(0, 2));
            return time < 6 ? 'early' : time < 12 ? 'morning' : time < 18 ? 'afternoon' : 'evening';
        },
        vehicleGroup(type) {
            return type === 'standard' || type === 'minivan' ? 'seat' : type;
        },
        visibleTrips() {
            const filtered = this.trips.filter(trip =>
                (!this.timeFilters.length || this.timeFilters.includes(this.timeSlot(trip.departure_hour)))
                && (!this.vehicleFilters.length || this.vehicleFilters.includes(this.vehicleGroup(trip.vehicle_type)))
                && (!this.rowFilters.length || this.rowFilters.some(row => trip.row_options.includes(row)))
                && (!this.deckFilters.length || this.deckFilters.some(deck => trip.deck_options.includes(deck)))
            );
            return filtered.sort((a, b) => {
                if (this.sortBy === 'price') return a.price - b.price || a.departure_time.localeCompare(b.departure_time);
                if (this.sortBy === 'seats') return b.available_seats - a.available_seats || a.departure_time.localeCompare(b.departure_time);
                return a.departure_time.localeCompare(b.departure_time);
            });
        },
        duration(minutes) {
            const hours = Math.floor(minutes / 60);
            const remaining = Math.round(minutes % 60);
            return hours + ' {{ __('core::trip-search.hours') }}' + (remaining ? ' ' + remaining + ' {{ __('core::trip-search.minutes') }}' : '');
        },
        vehicleLabel(type) {
            return type === 'limousine' ? 'Limousine'
                : type === 'sleeper' ? @js(__('core::trip-search.sleeper'))
                : @js(__('core::trip-search.seat'));
        },
        money(value) {
            return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
        },
    }"
    class="grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]"
>
    <aside class="self-start overflow-hidden rounded-xl bg-white shadow-[0_2px_7px_rgba(15,23,42,.18)]">
        <div class="flex items-center justify-between px-4 py-4">
            <h2 class="text-sm font-bold uppercase text-gray-950">{{ __('core::trip-search.filters') }}</h2>
            <button type="button" @click="resetFilters()" class="inline-flex items-center gap-1.5 text-sm font-medium text-[#ef5222] hover:underline">
                <span>{{ __('core::trip-search.clear_filters') }}</span>
                <x-heroicon-o-trash class="size-5 shrink-0" aria-hidden="true" />
            </button>
        </div>
        <div class="border-b border-gray-200 px-4 py-5">
            <h3 class="mb-3 text-sm font-semibold">{{ __('core::trip-search.departure_time') }}</h3>
            @foreach (['early', 'morning', 'afternoon', 'evening'] as $slot)
                <label class="mb-2 flex cursor-pointer items-center gap-2 text-sm text-gray-700 last:mb-0" :class="trips.length === 0 ? 'cursor-not-allowed opacity-40' : ''">
                    <input type="checkbox" value="{{ $slot }}" @change="toggleFilter('timeFilters', '{{ $slot }}')" :checked="timeFilters.includes('{{ $slot }}')" :disabled="trips.length === 0" class="size-4 accent-[#ef5222]">
                    <span>{{ __('core::trip-search.time_'.$slot) }}</span>
                </label>
            @endforeach
        </div>
        <div class="px-4 py-5">
            <h3 class="mb-3 text-sm font-semibold">{{ __('core::trip-search.vehicle') }}</h3>
            <div class="flex flex-wrap gap-2">
                @foreach (['seat', 'sleeper', 'limousine'] as $vehicle)
                    <button type="button" @click="toggleFilter('vehicleFilters', '{{ $vehicle }}')" :disabled="trips.length === 0" :aria-pressed="vehicleFilters.includes('{{ $vehicle }}')" :class="vehicleFilters.includes('{{ $vehicle }}') ? 'border-[#ef5222] bg-[#fff3ed] text-[#ef5222]' : 'border-gray-200 bg-white text-gray-800'" class="rounded-md border px-3 py-2 text-sm transition hover:border-[#ef5222] disabled:cursor-not-allowed disabled:opacity-40">{{ __('core::trip-search.'.$vehicle) }}</button>
                @endforeach
            </div>
        </div>
        <div class="border-t border-gray-200 px-4 py-5">
            <h3 class="mb-3 text-sm font-semibold">{{ __('core::trip-search.seat_row') }}</h3>
            <div class="flex flex-wrap gap-2">
                @foreach (['front', 'middle', 'back'] as $row)
                    <button type="button" @click="toggleFilter('rowFilters', '{{ $row }}')" :disabled="!hasOption('row_options', '{{ $row }}')" :aria-pressed="rowFilters.includes('{{ $row }}')" :class="rowFilters.includes('{{ $row }}') ? 'border-[#ef5222] bg-[#fff3ed] text-[#ef5222]' : 'border-gray-200 bg-white text-gray-800'" class="rounded-md border px-3 py-2 text-sm transition hover:border-[#ef5222] disabled:cursor-not-allowed disabled:opacity-40">{{ __('core::trip-search.row_'.$row) }}</button>
                @endforeach
            </div>
        </div>
        <div class="border-t border-gray-200 px-4 py-5">
            <h3 class="mb-3 text-sm font-semibold">{{ __('core::trip-search.deck') }}</h3>
            <div class="flex flex-wrap gap-2">
                @foreach (['upper', 'lower'] as $deck)
                    <button type="button" @click="toggleFilter('deckFilters', '{{ $deck }}')" :disabled="!hasOption('deck_options', '{{ $deck }}')" :aria-pressed="deckFilters.includes('{{ $deck }}')" :class="deckFilters.includes('{{ $deck }}') ? 'border-[#ef5222] bg-[#fff3ed] text-[#ef5222]' : 'border-gray-200 bg-white text-gray-800'" class="rounded-md border px-3 py-2 text-sm transition hover:border-[#ef5222] disabled:cursor-not-allowed disabled:opacity-40">{{ __('core::trip-search.deck_'.$deck) }}</button>
                @endforeach
            </div>
        </div>
    </aside>

    <div class="min-w-0">
        <h2 class="mb-3 text-xl font-semibold text-gray-950">{{ $from }} - {{ $to }}</h2>
        <div class="mb-6 flex flex-wrap gap-2.5">
            @foreach (['price', 'departure', 'seats'] as $sort)
                <button type="button" @click="sortBy = '{{ $sort }}'" :aria-pressed="sortBy === '{{ $sort }}'" @if ($sort === 'seats') :class="sortBy === 'seats' ? 'border-[#ffd8c8] bg-[#fff6f2] text-[#ef5222]' : 'border-gray-200 bg-white text-gray-900'" @endif class="inline-flex items-center gap-2 rounded-md border px-3.5 py-2 text-sm font-medium transition hover:border-[#ef5222] {{ $sort === 'seats' ? '' : 'border-[#ffd8c8] bg-[#fff6f2] text-[#ef5222]' }}">
                    @if ($sort === 'price')
                        <x-heroicon-o-banknotes class="size-5 shrink-0" aria-hidden="true" />
                    @elseif ($sort === 'departure')
                        <x-heroicon-o-clock class="size-5 shrink-0" aria-hidden="true" />
                    @else
                        <x-heroicon-o-ticket class="size-5 shrink-0" aria-hidden="true" />
                    @endif
                    <span>{{ __('core::trip-search.sort_'.$sort) }}</span>
                </button>
            @endforeach
        </div>

        <div x-show="visibleTrips().length === 0" class="min-h-96 rounded-xl bg-transparent py-16 text-center text-gray-500">
            <p class="text-lg font-semibold">{{ __('core::trip-search.no_results') }}</p>
            <p class="mt-2 text-sm">{{ __('core::trip-search.try_another') }}</p>
        </div>

        <div class="space-y-5" x-show="visibleTrips().length > 0">
            <template x-for="trip in visibleTrips()" :key="trip.id">
                <article class="rounded-xl border border-gray-200 bg-white px-4 py-4 shadow-sm sm:px-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex min-w-0 flex-1 items-start gap-3 sm:gap-5">
                            <div class="min-w-0 shrink-0">
                                <p class="text-2xl font-semibold text-gray-950" x-text="trip.departure_hour"></p>
                                <p class="mt-3 max-w-32 text-sm font-medium text-gray-900" x-text="trip.origin"></p>
                            </div>
                            <div class="min-w-20 flex-1 pt-2 text-center text-xs text-slate-500">
                                <p x-text="duration(trip.duration_minutes) + (trip.distance_km ? ' - ' + trip.distance_km + 'km' : '')"></p>
                                <div class="mt-2 border-t-2 border-dotted border-gray-200"></div>
                            </div>
                            <div class="min-w-0 shrink-0 text-right">
                                <p class="text-2xl font-semibold text-gray-950" x-text="trip.arrival_hour"></p>
                                <p class="mt-3 max-w-32 text-sm font-medium text-gray-900" x-text="trip.destination"></p>
                            </div>
                        </div>
                        <div class="ml-auto text-right">
                            <p class="text-sm text-slate-500"><span x-text="vehicleLabel(trip.vehicle_type)"></span> · <span class="font-semibold text-[#00613d]" x-text="trip.available_seats + ' ' + @js(__('core::trip-search.available_seats'))"></span></p>
                            <p class="mt-4 text-lg font-bold text-[#ef5222]" x-text="money(trip.price)"></p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-gray-200 pt-3">
                        <span class="text-sm text-gray-600" x-text="vehicleLabel(trip.vehicle_type)"></span>
                        <button type="button" @click="selectedTrip = selectedTrip === trip.id ? null : trip.id" :aria-pressed="selectedTrip === trip.id" :class="selectedTrip === trip.id ? 'bg-[#ef5222] text-white' : 'bg-[#fde9e2] text-[#ef5222]'" class="rounded-full px-5 py-2 text-sm font-semibold transition hover:bg-[#ef5222] hover:text-white" x-text="selectedTrip === trip.id ? @js(__('core::trip-search.selected')) : @js(__('core::trip-search.select_trip'))"></button>
                    </div>
                </article>
            </template>
        </div>
    </div>
</section>
