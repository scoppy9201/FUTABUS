import "../css/app.css";
import { phoneCountries, parseCustomerPhone } from "./phone.js";
import { countryFlag } from "./country-flags.js";
import { createPopup } from "../../../../Core/src/resources/js/ui-popup.js";

window.futaAiChat = (config) => ({
    init() {
        document
            .querySelectorAll(
                'a.fixed img[src$="/images/support/customer-support-agent.png"]',
            )
            .forEach((image) => image.closest("a").remove());
        this.$nextTick(() => {
            if (!this.$refs.countryPanel) return;
            this.countryPopup = createPopup(
                this.$refs.countryPanel,
                this.$refs.countryTrigger,
                {
                    matchWidth: false,
                    onOpen: () => {
                        this.countrySearch = "";
                        this.countryOpen = true;
                        this.$nextTick(() => {
                            this.$refs.countrySearch.focus();
                            this.countryPopup.position();
                        });
                    },
                    onClose: () => {
                        this.countryOpen = false;
                    },
                },
            );
            this.$watch("open", (value) => {
                if (!value) this.countryPopup.close();
            });
        });
    },
    open: false,
    loading: false,
    started: config.collectCustomer === false,
    phoneCountry: "VN",
    countrySearch: "",
    countryOpen: false,
    countryPopup: null,
    flagFor(code) {
        return countryFlag(code);
    },
    get filteredCountries() {
        const normalize = (value) =>
            value
                .normalize("NFD")
                .replace(/\p{Diacritic}/gu, "")
                .toLowerCase();
        const query = normalize(this.countrySearch.trim());
        return this.countries.filter((country) =>
            normalize(
                `${country.name} ${country.englishName} ${country.code} ${country.prefix}`,
            ).includes(query),
        );
    },
    toggleCountries() {
        this.$refs.countryPanel.style.width = `${this.$refs.phoneRow.getBoundingClientRect().width}px`;
        this.countryPopup.toggle();
    },
    chooseCountry(code) {
        if (!this.countries.some((country) => country.code === code)) return;
        this.phoneCountry = code;
        this.formatPhone();
        this.countryPopup?.close(true);
    },
    focusCountry(index) {
        const options =
            this.$refs.countryList.querySelectorAll("[role=option]");
        if (options.length)
            options[(index + options.length) % options.length].focus();
    },
    destroy() {
        this.countryPopup?.destroy();
    },
    countries: phoneCountries(
        document.documentElement.lang === "en" ? "en" : "vi",
    ),
    errors: {},
    get selectedCountry() {
        return this.countries.find(
            (country) => country.code === this.phoneCountry,
        );
    },
    get internationalPhone() {
        return (
            parseCustomerPhone(this.customer.phone, this.phoneCountry)
                ?.number || ""
        );
    },
    get validPhone() {
        return Boolean(this.internationalPhone);
    },

    formatPhone() {
        const phone = parseCustomerPhone(
            this.customer.phone,
            this.phoneCountry,
        );
        if (phone) this.customer.phone = phone.formatNational();
        this.validateField("phone");
    },

    customer: {
        salutation: "",
        name: "",
        email: "",
        phone: "",
        topic: "",
        content: "",
    },

    validateField(field) {
        if (!["name", "phone", "email", "topic", "content"].includes(field))
            return;
        delete this.errors[field];
        const value = this.customer[field]?.trim() || "";
        if (["name", "topic", "content"].includes(field) && !value) {
            this.errors[field] = this.copy.required_field;
        } else if (field === "phone" && !this.validPhone) {
            this.errors.phone =
                value.replace(/\D/g, "").length < 7
                    ? this.copy.phone_incomplete
                    : this.copy.phone_invalid;
        } else if (
            field === "email" &&
            value &&
            !this.$refs.email.checkValidity()
        ) {
            this.errors.email = this.copy.email_invalid;
        }
    },

    validateIntake() {
        this.errors = {};
        for (const field of ["name", "phone", "email", "topic", "content"])
            this.validateField(field);
        if (Object.values(this.errors).some(Boolean)) {
            this.$nextTick(() =>
                this.$refs.intake
                    .querySelector('[aria-invalid="true"]')
                    ?.focus(),
            );
            return false;
        }
        return this.$refs.intake.checkValidity();
    },

    startChat() {
        this.customer.name = this.customer.name.trim();
        this.customer.email = this.customer.email.trim();
        this.customer.content = this.customer.content.trim();
        if (this.loading || !this.validateIntake()) return;
        this.countryPopup?.close();
        this.started = true;
        this.draft = this.customer.content;
        return this.send(true);
    },
    draft: "",
    sessionId: null,
    locale: document.documentElement.lang === "en" ? "en" : "vi",
    messages: [{ role: "assistant", text: config.copy.greeting }],
    get copy() {
        return config.copy;
    },

    formatMetric(metric) {
        if (metric.format === "currency") {
            return new Intl.NumberFormat(this.locale, {
                style: "currency",
                currency: "VND",
                maximumFractionDigits: 0,
            }).format(metric.value);
        }
        if (metric.format === "percent")
            return `${Number(metric.value).toFixed(1)}%`;
        return new Intl.NumberFormat(this.locale).format(metric.value);
    },

    async confirmNavigation(action) {
        if (!action?.url || !window.FutaConfirm) return;
        let url;
        try {
            url = new URL(action.url, window.location.origin);
        } catch {
            return;
        }
        if (
            url.origin !== window.location.origin ||
            !["http:", "https:"].includes(url.protocol)
        )
            return;
        const confirmed = await window.FutaConfirm({
            title: this.copy.confirm_title,
            message: action.description || this.copy.confirm_open,
            label: this.copy.confirm_continue,
        });
        if (confirmed) window.location.assign(url.href);
    },

    confirmTrip(trip) {
        return this.confirmNavigation({
            url: trip.url,
            description: [
                `${trip.origin} → ${trip.destination}`,
                `${this.copy.departure}: ${trip.departure}`,
                `${this.copy.vehicle}: ${trip.type}`,
                new Intl.NumberFormat(this.locale, {
                    style: "currency", currency: "VND", maximumFractionDigits: 0,
                }).format(trip.price),
                this.copy.booking_confirmation,
            ].join("\n"),
        });
    },

    async send(starting = false) {
        const message = this.draft.trim();
        if (!this.started || !message || this.loading) return;

        this.messages.push({ role: "user", text: message });
        this.draft = "";
        this.loading = true;
        this.$nextTick(
            () =>
                (this.$refs.messages.scrollTop =
                    this.$refs.messages.scrollHeight),
        );

        try {
            const response = await fetch(config.endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": config.csrf,
                },
                body: JSON.stringify({
                    message,
                    session_id: this.sessionId,
                    ...(config.collectCustomer === false
                        ? {}
                        : {
                              customer: {
                                  ...this.customer,
                                  phone: this.internationalPhone || null,
                                  phone_country: this.phoneCountry,
                              },
                          }),
                }),
            });

            const result = await response.json();
            if (!response.ok) {
                if (result.errors)
                    this.errors = Object.fromEntries(
                        Object.entries(result.errors).map(([key, errors]) => [
                            key.replace("customer.", ""),
                            errors[0],
                        ]),
                    );
                throw new Error(result.message || this.copy.error);
            }

            // Persist server-issued session_id for subsequent turns
            if (result.session_id) this.sessionId = result.session_id;

            this.messages.push({
                role: "assistant",
                ...result,
                text: result.message,
            });
        } catch (error) {
            if (starting) {
                this.started = false;
                this.messages = [
                    { role: "assistant", text: this.copy.greeting },
                ];
            }
            this.messages.push({
                role: "assistant",
                text: error.message || this.copy.error,
            });
            window.FutaNotify?.error(error.message || this.copy.error);
        } finally {
            this.loading = false;
            this.$nextTick(
                () =>
                    (this.$refs.messages.scrollTop =
                        this.$refs.messages.scrollHeight),
            );
        }
    },
});
