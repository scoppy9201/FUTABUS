<section class="min-w-0" aria-label="{{ __('StaffDashboard::activity.queue_title') }}">
    <div class="mb-3 flex items-center justify-between gap-2">
        <h3 class="text-sm font-semibold">{{ __('StaffDashboard::activity.queue_title') }}</h3>
        <span class="inline-flex items-center gap-1.5 text-[10px] text-white/70">
            <span class="size-1.5 rounded-full" :class="activityFailed ? 'bg-amber-300' : (activityUpdatedAt ? 'bg-emerald-300' : 'bg-white/40')"></span>
            {{ __('StaffDashboard::activity.auto_refresh') }}
        </span>
    </div>
    <div class="staff-work-queue space-y-2" :aria-busy="activityLoading">
        <template x-for="item in activityQueue" :key="item.id">
            <a :href="item.url" class="block rounded-xl border border-white/20 bg-white/10 p-3 transition hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-white">
                <div class="flex items-center justify-between gap-2 text-xs">
                    <span class="font-semibold" x-text="(@js(__('StaffDashboard::activity.queue_kinds')))[item.kind]"></span>
                    <x-heroicon-o-arrow-up-right class="size-3.5 shrink-0 text-white/70" />
                </div>
                <p class="mt-1 truncate text-xs text-white/80" :title="item.title" x-text="item.title"></p>
                <p class="mt-1 truncate text-[10px] text-white/60" x-text="item.reference + ' · ' + activityDateLabel(item.created_at.slice(0, 10))"></p>
            </a>
        </template>
        <div x-show="!activityLoading && !activityFailed && !activityQueue.length" class="rounded-xl border border-white/15 bg-white/5 p-4">
            <x-heroicon-o-inbox class="mb-2 size-6 text-white/60" />
            <p class="text-xs leading-5 text-white/80">{{ __('StaffDashboard::activity.queue_empty') }}</p>
        </div>
        <p x-show="activityLoading && !activityUpdatedAt" class="rounded-xl bg-white/5 p-4 text-xs text-white/70">{{ __('StaffDashboard::staff.loading') }}</p>
    </div>
    <p x-show="activityFailed" x-cloak class="mt-2 text-xs text-amber-200">{{ __('StaffDashboard::activity.stale') }}</p>
</section>
