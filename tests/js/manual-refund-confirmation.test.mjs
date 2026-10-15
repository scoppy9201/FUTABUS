import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../packages/FuteBus/Payment/src/resources/js/manual-refunds.js', import.meta.url), 'utf8');

function setup(confirmed) {
    const handlers = {};
    const error = { textContent: '' };
    const content = { setAttribute() {}, querySelector: () => error };
    const form = {
        // A control named "action" masks HTMLFormElement.action in the browser.
        action: { value: 'claim', toString: () => '[object HTMLInputElement]' },
        getAttribute: name => name === 'action' ? '/refund' : null,
        dataset: { confirmTitle: 'Refund', confirmMessage: 'Verified refund?', confirmLabel: 'Confirm' },
        closest() { return this; },
        querySelectorAll: () => [],
    };
    const workspace = {
        dataset: { error: 'Failed', saved: 'Saved' },
        querySelector: selector => selector === '[data-refund-content]' ? content : null,
        addEventListener: (name, callback) => { handlers[name] = callback; },
    };
    const requests = [];
    const prompts = [];
    const window = {
        FutaConfirm: async options => { prompts.push(options); return confirmed; },
        location: { href: 'http://localhost/refunds', assign() {} },
    };
    class FormData { get(name) { return name === 'action' ? 'claim' : null; } }
    vm.runInNewContext(source, { document: { querySelector: () => workspace }, window, FormData,
        AbortController, URL, setTimeout, clearTimeout, fetch: async (...args) => {
            requests.push(args); return { ok: true, json: async () => ({}) };
        } });
    return { submit: () => handlers.submit({ target: form, preventDefault() {} }), requests, prompts };
}

test('declining shared confirmation sends no refund mutation', async () => {
    const context = setup(false);
    await context.submit();
    assert.equal(context.prompts.length, 1);
    assert.equal(context.requests.length, 0);
});

test('accepting shared confirmation submits exactly one mutation', async () => {
    const context = setup(true);
    await context.submit();
    assert.equal(context.requests.length, 1);
    assert.equal(context.requests[0][0], '/refund');
    assert.equal(context.prompts[0].message, 'Verified refund?');
});
