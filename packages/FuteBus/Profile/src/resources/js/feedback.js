import { mergeFeedbackImages } from './feedback-images.js';

const page = document.querySelector('[data-feedback-page]');
const createDialog = page?.querySelector('[data-feedback-create]');
const detailDialog = page?.querySelector('[data-feedback-detail]');
const form = createDialog?.querySelector('[data-feedback-form]');

function wireDialog(dialog) {
    if (!dialog || typeof dialog.showModal !== 'function') return;
    dialog.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('[data-feedback-close]')) {
            dialog.close();
        } else if (event.target === dialog) {
            const box = dialog.getBoundingClientRect();
            if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) {
                dialog.close();
            }
        }
    });
}

wireDialog(createDialog);
wireDialog(detailDialog);

if (createDialog && typeof createDialog.showModal === 'function') {
    const trigger = page.querySelector('[data-feedback-open]');
    trigger?.addEventListener('click', () => createDialog.showModal());
    createDialog.addEventListener('close', () => trigger?.focus({ preventScroll: true }));
    if (createDialog.hasAttribute('data-open-on-load')) {
        createDialog.showModal();
        const invalid = form?.querySelector('[aria-invalid="true"]');
        invalid?.focus();
    }
}

if (form) {
    const message = form.querySelector('[data-feedback-message]');
    const counter = form.querySelector('[data-feedback-count]');
    const input = form.querySelector('[data-feedback-images]');
    const dropzone = form.querySelector('[data-feedback-dropzone]');
    const previews = form.querySelector('[data-feedback-previews]');
    const template = form.querySelector('[data-feedback-preview-template]');
    const error = form.querySelector('[data-feedback-image-error]');
    let images = [...input.files];
    let previewUrls = [];
    const updateCount = () => { counter.textContent = `${Array.from(message.value).length}/3000`; };
    message.addEventListener('input', updateCount);
    updateCount();

    const revokePreviews = () => {
        previewUrls.forEach((url) => URL.revokeObjectURL(url));
        previewUrls = [];
    };
    const syncFiles = () => {
        const transfer = new DataTransfer();
        images.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    };
    const renderImages = () => {
        revokePreviews();
        previews.replaceChildren();
        images.forEach((file, index) => {
            const fragment = template.content.cloneNode(true);
            const image = fragment.querySelector('img');
            const url = URL.createObjectURL(file);
            previewUrls.push(url);
            image.src = url;
            image.alt = file.name;
            fragment.querySelector('figcaption').textContent = file.name;
            const remove = fragment.querySelector('button');
            remove.setAttribute('aria-label', form.dataset.removeImage.replace(':name', file.name));
            remove.addEventListener('click', () => {
                images.splice(index, 1);
                syncFiles();
                renderImages();
                error.textContent = '';
                input.setAttribute('aria-invalid', 'false');
                input.focus();
            });
            previews.append(fragment);
        });
    };
    const selectImages = (incoming) => {
        const selection = mergeFeedbackImages(images, incoming);
        images = selection.files;
        error.textContent = selection.valid ? '' : form.dataset.uploadError;
        input.setAttribute('aria-invalid', String(!selection.valid));
        syncFiles();
        renderImages();
    };
    input.addEventListener('change', () => selectImages([...input.files]));
    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('border-futa-orange');
    });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('border-futa-orange'));
    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('border-futa-orange');
        selectImages([...event.dataTransfer.files]);
    });
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) return;
        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = 'true';
        form.querySelector('[type="submit"]').disabled = true;
    });
    window.addEventListener('pageshow', () => {
        delete form.dataset.submitting;
        form.querySelector('[type="submit"]').disabled = false;
        renderImages();
    });
    window.addEventListener('pagehide', revokePreviews);
}

if (detailDialog && typeof detailDialog.showModal === 'function') {
    let activeRequest = null;
    let activeTrigger = null;
    const content = detailDialog.querySelector('[data-feedback-detail-content]');
    page.addEventListener('click', async (event) => {
        const link = event.target instanceof Element ? event.target.closest('[data-feedback-detail-link]') : null;
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        activeRequest?.abort();
        const controller = new AbortController();
        activeRequest = controller;
        activeTrigger = link;
        window.FutaLoader?.show();
        const timeout = window.setTimeout(() => controller.abort(), 15000);
        try {
            const url = new URL(link.href);
            url.searchParams.set('modal', '1');
            const response = await fetch(url, {
                credentials: 'same-origin', cache: 'no-store', signal: controller.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok || response.redirected) throw new Error('Unable to load feedback');
            const html = await response.text();
            if (activeRequest !== controller) return;
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            if (!parsed.querySelector('[data-feedback-report]')) throw new Error('Invalid feedback response');
            content.replaceChildren(...parsed.body.childNodes);
            if (!detailDialog.open) detailDialog.showModal();
            detailDialog.scrollTop = 0;
            content.querySelector('[data-feedback-close]')?.focus({ preventScroll: true });
        } catch {
            if (activeRequest === controller) window.location.assign(link.href);
        } finally {
            window.clearTimeout(timeout);
            if (activeRequest === controller) {
                activeRequest = null;
                window.FutaLoader?.hide();
            }
        }
    });
    detailDialog.addEventListener('close', () => activeTrigger?.focus({ preventScroll: true }));
    window.addEventListener('pagehide', () => {
        activeRequest?.abort();
        activeRequest = null;
    });
}
