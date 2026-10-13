@extends('Dashboard::layouts.admin')
@section('title', __('Payment::refunds.title'))
@section('content')
    <div class="mx-auto max-w-7xl space-y-5">
        <header>
            <h1 class="text-2xl font-bold text-gray-950">{{ __('Payment::refunds.title') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ __('Payment::refunds.intro') }}</p>
        </header>
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach(__('Payment::refunds.statuses') as $status => $label)
                <a href="{{ route('refunds.index', ['status' => $status]) }}" class="rounded-2xl border border-gray-200 bg-white p-5">
                    <p class="text-sm text-slate-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-bold text-futa-orange">{{ $counts[$status] ?? 0 }}</p>
                </a>
            @endforeach
        </div>
        <form method="get" class="flex flex-wrap gap-3 rounded-2xl border border-gray-200 bg-white p-4">
            <input name="search" value="{{ $filters['search'] ?? '' }}" aria-label="{{ __('Payment::refunds.search') }}"
                placeholder="{{ __('Payment::refunds.search') }}" class="min-w-48 flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm">
            <select name="status" aria-label="{{ __('Payment::refunds.status') }}" class="min-w-48 rounded-xl border border-gray-200 px-4 py-3 text-sm">
                <option value="">{{ __('Payment::refunds.all') }}</option>
                @foreach(__('Payment::refunds.statuses') as $status => $label)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-futa-orange px-6 py-3 text-sm font-bold text-white">{{ __('Payment::refunds.find') }}</button>
        </form>
        <div class="max-h-112 overflow-auto rounded-2xl border border-gray-200 bg-white">
            <table class="w-full min-w-200 text-left text-sm">
                <thead class="sticky top-0 bg-futa-orange-soft"><tr>
                    @foreach(['ticket', 'customer', 'amount', 'status'] as $key)
                        <th class="px-5 py-4">{{ __('Payment::refunds.'.$key) }}</th>
                    @endforeach
                    <th class="px-5 py-4"><span class="sr-only">{{ __('Payment::refunds.view') }}</span></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($refunds as $refund)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-bold text-futa-orange">{{ $refund->ticket_code }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $refund->booking_code }}</p>
                            </td>
                            <td class="px-5 py-4">{{ $refund->customer_name }}</td>
                            <td class="px-5 py-4 font-bold">{{ number_format($refund->amount, 0, ',', '.') }} ₫</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold">{{ __('Payment::refunds.statuses.'.$refund->status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <a data-refund-detail data-no-loader href="{{ route('refunds.show', $refund->id) }}" class="font-semibold text-futa-orange hover:underline">{{ __('Payment::refunds.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">{{ __('Payment::refunds.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $refunds->links() }}
    </div>
    <dialog data-refund-dialog data-error="{{ __('Payment::refunds.error') }}" data-saved="{{ __('Payment::refunds.saved') }}"
        class="m-auto max-h-[calc(100dvh-2rem)] w-[min(56rem,calc(100vw-2rem))] overflow-auto rounded-2xl border border-gray-200 bg-white p-0 shadow-2xl backdrop:bg-black/60">
        <div data-refund-content></div>
    </dialog>
    @vite('packages/FuteBus/Payment/src/resources/js/app.js')
@endsection
