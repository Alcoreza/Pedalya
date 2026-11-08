const header = document.querySelector('[data-site-header]');
const menuToggle = document.querySelector('[data-menu-toggle]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

const updateHeader = () => {
    header?.classList.toggle('is-scrolled', window.scrollY > 18);
};

updateHeader();
window.addEventListener('scroll', updateHeader, { passive: true });

if (menuToggle && mobileMenu) {
    const closeMenu = () => {
        menuToggle.setAttribute('aria-expanded', 'false');
        mobileMenu.hidden = true;
        document.body.classList.remove('menu-open');
    };

    menuToggle.addEventListener('click', () => {
        const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
        menuToggle.setAttribute('aria-expanded', String(!isOpen));
        mobileMenu.hidden = isOpen;
        document.body.classList.toggle('menu-open', !isOpen);
    });

    mobileMenu.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));

    window.addEventListener('resize', () => {
        if (window.innerWidth > 820) closeMenu();
    });
}

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const revealItems = document.querySelectorAll('.reveal');

if (reducedMotion || !('IntersectionObserver' in window)) {
    revealItems.forEach((item) => item.classList.add('is-visible'));
} else {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.14, rootMargin: '0px 0px -50px' },
    );

    revealItems.forEach((item) => observer.observe(item));
}
