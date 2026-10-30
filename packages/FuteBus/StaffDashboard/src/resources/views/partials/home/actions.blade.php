@php
    $actor = auth()->user();
    $quickActions = [
        ['key' => 'sale', 'route' => 'staff.tickets.sale', 'icon' => 'ticket', 'allowed' => $actor->hasPermissionTo('booking.create')],
        ['key' => 'lookup', 'route' => 'staff.ticket-lookup', 'icon' => 'magnifying-glass', 'allowed' => $actor->hasPermissionTo('ticket.view')],
        ['key' => 'exchange', 'route' => 'staff.tickets.exchange', 'icon' => 'arrows-right-left',
            'allowed' => $actor->hasPermissionTo('ticket.view') && $actor->hasPermissionTo('ticket.exchange')],
        ['key' => 'bookings', 'route' => 'staff.bookings.index', 'icon' => 'clipboard-document-list', 'allowed' => $actor->hasPermissionTo('booking.view')],
        ['key' => 'refund', 'route' => 'staff.refunds.index', 'icon' => 'banknotes', 'allowed' => $actor->hasPermissionTo('payment.view')],
        ['key' => 'support', 'route' => 'staff.support', 'icon' => 'chat-bubble-left-right', 'allowed' => $actor->hasPermissionTo('support.view')],
    ];
@endphp
<section id="tickets" aria-labelledby="staff-quick-actions">
    <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
        <h2 id="staff-quick-actions" class="text-lg font-bold text-slate-900">{{ __('StaffDashboard::staff.quick_actions') }}</h2>
        <p class="text-xs text-slate-500">{{ __('StaffDashboard::workspace.actions_hint') }}</p>
    </div>
    <span class="sr-only">{{ __('StaffDashboard::staff.tickets.title') }}</span>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 2xl:grid-cols-6">
        @foreach($quickActions as $action)
            @php $allowed = $actor->bus_company_id !== null && $action['allowed']; @endphp
            <a @if($allowed) href="{{ route($action['route']) }}" @else aria-disabled="true" @endif
                @class(['group flex min-w-0 flex-col rounded-2xl border bg-white p-4 transition',
                    'border-slate-200/70 hover:border-futa-green/25 hover:shadow-md focus-visible:outline-2 focus-visible:outline-futa-green' => $allowed,
                    'cursor-not-allowed border-slate-100 opacity-60' => !$allowed])>
                <div class="mb-4 flex items-center justify-between">
                    <span @class(['grid size-10 place-items-center rounded-xl',
                        'bg-futa-orange-soft text-futa-orange' => $action['key'] === 'sale',
                        'bg-futa-green/5 text-futa-green' => $action['key'] !== 'sale'])>
                        <x-dynamic-component :component="'heroicon-o-'.$action['icon']" class="size-5" />
                    </span>
                    @if($allowed)
                        <x-heroicon-o-arrow-up-right class="size-4 text-slate-400 group-hover:text-futa-green" />
                    @else
                        <x-heroicon-o-lock-closed class="size-4 text-slate-400" />
                    @endif
                </div>
                <span class="text-sm font-semibold text-slate-900">{{ __('StaffDashboard::workspace.actions.'.$action['key']) }}</span>
                <span class="mt-1 text-xs leading-5 text-slate-500">{{ $allowed ? __('StaffDashboard::workspace.action_descriptions.'.$action['key']) : __('StaffDashboard::workspace.access_required') }}</span>
            </a>
        @endforeach
    </div>
</section>
