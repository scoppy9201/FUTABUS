{
        trips: @js($trips),
        outboundTrips: @js($trips),
        returnTrips: @js($returnTrips),
        roundTrip: @js($roundTrip),
        direction: 'outbound',
        selectedOutboundTrip: null,
        changeDirection(value) {
            if (this.direction === value) return;
            if (value === 'return' && !this.selectedOutboundTrip) {
                this.showSeatNotice(@js(__('core::trip-search.select_outbound_first')));
                return;
            }
            this.direction = value;
            this.trips = value === 'return' ? this.returnTrips : this.outboundTrips;
            this.openTripId = null;
            this.openPanel = null;
            this.resetFilters();
        },
        timeFilters: [],
        vehicleFilters: [],
        rowFilters: [],
        deckFilters: [],
        sortBy: 'departure',
        sortHighlights: { price: true, departure: true, seats: false },
        openTripId: null,
        openPanel: null,
        selectedSeatIds: {},
        selectTrip(trip) {
            if (!this.roundTrip) {
                this.openBooking(trip);
                return;
            }
            if (this.direction === 'outbound') {
                this.selectedOutboundTrip = trip;
                this.changeDirection('return');
                this.$nextTick(() => this.$refs.directionTabs?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
                return;
            }
            if (trip.departure_time <= this.selectedOutboundTrip.arrival_time) {
                this.showSeatNotice(@js(__('core::booking.invalid_round_trip')));
                return;
            }
            this.openBooking(trip);
        },
        openBooking(trip) {
            this.$dispatch('staff-trip-selected', { trip, seats: this.selectedSeats(trip).map(seat => seat.id), direction: this.direction, outboundTrip: this.selectedOutboundTrip, outboundSeats: this.selectedOutboundTrip ? this.selectedSeats(this.selectedOutboundTrip).map(seat => seat.id) : [] });
        },
        selectedSeats(trip) {
            const ids = this.selectedSeatIds[trip.id] ?? trip.demo_selected_seat_ids ?? [];
            return trip.seats.filter(seat => ids.includes(seat.id));
        },
        seatSelected(trip, seat) {
            return this.selectedSeats(trip).some(selected => selected.id === seat.id);
        },
        showSeatNotice(message) {
            window.FutaNotify?.show(message, { tone: 'warning' });
        },
        toggleSeat(trip, seat) {
            if (seat.sold) return;
            const current = this.selectedSeats(trip).map(item => item.id);
            if (!current.includes(seat.id) && current.length >= 5) {
                this.showSeatNotice(@js(__('core::trip-search.seat_limit_notice')));
                return;
            }
            this.selectedSeatIds[trip.id] = current.includes(seat.id)
                ? current.filter(id => id !== seat.id)
                : [...current, seat.id];
        },
        deckSeats(trip, deck) {
            return trip.seats.filter(seat => seat.deck === deck);
        },
        togglePanel(tripId, panel) {
            if (this.openTripId === tripId && this.openPanel === panel) {
                this.openTripId = null;
                this.openPanel = null;
                return;
            }
            this.openTripId = tripId;
            this.openPanel = panel;
            this.$nextTick(() => {
                const detail = this.$el.querySelector('.trip-card.is-expanded .trip-card__detail');
                if (detail) detail.scrollTop = 0;
            });
        },
        toggleSort(sort) {
            this.sortHighlights[sort] = !this.sortHighlights[sort];
            if (this.sortHighlights[sort]) {
                this.sortBy = sort;
            } else if (this.sortBy === sort) {
                this.sortBy = ['departure', 'price', 'seats'].find(key => this.sortHighlights[key]) ?? null;
            }
        },
        toggleFilter(group, value) {
            this[group] = this[group].includes(value)
                ? this[group].filter(item => item !== value)
                : [...this[group], value];
        },
        resetFilters() {
            this.timeFilters = [];
            this.vehicleFilters = [];
            this.rowFilters = [];
            this.deckFilters = [];
        },
        hasOption(field, value) {
            return this.trips.some(trip => trip[field].includes(value));
        },
        timeSlot(hour) {
            const time = Number(hour.slice(0, 2));
            return time < 6 ? 'early' : time < 12 ? 'morning' : time < 18 ? 'afternoon' : 'evening';
        },
        vehicleGroup(type) {
            return type === 'standard' || type === 'minivan' ? 'seat' : type;
        },
        visibleTrips() {
            const filtered = this.trips.filter(trip =>
                (!this.timeFilters.length || this.timeFilters.includes(this.timeSlot(trip.departure_hour)))
                && (!this.vehicleFilters.length || this.vehicleFilters.includes(this.vehicleGroup(trip.vehicle_type)))
                && (!this.rowFilters.length || this.rowFilters.some(row => trip.row_options.includes(row)))
                && (!this.deckFilters.length || this.deckFilters.some(deck => trip.deck_options.includes(deck)))
            );
            if (!this.sortBy) return filtered;
            return filtered.sort((a, b) => {
                if (this.sortBy === 'price') return a.price - b.price || a.departure_time.localeCompare(b.departure_time);
                if (this.sortBy === 'seats') return b.available_seats - a.available_seats || a.departure_time.localeCompare(b.departure_time);
                return a.departure_time.localeCompare(b.departure_time);
            });
        },
        duration(minutes) {
            const hours = Math.floor(minutes / 60);
            const remaining = Math.round(minutes % 60);
            return hours + ' {{ __('core::trip-search.hours') }}' + (remaining ? ' ' + remaining + ' {{ __('core::trip-search.minutes') }}' : '');
        },
        vehicleLabel(type) {
            return type === 'limousine' ? 'Limousine'
                : type === 'sleeper' ? @js(__('core::trip-search.sleeper'))
                : @js(__('core::trip-search.seat'));
        },
        money(value) {
            return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
        },
        clockDuration(minutes) {
            return String(Math.floor(minutes / 60)).padStart(2, '0') + ':'
                + String(Math.round(minutes % 60)).padStart(2, '0') + ' h';
        },
        stationLabel(value) {
            return value.replace(/^Bến xe\s+/i, '');
        },
        activeTrip() {
            return this.openPanel ? this.trips.find(trip => trip.id === this.openTripId) ?? null : null;
        },
        summaryTrip() {
            return this.roundTrip && this.selectedOutboundTrip ? this.selectedOutboundTrip : this.activeTrip();
        },
        tripDate(value) {
            const date = new Date(value.replace(' ', 'T'));
            const weekdays = @js(app()->getLocale() === 'vi' ? ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'] : ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
            return weekdays[date.getDay()] + ', '
                + String(date.getDate()).padStart(2, '0') + '/'
                + String(date.getMonth() + 1).padStart(2, '0') + '/'
                + date.getFullYear();
        },
    }
