const schedulePage = document.querySelector('[data-schedule-panel]');

if (schedulePage) {
    const serviceNames = {
        'brake-inspection': 'Brake inspection',
        'wheel-alignment': 'Wheel alignment',
        'full-tune-up': 'Full tune-up',
        'flat-tire-repair': 'Flat tire repair',
    };
    const selectedService = window.sessionStorage.getItem('pedalya.selectedService');
    const serviceSummary = document.querySelector('[data-selected-service]');
    const dateButtons = [...schedulePage.querySelectorAll('[data-date]')];
    const slotButtons = [...schedulePage.querySelectorAll('[data-slot]')];
    const dateLabel = schedulePage.querySelector('[data-date-label]');
    const nextButton = document.querySelector('[data-schedule-next]');
    const message = document.querySelector('[data-schedule-message]');
    let chosenDate = dateButtons.find((button) => button.classList.contains('is-selected'))?.dataset.date ?? '';
    let chosenSlot = '';

    serviceSummary.textContent = selectedService && serviceNames[selectedService]
        ? `Selected service: ${serviceNames[selectedService]}`
        : 'Selected service: choose one from the previous step';

    const updateState = () => {
        const complete = chosenDate !== '' && chosenSlot !== '';
        nextButton.disabled = !complete;
        message.textContent = complete ? 'Schedule selected. Continue when you’re ready.' : 'Choose a date and time to continue.';
    };

    dateButtons.forEach((button) => {
        button.addEventListener('click', () => {
            dateButtons.forEach((item) => {
                item.classList.remove('is-selected');
                item.setAttribute('aria-pressed', 'false');
            });
            button.classList.add('is-selected');
            button.setAttribute('aria-pressed', 'true');
            chosenDate = button.dataset.date;
            dateLabel.textContent = button.dataset.date;
            chosenSlot = '';
            slotButtons.forEach((slot) => {
                slot.classList.remove('is-selected');
                slot.setAttribute('aria-pressed', 'false');
            });
            updateState();
        });
    });

    slotButtons.forEach((button) => {
        button.addEventListener('click', () => {
            slotButtons.forEach((item) => {
                item.classList.remove('is-selected');
                item.setAttribute('aria-pressed', 'false');
            });
            button.classList.add('is-selected');
            button.setAttribute('aria-pressed', 'true');
            chosenSlot = button.dataset.slot;
            updateState();
        });
    });

    nextButton.addEventListener('click', () => {
        if (nextButton.disabled) return;
        window.sessionStorage.setItem('pedalya.selectedSchedule', JSON.stringify({ date: chosenDate, time: chosenSlot }));
        window.pedalyaAdvanceBooking?.('/book/review');
    });
}
