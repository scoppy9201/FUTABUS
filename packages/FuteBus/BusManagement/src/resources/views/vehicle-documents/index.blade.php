@extends('Dashboard::layouts.admin')

@section('title', __('BusManagement::app.vd_title'))

@section('content')
@php $inp = 'w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#F26522] focus:outline-none focus:ring-2 focus:ring-[#F26522]/20'; @endphp
<div x-data="vehicleDocManager()">

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#ef5222]">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('BusManagement::app.vd_title') }}</h1>
            <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">{{ __('BusManagement::app.vd_subtitle') }}</p>
        </div>
        <span class="rounded-full bg-orange-50 px-4 py-2 text-sm font-bold text-[#d7461a]">
            {{ number_format($docs->total()) }} {{ __('Dashboard::app.records') }}
        </span>
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
        <form method="GET" action="{{ route('bus-management.vehicle-documents.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('BusManagement::app.vd_search_ph') }}"
                class="w-56 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-[#F26522] focus:outline-none focus:ring-2 focus:ring-[#F26522]/20" />
            <select name="bus_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:outline-none">
                <option value="">{{ __('BusManagement::app.vd_filter_bus_all') }}</option>
                @foreach($buses as $b)
                    <option value="{{ $b->id }}" @selected((string) $busId === (string) $b->id)>{{ $b->license_plate }}</option>
                @endforeach
            </select>
            <select name="state" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:outline-none">
                <option value="">{{ __('BusManagement::app.vd_filter_state_all') }}</option>
                @foreach(['valid', 'expiring', 'expired'] as $st)
                    <option value="{{ $st }}" @selected($state === $st)>{{ __('BusManagement::app.vd_state_'.$st) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('BusManagement::app.btn_search') }}</button>
            @if($search || $busId || $state)
                <a href="{{ route('bus-management.vehicle-documents.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500">{{ __('BusManagement::app.btn_clear_filter') }}</a>
            @endif
        </form>
        <button @click="openAdd()" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#F26522] to-[#E31B23] px-5 py-2.5 text-sm font-bold text-white shadow-md hover:opacity-90">
            <x-heroicon-o-plus class="size-4" /> {{ __('BusManagement::app.vd_btn_add') }}
        </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">{{ __('BusManagement::app.col_stt') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.vd_f_bus') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.vd_f_type') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.vd_f_issued') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.vd_f_expiry') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.vd_col_state') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.vd_col_record') }}</th>
                        <th class="px-5 py-4 text-center">{{ __('BusManagement::app.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($docs as $d)
                        @php
                            $expiry   = \Illuminate\Support\Carbon::parse($d->expiry_date);
                            $daysLeft = today()->diffInDays($expiry, false);
                            $stateKey = $daysLeft < 0 ? 'expired' : ($daysLeft <= 30 ? 'expiring' : 'valid');
                            $current  = $d->status === 'active' && ! $d->superseded;
                            $snap     = json_decode($d->field_values ?? '[]', true) ?: [];
                        @endphp
                        <tr class="hover:bg-orange-50/30 {{ $current ? '' : 'opacity-60' }}">
                            <td class="px-5 py-4 text-slate-500">{{ $docs->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4 font-bold text-slate-900">{{ $d->license_plate }}</td>
                            <td class="px-5 py-4">{{ $d->type_name }}</td>
                            <td class="whitespace-nowrap px-5 py-4">{{ \Illuminate\Support\Carbon::parse($d->issued_date)->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap px-5 py-4">{{ $expiry->format('d/m/Y') }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold',
                                    'bg-emerald-50 text-emerald-700' => $stateKey === 'valid',
                                    'bg-amber-50 text-amber-700' => $stateKey === 'expiring',
                                    'bg-rose-50 text-rose-700' => $stateKey === 'expired',
                                ])>{{ __('BusManagement::app.vd_state_'.$stateKey) }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs font-semibold text-slate-500">
                                @if($d->status !== 'active')
                                    {{ __('BusManagement::app.status_inactive') }}
                                @elseif($d->superseded)
                                    {{ __('BusManagement::app.vd_rec_history') }}
                                @else
                                    {{ __('BusManagement::app.vd_rec_current') }}
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openView({{ json_encode([
                                        'bus' => $d->license_plate, 'type' => $d->type_name,
                                        'issued' => \Illuminate\Support\Carbon::parse($d->issued_date)->format('d/m/Y'),
                                        'expiry' => $expiry->format('d/m/Y'), 'values' => $snap,
                                    ]) }})" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[#F26522] hover:text-[#F26522]">
                                        <x-heroicon-o-eye class="size-3.5" /> {{ __('BusManagement::app.vd_btn_view') }}
                                    </button>
                                    @if($current && $stateKey === 'expired')
                                    <button @click="openRenew({{ json_encode([
                                        'id' => $d->id, 'bus' => $d->license_plate, 'type' => $d->type_name,
                                        'type_id' => $d->document_type_id,
                                        'values' => collect($snap)->pluck('value', 'key'),
                                    ]) }})" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:border-emerald-300 hover:bg-emerald-50">
                                        <x-heroicon-o-arrow-path class="size-3.5" /> {{ __('BusManagement::app.vd_btn_renew') }}
                                    </button>
                                    @endif
                                    @if($current)
                                    <button @click="openDelete({{ json_encode(['id' => $d->id, 'name' => $d->type_name.' — '.$d->license_plate]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:border-rose-300 hover:bg-rose-50">
                                        <x-heroicon-o-no-symbol class="size-3.5" /> {{ __('BusManagement::app.vd_btn_deactivate') }}
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-16 text-center text-sm font-semibold text-slate-500">{{ __('BusManagement::app.vd_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($docs->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $docs->links() }}</div>
        @endif
    </div>

    {{-- ADD / RENEW MODAL --}}
    <div x-show="showForm" x-cloak @click.self="showForm = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between px-5 py-3"
                 :class="mode === 'renew' ? 'bg-slate-800' : 'bg-gradient-to-r from-[#F26522] to-[#E31B23]'">
                <h2 class="text-base font-bold text-white"
                    x-text="mode === 'renew' ? @js(__('BusManagement::app.vd_modal_renew')) : @js(__('BusManagement::app.vd_modal_add'))"></h2>
                <button type="button" @click="showForm = false" class="text-white/70 hover:text-white"><x-heroicon-o-x-mark class="size-4" /></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-3 p-5">
                @csrf

                <template x-if="mode === 'renew'">
                    <p class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700">
                        <span x-text="renewInfo.type"></span> — <span x-text="renewInfo.bus"></span>.
                        {{ __('BusManagement::app.vd_renew_hint') }}
                    </p>
                </template>

                <template x-if="mode === 'add'">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.vd_f_bus') }} <span class="text-rose-500">*</span></label>
                            <select name="bus_id" x-model="form.bus_id" required class="{{ $inp }}">
                                <option value="">{{ __('BusManagement::app.vd_f_bus_select') }}</option>
                                @foreach($buses->where('status', 'active') as $b)
                                    <option value="{{ $b->id }}">{{ $b->license_plate }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.vd_f_type') }} <span class="text-rose-500">*</span></label>
                            <select name="document_type_id" x-model="form.document_type_id" @change="form.values = {}" required class="{{ $inp }}">
                                <option value="">{{ __('BusManagement::app.vd_f_type_select') }}</option>
                                <template x-for="t in types" :key="t.id">
                                    <option :value="t.id" x-text="t.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.vd_f_issued') }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="issued_date" x-model="form.issued_date" required class="{{ $inp }}" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.vd_f_expiry') }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="expiry_date" x-model="form.expiry_date" required class="{{ $inp }}" />
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 p-3">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('BusManagement::app.vd_f_values') }}</p>
                    <p x-show="currentFields.length === 0" class="text-xs text-slate-400">{{ __('BusManagement::app.vd_no_fields') }}</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <template x-for="f in currentFields" :key="f.key">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-700">
                                    <span x-text="f.label"></span> <span x-show="f.required" class="text-rose-500">*</span>
                                </label>
                                <input :type="f.type === 'number' ? 'number' : (f.type === 'date' ? 'date' : 'text')" step="any" maxlength="255"
                                       :name="'values[' + f.key + ']'" x-model="form.values[f.key]" :required="f.required"
                                       class="{{ $inp }}" />
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showForm = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('BusManagement::app.btn_cancel') }}</button>
                    <button type="submit" class="rounded-lg bg-[#F26522] px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#d9561d]"
                            x-text="mode === 'renew' ? @js(__('BusManagement::app.vd_btn_renew')) : @js(__('BusManagement::app.btn_store'))"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- VIEW MODAL --}}
    <div x-show="showView" x-cloak @click.self="showView = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900">{{ __('BusManagement::app.vd_modal_view') }}</h3>
            <p class="mt-1 text-sm text-slate-500"><span x-text="viewData.type"></span> — <span class="font-bold" x-text="viewData.bus"></span></p>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('BusManagement::app.vd_f_issued') }}</dt><dd class="font-semibold text-slate-800" x-text="viewData.issued"></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('BusManagement::app.vd_f_expiry') }}</dt><dd class="font-semibold text-slate-800" x-text="viewData.expiry"></dd></div>
                <template x-for="v in (viewData.values || [])" :key="v.key">
                    <div class="flex justify-between gap-4"><dt class="text-slate-500" x-text="v.label"></dt><dd class="font-semibold text-slate-800" x-text="v.value || '—'"></dd></div>
                </template>
            </dl>
            <div class="mt-5 text-right">
                <button type="button" @click="showView = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('BusManagement::app.btn_cancel') }}</button>
            </div>
        </div>
    </div>

    {{-- DEACTIVATE MODAL --}}
    <div x-show="showDelete" x-cloak @click.self="showDelete = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto mb-4 grid size-14 place-items-center rounded-full bg-rose-100">
                <x-heroicon-o-exclamation-triangle class="size-7 text-rose-500" />
            </div>
            <h3 class="text-lg font-bold text-slate-900">{{ __('BusManagement::app.vd_modal_deactivate') }}</h3>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('BusManagement::app.vd_deactivate_msg') }}
                <span class="font-bold text-slate-800" x-text="delData.name"></span>?
            </p>
            <form :action="delAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDelete = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('BusManagement::app.btn_cancel') }}</button>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700">{{ __('BusManagement::app.btn_confirm_delete') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
function vehicleDocManager() {
    const types = @json($typePayload);
    const base = `{{ url('quan-tri/phuong-tien/ho-so-giay-to') }}`;
    const blank = () => ({ bus_id: '', document_type_id: '', issued_date: '', expiry_date: '', values: {} });
    return {
        types, mode: 'add', showForm: false, showView: false, showDelete: false,
        form: blank(), formAction: base, renewInfo: {}, viewData: {}, delData: {}, delAction: '',
        get currentFields() {
            const t = this.types.find(t => String(t.id) === String(this.form.document_type_id));
            return t ? t.fields : [];
        },
        openAdd() { this.mode = 'add'; this.form = blank(); this.formAction = base; this.showForm = true; },
        openRenew(d) {
            this.mode = 'renew'; this.renewInfo = d;
            this.form = { ...blank(), document_type_id: d.type_id, values: { ...d.values } };
            this.formAction = `${base}/${d.id}/gia-han`; this.showForm = true;
        },
        openView(d) { this.viewData = d; this.showView = true; },
        openDelete(d) { this.delData = d; this.delAction = `${base}/${d.id}`; this.showDelete = true; },
    };
}
</script>
@endsection