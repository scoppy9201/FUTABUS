import test from 'node:test';
import assert from 'node:assert/strict';
import { paginationPages } from '../../packages/FuteBus/Core/src/resources/js/data-pagination.js';

test('empty and short lists show every valid page without gaps', () => {
    assert.deepEqual(paginationPages(1, 0), [1]);
    assert.deepEqual(paginationPages(1, 4), [1, 2, 3, 4]);
});

test('large lists show the current window and boundaries in bounded space', () => {
    assert.deepEqual(paginationPages(50, 100), [1, null, 49, 50, 51, null, 100]);
    assert.deepEqual(paginationPages(1, 100), [1, 2, null, 100]);
    assert.deepEqual(paginationPages(100, 100), [1, null, 99, 100]);
});

test('out of range current pages cannot create invalid numbered buttons', () => {
    assert.deepEqual(paginationPages(20000, 3), [1, 2, 3]);
    assert.deepEqual(paginationPages(-2, 10), [1, 2, null, 10]);
});
