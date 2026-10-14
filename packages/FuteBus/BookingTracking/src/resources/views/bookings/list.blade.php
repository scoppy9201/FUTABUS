@vite(['packages/FuteBus/BookingTracking/src/resources/css/app.css', 'packages/FuteBus/BookingTracking/src/resources/js/app.js'])
<div data-booking-monitor class="space-y-5 text-sm">
    <header class="rounded-2xl border border-orange-100 bg-linear-to-r from-white to-futa-orange-soft p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl border border-slate-100 bg-white text-futa-orange shadow-sm">
                <x-heroicon-o-clipboard-document-list class="size-6" />
            </span>
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-950">{{ __('BookingTracking::bookings.title') }}</h1>
                <p class="mt-2 text-slate-500">{{ __('BookingTracking::bookings.intro') }}</p>
                <p class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                    <x-heroicon-o-eye class="size-4" />{{ __('BookingTracking::bookings.readonly') }}
                </p>
            </div>
        </div>
    </header>
    <form method="get" class="grid items-end gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:grid-cols-6">
        <label class="space-y-2 lg:col-span-2">
            <span class="font-medium text-slate-700">{{ __('BookingTracking::bookings.search') }}</span>
            <input name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100"
                class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-futa-orange">
        </label>
        <label class="space-y-2">
            <span class="font-medium text-slate-700">{{ __('BookingTracking::bookings.status') }}</span>
            <select name="status" class="w-full rounded-xl border border-slate-200 px-4 py-3">
                <option value="">{{ __('BookingTracking::bookings.all') }}</option>
                @foreach(__('BookingTracking::bookings.statuses') as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        @foreach(['from', 'to'] as $field)
            <label class="space-y-2">
                <span class="font-medium text-slate-700">{{ __('BookingTracking::bookings.'.$field) }}</span>
                <input type="date" name="{{ $field }}" value="{{ $filters[$field] ?? '' }}"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3">
            </label>
        @endforeach
        <div class="flex items-center gap-3">
            <button class="rounded-xl bg-futa-orange px-5 py-3 font-bold text-white hover:bg-futa-orange-dark">{{ __('BookingTracking::bookings.find') }}</button>
            <a href="{{ route($bookingRoutes.'.index') }}" class="text-xs font-semibold text-slate-500 hover:text-futa-orange">{{ __('BookingTracking::bookings.reset') }}</a>
        </div>
        @if($errors->any())<p class="text-red-600 lg:col-span-6" role="alert">{{ $errors->first() }}</p>@endif
    </form>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-auto">
            <table class="w-full min-w-240 text-left">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase text-slate-500">
                    <tr>@foreach(['code', 'customer', 'trip', 'amount', 'status', 'payment_status', 'view'] as $key)
                        <th scope="col" class="px-5 py-4">{{ __('BookingTracking::bookings.'.$key) }}</th>
                    @endforeach</tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $record)
                        <tr class="align-top hover:bg-slate-50/70">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-950">{{ $record->code }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ __('BookingTracking::bookings.'.($record->kind === 'booking' ? 'booking' : 'unissued')) }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $record->created_at ? \Illuminate\Support\Carbon::parse($record->created_at)->format('d/m/Y H:i') : '—' }}</p>
                            </td>
                            <td class="px-5 py-4"><p class="font-medium">{{ $record->customer_name ?? '—' }}</p><p class="mt-1 text-xs text-slate-500">{{ $record->customer_phone ?? '—' }}</p></td>
                            <td class="px-5 py-4">
                                <p>{{ $record->origin_city }} → {{ $record->destination_city }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($record->departure_time)->format('d/m/Y H:i') }} · {{ $record->seat_count }} {{ __('BookingTracking::bookings.seats') }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 font-bold">{{ $record->amount === null ? '—' : number_format($record->amount, 0, ',', '.').' ₫' }}</td>
                            <td class="px-5 py-4">@include('BookingTracking::bookings.status', ['status' => $record->status])</td>
                            <td class="px-5 py-4">
                                @if($record->payment_status)
                                    @include('BookingTracking::bookings.status', ['status' => $record->payment_status, 'group' => 'payment_statuses'])
                                @else<span class="text-xs text-slate-500">{{ __('BookingTracking::bookings.unpaid') }}</span>@endif
                            </td>
                            <td class="px-5 py-4">
                                <a data-booking-detail data-no-loader href="{{ route($bookingRoutes.'.show', ['kind' => $record->kind, 'record' => $record->id]) }}"
                                    class="inline-flex items-center gap-2 whitespace-nowrap rounded-xl border border-orange-200 px-3 py-2 font-semibold text-futa-orange hover:bg-futa-orange-soft">
                                    <x-heroicon-o-eye class="size-4" />{{ __('BookingTracking::bookings.view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-16">
                            <x-core::empty-state :title="__('BookingTracking::bookings.empty')" :description="__('BookingTracking::bookings.empty_hint')" />
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="border-t border-slate-100 p-4"><x-core::data-pagination :paginator="$records" /></footer>
    </section>
    <x-core::side-panel id="booking-monitor-detail" :title="__('BookingTracking::bookings.detail')" :description="__('BookingTracking::bookings.readonly')">
        <div data-booking-loading hidden role="status" class="flex items-center justify-center gap-3 p-10 text-slate-500">
            <x-heroicon-o-arrow-path class="size-5 animate-spin" />{{ __('BookingTracking::bookings.loading') }}
        </div>
        <div data-booking-error hidden role="alert" class="space-y-4 p-6 text-center">
            <p class="text-red-600">{{ __('BookingTracking::bookings.error') }}</p>
            <button type="button" data-booking-retry class="rounded-xl border border-slate-200 px-4 py-2 font-semibold">{{ __('BookingTracking::bookings.retry') }}</button>
        </div>
        <div data-booking-content class="min-h-0 flex-1 overflow-auto" aria-live="polite"></div>
        <footer class="shrink-0 border-t border-slate-200 px-6 py-4">
            <form method="dialog" data-no-loader class="flex justify-end">
                <button class="rounded-xl border border-slate-200 px-5 py-2.5 font-semibold text-slate-700">{{ __('BookingTracking::bookings.close') }}</button>
            </form>
        </footer>
    </x-core::side-panel>
</div>
