export function staffPayment(options) {
    return {
        detailOpen: false, tripTooltipOpen: false, priceTooltipOpen: false,
        paymentMethod: 'sepay', remainingSeconds: options.remaining, collectingCash: false,
        paymentClock: null, paymentPoll: null,
        startPaymentClock() {
            if (this.paymentClock !== null) return;
            // Keep a local deadline: background tabs can throttle interval callbacks.
            const deadline = performance.now() + options.remaining * 1000;
            const tick = () => {
                this.remainingSeconds = Math.max(0, Math.ceil((deadline - performance.now()) / 1000));
                if (this.remainingSeconds === 0) {
                    this.destroy();
                    this.refreshPaymentState().then(handled => {
                        if (!handled) window.location.replace(options.backUrl);
                    });
                }
            };
            this.paymentClock = setInterval(tick, 250);
            if (options.enabled) this.paymentPoll = setInterval(() => this.refreshPaymentState(), 3000);
            tick();
        },
        destroy() {
            clearInterval(this.paymentClock);
            clearInterval(this.paymentPoll);
            this.paymentClock = null;
            this.paymentPoll = null;
        },
        countdownText() {
            return `${String(Math.floor(this.remainingSeconds / 60)).padStart(2, '0')}:${String(this.remainingSeconds % 60).padStart(2, '0')}`;
        },
        async refreshPaymentState() {
            if (!options.enabled) return false;
            try {
                const { data } = await window.axios.get(options.statusUrl);
                if (['paid', 'needs_review'].includes(data.data.status)) {
                    window.location.replace(options.paymentUrl);
                    return true;
                }
                if (data.data.status === 'expired') {
                    window.location.replace(options.backUrl);
                    return true;
                }
            } catch { /* Keep the expiry clock running during transient network failures. */ }
            return false;
        },
        async collectCash() {
            if (this.collectingCash || this.remainingSeconds <= 0) return;
            this.collectingCash = true;
            try {
                await window.axios.post(options.cashUrl, { received: true }, { headers: { 'X-CSRF-TOKEN': options.csrf } });
                window.location.assign(options.paymentUrl);
            } catch (error) {
                const errors = Object.values(error.response?.data?.errors || {}).flat();
                (errors.length ? errors : [error.response?.data?.message || options.error]).forEach(message =>
                    window.FutaNotify?.show(message, { tone: 'error', duration: 4000 }));
                this.collectingCash = false;
            }
        },
        openTripDetail() {
            this.detailOpen = true;
            this.$nextTick(() => this.$refs.tripDetailClose.focus());
        },
        closeTripDetail() {
            if (!this.detailOpen) return;
            this.detailOpen = false;
            this.$nextTick(() => this.$refs.tripDetailTrigger.focus());
        },
    };
}
