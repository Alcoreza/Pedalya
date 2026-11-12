const bookingList = document.querySelector('[data-booking-list]');

if (bookingList) {
    const filters = [...document.querySelectorAll('[data-filter]')];
    const cards = [...bookingList.querySelectorAll('[data-booking-card]')];
    const emptyState = bookingList.querySelector('[data-booking-empty]');
    let filterAnimationFrame;

    const replayStatusAnimation = (card) => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('animate' in Element.prototype)) return;

        card.querySelectorAll('.booking-status-item').forEach((item, index) => {
            item.getAnimations?.().forEach((animation) => animation.cancel());

            item.animate([
                { opacity: 0, transform: 'translateY(-.75rem)' },
                { opacity: 1, transform: 'translateY(.16rem)', offset: .58 },
                { opacity: 1, transform: 'translateY(0)' },
            ], {
                duration: 420,
                delay: 180 + (index * 130),
                easing: 'cubic-bezier(.22, 1, .36, 1)',
                fill: 'both',
            });

            const label = item.querySelector('.status-label');
            label?.getAnimations?.().forEach((animation) => animation.cancel());
            label?.animate([
                { opacity: 0, transform: 'translateY(-.65rem) rotate(1.5deg)', filter: 'blur(2px)' },
                { opacity: 1, transform: 'translateY(.12rem) rotate(-.25deg)', filter: 'blur(0)', offset: .58 },
                { opacity: 1, transform: 'translateY(0) rotate(0)', filter: 'blur(0)' },
            ], {
                duration: 520,
                delay: 250 + (index * 130),
                easing: 'cubic-bezier(.22, 1, .36, 1)',
                fill: 'both',
            });
        });
    };

    filters.forEach((filter) => {
        filter.addEventListener('click', () => {
            const selectedFilter = filter.dataset.filter;
            let visibleCount = 0;

            filters.forEach((item) => item.classList.toggle('is-active', item === filter));
            if (filterAnimationFrame) window.cancelAnimationFrame(filterAnimationFrame);
            cards.forEach((card) => {
                const visible = selectedFilter === 'all' || card.dataset.group === selectedFilter;
                const statusItems = [...card.querySelectorAll('.booking-status-item')];
                card.hidden = !visible;
                if (visible) {
                    visibleCount += 1;
                    card.classList.remove('is-filtering-in');
                    statusItems.forEach((item) => item.classList.remove('is-status-refreshing'));
                    void card.offsetWidth;
                    filterAnimationFrame = window.requestAnimationFrame(() => {
                        card.classList.add('is-filtering-in');
                        replayStatusAnimation(card);
                    });
                }
            });
            emptyState.hidden = visibleCount > 0;
        });
    });
}
