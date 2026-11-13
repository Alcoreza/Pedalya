import './bootstrap';

const customerPage = document.querySelector('.customer-booking-page');

if (customerPage) {
    customerPage.classList.add('customer-motion-ready');
    const menuToggle = customerPage.querySelector('.customer-menu-toggle');
    const customerNav = customerPage.querySelector('.customer-nav');

    const closeCustomerMenu = () => {
        if (!menuToggle || !customerNav) return;
        menuToggle.setAttribute('aria-expanded', 'false');
        customerNav.classList.remove('is-open');
        customerPage.classList.remove('customer-menu-open');
    };

    menuToggle?.addEventListener('click', () => {
        const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
        menuToggle.setAttribute('aria-expanded', String(!isOpen));
        customerNav?.classList.toggle('is-open', !isOpen);
        customerPage.classList.toggle('customer-menu-open', !isOpen);
    });

    customerNav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeCustomerMenu));
    window.addEventListener('resize', () => {
        if (window.innerWidth > 820) closeCustomerMenu();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menuToggle?.getAttribute('aria-expanded') === 'true') {
            closeCustomerMenu();
            menuToggle.focus();
        }
    });

    const revealItems = [...customerPage.querySelectorAll('main > *')];
    const nestedRevealItems = [...customerPage.querySelectorAll([
        '.booking-record',
        '.bookings-summary > div',
        '.booking-filter',
        '.service-card',
        '.detail-card',
        '.dashboard-stats article',
        '.dashboard-panel',
    ].join(','))];
    const motionItems = [...new Set([...revealItems, ...nestedRevealItems])];

    motionItems.forEach((item, index) => {
        item.classList.add('customer-reveal');
        item.style.setProperty('--reveal-delay', `${Math.min(index * 55, 480)}ms`);
    });

    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -32px' });

        motionItems.forEach((item) => revealObserver.observe(item));
    } else {
        motionItems.forEach((item) => item.classList.add('is-visible'));
    }

    const interactiveItems = customerPage.querySelectorAll([
        'button',
        '.service-card',
        '.booking-record',
        '.dashboard-next-card',
        '.dashboard-progress-card',
        '.dashboard-panel',
        '.payment-card',
        '.detail-card',
        '.confirmation-card',
        '.result-card',
        '.repair-choice-card',
        '.date-card',
        '.time-slot',
    ].join(','));

    interactiveItems.forEach((item) => item.classList.add('customer-interactive'));

    window.pedalyaAdvanceBooking = (destination) => {
        const progress = customerPage.querySelector('.booking-steps');
        const activeStep = progress?.querySelector('.booking-step.is-active');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!progress || !activeStep || reducedMotion) {
            window.location.href = destination;
            return;
        }

        progress.classList.add('is-advancing');
        activeStep.classList.add('is-advancing');
        customerPage.classList.add('customer-page-leaving');
        window.setTimeout(() => {
            window.location.href = destination;
        }, 280);
    };

    customerPage.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || link.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const destination = new URL(link.href, window.location.href);
        const isCustomerRoute = destination.origin === window.location.origin
            && (destination.pathname.startsWith('/customer') || destination.pathname.startsWith('/book'));

        if (!isCustomerRoute || destination.href === window.location.href || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        event.preventDefault();
        customerPage.classList.add('customer-page-leaving');
        window.setTimeout(() => {
            window.location.href = destination.href;
        }, 220);
    });
}
