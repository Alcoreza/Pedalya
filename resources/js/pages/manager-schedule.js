const managerShell = document.querySelector('[data-manager-shell]');
const sidebarOpen = document.querySelector('[data-sidebar-open]');
const sidebarClose = document.querySelector('[data-sidebar-close]');
const sidebarOverlay = document.querySelector('[data-manager-overlay]');

const setSidebar = (isVisible) => {
    if (!managerShell || !sidebarOpen) return;

    managerShell.classList.toggle('sidebar-visible', isVisible);
    sidebarOpen.setAttribute('aria-expanded', String(isVisible));
    document.body.style.overflow = isVisible ? 'hidden' : '';
};

sidebarOpen?.addEventListener('click', () => setSidebar(true));
sidebarClose?.addEventListener('click', () => setSidebar(false));
sidebarOverlay?.addEventListener('click', () => setSidebar(false));

window.addEventListener('resize', () => {
    if (window.innerWidth > 820) setSidebar(false);
});

document.querySelector('[data-sync-form]')?.addEventListener('submit', (event) => {
    const button = event.currentTarget.querySelector('button[type="submit"]');
    if (!button) return;

    button.disabled = true;
    button.innerHTML = 'Queueing retry <span aria-hidden="true">↻</span>';
});

document.querySelector('[data-notice-dismiss]')?.addEventListener('click', (event) => {
    event.currentTarget.closest('[data-schedule-notice]')?.remove();
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setSidebar(false);
});
