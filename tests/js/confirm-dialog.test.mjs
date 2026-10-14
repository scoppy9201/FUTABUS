import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../packages/FuteBus/Core/src/resources/js/confirm-dialog.js', import.meta.url), 'utf8');

function setup() {
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
    const document = { getElementById: () => dialog, addEventListener() {}, querySelector: () => null, activeElement: { focus() {} } };
    vm.runInNewContext(source, { window, document });
    return { window, dialog, node };
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
