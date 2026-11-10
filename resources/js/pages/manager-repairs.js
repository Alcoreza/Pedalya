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

const completionDialog = document.querySelector('[data-completion-dialog]');

document.querySelector('[data-completion-open]')?.addEventListener('click', () => {
    completionDialog?.showModal();
});

completionDialog?.querySelector('[data-completion-close]')?.addEventListener('click', () => {
    completionDialog.close();
});

completionDialog?.addEventListener('click', (event) => {
    if (event.target === completionDialog) completionDialog.close();
});

completionDialog?.querySelector('form')?.addEventListener('submit', () => {
    const button = completionDialog.querySelector('[data-completion-submit]');
    if (!button) return;

    button.disabled = true;
    button.textContent = 'Saving completion…';
});

document.querySelector('[data-notice-dismiss]')?.addEventListener('click', (event) => {
    event.currentTarget.closest('[data-repair-notice]')?.remove();
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    setSidebar(false);
    if (completionDialog?.open) completionDialog.close();
});
