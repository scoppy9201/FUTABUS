import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../packages/FuteBus/Core/src/resources/js/confirm-dialog.js', import.meta.url), 'utf8');

function setup() {
    class HTMLFormElement {
        dataset = {};
        hasAttribute() { return true; }
        requestSubmit() { this.submitted = true; }
    }
    const nodes = new Map();
    const node = key => {
        if (!nodes.has(key)) nodes.set(key, { handlers: {}, focus() {}, addEventListener(event, callback) { this.handlers[event] = callback; } });
        return nodes.get(key);
    };
    const dialog = {
        open: false, handlers: {}, dataset: {},
        querySelector: node,
        addEventListener(event, callback) { this.handlers[event] = callback; },
        showModal() { this.open = true; },
        close() { this.open = false; this.handlers.close(); },
    };
    const window = { matchMedia: () => ({ matches: true }) };
    const handlers = {};
    const document = { getElementById: () => dialog, addEventListener(event, callback) { handlers[event] = callback; }, querySelector: () => null, activeElement: { focus() {} } };
    vm.runInNewContext(source, { window, document, HTMLFormElement });
    return { window, dialog, node, handlers, HTMLFormElement };
}

test('shared dialog resolves true only after acceptance and refuses overlapping prompts', async () => {
    const { window, dialog, node } = setup();
    const decision = window.FutaConfirm({ title: 'Send', message: 'Confirm response', label: 'Send' });
    assert.equal(dialog.open, true);
    assert.equal(node('#global-confirm-message').textContent, 'Confirm response');
    assert.equal(await window.FutaConfirm(), false);
    await node('[data-confirm-accept]').handlers.click();
    assert.equal(await decision, true);
    assert.equal(dialog.open, false);
});

test('cancel closes the shared dialog without authorizing the operation', async () => {
    const { window, node } = setup();
    const decision = window.FutaConfirm();
    node('[data-confirm-cancel]').handlers.click();
    assert.equal(await decision, false);
});

test('a second submitted form cannot replace the operation being confirmed', async () => {
    const { node, handlers, HTMLFormElement } = setup();
    const first = new HTMLFormElement();
    const second = new HTMLFormElement();
    handlers.submit({ target: first, preventDefault() {} });
    handlers.submit({ target: second, preventDefault() {} });
    await node('[data-confirm-accept]').handlers.click();
    assert.equal(first.submitted, true);
    assert.equal(second.submitted, undefined);
});
