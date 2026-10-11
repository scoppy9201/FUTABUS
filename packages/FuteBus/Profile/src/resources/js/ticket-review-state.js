export function toggleRating(current, selected) {
    return current === selected ? 0 : selected;
}

export function ratingGroup(rating) {
    if (rating >= 4) return 'positive';
    if (rating >= 1) return 'negative';
    return null;
}

export function composeReviewComment(tags, comment) {
    return [...tags, comment.trim()].filter(Boolean).join(' · ');
}
