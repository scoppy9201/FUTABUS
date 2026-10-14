<div x-data="{ openPanel: true }">
    <x-core::side-panel id="staff-edit-panel" data-staff-edit-panel :title="__('UserManagement::app.edit')"
        :description="$editingStaff->name"
        x-effect="window.FutaSidePanel.sync($el, openPanel, $nextTick)"
        @close="openPanel = false; window.history.replaceState({}, '', @js(route('staff-accounts.index')))"
        @click.self="openPanel = false">
        <form method="post" action="{{ route('staff-accounts.update', $editingStaff->id) }}" enctype="multipart/form-data"
            data-staff-live-validation data-validation="{{ json_encode(__('UserManagement::app.validation')) }}"
            x-data="{ avatarPreview: null, avatarFileName: '' }" class="flex min-h-0 flex-1 flex-col">
            @csrf
            @method('put')
            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-5 sm:px-6 sm:py-6">
                @include('UserManagement::partials.form', [
                    'staff' => $editingStaff,
                    'isCreate' => false,
                    'sidePanel' => true,
                    'idPrefix' => 'edit-staff',
                ])
            </div>
            <footer class="flex shrink-0 justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:px-6">
                <a href="{{ route('staff-accounts.index') }}"
                    class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    {{ __('UserManagement::app.cancel') }}
                </a>
                <button type="submit"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-futa-orange px-4 py-2 text-sm font-bold text-white transition hover:bg-futa-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange">
                    <x-heroicon-o-check class="size-4" />
                    {{ __('UserManagement::app.save_changes') }}
                </button>
            </footer>
        </form>
    </x-core::side-panel>
</div>
