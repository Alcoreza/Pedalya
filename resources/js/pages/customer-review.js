const reviewForm = document.querySelector('[data-review-form]');

if (reviewForm) {
    const consent = reviewForm.elements.consent;
    const message = reviewForm.querySelector('[data-review-message]');

    reviewForm.addEventListener('submit', (event) => {
        if (consent.checked) return;

        event.preventDefault();
        reviewForm.querySelector('[data-error-for="consent"]').textContent = 'Please confirm your booking details.';
        message.textContent = 'Confirm the booking details before continuing.';
        consent.focus();
    });

    consent.addEventListener('change', () => {
        if (consent.checked) {
            reviewForm.querySelector('[data-error-for="consent"]').textContent = '';
            message.textContent = '';
        }
    });
}
