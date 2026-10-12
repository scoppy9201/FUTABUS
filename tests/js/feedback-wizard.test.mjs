import assert from 'node:assert/strict';
import test from 'node:test';
import { initializeFeedbackWizard, moveFeedbackStep } from '../../packages/FuteBus/Profile/src/resources/js/feedback-wizard.js';

function element() {
    const classes = new Set();
    const attributes = new Map();
    const listeners = new Map();
    return {
        dataset: {}, hidden: false, disabled: false, focused: 0,
        classList: {
            add(...values) { values.forEach((value) => classes.add(value)); },
            remove(...values) { values.forEach((value) => classes.delete(value)); },
            toggle(value, enabled) { if (enabled) classes.add(value); else classes.delete(value); },
        },
        setAttribute(name, value) { attributes.set(name, value); },
        removeAttribute(name) { attributes.delete(name); },
        getAttribute(name) { return attributes.get(name) ?? null; },
        querySelector() { return null; },
        querySelectorAll() { return []; },
        closest() { return null; },
        focus() { this.focused++; this.hiddenWhenFocused = this.step?.hidden ?? false; },
        scrollIntoView() {},
        addEventListener(type, listener, capture = false) {
            const handlers = listeners.get(type) || [];
            handlers.push({ listener, capture });
            listeners.set(type, handlers);
        },
        emit(type, target = this) {
            const event = { target, defaultPrevented: false, preventDefault() { this.defaultPrevented = true; } };
            const handlers = [...(listeners.get(type) || [])].sort((a, b) => Number(b.capture) - Number(a.capture));
            handlers.forEach(({ listener }) => listener(event));
            return event;
        },
    };
}

function wizard({ serverInvalid = -1 } = {}) {
    const form = element();
    const steps = Array.from({ length: 4 }, () => element());
    const fields = steps.map((step, index) => {
        const field = Object.assign(element(), {
            step, name: ['category', 'booking_id', 'subject', 'truth_confirm'][index],
            value: ['trip_service', '', 'A meaningful subject', '1'][index],
            invalid: false, validityReads: 0, reports: 0,
            get willValidate() { return !this.disabled; },
        });
        Object.defineProperties(field, {
            willValidate: { get() { return !this.disabled; } },
            validity: { get() { this.validityReads++; return { valid: !this.invalid }; } },
        });
        field.closest = (selector) => selector === '[data-feedback-step]' ? step : field.wrapper || null;
        field.reportValidity = () => { field.reports++; form.emit('invalid', field); return !field.invalid; };
        return field;
    });
    const upload = Object.assign(element(), { step: steps[2], name: 'attachments[]', files: [], willValidate: false });
    const groups = fields.map((field, index) => index === 2 ? [field, upload] : [field]);
    steps.forEach((step, index) => {
        const heading = element();
        step.querySelector = (selector) => selector === '[data-feedback-step-heading]' ? heading : null;
        step.querySelectorAll = (selector) => selector === 'input, select, textarea' ? groups[index] : [];
    });
    const indicators = steps.map(() => {
        const indicator = element();
        const children = { '[data-feedback-step-number]': element(), '[data-feedback-step-label]': element() };
        indicator.querySelector = (selector) => children[selector] || null;
        return indicator;
    });
    const controls = Object.fromEntries(['back', 'next', 'submit', 'progress', 'viewport', 'status'].map((key) => [key, element()]));
    controls.status.dataset.template = 'Step :current of :total';
    const selectors = {
        '[data-feedback-step-back]': controls.back, '[data-feedback-step-next]': controls.next,
        '[data-feedback-step-submit]': controls.submit, '[data-feedback-wizard-progress]': controls.progress,
        '[data-feedback-wizard-viewport]': controls.viewport, '[data-feedback-step-status]': controls.status,
        '[aria-invalid="true"]': fields[serverInvalid],
    };
    form.querySelector = (selector) => selectors[selector] || null;
    form.querySelectorAll = (selector) => ({
        '[data-feedback-step]': steps, '[data-feedback-step-indicator]': indicators,
        'input, select, textarea': groups.flat(),
    })[selector] || [];
    initializeFeedbackWizard(form);
    return { form, steps, fields, upload, indicators, ...controls, active: () => steps.findIndex((step) => !step.hidden) };
}

test('a required issue or details error keeps the current step and prevents progression', () => {
    const visited = [];
    const validate = (step) => { visited.push(step); return false; };
    assert.equal(moveFeedbackStep(0, 4, 1, validate), 0);
    assert.equal(moveFeedbackStep(2, 4, 1, validate), 2);
    assert.deepEqual(visited, [0, 2]);
});

test('valid steps move through issue, optional trip, details and contact exactly once', () => {
    const visited = [];
    const validate = (step) => { visited.push(step); return true; };
    let current = 0;
    for (let step = 0; step < 3; step++) current = moveFeedbackStep(current, 4, 1, validate);
    assert.equal(current, 3);
    assert.deepEqual(visited, [0, 1, 2]);
});

