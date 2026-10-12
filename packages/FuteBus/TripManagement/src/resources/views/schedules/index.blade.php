@extends('Dashboard::layouts.admin')

@section('title', __('TripManagement::app.sch_title'))

@section('content')
@php
    $inp = 'w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20';
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
                                    <button type="button" @click="openStopAdd({{ $s->route_id }}, {{ $s->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-futa-orange px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-futa-orange-dark">
                                        <x-heroicon-o-plus class="size-3.5" /> {{ __('TripManagement::app.rs_btn_add') }}
                                    </button>
                                </div>

                                @if($routeStops->isEmpty())
                                    <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                                        {{ __('TripManagement::app.rs_need_two') }}
                                    </p>
                                @endif

                                <ol class="mt-4">
                                    {{-- Bến xuất phát (cố định) --}}
                                    <li class="flex gap-4">
                                        <span class="w-16 shrink-0 pt-0.5 text-right text-sm font-bold text-slate-800">{{ $dep->format('H:i') }}</span>
                                        <div class="flex flex-col items-center">
                                            <span class="mt-1.5 size-3 shrink-0 rounded-full border-2 border-futa-orange bg-futa-orange"></span>
                                            @if($routeStops->isNotEmpty())<span class="w-px grow bg-slate-200"></span>@endif
                                        </div>
                                        <div class="min-w-0 flex-1 pb-5">
                                            <p class="text-sm font-bold text-slate-800">
                                                {{ $s->origin_city }}
                                                <span class="ml-1 rounded bg-futa-orange-soft px-1.5 py-0.5 text-[10px] font-semibold text-futa-orange-dark">{{ __('TripManagement::app.rs_departure_point') }}</span>
                                            </p>
                                        </div>
                                    </li>

                                    @php $run = 0; @endphp
                                    @foreach($routeStops as $st)
                                        @php
                                            $run += (int) $st->leg_minutes;
                                            $t = $dep->copy()->addMinutes($run);
                                            $plus = (int) $dep->copy()->startOfDay()->diffInDays($t->copy()->startOfDay());
                                        @endphp
                                        <li class="flex gap-4">
                                            <span class="w-16 shrink-0 pt-0.5 text-right text-sm font-bold text-slate-800">
                                                {{ $t->format('H:i') }}
                                                @if($plus > 0)<span class="block text-[10px] font-semibold text-slate-400">+{{ $plus }}</span>@endif
                                            </span>
                                            <div class="flex flex-col items-center">
                                                <span class="mt-1.5 size-3 shrink-0 rounded-full border-2 bg-white {{ $loop->last ? 'border-futa-orange' : 'border-slate-500' }}"></span>
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
                                                <p class="text-[11px] text-slate-400">+{{ $st->leg_minutes }} {{ __('TripManagement::app.rs_minutes') }}</p>
                                            </div>
                                            <div class="flex shrink-0 items-start gap-1.5">
                                                <button type="button" title="{{ __('TripManagement::app.btn_edit') }}"
                                                    @click="openStopEdit({{ json_encode([
                                                        'id' => $st->id, 'name' => $st->name, 'address' => $st->address,
                                                        'leg_minutes' => $st->leg_minutes, 'position' => $st->stop_order,
                                                        'stop_type' => $st->stop_type,
                                                    ]) }}, {{ $s->route_id }}, {{ $s->id }})"
                                                    class="rounded-lg border border-slate-200 bg-white p-1.5 text-slate-600 hover:border-futa-orange hover:text-futa-orange">
                                                    <x-heroicon-o-pencil-square class="size-3.5" />
                                                </button>
                                                <button type="button" title="{{ __('TripManagement::app.rs_btn_deactivate') }}"
                                                    @click="openStopDelete({{ json_encode(['id' => $st->id, 'name' => $st->name]) }}, {{ $s->id }})"
                                                    class="rounded-lg border border-slate-200 bg-white p-1.5 text-rose-600 hover:border-rose-300 hover:bg-rose-50">
                                                    <x-heroicon-o-trash class="size-3.5" />
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>

                                <p class="mt-1 rounded-lg bg-white px-3 py-2 text-xs text-slate-500">{{ __('TripManagement::app.rs_arrival_note') }}</p>
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

    {{-- ADD / EDIT SCHEDULE MODAL --}}
    <div x-show="showForm" x-cloak @click.self="showForm = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between px-5 py-3" :class="isEdit ? 'bg-slate-800' : 'bg-futa-orange'">
                <h2 class="text-base font-bold text-white"
                    x-text="isEdit ? @js(__('TripManagement::app.sch_modal_edit')) : @js(__('TripManagement::app.sch_modal_add'))"></h2>
                <button type="button" @click="showForm = false" class="text-white/70 hover:text-white"><x-heroicon-o-x-mark class="size-4" /></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-3 p-5" @submit="validateForm($event)">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_route') }} <span class="text-rose-500">*</span></label>
                        <select name="route_id" x-model="form.route_id" required class="{{ $inp }}">
                            <option value="">{{ __('TripManagement::app.sch_f_route_select') }}</option>
                            @foreach($routes as $r)
                                <option value="{{ $r->id }}">{{ $r->code }} — {{ $r->origin_city }} → {{ $r->destination_city }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-400">{{ __('TripManagement::app.sch_hint_stops') }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_time') }} <span class="text-rose-500">*</span></label>
                        <input type="time" name="departure_time" x-model="form.departure_time" required class="{{ $inp }}" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_price') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="price" x-model="form.price" required min="0" step="any" class="{{ $inp }}" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_from') }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="start_date" x-model="form.start_date" required class="{{ $inp }}" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.sch_f_to') }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="end_date" x-model="form.end_date" required class="{{ $inp }}" />
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

    {{-- DEACTIVATE SCHEDULE MODAL --}}
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

    {{-- ADD / EDIT STOP MODAL --}}
    <div x-show="showStop" x-cloak @click.self="showStop = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between px-5 py-3" :class="stopEdit ? 'bg-slate-800' : 'bg-futa-orange'">
                <h2 class="text-base font-bold text-white"
                    x-text="stopEdit ? @js(__('TripManagement::app.rs_modal_edit')) : @js(__('TripManagement::app.rs_modal_add'))"></h2>
                <button type="button" @click="showStop = false" class="text-white/70 hover:text-white"><x-heroicon-o-x-mark class="size-4" /></button>
            </div>
            <form :action="stopAction" method="POST" class="space-y-3 p-5">
                @csrf
                <template x-if="stopEdit"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="route_id" :value="stopRoute">
                <input type="hidden" name="schedule_id" :value="stopSchedule">

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.rs_f_name') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="stopForm.name" required maxlength="150" class="{{ $inp }}" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.rs_f_address') }}</label>
                    <input type="text" name="address" x-model="stopForm.address" maxlength="255" class="{{ $inp }}" />
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.rs_f_leg') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="leg_minutes" x-model="stopForm.leg_minutes" required min="1" max="1440" step="any" class="{{ $inp }}" />
                        <p class="mt-0.5 text-xs text-slate-400">{{ __('TripManagement::app.rs_leg_hint') }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.rs_f_position') }}</label>
                        <input type="number" name="position" x-model="stopForm.position" min="1" step="any" class="{{ $inp }}" />
                        <p class="mt-0.5 text-xs text-slate-400">{{ __('TripManagement::app.rs_position_hint') }}</p>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('TripManagement::app.rs_f_type') }}</label>
                    <select name="stop_type" x-model="stopForm.stop_type" class="{{ $inp }}">
                        <option value="both">{{ __('TripManagement::app.rs_type_both') }}</option>
                        <option value="pickup">{{ __('TripManagement::app.rs_type_pickup') }}</option>
                        <option value="dropoff">{{ __('TripManagement::app.rs_type_dropoff') }}</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showStop = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('TripManagement::app.btn_cancel') }}</button>
                    <button type="submit" class="rounded-lg bg-futa-orange px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-futa-orange-dark"
                            x-text="stopEdit ? @js(__('TripManagement::app.btn_save')) : @js(__('TripManagement::app.btn_store'))"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- DELETE STOP MODAL --}}
    <div x-show="showStopDel" x-cloak @click.self="showStopDel = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto mb-4 grid size-14 place-items-center rounded-full bg-rose-100">
                <x-heroicon-o-exclamation-triangle class="size-7 text-rose-500" />
            </div>
            <h3 class="text-lg font-bold text-slate-900">{{ __('TripManagement::app.rs_modal_deactivate') }}</h3>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('TripManagement::app.rs_deactivate_msg') }}
                <span class="font-bold text-slate-800" x-text="stopDel.name"></span>?
            </p>
            <form :action="stopDelAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                @method('DELETE')
                <input type="hidden" name="schedule_id" :value="stopSchedule">
                <button type="button" @click="showStopDel = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('TripManagement::app.btn_cancel') }}</button>
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
function scheduleManager() {
    const blank = { route_id:'', departure_time:'', days_of_week:[], start_date:'', end_date:'', price:'' };
    const stopBlank = { name:'', address:'', leg_minutes:10, position:'', stop_type:'both' };
    const base = `{{ url('quan-tri/chuyen-xe/lich-trinh') }}`;
    const stopBase = `{{ url('quan-tri/chuyen-xe/diem-dung') }}`;
    const today = `{{ today()->toDateString() }}`;
    const t = {
        errDays: @js(__('TripManagement::app.sch_err_days')),
        errDate: @js(__('TripManagement::app.sch_err_date')),
        errPast: @js(__('TripManagement::app.sch_err_past')),
    };
    const notify = (type, msg, opts) => window.FutaNotify && window.FutaNotify[type](msg, opts);

    return {
        open: {!! json_encode((object) ($open ? [(int) $open => true] : [])) !!},
        showForm: false, showDelete: false, isEdit: false,
        form: { ...blank }, formAction: base,
        deleteData: {}, deleteAction: '',
        showStop: false, showStopDel: false, stopEdit: false,
        stopForm: { ...stopBlank }, stopAction: stopBase,
        stopRoute: '', stopSchedule: '',
        stopDel: {}, stopDelAction: '',

        toggle(id)   { this.open[id] = !this.open[id]; },
        openAdd()    { this.isEdit = false; this.form = { ...blank, days_of_week: [] }; this.formAction = base; this.showForm = true; },
        openEdit(d)  { this.isEdit = true; this.form = { ...blank, ...d, days_of_week: (d.days_of_week || []).map(String) }; this.formAction = `${base}/${d.id}`; this.showForm = true; },
        openDelete(d){ this.deleteData = d; this.deleteAction = `${base}/${d.id}`; this.showDelete = true; },

        // Kiểm tra sớm ở client, server vẫn kiểm tra lại
        validateForm(e) {
            const f = this.form;
            if (!f.days_of_week.length) {
                e.preventDefault();
                return notify('error', t.errDays, { key: 'sch-error' });
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

        openStopAdd(routeId, scheduleId) {
            this.stopEdit = false; this.stopForm = { ...stopBlank };
            this.stopAction = stopBase; this.stopRoute = routeId; this.stopSchedule = scheduleId;
            this.showStop = true;
        },
        openStopEdit(s, routeId, scheduleId) {
            this.stopEdit = true; this.stopForm = { ...stopBlank, ...s, address: s.address ?? '', position: s.position ?? '' };
            this.stopAction = `${stopBase}/${s.id}`; this.stopRoute = routeId; this.stopSchedule = scheduleId;
            this.showStop = true;
        },
        openStopDelete(s, scheduleId) {
            this.stopDel = s; this.stopDelAction = `${stopBase}/${s.id}`; this.stopSchedule = scheduleId;
            this.showStopDel = true;
        },
    };
}
</script>
@endsection