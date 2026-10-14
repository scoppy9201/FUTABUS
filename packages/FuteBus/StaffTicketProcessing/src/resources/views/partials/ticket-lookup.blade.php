            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 font-bold"><x-heroicon-o-magnifying-glass class="size-5" />{{ __('StaffDashboard::staff.tickets.actions.lookup') }}</h2>
                <form novalidate @submit.prevent="loadTickets()" class="grid items-end gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_1fr_auto]">
                    @foreach(['code' => 'text', 'phone' => 'tel', 'name' => 'text', 'date' => 'date'] as $field => $type)
                    <div>
                    <label
                        for="ticket-{{ $field }}"
                        class="mb-1 block text-xs font-semibold text-gray-600">{{ __('StaffTicketProcessing::tickets.'.$field) }}</label>
                    <input
                        id="ticket-{{ $field }}"
                        x-model="filters.{{ $field }}"
                        type="{{ $type }}" @if($field !== 'date')
                        placeholder="{{ __('StaffTicketProcessing::tickets.'.$field.'_hint') }}" @endif
                        class="h-10 w-full rounded-lg border border-gray-200 px-3 focus:border-futa-orange focus:outline-none">
                    </div>
                    @endforeach
                    <button
                        :disabled="loadingTickets || !groups?.tickets.lookup"
                        class="flex h-10 items-center justify-center gap-2 rounded-lg bg-futa-orange px-6 font-semibold text-white disabled:opacity-50">
                    <x-heroicon-o-magnifying-glass
                        class="size-4" />{{ __('StaffTicketProcessing::tickets.search') }}</button>
                </form>
            </section>
            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3"><h2 class="flex items-center gap-2 font-bold"><x-heroicon-o-list-bullet class="size-5" />{{ __('StaffTicketProcessing::tickets.results') }}</h2>
                    <select
                        x-model="filters.status"
                        @change="loadTickets()"
                        :disabled="loadingTickets || !groups?.tickets.lookup"
                        aria-label="{{ __('StaffDashboard::staff.status') }}"
                        class="rounded-md border border-gray-200 bg-white px-2 py-1 text-xs text-gray-500">
                    <option
                        value="">{{ __('StaffTicketProcessing::tickets.all_statuses') }}</option>@foreach(['active', 'expired', 'used', 'cancelled', 'refunded'] as $status)<option
                        value="{{ $status }}">{{ $status === 'expired' ? __('StaffTicketProcessing::tickets.expired') : __('core::ticket-lookup.ticket_status.'.$status) }}</option>@endforeach</select>
                </div>
                <div
                    class="overflow-x-auto">
                <table
                    class="w-full whitespace-nowrap text-left text-xs">
                <thead
                    class="bg-gray-50 text-gray-500">
                <tr>@foreach(['code', 'customer', 'phone', 'route', 'date', 'time', 'seat'] as $column)<th
                    class="px-3 py-3">{{ __('StaffTicketProcessing::tickets.'.$column) }}</th>@endforeach<th
                    class="px-3 py-3">{{ __('StaffDashboard::staff.status') }}</th>
                <th
                    class="px-3 py-3">{{ __('StaffDashboard::staff.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                    <template x-for="ticket in rows" :key="ticket.id"><tr x-show="!loadingTickets" class="border-b border-gray-100 hover:bg-orange-50/40">
                        <td
                            class="px-3 py-3 font-semibold"
                            x-text="ticket.code">
                        </td>
                        <td
                            class="px-3 py-3"
                            x-text="ticket.customer">
                        </td>
                        <td
                            class="px-3 py-3"
                            x-text="ticket.phone || '—'">
                        </td>
                        <td
                            class="px-3 py-3"
                            x-text="ticket.route">
                        </td>
                        <td
                            class="px-3 py-3"
                            x-text="ticket.date">
                        </td>
                        <td
                            class="px-3 py-3"
                            x-text="ticket.time">
                        </td>
                        <td
                            class="px-3 py-3"
                            x-text="ticket.seat || '—'">
                        </td>
                        <td
                            class="px-3 py-3">
                        <span
                            class="rounded-full px-2 py-1 font-medium"
                            :class="statusClass(ticket.display_status)"
                            x-text="ticket.status_label">
                        </span>
                        </td>
                        <td
                            class="px-3 py-3">
                        <button
                            type="button"
                            @click="openTicket(ticket)"
                            class="rounded-md border border-futa-orange px-4 py-1 font-semibold text-futa-orange hover:bg-futa-orange-soft">{{ __('StaffTicketProcessing::tickets.view') }}</button>
                        </td>
                    </tr></template>
                    <tr x-show="loadingTickets"><td colspan="9" class="h-32 px-4 text-center text-gray-500" role="status">{{ __('StaffTicketProcessing::tickets.loading') }}</td></tr>
                    <tr x-show="!loadingTickets && !rows.length"><td colspan="9" class="h-32 px-4 text-center text-gray-500">
                        <span
                            x-show="!groups?.tickets.lookup">{{ __('StaffTicketProcessing::tickets.denied') }}</span>
                        <span
                            x-show="groups?.tickets.lookup && !listFailed">{{ __('StaffTicketProcessing::tickets.empty') }}</span>
                        <button
                            x-show="groups?.tickets.lookup && listFailed"
                            @click="loadTickets()"
                            type="button"
                            class="text-futa-orange">{{ __('StaffDashboard::staff.retry') }}</button>
                    </td></tr>
                </tbody></table></div>
                <div x-show="meta.total > 0 && !listFailed" x-cloak class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-gray-500">
                    <span><span x-text="meta.from"></span>–<span x-text="meta.to"></span> / <span x-text="meta.total"></span> {{ __('StaffTicketProcessing::tickets.ticket_count') }}</span>
                    <div
                        class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="loadTickets(meta.current_page - 1)"
                        :disabled="loadingTickets || meta.current_page <= 1"
                        class="rounded-md border px-3 py-2 disabled:opacity-40">{{ __('StaffTicketProcessing::tickets.previous') }}</button>
                    <span>
                    <span
                        x-text="meta.current_page">
                    </span> / <span
                        x-text="meta.last_page">
                    </span>
                    </span>
                    <button
                        type="button"
                        @click="loadTickets(meta.current_page + 1)"
                        :disabled="loadingTickets || meta.current_page >= meta.last_page"
                        class="rounded-md border px-3 py-2 disabled:opacity-40">{{ __('StaffTicketProcessing::tickets.next') }}</button>
                    </div>
                </div>
            </section>
            <dialog x-ref="ticketDetail" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-gray-100 bg-white p-6 shadow-xl backdrop:bg-gray-950/50" @click="if ($event.target === $refs.ticketDetail) $refs.ticketDetail.close()" aria-labelledby="staff-ticket-detail-title">
                <div
                    class="mb-5 flex items-center justify-between gap-3">
                <h2
                    id="staff-ticket-detail-title"
                    class="text-lg font-bold text-futa-green">{{ __('core::ticket-lookup.ticket_details') }}</h2>
                <button
                    type="button"
                    @click="$refs.ticketDetail.close()"
                    aria-label="{{ __('core::booking.close') }}">
                <x-heroicon-o-x-mark
                    class="size-5" />
                </button>
                </div>
                <dl
                    class="grid gap-3 text-sm">@foreach(['code', 'customer', 'phone', 'route', 'date', 'time', 'seat'] as $field)<div
                    class="flex justify-between gap-4">
                <dt
                    class="text-gray-500">{{ __('StaffTicketProcessing::tickets.'.$field) }}</dt>
                <dd
                    class="text-right font-semibold"
                    x-text="selected?.{{ $field }} || '—'">
                </dd>
                </div>@endforeach<div
                    class="flex justify-between gap-4">
                <dt
                    class="text-gray-500">{{ __('StaffDashboard::staff.status') }}</dt>
                <dd
                    x-text="selected?.status_label">
                </dd>
                </div>
                <div
                    class="flex justify-between gap-4">
                <dt
                    class="text-gray-500">{{ __('core::ticket-lookup.fare') }}</dt>
                <dd
                    class="font-bold text-futa-orange"
                    x-text="selected ? money(selected.price) : ''">
                </dd>
                </div>
                </dl>
            </dialog>
