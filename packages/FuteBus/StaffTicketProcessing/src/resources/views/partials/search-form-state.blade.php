{
        roundTrip: @js(($initialSearch['trip_type'] ?? 'one_way') === 'round_trip'),
        departure: @js($initialSearch['departure'] ?? ''),
        destination: @js($initialSearch['destination'] ?? ''),
        recentSearches: [],
        locationOpen: null,
        locationQuery: '',
        highlightedLocation: 0,
        expandedArea: null,
        locationCatalog: @js($bookingLocations),
        voiceOpen: false,
        voiceListening: false,
        voiceTranscript: '',
        voicePreview: null,
        voiceError: '',
        voiceSubmitting: false,
        voiceErrors: @js(__('core::app.home.hero.voice_errors')),
        init() {
            try {
                const saved = JSON.parse(localStorage.getItem('futabus.recent-trip-searches.v1') || '[]');
                const valid = Array.isArray(saved)
                    ? saved.filter(search => search && typeof search.departure === 'string'
                        && typeof search.destination === 'string'
                        && /^\d{4}-\d{2}-\d{2}$/.test(search.departureDate))
                    : [];
                this.recentSearches = this.uniqueRecentSearches(valid);
                try {
                    localStorage.setItem('futabus.recent-trip-searches.v1', JSON.stringify(this.recentSearches));
                } catch (_) {
                    // Recent searches still work for this page when storage is unavailable.
                }
            } catch (_) {
                this.recentSearches = [];
            }
        },
        recentSearchKey(search) {
            return this.normalizeLocation(search.departure.trim()) + '|' + this.normalizeLocation(search.destination.trim());
        },
        uniqueRecentSearches(searches) {
            const seen = new Set();
            return searches.filter(search => {
                const key = this.recentSearchKey(search);
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            });
        },
        formatRecentDate(value) {
            const [year, month, day] = value.split('-');
            return day + '/' + month + '/' + year;
        },
        applyRecentSearch(search) {
            const today = @js(now()->format('Y-m-d'));
            const departureDate = search.departureDate < today ? today : search.departureDate;
            const roundTrip = !!search.roundTrip;
            const returnDate = roundTrip && search.returnDate >= departureDate
                ? search.returnDate : departureDate;
            const quantity = Number(search.quantity);
            const url = new URL(@js(route('trip-search')), window.location.href);
            url.searchParams.set('trip_type', roundTrip ? 'round_trip' : 'one_way');
            url.searchParams.set('departure', search.departure);
            url.searchParams.set('destination', search.destination);
            url.searchParams.set('departure_date', departureDate);
            if (roundTrip) url.searchParams.set('return_date', returnDate);
            const validQuantity = Number.isInteger(quantity) && quantity >= 1 && quantity <= 5 ? quantity : 1;
            url.searchParams.set('quantity', validQuantity);

            const latestSearch = { ...search, departureDate, returnDate: roundTrip ? returnDate : '', quantity: validQuantity };
            this.recentSearches = this.uniqueRecentSearches([latestSearch, ...this.recentSearches]);
            try {
                localStorage.setItem('futabus.recent-trip-searches.v1', JSON.stringify(this.recentSearches));
            } catch (_) {
                // Navigation still works if browser storage is unavailable.
            }
            window.FutaLoader?.show(0);
            window.setTimeout(() => window.location.assign(url.toString()), 30);
        },
        submitSearch(event) {
            if (!this.departure || !this.destination) {
                event.preventDefault();
                this.openLocation(!this.departure ? 'departure' : 'destination');
                return;
            }
            const form = event.currentTarget;
            const departureDate = form.elements.namedItem('departure_date').value;
            const returnDate = form.elements.namedItem('return_date').value;
            if (this.roundTrip && !returnDate) {
                event.preventDefault();
                window.dispatchEvent(new CustomEvent('hero-open-calendar', { detail: { field: 'return' } }));
                return;
            }
            const search = {
                departure: this.departure,
                destination: this.destination,
                departureDate,
                returnDate: this.roundTrip ? returnDate : '',
                roundTrip: this.roundTrip,
                quantity: Number(form.elements.namedItem('quantity').value),
            };
            this.recentSearches = this.uniqueRecentSearches([search, ...this.recentSearches]);
            try {
                localStorage.setItem('futabus.recent-trip-searches.v1', JSON.stringify(this.recentSearches));
            } catch (_) {
                // The current page still shows the searches if browser storage is unavailable.
            }
        },
        normalizeLocation(value) {
            return value.toLocaleLowerCase('vi').normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd')
                .replace(/tp\.?\s*hcm/g, 'ho chi minh');
        },
        hasLocationQuery() {
            return this.normalizeLocation(this.locationQuery.trim()).length >= 2;
        },
        visibleProvinces() {
            if (!this.hasLocationQuery()) return [];
            const query = this.normalizeLocation(this.locationQuery.trim());
            const provinces = this.locationCatalog[this.locationOpen]?.provinces || [];
            const otherField = this.locationOpen === 'departure' ? 'destination' : 'departure';
            return provinces.filter(name => name !== this[otherField]
                && this.normalizeLocation(name).includes(query));
        },
        visibleAreas() {
            if (!this.hasLocationQuery()) return [];
            const query = this.normalizeLocation(this.locationQuery.trim());
            const areas = this.locationCatalog[this.locationOpen]?.areas || [];
            return areas.filter(area => this.normalizeLocation(area.name).includes(query)
                || this.normalizeLocation(area.province).includes(query)
                || area.offices.some(office => this.normalizeLocation(office.name).includes(query)
                    || this.normalizeLocation(office.address).includes(query)));
        },
        visibleOffices() {
            if (!this.hasLocationQuery()) return [];
            const query = this.normalizeLocation(this.locationQuery.trim());
            const offices = this.locationCatalog[this.locationOpen]?.directory_offices || [];
            return offices.filter(office => this.normalizeLocation(office.name).includes(query)
                || this.normalizeLocation(office.address).includes(query));
        },
        locationRowCount() {
            return this.visibleProvinces().length + this.visibleAreas().length + this.visibleOffices().length;
        },
        onLocationQueryChange() {
            this.highlightedLocation = 0;
            this.expandedArea = this.visibleAreas().find(area => area.offices.length)?.name || null;
        },
        openLocation(field) {
            this.locationOpen = field;
            this.locationQuery = this[field] || '';
            this.onLocationQueryChange();
            this.$nextTick(() => this.$refs[field + 'Search'].focus({ preventScroll: true }));
        },
        chooseLocation(location) {
            if (!location || !this.locationOpen) return;
            this[this.locationOpen] = location;
            const field = this.locationOpen;
            this.locationOpen = null;
            if (field === 'departure') {
                setTimeout(() => this.openLocation('destination'), 0);
            } else {
                setTimeout(() => window.dispatchEvent(new CustomEvent('hero-open-calendar', {
                    detail: { field: 'departure' },
                })), 0);
            }
        },
        toggleLocationArea(area) {
            if (!area.offices.length) {
                this.chooseLocation(area.name);
                return;
            }
            this.expandedArea = this.expandedArea === area.name ? null : area.name;
        },
        moveLocationHighlight(direction) {
            const count = this.locationRowCount();
            if (count) this.highlightedLocation = (this.highlightedLocation + direction + count) % count;
        },
        activateHighlightedLocation() {
            const provinces = this.visibleProvinces();
            if (this.highlightedLocation < provinces.length) {
                this.chooseLocation(provinces[this.highlightedLocation]);
                return;
            }
            const area = this.visibleAreas()[this.highlightedLocation - provinces.length];
            if (area) {
                this.toggleLocationArea(area);
                return;
            }
            const office = this.visibleOffices()[this.highlightedLocation - provinces.length - this.visibleAreas().length];
            if (office) this.chooseLocation(office.name);
        },
        swapLocations() {
            [this.departure, this.destination] = [this.destination, this.departure];
            this.locationOpen = null;
        },
        startVoiceSearch() {
            this.voiceOpen = true;
            this.voiceError = '';
            if (!this.voiceListening) {
                this.voiceTranscript = '';
                this.voicePreview = null;
                this.voiceSubmitting = false;
            }
            window.FutaVoiceSearch?.start();
        },
        interpretVoice() {
            this.voicePreview = null;
            if (!this.voiceTranscript.trim()) return;
            this.voicePreview = window.FutaVoiceSearch?.parse(
                this.voiceTranscript, this.locationCatalog, @js(now()->format('Y-m-d')),
            ) || null;
            this.voiceError = this.voicePreview?.error
                ? (this.voiceErrors[this.voicePreview.error] || this.voiceErrors.default) : '';
        },
        applyVoiceSearch() {
            if (!this.voicePreview || this.voicePreview.error || this.voiceSubmitting) return;
            this.voiceSubmitting = true;
            const result = this.voicePreview;
            this.departure = result.departure;
            this.destination = result.destination;
            this.roundTrip = result.roundTrip;
            this.locationOpen = null;
            window.FutaVoiceSearch?.stop();
            this.$nextTick(() => {
                const departureDate = result.departureDate
                    || this.$el.querySelector('[name=departure_date]')?.value;
                window.dispatchEvent(new CustomEvent('hero-restore-dates', {
                    detail: { departureDate, returnDate: result.roundTrip ? result.returnDate : '' },
                }));
                if (result.quantity) window.dispatchEvent(new CustomEvent('hero-restore-quantity', {
                    detail: { quantity: result.quantity },
                }));
                const submitWhenReady = (attempts = 0) => {
                    const form = this.$el.querySelector('#trip-search');
                    const dateReady = form.elements.namedItem('departure_date').value === departureDate
                        && (!result.roundTrip || form.elements.namedItem('return_date').value === result.returnDate);
                    const quantityReady = !result.quantity
                        || Number(form.elements.namedItem('quantity').value) === result.quantity;
                    if (dateReady && quantityReady) {
                        this.voiceOpen = false;
                        form.requestSubmit();
                    } else if (attempts < 3) {
                        window.requestAnimationFrame(() => submitWhenReady(attempts + 1));
                    } else {
                        this.voiceSubmitting = false;
                        this.voiceError = this.voiceErrors.default;
                    }
                };
                this.$nextTick(() => window.requestAnimationFrame(() => submitWhenReady()));
            });
        },
    }
