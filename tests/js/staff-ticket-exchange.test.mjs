import test from 'node:test';
import assert from 'node:assert/strict';

globalThis.window = { staffTicketList: () => ({}) };
const { staffTicketExchange } = await import('../../packages/FuteBus/StaffTicketProcessing/src/resources/js/exchange.js');

async function setup() {
    globalThis.window = { staffTicketList: () => ({}), FutaNotify: { error() {} } };
    const state = staffTicketExchange('/tickets', { error: 'Failed', expired: 'Expired', consent: 'Consent' }, { csrf: 'csrf', context: '/context/__TICKET__' });
    state.scrollProgress = () => {};
    window.axios = { get: async () => ({ data: { ticket: { id: 1, departure: '2026-10-18T08:00:00+07:00', fare: 350000 }, urls: { base: '/exchange/1', hold: '/hold' } } }) };
    state.connectSearch = () => {};
    await state.chooseTicket({ id: 1, usable: true });
    state.flow.ticketId = 1;
    state.flow.selectTrip({ id: 2, fare: 350000, seats: [{ id: 3, code: 'A01', sold: false }],
        stops: { pickup: [{ id: 'pickup' }], dropoff: [{ id: 'dropoff' }] } });
    state.flow.seatId = '3';
    state.flow.pickup = 'pickup'; state.flow.dropoff = 'dropoff';
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
    assert.equal(state.step, 5);
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
    assert.equal(state.step, 5);
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
