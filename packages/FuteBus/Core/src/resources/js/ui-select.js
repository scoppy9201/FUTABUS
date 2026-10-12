import { controlLabel, createControl, dispatchControlChange, observeControl } from './ui-control.js';
import { createPopup } from './ui-popup.js';

const controls = new Map();

function enhanceSelect(source) {
    const control = createControl(source, '#futa-ui-select-template');
    if (!control) return;
    const { trigger, label, panel } = control;
    const template = document.querySelector('#futa-ui-select-template');
    panel.setAttribute('aria-label', controlLabel(source) || template.dataset.choose);
    let buttons = [];
    let optionSignature = null;
    let searchText = '';
    let searchTimer = null;
    const selectedOption = () => buttons.find((button) => button.dataset.optionIndex === String(source.selectedIndex));
    const enabledOptions = () => buttons.filter((button) => !button.disabled);
    const choose = (index) => {
        const option = source.options[index];
        if (source.disabled || !option || option.disabled || option.parentElement.disabled) return;
        dispatchControlChange(source, option.value);
        sync();
        popup.close(true);
    };

    function renderOptions() {
        const signature = [...source.options].map((option) => [option.value, option.label, option.disabled,
            option.hidden, option.parentElement.label, option.parentElement.disabled].join('\u0001')).join('\u0002');
        if (signature === optionSignature) return;
        optionSignature = signature;
        const activeIndex = document.activeElement?.dataset.optionIndex;
        panel.replaceChildren();
        buttons = [];
        let previousGroup = null;
        [...source.options].forEach((option, index) => {
            if (option.hidden) return;
            const group = option.parentElement.tagName === 'OPTGROUP' ? option.parentElement : null;
            if (group && group !== previousGroup) {
                const heading = document.createElement('p');
                heading.className = 'futa-control-group';
                heading.textContent = group.label;
                panel.append(heading);
            }
            previousGroup = group;
            const button = document.createElement('button');
            button.type = 'button';
            button.tabIndex = -1;
            button.className = 'futa-control-option';
            button.setAttribute('role', 'option');
            button.disabled = option.disabled || Boolean(group?.disabled);
            button.dataset.optionIndex = String(index);
            button.textContent = option.label || template.dataset.choose;
            button.addEventListener('click', () => choose(index));
            buttons.push(button);
            panel.append(button);
        });
        if (buttons.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'futa-control-group';
            empty.textContent = template.dataset.noOptions;
            panel.append(empty);
        }
        if (activeIndex !== undefined) buttons.find((button) => button.dataset.optionIndex === activeIndex)?.focus();
    }

    function sync() {
        renderOptions();
        label.textContent = source.selectedOptions[0]?.label || template.dataset.choose;
        trigger.disabled = source.disabled;
        trigger.setAttribute('aria-required', String(source.required));
        if (source.hasAttribute('aria-invalid')) trigger.setAttribute('aria-invalid', source.getAttribute('aria-invalid'));
        buttons.forEach((button) => button.setAttribute('aria-selected', String(button.dataset.optionIndex === String(source.selectedIndex))));
        if (source.disabled) popup.close();
    }

    const popup = createPopup(panel, trigger, {
        onOpen() {
            sync();
            queueMicrotask(() => {
                const selected = selectedOption();
                (selected && !selected.disabled ? selected : enabledOptions()[0])?.focus({ preventScroll: true });
            });
        },
        onClose() { searchText = ''; window.clearTimeout(searchTimer); },
    });
    trigger.addEventListener('click', () => popup.toggle());
    trigger.addEventListener('keydown', (event) => {
        if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
            event.preventDefault();
            popup.open();
            queueMicrotask(() => {
                const options = enabledOptions();
                (event.key === 'End' || event.key === 'ArrowUp' ? options.at(-1) : options[0])?.focus();
            });
        }
    });
    panel.addEventListener('keydown', (event) => {
        const options = enabledOptions();
        const index = options.indexOf(document.activeElement);
        let next;
        if (event.key === 'Enter' || event.key === ' ') {
            if (index < 0) return;
            event.preventDefault();
            choose(Number(options[index].dataset.optionIndex));
            return;
        }
        if (event.key === 'ArrowDown') next = options[(index + 1) % options.length];
        else if (event.key === 'ArrowUp') next = options[(index - 1 + options.length) % options.length];
        else if (event.key === 'Home') next = options[0];
        else if (event.key === 'End') next = options.at(-1);
        else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
            searchText += event.key.toLocaleLowerCase();
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => { searchText = ''; }, 600);
            const ordered = [...options.slice(index + 1), ...options.slice(0, index + 1)];
            next = ordered.find((button) => button.textContent.trim().toLocaleLowerCase().startsWith(searchText));
        } else return;
        event.preventDefault();
        next?.focus();
    });
    const stopObserving = observeControl(source, sync);
    controls.set(source, { popup, stopObserving });
}

export function initializeUiSelects(root = document) {
    root.querySelectorAll('select:not([multiple]):not([data-ui-enhanced])').forEach(enhanceSelect);
    for (const [source, control] of controls) {
        if (source.isConnected) continue;
        control.popup.destroy();
        control.stopObserving();
        controls.delete(source);
    }
}
