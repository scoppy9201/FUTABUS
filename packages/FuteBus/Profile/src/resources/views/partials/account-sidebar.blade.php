<aside class="rounded-2xl border border-gray-200 bg-white p-2.5 lg:min-h-134" aria-label="{{ __('Profile::app.title') }}">
    <div class="space-y-1.5">
        <div class="flex items-center gap-3 rounded-xl px-3 py-3 text-lg font-medium text-gray-900" aria-disabled="true" title="{{ __('Profile::app.coming_soon') }}">
            <span class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-full bg-[#00613d]">
                <img src="{{ asset('images/auth/White%20Brushstroke%20F%20on%20Forest%20Green.png') }}" alt="" class="size-full scale-125 object-cover">
            </span>
            <span>{{ __('Profile::app.futapay') }}</span>
        </div>
        <a
            href="{{ route('profile.show') }}"
            @if(request()->routeIs('profile.show')) aria-current="page" @endif
            @class([
                'flex items-center gap-3 rounded-xl px-3 py-3 text-lg hover:bg-orange-50',
                'bg-orange-50 font-semibold text-gray-950' => request()->routeIs('profile.show'),
                'font-medium text-gray-900' => ! request()->routeIs('profile.show'),
            ])
        >
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-amber-400 text-white"><x-heroicon-s-user-circle class="size-7" /></span>
            <span>{{ __('Profile::app.title') }}</span>
        </a>
        <a href="{{ route('profile.tickets.index') }}" @if(request()->routeIs('profile.tickets.*')) aria-current="page" @endif @class([
            'flex items-center gap-3 rounded-xl px-3 py-3 text-lg hover:bg-orange-50',
            'bg-orange-50 font-semibold text-gray-950' => request()->routeIs('profile.tickets.*'),
            'font-medium text-gray-900' => ! request()->routeIs('profile.tickets.*'),
        ])>
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-sky-500 text-white"><x-heroicon-o-clock class="size-6" /></span>
            <span>{{ __('Profile::app.ticket_history') }}</span>
        </a>
        <a
            href="{{ route('profile.password.edit') }}"
            @if(request()->routeIs('profile.password.edit')) aria-current="page" @endif
            @class([
                'flex items-center gap-3 rounded-xl px-3 py-3 text-lg hover:bg-orange-50',
                'bg-orange-50 font-semibold text-gray-950' => request()->routeIs('profile.password.edit'),
                'font-medium text-gray-900' => ! request()->routeIs('profile.password.edit'),
            ])
        >
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-[#ef6b31] text-white"><x-heroicon-o-lock-closed class="size-6" /></span>
            <span>{{ __('Profile::app.reset_password') }}</span>
        </a>
        <form
            action="{{ route('logout') }}"
            method="post"
            data-confirm
            data-confirm-title="{{ __('core::confirm.logout_title') }}"
            data-confirm-message="{{ __('core::confirm.logout_message') }}"
        >
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-lg font-medium text-gray-900 transition hover:bg-orange-50">
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-red-600 text-white"><x-heroicon-o-arrow-right-on-rectangle class="size-6" /></span>
                <span>{{ __('Profile::app.logout') }}</span>
            </button>
        </form>
    </div>
</aside>
