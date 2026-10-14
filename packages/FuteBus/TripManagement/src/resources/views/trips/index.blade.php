@extends('Dashboard::layouts.admin')

@section('title', __('TripManagement::app.tr_title'))

@section('content')
<div x-data="tripManager()">

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('TripManagement::app.tr_title') }}</h1>
            <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">{{ __('TripManagement::app.tr_subtitle') }}</p>
        </div>
        <span class="rounded-full bg-futa-orange-soft px-4 py-2 text-sm font-bold text-futa-orange-dark">
            {{ number_format($trips->total()) }} {{ __('Dashboard::app.records') }}
        </span>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('trip-management.trips.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('TripManagement::app.tr_search_ph') }}"
                class="w-64 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
            <input type="date" name="date" value="{{ $date }}"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-futa-orange focus:outline-none" />
            <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-futa-orange focus:outline-none">
                <option value="">{{ __('TripManagement::app.tr_filter_all') }}</option>
                @foreach(['unassigned','scheduled','departed','arrived','cancelled'] as $st)
                    <option value="{{ $st }}" @selected($status === $st)>{{ __('Dashboard::app.status.'.$st) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('TripManagement::app.btn_search') }}</button>
            @if($search || $status || $date)
                <a href="{{ route('trip-management.trips.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500">{{ __('TripManagement::app.btn_clear_filter') }}</a>
            @endif
        </form>
        <button @click="openAdd()" class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white shadow-md hover:opacity-90">
            <x-heroicon-o-plus class="size-4" /> {{ __('TripManagement::app.tr_btn_add') }}
        </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-250 text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">{{ __('TripManagement::app.col_stt') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.sch_f_route') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.tr_f_departure') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.tr_f_bus') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.tr_col_seats') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.tr_f_price') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.tr_col_source') }}</th>
                        <th class="px-5 py-4">{{ __('TripManagement::app.col_status') }}</th>
                        <th class="px-5 py-4 text-center">{{ __('TripManagement::app.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($trips as $t)
                        @php $open = in_array($t->status, ['unassigned', 'scheduled'], true); @endphp
                        <tr class="hover:bg-futa-orange-soft/30">
                            <td class="px-5 py-4 text-slate-500">{{ $trips->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $t->origin_city }} → {{ $t->destination_city }}
                                <span class="block text-xs font-medium text-slate-400">{{ $t->route_code }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                {{ \Illuminate\Support\Carbon::parse($t->departure_time)->format('H:i · d/m/Y') }}
                                <span class="block text-xs text-slate-400">{{ __('TripManagement::app.tr_arrival_short') }} {{ \Illuminate\Support\Carbon::parse($t->arrival_time)->format('H:i · d/m') }}</span>
                            </td>
                            <td class="px-5 py-4">{{ $t->license_plate ?? '—' }}</td>
                            <td class="px-5 py-4">{{ $t->license_plate ? $t->remaining_seats.'/'.$t->capacity : '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4">{{ number_format($t->price, 0, ',', '.') }} ₫</td>
                            <td class="px-5 py-4 text-xs font-semibold text-slate-500">
                                {{ $t->trip_schedule_id ? __('TripManagement::app.tr_src_schedule') : __('TripManagement::app.tr_src_manual') }}
                            </td>
                            <td class="px-5 py-4">@include('Dashboard::partials.status', ['value' => $t->status])</td>
                            <td class="px-5 py-4">
                                @if($open)
                                <div class="flex items-center justify-center gap-2">
                                    @if($t->ticket_count == 0)
                                    <button @click="openEdit({{ json_encode([
                                        'id' => $t->id, 'route_id' => $t->route_id, 'bus_id' => $t->bus_id,
                                        'departure_time' => \Illuminate\Support\Carbon::parse($t->departure_time)->format('Y-m-d\TH:i'),
                                        'price' => (int) $t->price, 'from_schedule' => $t->trip_schedule_id !== null,
                                    ]) }})" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-futa-orange hover:text-futa-orange">
                                        <x-heroicon-o-pencil-square class="size-3.5" /> {{ __('TripManagement::app.tr_btn_edit') }}
                                    </button>
                                    @endif
                                    <button @click="openCancel({{ json_encode([
                                    'id' => $t->id, 'name' => $t->origin_city.' → '.$t->destination_city, 'tickets' => (int) $t->ticket_count,
                                    'mode' => $t->status === 'unassigned' ? 'delete' : 'cancel',
                                ]) }})" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:border-rose-300 hover:bg-rose-50">
                                    @if($t->status === 'unassigned')
                                        <x-heroicon-o-trash class="size-3.5" /> {{ __('TripManagement::app.tr_btn_delete') }}
                                    @else
                                        <x-heroicon-o-no-symbol class="size-3.5" /> {{ __('TripManagement::app.tr_btn_cancel') }}
                                    @endif
                                </button>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center text-sm font-semibold text-slate-500">
                                <x-core::empty-state :title="__('TripManagement::app.tr_empty')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4"><x-core::data-pagination :paginator="$trips" /></div>
    </div>

    {{-- ADD / EDIT MODAL --}}
    <div role="dialog" aria-modal="true" x-show="showForm" x-cloak @click.self="closeForm()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-futa-orange px-5 py-3">
                <h2 class="text-base font-bold text-white"
                    x-text="isEdit ? @js(__('TripManagement::app.tr_modal_edit')) : @js(__('TripManagement::app.tr_modal_add'))"></h2>
                <button type="button" @click="closeForm()" class="text-white/70 hover:text-white"><x-heroicon-o-x-mark class="size-4" /></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-3 p-5" @submit="validateSubmit($event)">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <p x-show="locked" class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700">
                    {{ __('TripManagement::app.tr_locked_note') }}
                </p>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_route') }} <span class="text-rose-500">*</span></label>
                    <select name="route_id" x-model="form.route_id" :disabled="locked" required
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm disabled:bg-slate-100 focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
                        <option value="">{{ __('TripManagement::app.sch_f_route_select') }}</option>
                        @foreach($routes as $r)
                            <option value="{{ $r->id }}">{{ $r->code }} — {{ $r->origin_city }} → {{ $r->destination_city }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.tr_f_departure') }} <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="departure_time" x-model="form.departure_time" :disabled="locked" required
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm disabled:bg-slate-100 focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">
                            {{ __('TripManagement::app.tr_f_bus') }} <span x-show="!isEdit" class="text-rose-500">*</span>
                        </label>
                        <select name="bus_id" x-model="form.bus_id" :required="!isEdit"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
                            <option value="">{{ __('TripManagement::app.tr_f_bus_select') }}</option>
                            @foreach($buses as $b)
                                <option value="{{ $b->id }}">{{ $b->license_plate }} ({{ $b->capacity }} {{ __('BusManagement::app.field_capacity_unit') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.tr_f_price') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="price" x-model="form.price" required min="1" step="any"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="closeForm()" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('TripManagement::app.btn_cancel') }}</button>
                    <button type="submit"
                            class="rounded-lg bg-futa-orange px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-futa-orange-dark"
                            x-text="isEdit ? @js(__('TripManagement::app.btn_save')) : @js(__('TripManagement::app.btn_store'))"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- CANCEL MODAL --}}
    <div role="dialog" aria-modal="true" x-show="showCancel" x-cloak @click.self="showCancel = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto mb-4 grid size-14 place-items-center rounded-full bg-rose-100">
                <x-heroicon-o-exclamation-triangle class="size-7 text-rose-500" />
            </div>
            <h3 class="text-lg font-bold text-slate-900"
            x-text="cancelData.mode === 'delete' ? @js(__('TripManagement::app.tr_modal_delete')) : @js(__('TripManagement::app.tr_modal_cancel'))"></h3>
            <p class="mt-2 text-sm text-slate-500">
                <span x-text="cancelData.mode === 'delete' ? @js(__('TripManagement::app.tr_delete_msg')) : @js(__('TripManagement::app.tr_cancel_msg'))"></span>
                <span class="font-bold text-slate-800" x-text="cancelData.name"></span>?
            </p>
            <p x-show="cancelData.mode === 'delete'" class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-left text-xs text-rose-700">
                {{ __('TripManagement::app.tr_delete_note') }}
            </p>
            <p x-show="cancelData.mode !== 'delete' && cancelData.tickets > 0" class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-left text-xs text-amber-700">
                <span x-text="cancelData.tickets"></span> {{ __('TripManagement::app.tr_cancel_note_tickets') }}
            </p>
            <form :action="cancelAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="showCancel = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('TripManagement::app.btn_cancel') }}</button>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700">{{ __('TripManagement::app.btn_confirm') }}</button>
            </form>
        </div>
    </div>
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
function tripManager() {
    const blank = { route_id:'', bus_id:'', departure_time:'', price:'' };
    const base = `{{ url('quan-tri/chuyen-xe') }}`;
    const requiredTypes = @json($requiredTypes);
    const busDocs = @json($busDocs);
    const today = `{{ today()->toDateString() }}`;
    const t = {
        docWarn:    {{ Js::from(__('TripManagement::app.tr_doc_warn')) }},
        docMissing: {{ Js::from(__('TripManagement::app.tr_doc_missing')) }},
        docExpired: {{ Js::from(__('TripManagement::app.tr_doc_expired')) }},
        errPast:    {{ Js::from(__('TripManagement::app.tr_err_past')) }},
    };
    const notify = (type, msg, opts) => window.FutaNotify && window.FutaNotify[type](msg, opts);

    return {
        showForm: false, showCancel: false, isEdit: false, locked: false,
        form: { ...blank }, formAction: base,
        cancelData: {}, cancelAction: '',

        init() {
            // Tự động cảnh báo giấy tờ khi đổi xe / giờ khởi hành
            this.$watch('busProblems', () => this.syncDocToast());
        },

        get busProblems() {
            if (!this.form.bus_id) return [];
            const day = this.form.departure_time ? this.form.departure_time.slice(0, 10) : today;
            const docs = busDocs[this.form.bus_id] || {};
            const out = [];
            requiredTypes.forEach(r => {
                const exp = docs[r.id];
                if (!exp) out.push({ name: r.name, reason: 'missing' });
                else if (exp < day) out.push({ name: r.name, reason: 'expired', expiry: exp.split('-').reverse().join('/') });
            });
            return out;
        },

        docMessage() {
            const lines = this.busProblems.map(p => p.reason === 'missing'
                ? `${p.name} — ${t.docMissing}`
                : `${p.name} — ${t.docExpired} (${p.expiry})`);
            return `${t.docWarn} ${lines.join('; ')}`;
        },

        syncDocToast() {
            if (this.showForm && this.busProblems.length > 0) {
                notify('warning', this.docMessage(), { key: 'bus-docs', duration: 8000 });
            } else if (window.FutaNotify) {
                FutaNotify.close('bus-docs');
            }
        },

        validateSubmit(e) {
            if (this.busProblems.length > 0) {
                e.preventDefault();
                notify('error', this.docMessage(), { key: 'bus-docs', duration: 8000 });
                return;
            }
            if (!this.locked && this.form.departure_time && new Date(this.form.departure_time) <= new Date()) {
                e.preventDefault();
                notify('error', t.errPast, { key: 'trip-past' });
            }
        },

        closeForm() {
            this.showForm = false;
            if (window.FutaNotify) { FutaNotify.close('bus-docs'); FutaNotify.close('trip-past'); }
        },

        openAdd()    { this.isEdit = false; this.locked = false; this.form = { ...blank }; this.formAction = base; this.showForm = true; },
        openEdit(d)  { this.isEdit = true; this.locked = d.from_schedule; this.form = { ...blank, ...d, bus_id: d.bus_id ?? '' }; this.formAction = `${base}/${d.id}`; this.showForm = true; this.syncDocToast(); },
        openCancel(d){ this.cancelData = d; this.cancelAction = `${base}/${d.id}`; this.showCancel = true; },
    };
}
</script>
@endsection
