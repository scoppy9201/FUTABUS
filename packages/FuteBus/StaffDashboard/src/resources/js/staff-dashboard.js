window.staffDashboard = (endpoint) => ({
    employee: null,
    groups: null,
    dashboard: null,
    sidebarOpen: false,
    loading: true,
    failed: false,
    clockTimer: null,
    clockListener: null,
    dateLabel: '',
    timeLabel: '',
    async init() {
        this.updateClock();
        this.clockTimer = window.setInterval(() => this.updateClock(), 1000);
        this.clockListener = () => this.updateClock();
        document.addEventListener('visibilitychange', this.clockListener);
        window.addEventListener('focus', this.clockListener);
        await this.load();
    },
    updateClock() {
        const now = new Date();
        const locale = document.documentElement.lang;
        this.dateLabel = new Intl.DateTimeFormat(locale, { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'Asia/Ho_Chi_Minh' }).format(now);
        this.timeLabel = new Intl.DateTimeFormat(locale, { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Ho_Chi_Minh' }).format(now);
    },
    destroy() {
        window.clearInterval(this.clockTimer);
        document.removeEventListener('visibilitychange', this.clockListener);
        window.removeEventListener('focus', this.clockListener);
    },
    async load() {
        this.loading = true;
        this.failed = false;
        try {
            const response = await window.axios.get(endpoint, { headers: { Accept: 'application/json' }, params: { lang: document.documentElement.lang } });
            this.dashboard = response.data.data;
            this.employee = response.data.data.employee;
            this.groups = response.data.data.groups;
        } catch {
            this.employee = null;
            this.groups = null;
            this.dashboard = null;
            this.failed = true;
        } finally {
            this.loading = false;
        }
    },
});
