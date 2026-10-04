<style>
    .hero-form-border {
        border: 2px solid #ff8a65;
        box-shadow: 0 8px 0 rgba(181,86,51,.14);
    }

    .hero-return-field {
        min-width: 0;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
        transition: opacity 220ms ease, margin 300ms ease;
    }

    .hero-search-grid.is-round-trip .hero-return-field {
        overflow: visible;
        opacity: 1;
        pointer-events: auto;
    }

    @media (max-width: 1023px) {
        .hero-search-grid:not(.is-round-trip) .hero-return-field {
            display: none;
        }
    }

    @media (min-width: 1024px) {
        .hero-search-grid {
            grid-template-columns:
                minmax(0, 1fr) 22px minmax(0, 1fr)
                minmax(0, 1fr) minmax(0, 0fr) minmax(0, 1fr);
            transition: grid-template-columns 300ms ease;
        }

        .hero-search-grid .hero-return-field {
            margin-inline: -7.5px;
        }

        .hero-search-grid.is-round-trip {
            grid-template-columns:
                minmax(0, 1fr) 22px minmax(0, 1fr)
                minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);
        }

        .hero-search-grid.is-round-trip .hero-return-field {
            margin-inline: 0;
        }
    }
</style>

@php
    $today = now();
    $isoDay = (int) $today->isoFormat('d');
    $dayOfWeek = $isoDay === 7 ? 'CN' : 'Thứ '.($isoDay + 1);
@endphp

<section
    class="px-3 pt-2 pb-14.5 sm:px-4"
    x-data="{
        roundTrip: false,
        returnDate: '',
        departure: '',
        destination: '',
        swapLocations() {
            [this.departure, this.destination] = [this.destination, this.departure];
        },
    }"
