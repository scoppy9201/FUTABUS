<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('Auth::app.meta_description') }}">
    <title>@yield('title') | {{ __('Auth::app.site_name') }}</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'packages/FuteBus/Core/src/resources/css/app.css',
        'packages/FuteBus/Core/src/resources/js/app.js',
        'packages/FuteBus/Auth/src/resources/js/app.js',
    ])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.13.0/lottie.min.js" defer></script>
</head>
<body class="min-h-screen overflow-x-hidden bg-white text-gray-900 antialiased">
    @include('core::partials.global-loader')
    @include('core::partials.home.navbar', ['compact' => true])

    <main class="bg-[linear-gradient(to_bottom,var(--color-futa-orange)_0_110px,#fff_110px)] px-3 pb-7 sm:px-4">
        <section class="relative z-10 mx-auto grid min-h-118 w-full max-w-282 overflow-hidden rounded-2xl border border-futa-orange/50 bg-white shadow-[0_7px_0_rgb(239_82_34/8%)] lg:grid-cols-[60%_40%]" aria-labelledby="auth-title">
            <div class="relative hidden min-h-117.5 overflow-hidden lg:block" aria-hidden="true">
                <div class="absolute top-8 left-11 z-10 leading-none">
                    <p class="text-[32px] font-black tracking-[-1px] text-futa-green drop-shadow-[0_3px_2px_rgb(0_0_0/30%)]">{{ __('Auth::app.brand.name') }}</p>
                    <span class="mt-2 block text-[25px] font-semibold text-futa-orange drop-shadow-[0_3px_2px_rgb(0_0_0/28%)]">{{ __('Auth::app.brand.slogan') }}</span>
                </div>
                <div class="absolute top-62.5 left-0 z-10 w-84 text-center text-[26px] leading-13 font-black text-futa-green">
                    {{ __('Auth::app.brand.transfer_service') }}<br>{{ __('Auth::app.brand.door_to_door') }}
                </div>
                <img
                    src="{{ asset('images/auth/transfer-bus.png') }}"
                    alt=""
                    class="absolute -left-8 bottom-2 w-170 max-w-none"
                >
            </div>

            <div class="px-5 py-7 sm:px-8 sm:py-8 lg:pr-9 lg:pl-1">
                @yield('form')
            </div>
        </section>

        @include('core::partials.home.futa-ecosystem', ['compact' => true])
    </main>

    @include('core::partials.home.footer')
    @include('core::partials.floating-support')
    <x-confirm-dialog />
</body>
</html>
