window.staffProfile = (endpoints, messages) => ({
    employee: null,
    dashboard: null,
    sidebarOpen: false,
    profile: {},
    loading: true,
    saving: false,
    errors: {},
    avatarPreview: null,
    message: '',
    validate(field) {
        const value = String(this.profile[field] || '');
        const rules = messages.validation;
        let error = null;
        if (field === 'name') error = !value.trim() ? rules.name_required : value.length < 2 || value.length > 100 ? rules.name_length : null;
        if (field === 'phone') error = !value.trim() ? rules.phone_required : !/^(?:0|\+84)[35789]\d{8}$/.test(value.replace(/\s+/g, '')) ? rules.phone_invalid : null;
        if (field === 'date_of_birth' && value) {
            const today = new Date();
            const localToday = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
            error = value > localToday ? rules.birth_date_future : null;
        }
        if (field === 'address' && value.length > 255) error = rules.text_too_long;
        if (error) this.errors[field] = [error];
        else delete this.errors[field];
    },
    previewAvatar() {
        if (this.avatarPreview) URL.revokeObjectURL(this.avatarPreview);
        this.avatarPreview = null;
        delete this.errors.avatar;
        const file = this.$refs.avatar.files[0];
        if (!file) return;
        if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 1024 * 1024) {
            this.errors.avatar = [file.size > 1024 * 1024 ? messages.validation.avatar_large : messages.validation.avatar_invalid];
            this.$refs.avatar.value = '';
            return;
        }
        this.avatarPreview = URL.createObjectURL(file);
    },
    destroy() {
        if (this.avatarPreview) URL.revokeObjectURL(this.avatarPreview);
    },
    async init() {
        try {
            const { data } = await window.axios.get(endpoints.show, { params: { lang: document.documentElement.lang } });
            this.profile = data.data;
            this.employee = { ...data.data, avatar_url: data.data.has_avatar ? endpoints.avatar : null };
            const navigation = await window.axios.get(endpoints.dashboard, { params: { lang: document.documentElement.lang } }).catch(() => null);
            this.dashboard = navigation?.data.data || null;
        } catch {
            this.message = messages.error;
            window.FutaNotify.error(this.message, { duration: 4000 });
        } finally {
            this.loading = false;
        }
    },
    async save() {
        if (this.saving || !this.employee) return;
        ['name', 'phone', 'date_of_birth', 'address'].forEach(field => this.validate(field));
        if (Object.keys(this.errors).length) return;
        this.saving = true;
        this.errors = {};
        this.message = '';
        const body = new FormData(this.$refs.form);
        body.set('_method', 'PUT');
        try {
            const { data } = await window.axios.post(endpoints.update, body, {
                params: { lang: document.documentElement.lang },
                headers: { 'X-CSRF-TOKEN': endpoints.csrf, Accept: 'application/json' },
            });
            this.profile = data.data;
            this.employee = { ...data.data, avatar_url: data.data.has_avatar ? `${endpoints.avatar}?v=${Date.now()}` : null };
            this.$dispatch('staff-profile-saved', { name: this.employee.name, avatar_url: this.employee.avatar_url });
            this.$refs.avatar.value = '';
            if (this.avatarPreview) URL.revokeObjectURL(this.avatarPreview);
            this.avatarPreview = null;
            this.message = messages.updated;
            window.FutaNotify.success(messages.updated, { duration: 4000 });
        } catch (error) {
            this.errors = error.response?.data?.errors || {};
            if (error.response?.status !== 422) {
                const status = error.response?.status;
                this.message = status === 401 || status === 419 ? messages.sessionExpired
                    : status === 403 ? messages.forbidden
                    : status === 413 ? messages.uploadTooLarge
                    : !error.response ? messages.networkError : messages.saveError;
                this.message ||= messages.error;
                window.FutaNotify.error(this.message, { duration: 4000 });
            }
        } finally {
            this.saving = false;
        }
    },
});
