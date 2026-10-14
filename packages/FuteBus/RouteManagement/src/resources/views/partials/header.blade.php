<header class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">FUTA BUS LINES</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('RouteManagement::app.title') }}</h1>
        <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">{{ __('RouteManagement::app.description') }}</p>
    </div>
    <span class="rounded-full bg-futa-orange-soft px-4 py-2 text-sm font-bold text-futa-orange">
        {{ number_format($recordCount) }} {{ __('Dashboard::app.records') }}
    </span>
</header>
<nav class="mb-5 mt-5 flex flex-wrap items-center gap-1 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm" aria-label="{{ __('RouteManagement::app.title') }}">
    @foreach(['index' => 'routes', 'stops.index' => 'stops'] as $name => $label)
        <a href="{{ route('route-management.'.$name) }}"
            aria-current="{{ request()->routeIs('route-management.'.$name) ? 'page' : 'false' }}"
            @class(['inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold',
                'bg-futa-orange-soft text-futa-orange' => request()->routeIs('route-management.'.$name),
                'text-slate-600 hover:bg-slate-50' => !request()->routeIs('route-management.'.$name)])>
            <x-dynamic-component :component="$label === 'routes' ? 'heroicon-o-map' : 'heroicon-o-map-pin'" class="size-4" />
            {{ __('RouteManagement::app.'.$label) }}
        </a>
    @endforeach
    <a href="{{ route('trip-management.schedules.index') }}" class="ml-auto inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
        <x-heroicon-o-calendar-days class="size-4" />{{ __('RouteManagement::app.schedules') }}
    </a>
</nav>
@if(session('success'))
    <div data-route-flash data-message="{{ session('success') }}" role="status"
        class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif
