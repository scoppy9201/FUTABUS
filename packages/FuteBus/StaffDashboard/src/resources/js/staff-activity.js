export function staffActivity(endpoint) {
    const today = () => {
        const parts = new Intl.DateTimeFormat('en', { timeZone: 'Asia/Ho_Chi_Minh', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date());
        const value = (type) => parts.find((part) => part.type === type).value;
        return `${value('year')}-${value('month')}-${value('day')}`;
    };
    return {
        activityMonth: today().slice(0, 7),
        activityToday: today(),
        activityDays: [],
        activityQueue: [],
        activityUpdatedAt: null,
        activityLoading: false,
        activityFailed: false,
        activitySelectedDate: null,
        activityTimer: null,
        activityController: null,
        activityRequestId: 0,
        async initActivity() {
            if (!endpoint) return;
            this.activityTimer = window.setInterval(() => {
                if (!document.hidden && !this.activityLoading) this.loadActivity();
            }, 15000);
            await this.loadActivity();
        },
        destroyActivity() {
            window.clearInterval(this.activityTimer);
            this.activityRequestId++;
            this.activityController?.abort();
        },
        async loadActivity() {
            if (!endpoint) return;
            this.activityController?.abort();
            const request = ++this.activityRequestId;
            this.activityController = new AbortController();
            this.activityLoading = true;
            try {
                const response = await window.axios.get(endpoint, {
                    params: { month: this.activityMonth, lang: document.documentElement.lang },
                    headers: { Accept: 'application/json' }, signal: this.activityController.signal,
                });
                if (request !== this.activityRequestId) return;
                this.activityDays = response.data.data.days;
                this.activityQueue = response.data.data.queue;
                this.activityUpdatedAt = response.data.data.updated_at;
                this.activityToday = today();
                this.activityFailed = false;
            } catch (error) {
                if (request === this.activityRequestId && error.code !== 'ERR_CANCELED') this.activityFailed = true;
            } finally {
                if (request === this.activityRequestId) this.activityLoading = false;
            }
        },
        get activityCells() {
            const start = new Date(`${this.activityMonth}-01T12:00:00Z`);
            const padding = (start.getUTCDay() + 6) % 7;
            return [...Array.from({ length: padding }, (_, index) => ({ date: null, key: `empty-${index}` })),
                ...this.activityDays.map((day) => ({ ...day, key: day.date }))];
        },
        get activitySelectedDay() {
            return this.activityDays.find((day) => day.date === this.activitySelectedDate) || null;
        },
        activityMonthTitle() {
            return new Intl.DateTimeFormat(document.documentElement.lang, { month: 'long', year: 'numeric', timeZone: 'UTC' })
                .format(new Date(`${this.activityMonth}-01T12:00:00Z`));
        },
        activityDateLabel(date) {
            if (!date) return '';
            return new Intl.DateTimeFormat(document.documentElement.lang, { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'UTC' })
                .format(new Date(`${date}T12:00:00Z`));
        },
        activityPending(day) { return (day?.support_pending ?? 0) + (day?.refund_pending ?? 0); },
        async changeActivityMonth(offset) {
            const date = new Date(`${this.activityMonth}-01T12:00:00Z`);
            date.setUTCMonth(date.getUTCMonth() + offset);
            const month = date.toISOString().slice(0, 7);
            if (month < '1900-01' || month > '2099-12') return;
            this.activityMonth = month;
            this.activityDays = [];
            await this.loadActivity();
        },
        async showActivityToday() {
            this.activityMonth = today().slice(0, 7);
            await this.loadActivity();
        },
        openActivityDay(day) {
            this.activitySelectedDate = day.date;
            this.$nextTick(() => this.$refs.activityPanel.showModal());
        },
    };
}
