import test from 'node:test';
import assert from 'node:assert/strict';
import { setupAdvancedFilters } from '../../packages/FuteBus/Reporting/src/resources/js/report-filters.js';

function element() {
    const handlers = new Map();
    return {
        addEventListener(name, callback) {
            if (!handlers.has(name)) handlers.set(name, []);
            handlers.get(name).push(callback);
        },
        dispatchEvent(event) {
            for (const callback of handlers.get(event.type) || []) callback(event);
        },
        click() { this.dispatchEvent({ type: 'click', target: this }); },
    };
}

function fixture() {
    const fields = ['12', '34', '56', 'scheduled'].map((value, index) => ({
        ...element(), value, querySelector: () => index === 3 ? {} : null,
    }));
    const trigger = element();
    const cancel = element();
    const clear = element();
    const apply = element();
    const submit = {};
    const submissions = [];
    const form = {
        querySelector: () => submit,
        requestSubmit(button) { submissions.push({ button, open: panel.open, values: fields.map((field) => field.value) }); },
    };
    const panel = {
        ...element(), open: false,
        querySelectorAll: () => fields,
        querySelector: (selector) => ({
            '[data-report-advanced-cancel]': cancel,
            '[data-report-advanced-reset]': clear,
            '[data-report-advanced-apply]': apply,
        })[selector],
        showModal() { this.open = true; },
        close() { this.open = false; this.dispatchEvent({ type: 'close' }); },
    };
    globalThis.document = {
        getElementById: (id) => id === 'report-filters' ? form : panel,
        querySelector: () => trigger,
    };
    setupAdvancedFilters();
    return { fields, trigger, cancel, clear, apply, submit, panel, submissions };
}

test('closing advanced filters discards edits and updates the shared controls', () => {
    const state = fixture();
    let changes = 0;
    state.fields[0].addEventListener('change', () => changes++);
    state.trigger.click();
    state.fields[0].value = '99';
    state.cancel.click();
    assert.equal(state.fields[0].value, '12');
    assert.equal(changes, 1);
    assert.equal(state.panel.open, false);
    assert.equal(state.submissions.length, 0);
});

test('clearing filters changes the draft until applied and Escape restores original choices', () => {
    const state = fixture();
    state.trigger.click();
    state.clear.click();
    assert.deepEqual(state.fields.map((field) => field.value), ['', '', '', 'all']);
    state.panel.close();
    assert.deepEqual(state.fields.map((field) => field.value), ['12', '34', '56', 'scheduled']);
    assert.equal(state.submissions.length, 0);
});

test('applying closes the dialog before submitting current filters with the report submitter', () => {
    const state = fixture();
    state.trigger.click();
    state.fields[0].value = '99';
    state.apply.click();
    assert.equal(state.submissions.length, 1);
    assert.equal(state.submissions[0].button, state.submit);
    assert.equal(state.submissions[0].open, false);
    assert.deepEqual(state.submissions[0].values, ['99', '34', '56', 'scheduled']);
    state.trigger.click();
    state.fields[0].value = '100';
    state.panel.close();
    assert.equal(state.fields[0].value, '99');
});
