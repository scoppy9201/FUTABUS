@extends('core::layouts.home')

@section('title', __('core::booking-guide.meta.title'))
@section('meta_description', __('core::booking-guide.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-10 sm:px-6 sm:py-12 lg:px-0">
            <article>
                <header class="text-center">
                    <h1 class="text-xl font-semibold uppercase leading-snug text-futa-orange sm:text-2xl">
                        {{ __('core::booking-guide.heading') }} <span class="whitespace-nowrap text-futa-green">FUTABUS.VN</span>
                    </h1>
                </header>

                <section aria-labelledby="guide-app-heading" class="mt-13 text-center sm:mt-15">
                    <h2 id="guide-app-heading" class="text-base font-semibold uppercase text-gray-950 sm:text-lg">
                        {{ __('core::booking-guide.app_heading') }}
                    </h2>
                    <div class="mt-6 flex items-center justify-center gap-8">
                        <img
                            src="{{ asset('images/booking-guide/futa-logo.png') }}"
                            alt="{{ __('core::booking-guide.logo_alt') }}"
                            width="96"
                            height="96"
                            class="size-24 rounded-lg bg-white object-contain p-1 shadow-[0_7px_16px_rgba(0,0,0,.12)]"
                        >
                        <img
                            src="{{ asset('images/booking-guide/futa-app-qr.png') }}"
                            alt="{{ __('core::booking-guide.qr_alt') }}"
                            width="96"
                            height="96"
                            class="size-24 rounded-lg bg-white object-contain p-1 shadow-[0_7px_16px_rgba(0,0,0,.12)]"
                        >
                    </div>
                    <div class="mt-7 flex items-center justify-center gap-8" aria-label="{{ __('core::booking-guide.app_heading') }}">
                        <a href="https://play.google.com/store/apps/details?id=client.facecar.com" target="_blank" rel="noopener noreferrer" aria-label="{{ __('core::app.home.footer.google_play') }}" class="inline-flex h-7 items-center gap-1 rounded-full bg-[#60b95e] px-2.5 text-xs font-medium text-white">
                            <svg viewBox="0 0 16 16" aria-hidden="true" class="size-3.5 fill-current"><path d="M2.5 1.5a.8.8 0 0 0-.4.7v11.6a.8.8 0 0 0 1.2.7l10-5.8a.8.8 0 0 0 0-1.4l-10-5.8a.8.8 0 0 0-.8 0Z" /></svg>
                            CH Play
                        </a>
                        <a href="https://apps.apple.com/vn/app/futa/id1126633800" target="_blank" rel="noopener noreferrer" aria-label="{{ __('core::app.home.footer.app_store') }}">
                            <img src="{{ asset('icons/stores/app-store.svg') }}" alt="App Store" class="h-7 w-auto">
                        </a>
                    </div>
                </section>

                <section aria-labelledby="guide-commitment-heading" class="mt-11 bg-[#fff7f4] px-4 py-8">
                    <h2 id="guide-commitment-heading" class="text-center text-lg font-medium uppercase text-futa-orange sm:text-xl">
                        {{ __('core::booking-guide.commitment') }}
                    </h2>
                    <div class="mt-4 space-y-2 text-[15px] leading-7 text-black sm:text-[17px]">
                        <p>{{ __('core::booking-guide.introduction.first') }}</p>
                        <p>
                            {{ __('core::booking-guide.introduction.second_before') }}
                            <span class="text-futa-orange">{{ __('core::booking-guide.introduction.website_name') }}</span>
                            {{ __('core::booking-guide.introduction.second_after') }}
                        </p>
                        <p>
                            {{ __('core::booking-guide.introduction.third_before') }}
                            <span class="text-futa-orange">{{ __('core::booking-guide.introduction.website_name') }}</span>
                            {{ __('core::booking-guide.introduction.third_after') }}
                        </p>
                    </div>
                </section>

                @php
                    $benefitIcons = [
                        'flexible-schedule.png',
                        'seat-selection.png',
                        'skip-queues.png',
                        'partner-offers.png',
                        'member-gifts.png',
                        'customer-feedback.png',
                    ];
                @endphp
                <section aria-labelledby="guide-benefits-heading" class="mt-12">
                    <h2 id="guide-benefits-heading" class="mx-auto max-w-full text-center text-[25px] font-bold leading-9 text-futa-green sm:text-[28px]">
                        {{ __('core::booking-guide.benefits.heading_before') }}
                        <span class="text-futa-orange">FUTA Bus</span>
                        <span class="whitespace-nowrap">{{ __('core::booking-guide.benefits.heading_middle') }}</span>
                        <br class="hidden lg:block">
                        <span class="text-futa-orange">futabus.vn</span>
                        {{ __('core::booking-guide.benefits.heading_after') }}
                    </h2>

                    <div class="mx-auto mt-15 grid w-full max-w-272 gap-x-5 gap-y-8 md:grid-cols-2 lg:grid-cols-3">
                        @foreach(__('core::booking-guide.benefits.items') as $benefit)
                            <div class="flex min-h-56 flex-col items-center rounded-2xl bg-white px-7 pt-8 pb-9 text-center shadow-[0_8px_30px_rgba(0,0,0,.09)]">
                                <img
                                    src="{{ asset('images/booking-guide/'.$benefitIcons[$loop->index]) }}"
                                    alt=""
                                    aria-hidden="true"
                                    width="56"
                                    height="56"
                                    class="size-14 shrink-0 object-contain"
                                    loading="lazy"
                                >
                                <p class="mt-5 text-[17px] leading-7 text-black">{{ $benefit }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section aria-labelledby="guide-booking-steps-heading" class="mt-16 bg-[#fff7f4] px-4 pt-12 pb-14 sm:px-7">
                    <h2 id="guide-booking-steps-heading" class="text-center text-[25px] font-bold leading-tight text-futa-green sm:text-[30px]">
                        {{ __('core::booking-guide.booking_steps.heading') }}
                    </h2>

                    <div class="mt-10 overflow-x-auto pb-2">
                        <div class="mx-auto w-full min-w-200 max-w-250">
                            <div class="relative aspect-2170/304 w-full overflow-hidden" aria-hidden="true">
                                <img
                                    src="{{ asset('images/booking-guide/booking-steps-timeline.png') }}"
                                    alt=""
                                    width="2170"
                                    height="725"
                                    class="absolute left-0 top-[-67.4%] w-full max-w-none"
                                >
                            </div>
                            <ol class="mt-3 grid grid-cols-5 gap-2 text-center text-[16px] leading-7 text-black sm:text-[17px]">
                                @foreach(__('core::booking-guide.booking_steps.labels') as $label)
                                    <li class="px-1">{{ $label }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>

                    <h3 class="mt-11 text-center text-[26px] font-bold leading-tight text-black sm:text-[30px]">
                        {{ __('core::booking-guide.booking_steps.first_step') }}
                        <span class="text-futa-orange">futabus.vn</span>
                    </h3>
                    <img
                        src="{{ asset('images/booking-guide/website-devices.png') }}"
                        alt="{{ __('core::booking-guide.booking_steps.devices_alt') }}"
                        width="1855"
                        height="848"
                        class="mx-auto mt-9 h-auto w-full max-w-225 object-contain"
                        loading="lazy"
                    >

                    <div class="mt-9 text-center">
                        <h3 class="text-[20px] font-bold leading-tight text-black sm:text-[24px]">
                            {{ __('core::booking-guide.journey_step.download_before') }}
                            <span class="text-futa-orange">futabus.vn</span>
                            {{ __('core::booking-guide.journey_step.download_after') }}<br>
                            {{ __('core::booking-guide.journey_step.download_second_line') }}
                            <span class="text-futa-orange">Google Play</span>
                            {{ __('core::booking-guide.journey_step.download_or') }}
                            <span class="text-futa-orange">Apple store</span>
                        </h3>
                        <div class="mt-4 flex items-center justify-center gap-8">
                            <a href="https://play.google.com/store/apps/details?id=client.facecar.com" target="_blank" rel="noopener noreferrer" aria-label="{{ __('core::app.home.footer.google_play') }}" class="inline-flex h-7 items-center gap-1 rounded-full bg-[#60b95e] px-2.5 text-xs font-medium text-white">
                                <svg viewBox="0 0 16 16" aria-hidden="true" class="size-3.5 fill-current"><path d="M2.5 1.5a.8.8 0 0 0-.4.7v11.6a.8.8 0 0 0 1.2.7l10-5.8a.8.8 0 0 0 0-1.4l-10-5.8a.8.8 0 0 0-.8 0Z" /></svg>
                                CH Play
                            </a>
                            <a href="https://apps.apple.com/vn/app/futa/id1126633800" target="_blank" rel="noopener noreferrer" aria-label="{{ __('core::app.home.footer.app_store') }}">
                                <img src="{{ asset('icons/stores/app-store.svg') }}" alt="App Store" class="h-7 w-auto">
                            </a>
                        </div>
                    </div>

                    <div class="mt-10 overflow-x-auto pb-2">
                        <div class="mx-auto w-full min-w-200 max-w-250">
                            <div class="relative aspect-2169/316 w-full overflow-hidden" aria-hidden="true">
                                <img
                                    src="{{ asset('images/booking-guide/booking-steps-timeline-active-02.png') }}"
                                    alt=""
                                    width="2169"
                                    height="725"
                                    class="absolute left-0 top-[-63%] w-full max-w-none"
                                    loading="lazy"
                                >
                            </div>
                            <ol class="mt-3 grid grid-cols-5 gap-2 text-center text-[16px] leading-7 text-black sm:text-[17px]">
                                @foreach(__('core::booking-guide.booking_steps.labels') as $label)
                                    <li class="px-1">{{ $label }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>

                    <h3 class="mt-11 text-center text-[26px] font-bold leading-tight text-black sm:text-[30px]">
                        {{ __('core::booking-guide.journey_step.heading') }}
                    </h3>

                    <img
                        src="{{ asset('images/booking-guide/journey-selection-form.png') }}"
                        alt="{{ __('core::booking-guide.journey_step.form_alt') }}"
                        width="2154"
                        height="730"
                        class="mx-auto mt-9 h-auto w-full max-w-250 object-contain"
                        loading="lazy"
                    >

                    <ol class="mx-auto mt-10 grid w-full max-w-245 gap-x-8 gap-y-8 text-[18px] text-black sm:grid-cols-2 sm:text-[21px]">
                        @foreach(__('core::booking-guide.journey_step.fields') as $field)
                            <li class="flex items-center gap-4">
                                <span class="grid size-16 shrink-0 place-items-center rounded-full border-2 border-dashed border-futa-orange text-[42px] font-bold leading-none text-futa-orange">
                                    {{ $loop->iteration }}
                                </span>
                                <span>{{ $field }}</span>
                            </li>
                        @endforeach
                    </ol>

                    <img
                        src="{{ asset('images/booking-guide/trip-search-results.png') }}"
                        alt="{{ __('core::booking-guide.trip_selection_step.results_alt') }}"
                        width="930"
                        height="769"
                        class="mx-auto mt-16 h-auto w-full max-w-233 rounded-[18px] shadow-[0_12px_30px_rgba(34,34,34,.12)]"
                        loading="lazy"
                    >

                    <ol class="mx-auto mt-12 grid w-full max-w-245 gap-x-8 gap-y-8 text-[18px] text-black sm:grid-cols-2 sm:text-[21px]">
                        @foreach(__('core::booking-guide.trip_selection_step.fields') as $field)
                            <li class="flex items-center gap-4">
                                <span class="grid size-16 shrink-0 place-items-center rounded-full border-2 border-dashed border-futa-orange text-[42px] font-bold leading-none text-futa-orange">
                                    {{ $loop->iteration }}
                                </span>
                                <span>{{ $field }}</span>
                            </li>
                        @endforeach
                    </ol>

                    <div class="mt-10 overflow-x-auto pb-2">
                        <div class="mx-auto w-full min-w-200 max-w-250">
                            <div class="relative aspect-2169/312 w-full overflow-hidden" aria-hidden="true">
                                <img
                                    src="{{ asset('images/booking-guide/booking-steps-timeline-active-03.png') }}"
                                    alt=""
                                    width="2169"
                                    height="725"
                                    class="absolute left-0 top-[-63.8%] w-full max-w-none"
                                    loading="lazy"
                                >
                            </div>
                            <ol class="mt-3 grid grid-cols-5 gap-2 text-center text-[16px] leading-7 text-black sm:text-[17px]">
                                @foreach(__('core::booking-guide.booking_steps.labels') as $label)
                                    <li class="px-1">{{ $label }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>

                    <h3 class="mt-11 text-center text-[26px] font-bold leading-tight text-black sm:text-[30px]">
                        {{ __('core::booking-guide.trip_selection_step.next_heading') }}
                    </h3>

                    <img
                        src="{{ asset('images/booking-guide/seat-and-passenger-selection.png') }}"
                        alt="{{ __('core::booking-guide.seat_selection_step.form_alt') }}"
                        width="884"
                        height="868"
                        class="mx-auto mt-10 h-auto w-full max-w-245 rounded-2xl shadow-[0_12px_30px_rgba(34,34,34,.12)]"
                        loading="lazy"
                    >

                    <div class="mt-14 overflow-x-auto pb-2">
                        <div class="mx-auto w-full min-w-200 max-w-250">
                            <div class="relative aspect-2167/324 w-full overflow-hidden" aria-hidden="true">
                                <img
                                    src="{{ asset('images/booking-guide/booking-steps-timeline-active-04.png') }}"
                                    alt=""
                                    width="2167"
                                    height="725"
                                    class="absolute left-0 top-[-60.8%] w-full max-w-none"
                                    loading="lazy"
                                >
                            </div>
                            <ol class="mt-3 grid grid-cols-5 gap-2 text-center text-[16px] leading-7 text-black sm:text-[17px]">
                                @foreach(__('core::booking-guide.booking_steps.labels') as $label)
                                    <li class="px-1">{{ $label }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>

                    <h3 class="mt-11 text-center text-[26px] font-bold leading-tight text-black sm:text-[30px]">
                        {{ __('core::booking-guide.seat_selection_step.next_heading') }}
                    </h3>

                    <img
                        src="{{ asset('images/booking-guide/payment-method-selection.png') }}"
                        alt="{{ __('core::booking-guide.payment_step.form_alt') }}"
                        width="940"
                        height="574"
                        class="mx-auto mt-10 h-auto w-full max-w-235 rounded-2xl shadow-[0_12px_30px_rgba(34,34,34,.12)]"
                        loading="lazy"
                    >

                    <div class="mt-14 overflow-x-auto pb-2">
                        <div class="mx-auto w-full min-w-200 max-w-250">
                            <div class="relative aspect-2168/332 w-full overflow-hidden" aria-hidden="true">
                                <img
                                    src="{{ asset('images/booking-guide/booking-steps-timeline-active-05.png') }}"
                                    alt=""
                                    width="2168"
                                    height="725"
                                    class="absolute left-0 top-[-58.1%] w-full max-w-none"
                                    loading="lazy"
                                >
                            </div>
                            <ol class="mt-3 grid grid-cols-5 gap-2 text-center text-[16px] leading-7 text-black sm:text-[17px]">
                                @foreach(__('core::booking-guide.booking_steps.labels') as $label)
                                    <li class="px-1">{{ $label }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>

                    <h3 class="mt-11 text-center text-[26px] font-bold leading-tight text-black sm:text-[30px]">
                        {{ __('core::booking-guide.payment_step.next_heading') }}
                    </h3>

                    <img
                        src="{{ asset('images/booking-guide/ticket-booking-success.png') }}"
                        alt="{{ __('core::booking-guide.booking_success.image_alt') }}"
                        width="943"
                        height="780"
                        class="mx-auto mt-10 h-auto w-full max-w-235 rounded-2xl shadow-[0_12px_30px_rgba(34,34,34,.12)]"
                        loading="lazy"
                    >
                </section>

                <section aria-labelledby="guide-email-heading" class="mt-12 text-center">
                    <h2 id="guide-email-heading" class="text-[25px] font-bold leading-tight text-futa-green sm:text-[29px]">
                        {{ __('core::booking-guide.booking_success.email_heading') }}
                    </h2>
                    <img
                        src="{{ asset('images/booking-guide/ticket-email-preview.png') }}"
                        alt="{{ __('core::booking-guide.booking_success.email_image_alt') }}"
                        width="1652"
                        height="952"
                        class="mx-auto mt-10 h-auto w-full max-w-265 object-contain"
                        loading="lazy"
                    >
                </section>
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
