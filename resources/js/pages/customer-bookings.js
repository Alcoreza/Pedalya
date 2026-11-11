const bookingList = document.querySelector('[data-booking-list]');

if (bookingList) {
    const filters = [...document.querySelectorAll('[data-filter]')];
    const cards = [...bookingList.querySelectorAll('[data-booking-card]')];
    const emptyState = bookingList.querySelector('[data-booking-empty]');

    filters.forEach((filter) => {
        filter.addEventListener('click', () => {
            const selectedFilter = filter.dataset.filter;
            let visibleCount = 0;

            filters.forEach((item) => item.classList.toggle('is-active', item === filter));
            cards.forEach((card) => {
                const visible = selectedFilter === 'all' || card.dataset.group === selectedFilter;
                card.hidden = !visible;
                if (visible) {
                    visibleCount += 1;
                    card.classList.remove('is-filtering-in');
                    window.requestAnimationFrame(() => card.classList.add('is-filtering-in'));
                }
            });
            emptyState.hidden = visibleCount > 0;
        });
    });
}
