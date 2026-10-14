<script>
window.tripManagementLiveSearch = (() => {
    const pending = new WeakMap();
    const requests = new WeakMap();

    return (form, resultsId) => {
        clearTimeout(pending.get(form));
        pending.set(form, setTimeout(async () => {
            requests.get(form)?.abort();
            const controller = new AbortController();
            requests.set(form, controller);

            const url = new URL(form.action, window.location.href);
            new FormData(form).forEach((value, key) => {
                if (value !== '') url.searchParams.set(key, value);
                else url.searchParams.delete(key);
            });
            url.searchParams.delete('page');

            try {
                const response = await fetch(url, {
                    signal: controller.signal,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) return;

                const html = await response.text();
                const nextPage = new DOMParser().parseFromString(html, 'text/html');
                const currentResults = document.getElementById(resultsId);
                const nextResults = nextPage.getElementById(resultsId);
                if (!currentResults || !nextResults || requests.get(form) !== controller) return;

                currentResults.innerHTML = nextResults.innerHTML;
                const countId = `${resultsId}-count`;
                const currentCount = document.getElementById(countId);
                const nextCount = nextPage.getElementById(countId);
                if (currentCount && nextCount) currentCount.textContent = nextCount.textContent;
                window.history.replaceState({}, '', url);
            } catch (error) {
                if (error.name !== 'AbortError') console.error('Trip list filtering failed.', error);
            }
        }, 250));
    };
})();
</script>
