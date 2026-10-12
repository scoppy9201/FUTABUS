import assert from 'node:assert/strict';
import test from 'node:test';
import { emotionCropFrame } from '../../packages/FuteBus/Profile/src/resources/js/ticket-review-emotion-layout.js';

test('each rating crops its own Rive face into the full square icon without shifting vertically', () => {
    [1, 2, 3, 4, 5].forEach((rating) => {
        const frame = emotionCropFrame(rating, 96, 96);
        const centerX = 250 + (rating - 1) * 387.5;
        assert.equal(frame.minX, -(centerX - 200) * 0.24);
        assert.equal(frame.maxX, (2050 - (centerX - 200)) * 0.24);
        assert.equal(frame.minY, -12);
        assert.equal(frame.maxY, 108);
        assert.equal(frame.maxX - frame.minX, 492);
    });
});

test('cropping scales cleanly for high-density touch and desktop canvas sizes', () => {
    const small = emotionCropFrame(2, 48, 48);
    const large = emotionCropFrame(2, 192, 192);
    assert.equal(small.minX * 4, large.minX);
    assert.equal(small.maxX * 4, large.maxX);
    assert.equal(small.minY * 4, large.minY);
    assert.equal(small.maxY * 4, large.maxY);
});

test('invalid scores and zero-sized canvases cannot create a broken crop layout', () => {
    for (const rating of [0, 6, 2.5, Number.NaN]) {
        assert.throws(() => emotionCropFrame(rating, 96, 96), RangeError);
    }
    assert.throws(() => emotionCropFrame(1, 0, 96), RangeError);
});
