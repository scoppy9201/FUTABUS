export function ticketExchangeWizard(config) {
    return {
        step: 0, busy: false, ticketId: '', date: '', trips: [], selectedTrip: null, seatId: '',
        pickup: '', dropoff: '', reason: '', consent: false, hold: null, result: null, errors: {}, remaining: 0, timer: null, completionAttempted: false,
        get ticket() { return config.tickets.find(ticket => String(ticket.id) === String(this.ticketId)); },
        get seat() { return this.selectedTrip?.seats.find(seat => String(seat.id) === String(this.seatId)); },
        get pickupStop() { return this.selectedTrip?.stops.pickup.find(stop => String(stop.id) === String(this.pickup)); },
        get dropoffStop() { return this.selectedTrip?.stops.dropoff.find(stop => String(stop.id) === String(this.dropoff)); },
        get decks() { return [...new Set(this.selectedTrip?.seats.map(seat => seat.deck) || [])]; },
        get columns() { return Math.max(1, ...(this.selectedTrip?.seats.map(seat => Number(seat.column)) || [1])); },
        get ready() { return this.ticket && this.selectedTrip && this.seat && this.pickup && this.dropoff; },
        money(amount) { return new Intl.NumberFormat(document.documentElement.lang || 'vi').format(amount || 0) + ' ₫'; },
        time(value) { return value ? new Intl.DateTimeFormat(document.documentElement.lang || 'vi', { dateStyle: 'short', timeStyle: 'short', timeZone: 'Asia/Ho_Chi_Minh' }).format(new Date(value.replace(' ', 'T'))) : ''; },
        init() {
            this.timer = setInterval(() => {
                this.remaining = this.hold ? Math.max(0, Math.ceil((Date.parse(this.hold.expires_at) - Date.now()) / 1000)) : 0;
            }, 1000);
        },
        destroy() { clearInterval(this.timer); },
        async request(url, data = null) {
            const response = await fetch(url, {
                method: data ? 'POST' : 'GET', credentials: 'same-origin', cache: 'no-store',
                headers: { Accept: 'application/json', ...(data ? { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf } : {}) },
                ...(data ? { body: JSON.stringify(data) } : {}), signal: AbortSignal.timeout(20000),
            });
            if (response.status === 204) return null;
            const result = await response.json();
            if (!response.ok) {
                if (response.status === 422 && result.errors) {
                    this.errors = Object.fromEntries(Object.entries(result.errors).map(([key, messages]) => [key, messages[0]]));
                    if (this.step === 2) window.FutaNotify?.error(Object.values(this.errors).join(' '));
                }
                else window.FutaNotify?.error(result.message || config.error);
                throw new Error(config.error);
            }
            return result;
        },
        async run(operation) {
            if (this.busy) return;
            this.busy = true; this.errors = {};
            window.FutaLoader?.show(0);
            try { await operation(); }
            catch (error) { if (Object.keys(this.errors).length === 0) window.FutaNotify?.error(config.error); }
            finally { this.busy = false; window.FutaLoader?.hide(); }
        },
        async loadTrips() {
            this.trips = []; this.selectedTrip = null; this.seatId = ''; this.pickup = ''; this.dropoff = '';
            if (!this.date || !this.ticketId) return;
            await this.run(async () => {
                const url = new URL(config.trips);
                url.searchParams.set('ticket_id', this.ticketId); url.searchParams.set('date', this.date);
                this.trips = (await this.request(url.href)).trips;
            });
        },
        selectTrip(trip) { this.selectedTrip = trip; this.seatId = ''; this.pickup = ''; this.dropoff = ''; this.errors = {}; },
        async next() {
            if (this.step === 0 && this.ticket?.eligible) {
                this.step = 1; this.date = this.ticket.departure.slice(0, 10); await this.loadTrips(); return;
            }
            if (this.step !== 1 || !this.ready) return;
            await this.run(async () => {
                this.hold = await this.request(config.hold, { ticket_id: this.ticketId, trip_id: this.selectedTrip.id,
                    seat_id: this.seatId, pickup: this.pickup, dropoff: this.dropoff, reason: this.reason });
                this.remaining = Math.max(0, Math.ceil((Date.parse(this.hold.expires_at) - Date.now()) / 1000));
                this.consent = false; this.completionAttempted = false; this.step = 2;
            });
        },
        async release() {
            if (!this.hold || this.result) return;
            await this.request(config.base + '/' + this.hold.token + '/bo-giu', {});
            this.hold = null;
        },
        async back() {
            await this.run(async () => { await this.release(); this.step = Math.max(0, this.step - 1); });
        },
        async complete() {
            if (!this.consent) { this.errors = { consent: config.consentError }; return; }
            if (!this.hold || (!this.remaining && !this.completionAttempted)) { this.errors = { ticket: config.expired }; return; }
            await this.run(async () => {
                this.completionAttempted = true;
                this.result = await this.request(config.base + '/' + this.hold.token + '/xac-nhan', { consent: true });
                this.step = 3;
            });
        },
        async close() {
            if (this.result) { window.location.reload(); return; }
            await this.run(async () => {
                await this.release();
                if (this.completionAttempted) window.location.reload();
                else this.$el.closest('dialog').close();
            });
        },
        abandon() {
            if (!this.hold || this.result) return;
            fetch(config.base + '/' + this.hold.token + '/bo-giu', {
                method: 'POST', credentials: 'same-origin', keepalive: true,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf }, body: '{}',
            }).catch(() => {});
        },
    };
}
