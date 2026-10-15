import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';

const source = await readFile(new URL('../../packages/FuteBus/Payment/src/resources/js/manual-refunds.js', import.meta.url), 'utf8');

function setup(openOnLoad = false) {
    const handlers = {};
    let close;
    let replacedUrl;
    let mutations = 0;
    const panel = {
        open: false,
        showModal() { this.open = true; },
        addEventListener(name, callback) { if (name === 'close') close = callback; },
    };
    const content = { innerHTML: '', inert: false, setAttribute() {}, querySelector: () => null };
    const workspace = {
        dataset: { refundOpen: String(openOnLoad) },
        querySelector: selector => selector === '#refund-details' ? panel : selector === '[data-refund-content]' ? content : null,
        querySelectorAll: () => [],
        addEventListener(name, callback) { handlers[name] = callback; },
    };
    vm.runInNewContext(source, {
        document: { querySelector: () => workspace },
        window: {
            location: { href: 'http://localhost/refunds?status=refunded&selected=1' },
            history: { state: null, replaceState(_state, _title, url) { replacedUrl = url; } },
        },
        AbortController, URL, setTimeout, clearTimeout,
        fetch: async (_url, options) => {
            if (options.method) mutations += 1;
            return { ok: true, text: async () => '<article>Refund details</article>' };
        },
    });
    return { panel, content, handlers, close: () => close(), url: () => replacedUrl, mutations: () => mutations };
}

test('refund list opens details only after selection and closing preserves filters', async () => {
    const context = setup();
    assert.equal(context.panel.open, false);
    const link = { href: '/refunds/1', dataset: { refundId: '1' } };
    await context.handlers.click({
        target: { closest: selector => selector === '[data-refund-detail]' ? link : null },
        preventDefault() {},
    });
    assert.equal(context.panel.open, true);
    assert.equal(context.content.innerHTML, '<article>Refund details</article>');
    assert.equal(context.content.inert, false);
    context.close();
    assert.equal(context.url(), 'http://localhost/refunds?status=refunded');
    assert.equal(context.mutations(), 0);
});

test('returning from a refund update reopens the selected details', () => {
    assert.equal(setup(true).panel.open, true);
});
