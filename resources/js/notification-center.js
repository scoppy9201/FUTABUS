const DEFAULT_DURATION = 5000;
const MAX_VISIBLE = 3;
const activeNotifications = [];

function notificationHost() {
    let host = document.getElementById('futa-notifications');
    if (host) return host;

    host = document.createElement('div');
    host.id = 'futa-notifications';
    host.className = 'futa-notifications';
    host.setAttribute('aria-label', document.documentElement.lang === 'vi' ? 'Thông báo hệ thống' : 'Notifications');
    document.body.append(host);
    return host;
}

function showNotification(message, options = {}) {
    if (typeof message !== 'string' || !message.trim()) return () => {};

    const host = notificationHost();
    const tone = ['info', 'success', 'warning', 'error'].includes(options.tone) ? options.tone : 'info';
    const requestedDuration = Number(options.duration);
    const duration = Number.isFinite(requestedDuration) && requestedDuration > 0
        ? Math.min(10000, Math.max(1500, requestedDuration))
        : DEFAULT_DURATION;
    const locale = document.documentElement.lang === 'vi' ? 'vi' : 'en';
    const title = options.title || (locale === 'vi' ? 'Thông báo hệ thống' : 'Notification');
    const closeLabel = locale === 'vi' ? 'Đóng thông báo' : 'Dismiss notification';
    const key = options.key == null ? null : String(options.key);

    if (key !== null) {
        activeNotifications.find((entry) => entry.key === key)?.remove();
    }
    while (activeNotifications.length >= MAX_VISIBLE) activeNotifications[0].remove();

    const toast = document.createElement('div');
    toast.className = 'futa-notification is-' + tone;
    toast.setAttribute('role', tone === 'warning' || tone === 'error' ? 'alert' : 'status');
    toast.style.setProperty('--notification-duration', duration + 'ms');

    const icon = document.createElement('img');
    icon.className = 'futa-notification__icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.src = '/icons/notifications/' + tone + '.svg';
    icon.alt = '';

    const content = document.createElement('div');
    content.className = 'futa-notification__content';
    const heading = document.createElement('strong');
    heading.className = 'futa-notification__title';
    heading.textContent = String(title);
    const body = document.createElement('p');
    body.className = 'futa-notification__message';
    body.textContent = message.trim();
    content.append(heading, body);

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'futa-notification__close';
    closeButton.setAttribute('aria-label', closeLabel);
    const closeIcon = document.createElement('img');
    closeIcon.src = '/icons/notifications/close.svg';
    closeIcon.alt = '';
    closeIcon.setAttribute('aria-hidden', 'true');
    closeButton.append(closeIcon);

    let timeout;
    let removalTimeout;
    let dismissed = false;
    const entry = {
        key,
        remove() {
            clearTimeout(timeout);
            clearTimeout(removalTimeout);
            toast.remove();
            const index = activeNotifications.indexOf(entry);
            if (index !== -1) activeNotifications.splice(index, 1);
        },
    };
    function dismiss() {
        if (dismissed) return;
        dismissed = true;
        clearTimeout(timeout);
        toast.classList.add('is-leaving');
        removalTimeout = setTimeout(() => entry.remove(), 180);
    }

    closeButton.addEventListener('click', dismiss);
    toast.append(icon, content, closeButton);
    host.append(toast);
    activeNotifications.push(entry);
    timeout = setTimeout(dismiss, duration);
    return dismiss;
}

window.FutaNotify = {
    show: showNotification,
    info: (message, options) => showNotification(message, { ...options, tone: 'info' }),
    success: (message, options) => showNotification(message, { ...options, tone: 'success' }),
    warning: (message, options) => showNotification(message, { ...options, tone: 'warning' }),
    error: (message, options) => showNotification(message, { ...options, tone: 'error' }),
    close: (key) => activeNotifications.find((entry) => entry.key === String(key))?.remove(),
};
