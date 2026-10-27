export function staffTicketCancellation(endpoint, config) {
    return {
        ...window.staffTicketList(endpoint, config),
        step: 1, query: '', searchField: 'code', searched: false, choosing: false,
        refundMethod: 'request',
        get cashRefund() { return this.context?.cash || this.refundMethod === 'cash'; },
        context: null, reason: '', note: '', busy: false, cashPaid: false, pending: null, result: null, quoteStale: false,
        init() {},
        resetReasonValidation() {
            const input = this.$refs.cancellationReason;
            if (!input) return;
            input.required = false;
            input.setCustomValidity('');
            input.value = this.reason;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },
        scroll() { this.$nextTick(() => this.$refs.progress.scrollIntoView({ behavior: 'smooth', block: 'start' })); },
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
                const { data } = await window.axios.get(config.context.replace('__TICKET__', ticket.id), { timeout: 20000 });
                this.context = data;
                this.refundMethod = data.cash || !data.can_request_refund ? 'cash' : 'request';
                this.quoteStale = false;
                this.selected = data.ticket;
                this.reason = ''; this.note = ''; this.pending = null; this.result = null; this.cashPaid = false;
                this.step = 2;
                this.resetReasonValidation();
                this.scroll();
            } catch (error) { this.notify(error); }
            finally { this.choosing = false; }
        },
        proceed() {
            if (this.step !== 2 || this.busy || this.quoteStale) return;
            const input = this.$refs.cancellationReason;
            if (input) {
                input.required = true;
                input.setCustomValidity(this.reason ? '' : input.dataset.requiredMessage);
            }
            if (!this.$refs.reasonForm.reportValidity()) return;
            this.step = 3; this.cashPaid = false; this.scroll();
        },
        back() {
            if (this.busy || this.pending || this.result) return;
            this.step = Math.max(1, this.step - 1);
            this.resetReasonValidation();
            this.scroll();
        },
        async submitCancellation() {
            if (this.busy || this.result || this.step !== 3 || !this.context) return;
            if (this.cashRefund && (!this.cashPaid || !this.context.can_refund_cash)) return;
            this.pending ||= { expected_refund: this.context.quote.refund, reason: this.reason,
                note: this.note.trim(), refund_method: this.cashRefund ? 'cash' : 'request', cash_paid: this.cashRefund && this.cashPaid };
            this.busy = true;
            window.FutaLoader?.show(0);
            try {
                const { data } = await window.axios.post(config.store.replace('__TICKET__', this.selected.id), this.pending,
                    { timeout: 20000, headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' } });
                this.result = data; this.pending = null; this.step = 4; this.scroll();
            } catch (error) {
                if (error.response?.status && error.response.status < 500) this.pending = null;
                if (error.response?.status === 422) { this.step = 2; this.quoteStale = true; this.scroll(); }
                this.notify(error);
            } finally { this.busy = false; window.FutaLoader?.hide(); }
        },
        time(value) {
            return value ? new Intl.DateTimeFormat(document.documentElement.lang || 'vi', { dateStyle: 'short', timeStyle: 'short', timeZone: 'Asia/Ho_Chi_Minh' }).format(new Date(value.replace(' ', 'T'))) : '—';
        },
        notify(error) {
            const errors = Object.values(error.response?.data?.errors || {}).flat();
            const messages = errors.length ? errors : [error.response?.status === 403 ? config.denied : error.response?.data?.message || config.error];
            messages.forEach(message => window.FutaNotify?.show(message, { tone: 'error', duration: 5000 }));
        },
    };
}
window.staffTicketCancellation = staffTicketCancellation;
