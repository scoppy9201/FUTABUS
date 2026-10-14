import test from 'node:test';
import assert from 'node:assert/strict';
import { syncSidePanel } from '../../packages/FuteBus/Core/src/resources/js/side-panel.js';

function panel() {
    return { open: false, isConnected: true, opens: 0, closes: 0,
        showModal() { this.open = true; this.opens++; }, close() { this.open = false; this.closes++; } };
}

test('panels open after form updates and repeated effects do not reopen them', () => {
    const target = panel();
    const callbacks = [];
    syncSidePanel(target, true, callback => callbacks.push(callback));
    assert.equal(target.open, false);
    callbacks.shift()();
    syncSidePanel(target, true, callback => callback());
    assert.equal(target.opens, 1);
    syncSidePanel(target, false);
    assert.equal(target.closes, 1);
});

test('closing or removing a panel cancels a queued open', () => {
    const target = panel();
    const callbacks = [];
    syncSidePanel(target, true, callback => callbacks.push(callback));
    syncSidePanel(target, false);
    callbacks.shift()();
    assert.equal(target.opens, 0);
    syncSidePanel(target, true, callback => callbacks.push(callback));
    target.isConnected = false;
    callbacks.shift()();
    assert.equal(target.opens, 0);
});
