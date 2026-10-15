<div
    class="relative"
    x-data="{
        open: false,
        loading: false,
        failed: false,
        items: [],
        detailItem: null,
        detailHtml: '',
        detailLoading: false,
        detailFailed: false,
        unreadCount: {{ auth()->user()->unreadNotifications()->count() }},
        csrf: @js(csrf_token()),
        async load() {
            this.loading = true;
            this.failed = false;
            try {
                const response = await fetch(@js(route('notifications.index')), { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Unable to load notifications');
                const data = await response.json();
                this.items = data.notifications;
                this.unreadCount = data.unread_count;
            } catch (_) {
                this.failed = true;
            } finally {
                this.loading = false;
            }
        },
        async toggle() {
            this.open = !this.open;
            if (this.open) await this.load();
        },
        async read(item) {
            this.open = false;
            if (!item.read_at) {
                const url = @js(route('notifications.read', ['notification' => '__ID__'])).replace('__ID__', encodeURIComponent(item.id));
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.csrf },
                });
                if (!response.ok) return;
                const data = await response.json();
                item.read_at = new Date().toISOString();
                this.unreadCount = data.unread_count;
            }
            this.detailItem = item;
            this.detailHtml = '';
            this.detailFailed = false;
            this.$refs.detailPanel.showModal();
            if (!item.url) return;
            this.detailLoading = true;
            try {
                const response = await fetch(item.url, { headers: { Accept: 'text/html' } });
                if (!response.ok) throw new Error('Unable to load booking details');
                this.detailHtml = await response.text();
            } catch (_) {
                this.detailFailed = !item.message;
            } finally {
                this.detailLoading = false;
            }
        },
        closeDetail() {
            if (this.$refs.detailPanel?.open) this.$refs.detailPanel.close();
            this.resetDetail();
        },
        resetDetail() {
            this.detailItem = null;
            this.detailHtml = '';
        },
        init() {
            this.load();
            window.setInterval(() => this.load(), 15000);
        },
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    <button type="button" @click="toggle()" :aria-expanded="open" aria-haspopup="dialog"
        aria-label="{{ __('Notification::app.unread_count') }}"
        class="relative rounded-full p-2 text-gray-500 transition hover:bg-futa-orange-soft hover:text-futa-orange focus-visible:outline-2 focus-visible:outline-futa-orange">
        <x-heroicon-o-bell class="size-5" />
        <span x-cloak x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"
            class="absolute -right-0.5 -top-0.5 min-w-4 rounded-full bg-[#E31B23] px-1 text-center text-[10px] font-bold leading-4 text-white ring-2 ring-white"></span>
    </button>

    <section x-cloak x-show="open" x-transition.origin.top.right id="notification-menu" role="dialog"
        aria-label="{{ __('StaffDashboard::staff.notifications') }}"
        class="absolute right-0 top-full z-[80] mt-2 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl">
        <header class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <h2 class="text-sm font-bold text-gray-900">{{ __('StaffDashboard::staff.notifications') }}</h2>
            <span class="text-xs font-semibold text-gray-500" x-text="unreadCount"></span>
        </header>
        <p x-show="loading" class="px-4 py-5 text-center text-sm text-gray-500">{{ __('core::app.loading') }}</p>
        <p x-show="failed" class="px-4 py-5 text-center text-sm text-red-600">{{ __('Notification::app.load_failed') }}</p>
        <p x-show="!loading && !failed && items.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('Notification::app.empty') }}</p>
        <ul x-show="!loading && !failed && items.length > 0" class="max-h-96 divide-y divide-gray-100 overflow-y-auto">
            <template x-for="item in items" :key="item.id">
                <li>
                    <button type="button" @click="read(item)" :class="item.read_at ? 'bg-white' : 'bg-orange-50/70'"
                        class="block w-full px-4 py-3 text-left transition hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-inset focus-visible:outline-futa-orange">
                        <span class="flex items-start justify-between gap-3">
                            <strong class="text-sm font-semibold text-gray-900" x-text="item.summary || item.title"></strong>
                            <span x-show="!item.read_at" class="mt-1 size-2 shrink-0 rounded-full bg-futa-orange" aria-label="Chưa đọc"></span>
                        </span>
                        <span class="mt-1.5 block text-xs text-gray-400" x-text="item.created_at"></span>
                    </button>
                </li>
            </template>
        </ul>
    </section>

    <template x-teleport="body">
        <x-core::side-panel id="notification-detail-panel" :title="__('BookingTracking::bookings.detail')"
            :description="__('BookingTracking::bookings.readonly')" x-ref="detailPanel" @close="resetDetail()" @click.self="closeDetail()">
            <div class="min-h-0 flex-1 overflow-y-auto" aria-live="polite">
                <p x-show="detailLoading" class="p-8 text-center text-sm text-gray-500">{{ __('BookingTracking::bookings.loading') }}</p>
                <p x-show="detailFailed" class="p-8 text-center text-sm text-red-600">{{ __('BookingTracking::bookings.error') }}</p>
                <div x-show="detailHtml" x-html="detailHtml"></div>
                <p x-show="!detailLoading && detailItem && (!detailItem.url || detailFailed) && detailItem.message" class="whitespace-pre-line p-6 text-sm leading-6 text-gray-700" x-text="detailItem?.message"></p>
            </div>
            <footer class="shrink-0 border-t border-slate-200 px-6 py-4">
                <form method="dialog" data-no-loader class="flex justify-end">
                    <button class="rounded-xl border border-slate-200 px-5 py-2.5 font-semibold text-slate-700">{{ __('BookingTracking::bookings.close') }}</button>
                </form>
            </footer>
        </x-core::side-panel>
    </template>
</div>
