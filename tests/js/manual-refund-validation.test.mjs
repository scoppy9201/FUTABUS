import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';

const source = await readFile(new URL('../../packages/FuteBus/Payment/src/resources/js/manual-refunds.js', import.meta.url), 'utf8');

function setup(response, invalid = false) {
    const handlers = {};
    const notifications = [];
    let confirmations = 0;
    let requests = 0;
    const error = { id: 'amount-error', dataset: { refundFieldError: 'amount' }, hidden: true };
    const field = {
        name: 'amount', value: '1400', willValidate: true, validationMessage: 'Enter a valid amount',
        checkValidity: () => !invalid, setAttribute() {}, focus() {},
    };
    const form = {
        dataset: {}, closest() { return this; }, getAttribute: () => '/refunds/1',
        querySelectorAll: selector => selector === '[data-refund-field-error]' ? [error] : [field],
    };
    const content = { inert: false, setAttribute() {} };
    const workspace = {
        dataset: { error: 'Cannot process refund' },
        querySelector: selector => selector === '[data-refund-content]' ? content : null,
        addEventListener(name, callback) { handlers[name] = callback; },
    };
    vm.runInNewContext(source, {
        document: { querySelector: () => workspace },
        window: {
            FutaConfirm: async () => { confirmations += 1; return true; },
            FutaNotify: { error: message => notifications.push(message) },
        },
        FormData: class { get() { return 'claim'; } },
        AbortController, URL, setTimeout, clearTimeout,
        fetch: async () => { requests += 1; return response; },
    });
    return {
        submit: () => handlers.submit({ target: form, preventDefault() {} }),
        error, field, content, notifications, confirmations: () => confirmations, requests: () => requests,
    };
}

test('invalid input displays below the field before confirmation or mutation', async () => {
    const context = setup(null, true);
    await context.submit();
    assert.equal(context.error.hidden, false);
    assert.equal(context.error.textContent, 'Enter a valid amount');
    assert.equal(context.confirmations(), 0);
    assert.equal(context.requests(), 0);
});

test('server field validation remains inline and preserves input', async () => {
    const context = setup({ ok: false, status: 422, json: async () => ({ errors: { amount: ['Wrong refund amount'] } }) });
    await context.submit();
    assert.equal(context.error.textContent, 'Wrong refund amount');
    assert.equal(context.error.hidden, false);
    assert.equal(context.field.value, '1400');
    assert.equal(context.content.inert, false);
    assert.equal(context.notifications.length, 0);
});

test('state conflicts use the system notification and keep the form editable', async () => {
    const context = setup({ ok: false, status: 409, json: async () => ({ message: 'Request already assigned' }) });
    await context.submit();
    assert.deepEqual(context.notifications, ['Request already assigned']);
    assert.equal(context.content.inert, false);
    assert.equal(context.error.hidden, true);
});
