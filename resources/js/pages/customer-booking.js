const serviceForm = document.querySelector('[data-service-form]');

if (serviceForm) {
    const serviceInputs = [...serviceForm.querySelectorAll('[data-service-input]')];
    const nextButton = serviceForm.querySelector('[data-next-step]');
    const message = serviceForm.querySelector('[data-selection-message]');

    serviceInputs.forEach((input) => {
        input.addEventListener('change', () => {
            nextButton.disabled = false;
            message.textContent = 'Service selected. Continue when you’re ready.';
        });
    });

    nextButton.addEventListener('click', () => {
        const selected = serviceForm.querySelector('[data-service-input]:checked');

        if (!selected) return;

        window.sessionStorage.setItem('pedalya.selectedService', selected.value);
        window.pedalyaAdvanceBooking?.('/book/schedule');
    });
}
