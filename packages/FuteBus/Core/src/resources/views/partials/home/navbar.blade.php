{{-- Navbar --}}
<nav class="bg-linear-to-r from-orange-500 to-orange-600">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('icons/futabus-logo.png') }}" alt="FUTA" class="h-10 w-auto brightness-0 invert">
                </a>
            </div>

            {{-- Menu desktop --}}
            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}" class="px-4 py-2 text-sm font-bold text-white rounded-lg hover:bg-white/10 transition">{{ __('core::app.home.navbar.home') }}</a>
                <a href="#" class="px-4 py-2 text-sm font-bold text-white/90 rounded-lg hover:bg-white/10 transition">{{ __('core::app.home.navbar.schedules') }}</a>
                <a href="#" class="px-4 py-2 text-sm font-bold text-white/90 rounded-lg hover:bg-white/10 transition">{{ __('core::app.home.navbar.lookup') }}</a>
                <a href="#" class="px-4 py-2 text-sm font-bold text-white/90 rounded-lg hover:bg-white/10 transition">{{ __('core::app.home.navbar.news') }}</a>
                <a href="#" class="px-4 py-2 text-sm font-bold text-white/90 rounded-lg hover:bg-white/10 transition">{{ __('core::app.home.navbar.invoice') }}</a>
                <a href="#" class="px-4 py-2 text-sm font-bold text-white/90 rounded-lg hover:bg-white/10 transition">{{ __('core::app.home.navbar.contact') }}</a>
                <a href="#" class="px-4 py-2 text-sm font-bold text-white/90 rounded-lg hover:bg-white/10 transition">{{ __('core::app.home.navbar.about') }}</a>
            </div>

            {{-- Auth --}}
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-orange-600 shadow hover:bg-orange-50 transition">
                        <x-heroicon-s-user class="h-4 w-4" />
                        {{ Auth::user()->name }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-orange-600 shadow hover:bg-orange-50 transition">
                        <x-heroicon-s-user class="h-4 w-4" />
                        {{ __('core::app.home.navbar.login') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
