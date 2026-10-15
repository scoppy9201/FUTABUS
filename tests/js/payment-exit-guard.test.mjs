import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';

const source = await readFile(new URL('../../packages/FuteBus/Payment/src/resources/js/payment-exit-guard.js', import.meta.url), 'utf8');

function setup(decision) {
    const events = new Map();
    let submit;
    let submissions = 0;
    let confirmations = 0;
    const consent = { value: '0' };
    const button = { disabled: true };
    const form = {
        dataset: { confirmTitle: 'Exit', confirmMessage: 'Release the hold' },
        elements: { namedItem: () => consent },
        querySelector: () => button,
        addEventListener(name, callback) { if (name === 'submit') submit = callback; },
        requestSubmit() {
            const event = { prevented: false, preventDefault() { this.prevented = true; } };
            const promise = submit(event);
            if (!event.prevented) submissions += 1;
            return promise;
        },
    };
    const pushes = [];
    const window = {
        location: { href: 'http://localhost/thanh-toan/draft' },
        history: { pushState(...args) { pushes.push(args); } },
        addEventListener(name, callback) { events.set(name, callback); },
        async FutaConfirm() { confirmations += 1; return decision; },
    };
    vm.runInNewContext(source, { window, document: { querySelector: () => ({ querySelector: () => form }) } });
    return { events, form, pushes, consent, button, submissions: () => submissions, confirmations: () => confirmations };
}

test('declining exit does not submit cancellation and retains the leave warning', async () => {
    const state = setup(false);
    await state.form.requestSubmit();
    assert.equal(state.submissions(), 0);
    assert.equal(state.consent.value, '0');
    let prevented = false;
    state.events.get('beforeunload')({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
});

test('confirming exit submits cancellation once and allows the server redirect', async () => {
    const state = setup(true);
    await state.form.requestSubmit();
    assert.equal(state.confirmations(), 1);
    assert.equal(state.submissions(), 1);
    assert.equal(state.consent.value, '1');
    let prevented = false;
    state.events.get('beforeunload')({ preventDefault() { prevented = true; } });
    assert.equal(prevented, false);
});

test('Browser Back requests the same confirmation and keeps the payment URL on decline', async () => {
    const state = setup(false);
    state.events.get('popstate')();
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(state.confirmations(), 1);
    assert.equal(state.submissions(), 0);
    assert.equal(state.pushes.at(-1)[2], 'http://localhost/thanh-toan/draft');
});

test('payment success or expiry can redirect without triggering a cancellation', () => {
    const state = setup(false);
    state.events.get('futa:payment-terminal')();
    state.events.get('beforeunload')({ preventDefault() { assert.fail('Terminal payment must be allowed to redirect'); } });
    assert.equal(state.submissions(), 0);
});
