<section class="bg-[#fff8f5] py-10 sm:py-12">
    <div class="mx-auto w-full max-w-282 px-4 sm:px-6 lg:px-0">
        <header class="mb-7 text-center sm:mb-8">
            <h2 class="text-2xl font-extrabold uppercase leading-tight text-[#00613d] xl:text-3xl">
                {{ __('core::app.home.popular_routes.title') }}
            </h2>
            <p class="mt-2 text-sm text-[#4a342e] sm:text-base">
                {{ __('core::app.home.popular_routes.subtitle') }}
            </p>
        </header>

        @if($popularRoutes->isEmpty())
            <div class="rounded-xl border border-dashed border-orange-200 bg-white px-6 py-10 text-center text-sm text-gray-500">
                {{ __('core::app.home.popular_routes.empty') }}
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach($popularRoutes as $group)
                    @php
                        $cityImages = [
                            'Hồ Chí Minh' => 'images/popular-routes/ho-chi-minh-city.png',
                            'Đà Lạt' => 'images/popular-routes/da-lat.png',
                            'Đà Nẵng' => 'images/popular-routes/da-nang.png',
                        ];
                        $fallbackImages = [
                            'images/popular-routes/ho-chi-minh-city.png',
                            'images/popular-routes/da-lat.png',
                            'images/popular-routes/da-nang.png',
                        ];
                        $cityImage = $cityImages[$group['city']] ?? $fallbackImages[$loop->index % count($fallbackImages)];
                    @endphp

                    <article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-[0_3px_8px_rgba(0,0,0,.22)]">
                        <div class="relative h-35 overflow-hidden bg-gray-200">
                            <img
                                src="{{ asset($cityImage) }}"
                                alt="{{ $group['city'] }}"
                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy"
                            >
                            <div class="absolute inset-0 bg-linear-to-t from-black/70 via-black/15 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 px-4 pb-5 text-white">
                                <p class="text-sm font-bold leading-5">
                                    {{ __('core::app.home.popular_routes.from_label') }}
                                </p>
                                <h3 class="mt-0.5 text-xl font-extrabold leading-6 drop-shadow-sm">
                                    {{ $group['city'] }}
                                </h3>
                            </div>
                        </div>

                        <div class="divide-y divide-[#e5e7eb]">
                            @foreach($group['routes']->take(3) as $route)
                                @php
                                    $duration = null;

                                    if ($route->duration_minutes) {
                                        $hours = intdiv((int) $route->duration_minutes, 60);
                                        $minutes = (int) $route->duration_minutes % 60;
                                        $duration = collect([
                                            $hours > 0 ? $hours . ' giờ' : null,
                                            $minutes > 0 ? $minutes . ' phút' : null,
                                        ])->filter()->implode(' ');
                                    }
                                @endphp

                                <div class="flex min-h-21.25 items-center justify-between gap-4 px-4 py-3 transition-colors hover:bg-[#fff9f6]">
                                    <div class="min-w-0">
                                        <p class="truncate text-[17px] font-medium leading-6 text-[#00613d]">
                                            {{ $route->destination_city }}
                                        </p>
                                        @if($route->distance_km || $duration)
                                            <p class="mt-1 text-sm leading-5 text-[#637083]">
                                                @if($route->distance_km)
                                                    {{ number_format($route->distance_km, 0, ',', '.') }}km
                                                @endif
                                                @if($route->distance_km && $duration)
                                                    <span aria-hidden="true"> • </span>
                                                @endif
                                                @if($duration)
                                                    {{ $duration }}
                                                @endif
                                            </p>
                                        @endif
                                    </div>

                                    <p class="shrink-0 text-right text-[15px] font-semibold text-gray-950">
                                        {{ number_format($route->base_price, 0, ',', '.') }}đ
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
