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

const transitionDialog = document.querySelector('[data-transition-dialog]');

document.querySelector('[data-transition-open]')?.addEventListener('click', () => {
    transitionDialog?.showModal();
});

transitionDialog?.querySelector('[data-transition-close]')?.addEventListener('click', () => {
    transitionDialog.close();
});

transitionDialog?.addEventListener('click', (event) => {
    if (event.target === transitionDialog) transitionDialog.close();
});

transitionDialog?.querySelector('form')?.addEventListener('submit', () => {
    const button = transitionDialog.querySelector('[data-transition-submit]');
    if (!button) return;

    button.disabled = true;
    button.textContent = 'Saving update…';
});

document.querySelector('[data-notice-dismiss]')?.addEventListener('click', (event) => {
    event.currentTarget.closest('[data-repair-notice]')?.remove();
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    setSidebar(false);
    if (transitionDialog?.open) transitionDialog.close();
});
