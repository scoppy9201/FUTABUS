import assert from 'node:assert/strict';
import test from 'node:test';
import { composeReviewComment, ratingGroup, toggleRating } from '../../packages/FuteBus/Profile/src/resources/js/ticket-review-state.js';

test('clicking the selected star again clears the rating', () => {
    assert.equal(toggleRating(0, 4), 4);
    assert.equal(toggleRating(4, 4), 0);
    assert.equal(toggleRating(4, 2), 2);
});

test('positive and negative suggestions follow the selected score', () => {
    assert.equal(ratingGroup(0), null);
    assert.equal(ratingGroup(1), 'negative');
    assert.equal(ratingGroup(3), 'negative');
    assert.equal(ratingGroup(4), 'positive');
    assert.equal(ratingGroup(5), 'positive');
});

test('selected suggestions are saved with the optional written comment', () => {
    assert.equal(composeReviewComment([], ''), '');
    assert.equal(composeReviewComment(['Xe sạch sẽ'], ''), 'Xe sạch sẽ');
    assert.equal(composeReviewComment(['Xe sạch sẽ', 'Đúng giờ'], '  Đi rất thoải mái  '),
        'Xe sạch sẽ · Đúng giờ · Đi rất thoải mái');
});
