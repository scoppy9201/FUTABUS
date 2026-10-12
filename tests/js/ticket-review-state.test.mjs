import assert from 'node:assert/strict';
import test from 'node:test';
import { composeReviewComment, ratingGroup, toggleRating } from '../../packages/FuteBus/Profile/src/resources/js/ticket-review-state.js';

test('each emotion can be selected and cleared by choosing it again', () => {
    for (const rating of [1, 2, 3, 4, 5]) {
        assert.equal(toggleRating(0, rating), rating);
        assert.equal(toggleRating(rating, rating), 0);
    }
});

test('choosing another emotion replaces the selected rating', () => {
    assert.equal(toggleRating(4, 2), 2);
    assert.equal(toggleRating(2, 5), 5);
});

test('positive and negative suggestions follow the selected score', () => {
    assert.equal(ratingGroup(0), null);
    assert.equal(ratingGroup(1), 'negative');
    assert.equal(ratingGroup(2), 'negative');
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
