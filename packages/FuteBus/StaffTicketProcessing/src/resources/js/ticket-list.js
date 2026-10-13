import '../../../../StaffDashboard/src/resources/js/app.js';

window.staffTicketList = (endpoint, messages) => ({
    rows: [], selected: null, loadingTickets: false, listFailed: false,
    filters: { code: '', phone: '', name: '', date: '', status: '' },
    meta: { current_page: 1, last_page: 1, total: 0, from: null, to: null },
    init() {
        this.$watch('groups', groups => {
            if (groups?.tickets.lookup) this.loadTickets();
        });
        if (this.groups?.tickets.lookup) this.loadTickets();
    },
    async loadTickets(page = 1) {
        if (this.loadingTickets || !this.groups?.tickets.lookup) return;
        this.loadingTickets = true;
        this.listFailed = false;
        try {
            const { data } = await window.axios.get(endpoint, { params: { ...this.filters, page, lang: document.documentElement.lang } });
            this.rows = data.data;
            this.meta = data.meta;
        } catch (error) {
            this.rows = [];
            this.listFailed = true;
            const errors = Object.values(error.response?.data?.errors || {}).flat();
            (errors.length ? errors : [error.response?.status === 403 ? messages.denied : messages.error]).forEach(message =>
                window.FutaNotify?.show(message, { tone: 'error', duration: 4000 }));
        } finally {
            this.loadingTickets = false;
        }
    },
    money(amount) {
        return new Intl.NumberFormat(document.documentElement.lang, { style: 'currency', currency: 'VND' }).format(amount);
    },
    statusClass(status) {
        return { active: 'bg-green-50 text-green-700', expired: 'bg-gray-100 text-gray-600', used: 'bg-blue-50 text-blue-700', cancelled: 'bg-red-50 text-red-700', refunded: 'bg-orange-50 text-orange-700' }[status] || 'bg-gray-50 text-gray-600';
    },
    async openTicket(ticket) {
        try {
            const { data } = await window.axios.get(endpoint, { params: { code: ticket.code, lang: document.documentElement.lang } });
            if (!data.data.length) throw new Error(messages.error);
            this.selected = data.data[0];
            this.$nextTick(() => this.$refs.ticketDetail.showModal());
        } catch {
            window.FutaNotify?.show(messages.error, { tone: 'error', duration: 4000 });
        }
    },
});
