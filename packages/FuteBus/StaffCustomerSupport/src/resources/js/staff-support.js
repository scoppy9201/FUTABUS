window.staffSupport = (urls, labels, initialReport = null) => ({
    fieldErrors: {},
    rows: [], detail: null, meta: { current_page: 1, last_page: 1, total: 0 },
    filters: { search: '', status: '', category: '', urgency: '', queue: 'all' },
    note: '', transferReason: '', departmentRole: '', pendingActions: {}, response: '', responseStatus: 'reviewing', loading: false, loadingDetail: false,
    failed: false, detailFailed: false, busy: false, pendingRequest: null,
    listSequence: 0, detailSequence: 0, supportLabels: labels,
    init() {
        this.loadList();
        if (initialReport) this.loadDetail(initialReport);
    },
    async loadList(page = 1) {
        const sequence = ++this.listSequence;
        this.loading = true;
        this.failed = false;
        try {
            const { data } = await window.axios.get(urls.list, {
                params: { ...this.filters, page, lang: document.documentElement.lang },
            });
            if (sequence !== this.listSequence) return;
            this.rows = data.data;
            this.meta = data.meta;
        } catch (error) {
            if (sequence !== this.listSequence) return;
            this.rows = [];
            this.failed = true;
            this.notifyError(error);
        } finally {
            if (sequence === this.listSequence) this.loading = false;
        }
    },
    async loadDetail(report) {
        const sequence = ++this.detailSequence;
        this.loadingDetail = true;
        this.detailFailed = false;
        try {
            const { data } = await window.axios.get(urls.detail.replace('__report__', report), {
                params: { lang: document.documentElement.lang },
            });
            if (sequence !== this.detailSequence) return;
            this.detail = data.data;
        } catch (error) {
            if (sequence !== this.detailSequence) return;
            this.detail = null;
            this.detailFailed = true;
            this.notifyError(error);
        } finally {
            if (sequence === this.detailSequence) this.loadingDetail = false;
        }
    },
    async claim() {
        await this.mutate(() => window.axios.put(urls.claim.replace('__report__', this.detail.id), {
            version: this.detail.version,
        }, { headers: { 'X-CSRF-TOKEN': urls.csrf } }));
    },
    async respond() {
        if (!this.detail || this.busy) return;
        if (!this.validateFields(['response'])) return;
        if (window.FutaConfirm && !await window.FutaConfirm({ title: labels.confirm_title, message: this.responseStatus === 'resolved' ? labels.confirm_resolve : labels.confirm_send, label: labels.confirm_label })) return;
        const content = JSON.stringify([this.detail.id, this.responseStatus, this.response.trim()]);
        if (!this.pendingRequest || this.pendingRequest.content !== content) {
            this.pendingRequest = { content, key: window.crypto.randomUUID() };
        }
        await this.mutate(async () => {
            await window.axios.post(urls.respond.replace('__report__', this.detail.id), {
                version: this.detail.version, status: this.responseStatus, response: this.response.trim(),
                idempotency_key: this.pendingRequest.key,
            }, { headers: { 'X-CSRF-TOKEN': urls.csrf } });
            this.pendingRequest = null;
            this.response = '';
        });
    },
    async addAction(kind) {
        if (!this.detail || this.busy) return;
        const text = (kind === 'transfer' ? this.transferReason : this.note).trim();
        if (!this.validateFields(kind === 'transfer' ? ['departmentRole', 'transferReason'] : ['note'])) return;
        if (window.FutaConfirm && !await window.FutaConfirm({ title: labels.confirm_title, message: kind === 'transfer' ? labels.confirm_transfer : labels.confirm_note, label: labels.confirm_label })) return;
        const content = JSON.stringify([this.detail.id, kind, text, this.departmentRole]);
        if (!this.pendingActions[kind] || this.pendingActions[kind].content !== content) {
            this.pendingActions[kind] = { content, key: window.crypto.randomUUID() };
        }
        await this.mutate(async () => {
            await window.axios.post(urls[kind].replace('__report__', this.detail.id), {
                version: this.detail.version, content: text, idempotency_key: this.pendingActions[kind].key,
                ...(kind === 'transfer' ? { department_role_id: this.departmentRole } : {}),
            }, { headers: { 'X-CSRF-TOKEN': urls.csrf } });
            delete this.pendingActions[kind];
            if (kind === 'transfer') { this.transferReason = ''; this.departmentRole = ''; }
            else this.note = '';
        });
    },
    validateFields(fields) {
        for (const field of fields) {
            const value = String(this[field] || '').trim();
            this.fieldErrors[field] = !value ? labels[field + '_required'] :
                (field !== 'departmentRole' && [...value].length > 3000 ? labels.too_long : '');
        }
        const invalid = fields.find(field => this.fieldErrors[field]);
        if (!invalid) return true;
        this.$refs?.[invalid]?.focus();
        window.FutaNotify?.show(this.fieldErrors[invalid], { tone: 'error', duration: 4000 });
        return false;
    },
    async mutate(action) {
        if (!this.detail || this.busy) return;
        this.busy = true;
        const id = this.detail.id;
        try {
            await action();
            await Promise.all([this.loadDetail(id), this.loadList(this.meta.current_page)]);
            window.FutaNotify?.success(labels.saved, { duration: 4000 });
        } catch (error) {
            this.notifyError(error);
            if (error.response?.status === 409) await this.loadDetail(id);
        } finally {
            this.busy = false;
        }
    },
    notifyError(error) {
        const errors = Object.values(error.response?.data?.errors || {}).flat();
        const message = error.response?.status === 409 ? error.response.data.message : labels.error;
        (errors.length ? errors : [message]).forEach(text => window.FutaNotify?.show(text, { tone: 'error', duration: 5000 }));
    },
    date(value) {
        return new Intl.DateTimeFormat(document.documentElement.lang, {
            dateStyle: 'short', timeStyle: 'short', timeZone: 'Asia/Ho_Chi_Minh',
        }).format(new Date(value));
    },
});
