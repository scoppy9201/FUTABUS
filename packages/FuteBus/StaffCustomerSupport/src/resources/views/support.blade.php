@extends('StaffDashboard::layouts.staff')
@section('title', __('StaffCustomerSupport::support.title'))
@push('styles')
    @vite(['packages/FuteBus/StaffDashboard/src/resources/css/app.css', 'packages/FuteBus/StaffDashboard/src/resources/js/app.js', 'packages/FuteBus/StaffCustomerSupport/src/resources/css/app.css', 'packages/FuteBus/StaffCustomerSupport/src/resources/js/app.js'])
@endpush
@section('content')
    <div x-data="staffDashboard(@js(route('api.v1.staff.dashboard.show')))" class="flex h-dvh min-h-0 flex-col overflow-hidden bg-gray-50 print:h-auto print:overflow-visible">
        @include('StaffDashboard::partials.header')
        @include('StaffDashboard::partials.notifications')
        <div
            class="staff-shell grid min-h-0 min-w-0 flex-1 overflow-hidden grid-cols-1 bg-[#F8F9FA] text-sm lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-stretch [&>main]:min-h-0
            [&>main]:overflow-y-auto [&>main]:p-4 md:[&>main]:p-6 lg:[&>main]:p-8 print:block print:overflow-visible print:[&>main]:overflow-visible print:[&>main]:p-0">
            @include('StaffDashboard::partials.sidebar')
            <main class="min-w-0 space-y-5 pb-8"
                x-data="staffSupport(@js([
                    'list' => route('api.v1.staff.support.index'),
                    'detail' => route('api.v1.staff.support.show', ['report' => '__report__']),
                    'transfer' => route('api.v1.staff.support.transfers.store', ['report' => '__report__']),
                    'note' => route('api.v1.staff.support.notes.store', ['report' => '__report__']),
                    'claim' => route('api.v1.staff.support.claim', ['report' => '__report__']),
                    'respond' => route('api.v1.staff.support.responses.store', ['report' => '__report__']),
                    'csrf' => csrf_token(),
                ]), @js([
                    'statuses' => __('Profile::feedback.status'),
                    'categories' => __('Profile::feedback.categories'),
                    'urgencies' => __('Profile::feedback.urgencies'),
                    'error' => __('StaffCustomerSupport::support.error'), 'saved' => __('StaffCustomerSupport::support.saved'),
                    'confirm_title' => __('StaffCustomerSupport::support.confirm_title'),
                    'confirm_send' => __('StaffCustomerSupport::support.confirm_send'),
                    'confirm_resolve' => __('StaffCustomerSupport::support.confirm_resolve'),
                    'confirm_transfer' => __('StaffCustomerSupport::support.confirm_transfer'),
                    'confirm_note' => __('StaffCustomerSupport::support.confirm_note'),
                    'response_required' => __('StaffCustomerSupport::support.response_required'),
                    'note_required' => __('StaffCustomerSupport::support.note_required'),
                    'transferReason_required' => __('StaffCustomerSupport::support.transferReason_required'),
                    'departmentRole_required' => __('StaffCustomerSupport::support.departmentRole_required'),
                    'too_long' => __('StaffCustomerSupport::support.too_long'),
                    'confirm_label' => __('StaffCustomerSupport::support.confirm_label'),
                    'events' => collect(['claim', 'response', 'assignment', 'admin_update', 'transfer', 'note'])
                        ->mapWithKeys(fn ($kind) => [$kind => __('StaffCustomerSupport::support.'.$kind.'_event')])->all(),
                ]), @js($report ?? null))">
                <header class="relative flex min-h-36 items-center overflow-hidden rounded-xl bg-orange-50 p-5 sm:p-6">
                    <img src="{{ asset('images/banners/home-banner.jpg') }}" alt=""
                        class="absolute inset-y-0 right-0 h-full w-1/2 object-cover object-right">
                    <div class="relative flex items-center gap-4 rounded-xl bg-orange-50/95 p-3">
                        <span class="grid size-14 shrink-0 place-items-center rounded-xl bg-futa-orange text-white"><x-heroicon-o-chat-bubble-left-right class="size-8" /></span>
                        <div class="min-w-0">
                            <h1 class="text-2xl font-bold text-futa-orange sm:text-3xl">{{ __('StaffCustomerSupport::support.title') }}</h1>
                            <p class="mt-2 text-gray-500">{{ __('StaffCustomerSupport::support.intro') }}</p>
                        </div>
                    </div>
                </header>
                @if(! isset($report))
                    <nav class="flex flex-wrap gap-2" aria-label="{{ __('StaffCustomerSupport::support.filters') }}">
                        @foreach(__('StaffCustomerSupport::support.queues') as $queue => $label)
                            <button type="button" @click="filters.queue = @js($queue); loadList(1)" :aria-pressed="filters.queue === @js($queue)"
                                :class="filters.queue === @js($queue) ? 'border-futa-orange bg-futa-orange text-white' : 'border-gray-200 bg-white text-gray-600'"
                                class="rounded-full border px-4 py-2 text-sm font-semibold">{{ $label }}</button>
                        @endforeach
                    </nav>
                    <section aria-labelledby="support-filters-title" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center gap-2">
                            <x-heroicon-o-adjustments-horizontal class="size-5 text-futa-orange" />
                            <h2 id="support-filters-title" class="font-semibold text-gray-900">{{ __('StaffCustomerSupport::support.filters') }}</h2>
                        </div>
                        <form @submit.prevent="loadList(1)" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1.5fr)_repeat(3,minmax(0,1fr))_auto]">
                            <label class="block text-sm font-semibold text-gray-600">{{ __('StaffCustomerSupport::support.search') }}
                                <input x-model="filters.search" type="search" maxlength="150" placeholder="{{ __('StaffCustomerSupport::support.search_placeholder') }}"
                                    class="mt-2 h-11 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 text-sm font-normal outline-none focus:border-futa-orange focus:ring-2 focus:ring-futa-orange/15">
                            </label>
                            @foreach(['status' => 'status', 'category' => 'categories', 'urgency' => 'urgencies'] as $field => $translation)
                                <label class="block text-sm font-semibold text-gray-600">{{ $field === 'status' ? __('StaffCustomerSupport::support.status') : __('Profile::feedback.fields.'.$field) }}
                                    <select x-model="filters.{{ $field }}" class="mt-2 h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm font-normal focus:border-futa-orange focus:ring-2 focus:ring-futa-orange/15">
                                        <option value="">{{ __('StaffCustomerSupport::support.all') }}</option>
                                        @foreach(__('Profile::feedback.'.$translation) as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach
                            <button :disabled="loading" class="flex h-11 items-center justify-center gap-2 self-end rounded-xl bg-futa-orange px-5 font-semibold text-white transition hover:bg-futa-orange-dark disabled:opacity-50">
                                <x-heroicon-o-magnifying-glass class="size-4" />{{ __('StaffTicketProcessing::tickets.search') }}
                            </button>
                        </form>
                    </section>
                    <section aria-labelledby="support-list-title" :aria-busy="loading" class="min-w-0 rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-futa-orange-soft text-futa-orange"><x-heroicon-o-inbox-stack class="size-5" /></span>
                                <div>
                                    <h2 id="support-list-title" class="text-lg font-bold text-gray-900">{{ __('StaffCustomerSupport::support.list_title') }}</h2>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('StaffCustomerSupport::support.list_hint') }}</p>
                                </div>
                            </div>
                            <span x-cloak x-show="!loading && !failed" class="rounded-full bg-gray-100 px-3 py-1.5 text-sm font-semibold text-gray-600"><span x-text="meta.total"></span> {{ __('StaffCustomerSupport::support.requests') }}</span>
                        </div>
                        <div x-show="loading" role="status" class="flex min-h-60 flex-col items-center justify-center gap-3 p-8 text-gray-500">
                            <x-heroicon-o-arrow-path class="size-6 animate-spin text-futa-orange" />{{ __('StaffCustomerSupport::support.loading') }}
                        </div>
                        <div x-cloak x-show="!loading && failed" role="status" class="flex min-h-60 flex-col items-center justify-center gap-4 p-8 text-center">
                            <x-heroicon-o-exclamation-circle class="size-10 text-gray-400" />
                            <p class="text-gray-600">{{ __('StaffCustomerSupport::support.error') }}</p>
                            <button @click="loadList()" class="rounded-xl border border-futa-orange px-4 py-2 font-semibold text-futa-orange">{{ __('StaffCustomerSupport::support.reload') }}</button>
                        </div>
                        <div x-cloak x-show="!loading && !failed" class="overflow-x-auto">
                            <table class="w-full min-w-[1100px] table-fixed text-left text-sm">
                                <colgroup>
                                    <col class="w-[22%]">
                                    <col class="w-[18%]">
                                    <col class="w-40">
                                    <col class="w-32">
                                    <col class="w-36">
                                    <col>
                                    <col class="w-36">
                                </colgroup>
                                <thead class="border-b border-gray-100 bg-gray-50 text-sm font-semibold text-gray-500"><tr>
                                    <th scope="col" class="px-5 py-4">{{ __('StaffCustomerSupport::support.request') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Profile::feedback.fields.category') }}</th>
                                    <th scope="col" class="px-4 py-3 text-center">{{ __('StaffDashboard::staff.time') }}</th>
                                    <th scope="col" class="px-4 py-3 text-center">{{ __('Profile::feedback.fields.urgency') }}</th>
                                    <th scope="col" class="px-4 py-3 text-center">{{ __('StaffCustomerSupport::support.status') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('StaffCustomerSupport::support.handler') }}</th>
                                    <th scope="col" class="px-4 py-3 text-center">{{ __('StaffDashboard::staff.actions') }}</th>
                                </tr></thead>
                                <tbody class="divide-y divide-gray-100"><template x-for="item in rows" :key="item.id">
                                    <tr class="align-middle transition-colors hover:bg-futa-orange-soft/40">
                                        <td class="px-5 py-3 align-middle">
                                            <a :href="@js(route('staff.support', ['report' => '__report__'])).replace('__report__', item.id)" class="font-semibold text-futa-orange hover:underline" x-text="item.reference"></a>
                                            <p class="mt-1 truncate font-medium text-gray-900" :title="item.subject" x-text="item.subject"></p>
                                            <p class="mt-1 text-sm text-gray-500" x-show="item.customer_name" x-text="item.customer_name"></p>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600" x-text="supportLabels.categories[item.category]"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center text-gray-500" x-text="date(item.created_at)"></td>
                                        <td
                                            class="px-4 py-3 text-center">
                                            <span
                                            class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-sm font-medium"
                                            :class="item.urgency === 'urgent' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-600'"
                                            x-text="supportLabels.urgencies[item.urgency]">
                                            </span>
                                            </td>
                                        <td class="px-4 py-3 text-center">
                                            <span
                                            class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-sm font-semibold"
                                            :class="{'bg-blue-50 text-blue-700': item.status === 'received', 'bg-orange-50 text-orange-700': item.status === 'reviewing', 'bg-purple-50 text-purple-700': item.status === 'waiting', 'bg-green-50 text-green-700': item.status === 'resolved'}"
                                            x-text="supportLabels.statuses[item.status]">
                                            </span>
                                            </td>
                                        <td class="px-4 py-3 text-gray-600" x-text="item.handler || item.department || @js(__('StaffCustomerSupport::support.unclaimed'))"></td>
                                        <td class="px-4 py-3 text-center">
                                            <a
                                            :href="@js(route('staff.support', ['report' => '__report__'])).replace('__report__', item.id)"
                                            class="inline-flex items-center gap-1 whitespace-nowrap rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:border-futa-orange hover:text-futa-orange">
                                            {{ __('StaffCustomerSupport::support.view') }}
                                            </a>
                                            </td>
                                    </tr>
                                </template>
                                    <tr x-show="!rows.length">
                                        <td colspan="7" class="px-6 py-10 text-center">
                                            <div class="flex flex-col items-center">
                                                <x-core::empty-state :title="__('StaffCustomerSupport::support.empty')" :description="__('StaffCustomerSupport::support.empty_hint')" compact />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <x-core::data-pagination meta="meta" navigate="loadList" busy="loading"
                            x-cloak x-show="!failed" class="border-t border-slate-100 px-5 py-4" />
                    </section>
                @else
                    <a href="{{ route('staff.support') }}" class="inline-flex items-center gap-2 font-semibold text-futa-orange">
                        <x-heroicon-o-arrow-left class="size-4" />{{ __('StaffCustomerSupport::support.back') }}
                    </a>
                    <p x-show="loadingDetail" role="status">{{ __('StaffCustomerSupport::support.loading') }}</p>
                    <button x-show="detailFailed" @click="loadDetail(@js($report))" class="font-semibold text-futa-orange">{{ __('StaffCustomerSupport::support.reload') }}</button>
                    <template x-if="detail">
                        <div class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                            <div class="min-w-0 space-y-4">
                                <header class="relative flex items-center gap-3 overflow-hidden rounded-xl border border-orange-100 bg-linear-to-r from-orange-50 to-white p-5">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-futa-orange text-white"><x-heroicon-o-chat-bubble-left-right class="size-6" /></span>
                                    <div class="relative min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h2 class="text-xl font-bold text-gray-900" x-text="detail.reference"></h2>
                                            <span class="rounded-full bg-futa-orange-soft px-3 py-1 text-xs font-semibold text-futa-orange" x-text="supportLabels.statuses[detail.status]"></span>
                                        </div>
                                        <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                            <span>{{ __('StaffCustomerSupport::support.created') }} <span x-text="date(detail.created_at)"></span></span>
                                            <span>{{ __('StaffCustomerSupport::support.updated') }} <span x-text="date(detail.updated_at)"></span></span>
                                        </p>
                                    </div>
                                    <x-heroicon-o-truck class="hidden size-16 shrink-0 text-futa-orange/30 sm:block" />
                                </header>
                                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                    <h3 class="flex items-center gap-2 font-bold text-gray-900"><x-heroicon-o-document-text class="size-5 text-futa-orange" />{{ __('StaffCustomerSupport::support.request_info') }}</h3>
                                    <div class="mt-4 rounded-xl border border-gray-100 bg-gray-50/70 p-4">
                                        <p class="font-semibold text-gray-800" x-text="detail.subject"></p>
                                        <p class="mt-2 whitespace-pre-wrap wrap-break-word text-sm leading-6 text-gray-600" x-text="detail.message"></p>
                                        <p class="mt-5 flex items-center gap-2 text-xs font-semibold text-gray-500">
                                            <x-heroicon-o-paper-clip class="size-4" />{{ __('StaffCustomerSupport::support.attachments') }} (<span x-text="detail.attachments.length"></span>)
                                        </p>
                                        <div class="mt-2 flex flex-wrap gap-2"><template x-for="image in detail.attachments" :key="image.id">
                                            <a :href="image.url" target="_blank" rel="noopener" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-futa-orange" x-text="image.name"></a>
                                        </template></div>
                                    </div>
                                </section>
                                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                    <h3 class="flex items-center gap-2 font-bold text-gray-900"><x-heroicon-o-ticket class="size-5 text-futa-orange" />{{ __('StaffCustomerSupport::support.ticket_info') }}</h3>
                                    <p x-show="!detail.booking_code" class="mt-4 text-sm text-gray-500">{{ __('StaffCustomerSupport::support.no_booking') }}</p>
                                    <div x-show="detail.booking_code" class="mt-4 grid gap-4 rounded-xl border border-gray-100 bg-gray-50/70 p-4 sm:grid-cols-2 xl:grid-cols-4">
                                        <div class="min-w-0"><p class="text-xs text-gray-400">{{ __('StaffCustomerSupport::support.booking_code') }}</p><p class="mt-2 break-all text-sm font-bold" x-text="detail.booking_code"></p></div>
                                        <div><p class="text-xs text-gray-400">{{ __('StaffCustomerSupport::support.route') }}</p><p class="mt-2 text-sm font-semibold" x-text="detail.origin ? detail.origin + ' → ' + detail.destination : '—'"></p></div>
                                        <div><p class="text-xs text-gray-400">{{ __('StaffCustomerSupport::support.departure') }}</p><p class="mt-2 text-sm font-semibold" x-text="detail.departure_at ? date(detail.departure_at) : '—'"></p></div>
                                        <div><p class="text-xs text-gray-400">{{ __('StaffCustomerSupport::support.seats') }}</p><p class="mt-2 text-sm font-semibold" x-text="detail.seat_codes.join(', ') || '—'"></p></div>
                                    </div>
                                    @if(auth()->user()->hasPermissionTo('ticket.view'))
                                        <a x-show="detail.booking_code" :href="@js(route('staff.tickets')) + '?code=' + encodeURIComponent(detail.booking_code)"
                                            class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-futa-orange hover:underline">
                                            <x-heroicon-o-magnifying-glass class="size-4" />{{ __('StaffCustomerSupport::support.ticket_lookup') }}
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermissionTo('payment.view'))
                                        <a x-show="detail.booking_code" :href="@js(route('staff.refunds.index')) + '?search=' + encodeURIComponent(detail.booking_code)"
                                            class="mt-3 block text-xs font-semibold text-futa-orange hover:underline">{{ __('StaffCustomerSupport::support.refunds') }}</a>
                                    @endif
                                </section>
                                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                    <h3 class="flex items-center gap-2 font-bold text-gray-900"><x-heroicon-o-clock class="size-5 text-futa-orange" />{{ __('StaffCustomerSupport::support.history') }}</h3>
                                    <ol class="mt-5">
                                        <li class="relative ml-2 border-l border-gray-200 pb-5 pl-5">
                                            <span class="absolute -left-1.5 top-1 size-3 rounded-full bg-gray-400 ring-4 ring-white"></span>
                                            <div class="flex flex-wrap justify-between gap-2"><p class="text-xs font-semibold text-gray-600" x-text="detail.contact.name"></p><time class="text-xs text-gray-400" x-text="date(detail.created_at)"></time></div>
                                            <p class="mt-2 whitespace-pre-wrap wrap-break-word text-sm text-gray-600" x-text="detail.message"></p>
                                        </li>
                                        <template x-for="event in detail.history" :key="event.id">
                                            <li class="relative ml-2 border-l border-gray-200 pb-5 pl-5 last:pb-0">
                                                <span class="absolute -left-1.5 top-1 size-3 rounded-full bg-futa-orange ring-4 ring-white"></span>
                                                <div class="flex flex-wrap justify-between gap-2"><p class="text-xs font-semibold text-gray-600" x-text="event.actor_name"></p><time class="text-xs text-gray-400" x-text="date(event.created_at)"></time></div>
                                                <p class="mt-2 text-xs font-semibold text-gray-700" x-text="supportLabels.events[event.kind] + ' · ' + supportLabels.statuses[event.status]"></p>
                                                <p class="mt-2 whitespace-pre-wrap wrap-break-word text-sm text-gray-600" x-text="event.response || ''"></p>
                                                <p x-show="event.department_name" class="mt-2 text-xs font-semibold text-purple-700" x-text="event.department_name"></p>
                                                <div x-show="event.internal_note" class="mt-2 rounded-lg bg-gray-50 p-3">
                                                    <p class="text-xs font-semibold text-gray-500">{{ __('StaffCustomerSupport::support.notes') }}</p>
                                                    <p class="mt-1 whitespace-pre-wrap wrap-break-word text-sm" x-text="event.internal_note"></p>
                                                </div>
                                            </li>
                                        </template>
                                    </ol>
                                </section>
                            </div>
                            <aside class="space-y-4">
                                <section class="rounded-xl bg-white p-5 shadow-sm">
                                    <h3 class="font-bold">{{ __('StaffCustomerSupport::support.contact') }}</h3>
                                    <p class="mt-3 text-sm" x-text="detail.contact.name"></p>
                                    <template x-if="detail.contact.allow_contact"><div class="mt-2 space-y-1 text-sm">
                                        <p x-text="detail.contact.phone"></p><p class="break-all" x-text="detail.contact.email"></p>
                                    </div></template>
                                    <p x-show="!detail.contact.allow_contact" class="mt-2 text-sm text-gray-500">{{ __('StaffCustomerSupport::support.no_contact') }}</p>
                                </section>
                                <section class="rounded-xl bg-white p-5 shadow-sm">
                                    <h3 class="font-bold">{{ __('StaffCustomerSupport::support.handler') }}</h3>
                                    <p class="mt-2 text-sm" x-text="detail.handler || @js(__('StaffCustomerSupport::support.unclaimed'))"></p>
                                    <p x-show="!detail.can_respond" class="mt-3 text-sm text-gray-500">{{ __('StaffCustomerSupport::support.view_only') }}</p>
                                    <button x-show="detail.can_respond && detail.can_claim && !detail.assigned_staff_id && detail.status !== 'resolved'"
                                        @click="claim()" :disabled="busy" class="mt-4 w-full rounded-lg bg-futa-orange px-4 py-3 font-semibold text-white disabled:opacity-50">
                                        {{ __('StaffCustomerSupport::support.claim') }}
                                    </button>
                                    <form novalidate x-show="detail.can_respond && detail.is_handler && detail.status === 'reviewing'"
                                        @submit.prevent="respond()" class="mt-4 space-y-3">
                                        <label class="block text-sm font-semibold">{{ __('StaffCustomerSupport::support.response') }}
                                            <textarea x-model="response" x-ref="response" @input="fieldErrors.response = ''"
                                                :aria-invalid="!!fieldErrors.response" aria-describedby="support-error-response" required maxlength="3000" rows="6"
                                                class="mt-2 w-full rounded-lg border border-gray-200 p-3 font-normal"></textarea>
                                            <p id="support-error-response" x-cloak x-show="fieldErrors.response" x-text="fieldErrors.response" role="alert" class="mt-2 text-xs text-red-600"></p>
                                        </label>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" :checked="responseStatus === 'resolved'"
                                                @change="responseStatus = $event.target.checked ? 'resolved' : 'reviewing'" class="accent-futa-orange">
                                            {{ __('StaffCustomerSupport::support.resolve') }}
                                        </label>
                                        <button :disabled="busy" class="w-full rounded-lg bg-futa-orange px-4 py-3 font-semibold text-white disabled:opacity-50">
                                            <span x-text="responseStatus === 'resolved' ? @js(__('StaffCustomerSupport::support.send_resolve')) : @js(__('StaffCustomerSupport::support.send'))"></span>
                                        </button>
                                    </form>
                                    <p x-show="detail.department" class="mt-3 text-sm text-purple-700" x-text="detail.department"></p>
                                    <p x-show="!detail.can_claim && detail.status === 'waiting'" class="mt-2 text-sm text-gray-500">{{ __('StaffCustomerSupport::support.claim_restricted') }}</p>
                                    <p x-show="detail.status === 'resolved'" class="mt-4 text-sm text-futa-green">{{ __('StaffCustomerSupport::support.closed') }}</p>
                                </section>
                                <section x-show="detail.can_respond && detail.is_handler && detail.status === 'reviewing'" class="space-y-4 rounded-xl bg-white p-5 shadow-sm">
                                    <h3 class="font-bold">{{ __('StaffCustomerSupport::support.notes') }}</h3>
                                    <p class="text-xs text-gray-500">{{ __('StaffCustomerSupport::support.note_hint') }}</p>
                                    <form novalidate @submit.prevent="addAction('note')" class="space-y-3">
                                        <label class="sr-only" for="support-note">{{ __('StaffCustomerSupport::support.notes') }}</label>
                                        <textarea id="support-note" x-model="note" x-ref="note" @input="fieldErrors.note = ''"
                                                :aria-invalid="!!fieldErrors.note" aria-describedby="support-error-note" required maxlength="3000" rows="3" class="w-full rounded-lg border border-gray-200 p-3 text-sm"></textarea>
                                            <p id="support-error-note" x-cloak x-show="fieldErrors.note" x-text="fieldErrors.note" role="alert" class="mt-2 text-xs text-red-600"></p>
                                        <button :disabled="busy" class="rounded-lg border border-gray-200 px-4 py-2 font-semibold disabled:opacity-50">{{ __('StaffCustomerSupport::support.save_note') }}</button>
                                    </form>
                                </section>
                                <section x-show="detail.can_respond && detail.is_handler && detail.status === 'reviewing'" class="space-y-4 rounded-xl bg-white p-5 shadow-sm">
                                    <h3 class="font-bold">{{ __('StaffCustomerSupport::support.transfer') }}</h3>
                                    <p class="text-xs text-gray-500">{{ __('StaffCustomerSupport::support.department_hint') }}</p>
                                    <x-core::empty-state x-show="!detail.departments.length" :title="__('StaffCustomerSupport::support.no_departments')" compact />
                                    <form novalidate x-show="detail.departments.length" @submit.prevent="addAction('transfer')" class="space-y-3">
                                        <label class="block text-sm">{{ __('StaffCustomerSupport::support.department') }}
                                            <select x-model="departmentRole" x-ref="departmentRole" @input="fieldErrors.departmentRole = ''"
                                                :aria-invalid="!!fieldErrors.departmentRole" aria-describedby="support-error-departmentRole" required class="mt-2 w-full rounded-lg border border-gray-200 bg-white p-3">
                                                <option value="">{{ __('StaffCustomerSupport::support.choose') }}</option>
                                                <template x-for="department in detail.departments" :key="department.id"><option :value="department.id" :disabled="department.id === detail.department_role_id" x-text="department.name"></option></template>
                                            </select>
                                            <p id="support-error-departmentRole" x-cloak x-show="fieldErrors.departmentRole" x-text="fieldErrors.departmentRole" role="alert" class="mt-2 text-xs text-red-600"></p>
                                        </label>
                                        <label class="block text-sm">{{ __('StaffCustomerSupport::support.transfer_reason') }}
                                            <textarea x-model="transferReason" x-ref="transferReason" @input="fieldErrors.transferReason = ''"
                                                :aria-invalid="!!fieldErrors.transferReason" aria-describedby="support-error-transferReason" required maxlength="3000" rows="3" class="mt-2 w-full rounded-lg border border-gray-200 p-3"></textarea>
                                            <p id="support-error-transferReason" x-cloak x-show="fieldErrors.transferReason" x-text="fieldErrors.transferReason" role="alert" class="mt-2 text-xs text-red-600"></p>
                                        </label>
                                        <button :disabled="busy" class="w-full rounded-lg bg-futa-orange px-4 py-3 font-semibold text-white disabled:opacity-50">{{ __('StaffCustomerSupport::support.transfer') }}</button>
                                    </form>
                                </section>
                            </aside>
                        </div>
                    </template>
                @endif
            </main>
        </div>
    </div>
@endsection
