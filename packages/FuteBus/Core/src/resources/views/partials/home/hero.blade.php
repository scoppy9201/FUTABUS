<style>
    .hero-form-border {
        border: 2px solid #ff8a65;
        box-shadow: 0 8px 0 rgba(181,86,51,.14);
    }
</style>

<section class="futa-hero-backdrop px-3 pt-2 pb-[58px] sm:px-4" x-data="{ roundTrip: false }">
    <div class="mx-auto aspect-1128/310 w-full max-w-[1128px] overflow-hidden rounded-xl border border-white/60 bg-[#fff7f1] shadow-[0_6px_14px_rgba(67,31,18,.26)] max-sm:aspect-16/7">
        <img
            src="{{ asset('images/banners/home-banner.jpg') }}"
            alt="{{ __('core::app.home.hero.banner_alt') }}"
            class="h-full w-full object-cover object-[center_35%] max-sm:object-center"
        >
    </div>

    <form class="relative mx-auto mt-8 w-full max-w-[1128px] rounded-[18px] bg-white px-6 pt-[26px] pb-[42px] hero-form-border max-sm:px-4" action="#" method="GET">
        <div class="mb-[21px] flex items-center justify-between gap-4">
            <div class="flex items-center gap-7 max-sm:gap-4">
                <label class="flex cursor-pointer items-center gap-2 font-bold transition-colors duration-200" :class="!roundTrip ? 'text-[#ef5222]' : 'text-gray-400'">
                    <input type="radio" name="trip_type" value="one_way" checked class="h-[17px] w-[17px] accent-[#ef5222]" @change="roundTrip = false">
                    <span>{{ __('core::app.home.hero.one_way') }}</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 font-bold transition-colors duration-200" :class="roundTrip ? 'text-[#ef5222]' : 'text-gray-400'">
                    <input type="radio" name="trip_type" value="round_trip" class="h-[17px] w-[17px] accent-[#ef5222]" @change="roundTrip = true">
                    <span>{{ __('core::app.home.hero.round_trip') }}</span>
                </label>
            </div>
            <a href="#" class="text-sm font-medium text-[#ef5222]">{{ __('core::app.home.hero.guide') }}</a>
        </div>

        @php
            $today = now();
            $isoDay = (int) $today->isoFormat('d');
            $dayOfWeek = $isoDay === 7 ? 'CN' : 'Thứ ' . ($isoDay + 1);
        @endphp

        <div class="grid grid-cols-1 items-end gap-[15px] md:grid-cols-2 lg:grid-cols-[1fr_22px_1fr_1fr_1fr]">
            <div>
                <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ __('core::app.home.hero.from') }}</label>
                <input type="text" name="departure" placeholder="{{ __('core::app.home.hero.from_placeholder') }}" class="h-[67px] w-full rounded-[10px] border border-gray-300 bg-white px-[18px] text-base text-gray-900 outline-none placeholder:text-center placeholder:text-gray-400 focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10">
            </div>

            <button type="button" class="z-10 mb-[15px] -mx-[7px] hidden h-[37px] w-[37px] place-items-center rounded-full border border-gray-200 bg-white text-[#ef5222] shadow-sm lg:grid" aria-label="{{ __('core::app.home.hero.swap_aria') }}">
                <svg class="h-[19px] w-[19px] fill-none stroke-current stroke-[1.8]" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h12m0 0-3-3m3 3-3 3M17 17H5m0 0 3 3m-3-3 3-3" /></svg>
            </button>

            <div>
                <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ __('core::app.home.hero.to') }}</label>
                <input type="text" name="destination" placeholder="{{ __('core::app.home.hero.to_placeholder') }}" class="h-[67px] w-full rounded-[10px] border border-gray-300 bg-white px-[18px] text-base text-gray-900 outline-none placeholder:text-center placeholder:text-gray-400 focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10">
            </div>

            <div>
                <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ __('core::app.home.hero.date') }}</label>
                <input type="hidden" name="departure_date" value="{{ $today->format('Y-m-d') }}">
                <div class="flex h-[67px] w-full items-center justify-between rounded-[10px] border border-gray-300 bg-white px-[18px] focus-within:border-[#ff8a65] focus-within:ring-3 focus-within:ring-[#ef5222]/10">
                    <div>
                        <span class="text-[22px] font-bold leading-tight text-gray-900">{{ $today->format('d/m/Y') }}</span>
                        <span class="block text-[13px] leading-tight text-gray-500">{{ $dayOfWeek }}</span>
                    </div>
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                </div>
            </div>

            <div class="relative" x-data="{ open: false, selected: 1 }" @click.away="open = false">
                <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ __('core::app.home.hero.quantity') }}</label>
                <input type="hidden" name="quantity" :value="selected">
                <button type="button" @click="open = !open" class="flex h-[67px] w-full items-center justify-between rounded-[10px] border border-gray-300 bg-white px-[18px] text-base text-gray-900 outline-none transition-colors hover:border-[#ff8a65] focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10">
                    <span x-text="selected"></span>
                    <span class="flex h-[30px] w-[30px] items-center justify-center rounded-lg bg-gray-100">
                        <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="absolute left-0 right-0 top-full z-30 mt-1 max-h-[200px] overflow-auto rounded-[10px] border border-gray-200 bg-white py-1 shadow-lg" style="display: none;">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" @click="selected = {{ $i }}; open = false" class="flex w-full items-center px-4 py-2.5 text-left text-[15px] transition-colors" :class="selected === {{ $i }} ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50'">
                            {{ $i }}
                        </button>
                    @endfor
                </div>
            </div>
        </div>

        {{-- Ngày về - overlay bên dưới, không phá grid --}}
        <div x-show="roundTrip" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-[15px] grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1fr_22px_1fr]" x-cloak>
            <div></div>
            <div></div>
            <div>
                <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ __('core::app.home.hero.return_date') }}</label>
                <input type="date" name="return_date" class="h-[67px] w-full rounded-[10px] border border-gray-300 bg-white px-[18px] text-base text-gray-900 outline-none focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10">
            </div>
        </div>

        <button type="submit" class="absolute bottom-[-24px] left-1/2 h-[49px] w-[calc(100%-48px)] max-w-[264px] -translate-x-1/2 rounded-full bg-[#ef5222] text-base font-extrabold text-white shadow-[0_8px_18px_rgba(239,82,34,.28)] transition hover:-translate-y-0.5 hover:bg-[#e94512]">
            {{ __('core::app.home.hero.search') }}
        </button>
    </form>
</section>
