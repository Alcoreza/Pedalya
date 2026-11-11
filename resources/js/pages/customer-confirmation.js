const confirmationPage = document.querySelector('.customer-confirmation-page');

if (confirmationPage) {
    const serviceNames = {
        'brake-inspection': { name: 'Brake inspection', price: '₱500' },
        'wheel-alignment': { name: 'Wheel alignment', price: '₱650' },
        'full-tune-up': { name: 'Full tune-up', price: '₱1,200' },
        'flat-tire-repair': { name: 'Flat tire repair', price: '₱350' },
    };
    const service = serviceNames[window.sessionStorage.getItem('pedalya.selectedService')] ?? serviceNames['brake-inspection'];
    const schedule = JSON.parse(window.sessionStorage.getItem('pedalya.selectedSchedule') ?? '{"date":"Tue, Mar 17","time":"9:00 AM"}');
    const customer = JSON.parse(window.sessionStorage.getItem('pedalya.customerDetails') ?? '{"name":"Preview customer"}');

    document.querySelector('[data-confirmation-service]').textContent = service.name;
    document.querySelector('[data-confirmation-price]').textContent = service.price;
    document.querySelector('[data-confirmation-date]').textContent = `${schedule.date} · ${schedule.time}`;
    document.querySelector('[data-confirmation-customer]').textContent = customer.name;
}
