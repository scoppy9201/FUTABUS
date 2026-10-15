import { ticketExchangeWizard } from '../../../../Payment/src/resources/js/exchange-wizard.js';

export function staffTicketExchange(endpoint, messages, urls) {
    const config = { tickets: [], csrf: urls.csrf, error: messages.error, consentError: messages.consent, expired: messages.expired };
    return {
        ...window.staffTicketList(endpoint, messages),
        flow: ticketExchangeWizard(config),
        step: 1, query: '', searchField: 'code', searched: false, choosing: false,
        tripBusy: false, tripSearched: false, resultsHtml: '', trips: [], date: '', reason: '',
        init() { this.flow.init(); },
        destroy() { this.flow.abandon(); this.flow.destroy(); },
        async searchTickets() {
            if (this.loadingTickets || !this.query.trim()) return;
            this.filters = { code: '', phone: '', name: '', date: '', status: '', [this.searchField]: this.query.trim() };
            this.searched = true;
            await this.loadTickets();
        },
        async chooseTicket(ticket) {
            if (this.choosing || !ticket.usable) return;
            this.choosing = true;
            try {
                const { data } = await window.axios.get(urls.context.replace('__TICKET__', ticket.id));
                this.selected = data.ticket;
                Object.assign(config, data.urls, { tickets: [{ ...data.ticket, eligible: true }] });
                this.flow.ticketId = data.ticket.id;
                this.flow.step = 1;
                this.flow.selectedTrip = null;
                this.flow.result = null;
                this.flow.completionAttempted = false;
                this.flow.errors = {};
                this.date = data.ticket.departure.slice(0, 10);
                this.resultsHtml = '';
                this.trips = [];
                this.tripSearched = false;
                this.reason = '';
                this.step = 2;
                this.connectSearch();
                this.scrollProgress();
            } catch (error) { this.notifyError(error); }
            finally { this.choosing = false; }
        },
        connectSearch() {
            const search = window.Alpine.$data(this.$refs.customerSearch.querySelector('section[x-data]'));
            search.departure = this.selected.origin;
            search.destination = this.selected.destination;
            search.roundTrip = false;
            window.dispatchEvent(new CustomEvent('hero-restore-dates', { detail: { departureDate: this.date, returnDate: this.date } }));
            window.dispatchEvent(new CustomEvent('hero-restore-quantity', { detail: { quantity: 1 } }));
            search.submitSearch = event => {
                event.preventDefault();
                this.date = new FormData(event.currentTarget).get('departure_date');
                this.searchTrips();
            };
            search.applyRecentSearch = recent => {
                const now = new Date();
                const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
                this.date = recent.departureDate < today ? today : recent.departureDate;
                window.dispatchEvent(new CustomEvent('hero-restore-dates', { detail: { departureDate: this.date, returnDate: this.date } }));
                this.searchTrips();
            };
        },
        async searchTrips() {
            if (this.tripBusy) return;
            this.tripBusy = true;
            this.resultsHtml = '';
            this.flow.errors = {};
            try {
                const { data } = await window.axios.get(config.trips, { params: { date: this.date } });
                this.resultsHtml = data.html;
                this.trips = data.trips;
                this.tripSearched = true;
            } catch (error) { this.notifyError(error); }
            finally { this.tripBusy = false; }
        },
        chooseTrip(selection) {
            const trip = this.trips.find(item => item.id === selection.trip.id);
            if (!trip || trip.available < 1 || this.step !== 2) return;
            this.flow.selectTrip(trip);
            this.flow.seatId = selection.seats.length === 1 ? String(selection.seats[0]) : '';
            this.flow.pickup = trip.stops.pickup.find(stop => !stop.expired)?.id || '';
            this.flow.dropoff = trip.stops.dropoff.find(stop => !stop.expired)?.id || '';
            this.step = 3;
            this.scrollProgress();
        },
        async holdSeat() {
            if (this.flow.busy || !this.flow.ready) return;
            this.flow.reason = this.reason;
            await this.flow.next();
            if (this.flow.hold) { this.step = 4; this.scrollProgress(); }
        },
        async completeExchange() {
            await this.flow.complete();
            if (this.flow.result) { this.step = 5; this.scrollProgress(); }
        },
        async back() {
            if (this.flow.busy || this.flow.completionAttempted) return;
            if (this.step === 4) {
                await this.flow.run(async () => { await this.flow.release(); this.flow.step = 1; this.step = 3; });
            } else this.step = Math.max(1, this.step - 1);
        },
        scrollProgress() { this.$nextTick(() => this.$refs.exchangeProgress.scrollIntoView({ behavior: 'smooth', block: 'start' })); },
        notifyError(error) {
            const errors = Object.values(error.response?.data?.errors || {}).flat();
            this.flow.errors = error.response?.data?.errors ? Object.fromEntries(Object.entries(error.response.data.errors).map(([key, values]) => [key, values[0]])) : {};
            (errors.length ? errors : [error.response?.status === 403 ? messages.denied : messages.error]).forEach(message => window.FutaNotify?.show(message, { tone: 'error', duration: 4000 }));
        },
    };
}
window.staffTicketExchange = staffTicketExchange;
