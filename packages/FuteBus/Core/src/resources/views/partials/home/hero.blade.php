{{-- Hero Section: Banner + Search Form --}}
<section class="bg-linear-to-br from-orange-500 via-orange-400 to-orange-600 pb-8">
    {{-- Banner --}}
    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl shadow-lg">
            <img
                src="{{ asset('images/banners/home-banner.jpg') }}"
                alt="FUTA Group"
                class="h-auto w-full object-cover"
            >
        </div>
    </div>

    {{-- Search Form --}}
    <div class="mx-auto max-w-4xl px-4 pt-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-orange-100">
            {{-- Trip type --}}
            <div class="mb-5 flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="trip_type" value="one_way" checked class="h-4 w-4 border-orange-400 text-orange-500 focus:ring-orange-400">
                    <span class="text-sm font-semibold text-gray-700">{{ __('core::app.home.hero.one_way') }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="trip_type" value="round_trip" class="h-4 w-4 border-orange-400 text-orange-500 focus:ring-orange-400">
                    <span class="text-sm font-semibold text-gray-700">{{ __('core::app.home.hero.round_trip') }}</span>
                </label>
                <a href="#" class="ml-auto text-sm font-bold text-orange-500 hover:text-orange-600 underline-offset-2 hover:underline">
                    {{ __('core::app.home.hero.guide') }}
                </a>
            </div>

            {{-- Fields --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Diem di --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">{{ __('core::app.home.hero.from') }}</label>
                    <div class="relative">
                        <input
                            type="text"
                            placeholder="{{ __('core::app.home.hero.from_placeholder') }}"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 placeholder-gray-400 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100"
                        >
                    </div>
                </div>

                {{-- Doi chieu --}}
                <div class="hidden sm:flex items-end justify-center pb-1">
                    <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-dashed border-orange-300 text-orange-400 transition hover:border-orange-500 hover:bg-orange-50 hover:text-orange-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                    </button>
                </div>

                {{-- Diem den --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">{{ __('core::app.home.hero.to') }}</label>
                    <div class="relative">
                        <input
                            type="text"
                            placeholder="{{ __('core::app.home.hero.to_placeholder') }}"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 placeholder-gray-400 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100"
                        >
                    </div>
                </div>

                {{-- Ngay di --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">{{ __('core::app.home.hero.date') }}</label>
                    <input
                        type="date"
                        value="{{ now()->format('Y-m-d') }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100"
                    >
                </div>

                {{-- So ve --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">{{ __('core::app.home.hero.quantity') }}</label>
                    <select class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100">
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ $i === 1 ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            {{-- Button --}}
            <div class="mt-6 flex justify-center">
                <button
                    type="button"
                    class="rounded-full bg-linear-to-r from-orange-500 to-orange-600 px-10 py-3 text-base font-bold text-white shadow-lg shadow-orange-500/30 transition hover:from-orange-600 hover:to-orange-700 hover:shadow-xl hover:shadow-orange-500/40 active:scale-[0.98]"
                >
                    {{ __('core::app.home.hero.search') }}
                </button>
            </div>
        </div>
    </div>
</section>
