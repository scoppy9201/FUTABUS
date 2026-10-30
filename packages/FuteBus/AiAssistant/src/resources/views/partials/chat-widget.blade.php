<div class="ai-chat" x-data="futaAiChat({ endpoint: @js(route('ai-assistant.ask')), csrf: @js(csrf_token()), collectCustomer: @js(!auth()->user()?->hasPermissionTo('report.view')), copy: @js(__('AiAssistant::app')) })" @keydown.escape.window="open = false">
    <button type="button" class="ai-chat__launcher" @click="open = !open" :aria-label="open ? copy.close : copy.open"
        :aria-expanded="open.toString()" aria-controls="futa-ai-chat-panel">
        <img src="{{ asset('images/support/customer-support-agent.png') }}" alt="" aria-hidden="true">
        <span class="sr-only" x-text="open ? copy.close : copy.open">
        </span>
    </button>
    <section id="futa-ai-chat-panel" class="ai-chat__panel" :class="{ 'is-intake': !started }" x-cloak x-show="open"
        x-transition aria-label="{{ __('AiAssistant::app.title') }}">
        <header class="ai-chat__header">
            <div>
                <h2>{{ __('AiAssistant::app.title') }}</h2>
                <p x-show="started">{{ __('AiAssistant::app.subtitle') }}</p>
                <p class="ai-chat__welcome" x-show="!started">{{ __('AiAssistant::app.intake_intro') }}</p>
            </div>
            <button type="button" @click="open = false" :aria-label="copy.close">×</button>
        </header>
        <form class="ai-chat__intake" x-show="!started" x-ref="intake" novalidate @submit.prevent="startChat()"
            @input="delete errors[$event.target.name]" @change="delete errors[$event.target.name]"
            @focusout="validateField($event.target.name)">
            <fieldset class="ai-chat__salutations">
                <legend>{{ __('AiAssistant::app.salutation') }}</legend>
                @foreach (['mr', 'ms'] as $salutation)
                    <label class="ai-chat__option">
                        <input type="radio" x-model="customer.salutation" name="salutation"
                            value="{{ $salutation }}"> {{ __('AiAssistant::app.salutation_' . $salutation) }}</label>
                @endforeach
            </fieldset>
            <div class="ai-chat__field" :class="{ 'has-error': errors.name }">
                <label for="chat-name">
                    <span aria-hidden="true">*</span> {{ __('AiAssistant::app.customer_name') }}</label>
                <input id="chat-name" name="name" x-model="customer.name" autocomplete="name" required
                    maxlength="100" pattern=".*\S.*" :aria-invalid="Boolean(errors.name).toString()"
                    aria-describedby="chat-name-error">
                <p id="chat-name-error" class="ai-chat__field-error" x-show="errors.name" x-text="errors.name">
                </p>
            </div>
            <div class="ai-chat__field" :class="{ 'has-error': errors.email }">
                <label for="chat-email">Email</label>
                <input id="chat-email" name="email" x-ref="email" type="email" x-model="customer.email"
                    autocomplete="email" maxlength="254" :aria-invalid="Boolean(errors.email).toString()"
                    aria-describedby="chat-email-error">
                <p id="chat-email-error" class="ai-chat__field-error" x-show="errors.email" x-text="errors.email">
                </p>
            </div>
            <div class="ai-chat__field ai-chat__phone-field" :class="{ 'has-error': errors.phone }">
                <div class="ai-chat__phone" x-ref="phoneRow">
                    <input type="hidden" name="phone_country" :value="phoneCountry">
                    <button id="chat-country-code" x-ref="countryTrigger" type="button"
                        class="ai-chat__country-trigger" @click="toggleCountries()"
                        @keydown.arrow-down.prevent="!countryOpen && toggleCountries()" aria-haspopup="dialog"
                        aria-controls="chat-country-panel" :aria-expanded="countryOpen.toString()"
                        :aria-label="`${copy.country_code}: ${selectedCountry?.name} (${selectedCountry?.prefix})`">
                        <img class="ai-chat__country-flag" :src="flagFor(phoneCountry)" alt=""
                            aria-hidden="true">
                        <x-heroicon-m-chevron-down class="size-3" aria-hidden="true" />
                    </button>
                    <div class="ai-chat__phone-number">
                        <label for="chat-phone">
                            <span aria-hidden="true">*</span> {{ __('AiAssistant::app.customer_phone') }}</label>
                        <span class="ai-chat__dial-plus" aria-hidden="true">+</span>
                        <span class="ai-chat__dial-code" x-text="selectedCountry?.prefix.slice(1)" aria-hidden="true">
                        </span>
                        <input id="chat-phone" name="phone" x-ref="phone" type="tel" required
                            x-model="customer.phone" @blur="formatPhone()" autocomplete="tel-national" inputmode="tel"
                            maxlength="30" :aria-invalid="Boolean(errors.phone).toString()"
                            aria-describedby="chat-phone-error chat-phone-hint">
                    </div>
                </div>
                <div id="chat-country-panel" x-ref="countryPanel" class="ai-chat__countries" x-cloak role="dialog"
                    :aria-label="copy.country_code">
                    <label class="sr-only"
                        for="chat-country-search">{{ __('AiAssistant::app.search_country') }}</label>
                    <input id="chat-country-search" x-ref="countrySearch" type="search" x-model="countrySearch"
                        :placeholder="copy.search_country" autocomplete="off"
                        @keydown.arrow-down.prevent="focusCountry(0)" @keydown.arrow-up.prevent="focusCountry(-1)"
                        @keydown.enter.prevent="filteredCountries.length && chooseCountry(filteredCountries[0].code)"
                        aria-controls="chat-country-list">
                    <div id="chat-country-list" x-ref="countryList" class="ai-chat__country-list" role="listbox"
                        :aria-label="copy.country_code">
                        <template x-for="(country, index) in filteredCountries" :key="country.code">
                            <button type="button" role="option" tabindex="-1"
                                :aria-selected="(country.code === phoneCountry).toString()"
                                @click="chooseCountry(country.code)"
                                @keydown.arrow-down.prevent="focusCountry(index + 1)"
                                @keydown.arrow-up.prevent="focusCountry(index - 1)"
                                @keydown.home.prevent="focusCountry(0)" @keydown.end.prevent="focusCountry(-1)">
                                <img class="ai-chat__country-flag" :src="flagFor(country.code)" alt=""
                                    aria-hidden="true" loading="lazy">
                                <span class="ai-chat__country-name" x-text="country.name">
                                </span>
                                <span class="ai-chat__country-prefix" x-text="`(${country.prefix})`">
                                </span>
                            </button>
                        </template>
                        <p class="ai-chat__country-empty" x-show="!filteredCountries.length"
                            x-text="copy.no_countries">
                        </p>
                    </div>
                </div>
                <p id="chat-phone-error" class="ai-chat__field-error" x-show="errors.phone" x-text="errors.phone">
                </p>
                <p id="chat-phone-hint" class="ai-chat__privacy" x-show="!errors.phone">
                    {{ __('AiAssistant::app.phone_hint') }}</p>
            </div>
            <fieldset :class="{ 'has-error': errors.topic }">
                <legend>
                    <span aria-hidden="true">*</span> {{ __('AiAssistant::app.support_topic') }}
                </legend>
                @foreach (['booking', 'feedback', 'locations', 'schedule', 'other'] as $topic)
                    <label class="ai-chat__option">
                        <input type="radio" x-model="customer.topic" name="topic" value="{{ $topic }}"
                            required :aria-invalid="Boolean(errors.topic).toString()"
                            aria-describedby="chat-topic-error"> {{ __('AiAssistant::app.topic_' . $topic) }}</label>
                @endforeach
                <p id="chat-topic-error" class="ai-chat__field-error" x-show="errors.topic" x-text="errors.topic">
                </p>
            </fieldset>
            <div class="ai-chat__field" :class="{ 'has-error': errors.content }">
                <label for="chat-content">
                    <span aria-hidden="true">*</span> {{ __('AiAssistant::app.support_content') }}</label>
                <textarea id="chat-content" name="content" x-model="customer.content" required maxlength="500" rows="3"
                    :aria-invalid="Boolean(errors.content).toString()" aria-describedby="chat-content-error">
