            {{-- Language dropdown --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                    <button
                        type="button"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                        aria-haspopup="true"
                        aria-controls="language-menu"
                        class="flex items-center rounded-lg text-xs font-bold text-gray-600 hover:text-futa-orange focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-futa-orange"
                        aria-label="{{ __('core::app.home.navbar.language_selector') }}">
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                    <div
                        id="language-menu"
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="absolute left-0 top-full z-50 mt-2 w-40 overflow-hidden rounded-lg border border-white/20 bg-white py-1 shadow-xl"
                        x-cloak
                    >
                        <a
                            href="{{ request()->fullUrlWithQuery(['lang' => 'vi']) }}"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors focus-visible:bg-futa-orange-soft focus-visible:outline-none {{ app()->getLocale() === 'vi' ?
                                'bg-futa-orange-soft text-futa-orange' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="inline-flex size-5.5 items-center justify-center overflow-hidden rounded-full">
                                <img src="{{ asset('icons/flags/vi.svg') }}" alt="" class="h-full w-full object-cover">
                            </span>
                            <span>{{ __('StaffDashboard::language.vi') }}</span>
                        </a>
                        <a
                            href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors focus-visible:bg-futa-orange-soft focus-visible:outline-none {{ app()->getLocale() === 'en' ?
                                'bg-futa-orange-soft text-futa-orange' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="inline-flex size-5.5 items-center justify-center overflow-hidden rounded-full">
                                <img src="{{ asset('icons/flags/en.svg') }}" alt="" class="h-full w-full object-cover">
                            </span>
                            <span>{{ __('StaffDashboard::language.en') }}</span>
                        </a>
                    </div>
                </div>
