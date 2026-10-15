import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';

const source = await readFile(new URL('../../packages/FuteBus/Payment/src/resources/js/ticket-success.js', import.meta.url), 'utf8');

test('paid checkout opens a modal and Escape returns home', () => {
    let cancel;
    let destination;
    const modal = {
        dataset: { homeUrl: '/home' },
        open: false,
        showModal() { this.open = true; },
        addEventListener(name, handler) { if (name === 'cancel') cancel = handler; },
    };
    vm.runInNewContext(source, {
        document: {
            querySelector: (selector) => selector === '[data-payment-success-modal]' ? modal : null,
        },
        window: {
            location: { assign: (url) => { destination = url; } },
            addEventListener() {},
        },
    });
    assert.equal(modal.open, true);
    let prevented = false;
    cancel({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(destination, '/home');
});

test('pending checkout does not open a success modal', () => {
    vm.runInNewContext(source, {
        document: { querySelector: () => null },
    });
});
