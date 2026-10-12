import assert from 'node:assert/strict';
import test from 'node:test';
import { createControl, dispatchControlChange, observeControl } from '../../packages/FuteBus/Core/src/resources/js/ui-control.js';

class TestObserver {
    constructor(callback) { this.callback = callback; }
    observe() {}
    disconnect() {}
}
globalThis.MutationObserver = TestObserver;

class FormControl extends EventTarget {
    constructor() {
        super();
        this.currentValue = '';
        this.currentDisabled = false;
        this.attributes = new Map();
        this.validity = { valid: true };
        this.form = new EventTarget();
    }
    get value() { return this.currentValue; }
    set value(value) { this.currentValue = String(value); }
    get disabled() { return this.currentDisabled; }
    set disabled(value) { this.currentDisabled = Boolean(value); }
    setAttribute(name, value) { this.attributes.set(name, value); }
    getAttribute(name) { return this.attributes.get(name) ?? null; }
}

const flush = () => new Promise((resolve) => queueMicrotask(resolve));

test('programmatic model changes synchronize the UI without altering native values or disabled state', async () => {
    const source = new FormControl();
    const states = [];
    const stop = observeControl(source, () => states.push([source.value, source.disabled]));
    source.value = 'route-42';
    source.disabled = true;
    await flush();
    assert.deepEqual(states, [['', false], ['route-42', true]]);
    assert.equal(source.value, 'route-42');
    stop();
});

test('option.selected updates used by Alpine select models synchronize without a change event', async () => {
    class Option {
        get selected() { return this.isSelected ?? false; }
        set selected(value) { this.isSelected = Boolean(value); }
    }
    const source = new FormControl();
    source.options = [new Option(), new Option()];
    const selected = [];
    const stop = observeControl(source, () => selected.push(source.options.findIndex((option) => option.selected)));
    source.options[1].selected = true;
    await flush();
    assert.deepEqual(selected, [-1, 1]);
    stop();
    assert.equal(Object.hasOwn(source.options[1], 'selected'), false);
});

test('selection sends input then change with the actual form value and preserves invalid state', () => {
    const source = new FormControl();
    const events = [];
    source.addEventListener('input', (event) => events.push([event.type, source.value, event.bubbles]));
    source.addEventListener('change', (event) => events.push([event.type, source.value, event.bubbles]));
    dispatchControlChange(source, 'confirmed');
    assert.deepEqual(events, [['input', 'confirmed', true], ['change', 'confirmed', true]]);
    assert.equal(source.attributes.get('aria-invalid'), 'false');
    source.validity.valid = false;
    dispatchControlChange(source, '');
    assert.equal(source.attributes.get('aria-invalid'), 'true');
});

test('form reset resynchronizes and disposal restores native descriptors and cancels queued work', async () => {
    const source = new FormControl();
    let calls = 0;
    const stop = observeControl(source, () => calls++);
    source.form.dispatchEvent(new Event('reset'));
    await flush();
    assert.equal(calls, 2);
    source.value = 'pending';
    stop();
    await flush();
    assert.equal(calls, 2);
    assert.equal(Object.hasOwn(source, 'value'), false);
    source.value = 'restored';
    assert.equal(source.value, 'restored');
});

function enhancedControl() {
    const source = new FormControl();
    source.dataset = {};
    source.className = '';
    source.classList = { add() {} };
    source.before = () => {};
    const trigger = new FormControl();
    trigger.focus = () => {};
    const error = { hidden: true, textContent: '' };
    const parts = { '[data-ui-trigger]': trigger, '[data-ui-error]': error,
        '[data-ui-value]': {}, '[data-ui-panel]': {} };
    const root = { className: '', classList: { add() {} }, prepend() {},
        querySelector: (selector) => parts[selector] };
    const previous = globalThis.document;
    globalThis.document = { querySelector: () => ({ dataset: { choose: 'Choose' },
        content: { firstElementChild: { cloneNode: () => root } } }) };
    try {
        createControl(source, '#control-template');
    } finally {
        globalThis.document = previous;
    }
    return { source, trigger, error };
}

test('reusing a modal clears a generated error after a valid model update without submitting the form', async () => {
    const { source, trigger, error } = enhancedControl();
    let changes = 0;
    source.addEventListener('change', () => changes++);
    const stop = observeControl(source, () => {});
    source.validity.valid = false;
    source.validationMessage = 'Required';
    source.dispatchEvent(new Event('invalid', { cancelable: true }));
    assert.equal(error.hidden, false);
    assert.equal(trigger.getAttribute('aria-invalid'), 'true');
    source.validity.valid = true;
    source.value = 'another-record';
    await flush();
    assert.equal(error.hidden, true);
    assert.equal(error.textContent, '');
    assert.equal(source.getAttribute('aria-invalid'), 'false');
    assert.equal(trigger.getAttribute('aria-invalid'), 'false');
    assert.equal(changes, 0);
    stop();
});

test('initial server errors survive model synchronization and clear after an actual valid edit', async () => {
    const { source, trigger } = enhancedControl();
    source.setAttribute('aria-invalid', 'true');
    trigger.setAttribute('aria-invalid', 'true');
    const stop = observeControl(source, () => {});
    source.value = 'restored-server-value';
    await flush();
    assert.equal(source.getAttribute('aria-invalid'), 'true');
    assert.equal(trigger.getAttribute('aria-invalid'), 'true');
    dispatchControlChange(source, 'corrected-value');
    assert.equal(source.getAttribute('aria-invalid'), 'false');
    assert.equal(trigger.getAttribute('aria-invalid'), 'false');
    stop();
});
