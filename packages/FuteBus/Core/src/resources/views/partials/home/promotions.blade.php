{{-- Khuyến mãi nổi bật --}}
@if($promotions->isNotEmpty())
<section class="bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Title --}}
        <div class="mb-8 text-center">
            <h2 class="text-2xl font-black uppercase tracking-wider text-[#1E603C] sm:text-3xl">
                {{ __('core::app.home.promotions.title') }}
            </h2>
        </div>

        {{-- Carousel --}}
        <div x-data="{ active: 0, total: {{ $promotions->count() }}, perPage: 3 }" class="relative">
            {{-- Cards --}}
            <div class="overflow-hidden">
                <div
                    class="flex transition-transform duration-500 ease-in-out"
                    :style="`transform: translateX(-${active * (100 / perPage)}%)`"
                >
                    @foreach($promotions as $promo)
                    <div class="w-full shrink-0 px-3 sm:w-1/2 lg:w-1/3">
                        <div class="group h-full overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-lg">
                            <a href="{{ $promo->link ?? '#' }}" class="block">
                                <div class="relative aspect-video overflow-hidden bg-gray-100">
                                    @if($promo->image)
                                        <img
                                            src="{{ asset('storage/' . $promo->image) }}"
                                            alt="{{ $promo->title }}"
                                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                        >
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-orange-100 to-orange-200">
                                            <span class="text-orange-400">
                                                <x-heroicon-o-megaphone class="h-12 w-12" />
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="line-clamp-2 text-sm font-bold text-gray-800 group-hover:text-orange-600 transition">
                                        {{ $promo->title }}
                                    </h3>
                                    @if($promo->description)
                                        <p class="mt-1.5 line-clamp-2 text-xs text-gray-500">
                                            {{ $promo->description }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Dots --}}
            @if($promotions->count() > 3)
            <div class="mt-6 flex justify-center gap-2">
                @php
                    $totalSlides = (int) ceil($promotions->count() / 3);
                @endphp
                @for($i = 0; $i < $totalSlides; $i++)
                    <button
                        type="button"
                        @click="active = {{ $i * 3 }}; if(active >= total) active = 0"
                        :class="active === {{ $i * 3 }} ? 'bg-orange-500 w-8' : 'bg-gray-300 w-3'"
                        class="h-3 rounded-full transition-all duration-300"
                    ></button>
                @endfor
            </div>
            @endif
        </div>
    </div>
</section>
@endif
