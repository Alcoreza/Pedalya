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

const dashboardSearch = document.querySelector('[data-dashboard-search]');
const searchOpen = document.querySelector('[data-dashboard-search-open]');
const searchInput = dashboardSearch?.querySelector('input');

const setSearch = (isVisible) => {
    if (!dashboardSearch) return;
    dashboardSearch.hidden = !isVisible;
    if (isVisible) requestAnimationFrame(() => searchInput?.focus());
};

searchOpen?.addEventListener('click', () => setSearch(true));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setSearch(false);
        setSidebar(false);
    }

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        setSearch(true);
    }
});

const requestFilters = [...document.querySelectorAll('[data-request-filter]')];
const requestRows = [...document.querySelectorAll('[data-request-status]')];

requestFilters.forEach((filter) => {
    filter.addEventListener('click', () => {
        const selectedStatus = filter.dataset.requestFilter;

        requestFilters.forEach((item) => item.classList.toggle('is-active', item === filter));
        requestRows.forEach((row) => {
            const shouldShow = selectedStatus === 'all' || row.dataset.requestStatus === selectedStatus;
            row.classList.toggle('is-filtered-out', !shouldShow);
        });
    });
});

const dayPeriod = document.querySelector('[data-day-period]');

if (dayPeriod) {
    const hour = new Date().getHours();
    dayPeriod.textContent = hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening';
}
