<section class="bg-white py-10 sm:py-12" aria-labelledby="featured-promotions-heading">
    <div class="mx-auto w-full max-w-282 px-4 sm:px-6 lg:px-0">
        <h2 id="featured-promotions-heading" class="mb-7 text-center text-2xl font-extrabold uppercase leading-tight text-[#00613d] sm:mb-8 xl:text-3xl">
            {{ __('core::app.home.promotions.title') }}
        </h2>

        <div
            x-data="{
                active: 0,
                perPage: 3,
                total: {{ $promotions->count() }},
                totalPages: 1,
                timer: null,
                init() { this.syncLayout(); this.start(); },
                destroy() { clearInterval(this.timer); },
                start() {
                    clearInterval(this.timer);
                    if (this.totalPages > 1) {
                        this.timer = setInterval(() => { this.active = (this.active + 1) % this.totalPages; }, 5000);
                    }
                },
                goTo(page) { this.active = page; this.start(); },
                syncLayout() {
                    this.perPage = window.innerWidth >= 1024 ? 3 : window.innerWidth >= 640 ? 2 : 1;
                    this.totalPages = Math.max(1, Math.ceil(this.total / this.perPage));
                    this.active = Math.min(this.active, this.totalPages - 1);
                },
            }"
            @resize.window.debounce.150ms="syncLayout()"
        >
            <div class="overflow-hidden">
                <div class="flex transition-transform duration-500 ease-in-out" :style="`transform: translateX(-${active * 100}%)`">
                    @foreach ($promotions as $promotion)
                        <div class="w-full shrink-0 px-2.5 sm:w-1/2 lg:w-1/3">
                            <a
                                href="{{ route('promotion-article', $promotion['slug']) }}"
                                aria-label="{{ $promotion['title'] }}"
                                class="block overflow-hidden rounded-xl bg-white shadow-[0_3px_7px_rgba(0,0,0,.3)] transition-shadow hover:shadow-[0_5px_12px_rgba(0,0,0,.3)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ef5222]"
                            >
                                <img
                                    src="{{ asset($promotion['image']) }}"
                                    alt="{{ $promotion['title'] }}"
                                    class="aspect-599/337 w-full object-cover"
                                    loading="lazy"
                                >
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex justify-center gap-2" x-show="totalPages > 1">
                <template x-for="page in totalPages" :key="page">
                    <button
                        type="button"
                        class="h-2.5 rounded-full transition-all duration-300"
                        :class="active === page - 1 ? 'w-7 bg-[#ef5222]' : 'w-2.5 bg-gray-300'"
                        @click="goTo(page - 1)"
                        :aria-label="`Trang ${page}`"
                        :aria-current="active === page - 1 ? 'page' : null"
                    ></button>
                </template>
            </div>
        </div>
    </div>
</section>
