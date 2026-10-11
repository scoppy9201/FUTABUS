const allowedImageTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);
const maxImageBytes = 5 * 1024 * 1024;

export function mergeFeedbackImages(current, incoming) {
    const files = [...current];
    for (const file of incoming) {
        if (!allowedImageTypes.has(file.type) || file.size <= 0 || file.size > maxImageBytes) {
            return { files: [...current], valid: false };
        }
        const duplicate = files.some((existing) => existing.name === file.name
            && existing.size === file.size && existing.lastModified === file.lastModified);
        if (!duplicate) files.push(file);
    }
    return files.length <= 3 ? { files, valid: true } : { files: [...current], valid: false };
}
