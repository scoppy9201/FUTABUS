const installChatbot = (root) => {
    const panel = root.querySelector('[data-chat-panel]');
    const launcher = root.querySelector('[data-chat-open]');
    const closeButton = root.querySelector('[data-chat-close]');
    const form = root.querySelector('[data-chat-form]');
    const input = root.querySelector('[data-chat-input]');
    const messages = root.querySelector('[data-chat-messages]');
    const suggestions = root.querySelector('[data-chat-suggestions]');
    const feedbackForm = root.querySelector('[data-chat-feedback-form]');
    const feedbackError = root.querySelector('[data-feedback-error]');
    const history = [];

    if (!panel || !launcher || !form || !input || !messages) return;

    const setOpen = (open) => {
        panel.hidden = !open;
        panel.setAttribute('aria-hidden', String(!open));
        launcher.setAttribute('aria-expanded', String(open));
        if (!open) launcher.focus();
    };

    const scrollToLatest = () => { messages.scrollTop = messages.scrollHeight; };

    const addMessage = (text, sender) => {
        const bubble = document.createElement('div');
        bubble.className = `futa-chat__message futa-chat__message--${sender}`;
        bubble.textContent = text;
        messages.append(bubble);
        scrollToLatest();

        return bubble;
    };

    const addAction = (action) => {
        if (!action || typeof action.label !== 'string') return;

        if (action.type === 'feedback') {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'futa-chat__action';
            button.textContent = action.label;
            button.addEventListener('click', () => {
                if (!feedbackForm) return;
                feedbackForm.hidden = false;
                feedbackForm.querySelector('input[name="subject"]')?.focus();
                scrollToLatest();
            });
            messages.append(button);
            scrollToLatest();

            return;
        }

        if (action.type === 'link' && typeof action.url === 'string') {
            const safeUrl = new URL(action.url, window.location.origin);
            if (safeUrl.origin !== window.location.origin) return;

            const link = document.createElement('a');
            link.className = 'futa-chat__action';
            link.href = safeUrl.pathname + safeUrl.search + safeUrl.hash;
            link.textContent = action.label;
            messages.append(link);
            scrollToLatest();
        }
    };

    const sendMessage = async (text) => {
        const message = text.trim();
        if (!message || form.dataset.busy === 'true') return;

        addMessage(message, 'user');
        if (suggestions) suggestions.hidden = true;
        input.value = '';

        if (history.length >= 12) history.splice(0, 2);
        history.push({ role: 'user', text: message });

        form.dataset.busy = 'true';
        form.querySelector('button[type="submit"]').disabled = true;
        const typing = addMessage(root.dataset.typing, 'assistant');
        let errorMessage = root.dataset.error;

        try {
            const response = await fetch(root.dataset.messageUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ locale: root.dataset.locale, messages: history }),
            });
            const payload = await response.json();
            typing.remove();

            if (!response.ok) {
                errorMessage = response.status === 503 ? root.dataset.unavailable : root.dataset.error;
                throw new Error('Chat request failed.');
            }

            const reply = typeof payload.reply === 'string' ? payload.reply : root.dataset.error;
            addMessage(reply, 'assistant');
            history.push({ role: 'assistant', text: reply });
            addAction(payload.action);
        } catch {
            typing.remove();
            if (history.at(-1)?.role === 'user') history.pop();
            addMessage(errorMessage, 'assistant');
        } finally {
            form.dataset.busy = 'false';
            form.querySelector('button[type="submit"]').disabled = false;
            input.focus();
        }
    };

    launcher.addEventListener('click', () => setOpen(panel.hidden));
    closeButton?.addEventListener('click', () => setOpen(false));
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        void sendMessage(input.value);
    });
    suggestions?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-chat-prompt]');
        if (button) void sendMessage(button.dataset.chatPrompt);
    });

    feedbackForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!feedbackForm.reportValidity()) return;

        const submitButton = feedbackForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        feedbackError.hidden = true;

        try {
            const values = Object.fromEntries(new FormData(feedbackForm).entries());
            values.subject = `Chatbot | ${values.subject}`.slice(0, feedbackForm.dataset.mode === 'member' ? 150 : 255);
            const response = await fetch(feedbackForm.dataset.contactUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    ...(feedbackForm.dataset.mode === 'member' ? { 'X-CSRF-TOKEN': feedbackForm.dataset.csrf } : {}),
                },
                body: JSON.stringify(values),
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(root.dataset.feedbackError);

            feedbackForm.hidden = true;
            feedbackForm.reset();
            addMessage(root.dataset.feedbackSuccess.replace(':id', String(payload.data?.reference ?? payload.data?.id ?? '')), 'assistant');
        } catch {
            feedbackError.textContent = root.dataset.feedbackError;
            feedbackError.hidden = false;
            scrollToLatest();
        } finally {
            submitButton.disabled = false;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) setOpen(false);
    });
};

document.querySelectorAll('[data-futa-chat]').forEach(installChatbot);
