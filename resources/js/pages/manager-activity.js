const managerShell = document.querySelector('[data-manager-shell]');
const sidebarOpen = document.querySelector('[data-sidebar-open]');
const sidebarClose = document.querySelector('[data-sidebar-close]');
const managerOverlay = document.querySelector('[data-manager-overlay]');

const setNavigationState = (isOpen) => {
    if (!managerShell) return;

    managerShell.classList.toggle('is-sidebar-open', isOpen);
    sidebarOpen?.setAttribute('aria-expanded', String(isOpen));
    document.body.classList.toggle('has-manager-menu', isOpen);
};

sidebarOpen?.addEventListener('click', () => setNavigationState(true));
sidebarClose?.addEventListener('click', () => setNavigationState(false));
managerOverlay?.addEventListener('click', () => setNavigationState(false));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setNavigationState(false);
});

window.addEventListener('resize', () => {
    if (window.innerWidth > 1024) setNavigationState(false);
});
