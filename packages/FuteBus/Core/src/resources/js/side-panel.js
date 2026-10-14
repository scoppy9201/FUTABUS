const requestedStates = new WeakMap();

export function syncSidePanel(panel, visible, schedule = queueMicrotask) {
    requestedStates.set(panel, visible);
    if (!visible) {
        if (panel.open) panel.close();
        return;
    }
    schedule(() => {
        if (panel.isConnected && requestedStates.get(panel) && !panel.open) panel.showModal();
    });
}

if (typeof window !== 'undefined') window.FutaSidePanel = { sync: syncSidePanel };
