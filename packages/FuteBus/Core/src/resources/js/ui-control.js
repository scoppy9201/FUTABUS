let controlSequence = 0;
const validationClearers = new WeakMap();

export function controlLabel(source) {
    return source.getAttribute('aria-label') || [...(source.labels ?? [])].map((label) => label.textContent.trim()).join(' ')
        || source.getAttribute('placeholder') || source.name;
}

export function createControl(source, templateSelector) {
    const template = document.querySelector(templateSelector);
    if (!template || source.dataset.uiEnhanced) return null;
    const root = template.content.firstElementChild.cloneNode(true);
    const trigger = root.querySelector('[data-ui-trigger]');
    const label = root.querySelector('[data-ui-value]');
    const panel = root.querySelector('[data-ui-panel]');
    const error = root.querySelector('[data-ui-error]');
    const id = `futa-control-${++controlSequence}`;
    root.className += ` ${source.className}`;
    root.classList.add('futa-control');
    source.before(root);
    root.prepend(source);
    source.classList.add('futa-control__source');
    source.dataset.uiEnhanced = 'true';
    source.tabIndex = -1;
    source.setAttribute('aria-hidden', 'true');
    trigger.id = `${id}-trigger`;
    panel.id = `${id}-panel`;
    error.id = `${id}-error`;
    trigger.setAttribute('aria-label', controlLabel(source) || template.dataset.choose);
    trigger.setAttribute('aria-describedby', `${source.getAttribute('aria-describedby') || ''} ${error.id}`.trim());
    let generatedError = false;
    let hadServerError = false;
    const clearValidation = (actualEdit = false) => {
        if (!source.validity.valid) return;
        if (generatedError) {
            generatedError = false;
            error.hidden = true;
            error.textContent = '';
            const invalid = hadServerError && !actualEdit;
            source.setAttribute('aria-invalid', String(invalid));
            trigger.setAttribute('aria-invalid', String(invalid));
        } else if (actualEdit) {
            source.setAttribute('aria-invalid', 'false');
            trigger.setAttribute('aria-invalid', 'false');
        }
    };
    validationClearers.set(source, clearValidation);
    source.addEventListener('focus', () => trigger.focus());
    source.addEventListener('mousedown', (event) => event.preventDefault());
    source.addEventListener('click', (event) => { event.preventDefault(); trigger.focus(); });
    source.addEventListener('invalid', (event) => {
        event.preventDefault();
        if (!generatedError) hadServerError = source.getAttribute('aria-invalid') === 'true';
        generatedError = true;
        error.textContent = source.validationMessage;
        error.hidden = false;
        source.setAttribute('aria-invalid', 'true');
        trigger.setAttribute('aria-invalid', 'true');
        trigger.focus();
    });
    source.addEventListener('change', () => clearValidation(true));
    return { root, trigger, label, panel, error };
}

export function dispatchControlChange(source, value) {
    source.value = value;
    source.setAttribute('aria-invalid', String(!source.validity.valid));
    source.dispatchEvent(new Event('input', { bubbles: true }));
    source.dispatchEvent(new Event('change', { bubbles: true }));
}

export function observeControl(source, callback) {
    let queued = false;
    let stopped = false;
    let observedValue = source.value;
    const restorers = [];
    const observedOptions = new WeakSet();
    const schedule = () => {
        if (queued || stopped) return;
        queued = true;
        queueMicrotask(() => {
            queued = false;
            if (stopped) return;
            if (source.value !== observedValue) {
                validationClearers.get(source)?.();
                observedValue = source.value;
            }
            callback();
        });
    };
    const observeProperty = (element, property) => {
        if (Object.hasOwn(element, property)) return;
        let prototype = Object.getPrototypeOf(element);
        let descriptor;
        while (prototype && !descriptor) {
            descriptor = Object.getOwnPropertyDescriptor(prototype, property);
            prototype = Object.getPrototypeOf(prototype);
        }
        if (!descriptor?.set || !descriptor.get) return;
        Object.defineProperty(element, property, {
            configurable: true,
            get() { return descriptor.get.call(this); },
            set(value) { descriptor.set.call(this, value); schedule(); },
        });
        restorers.push(() => { delete element[property]; });
    };
    const observeOptions = () => {
        for (const option of source.options ?? []) {
            if (observedOptions.has(option)) continue;
            observedOptions.add(option);
            observeProperty(option, 'selected');
        }
    };
    ['value', 'disabled', 'readOnly', 'selectedIndex'].forEach((property) => observeProperty(source, property));
    observeOptions();
    const observer = new MutationObserver(() => { observeOptions(); schedule(); });
    observer.observe(source, { attributes: true, childList: true, characterData: true, subtree: true });
    source.addEventListener('input', schedule);
    source.addEventListener('change', schedule);
    source.form?.addEventListener('reset', schedule);
    callback();
    return () => {
        stopped = true;
        observer.disconnect();
        restorers.forEach((restore) => restore());
        source.removeEventListener('input', schedule);
        source.removeEventListener('change', schedule);
        source.form?.removeEventListener('reset', schedule);
    };
}
