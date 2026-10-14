export function createDetailLoader(fetchDetails, render) {
    let revision = 0;
    let controller = null;
    return {
        async load(url) {
            const current = ++revision;
            controller?.abort();
            controller = new AbortController();
            const requestController = controller;
            const timeout = setTimeout(() => requestController.abort(), 15000);
            render({ state: 'loading' });
            try {
                const response = await fetchDetails(url, { signal: controller.signal, credentials: 'same-origin', headers: { Accept: 'text/html' } });
                if (!response.ok || response.redirected) throw new Error('Unavailable booking');
                const html = await response.text();
                if (current === revision) render({ state: 'ready', html });
            } catch (error) {
                if (current === revision) render({ state: 'error' });
            } finally {
                clearTimeout(timeout);
            }
        },
        cancel() { revision++; controller?.abort(); },
    };
}
