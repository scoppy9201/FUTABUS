import { staffExchangeWizard } from './exchange-wizard.js';
import { initTicketSuccess } from './customer-ticket-success.js';

export function staffTicketExchange(endpoint, messages, urls) {
    const config = { tickets: [], csrf: urls.csrf, error: messages.error, consentError: messages.consent, expired: messages.expired };
    return {
        ...window.staffTicketList(endpoint, messages),
        flow: staffExchangeWizard(config),
        step: 1, query: '', searchField: 'code', searched: false, choosing: false,
        tripBusy: false, tripSearched: false, resultsHtml: '', trips: [], date: '', reason: '',
        passengerName: '', passengerPhone: '', confirmedPassenger: null, pendingHold: null,
        pickupMode: 'station', dropoffMode: 'station', pickupAddress: '', dropoffAddress: '',
        get seatDecks() { return [...new Set(this.flow.selectedTrip?.seats.map(seat => seat.deck) || [])]; },
        get seatColumns() { return Math.max(1, ...(this.flow.selectedTrip?.seats.map(seat => Number(seat.column)) || [])); },
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
                this.passengerName = data.ticket.customer || '';
                this.passengerPhone = data.ticket.phone || '';
                this.confirmedPassenger = null;
                Object.assign(config, data.urls, { tickets: [{ ...data.ticket, eligible: true }] });
                this.flow.tickets = config.tickets;
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
                await this.searchTrips();
                this.scrollProgress();
            } catch (error) { this.notifyError(error); }
            finally { this.choosing = false; }
        },
        async searchTrips() {
            if (this.tripBusy) return;
            this.tripBusy = true;
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
            this.pickupMode = 'station'; this.dropoffMode = 'station';
            this.pickupAddress = ''; this.dropoffAddress = '';
            this.flow.seatId = selection.seats.length === 1 ? String(selection.seats[0]) : '';
            this.flow.pickup = trip.stops.pickup.find(stop => !stop.expired)?.id || '';
            this.flow.dropoff = trip.stops.dropoff.find(stop => !stop.expired)?.id || '';
            this.step = 3;
            this.scrollProgress();
        },
        seatRow(seat) {
            const rows = this.flow.selectedTrip.seats.filter(item => item.deck === seat.deck).map(item => Number(item.row));
            return Number(seat.row) - Math.min(...rows) + 1;
        },
        async holdSeat() {
            if (this.flow.busy || !this.flow.ready || this.step !== 3) return;
            if (!this.pendingHold) {
                this.passengerName = this.passengerName.trim();
                this.passengerPhone = this.passengerPhone.trim();
                if (!this.$refs.passengerForm.reportValidity()) return;
            }
            this.pendingHold ||= {
                ticket_id: this.flow.ticketId, trip_id: this.flow.selectedTrip.id, seat_id: this.flow.seatId,
                pickup: this.flow.pickup, dropoff: this.flow.dropoff, reason: this.reason,
                passenger_name: this.passengerName.trim(), passenger_phone: this.passengerPhone.trim(),
                pickup_mode: this.pickupMode, dropoff_mode: this.dropoffMode,
                pickup_address: this.pickupMode === 'transfer' ? this.pickupAddress.trim() : null,
                dropoff_address: this.dropoffMode === 'transfer' ? this.dropoffAddress.trim() : null,
            };
            await this.flow.run(async () => {
                try {
                    this.flow.hold = await this.flow.request(config.hold, this.pendingHold);
                } catch (error) {
                    if (error.status && error.status < 500) this.pendingHold = null;
                    throw error;
                }
                this.confirmedPassenger = this.flow.hold.passenger;
                this.pendingHold = null;
                this.flow.remaining = Math.max(0, Math.ceil((Date.parse(this.flow.hold.expires_at) - Date.now()) / 1000));
                this.flow.consent = false; this.flow.completionAttempted = false; this.flow.step = 2;
                this.step = 4;
                this.scrollProgress();
            });
        },
        async completeExchange() {
            if (this.step !== 4 || this.flow.busy || this.flow.result) return;
            this.flow.consent = true;
            await this.flow.complete();
            if (this.flow.result) {
                this.scrollProgress();
                this.$nextTick?.(() => {
                    if (this.$refs?.exchangeSuccess) initTicketSuccess(this.$refs.exchangeSuccess, messages.error);
                });
            }
        },
        async back() {
            if (this.flow.busy || this.flow.completionAttempted || this.pendingHold || this.flow.result) return;
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
