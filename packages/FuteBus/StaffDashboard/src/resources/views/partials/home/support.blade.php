<section id="support" class="min-w-0 overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm">
    <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">
        <div>
            <h2 class="text-lg font-bold text-slate-900">{{ __('StaffDashboard::staff.recent_support') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ __('StaffDashboard::workspace.support_intro') }}</p>
        </div>
        @if(auth()->user()->bus_company_id && auth()->user()->hasPermissionTo('support.view'))
            <a href="{{ route('staff.support') }}" class="shrink-0 text-xs font-semibold text-futa-green hover:underline">{{ __('StaffDashboard::staff.view_all') }}</a>
        @endif
    </div>
    <div x-show="loading" class="space-y-4 p-5" aria-hidden="true">
        @foreach(range(1, 3) as $row)
            <div class="h-12 animate-pulse rounded-xl bg-slate-50 motion-reduce:animate-none"></div>
        @endforeach
    </div>
    <div x-cloak x-show="!loading && dashboard?.support_requests?.length" class="overflow-x-auto">
        <table class="w-full min-w-[560px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500">
                <tr>
                    @foreach(['request', 'content', 'status', 'actions'] as $column)
                        <th class="whitespace-nowrap px-5 py-3 font-semibold">{{ __('StaffDashboard::staff.'.$column) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="item in dashboard?.support_requests || []" :key="item.id">
                    <tr class="hover:bg-futa-green/5">
                        <td class="whitespace-nowrap px-5 py-4 text-xs font-semibold text-futa-green" x-text="item.reference"></td>
                        <td class="px-5 py-4">
                            <p class="max-w-64 truncate font-medium text-slate-800" :title="item.subject" x-text="item.subject"></p>
                            <p class="mt-1 text-xs text-slate-500" x-text="new Date(item.created_at).toLocaleDateString(document.documentElement.lang)"></p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="({ waiting: 'bg-amber-50 text-amber-700', received: 'bg-blue-50 text-blue-700', reviewing: 'bg-futa-orange-soft text-futa-orange-dark', resolved: 'bg-green-50 text-green-700' })[item.status] || 'bg-slate-100 text-slate-600'"
                                x-text="(@js(__('Profile::feedback.status')))[item.status]"></span>
                        </td>
                        <td class="px-5 py-4"><a :href="item.url" class="whitespace-nowrap text-xs font-semibold text-futa-green hover:underline">{{ __('StaffCustomerSupport::support.view') }}</a></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <div x-cloak x-show="!loading && !failed && !dashboard?.support_requests?.length" class="px-5 py-3">
        <x-core::empty-state :title="auth()->user()->hasPermissionTo('support.view') ? __('StaffCustomerSupport::support.empty') : __('StaffDashboard::staff.unavailable')"
            :description="__('StaffDashboard::workspace.support_empty_hint')" compact />
    </div>
    <div class="border-t border-slate-100 p-5">
        <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-800">
            <x-heroicon-o-book-open class="size-4 text-futa-green" />{{ __('StaffDashboard::workspace.guides') }}
        </h3>
        <template x-for="item in dashboard?.faq || []" :key="item.id">
            <a :href="item.url" class="flex items-start justify-between gap-3 border-t border-slate-100 py-3 text-sm text-slate-600 hover:text-futa-green">
                <span x-text="item.question"></span><x-heroicon-o-arrow-up-right class="mt-0.5 size-4 shrink-0" />
            </a>
        </template>
        <p x-show="!loading && !dashboard?.faq?.length" class="text-xs text-slate-500">{{ __('StaffDashboard::staff.no_faq') }}</p>
    </div>
</section>
