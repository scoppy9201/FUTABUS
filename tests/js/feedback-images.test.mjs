import assert from 'node:assert/strict';
import test from 'node:test';
import { mergeFeedbackImages } from '../../packages/FuteBus/Profile/src/resources/js/feedback-images.js';

const image = (name, type = 'image/png', size = 1024) => ({ name, type, size, lastModified: 1 });

test('accepts all supported image types and the exact 5 MB boundary', () => {
    const files = [image('a.jpg', 'image/jpeg', 5 * 1024 * 1024), image('b.png'), image('c.webp', 'image/webp')];
    assert.deepEqual(mergeFeedbackImages([], files), { files, valid: true });
});

test('appends selections, avoids duplicates, and permits replacements after removing an image', () => {
    const first = image('a.png');
    const second = image('b.png');
    const third = image('c.png');
    assert.deepEqual(mergeFeedbackImages([first], [first, second]), { files: [first, second], valid: true });
    assert.deepEqual(mergeFeedbackImages([second], [third]), { files: [second, third], valid: true });
});

test('rejects excess files atomically and retains the existing evidence', () => {
    const current = [image('a.png'), image('b.png')];
    assert.deepEqual(mergeFeedbackImages(current, [image('c.png'), image('d.png')]), { files: current, valid: false });
    assert.equal(current.length, 2);
});

test('rejects unsupported MIME, empty and oversized images without losing earlier files', () => {
    const current = [image('a.png')];
    const invalid = [image('photo.gif', 'image/gif'), image('forged.jpg', 'text/html'), image('empty.png', 'image/png', 0), image('big.png', 'image/png', 5242881)];
    for (const file of invalid) {
        assert.deepEqual(mergeFeedbackImages(current, [image('valid.png'), file]), { files: current, valid: false });
    }
});
