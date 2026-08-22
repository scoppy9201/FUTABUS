@php
    $qualityStats = __('core::app.home.service_quality.stats');
    $qualityImages = [
        'images/service-quality/passengers.png',
        'images/service-quality/ticket-offices.png',
        'images/service-quality/daily-trips.png',
    ];
@endphp

<section class="bg-white py-10 sm:py-12">
    <div class="mx-auto w-full max-w-282 px-4 sm:px-6 lg:px-0">
        <header class="text-center">
            <h2 class="text-2xl font-extrabold uppercase leading-tight text-[#00613d] xl:text-3xl">
                {{ __('core::app.home.service_quality.title') }}
            </h2>
            <p class="mt-2 text-sm text-[#4a342e] sm:text-base">
                {{ __('core::app.home.service_quality.subtitle') }}
            </p>
        </header>

        <div class="mt-8 grid items-center gap-8 min-[900px]:grid-cols-[1.05fr_.95fr] min-[900px]:gap-12">
            <div class="space-y-5 sm:space-y-6">
                @foreach($qualityStats as $stat)
                    <article class="flex items-center gap-4 sm:gap-5">
                        <div class="size-20 shrink-0 overflow-hidden rounded-full border border-[#f9ded4] bg-[#fff0eb] shadow-sm sm:size-24">
                            <img
                                src="{{ asset($qualityImages[$loop->index]) }}"
                                alt=""
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-baseline gap-x-2">
                                <h3 class="text-2xl font-black leading-tight text-gray-950 xl:text-[30px]">
                                    {{ $stat['value'] }}
                                </h3>
                                <span class="font-bold text-gray-950">{{ $stat['label'] }}</span>
                            </div>
                            <p class="mt-1 max-w-md text-sm leading-5 text-[#637083] xl:text-base xl:leading-6">
                                {{ $stat['description'] }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mx-auto flex h-72 w-full max-w-xl items-center justify-center sm:h-80 min-[900px]:h-88 xl:h-96">
                <img
                    src="{{ asset('images/service-quality/travel-illustration.png') }}"
                    alt="{{ __('core::app.home.service_quality.illustration_alt') }}"
                    class="max-h-full w-full object-contain"
                    loading="lazy"
                >
            </div>
        </div>
    </div>
</section>