test('returning to a previous step is always possible even while the current step is invalid', () => {
    const validate = () => { throw new Error('Back must not validate or discard an unfinished step.'); };
    assert.equal(moveFeedbackStep(3, 4, -1, validate), 2);
    assert.equal(moveFeedbackStep(2, 4, -1, validate), 1);
    assert.equal(moveFeedbackStep(1, 4, -1, validate), 0);
    assert.equal(moveFeedbackStep(0, 4, -1, validate), 0);
});

test('the contact step cannot advance beyond the final submit screen', () => {
    const validate = () => { throw new Error('Final submission is validated by the form, not another step.'); };
    assert.equal(moveFeedbackStep(3, 4, 1, validate), 3);
});

test('Next inspects only the visible group and focuses its invalid control after revealing it', () => {
    const view = wizard();
    view.fields[2].invalid = true;
    view.fields[3].invalid = true;
    view.next.emit('click');
    assert.equal(view.active(), 1);
    assert.deepEqual(view.fields.map((field) => field.validityReads), [1, 0, 0, 0]);
    view.next.emit('click');
    assert.equal(view.active(), 2);
    view.next.emit('click');
    assert.equal(view.active(), 2);
    assert.equal(view.fields[2].reports, 1);
    assert.equal(view.fields[2].focused, 1);
    assert.equal(view.fields[2].hiddenWhenFocused, false);
    assert.equal(view.fields[3].validityReads, 0);
});

test('Back preserves entered text and selected image objects without validating unfinished fields', () => {
    const view = wizard();
    view.next.emit('click');
    view.next.emit('click');
    view.fields[2].value = 'Customer evidence still being written';
    const files = [{ name: 'evidence.png', type: 'image/png', size: 2048 }];
    view.upload.files = files;
    view.fields[2].invalid = true;
    view.back.emit('click');
    assert.equal(view.active(), 1);
    view.next.emit('click');
    assert.equal(view.active(), 2);
    assert.equal(view.fields[2].value, 'Customer evidence still being written');
    assert.equal(view.upload.files, files);
    assert.equal(view.fields[2].validityReads, 0);
    assert.equal(view.fields[2].reports, 0);
    assert.equal(view.fields[2].disabled, false);
    assert.equal(view.upload.disabled, false);
});

test('early submit advances one screen and prevents the submit guard from disabling the button', () => {
    const view = wizard();
    let sent = 0;
    view.form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) return;
        sent++;
        view.submit.disabled = true;
    });
    assert.equal(view.form.emit('submit').defaultPrevented, true);
    assert.equal(view.active(), 1);
    assert.equal(sent, 0);
    assert.equal(view.submit.disabled, false);
    view.next.emit('click');
    view.next.emit('click');
    assert.equal(view.form.emit('submit').defaultPrevented, false);
    assert.equal(sent, 1);
    assert.equal(view.submit.disabled, true);
});

test('final submit validates hidden earlier fields, ignores disabled fields and focuses the shared trigger', () => {
    const view = wizard();
    for (let index = 0; index < 3; index++) view.next.emit('click');
    view.fields.forEach((field) => { field.validityReads = 0; });
    view.fields[0].disabled = true;
    view.fields[0].invalid = true;
    view.fields[2].invalid = true;
    const trigger = Object.assign(element(), { step: view.steps[2] });
    view.fields[2].wrapper = { querySelector: (selector) => selector === '[data-ui-trigger]' ? trigger : null };
    assert.equal(view.steps[2].hidden, true);
    assert.equal(view.form.emit('submit').defaultPrevented, true);
    assert.equal(view.active(), 2);
    assert.equal(view.fields[0].validityReads, 0);
    assert.equal(view.fields[0].reports, 0);
    assert.equal(view.fields[2].reports, 1);
    assert.equal(view.fields[2].focused, 0);
    assert.equal(trigger.focused, 1);
    assert.equal(trigger.hiddenWhenFocused, false);
    assert.equal(view.fields[3].validityReads, 0);
    view.fields[2].invalid = false;
    view.next.emit('click');
    assert.equal(view.form.emit('submit').defaultPrevented, false);
});

test('server field errors open the right group with one current progress indicator and the correct action', () => {
    for (let index = 0; index < 4; index++) {
        const view = wizard({ serverInvalid: index });
        assert.equal(view.active(), index);
        assert.equal(view.status.textContent, `Step ${index + 1} of 4`);
        assert.deepEqual(view.indicators.map((indicator) => indicator.getAttribute('aria-current')),
            view.indicators.map((_, position) => position === index ? 'step' : null));
        assert.equal(view.back.hidden, index === 0);
        assert.equal(view.next.hidden, index === 3);
        assert.equal(view.submit.hidden, index !== 3);
        assert.equal(view.progress.hidden, false);
        assert.equal(view.form.noValidate, true);
    }
});

test('native invalid capture reveals a hidden field before a following handler focuses it', () => {
    const view = wizard();
    view.form.addEventListener('invalid', ({ target }) => target.focus());
    view.form.emit('invalid', view.fields[3]);
    assert.equal(view.active(), 3);
    assert.equal(view.fields[3].hiddenWhenFocused, false);
});

test('initializing an existing wizard again does not duplicate navigation listeners', () => {
    const view = wizard();
    initializeFeedbackWizard(view.form);
    view.next.emit('click');
    assert.equal(view.active(), 1);
});
