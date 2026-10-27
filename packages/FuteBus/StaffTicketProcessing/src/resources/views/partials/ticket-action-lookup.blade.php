            <div x-show="step === 1" class="space-y-5">
            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 font-bold text-slate-800">{{ __('StaffTicketProcessing::exchange.search_title') }}</h2>
                <form @submit.prevent="searchTickets()">
                    <fieldset class="mb-3 flex flex-wrap gap-x-7 gap-y-3" :disabled="loadingTickets">
                        <legend class="sr-only">{{ __('StaffTicketProcessing::exchange.search_title') }}</legend>
                        @foreach(['code', 'phone', 'name'] as $field)
                            <label
                                class="flex cursor-pointer items-center gap-2 text-sm">
                                <input
                                type="radio" name="exchange-search" value="{{ $field }}"
                                x-model="searchField"
                                class="size-4 accent-futa-orange">{{ $field === 'name' ? __('StaffTicketProcessing::exchange.name') : __('StaffTicketProcessing::tickets.'.$field) }}</label>
                        @endforeach
                    </fieldset>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="relative flex-1">
                            <input
                                x-model="query"
                                :type="searchField === 'phone' ? 'tel' : 'text'"
                                :aria-label="searchField === 'code' ? @js(__('StaffTicketProcessing::tickets.code')) : searchField === 'phone' ? @js(__('StaffTicketProcessing::tickets.phone')) : @js(__('StaffTicketProcessing::exchange.name'))" required maxlength="255"
                                class="h-11 w-full rounded-lg border border-gray-200 pl-3 pr-11 outline-none focus:border-futa-orange focus:ring-2 focus:ring-futa-orange/15">
                            <button type="button" @click="query = ''; $el.previousElementSibling.focus()" aria-label="{{ __('StaffTicketProcessing::exchange.clear') }}" class="absolute inset-y-0 right-0 px-3 text-slate-400 hover:text-futa-orange"><x-heroicon-o-x-circle class="size-5" /></button>
                        </div>
                        <button
                            :disabled="loadingTickets || !groups?.tickets.lookup || !query.trim()"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-futa-orange px-10 font-semibold text-white hover:bg-futa-orange-dark disabled:opacity-50">
                            <x-heroicon-o-magnifying-glass
                            class="size-5" />{{ __('StaffTicketProcessing::exchange.search') }}</button>
                    </div>
                </form>
            </section>
            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div
                    class="mb-4 flex items-center justify-between gap-3">
                    <h2
                    class="flex items-center gap-2 font-bold">
                    <x-heroicon-o-list-bullet
                    class="size-5" />{{ __('StaffTicketProcessing::exchange.results') }} (<span
                    x-text="meta.total">
                    </span> {{ __('StaffTicketProcessing::tickets.ticket_count') }})</h2>

                </div>
                <div
                    class="overflow-x-auto">
                <table
                    class="w-full whitespace-nowrap text-left text-sm">
                <thead
                    class="bg-gray-50 text-gray-500">
                <tr><th class="px-3 py-3">{{ __('StaffTicketProcessing::exchange.number') }}</th>@foreach(['code', 'customer', 'phone', 'route', 'date', 'time', 'seat'] as $column)<th
                    @class(['px-3 py-3', 'text-center' => in_array($column, ['date', 'time', 'seat'])])>{{ __('StaffTicketProcessing::tickets.'.$column) }}</th>@endforeach<th
                    class="px-3 py-3 text-center">{{ __('StaffDashboard::staff.status') }}</th>
                <th
                    class="px-3 py-3 text-center">{{ __('StaffDashboard::staff.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                    <tr x-show="!searched"><td colspan="10" class="h-32 px-4 text-center text-slate-500">{{ __('StaffTicketProcessing::exchange.initial') }}</td></tr>
                    <template x-for="(ticket, index) in rows" :key="ticket.id"><tr x-show="!loadingTickets" class="border-b border-gray-100 hover:bg-orange-50/40">
                        <td class="px-3 py-3 text-slate-500" x-text="(meta.from || 1) + index"></td>
                        <td
                            class="max-w-52 whitespace-normal break-all px-3 py-3 font-semibold"
                            >
                            <span class="block" x-text="ticket.code"></span>
                            <span class="mt-1 block text-xs font-normal text-slate-400" x-text="ticket.booking_code"></span>
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
                            class="max-w-48 whitespace-normal px-3 py-3"
                            x-text="ticket.route">
                        </td>
                        <td
                            class="px-3 py-3 text-center whitespace-nowrap"
                            x-text="ticket.date">
                        </td>
                        <td
                            class="px-3 py-3 text-center whitespace-nowrap"
                            x-text="ticket.time">
                        </td>
                        <td
                            class="px-3 py-3 text-center"
                            x-text="ticket.seat || '—'">
                        </td>
                        <td
                            class="px-3 py-3 text-center">
                        <span
                            class="rounded-full px-2 py-1 font-medium"
                            :class="statusClass(ticket.display_status)"
                            x-text="ticket.status_label">
                        </span>
                        </td>
                        <td
                            class="px-3 py-3 text-center">
                        <button
                            type="button"
                            @click="chooseTicket(ticket)" :disabled="!ticket.usable || loadingTickets || choosing"
                           
                                class="rounded-md border border-futa-orange px-4 py-1 font-semibold text-futa-orange hover:bg-futa-orange-soft disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400">
                                <span
                                x-text="ticket.usable ? @js(__('StaffTicketProcessing::exchange.select')) : @js(__('StaffTicketProcessing::exchange.unavailable'))">
                                </span>
                                </button>
                        </td>
                    </tr></template>
                    <tr x-show="loadingTickets"><td colspan="10" class="h-32 px-4 text-center text-gray-500" role="status">{{ __('StaffTicketProcessing::tickets.loading') }}</td></tr>
                    <tr x-show="searched && !loadingTickets && !rows.length"><td colspan="10" class="h-32 px-4 text-center text-gray-500">
                        <span
                            x-show="!groups?.tickets.lookup">{{ __('StaffTicketProcessing::tickets.denied') }}</span>
                        <x-core::empty-state x-show="groups?.tickets.lookup && !listFailed" :title="__('StaffTicketProcessing::tickets.empty')" compact />
                        <button
                            x-show="groups?.tickets.lookup && listFailed"
                            @click="loadTickets()"
                            type="button"
                            class="text-futa-orange">{{ __('StaffDashboard::staff.retry') }}</button>
                    </td></tr>
                </tbody></table></div>
                <x-core::data-pagination meta="meta" navigate="loadTickets" busy="loadingTickets"
                    x-cloak x-show="searched && !listFailed && groups?.tickets.lookup" class="mt-4" />

            </section>
            </div>
