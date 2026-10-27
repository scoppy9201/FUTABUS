<div x-data="{ openPanel: true }">
    <x-core::side-panel id="staff-detail-panel" data-staff-detail-panel :title="__('UserManagement::app.detail_title')"
        :description="$viewingStaff->name"
        x-effect="window.FutaSidePanel.sync($el, openPanel, $nextTick)"
        @close="openPanel = false; window.history.replaceState({}, '', @js(route('staff-accounts.index')))"
        @click.self="openPanel = false">
        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-5 py-5 sm:px-6 sm:py-6">
            <section class="flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-full border border-slate-200 bg-white text-lg font-bold text-slate-500">
                    @if($viewingStaff->avatar)
                        <img src="{{ route('staff-accounts.avatar', $viewingStaff->id) }}"
                            alt="{{ $viewingStaff->name }}" class="size-full object-cover">
                    @else
                        {{ mb_strtoupper(mb_substr($viewingStaff->name, 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0">
                    <h3 class="truncate text-base font-bold text-slate-950">{{ $viewingStaff->name }}</h3>
                    <span @class([
                        'mt-1 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold',
                        'bg-emerald-50 text-emerald-700' => $viewingStaff->is_active,
                        'bg-amber-50 text-amber-700' => ! $viewingStaff->is_active,
                    ])>
                        @if($viewingStaff->is_active)
                            <x-heroicon-o-check-circle class="size-3.5" />
                        @else
                            <x-heroicon-o-x-circle class="size-3.5" />
                        @endif
                        {{ __('UserManagement::app.'.($viewingStaff->is_active ? 'active' : 'inactive')) }}
                    </span>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3">
                    <x-heroicon-o-user class="size-5 text-slate-600" />
                    <h3 class="text-sm font-bold text-slate-800">{{ __('UserManagement::app.basic_info') }}</h3>
                </div>
                <dl class="grid grid-cols-[7.5rem_minmax(0,1fr)] gap-x-3 gap-y-4 p-4 text-sm">
                    <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.name') }}</dt>
                    <dd class="break-words font-semibold text-slate-900">{{ $viewingStaff->name }}</dd>
                    <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.phone') }}</dt>
                    <dd class="break-words font-semibold text-slate-900">{{ $viewingStaff->phone ?: '—' }}</dd>
                    <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.date_of_birth') }}</dt>
                    <dd class="font-semibold text-slate-900">{{ $viewingStaff->date_of_birth?->format('d/m/Y') ?: '—' }}</dd>
                    <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.gender') }}</dt>
                    <dd class="font-semibold text-slate-900">
                        {{ $viewingStaff->gender ? __('UserManagement::app.gender_values.'.$viewingStaff->gender) : '—' }}
                    </dd>
                    <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.address') }}</dt>
                    <dd class="break-words font-semibold text-slate-900">{{ $viewingStaff->address ?: '—' }}</dd>
                </dl>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3">
                    <x-heroicon-o-lock-closed class="size-5 text-slate-600" />
                    <h3 class="text-sm font-bold text-slate-800">{{ __('UserManagement::app.account_info') }}</h3>
                </div>
                <dl class="grid grid-cols-[7.5rem_minmax(0,1fr)] gap-x-3 gap-y-4 p-4 text-sm">
                    <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.email') }}</dt>
                    <dd class="break-all font-semibold text-slate-900">{{ $viewingStaff->email }}</dd>
                    <dt class="font-medium text-slate-500">{{ __('UserManagement::app.columns.created_at') }}</dt>
                    <dd class="font-semibold text-slate-900">{{ $viewingStaff->created_at?->format('d/m/Y H:i') ?: '—' }}</dd>
                    <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.password') }}</dt>
                    <dd class="font-semibold tracking-widest text-slate-700" aria-label="{{ __('UserManagement::app.password_hidden') }}">••••••••</dd>
                </dl>
            </section>
        </div>

        <footer class="flex shrink-0 flex-wrap justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:px-6">
            <form method="dialog" data-no-loader>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    {{ __('UserManagement::app.cancel') }}
                </button>
            </form>
            @if(auth()->user()->hasPermissionTo('user.update'))
                <a href="{{ route('staff-accounts.edit', $viewingStaff->id) }}"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-futa-orange px-4 py-2 text-sm font-bold text-white transition hover:bg-futa-orange-dark">
                    <x-heroicon-o-pencil-square class="size-4" />
                    {{ __('UserManagement::app.edit_action') }}
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('user.delete'))
                <form method="post" action="{{ route('staff-accounts.destroy', $viewingStaff->id) }}"
                    data-confirm data-confirm-title="{{ __('UserManagement::app.delete_title') }}"
                    data-confirm-message="{{ __('UserManagement::app.delete_message', ['name' => $viewingStaff->name]) }}"
                    data-confirm-label="{{ __('UserManagement::app.delete_action') }}">
                    @csrf
                    @method('delete')
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-bold text-red-700 transition hover:bg-red-50">
                        <x-heroicon-o-trash class="size-4" />
                        {{ __('UserManagement::app.delete_action') }}
                    </button>
                </form>
            @endif
        </footer>
    </x-core::side-panel>
</div>
