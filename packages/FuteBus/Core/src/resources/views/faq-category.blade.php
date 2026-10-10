@extends('core::layouts.home')

@section('title', __('core::faq-category.meta.title', ['category' => $category->localized('name')]))
@section('meta_description', __('core::faq-category.meta.description', ['category' => $category->localized('name')]))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="px-3 py-5 sm:px-6">
            <section
                class="mx-auto min-h-150 w-full max-w-282 overflow-hidden rounded-2xl border border-slate-300 bg-white"
                x-data="{
                    active: 0,
                    query: '',
                    sidebarOpen: true,
                    questions: {{ Js::from($questions) }},
                    get filteredQuestions() {
                        const keyword = this.query.trim().toLocaleLowerCase();
                        return this.questions
                            .map((question, index) => ({ ...question, originalIndex: index }))
                            .filter(question => question.question.toLocaleLowerCase().includes(keyword));
                    },
                    selectQuestion(index) {
                        this.active = index;
                        if (window.innerWidth < 768) {
                            this.$nextTick(() => this.$refs.answer.scrollIntoView({ behavior: 'smooth', block: 'start' }));
                        }
                    },
                }"
            >
                <header class="grid items-center gap-4 bg-[#f1f1f4] px-4 py-4 md:grid-cols-[1fr_1.5fr]">
                    <a href="{{ route('faq') }}" class="text-lg font-extrabold text-gray-950 hover:text-futa-orange">
                        {{ __('core::faq-category.back', ['category' => $category->localized('name')]) }}
                    </a>
                    <label class="relative block">
                        <span class="sr-only">{{ __('core::faq-category.search_label') }}</span>
                        <x-heroicon-o-magnifying-glass
                            class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-gray-400"
                        />
                        <input
                            type="search"
                            x-model="query"
                            placeholder="{{ __('core::faq-category.search_placeholder', ['category' => $category->localized('name')]) }}"
                            class="h-12 w-full rounded-2xl border border-futa-orange/70 bg-white pl-10 pr-4 text-sm
                                font-semibold text-gray-950 outline-none transition placeholder:text-gray-400
                                focus:border-futa-orange focus:ring-3 focus:ring-futa-orange/10"
                        >
                    </label>
                </header>

                <div class="grid md:grid-cols-[400px_1fr]">
                    <aside class="border-b border-slate-200 md:border-b-0 md:border-r">
                        <button
                            type="button"
                            @click="sidebarOpen = !sidebarOpen"
                            class="flex w-full items-center justify-between border-b border-slate-100 px-6 py-4
                                text-left text-xl font-extrabold uppercase text-futa-orange"
                            :aria-expanded="sidebarOpen"
                        >
                            <span>{{ __('core::faq-category.heading') }}</span>
                            <x-heroicon-s-chevron-up
                                class="size-4 transition-transform duration-200"
                                ::class="sidebarOpen ? '' : 'rotate-180'"
                            />
                        </button>
                        <div
                            x-show="sidebarOpen"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                        >
                            <template x-for="question in filteredQuestions" :key="question.originalIndex">
                                <button
                                    type="button"
                                    @click="selectQuestion(question.originalIndex)"
                                    class="block w-full truncate border-r-2 px-12 py-3.5 text-left text-sm font-semibold
                                        transition hover:bg-futa-orange-soft hover:text-futa-orange"
                                    :class="active === question.originalIndex
                                        ? 'border-futa-orange bg-futa-orange-soft text-futa-orange'
                                        : 'border-transparent text-gray-950'"
                                    x-text="question.question"
                                ></button>
                            </template>
                            <p x-show="filteredQuestions.length === 0" class="px-6 py-8 text-center text-gray-500">
                                {{ __('core::faq-category.no_results') }}
                            </p>
                        </div>
                    </aside>

                    <article x-ref="answer" class="scroll-mt-4 px-5 py-6 sm:px-8 md:px-5">
                        <h2 class="text-2xl font-extrabold leading-tight text-gray-950 sm:text-[27px]"
                            x-text="questions[active].question"></h2>
                        <div class="mt-6 space-y-3 text-base font-semibold leading-7 text-gray-950">
                            <template x-for="paragraph in questions[active].answer" :key="paragraph">
                                <p x-text="paragraph"></p>
                            </template>
                        </div>
                    </article>
                </div>
            </section>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