</textarea>
                <p id="chat-content-error" class="ai-chat__field-error" x-show="errors.content"
                    x-text="errors.content">
                </p>
            </div>
            <p class="ai-chat__privacy">{{ __('AiAssistant::app.intake_privacy') }}</p>
            <button type="submit" :disabled="loading">
                <x-heroicon-o-paper-airplane class="size-5" /> {{ __('AiAssistant::app.start_chat') }}</button>
        </form>
        <div class="ai-chat__messages" x-show="started" x-ref="messages" aria-live="polite">
            <template x-for="(item, index) in messages" :key="index">
                <article class="ai-chat__message" :class="item.role === 'user' ? 'is-user' : 'is-assistant'">
                    <p x-text="item.text">
                    </p>

                    {{-- Metrics card (report KPIs) --}}
                    <template x-if="item.metrics?.length">
                        <div class="ai-chat__metrics">
                            <template x-for="metric in item.metrics" :key="metric.label">
                                <div>
                                    <span x-text="metric.label">
                                    </span>
                                    <strong x-text="formatMetric(metric)">
                                    </strong>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Ticket detail card --}}
                    <template x-if="item.ticket">
                        <div class="ai-chat__ticket">
                            <strong x-text="`${copy.booking_code}: ${item.ticket.booking_code}`">
                            </strong>
                            <span x-text="`${item.ticket.origin} → ${item.ticket.destination}`">
                            </span>
                            <span x-text="`${copy.departure}: ${item.ticket.departure}`">
                            </span>
                            <span x-text="`${copy.vehicle}: ${item.ticket.bus_type}`">
                            </span>
                            <template x-for="seat in item.ticket.seats" :key="seat.code">
                                <span
                                    x-text="`${copy.ticket_code}: ${seat.code} · ${copy.seat}: ${seat.seat || copy.not_assigned}`">
                                </span>
                            </template>
                        </div>
                    </template>

                    {{-- Trip cards --}}
                    <template x-if="item.trips?.length">
                        <div class="ai-chat__trips">
                            <template x-for="trip in item.trips" :key="trip.id">
                                <article class="ai-chat__trip">
                                    <strong x-text="`${trip.origin} → ${trip.destination}`">
                                    </strong>
                                    <span x-text="`${trip.departure} · ${copy.arrival}: ${trip.arrival}`">
                                    </span>
                                    <span
                                        x-text="`${copy.vehicle}: ${trip.type} · ${copy.duration}: ${Math.floor(trip.duration_minutes / 60)}h ${trip.duration_minutes % 60}m`">
                                    </span>
                                    <span x-text="copy.seats.replace(':count', trip.available_seats)">
                                    </span>
                                    <b
                                        x-text="new Intl.NumberFormat(locale, { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(trip.price)">
                                    </b>
                                    <button type="button" class="ai-chat__confirm-btn" @click="confirmTrip(trip)"
                                        x-text="copy.choose">
                                    </button>
                                </article>
                            </template>
                        </div>
                    </template>

                    {{-- Confirm navigation card (replaces raw links) --}}
                    <template x-if="item.pending_action?.url">
                        <div class="ai-chat__confirm">
                            <p class="ai-chat__confirm-desc"
                                x-show="item.pending_action.description && item.pending_action.description !== item.text"
                                x-text="item.pending_action.description">
                            </p>
                            <button type="button" class="ai-chat__confirm-btn"
                                @click="confirmNavigation(item.pending_action)" x-text="item.pending_action.label">
                            </button>
                        </div>
                    </template>

                </article>
            </template>
            <p class="ai-chat__status" x-show="loading" x-text="copy.loading">
            </p>
        </div>
        <form class="ai-chat__form" x-show="started" @submit.prevent="send()">
            <label class="sr-only" for="futa-ai-chat-input">{{ __('AiAssistant::app.input_label') }}</label>
            <input id="futa-ai-chat-input" x-model="draft" maxlength="500" autocomplete="off"
                :placeholder="copy.placeholder" :disabled="loading">
            <button type="submit" :disabled="loading || !draft.trim()" x-text="copy.send">
            </button>
        </form>
    </section>
</div>
