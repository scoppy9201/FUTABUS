@extends('Dashboard::layouts.admin')

@section('title', __('TripManagement::app.sch_title'))

@section('content')
@php
    $inp = 'w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20';
@endphp
<div x-data="scheduleManager()">

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('TripManagement::app.sch_title') }}</h1>
            <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">{{ __('TripManagement::app.sch_subtitle') }}</p>
        </div>
        <span class="rounded-full bg-futa-orange-soft px-4 py-2 text-sm font-bold text-futa-orange-dark">
            {{ number_format($schedules->total()) }} {{ __('Dashboard::app.records') }}
        </span>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('trip-management.schedules.index') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('TripManagement::app.sch_search_ph') }}"
                class="w-72 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
            <button type="submit" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('TripManagement::app.btn_search') }}</button>
            @if($search)
                <a href="{{ route('trip-management.schedules.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500">{{ __('TripManagement::app.btn_clear_filter') }}</a>
            @endif
        </form>
        <button @click="openAdd()" class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white shadow-md hover:opacity-90">
            <x-heroicon-o-plus class="size-4" /> {{ __('TripManagement::app.btn_add') }}
        </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-225 text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">{{ __('TripManagement::app.col_stt') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.sch_f_route') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.sch_f_time') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.sch_f_days') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.sch_f_from') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.sch_f_price') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.col_status') }}</th>
                        <th class="px-5 py-4 text-center">{{ __('TripManagement::app.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $s)
                        @php
                            $days = json_decode($s->days_of_week, true) ?: [];
                            $routeStops = ($stopsByRoute[$s->route_id] ?? collect())->sortBy('stop_order')->values();
                            $dep = \Illuminate\Support\Carbon::createFromFormat('H:i', substr($s->departure_time, 0, 5));
                        @endphp

                        {{-- Dòng lịch trình: bấm để mở/đóng danh sách điểm dừng --}}
                        <tr class="cursor-pointer hover:bg-futa-orange-soft/30" @click="toggle({{ $s->id }})">
                            <td class="px-5 py-4 text-slate-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="inline-flex text-slate-400 transition-transform duration-200"
                                          :class="open[{{ $s->id }}] ? 'rotate-90 text-futa-orange' : ''">
                                        <x-heroicon-o-chevron-right class="size-4" />
                                    </span>
                                    {{ $schedules->firstItem() + $loop->index }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $s->origin_city }} → {{ $s->destination_city }}
                                <span class="block text-xs font-medium text-slate-400">{{ $s->route_code }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">{{ substr($s->departure_time, 0, 5) }}</td>
                            <td class="px-5 py-4">
                                {{ collect($days)->map(fn ($d) => __('TripManagement::app.day_'.$d))->implode(', ') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                {{ \Illuminate\Support\Carbon::parse($s->start_date)->format('d/m/Y') }}
                                → {{ \Illuminate\Support\Carbon::parse($s->end_date)->format('d/m/Y') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">{{ number_format($s->price, 0, ',', '.') }} ₫</td>
                            <td class="px-5 py-4">
                                @if($s->status === 'active')
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ __('TripManagement::app.status_active') }}</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ __('TripManagement::app.status_inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($s->status === 'active')
                                <div class="flex items-center justify-center gap-2">
                                    <button @click.stop="openEdit({{ json_encode([
                                        'id' => $s->id, 'route_id' => $s->route_id,
                                        'departure_time' => substr($s->departure_time, 0, 5),
                                        'days_of_week' => $days,
                                        'start_date' => $s->start_date, 'end_date' => $s->end_date,
                                        'price' => (int) $s->price,
                                    ]) }})" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-futa-orange hover:text-futa-orange">
                                        <x-heroicon-o-pencil-square class="size-3.5" /> {{ __('TripManagement::app.btn_edit') }}
                                    </button>
                                    <button @click.stop="openDelete({{ json_encode(['id' => $s->id, 'name' => $s->origin_city.' → '.$s->destination_city]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:border-rose-300 hover:bg-rose-50">
                                        <x-heroicon-o-no-symbol class="size-3.5" /> {{ __('TripManagement::app.btn_delete') }}
                                    </button>
                                </div>
                                @endif
                            </td>
                        </tr>

                        {{-- Dòng mở rộng: lịch trình các điểm dừng --}}
                        <tr x-show="open[{{ $s->id }}]" x-cloak style="display:none">
                            <td colspan="8" class="bg-slate-50/70 px-5 py-5 sm:px-10">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h3 class="text-sm font-extrabold text-slate-800">
                                        {{ __('TripManagement::app.sch_stops_of') }}
                                        {{ $s->origin_city }} → {{ $s->destination_city }}
                                    </h3>
                                    <a href="{{ route('route-management.stops.index', ['route_id' => $s->route_id]) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 bg-white px-3.5 py-2 text-xs font-bold text-futa-orange">
                                        <x-heroicon-o-map-pin class="size-3.5" /> {{ __('RouteManagement::app.manage_stops') }}
                                    </a>
                                </div>

                                @if($routeStops->isEmpty())
                                    <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                                        {{ __('TripManagement::app.rs_need_two') }}
                                    </p>
                                @endif

                                <ol class="mt-4">
                                    @php $run = 0; @endphp
                                    @foreach($routeStops as $st)
                                        @php
                                            $run += (int) $st->leg_minutes;
                                            $t = $dep->copy()->addMinutes($run);
                                            $plus = (int) $dep->copy()->startOfDay()->diffInDays($t->copy()->startOfDay());
                                            $mapQuery = implode(', ', array_filter([$st->name, $st->address, 'Việt Nam']));
                                            $mapUrl = 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($mapQuery);
                                        @endphp
                                        <li class="flex gap-4">
                                            <span class="w-16 shrink-0 pt-0.5 text-right text-sm font-bold text-slate-800">
                                                {{ $t->format('H:i') }}
                                                @if($plus > 0)<span class="block text-[10px] font-semibold text-slate-400">+{{ $plus }}</span>@endif
                                            </span>
                                            <div class="flex w-6 shrink-0 flex-col items-center" aria-hidden="true">
                                                @if($loop->first || $loop->last)
                                                    <span class="-mt-0.5 grid size-6 shrink-0 place-items-center rounded-full border border-orange-200 bg-orange-50 text-futa-orange">
                                                        @if($loop->first)
                                                            <x-heroicon-o-map-pin class="size-4" />
                                                        @else
                                                            <x-heroicon-o-flag class="size-4" />
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="mt-1.5 size-3 shrink-0 rounded-full border-2 border-slate-500 bg-white"></span>
                                                @endif
                                                @unless($loop->last)<span class="w-px grow bg-slate-200"></span>@endunless
                                            </div>
                                            <div class="min-w-0 flex-1 pb-5">
                                                <p class="text-sm font-bold text-slate-800">
                                                    {{ $st->name }}
                                                    <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">{{ __('TripManagement::app.rs_type_'.$st->stop_type) }}</span>
                                                    @if($loop->last)
                                                        <span class="ml-1 rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">{{ __('TripManagement::app.rs_last_stop') }}</span>
                                                    @endif
                                                </p>
                                                @if($st->address)<p class="text-xs text-slate-500">{{ $st->address }}</p>@endif
                                                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                                                    <p class="text-[11px] text-slate-400">+{{ $st->leg_minutes }} {{ __('TripManagement::app.rs_minutes') }}</p>
                                                    <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer"
                                                        class="inline-flex items-center gap-1 text-xs font-semibold text-futa-orange hover:underline
                                                            focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange">
                                                        <x-heroicon-o-map-pin class="size-3.5" aria-hidden="true" />
                                                        {{ __('TripManagement::app.rs_view_location') }}
                                                        <span class="sr-only">: {{ $st->name }} — {{ __('TripManagement::app.rs_map_new_tab') }}</span>
                                                        <x-heroicon-o-arrow-top-right-on-square class="size-3" aria-hidden="true" />
                                                    </a>
                                                </div>
                                            </div>

                                        </li>
                                    @endforeach
                                </ol>

                                <p class="mt-1 rounded-lg bg-white px-3 py-2 text-xs text-slate-500">{{ __('TripManagement::app.rs_arrival_note') }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-sm font-semibold text-slate-500">
                                <x-core::empty-state :title="__('TripManagement::app.empty')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4"><x-core::data-pagination :paginator="$schedules" /></div>
    </div>

    {{-- Shared right-side panel --}}
    <x-core::side-panel id="schedules-form-panel" @close="showForm = false" @click.self="showForm = false"
        x-effect="window.FutaSidePanel.sync($el, showForm, $nextTick)">
        <x-slot:title><span
                    x-text="isEdit ? @js(__('TripManagement::app.sch_modal_edit')) : @js(__('TripManagement::app.sch_modal_add'))"></span></x-slot:title>
        <form :action="formAction" method="POST" class="flex min-h-0 flex-1 flex-col" @submit="validateForm($event)">
                @csrf
                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6">
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_route') }} <span class="text-rose-500">*</span></label>
                        <select name="route_id" x-model="form.route_id" required class="{{ $inp }}">
                            <option value="">{{ __('TripManagement::app.sch_f_route_select') }}</option>
                            @foreach($routes as $r)
                                <option value="{{ $r->id }}">{{ $r->code }} — {{ $r->origin_city }} → {{ $r->destination_city }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-400">{{ __('TripManagement::app.sch_hint_stops') }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_time') }} <span class="text-rose-500">*</span></label>
                        <input type="time" name="departure_time" x-model="form.departure_time" required class="{{ $inp }}" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_price') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="price" x-model="form.price" required step="any"
                               class="{{ $inp }}" :class="form.price !== '' && !isPriceValid() ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500' : ''" />
                        <p x-cloak x-show="form.price !== '' && !isPriceValid()" class="mt-1 text-xs text-rose-600" role="alert">
                            {{ __('TripManagement::app.sch_err_price') }}
                        </p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_from') }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="start_date" x-model="form.start_date" required class="{{ $inp }}" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_to') }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="end_date" x-model="form.end_date" required class="{{ $inp }}" />
                    </div>
                    <fieldset class="sm:col-span-2" aria-describedby="schedule-weekdays-hint">
                        <legend class="mb-2 text-sm font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_days') }}</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach(range(1, 7) as $v)
                                <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-xl px-2.5 text-sm font-semibold text-slate-700 hover:bg-futa-orange-soft/50">
                                    <input type="checkbox" name="days_of_week[]" value="{{ $v }}" x-model="form.days_of_week" class="peer sr-only">
                                    <span aria-hidden="true"
                                        class="grid size-5 shrink-0 place-items-center rounded-full border-2 border-slate-300 bg-white transition
                                            peer-checked:border-futa-orange peer-checked:[&>span]:opacity-100
                                            peer-focus-visible:ring-2 peer-focus-visible:ring-futa-orange/30 peer-focus-visible:ring-offset-2">
                                        <span class="size-2.5 rounded-full border-2 border-futa-orange opacity-0 transition"></span>
                                    </span>
                                    <span class="peer-checked:text-futa-orange">{{ __('TripManagement::app.day_'.$v) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p id="schedule-weekdays-hint" class="mt-2 text-xs leading-5 text-slate-500">{{ __('TripManagement::app.sch_hint_all_days') }}</p>
                    </fieldset>
                </div>

                <input type="hidden" name="apply_all_days" value="1" :disabled="form.days_of_week.length > 0">

                </div>
                <footer class="flex shrink-0 items-center justify-end gap-3 border-t border-slate-200 bg-white px-6 py-4">
                    <button type="button" @click="showForm = false" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('TripManagement::app.btn_cancel') }}</button>
                    <button type="submit" class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-futa-orange-dark"
                            x-text="isEdit ? @js(__('TripManagement::app.btn_save')) : @js(__('TripManagement::app.btn_store'))"></button>
                </footer>
            </form>
    </x-core::side-panel>

    {{-- DEACTIVATE SCHEDULE MODAL --}}
    <form x-ref="deleteForm" :action="deleteAction" method="POST" hidden data-confirm
        :data-confirm-title="@js(__('TripManagement::app.sch_modal_delete'))"
        :data-confirm-message="@js(__('TripManagement::app.sch_delete_msg')) + ' ' + deleteData.name + '?' + ' ' + @js(__('TripManagement::app.sch_delete_note'))"
        data-confirm-label="{{ __('TripManagement::app.btn_confirm') }}">
        @csrf
        @method('DELETE')
    </form>

</div>

{{-- THÔNG BÁO TỪ SERVER (FutaNotify) --}}
@if(session('success') || session('warning') || $errors->any())
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.FutaNotify) return;
    @if(session('success'))
        FutaNotify.success(@json(session('success'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { key: 'flash-success' });
    @endif
    @if(session('warning'))
        FutaNotify.warning(@json(session('warning'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { key: 'flash-warning' });
    @endif
    @if($errors->any())
        FutaNotify.error(@json(implode(' • ', $errors->all()), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { key: 'flash-error', duration: 8000 });
    @endif
});
</script>
@endif

<script>
function scheduleManager() {
    const blank = { route_id:'', departure_time:'', days_of_week:[], start_date:'', end_date:'', price:'' };
    const base = `{{ url('quan-tri/chuyen-xe/lich-trinh') }}`;
    const today = `{{ today()->toDateString() }}`;
    const t = {
        errDate: @js(__('TripManagement::app.sch_err_date')),
        errPast: @js(__('TripManagement::app.sch_err_past')),
    };
    const notify = (type, msg, opts) => window.FutaNotify && window.FutaNotify[type](msg, opts);

    return {
        open: {!! json_encode((object) ($open ? [(int) $open => true] : [])) !!},
        showForm: false, isEdit: false,
        form: { ...blank }, formAction: base,
        deleteData: {}, deleteAction: '',

        toggle(id)   { this.open[id] = !this.open[id]; },
        openAdd()    { this.isEdit = false; this.form = { ...blank, days_of_week: [] }; this.formAction = base; this.showForm = true; },
        openEdit(d)  { this.isEdit = true; this.form = { ...blank, ...d, days_of_week: (d.days_of_week || []).map(String) }; this.formAction = `${base}/${d.id}`; this.showForm = true; },
        openDelete(d){ this.deleteData = d; this.deleteAction = `${base}/${d.id}`; this.$nextTick(() => this.$refs.deleteForm.requestSubmit()); },

        isPriceValid() {
            const price = Number(this.form.price);
            return Number.isInteger(price) && price > 1000 && price < 100000000;
        },

        // Kiểm tra sớm ở client, server vẫn kiểm tra lại
        validateForm(e) {
            const f = this.form;
            if (f.price !== '' && !this.isPriceValid()) {
                e.preventDefault();
                return;
            }
            if (f.start_date && f.end_date && f.end_date < f.start_date) {
                e.preventDefault();
                return notify('error', t.errDate, { key: 'sch-error' });
            }
            if (f.end_date && f.end_date < today) {
                e.preventDefault();
                return notify('error', t.errPast, { key: 'sch-error' });
            }
        },

    };
}
</script>
@endsection
