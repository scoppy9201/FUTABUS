@extends('core::layouts.home')

@section('title', __('Payment::topup-guide.title'))
@section('meta_description', __('Payment::topup-guide.description'))

@section('content')
    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-8 sm:px-6 lg:px-0">
            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
                <div class="px-5 pt-7 pb-9 text-center sm:px-10 sm:pt-9">
                    <h1 class="text-2xl font-bold uppercase leading-tight text-futa-orange sm:text-4xl">
                        {{ __('Payment::topup-guide.title') }}
                    </h1>

                    <section aria-labelledby="topup-download-title" class="mt-8 sm:mt-9">
                        <h2 id="topup-download-title" class="text-2xl font-extrabold leading-tight tracking-tight text-futa-green-dark sm:text-3xl">
                            {{ __('Payment::topup-guide.download_title') }}
                        </h2>
                        <p class="mt-10 text-sm font-medium uppercase text-gray-950 sm:mt-14 sm:text-base">
                            {{ __('Payment::topup-guide.download_intro') }}
                        </p>
                        <div class="mt-6 flex items-center justify-center gap-6 sm:gap-8">
                            <img
                                src="{{ asset('images/booking-guide/futa-logo.png') }}"
                                alt="{{ __('Payment::topup-guide.logo_alt') }}"
                                width="96"
                                height="96"
                                class="size-24 rounded-lg bg-white object-contain shadow-lg"
                            >
                            <img
                                src="{{ asset('images/booking-guide/futa-app-qr.png') }}"
                                alt="{{ __('Payment::topup-guide.qr_alt') }}"
                                width="96"
                                height="96"
                                class="size-24 rounded-lg bg-white object-contain shadow-lg"
                            >
                        </div>
                        <div class="mt-7 flex flex-wrap items-center justify-center gap-5">
                            <a
                                href="https://play.google.com/store/apps/details?id=client.facecar.com"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="{{ __('core::app.home.footer.google_play') }}"
                                class="rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange"
                            >
                                <img src="{{ asset('icons/stores/google-play.svg') }}" alt="" class="h-8 w-auto">
                            </a>
                            <a
                                href="https://apps.apple.com/vn/app/futa/id1126633800"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="{{ __('core::app.home.footer.app_store') }}"
                                class="rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange"
                            >
                                <img src="{{ asset('icons/stores/app-store.svg') }}" alt="" class="h-8 w-auto">
                            </a>
                        </div>
                    </section>
                </div>

                <section aria-labelledby="topup-login-title" class="bg-[#fff8f5] px-5 py-8 sm:px-10 sm:py-10">
                    <h2 id="topup-login-title" class="text-center text-2xl font-extrabold leading-tight tracking-tight text-futa-green-dark sm:text-3xl">
                        {{ __('Payment::topup-guide.login_title') }}
                    </h2>

                    <figure class="mx-auto mt-8 max-w-245 sm:mt-10">
                        <img
                            src="{{ asset('images/topup-guide/sign-in-with-phone.png') }}"
                            alt="{{ __('Payment::topup-guide.login_image_alt') }}"
                            width="1536"
                            height="1024"
                            loading="lazy"
                            class="h-auto w-full object-contain"
                        >
                    </figure>
                </section>

                <section aria-labelledby="topup-action-title" class="px-5 py-8 sm:px-10 sm:py-10">
                    <h2 id="topup-action-title" class="text-center text-2xl font-extrabold leading-tight tracking-tight text-futa-green-dark sm:text-3xl">
                        {{ __('Payment::topup-guide.action_title') }}
                    </h2>
                    <figure class="mx-auto mt-8 max-w-245 sm:mt-10">
                        <img
                            src="{{ asset('images/topup-guide/select-top-up.png') }}"
                            alt="{{ __('Payment::topup-guide.action_image_alt') }}"
                            width="1536"
                            height="1024"
                            loading="lazy"
                            class="h-auto w-full object-contain"
                        >
                    </figure>
                </section>

                <section aria-labelledby="topup-confirm-title" class="bg-[#fff8f5] px-5 py-8 sm:px-10 sm:py-10">
                    <h2 id="topup-confirm-title" class="text-center text-2xl font-extrabold leading-tight tracking-tight text-futa-green-dark sm:text-3xl">
                        {{ __('Payment::topup-guide.confirm_title') }}
                    </h2>
                    <figure class="mx-auto mt-8 max-w-280 sm:mt-10">
                        <img
                            src="{{ asset('images/topup-guide/enter-amount-and-payment-source.png') }}"
                            alt="{{ __('Payment::topup-guide.confirm_image_alt') }}"
                            width="1536"
                            height="1024"
                            loading="lazy"
                            class="h-auto w-full object-contain"
                        >
                    </figure>
                </section>
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
