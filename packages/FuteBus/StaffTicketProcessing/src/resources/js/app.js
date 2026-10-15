import '../../../../StaffDashboard/src/resources/js/app.js';
import { initTicketSuccess } from './customer-ticket-success';
import { staffPayment } from './payment';
import './ticket-list';

window.staffPayment = staffPayment;

window.counterSale = (urls, messages, initialSale = null) => ({
    employee: null, dashboard: null, sidebarOpen: false, access: false, ready: false, paymentReady: false,
    busy: false, errors: {}, trips: [], trip: null, selectedIds: [], sale: initialSale,
    uncertain: false, submission: null, searched: false,
    paymentMethod: 'sepay',
    bookingHtml: '', paymentHtml: initialSale?.html || '', resultsHtml: '',
    outboundTrip: null, outboundSelectedIds: [], outboundAmount: 0, outboundPickupMode: 'station', outboundDropoffMode: 'station', outboundPickupAddress: '', outboundDropoffAddress: '',
    criteria: initialSale?.preview?.criteria || { departure: '', destination: '', departure_date: '', trip_type: 'one_way', quantity: 1 },
    customerName: '', customerPhone: '', customerEmail: '', acceptTerms: false,
    pickupMode: 'station', dropoffMode: 'station', pickupAddress: '', dropoffAddress: '',
    termsModalOpen: false, termsScrolling: false, quoteAmount: null, quotedFare: null, boardingAt: null, quoteBusy: false, quoteRevision: 0,
    vehicleInfoOpen: false, vehicleInfoTab: 'schedule', tripDetailOpen: false, policyTooltipOpen: false,
    statusTimer: null, clockTimer: null, clockMs: Date.now(),
    async init() {
        await this.request(async () => {
            const { data } = await window.axios.get(urls.context);
            this.dashboard = data.data;
            this.employee = data.data.employee;
            this.access = data.data.groups.tickets.sale;
            this.paymentReady = data.data.payment_ready === true;
            this.ready = true;
            this.$nextTick(() => {
                if (initialSale) {
                    if (initialSale.booking) initTicketSuccess(this.$refs.paymentView, messages.error);
                } else this.connectCustomerSearch(data.data.locations);
            });
        });
        this.$watch('sale', sale => {
            if (sale?.status === 'pending' && !this.paymentHtml && !this.statusTimer) {
                this.statusTimer = setInterval(() => { if (!this.busy) this.refresh(); }, 3000);
                this.clockTimer = setInterval(() => { this.clockMs = Date.now(); }, 1000);
            } else if (sale?.status !== 'pending') this.destroy();
        });
    },
    destroy() {
        clearInterval(this.statusTimer); clearInterval(this.clockTimer);
        this.statusTimer = null; this.clockTimer = null;
    },
    connectCustomerSearch(locations) {
        const element = this.$refs.customerSearch.querySelector('section[x-data]');
        const search = window.Alpine.$data(element);
        search.locationCatalog = locations;
        const submit = search.submitSearch.bind(search);
        search.submitSearch = event => {
            submit(event);
            if (event.defaultPrevented) return;
            event.preventDefault();
            this.criteria = Object.fromEntries(new FormData(event.currentTarget));
            this.search();
        };
        search.applyRecentSearch = recent => {
            const url = new URL(urls.page, window.location.href);
            const now = new Date();
            const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
            const date = recent.departureDate < today ? today : recent.departureDate;
            for (const [key,
                value] of Object.entries({ departure: recent.departure,
                destination: recent.destination,
                departure_date: date,
                trip_type: recent.roundTrip ? 'round_trip' : 'one_way',
                quantity: recent.quantity || 1,
                return_date: recent.returnDate >= date ? recent.returnDate : date })) url.searchParams.set(key,
                value);
            window.location.assign(url);
        };
        const params = new URLSearchParams(window.location.search);
        if (params.has('sale')) {
            this.criteria = Object.fromEntries(params);
            this.submission = { idempotency_key: params.get('sale') };
            this.refresh();
            return;
        }
        if (params.has('departure') && params.has('destination') && params.has('departure_date')) {
            this.criteria = Object.fromEntries(params);
            this.search();
        }
    },
    async selectCustomerTrip(selection) {
        this.criteria.direction = selection.direction;
        if (selection.outboundTrip) {
            this.outboundTrip = selection.outboundTrip;
            this.outboundSelectedIds = selection.outboundSeats;
        }
        await this.request(async () => {
            const params = { ...this.criteria, seats: selection.seats.join(','), outbound_trip: selection.outboundTrip?.id, outbound_seats: selection.outboundSeats?.join(',') };
            const { data } = await window.axios.get(urls.booking.replace('__TRIP__', selection.trip.id), { params });
            this.trip = selection.trip;
            this.bookingHtml = data.data.html;
        });
    },
    async search() {
        if (this.uncertain) return;
        this.criteria.direction = 'outbound';
        this.trip = null; this.selectedIds = []; this.submission = null; this.outboundTrip = null; this.outboundSelectedIds = [];
        await this.request(async () => {
            const { data } = await window.axios.get(urls.trips, { params: this.criteria });
            this.trips = data.data;
            this.resultsHtml = data.html;
            this.searched = true;
        });
    },
    backToResults() {
        if (this.busy || this.uncertain || this.sale) return;
        this.quoteRevision++;
        this.trip = null;
        this.selectedIds = [];
        this.submission = null;
        this.errors = {};
        this.quoteAmount = null;
        this.quotedFare = null;
        this.boardingAt = null;
        this.quoteBusy = false;
    },
    async create() {
        if (!this.trip || this.busy) return;
        this.submission ||= { ...this.criteria, trip_id: this.trip.id, idempotency_key: crypto.randomUUID(), seats: [...this.selectedIds],
            name: this.customerName, phone: this.customerPhone, email: this.customerEmail, accept_terms: this.acceptTerms, quoted_fare: this.quotedFare,
            pickup_mode: this.pickupMode, dropoff_mode: this.dropoffMode, pickup_address: this.pickupAddress, dropoff_address: this.dropoffAddress };
        if (this.criteria.trip_type === 'round_trip') Object.assign(this.submission, {
            outbound_trip: this.outboundTrip?.id, outbound_seats: [...this.outboundSelectedIds],
            outbound_pickup_mode: this.outboundPickupMode, outbound_dropoff_mode: this.outboundDropoffMode,
            outbound_pickup_address: this.outboundPickupAddress, outbound_dropoff_address: this.outboundDropoffAddress,
        });
        await this.request(async () => {
            try {
                this.sale = (await window.axios.post(this.paymentReady ? urls.create : urls.preview, this.submission, { headers: { 'X-CSRF-TOKEN': urls.csrf } })).data.data;
                this.paymentHtml = this.sale.html;
                this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
                if (this.sale.id) {
                    const url = new URL(window.location.href);
                    for (const [key, value] of Object.entries(this.criteria)) if (value != null) url.searchParams.set(key, value);
                    url.searchParams.set('sale', this.sale.id);
                    window.history.replaceState({}, '', url);
                }
                if (this.sale.booking) this.$nextTick(() => initTicketSuccess(this.$refs.paymentView, messages.error));
                this.uncertain = false;
            } catch (error) {
                const unavailable = error.response?.status === 503 && error.response.data?.code === 'payment_unavailable';
                if (unavailable) this.paymentReady = false;
                this.uncertain = !unavailable && (!error.response || error.response.status >= 500);
                if (!this.uncertain && error.response.status !== 409) this.submission = null;
                throw error;
            }
        });
    },
    async refresh() {
        await this.request(async () => {
            const previousStatus = this.sale?.status;
            this.sale = (await window.axios.get(urls.status.replace('__SALE__', this.sale?.id || this.submission.idempotency_key))).data.data;
            if (!this.paymentHtml || this.sale.status !== previousStatus) this.paymentHtml = this.sale.html;
            if (this.sale.booking) this.$nextTick(() => initTicketSuccess(this.$refs.paymentView, messages.error));
            this.uncertain = false;
        });
    },
    async submitCustomerBooking(detail) {
        const fields = new FormData(detail.form);
        this.criteria = { ...detail.criteria, direction: this.criteria.direction };
        this.trip = detail.trip;
        this.selectedIds = fields.getAll('seats[]').map(Number);
        this.outboundTrip = detail.outboundTrip;
        this.outboundSelectedIds = fields.getAll('outbound_seats[]').map(Number);
        this.customerName = fields.get('name'); this.customerPhone = fields.get('phone'); this.customerEmail = fields.get('email');
        this.acceptTerms = fields.get('accept_terms') === '1';
        this.outboundPickupMode = fields.get('outbound_pickup_mode') || 'station'; this.outboundDropoffMode = fields.get('outbound_dropoff_mode') || 'station';
        this.outboundPickupAddress = fields.get('outbound_pickup_address') || ''; this.outboundDropoffAddress = fields.get('outbound_dropoff_address') || '';
        this.submission ||= { ...this.criteria, ...Object.fromEntries(fields), trip_id: detail.trip.id, idempotency_key: crypto.randomUUID(),
            seats: this.selectedIds, outbound_trip: detail.outboundTrip?.id, outbound_seats: this.outboundSelectedIds, quoted_fare: detail.trip.price };
        await this.create();
        if (Object.keys(this.errors).length) this.$dispatch('staff-booking-errors', this.errors);
    },
    async backFromPayment() {
        if (this.sale?.id) {
            await this.request(async () => {
                await window.axios.delete(urls.status.replace('__SALE__', this.sale.id), { headers: { 'X-CSRF-TOKEN': urls.csrf } });
                this.sale = null; this.paymentHtml = ''; this.submission = null;
            });
        } else { this.sale = null; this.paymentHtml = ''; this.submission = null; }
        if (!this.sale) {
            if (initialSale) {
                const page = new URL(urls.page, window.location.href);
                for (const [key, value] of Object.entries(this.criteria)) if (value != null) page.searchParams.set(key, value);
                window.location.assign(page);
                return;
            }
            const url = new URL(window.location.href);
            url.searchParams.delete('sale');
            window.history.replaceState({}, '', url);
            if (!this.trip) this.search();
        }
    },
    async request(action) {
        if (this.busy) return;
        this.busy = true; this.errors = {};
        try { await action(); }
        catch (error) {
            this.errors = error.response?.data?.errors || {};
            if (error.response?.status !== 422) window.FutaNotify.error(error.response?.status === 403 ? messages.denied : error.response?.data?.code === 'payment_unavailable' ? messages.unavailable : messages.error, { duration: 4000 });
        } finally { this.busy = false; }
    },
});

import './exchange';
