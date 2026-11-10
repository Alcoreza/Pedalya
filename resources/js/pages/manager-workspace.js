const managerPagePath = window.location.pathname;
const managerScrollKey = `pedalya:manager-scroll:${managerPagePath}`;
const managerScrollLifetime = 60_000;

const saveManagerScrollPosition = () => {
    try {
        sessionStorage.setItem(managerScrollKey, JSON.stringify({
            x: window.scrollX,
            y: window.scrollY,
            savedAt: Date.now(),
        }));
    } catch {
        // The page still works normally if browser storage is unavailable.
    }
};

const readManagerScrollPosition = () => {
    try {
        const storedPosition = sessionStorage.getItem(managerScrollKey);
        sessionStorage.removeItem(managerScrollKey);

        if (!storedPosition) return null;

        const position = JSON.parse(storedPosition);
        const isRecent = Date.now() - position.savedAt <= managerScrollLifetime;
        const isValid = Number.isFinite(position.x) && Number.isFinite(position.y);

        return isRecent && isValid ? position : null;
    } catch {
        return null;
    }
};

const navigationEntry = performance.getEntriesByType('navigation')[0];
const storedPosition = readManagerScrollPosition();
const savedPosition = navigationEntry?.type === 'back_forward' ? null : storedPosition;

if (savedPosition) {
    const restoreManagerScrollPosition = () => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                window.scrollTo({
                    left: savedPosition.x,
                    top: savedPosition.y,
                    behavior: 'auto',
                });
            });
        });
    };

    restoreManagerScrollPosition();

    if (document.readyState !== 'complete') {
        window.addEventListener('load', restoreManagerScrollPosition, { once: true });
    }
}

document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    const link = event.target instanceof Element
        ? event.target.closest('a[href]')
        : null;

    if (!link || link.target === '_blank' || link.hasAttribute('download')) return;

    const destination = new URL(link.href, window.location.href);
    const changesCurrentView = destination.origin === window.location.origin
        && destination.pathname === managerPagePath
        && destination.search !== window.location.search;

    if (changesCurrentView) saveManagerScrollPosition();
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) return;

    const destination = new URL(form.action || window.location.href, window.location.href);

    if (destination.origin === window.location.origin && destination.pathname.startsWith('/manager/')) {
        saveManagerScrollPosition();
    }
});
