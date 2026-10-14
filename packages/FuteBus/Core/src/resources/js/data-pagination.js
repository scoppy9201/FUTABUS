export function paginationPages(current, last) {
    last = Math.max(1, Number(last) || 1);
    current = Math.max(1, Math.min(last, Number(current) || 1));
    if (last <= 5) return Array.from({ length: last }, (_, index) => index + 1);
    const pages = [...new Set([1, last, ...Array.from({ length: Math.min(last, current + 1) - Math.max(1, current - 1) + 1 },
        (_, index) => Math.max(1, current - 1) + index)])].sort((a, b) => a - b);
    const result = [];
    pages.forEach((page, index) => {
        if (index && page > pages[index - 1] + 1) result.push(null);
        result.push(page);
    });
    return result;
}

if (typeof window !== 'undefined') window.FutaPagination = { pages: paginationPages };
