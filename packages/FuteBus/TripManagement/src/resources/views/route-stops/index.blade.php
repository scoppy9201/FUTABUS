@extends('Dashboard::layouts.admin')

@section('title', __('TripManagement::app.rs_title'))

@section('content')
@php
    $inp = 'w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#F26522] focus:outline-none focus:ring-2 focus:ring-[#F26522]/20';
    $activeStops = $stops->where('status', 'active');
    $min = $activeStops->min('offset_minutes') ?? 0;
@endphp
<div x-data="stopManager()">

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#ef5222]">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('TripManagement::app.rs_title') }}</h1>
            <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">{{ __('TripManagement::app.rs_subtitle') }}</p>
        </div>
        <a href="{{ route('trip-management.schedules.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            ← {{ __('TripManagement::app.rs_back') }}
        </a>
    </div>

    @if(session('success'))
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            <x-heroicon-o-check-circle class="size-5 shrink-0" /> {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
            <x-heroicon-o-exclamation-triangle class="size-5 shrink-0" /> {{ session('warning') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('trip-management.stops.index') }}" class="flex items-center gap-2">
            <select name="route_id" onchange="this.form.submit()"
                    class="w-96 max-w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:outline-none">
                @foreach($routes as $r)
                    <option value="{{ $r->id }}" @selected($routeId === $r->id)>{{ $r->code }} — {{ $r->origin_city }} → {{ $r->destination_city }}</option>
                @endforeach
            </select>
        </form>
        @if($route)
        <button @click="openAdd()" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#F26522] to-[#E31B23] px-5 py-2.5 text-sm font-bold text-white shadow-md hover:opacity-90">
            <x-heroicon-o-plus class="size-4" /> {{ __('TripManagement::app.rs_btn_add') }}
        </button>
        @endif
    </div>

    @if($route && $activeStops->count() < 2)
        <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">{{ __('TripManagement::app.rs_need_two') }}</p>
    @endif

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">#</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.rs_f_name') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.rs_f_offset') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.rs_f_type') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.col_status') }}</th>
                        <th class="px-5 py-4 text-center">{{ __('TripManagement::app.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stops as $s)
                        <tr class="hover:bg-orange-50/30 {{ $s->status === 'active' ? '' : 'opacity-50' }}">
                            <td class="px-5 py-4 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $s->name }}
                                @if($s->address)<span class="block max-w-md truncate text-xs font-medium text-slate-400">{{ $s->address }}</span>@endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                {{ $s->offset_minutes }} {{ __('TripManagement::app.rs_minutes') }}
                                @if($s->status === 'active')
                                    <span class="block text-xs text-slate-400">+{{ $s->offset_minutes - $min }}'</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">{{ __('TripManagement::app.rs_type_'.$s->stop_type) }}</td>
                            <td class="px-5 py-4">
                                @if($s->status === 'active')
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ __('TripManagement::app.rs_active') }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ __('TripManagement::app.rs_inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEdit({{ json_encode([
                                        'id' => $s->id, 'name' => $s->name, 'address' => $s->address,
                                        'offset_minutes' => $s->offset_minutes, 'stop_type' => $s->stop_type, 'status' => $s->status,
                                    ]) }})" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[#F26522] hover:text-[#F26522]">
                                        <x-heroicon-o-pencil-square class="size-3.5" /> {{ __('TripManagement::app.btn_edit') }}
                                    </button>
                                    @if($s->status === 'active')
                                    <button @click="openDelete({{ json_encode(['id' => $s->id, 'name' => $s->name]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:border-rose-300 hover:bg-rose-50">
                                        <x-heroicon-o-no-symbol class="size-3.5" /> {{ __('TripManagement::app.btn_delete') }}
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center text-sm font-semibold text-slate-500">{{ __('TripManagement::app.rs_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ADD / EDIT MODAL --}}
    <div x-show="showForm" x-cloak @click.self="showForm = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between px-5 py-3"
                 :class="isEdit ? 'bg-slate-800' : 'bg-gradient-to-r from-[#F26522] to-[#E31B23]'">
                <h2 class="text-base font-bold text-white"
                    x-text="isEdit ? @js(__('TripManagement::app.rs_modal_edit')) : @js(__('TripManagement::app.rs_modal_add'))"></h2>
                <button type="button" @click="showForm = false" class="text-white/70 hover:text-white"><x-heroicon-o-x-mark class="size-4" /></button>
            </div>
            <form :action="formAction" method="POST" class="space-y-3 p-5">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="route_id" value="{{ $routeId }}">

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.rs_f_name') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="form.name" required maxlength="150" class="{{ $inp }}" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.rs_f_address') }}</label>
                    <input type="text" name="address" x-model="form.address" maxlength="255" class="{{ $inp }}" />
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.rs_f_offset') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="offset_minutes" x-model="form.offset_minutes" required min="0" max="4320" step="any" class="{{ $inp }}" />
                        <p class="mt-0.5 text-xs text-slate-400">{{ __('TripManagement::app.rs_offset_hint') }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.rs_f_type') }}</label>
                        <select name="stop_type" x-model="form.stop_type" class="{{ $inp }}">
                            <option value="both">{{ __('TripManagement::app.rs_type_both') }}</option>
                            <option value="pickup">{{ __('TripManagement::app.rs_type_pickup') }}</option>
                            <option value="dropoff">{{ __('TripManagement::app.rs_type_dropoff') }}</option>
                        </select>
                    </div>
                </div>
                <div x-show="isEdit">
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.col_status') }}</label>
                    <select name="status" x-model="form.status" :disabled="!isEdit" class="{{ $inp }}">
                        <option value="active">{{ __('TripManagement::app.rs_active') }}</option>
                        <option value="inactive">{{ __('TripManagement::app.rs_inactive') }}</option>
                    </select>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showForm = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('TripManagement::app.btn_cancel') }}</button>
                    <button type="submit" class="rounded-lg bg-[#F26522] px-5 py-2 text-xs font-bold text-white hover:bg-[#d9561d]"
                            x-text="isEdit ? @js(__('TripManagement::app.btn_save')) : @js(__('TripManagement::app.btn_store'))"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- DEACTIVATE MODAL --}}
    <div x-show="showDelete" x-cloak @click.self="showDelete = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto mb-4 grid size-14 place-items-center rounded-full bg-rose-100">
                <x-heroicon-o-exclamation-triangle class="size-7 text-rose-500" />
            </div>
            <h3 class="text-lg font-bold text-slate-900">{{ __('TripManagement::app.rs_modal_deactivate') }}</h3>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('TripManagement::app.rs_deactivate_msg') }}
                <span class="font-bold text-slate-800" x-text="delData.name"></span>?
            </p>
            <form :action="delAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDelete = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('TripManagement::app.btn_cancel') }}</button>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700">{{ __('TripManagement::app.btn_confirm') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
function stopManager() {
    const blank = { name:'', address:'', offset_minutes:0, stop_type:'both', status:'active' };
    const base = `{{ url('quan-tri/chuyen-xe/diem-dung') }}`;
    return {
        showForm: false, showDelete: false, isEdit: false,
        form: { ...blank }, formAction: base, delData: {}, delAction: '',
        openAdd()    { this.isEdit = false; this.form = { ...blank }; this.formAction = base; this.showForm = true; },
        openEdit(d)  { this.isEdit = true; this.form = { ...blank, ...d, address: d.address ?? '' }; this.formAction = `${base}/${d.id}`; this.showForm = true; },
        openDelete(d){ this.delData = d; this.delAction = `${base}/${d.id}`; this.showDelete = true; },
    };
}
</script>
@endsection