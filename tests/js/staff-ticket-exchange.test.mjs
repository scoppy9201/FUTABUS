import test from 'node:test';
import assert from 'node:assert/strict';

globalThis.window = { staffTicketList: () => ({}) };
const { staffTicketExchange } = await import('../../packages/FuteBus/StaffTicketProcessing/src/resources/js/exchange.js');

async function setup() {
    globalThis.window = { staffTicketList: () => ({}), FutaNotify: { error() {} } };
    const state = staffTicketExchange('/tickets', { error: 'Failed', expired: 'Expired', consent: 'Consent' }, { csrf: 'csrf', context: '/context/__TICKET__' });
    state.scrollProgress = () => {};
    window.axios = { get: async () => ({ data: { ticket: { id: 1, departure: '2026-10-18T08:00:00+07:00', fare: 350000 }, urls: { base: '/exchange/1', hold: '/hold' } } }) };
    state.searchTrips = async () => {};
    await state.chooseTicket({ id: 1, usable: true });
    state.flow.ticketId = 1;
    state.flow.selectTrip({ id: 2, fare: 350000, seats: [{ id: 3, code: 'A01', sold: false }],
        stops: { pickup: [{ id: 'pickup' }], dropoff: [{ id: 'dropoff' }] } });
    state.flow.seatId = '3';
    state.flow.pickup = 'pickup'; state.flow.dropoff = 'dropoff';
    state.$refs = { passengerForm: { reportValidity: () => true } };
    state.passengerName = 'Updated Passenger'; state.passengerPhone = '0912345678';
    state.step = 3;
    return state;
}

test('staff remains on seat selection when the hold is rejected', async () => {
    const state = await setup();
    state.flow.request = async () => { throw new Error('Seat taken'); };
    await state.holdSeat();
    assert.equal(state.step, 3);
    assert.equal(state.flow.hold, null);
});

test('staff payment review requires a server hold and completion requires a server result', async () => {
    const state = await setup();
    state.flow.request = async () => ({ token: 'hold', expires_at: new Date(Date.now() + 300000).toISOString() });
    await state.holdSeat();
    assert.equal(state.step, 4);
    assert.equal(state.flow.result, null);
    state.flow.consent = true;
    state.flow.request = async () => ({ code: 'REPLACEMENT', qr: 'verified-qr' });
    await state.completeExchange();
    assert.equal(state.step, 4);
    assert.equal(state.flow.result.code, 'REPLACEMENT');
});

test('lost completion response keeps the token for retry and blocks navigating back', async () => {
    const state = await setup();
    state.step = 4; state.flow.hold = { token: 'same-token' }; state.flow.remaining = 20; state.flow.consent = true;
    state.flow.request = async () => { throw new Error('Lost response'); };
    await state.completeExchange();
    await state.back();
    assert.equal(state.step, 4);
    assert.equal(state.flow.hold.token, 'same-token');
    state.flow.remaining = 0;
    state.flow.request = async url => { assert.ok(url.includes('same-token')); return { code: 'RETRY-RESULT' }; };
    await state.completeExchange();
    assert.equal(state.step, 4);
});

test('back from payment releases the new hold before allowing seat changes', async () => {
    const state = await setup();
    state.step = 4; state.flow.hold = { token: 'hold' };
    const requests = [];
    state.flow.request = async url => { requests.push(url); };
    await state.back();
    assert.equal(state.step, 3);
    assert.equal(state.flow.hold, null);
    assert.ok(requests[0].endsWith('/hold/bo-giu'));
});

test('an unavailable or unlisted trip cannot enter staff seat selection', async () => {
    const state = await setup(); state.step = 2;
    state.trips = [{ id: 2, available: 0 }];
    state.chooseTrip({ trip: { id: 2 }, seats: [] });
    state.chooseTrip({ trip: { id: 99 }, seats: [] });
    assert.equal(state.step, 2);
});


