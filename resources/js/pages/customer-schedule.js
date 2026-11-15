const scheduleForm = document.querySelector('[data-schedule-form]');

if (scheduleForm) {
    const schedulePanel = document.querySelector('[data-schedule-panel]');
    const dateGrid = schedulePanel.querySelector('.date-grid');
    const dateButtons = [...dateGrid.querySelectorAll('[data-date-option]')];
    const slotGrid = scheduleForm.querySelector('.slot-grid');
    const selectedDate = scheduleForm.querySelector('[name="date"]');
    const selectedTime = scheduleForm.querySelector('[data-selected-time]');
    const nextButton = scheduleForm.querySelector('[data-schedule-next]');
    const message = scheduleForm.querySelector('[data-schedule-message]');
    const dateLabel = schedulePanel.querySelector('[data-selected-long-date]');
    const monthLabel = schedulePanel.querySelector('[data-calendar-month]');
    const calendarDay = schedulePanel.querySelector('[data-calendar-day]');
    const openSlots = schedulePanel.querySelector('[data-open-slots]');
    const optionsElement = schedulePanel.querySelector('[data-schedule-options]');
    const dateOptions = JSON.parse(optionsElement?.textContent ?? '[]');
    const optionsByDate = new Map(dateOptions.map((option) => [option.key, option]));
    const selectionStorageKey = `pedalya-schedule-selection-${schedulePanel.dataset.serviceId}`;
    const saveSelection = () => {
        try {
            sessionStorage.setItem(selectionStorageKey, JSON.stringify({ date: selectedDate.value, time: selectedTime.value }));
        } catch {}
    };

    const selectTime = (button) => {
        slotGrid.querySelectorAll('[data-slot]').forEach((slot) => {
            slot.classList.remove('is-selected');
            slot.setAttribute('aria-pressed', 'false');
        });
        button.classList.add('is-selected');
        button.setAttribute('aria-pressed', 'true');
        selectedTime.value = button.dataset.slot;
        nextButton.disabled = false;
        message.textContent = 'Time selected. Continue when you’re ready.';
        saveSelection();
    };

    const renderSlots = (option) => {
        slotGrid.replaceChildren();
        selectedTime.value = '';
        nextButton.disabled = true;

        if (!option || option.slots.length === 0) {
            const emptyMessage = document.createElement('p');
            emptyMessage.textContent = 'No slots are available for this date. Choose another date.';
            slotGrid.append(emptyMessage);
            message.textContent = 'Choose a date with an available time.';
            return;
        }

        option.slots.forEach((slot) => {
            const button = document.createElement('button');
            button.className = 'time-slot';
            button.type = 'button';
            button.dataset.slot = slot.time;
            button.setAttribute('aria-pressed', 'false');
            button.append(document.createTextNode(slot.label));

            const availability = document.createElement('span');
            availability.textContent = 'Available';
            button.append(availability);
            slotGrid.append(button);
        });

        message.textContent = 'Choose a date and time to continue.';
    };

    dateGrid.addEventListener('click', (event) => {
        const button = event.target.closest('[data-date-option]');
        if (!button) return;

        const option = optionsByDate.get(button.dataset.dateOption);
        if (!option) return;

        dateButtons.forEach((dateButton) => {
            const active = dateButton === button;
            dateButton.classList.toggle('is-selected', active);
            dateButton.setAttribute('aria-pressed', String(active));
        });

        selectedDate.value = option.key;
        dateLabel.textContent = option.long_label;
        monthLabel.textContent = option.month_label;
        calendarDay.textContent = option.day;
        openSlots.textContent = String(option.slots.length);
        renderSlots(option);
        saveSelection();
    });

    slotGrid.addEventListener('click', (event) => {
        const button = event.target.closest('[data-slot]');
        if (button) selectTime(button);
    });

    if (schedulePanel.dataset.calendarReturned === 'true') {
        try {
            const savedSelection = JSON.parse(sessionStorage.getItem(selectionStorageKey) ?? 'null');
            sessionStorage.removeItem(selectionStorageKey);
            const savedDateButton = dateButtons.find((button) => button.dataset.dateOption === savedSelection?.date);
            if (savedDateButton) {
                savedDateButton.click();
                const savedTimeButton = [...slotGrid.querySelectorAll('[data-slot]')]
                    .find((button) => button.dataset.slot === savedSelection?.time);
                if (savedTimeButton) savedTimeButton.click();
            }
            sessionStorage.removeItem(selectionStorageKey);
        } catch {
            sessionStorage.removeItem(selectionStorageKey);
        }
    }

    scheduleForm.addEventListener('submit', (event) => {
        if (selectedTime.value) {
            sessionStorage.removeItem(selectionStorageKey);
            return;
        }

        event.preventDefault();
        message.textContent = 'Choose an available time to continue.';
    });
}
