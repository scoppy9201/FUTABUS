export function emotionCropFrame(rating, width, height) {
    if (!Number.isInteger(rating) || rating < 1 || rating > 5 || width <= 0 || height <= 0) {
        throw new RangeError('A trip emotion requires a rating from 1 to 5 and a non-zero canvas.');
    }
    const cropLeft = 250 + (rating - 1) * 387.5 - 200;
    const cropTop = 50;
    const scale = width / 400;
    return {
        minX: -cropLeft * scale,
        maxX: (2050 - cropLeft) * scale,
        minY: -cropTop * scale,
        maxY: (500 - cropTop) * scale,
    };
}