test('edited passenger is sent to the staff hold and review uses the server snapshot', async () => {
    const state = await setup();
    let payload;
    state.passengerName = '  New Name  ';
    state.flow.request = async (url, body) => {
        assert.equal(url, '/hold'); payload = body;
        return { token: 'hold', expires_at: new Date(Date.now() + 300000).toISOString(),
            passenger: { passenger_name: body.passenger_name, passenger_phone: body.passenger_phone } };
    };
    await state.holdSeat();
    assert.equal(payload.passenger_name, 'New Name');
    assert.equal(payload.passenger_phone, '0912345678');
    assert.equal(state.confirmedPassenger.passenger_name, 'New Name');
    assert.equal(state.step, 4);
});

test('lost hold response retries identical passenger and seat choices before allowing edits', async () => {
    const state = await setup();
    const payloads = [];
    state.flow.request = async (url, body) => {
        payloads.push(structuredClone(body));
        if (payloads.length === 1) throw new Error('Network lost');
        return { token: 'recovered', expires_at: new Date(Date.now() + 300000).toISOString(), passenger: body };
    };
    await state.holdSeat();
    assert.ok(state.pendingHold);
    await state.back();
    assert.equal(state.step, 3);
    state.passengerName = 'Changed after timeout';
    await state.holdSeat();
    assert.deepEqual(payloads[0], payloads[1]);
    assert.equal(state.pendingHold, null);
    assert.equal(state.step, 4);
});

test('server validation errors allow correcting passenger details without advancing', async () => {
    const state = await setup();
    state.flow.request = async () => { const error = new Error('Invalid phone'); error.status = 422; throw error; };
    await state.holdSeat();
    assert.equal(state.pendingHold, null);
    assert.equal(state.step, 3);
    assert.equal(state.flow.hold, null);
});

test('staff seat grid derives columns and decks from the actual trip', async () => {
    const state = await setup();
    state.flow.selectedTrip.seats = [{ id: 3, column: 1, deck: 'lower' }, { id: 4, column: 3, deck: 'upper' }];
    assert.deepEqual(state.seatDecks, ['lower', 'upper']);
    assert.equal(state.seatColumns, 3);
});


test('a definitive confirmation rejection allows releasing the hold and choosing again', async () => {
    const state = await setup();
    state.step = 4; state.flow.hold = { token: 'hold' }; state.flow.remaining = 20; state.flow.consent = true;
    state.flow.request = async () => { const error = new Error('Ticket changed'); error.status = 422; throw error; };
    await state.completeExchange();
    assert.equal(state.step, 4);
    assert.equal(state.flow.completionAttempted, false);
    state.flow.request = async () => null;
    await state.back();
    assert.equal(state.step, 3);
    assert.equal(state.flow.hold, null);
});


test('choosing a ticket updates the original fare and switching tickets replaces it', async () => {
    const state = await setup();
    assert.equal(state.flow.ticket.fare, 350000);
    window.axios.get = async () => ({ data: { ticket: { id: 8, fare: 2000, departure: '2026-10-26T08:00:00+07:00' }, urls: {} } });
    await state.chooseTicket({ id: 8, usable: true });
    assert.equal(state.flow.ticket.id, 8);
    assert.equal(state.flow.ticket.fare, 2000);
});

test('upper deck rows start at the top while preserving relative row gaps', async () => {
    const state = await setup();
    state.flow.selectedTrip.seats = [{ id: 1, deck: 'lower', row: 1 }, { id: 2, deck: 'upper', row: 7 }, { id: 3, deck: 'upper', row: 9 }];
    assert.equal(state.seatRow(state.flow.selectedTrip.seats[1]), 1);
    assert.equal(state.seatRow(state.flow.selectedTrip.seats[2]), 3);
});


test('a server rejection displays one specific error and keeps staff on the current step', async () => {
    const state = await setup();
    const messages = [];
    window.FutaNotify = { error: message => messages.push(message) };
    const previousFetch = globalThis.fetch;
    globalThis.fetch = async () => ({ status: 422, ok: false, json: async () => ({ message: 'Specific server error' }) });
    try {
        await state.flow.run(() => state.flow.request('/hold', { ticket_id: 1 }));
        assert.deepEqual(messages, ['Specific server error']);
        assert.equal(state.flow.errors.ticket, 'Specific server error');
        assert.equal(state.step, 3);
        assert.equal(state.flow.hold, null);
    } finally {
        globalThis.fetch = previousFetch;
    }
});
