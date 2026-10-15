import test from 'node:test';
import assert from 'node:assert/strict';
import { ticketExchangeWizard } from '../../packages/FuteBus/Payment/src/resources/js/exchange-wizard.js';

const config = {
    tickets: [{ id: 1, eligible: true, departure: '2026-12-20 22:00:00', fare: 2000 }],
    trips: 'http://localhost/doi-ve/1/chuyen', hold: '/hold', base: '/doi-ve/1',
    error: 'Operation failed', expired: 'Hold expired', consentError: 'Consent required',
    labels: { sleeper: 'Sleeper', seat: 'Seat' },
};

function setup() {
    const notices = [];
    let closed = false;
    globalThis.window = { FutaNotify: { error: message => notices.push(message) }, location: { reload() {} } };
    const wizard = ticketExchangeWizard(config);
    wizard.$el = { closest: () => ({ close() { closed = true; } }) };
    return { wizard, notices, closed: () => closed };
}

function selection(wizard) {
    wizard.ticketId = '1'; wizard.step = 1;
    wizard.selectTrip({ id: 2, seats: [{ id: 3, code: 'A01', row: 1, column: 1, deck: 'lower', sold: false }],
        stops: { pickup: [{ id: 'pickup', name: 'A' }], dropoff: [{ id: 'dropoff', name: 'B' }] } });
    wizard.seatId = '3'; wizard.pickup = 'pickup'; wizard.dropoff = 'dropoff';
}

test('selection obtains a hold before confirmation without changing the current ticket', async () => {
    const { wizard } = setup();
    selection(wizard);
    const requests = [];
    wizard.request = async (url, body) => {
        requests.push({ url, body });
        return { token: 'hold-token', expires_at: new Date(Date.now() + 300000).toISOString() };
    };
    await wizard.next();
    assert.equal(wizard.step, 2);
    assert.equal(wizard.consent, false);
    assert.equal(requests[0].url, '/hold');
    assert.equal(requests[0].body.seat_id, '3');
    assert.equal(config.tickets[0].eligible, true);
    await wizard.complete();
    assert.equal(wizard.errors.consent, 'Consent required');
    assert.equal(requests.length, 1);
});

test('closing releases only the new hold', async () => {
    const { wizard, closed } = setup();
    wizard.hold = { token: 'hold-token' };
    let destination;
    wizard.request = async url => { destination = url; };
    await wizard.close();
    assert.equal(destination, '/doi-ve/1/hold-token/bo-giu');
    assert.equal(wizard.hold, null);
    assert.equal(closed(), true);
});

test('lost completion response retains the token and permits an idempotent retry after the local timer expires', async () => {
    const { wizard } = setup();
    wizard.step = 2; wizard.hold = { token: 'same-token' }; wizard.consent = true; wizard.remaining = 30;
    let attempts = 0;
    wizard.request = async url => {
        assert.equal(url, '/doi-ve/1/same-token/xac-nhan');
        if (++attempts === 1) throw new Error('Response lost');
        return { code: 'NEW-TICKET', url: '/ticket' };
    };
    await wizard.complete();
    assert.equal(wizard.result, null);
    assert.equal(wizard.hold.token, 'same-token');
    wizard.remaining = 0;
    await wizard.complete();
    assert.equal(wizard.step, 3);
    assert.equal(wizard.result.code, 'NEW-TICKET');
    assert.equal(attempts, 2);
});

test('double confirmation sends one request while the first is in progress', async () => {
    const { wizard } = setup();
    wizard.step = 2; wizard.hold = { token: 'same-token' }; wizard.consent = true; wizard.remaining = 30;
    let resolve;
    let attempts = 0;
    wizard.request = () => { attempts += 1; return new Promise(done => { resolve = done; }); };
    const first = wizard.complete();
    await wizard.complete();
    assert.equal(attempts, 1);
    resolve({ code: 'ONE-TICKET' });
    await first;
    assert.equal(wizard.result.code, 'ONE-TICKET');
});

test('exchange trip card selection keeps one seat and opens the shared seat panel', () => {
    const { wizard } = setup();
    const trip = { id: 8, available: 2, seats: [
        { id: 11, code: 'A01', deck: 'lower', sold: false },
        { id: 12, code: 'A02', deck: 'lower', sold: false },
    ], stops: { pickup: [], dropoff: [] }, timeline: [] };

    wizard.selectTrip(trip);
    wizard.chooseSeat(trip, trip.seats[0]);
    assert.equal(wizard.openPanel, 'seats');
    assert.equal(wizard.seatId, '11');
    assert.equal(wizard.seatSelected(trip, trip.seats[0]), true);
    wizard.chooseSeat(trip, trip.seats[1]);
    assert.equal(wizard.seatId, '12');
    assert.equal(wizard.seatSelected(trip, trip.seats[0]), false);
});

test('itinerary selection allows only active compatible stops and preserves stop order', () => {
    const { wizard } = setup();
    const trip = { id: 9, available: 2, seats: [{ id: 21, code: 'B01', sold: false }],
        stops: { pickup: [{ id: '1', name: 'Start' }], dropoff: [{ id: '3', name: 'End' }] }, timeline: [
            { id: 1, type: 'pickup', active: true, expired: false },
            { id: 2, type: 'both', active: false, expired: false },
            { id: 3, type: 'both', active: true, expired: false },
            { id: 4, type: 'dropoff', active: true, expired: true },
            { id: 5, type: 'pickup', active: true, expired: false },
        ] };

    wizard.ticketId = '1';
    wizard.step = 1;
    wizard.selectTrip(trip);
    wizard.chooseSeat(trip, trip.seats[0]);
    assert.equal(wizard.stopOption(trip, 'pickup', 1), true);
    assert.equal(wizard.stopOption(trip, 'pickup', 2), false);
    assert.equal(wizard.stopOption(trip, 'pickup', 4), false);
    wizard.chooseStop(trip, 'pickup', 1);
    assert.equal(wizard.openPanel, 'schedule');
    assert.equal(wizard.canSelectStop(trip, 'dropoff', 1), false);
    wizard.chooseStop(trip, 'dropoff', 3);
    assert.equal(wizard.ready, true);
    assert.equal(wizard.canSelectStop(trip, 'pickup', 5), false);
});