>
    <div class="mx-auto aspect-1128/310 w-full max-w-282 overflow-hidden rounded-xl border border-white/60 bg-[#fff7f1] shadow-[0_6px_14px_rgba(67,31,18,.26)] max-sm:aspect-16/7">
        <img
            src="{{ asset('images/banners/home-banner.jpg') }}"
            alt="{{ __('core::app.home.hero.banner_alt') }}"
            class="h-full w-full object-cover object-[center_35%] max-sm:object-center"
        >
    </div>

    <form class="relative mx-auto mt-8 w-full max-w-282 rounded-[18px] bg-white px-6 pt-6.5 pb-10.5 hero-form-border max-sm:px-4" action="#" method="GET">
        <div class="mb-5.25 flex items-center justify-between gap-4">
            <div class="flex items-center gap-7 max-sm:gap-4">
                <label class="flex cursor-pointer items-center gap-2 font-bold transition-colors duration-200" :class="!roundTrip ? 'text-[#ef5222]' : 'text-gray-500'">
                    <input
                        type="radio"
                        name="trip_type"
                        value="one_way"
                        checked
                        class="h-4.25 w-4.25 accent-[#ef5222]"
                        @change="roundTrip = false"
                    >
                    <span>{{ __('core::app.home.hero.one_way') }}</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 font-bold transition-colors duration-200" :class="roundTrip ? 'text-[#ef5222]' : 'text-gray-500'">
                    <input
                        type="radio"
                        name="trip_type"
                        value="round_trip"
                        class="h-4.25 w-4.25 accent-[#ef5222]"
                        @change="roundTrip = true"
                    >
                    <span>{{ __('core::app.home.hero.round_trip') }}</span>
                </label>
            </div>
            <a href="{{ route('booking-guide') }}" class="text-sm font-medium text-[#ef5222] hover:underline focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ef5222]">{{ __('core::app.home.hero.guide') }}</a>
        </div>

        <div
            class="hero-search-grid grid grid-cols-1 items-end gap-3.75 md:grid-cols-2"
            :class="{ 'is-round-trip': roundTrip }"
        >
            <div>
                <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ __('core::app.home.hero.from') }}</label>
                <input
                    type="text"
                    name="departure"
                    x-model="departure"
                    placeholder="{{ __('core::app.home.hero.from_placeholder') }}"
                    class="h-16.75 w-full rounded-[10px] border border-gray-300 bg-white px-4.5 text-base text-gray-900 outline-none placeholder:text-center placeholder:text-gray-400 focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10"
                >
            </div>

            <button
                type="button"
                @click="swapLocations"
                class="group z-10 mb-3.75 -mx-1.75 hidden size-9.25 place-items-center rounded-full border border-gray-200 bg-white text-[#ef5222] shadow-sm transition hover:border-[#ef5222] hover:shadow-md lg:grid"
                aria-label="{{ __('core::app.home.hero.swap_aria') }}"
            >
                <x-heroicon-o-arrows-right-left
                    class="size-4.75 transition-transform duration-300 ease-out group-hover:rotate-180"
                />
            </button>

            <div>
                <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ __('core::app.home.hero.to') }}</label>
                <input
                    type="text"
                    name="destination"
                    x-model="destination"
                    placeholder="{{ __('core::app.home.hero.to_placeholder') }}"
                    class="h-16.75 w-full rounded-[10px] border border-gray-300 bg-white px-4.5 text-base text-gray-900 outline-none placeholder:text-center placeholder:text-gray-400 focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10"
                >
            </div>

            @include('core::partials.home.date-picker', [
                'picker' => 'departure',
                'name' => 'departure_date',
                'label' => __('core::app.home.hero.date'),
                'placeholder' => __('core::app.home.hero.date'),
            ])

            <div
                class="hero-return-field"
                :aria-hidden="(!roundTrip).toString()"
                x-cloak
            >
                @include('core::partials.home.date-picker', [
                    'picker' => 'return',
                    'name' => 'return_date',
                    'label' => __('core::app.home.hero.return_date'),
                    'placeholder' => __('core::app.home.hero.return_placeholder'),
                ])
            </div>

            <div
                class="relative"
                x-data="{ open: false, selected: 1 }"
                @click.away="open = false"
                @keydown.escape.window="open = false"
            >
                <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ __('core::app.home.hero.quantity') }}</label>
                <input type="hidden" name="quantity" :value="selected">
                <button
                    type="button"
                    @click="open = !open"
                    :aria-expanded="open"
                    aria-haspopup="listbox"
                    class="flex h-16.75 w-full items-center justify-between rounded-[10px] border border-gray-300 bg-white px-4.5 text-lg font-medium text-gray-900 outline-none transition-colors hover:border-[#ff8a65] focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10"
                >
                    <span x-text="selected"></span>
                    <span class="flex size-7.5 items-center justify-center rounded-lg bg-gray-100">
                        <x-heroicon-o-chevron-down
                            class="size-4 text-gray-500 transition-transform duration-200"
                            ::class="open ? 'rotate-180' : ''"
                        />
                    </span>
                </button>
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    role="listbox"
                    class="scrollbar-hidden absolute left-0 right-0 top-full z-30 mt-2 max-h-60 overflow-auto rounded-[10px] border border-gray-200 bg-white py-1.5 shadow-[0_8px_20px_rgba(0,0,0,.14)]"
                    style="display: none;"
                >
                    @for($i = 1; $i <= 5; $i++)
                        <button
                            type="button"
                            role="option"
                            :aria-selected="selected === {{ $i }}"
                            @click="selected = {{ $i }}; open = false"
                            class="flex w-full items-center justify-between px-4 py-3 text-left text-lg transition-colors"
                            :class="selected === {{ $i }} ? 'bg-[#fff6f1] font-semibold text-[#ef5222]' : 'text-gray-800 hover:bg-gray-50'"
                        >
                            <span>{{ $i }}</span>
                            <span
                                x-show="selected === {{ $i }}"
                                class="grid size-5.5 place-items-center rounded-full bg-[#ef5222] text-white"
                            >
                                <x-heroicon-s-check class="size-3.5" />
                            </span>
                        </button>
                    @endfor
                </div>
            </div>
        </div>

        <button type="submit" class="absolute -bottom-6 left-1/2 h-12.25 w-[calc(100%-48px)] max-w-66 -translate-x-1/2 rounded-full bg-[#ef5222] text-base font-extrabold text-white shadow-[0_8px_18px_rgba(239,82,34,.28)] transition hover:-translate-y-0.5 hover:bg-[#e94512]">
            {{ __('core::app.home.hero.search') }}
        </button>
    </form>
</section>
