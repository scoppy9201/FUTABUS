import assert from 'node:assert/strict';
import test from 'node:test';
import {
    createModalState, createScrollbarActivity, pointerOnScrollbar, shouldLockScroller,
} from '../../packages/FuteBus/Core/src/resources/js/ui-overlays.js';

function node(children = []) {
    const classes = new Set();
    return {
        classes,
        contains(target) { return target === this || children.some((child) => child === target || child.contains(target)); },
        classList: {
            add(...values) { values.forEach((value) => classes.add(value)); },
            remove(...values) { values.forEach((value) => classes.delete(value)); },
        },
    };
}

test('closing an inner modal retains the outer modal and its background lock', () => {
    const innerContent = node();
    const inner = node([innerContent]);
    const outerContent = node([inner]);
    const outer = node([outerContent]);
    const page = node([outer]);
    const state = createModalState();

    let active = state.sync([outer]);
    assert.equal(shouldLockScroller(page, active), true);
    assert.equal(shouldLockScroller(outerContent, active), false);

    active = state.sync([outer, inner]);
    assert.equal(shouldLockScroller(outerContent, active), true);
    assert.equal(shouldLockScroller(innerContent, active), false);

    active = state.sync([outer]);
    assert.equal(active, outer);
    assert.equal(shouldLockScroller(page, active), true);
    assert.equal(shouldLockScroller(outerContent, active), false);

    assert.equal(shouldLockScroller(page, state.sync([])), false);
});

test('DOM order changes do not move an existing overlay above the latest open modal', () => {
    const first = node();
    const latest = node();
    const state = createModalState();
    state.sync([first]);
    assert.equal(state.sync([latest, first]), latest);
    assert.equal(state.sync([first, latest]), latest);
    assert.equal(state.sync([first]), first);
});

test('scrolling extends visibility but leaving immediately cancels the pending reveal', () => {
    const tasks = new Map();
    let sequence = 0;
    const activity = createScrollbarActivity((callback) => {
        tasks.set(++sequence, callback);
        return sequence;
    }, (id) => tasks.delete(id));
    const element = node();

    activity.scroll(element);
    assert.equal(element.classes.has('ui-scrolling'), true);
    activity.scroll(element);
    assert.equal(tasks.size, 1);

    element.classList.add('ui-scrollbar-hover');
    activity.hide(element);
    assert.equal(element.classes.size, 0);
    assert.equal(tasks.size, 0);
});

test('a scrollbar hides after scrolling stops without changing container classes', () => {
    let expire;
    const activity = createScrollbarActivity((callback) => { expire = callback; return 1; }, () => {});
    const element = node();
    element.classList.add('ui-scrollable', 'overflow-auto');
    activity.scroll(element);
    expire();
    assert.deepEqual([...element.classes], ['ui-scrollable', 'overflow-auto']);
});

test('content hover does not reveal the scrollbar while its narrow gutter can be hovered', () => {
    const box = { left: 20, top: 30, right: 220, bottom: 180 };
    const vertical = { vertical: true, horizontal: false, gutter: 8 };
    assert.equal(pointerOnScrollbar({ x: 100, y: 100 }, box, vertical), false);
    assert.equal(pointerOnScrollbar({ x: 217, y: 100 }, box, vertical), true);
    assert.equal(pointerOnScrollbar({ x: 225, y: 100 }, box, vertical), false);
    assert.equal(pointerOnScrollbar({ x: 217, y: 190 }, box, vertical), false);
    assert.equal(pointerOnScrollbar({ x: 25, y: 100 }, box, { ...vertical, rtl: true }), true);
    assert.equal(pointerOnScrollbar({ x: 217, y: 100 }, box, { ...vertical, rtl: true }), false);
    assert.equal(pointerOnScrollbar({ x: 100, y: 177 }, box, { vertical: false, horizontal: true }), true);
});
