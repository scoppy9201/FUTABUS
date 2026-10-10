@extends('core::layouts.home')

@section('title', __('core::complaint.meta.title'))
@section('meta_description', __('core::complaint.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto min-h-101.75 w-full max-w-285 px-4 py-10 sm:px-6 lg:px-0">
            <article class="text-base font-medium leading-7 text-gray-950">
                <header class="text-center">
                    <p class="text-[30px] font-extrabold uppercase leading-tight text-futa-orange sm:text-[36px]">
                        {{ __('core::complaint.brand') }}
                    </p>
                    <h1 class="mt-3 text-2xl font-extrabold uppercase leading-tight sm:text-[30px]">
                        {{ __('core::complaint.heading') }}
                    </h1>
                </header>

                <p class="mt-5 font-bold italic">{{ __('core::complaint.introduction') }}</p>

                <ul class="mt-4 list-disc space-y-4 pl-5">
                    <li>
                        <strong class="font-bold italic">{{ __('core::complaint.form.label') }}</strong>
                        <a href="#" class="text-futa-orange hover:underline">{{ __('core::complaint.form.link') }}</a>.
                        {{ __('core::complaint.form.description') }}
                    </li>
                    <li>
                        <strong class="font-bold italic">{{ __('core::complaint.chat.label') }}</strong>
                        {{ __('core::complaint.chat.description') }}
                    </li>
                    <li>
                        <strong class="font-bold italic">{{ __('core::complaint.phone.label') }}</strong>
                        <a href="tel:19006067" class="font-extrabold italic text-futa-orange hover:underline">
                            1900 6067
                        </a>
                        {{ __('core::complaint.phone.description') }}
                    </li>
                </ul>

                <p class="mt-4">{{ __('core::complaint.closing') }}</p>
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
