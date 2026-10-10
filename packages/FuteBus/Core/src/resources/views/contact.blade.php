@extends('core::layouts.home')

@section('title', __('core::contact.meta.title'))
@section('meta_description', __('core::contact.meta.description'))

@section('content')
    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-282 px-4 py-8 sm:px-6 lg:px-0">
            <div class="contact-layout">
                <section x-data="{ companyOpen: true }" aria-labelledby="contact-details-title">
                    <h1 id="contact-details-title" class="text-lg font-extrabold uppercase text-gray-950">
                        {{ __('core::contact.title') }}
                    </h1>
                    <button
                        type="button"
                        class="mt-5 flex w-full items-center gap-3 text-left text-sm font-extrabold uppercase text-gray-900 transition-colors hover:text-futa-orange focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-futa-orange"
                        aria-controls="contact-company-details"
                        :aria-expanded="companyOpen.toString()"
                        @click="companyOpen = !companyOpen"
                    >
                        <x-heroicon-s-chevron-right
                            class="size-4 shrink-0 text-gray-400 transition-transform duration-300"
                            ::class="companyOpen ? 'rotate-90 text-futa-orange' : ''"
                        />
                        FUTA Bus Lines
                    </button>

                    <div
                        id="contact-company-details"
                        class="contact-company-panel"
                        :class="companyOpen ? 'is-open' : ''"
                    >
                        <div class="min-h-0 overflow-hidden">
                            <div class="pt-5 pl-6">
                                <h2 class="text-lg font-extrabold uppercase leading-7 text-futa-orange">
                                    {{ __('core::contact.company') }}
                                </h2>
                                <dl class="mt-5 space-y-2 text-base font-semibold leading-6 text-gray-900">
                            <div>
                                <dt class="inline text-gray-600">{{ __('core::contact.address_label') }}</dt>
                                <dd class="inline"> {{ __('core::contact.address') }}</dd>
                            </div>
                            <div>
                                <dt class="inline text-gray-600">{{ __('core::contact.website_label') }}</dt>
                                <dd class="inline"> <a href="https://futabus.vn" class="hover:text-futa-orange">https://futabus.vn/</a></dd>
                            </div>
                            <div>
                                <dt class="inline text-gray-600">{{ __('core::contact.phone_label') }}</dt>
                                <dd class="inline"> <a href="tel:02838386852" class="hover:text-futa-orange">02838386852</a></dd>
                            </div>
                            <div>
                                <dt class="inline text-gray-600">{{ __('core::contact.fax_label') }}</dt>
                                <dd class="inline"> 02838386853</dd>
                            </div>
                            <div>
                                <dt class="inline text-gray-600">{{ __('core::contact.email_label') }}</dt>
                                <dd class="inline"> <a href="mailto:hotro@futa.vn" class="hover:text-futa-orange">hotro@futa.vn</a></dd>
                            </div>
                            <div>
                                <dt class="inline text-gray-600">{{ __('core::contact.hotline_label') }}</dt>
                                <dd class="inline"> <a href="tel:19006067" class="font-extrabold hover:text-futa-orange">19006067</a></dd>
                            </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </section>

                <section aria-labelledby="contact-form-title">
                    <h2 id="contact-form-title" class="flex min-h-8 items-center gap-3 text-xl font-extrabold text-futa-orange">
                        <x-heroicon-o-envelope class="size-8 shrink-0" />
                        {{ __('core::contact.form_title') }}
                    </h2>

                    @if(session('contact_success'))
                        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800" role="status">
                            {{ session('contact_success') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="mt-4 rounded-xl bg-[#f3f3f3] p-5 sm:p-6" novalidate>
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="sr-only" for="department">{{ __('core::contact.department') }}</label>
                            <select id="department" name="department" class="h-11 rounded-lg border border-gray-300 bg-white px-4 text-sm font-bold outline-none transition focus:border-futa-orange focus:ring-3 focus:ring-futa-orange/10">
                                <option value="futabus">{{ __('core::contact.department') }}</option>
                            </select>

                            <div>
                                <label class="sr-only" for="contact-name">{{ __('core::contact.name') }}</label>
                                <input id="contact-name" name="name" value="{{ old('name', auth()->user()?->name) }}" placeholder="{{ __('core::contact.name') }}" aria-describedby="contact-name-error"
                                    @class(['h-11 w-full rounded-lg border bg-white px-4 text-sm font-semibold outline-none transition placeholder:text-gray-400 focus:ring-3 focus:ring-futa-orange/10',
                                        'border-red-500 focus:border-red-500' => $errors->has('name'),
                                        'border-gray-300 focus:border-futa-orange' => !$errors->has('name')]) required>
                                @error('name')
                                    <p id="contact-name-error" class="mt-1.5 text-xs font-semibold text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="sr-only" for="contact-email">{{ __('core::contact.email') }}</label>
                                <input id="contact-email" type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" placeholder="{{ __('core::contact.email') }}" aria-describedby="contact-email-error"
                                    @class(['h-11 w-full rounded-lg border bg-white px-4 text-sm font-semibold outline-none transition placeholder:text-gray-400 focus:ring-3 focus:ring-futa-orange/10',
                                        'border-red-500 focus:border-red-500' => $errors->has('email'),
                                        'border-gray-300 focus:border-futa-orange' => !$errors->has('email')]) required>
                                @error('email')
                                    <p id="contact-email-error" class="mt-1.5 text-xs font-semibold text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="sr-only" for="contact-phone">{{ __('core::contact.phone') }}</label>
                                <input id="contact-phone" type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="{{ __('core::contact.phone') }}" aria-describedby="contact-phone-error"
                                    @class(['h-11 w-full rounded-lg border bg-white px-4 text-sm font-semibold outline-none transition placeholder:text-gray-400 focus:ring-3 focus:ring-futa-orange/10',
                                        'border-red-500 focus:border-red-500' => $errors->has('phone'),
                                        'border-gray-300 focus:border-futa-orange' => !$errors->has('phone')]) required>
                                @error('phone')
                                    <p id="contact-phone-error" class="mt-1.5 text-xs font-semibold text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <label class="sr-only" for="contact-subject">{{ __('core::contact.subject') }}</label>
                        <input id="contact-subject" name="subject" value="{{ old('subject') }}" placeholder="{{ __('core::contact.subject') }}" aria-describedby="contact-subject-error"
                            @class(['mt-4 h-11 w-full rounded-lg border bg-white px-4 text-sm font-semibold outline-none transition placeholder:text-gray-400 focus:ring-3 focus:ring-futa-orange/10',
                                'border-red-500 focus:border-red-500' => $errors->has('subject'),
                                'border-gray-300 focus:border-futa-orange' => !$errors->has('subject')]) required>
                        @error('subject')
                            <p id="contact-subject-error" class="mt-1.5 text-xs font-semibold text-red-600" role="alert">{{ $message }}</p>
                        @enderror

                        <label class="sr-only" for="contact-message">{{ __('core::contact.message') }}</label>
                        <textarea id="contact-message" name="message" rows="5" placeholder="{{ __('core::contact.message') }}" aria-describedby="contact-message-error"
                            @class(['mt-4 w-full resize-y rounded-lg border bg-white px-4 py-3 text-sm font-semibold outline-none transition placeholder:text-gray-400 focus:ring-3 focus:ring-futa-orange/10',
                                'border-red-500 focus:border-red-500' => $errors->has('message'),
                                'border-gray-300 focus:border-futa-orange' => !$errors->has('message')]) required>{{ old('message') }}</textarea>
                        @error('message')
                            <p id="contact-message-error" class="mt-1.5 text-xs font-semibold text-red-600" role="alert">{{ $message }}</p>
                        @enderror

                        <div class="mt-6 text-center">
                            <button type="submit" class="contact-submit rounded-lg bg-futa-orange px-8 text-sm font-extrabold text-white shadow-sm transition hover:bg-futa-orange-dark focus:outline-none focus:ring-3 focus:ring-futa-orange/30">
                                {{ __('core::contact.send') }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
