<div class="relative ml-auto" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button
        type="button"
        id="account-menu-button"
        class="flex max-w-56 items-center gap-2 rounded-full px-2 py-1 text-base font-semibold text-white transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:gap-2.5"
        @click="open = ! open"
        :aria-expanded="open.toString()"
        aria-haspopup="true"
        aria-controls="account-menu"
        aria-label="{{ __('core::app.home.navbar.account_menu', ['name' => Auth::user()->name]) }}"
    >
        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-[#414e62] text-[#ff8228] ring-2 ring-white/30">
            <x-heroicon-s-user-circle class="size-8" />
        </span>
        <span class="hidden truncate sm:block">{{ Auth::user()->name }}</span>
        <x-heroicon-s-chevron-down class="hidden size-4 shrink-0 transition-transform duration-200 sm:block" ::class="open ? 'rotate-180' : ''" />
    </button>

    <div
        id="account-menu"
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute right-0 top-full z-50 mt-2 w-68 overflow-hidden rounded-xl border border-gray-100 bg-white py-1.5 text-gray-900 shadow-2xl"
        aria-labelledby="account-menu-button"
    >
        @if(Auth::user()->isAdmin())
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-base font-semibold text-gray-900 transition hover:bg-orange-50 focus-visible:bg-orange-50 focus-visible:outline-none">
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-[#0b2733] text-white"><x-heroicon-o-squares-2x2 class="size-5" /></span>
                <span>{{ __('Dashboard::app.owner_portal') }}</span>
            </a>
        @endif
        <a href="{{ route('profile.futapay') }}" class="flex items-center gap-3 px-3 py-2.5 text-base font-semibold text-gray-900 transition hover:bg-orange-50 focus-visible:bg-orange-50 focus-visible:outline-none">
            <span class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-full bg-[#00613d]"><img src="{{ asset('images/auth/White%20Brushstroke%20F%20on%20Forest%20Green.png') }}" alt="" class="size-full scale-125 object-cover"></span>
            <span>{{ __('core::app.home.navbar.futapay') }}</span>
        </a>
        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3 py-2.5 text-base font-semibold text-gray-900 transition hover:bg-orange-50 focus-visible:bg-orange-50 focus-visible:outline-none">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-amber-400 text-white"><x-heroicon-s-user-circle class="size-7" /></span>
            <span>{{ __('core::app.home.navbar.account_information') }}</span>
        </a>
        <a href="{{ route('profile.tickets.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-base font-semibold text-gray-900 transition hover:bg-orange-50 focus-visible:bg-orange-50 focus-visible:outline-none">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-sky-500 text-white"><x-heroicon-o-clock class="size-6" /></span>
            <span>{{ __('core::app.home.navbar.ticket_history') }}</span>
        </a>
        <a href="{{ route('profile.password.edit') }}" class="flex items-center gap-3 px-3 py-2.5 text-base font-semibold text-gray-900 transition hover:bg-orange-50 focus-visible:bg-orange-50 focus-visible:outline-none">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-[#ef6b31] text-white"><x-heroicon-o-lock-closed class="size-6" /></span>
            <span>{{ __('core::app.home.navbar.reset_password') }}</span>
        </a>
        <form
            action="{{ route('logout') }}"
            method="post"
            data-confirm
            data-confirm-title="{{ __('core::confirm.logout_title') }}"
            data-confirm-message="{{ __('core::confirm.logout_message') }}"
            class="border-t border-gray-100 pt-1"
        >
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 px-3 py-2.5 text-left text-base font-semibold text-gray-900 transition hover:bg-orange-50 focus-visible:bg-orange-50 focus-visible:outline-none">
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-red-600 text-white"><x-heroicon-o-arrow-right-on-rectangle class="size-6" /></span>
                <span>{{ __('core::app.home.navbar.logout') }}</span>
            </button>
        </form>
    </div>
</div>
