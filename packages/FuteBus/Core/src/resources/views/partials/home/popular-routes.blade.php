<section class="bg-[#fff8f5] px-4 pt-6 pb-8 sm:px-6 sm:pt-7 sm:pb-9" aria-labelledby="popular-routes-heading">
    <div class="mx-auto w-full max-w-282">
        <header class="mb-8 text-center">
            <h2 id="popular-routes-heading" class="text-[28px] font-extrabold uppercase leading-tight text-[#00613d] sm:text-[30px]">
                {{ __('core::app.home.popular_routes.title') }}
            </h2>
            <p class="mt-1.5 text-base text-gray-950">
                {{ __('core::app.home.popular_routes.subtitle') }}
            </p>
        </header>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3 lg:gap-5.5">
            @foreach ($popularRoutes as $group)
                <article class="overflow-hidden rounded-xl border border-[#dedede] bg-white shadow-[0_3px_5px_rgba(0,0,0,.25)]">
                    <div class="relative h-35 overflow-hidden bg-gray-200">
                        <img
                            src="{{ asset($group['image']) }}"
                            alt="{{ $group['city'] }}"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                        <div class="absolute inset-0 bg-linear-to-t from-black/60 via-transparent to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 px-4 pb-5 text-white">
                            <p class="text-sm font-bold leading-5">{{ __('core::app.home.popular_routes.from_label') }}</p>
                            <h3 class="mt-0.5 text-xl font-extrabold leading-6 drop-shadow-sm">{{ $group['city'] }}</h3>
                        </div>
                    </div>

                    <div class="divide-y divide-[#e1e4e8]">
                        @foreach ($group['routes'] as $route)
                            <div class="flex min-h-21.25 items-center justify-between gap-4 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-lg font-medium leading-6 text-[#00613d]">{{ $route['destination'] }}</p>
                                    <p class="mt-1 text-sm font-medium leading-5 text-[#64748b]">{{ $route['distance'] }}km - {{ $route['hours'] }} {{ __('core::app.home.popular_routes.hours') }}</p>
                                </div>
                                <p class="shrink-0 self-start pt-1 text-right text-[15px] font-semibold text-gray-950">{{ number_format($route['price'], 0, ',', '.') }}đ</p>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
