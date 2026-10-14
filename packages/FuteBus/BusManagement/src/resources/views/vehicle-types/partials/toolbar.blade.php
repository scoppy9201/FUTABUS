{{-- Page title, filters, and create action. --}}
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">{{ $company?->name ?? __('BusManagement::app.bus_futa_company_fallback') }}</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('BusManagement::app.vehicle_types_title') }}</h1>
        <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">{{ __('BusManagement::app.vehicle_types_subtitle') }}</p>
    </div>
    <span id="vehicle-type-results-count" class="rounded-full bg-futa-orange-soft px-4 py-2 text-sm font-bold text-futa-orange">
        {{ number_format($vehicleTypes->total()) }} {{ __('Dashboard::app.records') }}
    </span>
</div>

<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
    <form method="GET" action="{{ route('bus-management.vehicle-types.index') }}" class="flex flex-wrap items-center gap-2">
        <div class="relative">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input type="text" name="search" value="{{ $search }}" @input="window.busManagementLiveSearch($el.form, 'vehicle-type-results')"
                placeholder="{{ __('BusManagement::app.search_placeholder') }}"
                class="w-64 rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm shadow-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
        </div>
        <select name="vehicle_class" @change="window.busManagementLiveSearch($el.form, 'vehicle-type-results')" class="w-48 shrink-0 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
            <option value="">{{ __('BusManagement::app.vt_filter_all_class') }}</option>
            @foreach($classes as $key => $label)
                <option value="{{ $key }}" @selected($vehicleClass === $key)>{{ __('BusManagement::app.vt_class_'.$key) }}</option>
            @endforeach
        </select>
        <select name="status" @change="window.busManagementLiveSearch($el.form, 'vehicle-type-results')" class="w-40 shrink-0 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
            <option value="">{{ __('BusManagement::app.vt_filter_all_status') }}</option>
            <option value="active" @selected($statusFilter === 'active')>{{ __('BusManagement::app.status_active') }}</option>
            <option value="inactive" @selected($statusFilter === 'inactive')>{{ __('BusManagement::app.status_inactive') }}</option>
        </select>
        @if($search || $vehicleClass || $statusFilter)
            <a href="{{ route('bus-management.vehicle-types.index') }}"
               class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500 transition hover:text-slate-700">
                {{ __('BusManagement::app.btn_clear_filter') }}
            </a>
        @endif
    </form>

    <button id="btn-add-vehicle-type" @click="openAdd()"
        class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-futa-orange-dark hover:shadow-lg">
        <x-heroicon-o-plus class="size-4" /> {{ __('BusManagement::app.btn_add') }}
    </button>
</div>
