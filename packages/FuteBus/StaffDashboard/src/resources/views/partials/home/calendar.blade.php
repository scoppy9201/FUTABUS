<section class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm" aria-label="{{ __('StaffDashboard::activity.title') }}">
    <div class="flex items-center justify-between gap-2">
        <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900"><x-heroicon-o-calendar-days class="size-5 text-futa-green" />{{ __('StaffDashboard::activity.title') }}</h2>
        <button type="button" @click="showActivityToday()" :disabled="activityLoading" class="text-xs font-semibold text-futa-green disabled:opacity-50">{{ __('StaffDashboard::activity.today') }}</button>
    </div>
    <div class="my-3 flex items-center justify-between gap-2">
        <button type="button" @click="changeActivityMonth(-1)" :disabled="activityLoading" aria-label="{{ __('StaffDashboard::activity.previous') }}"
            class="rounded-lg p-2 text-slate-500 hover:bg-futa-green/5 disabled:opacity-40"><x-heroicon-o-chevron-left class="size-4" /></button>
        <p class="text-sm font-semibold capitalize text-slate-800" x-text="activityMonthTitle()" aria-live="polite"></p>
        <button type="button" @click="changeActivityMonth(1)" :disabled="activityLoading" aria-label="{{ __('StaffDashboard::activity.next') }}"
            class="rounded-lg p-2 text-slate-500 hover:bg-futa-green/5 disabled:opacity-40"><x-heroicon-o-chevron-right class="size-4" /></button>
    </div>
    <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold text-slate-400">
        @foreach(__('StaffDashboard::activity.weekdays') as $weekday)<span class="py-1">{{ $weekday }}</span>@endforeach
    </div>
    <div class="grid grid-cols-7 gap-1" :aria-busy="activityLoading">
        <template x-for="cell in activityCells" :key="cell.key">
            <div class="min-w-0">
                <button type="button" x-show="cell.date" @click="openActivityDay(cell)" :disabled="activityLoading"
                    :aria-label="activityDateLabel(cell.date) + '. ' + @js(__('StaffDashboard::activity.open_day'))"
                    :aria-current="cell.date === activityToday ? 'date' : null"
                    class="flex h-10 w-full flex-col items-center justify-center rounded-lg text-xs transition focus-visible:outline-2 focus-visible:outline-futa-green disabled:opacity-50"
                    :class="cell.date === activitySelectedDate ? 'bg-futa-green text-white' : (cell.date === activityToday ? 'bg-futa-green/5 font-bold text-futa-green ring-1 ring-inset ring-futa-green/20' : 'text-slate-700 hover:bg-slate-50')">
                    <span x-text="Number(cell.date?.slice(-2))"></span>
                    <span class="mt-1 flex h-1 gap-0.5" aria-hidden="true">
                        <span x-show="cell.issued > 0" class="size-1 rounded-full bg-emerald-500"></span>
                        <span x-show="cell.cancelled > 0" class="size-1 rounded-full bg-futa-orange"></span>
                        <span x-show="activityPending(cell) > 0" class="size-1 rounded-full bg-amber-400"></span>
                    </span>
                </button>
            </div>
        </template>
    </div>
    <div x-show="activityLoading && !activityDays.length" class="grid grid-cols-7 gap-2 py-2" aria-hidden="true">
        @foreach(range(1, 35) as $cell)<span class="h-8 animate-pulse rounded-lg bg-slate-50 motion-reduce:animate-none"></span>@endforeach
    </div>
    <div class="mt-4 flex flex-wrap justify-center gap-x-3 gap-y-1 border-t border-slate-100 pt-3 text-[10px] text-slate-500">
        @foreach(['issued' => 'bg-emerald-500', 'cancelled' => 'bg-futa-orange', 'pending' => 'bg-amber-400'] as $kind => $color)
            <span class="inline-flex items-center gap-1"><span class="size-1.5 rounded-full {{ $color }}"></span>{{ __('StaffDashboard::activity.legend.'.$kind) }}</span>
        @endforeach
    </div>
    <button x-show="activityFailed" x-cloak type="button" @click="loadActivity()" class="mt-3 text-xs text-futa-orange">{{ __('StaffDashboard::activity.retry') }}</button>
</section>

<x-core::side-panel id="staff-activity-day" x-ref="activityPanel" :title="__('StaffDashboard::activity.day_title')">
    <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-6">
        <p class="text-lg font-bold text-futa-green" x-text="activityDateLabel(activitySelectedDate)"></p>
        <p class="text-sm leading-6 text-slate-500">{{ __('StaffDashboard::activity.day_intro') }}</p>
        <div class="grid grid-cols-2 gap-3">
            @foreach(['issued' => 'ticket', 'cancelled' => 'x-circle', 'support_pending' => 'chat-bubble-left-right', 'refund_pending' => 'banknotes'] as $kind => $icon)
                <article class="rounded-xl border border-slate-200 p-4">
                    <x-dynamic-component :component="'heroicon-o-'.$icon" class="mb-3 size-5 text-futa-green" />
                    <p class="text-xs leading-5 text-slate-500">{{ __('StaffDashboard::activity.counts.'.$kind) }}</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900" x-text="activitySelectedDay?.[@js($kind)] ?? '—'"></p>
                </article>
            @endforeach
        </div>
        <p class="rounded-xl bg-futa-green/5 p-4 text-xs leading-6 text-futa-green">{{ __('StaffDashboard::activity.basis') }}</p>
        @if(auth()->user()->bus_company_id && auth()->user()->hasPermissionTo('support.view'))
            <a href="{{ route('staff.support') }}" class="flex items-center justify-between rounded-xl border border-slate-200 p-4 text-sm font-semibold text-futa-green">
                {{ __('StaffDashboard::activity.open_support') }}<x-heroicon-o-arrow-right class="size-4" />
            </a>
        @endif
        @if(auth()->user()->bus_company_id && auth()->user()->hasPermissionTo('payment.view'))
            <a href="{{ route('staff.refunds.index') }}" class="flex items-center justify-between rounded-xl border border-slate-200 p-4 text-sm font-semibold text-futa-green">
                {{ __('StaffDashboard::activity.open_refunds') }}<x-heroicon-o-arrow-right class="size-4" />
            </a>
        @endif
        <p x-show="activityFailed" class="text-xs text-futa-orange">{{ __('StaffDashboard::activity.stale') }}</p>
    </div>
</x-core::side-panel>
