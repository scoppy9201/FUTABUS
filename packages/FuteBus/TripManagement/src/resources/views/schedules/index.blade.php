@extends('Dashboard::layouts.admin')

@section('title', __('TripManagement::app.sch_title'))

@section('content')
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
                        @php $days = json_decode($s->days_of_week, true) ?: []; @endphp
                        <tr class="hover:bg-futa-orange-soft/30">
                            <td class="px-5 py-4 text-slate-500">{{ $schedules->firstItem() + $loop->index }}</td>
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
                                    <button @click="openEdit({{ json_encode([
                                        'id' => $s->id, 'route_id' => $s->route_id,
                                        'departure_time' => substr($s->departure_time, 0, 5),
                                        'duration_minutes' => $s->duration_minutes,
                                        'days_of_week' => $days,
                                        'start_date' => $s->start_date, 'end_date' => $s->end_date,
                                        'price' => (int) $s->price,
                                    ]) }})" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-futa-orange hover:text-futa-orange">
                                        <x-heroicon-o-pencil-square class="size-3.5" /> {{ __('TripManagement::app.btn_edit') }}
                                    </button>
                                    <button @click="openDelete({{ json_encode(['id' => $s->id, 'name' => $s->origin_city.' → '.$s->destination_city]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:border-rose-300 hover:bg-rose-50">
                                        <x-heroicon-o-no-symbol class="size-3.5" /> {{ __('TripManagement::app.btn_delete') }}
                                    </button>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-16 text-center text-sm font-semibold text-slate-500">{{ __('TripManagement::app.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schedules->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $schedules->links() }}</div>
        @endif
    </div>

    {{-- ADD / EDIT MODAL --}}
    <div x-show="showForm" x-cloak @click.self="showForm = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between px-5 py-3"
                 :class="isEdit ? 'bg-slate-800' : 'bg-futa-orange'">
                <h2 class="text-base font-bold text-white"
                    x-text="isEdit ? @js(__('TripManagement::app.sch_modal_edit')) : @js(__('TripManagement::app.sch_modal_add'))"></h2>
                <button type="button" @click="showForm = false" class="text-white/70 hover:text-white"><x-heroicon-o-x-mark class="size-4" /></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-3 p-5">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_route') }} <span class="text-rose-500">*</span></label>
                        <select name="route_id" x-model="form.route_id" required
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
                            <option value="">{{ __('TripManagement::app.sch_f_route_select') }}</option>
                            @foreach($routes as $r)
                                <option value="{{ $r->id }}">{{ $r->code }} — {{ $r->origin_city }} → {{ $r->destination_city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_time') }} <span class="text-rose-500">*</span></label>
                        <input type="time" name="departure_time" x-model="form.departure_time" required
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_duration') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="duration_minutes" x-model="form.duration_minutes" required min="0" max="2880"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_from') }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="start_date" x-model="form.start_date" required
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_to') }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="end_date" x-model="form.end_date" required
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_price') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="price" x-model="form.price" required min="0"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_days') }} <span class="text-rose-500">*</span></label>
                        <div class="flex flex-wrap gap-4">
                            @foreach(range(1, 7) as $v)
                                <label class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <input type="checkbox" name="days_of_week[]" value="{{ $v }}" x-model="form.days_of_week">
                                    {{ __('TripManagement::app.day_'.$v) }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showForm = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('TripManagement::app.btn_cancel') }}</button>
                    <button type="submit" class="rounded-lg bg-futa-orange px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-futa-orange-dark"
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
            <h3 class="text-lg font-bold text-slate-900">{{ __('TripManagement::app.sch_modal_delete') }}</h3>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('TripManagement::app.sch_delete_msg') }}
                <span class="font-bold text-slate-800" x-text="deleteData.name"></span>?
            </p>
            <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">{{ __('TripManagement::app.sch_delete_note') }}</p>
            <form :action="deleteAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDelete = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('TripManagement::app.btn_cancel') }}</button>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700">{{ __('TripManagement::app.btn_confirm') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
function scheduleManager() {
    const blank = { route_id:'', departure_time:'', duration_minutes:0, days_of_week:[],
                    start_date:'', end_date:'', price:'' };
    const base = `{{ url('quan-tri/chuyen-xe/lich-trinh') }}`;
    return {
        showForm: false, showDelete: false, isEdit: false,
        form: { ...blank }, formAction: base,
        deleteData: {}, deleteAction: '',
        openAdd()   { this.isEdit = false; this.form = { ...blank, days_of_week: [] }; this.formAction = base; this.showForm = true; },
        openEdit(d) { this.isEdit = true; this.form = { ...blank, ...d, days_of_week: (d.days_of_week || []).map(String) }; this.formAction = `${base}/${d.id}`; this.showForm = true; },
        openDelete(d){ this.deleteData = d; this.deleteAction = `${base}/${d.id}`; this.showDelete = true; },
    };
}
</script>
@endsection