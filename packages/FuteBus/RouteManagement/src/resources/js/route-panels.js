function initializeRoutePanels() {
    document.querySelectorAll('[data-record-panel]').forEach(panel => {
        const kind = panel.dataset.recordPanel;
        const form = panel.querySelector('[data-record-form]');
        const defaults = kind === 'route'
            ? { vehicle_type: 'Limousine', is_active: '1' }
            : { position: form.dataset.position, leg_minutes: Number(form.dataset.position) === 1 ? 0 : 60, stop_type: 'both' };
        let originalPosition = 0;
        function updateStopTiming() {
            if (kind !== 'stop') return;
            const first = Number(form.elements.namedItem('position').value) === 1;
            const nextRequired = first && Number(form.dataset.position) > 1 && originalPosition !== 1;
            const next = form.elements.namedItem('next_leg_minutes');
            panel.querySelector('[data-next-leg-field]').hidden = !nextRequired;
            next.disabled = !nextRequired;
            next.required = nextRequired;
            const leg = form.elements.namedItem('leg_minutes');
            leg.min = first ? '0' : '1';
            if (first) leg.value = '0';
        }
        function open(record = {}) {
            form.reset();
            for (const [name, value] of Object.entries({ ...defaults, ...record })) {
                const field = form.elements.namedItem(name);
                if (!field || field.type === 'hidden') continue;
                field.value = value ?? '';
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
            const editing = Number(record.id) > 0;
            if (kind === 'stop') {
                originalPosition = Number(record.stop_order || 0);
                form.elements.namedItem('_original_position').value = originalPosition;
                form.elements.namedItem('position').max = String(Number(form.dataset.position) - (editing ? 1 : 0));
                updateStopTiming();
            }
            form.elements.namedItem('_method').disabled = !editing;
            form.elements.namedItem('_record').value = editing ? record.id : '';
            form.action = editing ? `${form.dataset.base}/${Number(record.id)}` : form.dataset.base;
            panel.querySelector('h2').textContent = editing ? panel.dataset.editTitle : panel.dataset.createTitle;
            if (!panel.open) panel.showModal();
        }
        document.querySelectorAll(`[data-${kind}-open]`).forEach(button => {
            button.addEventListener('click', () => open(JSON.parse(button.dataset.record || '{}')));
        });
        if (kind === 'stop') form.elements.namedItem('position').addEventListener('input', updateStopTiming);
        panel.querySelectorAll('[data-panel-close]').forEach(button => button.addEventListener('click', () => panel.close()));
        panel.addEventListener('click', event => {
            if (event.target !== panel) return;
            const bounds = panel.getBoundingClientRect();
            if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) panel.close();
        });
        if (form.dataset.reopen === '1') open({ ...JSON.parse(form.dataset.initial), id: Number(form.dataset.recordId) });
    });
    const flash = document.querySelector('[data-route-flash]');
    if (flash && window.FutaNotify) {
        window.FutaNotify.success(flash.dataset.message, { key: 'route-flash' });
        flash.hidden = true;
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeRoutePanels, { once: true });
} else {
    initializeRoutePanels();
}
