{{-- Khuyến mãi nổi bật --}}
@if($promotions->isNotEmpty())
<section class="bg-white py-10 sm:py-12">
    <div class="mx-auto w-full max-w-282 px-4 sm:px-6 lg:px-0">
        {{-- Title --}}
        <div class="mb-7 text-center sm:mb-8">
            <h2 class="text-2xl font-extrabold uppercase leading-tight text-[#00613d] xl:text-3xl">
                {{ __('core::app.home.promotions.title') }}
            </h2>
        </div>

        {{-- Carousel --}}
        <div
            class="relative"
            x-data="{
                active: 0,
                perPage: 3,
                total: {{ $promotions->count() }},
                totalPages: 1,
                init() {
                    this.syncLayout();
                },
                syncLayout() {
                    this.perPage = window.innerWidth >= 1024 ? 3 : window.innerWidth >= 640 ? 2 : 1;
                    this.totalPages = Math.max(1, Math.ceil(this.total / this.perPage));
                    this.active = Math.min(this.active, this.totalPages - 1);
                },
            }"
            @resize.window.debounce.150ms="syncLayout()"
        >
            {{-- Cards --}}
            <div class="overflow-hidden">
                <div
                    class="flex transition-transform duration-500 ease-in-out"
                    :style="`transform: translateX(-${active * 100}%)`"
                >
                    @foreach($promotions as $promo)
                    @php
                        $promoImageExists = $promo->image
                            && Storage::disk('public')->exists($promo->image);
                    @endphp
                    <div class="w-full shrink-0 px-2.5 sm:w-1/2 lg:w-1/3">
                        <div class="group h-full overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-lg">
                            <a href="{{ $promo->link ?? '#' }}" class="block">
                                <div class="relative h-44 overflow-hidden bg-gray-100 sm:h-48">
                                    @if($promoImageExists)
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
            <div class="mt-6 flex justify-center gap-2" x-show="totalPages > 1">
                <template x-for="page in totalPages" :key="page">
                    <button
                        type="button"
                        class="h-2.5 rounded-full transition-all duration-300"
                        :class="active === page - 1 ? 'w-7 bg-[#ef5222]' : 'w-2.5 bg-gray-300'"
                        @click="active = page - 1"
                        :aria-label="`Trang ${page}`"
                        :aria-current="active === page - 1 ? 'page' : null"
                    ></button>
                </template>
            </div>
        </div>
    </div>
</section>
@endif
