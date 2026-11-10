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

const decisionDialog = document.querySelector('[data-decision-dialog]');
const decisionValue = decisionDialog?.querySelector('[data-decision-value]');
const decisionTitle = decisionDialog?.querySelector('[data-dialog-title]');
const decisionCopy = decisionDialog?.querySelector('[data-dialog-copy]');
const decisionSubmit = decisionDialog?.querySelector('[data-decision-submit]');

const decisionContent = {
    confirmed: {
        title: 'Confirm this booking?',
        copy: 'The booking decision will be saved first. Google Calendar synchronization remains a separate operation and may still be pending or fail.',
        submit: 'Confirm booking',
    },
    rejected: {
        title: 'Reject this request?',
        copy: 'This request will be marked rejected and must not create a confirmed Google Calendar appointment.',
        submit: 'Reject request',
    },
};

document.querySelectorAll('[data-decision-open]').forEach((button) => {
    button.addEventListener('click', () => {
        if (!decisionDialog || !decisionValue || !decisionTitle || !decisionCopy || !decisionSubmit) return;

        const decision = button.dataset.decisionOpen;
        const content = decisionContent[decision];
        if (!content) return;

        decisionValue.value = decision;
        decisionTitle.textContent = content.title;
        decisionCopy.textContent = content.copy;
        decisionSubmit.textContent = content.submit;
        decisionDialog.classList.toggle('is-rejecting', decision === 'rejected');
        decisionDialog.showModal();
    });
});

decisionDialog?.querySelector('[data-decision-close]')?.addEventListener('click', () => {
    decisionDialog.close();
});

decisionDialog?.addEventListener('click', (event) => {
    if (event.target === decisionDialog) decisionDialog.close();
});

decisionDialog?.querySelector('form')?.addEventListener('submit', () => {
    if (!decisionSubmit) return;

    decisionSubmit.disabled = true;
    decisionSubmit.textContent = 'Saving decision…';
});

document.querySelector('[data-notice-dismiss]')?.addEventListener('click', (event) => {
    event.currentTarget.closest('[data-booking-notice]')?.remove();
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    setSidebar(false);
    if (decisionDialog?.open) decisionDialog.close();
});
