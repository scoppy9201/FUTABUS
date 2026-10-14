<header class="futa-header-pattern sticky top-0 z-40 shrink-0 text-white">
    <div class="flex h-20 items-center justify-between gap-2 px-3 sm:px-5">
        <a href="{{ route($operatorHome ?? 'staff.dashboard') }}" class="grid h-16 w-32 shrink-0 place-items-center rounded-2xl bg-white px-3 sm:w-44" aria-label="{{ __('StaffDashboard::staff.home') }}">
            <img src="{{ asset('icons/futabus-logo.png') }}" alt="{{ __('core::app.home.navbar.logo_alt') }}" class="max-h-12 w-full object-contain">
        </a>
        <div class="flex min-w-0 items-center gap-3 sm:gap-5">
            @include('StaffDashboard::partials.language-dropdown')
            @include('StaffDashboard::partials.account-menu',
                ['apiAccount' => true,
                'staffAccount' => true,
                'accountProfileRoute' => $operatorProfileRoute ?? 'staff.profile',
                'accountPasswordRoute' => $operatorPasswordRoute ?? 'staff.password',
                'accountRole' => $operatorRole ?? __('StaffDashboard::staff.role')])
        </div>
    </div>
</header>
