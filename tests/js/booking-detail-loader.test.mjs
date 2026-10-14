import test from 'node:test';
import assert from 'node:assert/strict';
import { createDetailLoader } from '../../packages/FuteBus/BookingTracking/src/resources/js/detail-loader.js';

test('late responses and closed dialogs cannot replace current booking details', async () => {
    const pending = [];
    const states = [];
    const loader = createDetailLoader(() => new Promise(resolve => pending.push(resolve)), state => states.push(state));
    const first = loader.load('/first');
    const second = loader.load('/second');
    pending[1]({ ok: true, text: async () => 'second' });
    await second;
    pending[0]({ ok: true, text: async () => 'first' });
    await first;
    assert.deepEqual(states.at(-1), { state: 'ready', html: 'second' });
    const third = loader.load('/third');
    loader.cancel();
    pending[2]({ ok: true, text: async () => 'third' });
    await third;
    assert.equal(states.at(-1).state, 'loading');
});

test('login redirects and failed requests show an error and allow retry', async () => {
    const states = [];
    let attempt = 0;
    const loader = createDetailLoader(async () => ++attempt === 1
        ? { ok: true, redirected: true } : { ok: true, text: async () => 'details' }, state => states.push(state));
    await loader.load('/details');
    assert.equal(states.at(-1).state, 'error');
    await loader.load('/details');
    assert.deepEqual(states.at(-1), { state: 'ready', html: 'details' });
});
