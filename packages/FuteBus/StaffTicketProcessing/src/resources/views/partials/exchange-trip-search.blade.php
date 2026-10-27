<form @submit.prevent="$dispatch('staff-exchange-search')" class="space-y-4 rounded-xl bg-white p-4 shadow-[0_2px_7px_rgba(15,23,42,.18)]">
    <h2 class="font-bold text-gray-950">{{ __('StaffTicketProcessing::exchange.trip_search_title') }}</h2>
    <div>
        <label class="block space-y-2 text-sm font-semibold">
            <span>{{ __('StaffTicketProcessing::tickets.route') }}</span>
            <input type="text" :value="selected?.route || ''" readonly
                class="h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-normal text-slate-700">
        </label>
    </div>
    <div>
        <label class="block space-y-2 text-sm font-semibold">
            <span>{{ __('StaffTicketProcessing::tickets.date') }}</span>
            <input type="date" x-model="date" required min="{{ now()->toDateString() }}" :disabled="tripBusy"
                class="h-11 w-full rounded-lg border border-gray-200 px-3 font-normal outline-none focus:border-futa-orange focus:ring-2 focus:ring-futa-orange/15">
        </label>
    </div>
    <button type="submit" :disabled="tripBusy || !date"
        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-futa-orange px-4 font-semibold text-white hover:bg-futa-orange-dark disabled:opacity-50">
        <x-heroicon-o-magnifying-glass class="size-5" />{{ __('StaffTicketProcessing::exchange.trip_search_action') }}
    </button>
</form>
