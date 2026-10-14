<div data-refund-workspace data-error="{{ __('Payment::refunds.error') }}" data-saved="{{ __('Payment::refunds.saved') }}" class="mx-auto w-full space-y-5">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">FUTA Bus Lines</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('Payment::refunds.title') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ __('Payment::refunds.intro') }}</p>
        </div>
        <a
            href="{{ route($refundRoutes.'.index', array_merge($filters, ['export' => 'csv'])) }}" data-no-loader
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <x-heroicon-o-arrow-down-tray class="size-4" />{{ __('Payment::refunds.export') }}
        </a>
    </header>
    <section aria-label="{{ __('Payment::refunds.summary_scope') }}" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach(__('Payment::refunds.statuses') as $status => $label)
            <a href="{{ route($refundRoutes.'.index', ['status' => $status]) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-futa-orange/40 sm:p-5">
                <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
                <p @class(['mt-2 text-3xl font-extrabold',
                    'text-amber-700' => $status === 'refund_pending',
                    'text-blue-600' => $status === 'refund_processing',
                    'text-green-700' => $status === 'refunded',
                    'text-red-600' => $status === 'refund_failed'])>{{ $summary[$status]->total ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ __('Payment::refunds.total') }} {{ number_format($summary[$status]->amount ?? 0, 0, ',', '.') }} ₫</p>
            </a>
        @endforeach
    </section>
    <form method="get" class="flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <input name="search" value="{{ $filters['search'] ?? '' }}" aria-label="{{ __('Payment::refunds.search') }}" placeholder="{{ __('Payment::refunds.search') }}"
            class="min-w-48 flex-1 rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm focus:border-futa-orange focus:ring-futa-orange/20">
        <select name="status" aria-label="{{ __('Payment::refunds.status') }}" class="min-w-44 rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
            <option value="">{{ __('Payment::refunds.all') }}</option>
            @foreach(__('Payment::refunds.statuses') as $status => $label)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="period" aria-label="{{ __('Payment::refunds.period') }}" class="min-w-36 rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
            @foreach(['all', '7', '30'] as $period)
                <option value="{{ $period }}" @selected(($filters['period'] ?? 'all') === $period)>{{ __('Payment::refunds.period_'.$period) }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-futa-orange px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-futa-orange-dark">{{ __('Payment::refunds.find') }}</button>
    </form>
    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.7fr)_minmax(22rem,1fr)]">
        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="max-h-128 overflow-auto">
                <table class="w-full min-w-180 text-left text-sm">
                    <thead class="sticky top-0 z-10 border-b border-slate-200 bg-futa-orange-soft text-[11px] font-bold uppercase tracking-wide text-slate-600">
                        <tr>@foreach(['request', 'ticket', 'customer', 'amount', 'status'] as $key)
                            <th scope="col" class="px-4 py-4">{{ __('Payment::refunds.'.$key) }}</th>
                        @endforeach</tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($refunds as $refund)
                            <tr data-refund-row="{{ $refund->id }}" @class(['group cursor-pointer transition hover:bg-slate-50', 'bg-futa-orange-soft/50' => $selectedRefund?->id === $refund->id])>
                                <td class="max-w-48 border-l-2 border-l-transparent px-4 py-4 group-has-[[aria-current]]:border-l-futa-orange">
                                    <a data-refund-detail data-no-loader data-refund-id="{{ $refund->id }}" aria-controls="refund-details"
                                        @if($selectedRefund?->id === $refund->id) aria-current="true" @endif
                                        href="{{ route($refundRoutes.'.show', $refund->id) }}"
                                        class="block wrap-break-word font-bold text-slate-950 hover:text-futa-orange focus-visible:outline-futa-orange">{{ $refund->reference }}</a>
                                    <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($refund->cancelled_at)->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-slate-700">{{ $refund->ticket_code }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $refund->origin_city }} → {{ $refund->destination_city }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $refund->booking_code }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-slate-700">{{ $refund->customer_name }}</p>
                                    @if($refund->customer_phone)<p class="mt-1 text-xs text-slate-500">{{ $refund->customer_phone }}</p>@endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 font-bold text-slate-950">{{ number_format($refund->amount, 0, ',', '.') }} ₫</td>
                                <td class="px-4 py-4">@include('Payment::refunds.status', ['status' => $refund->status])</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-20 text-center text-slate-500">
                                    <x-core::empty-state :title="__('Payment::refunds.empty')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="space-y-3 border-t border-slate-100 p-4">
                <x-core::data-pagination :paginator="$refunds" />
            </footer>
        </section>
        <aside id="refund-details" data-refund-content aria-label="{{ __('Payment::refunds.view') }}" aria-busy="false" class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm xl:sticky xl:top-5 xl:max-h-[calc(100dvh-8rem)] xl:overflow-auto">
            @if($selectedRefund)
                @include('Payment::refunds.detail', ['refund' => $selectedRefund])
            @else
                <div class="px-6 py-16 text-center">
                    <x-core::empty-state :title="__('Payment::refunds.choose')" compact />
                </div>
            @endif
        </aside>
    </div>
</div>
@vite('packages/FuteBus/Payment/src/resources/js/app.js')
