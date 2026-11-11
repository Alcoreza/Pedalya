const reviewForm = document.querySelector('[data-review-form]');

if (reviewForm) {
    const serviceNames = {
        'brake-inspection': { name: 'Brake inspection', duration: '45 min', price: '₱500' },
        'wheel-alignment': { name: 'Wheel alignment', duration: '60 min', price: '₱650' },
        'full-tune-up': { name: 'Full tune-up', duration: '90 min', price: '₱1,200' },
        'flat-tire-repair': { name: 'Flat tire repair', duration: '30 min', price: '₱350' },
    };
    const selectedService = serviceNames[window.sessionStorage.getItem('pedalya.selectedService')] ?? serviceNames['brake-inspection'];
    const selectedSchedule = JSON.parse(window.sessionStorage.getItem('pedalya.selectedSchedule') ?? '{"date":"Tue, Mar 17","time":"9:00 AM"}');

    document.querySelector('[data-summary-service]').textContent = selectedService.name;
    document.querySelector('[data-summary-duration]').textContent = selectedService.duration;
    document.querySelector('[data-summary-price]').textContent = selectedService.price;
    document.querySelector('[data-summary-date]').textContent = `${selectedSchedule.date} · ${selectedSchedule.time}`;

    reviewForm.addEventListener('submit', (event) => {
        event.preventDefault();
        let valid = true;
        const message = reviewForm.querySelector('[data-review-message]');

        reviewForm.querySelectorAll('[data-error-for]').forEach((error) => {
            error.textContent = '';
            error.closest('.review-field, .review-consent')?.classList.remove('has-error');
        });

        ['name', 'phone', 'email'].forEach((fieldName) => {
            const field = reviewForm.elements[fieldName];
            if (!field.value.trim()) {
                const error = reviewForm.querySelector(`[data-error-for="${fieldName}"]`);
                error.textContent = 'This field is required.';
                error.closest('.review-field')?.classList.add('has-error');
                valid = false;
            }
        });

        const email = reviewForm.elements.email;
        if (email.value.trim() && !email.validity.valid) {
            const error = reviewForm.querySelector('[data-error-for="email"]');
            error.textContent = 'Enter a valid email address.';
            error.closest('.review-field')?.classList.add('has-error');
            valid = false;
        }

        if (!reviewForm.elements.consent.checked) {
            reviewForm.querySelector('[data-error-for="consent"]').textContent = 'Please confirm your booking details.';
            valid = false;
        }

        if (!valid) {
            message.textContent = 'Please review the highlighted fields.';
            return;
        }

        window.sessionStorage.setItem('pedalya.customerDetails', JSON.stringify({
            name: reviewForm.elements.name.value.trim(),
            phone: reviewForm.elements.phone.value.trim(),
            email: reviewForm.elements.email.value.trim(),
            notes: reviewForm.elements.notes.value.trim(),
        }));
        window.pedalyaAdvanceBooking?.('/book/confirmation');
    });
}
