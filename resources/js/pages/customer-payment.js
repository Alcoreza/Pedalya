const paymentPage = document.querySelector('[data-payment-reference]');

if (paymentPage) {
    const records = {
        'PDL-1048': { service: 'Brake inspection', appointment: 'Tue, Mar 17 · 9:00 AM', total: '₱500' },
        'PDL-1037': { service: 'Full tune-up', appointment: 'Fri, Mar 13 · 1:30 PM', total: '₱1,200' },
        'PDL-1019': { service: 'Wheel alignment', appointment: 'Mon, Mar 02 · 10:30 AM', total: '₱650' },
    };
    const record = records[paymentPage.dataset.paymentReference] ?? records['PDL-1048'];
    const setText = (selector, value) => document.querySelectorAll(selector).forEach((element) => { element.textContent = value; });
    setText('[data-payment-service]', record.service);
    setText('[data-payment-appointment]', record.appointment);
    setText('[data-payment-total]', record.total);

    document.querySelector('[data-payment-placeholder]').addEventListener('click', () => {
        document.querySelector('[data-payment-message]').textContent = 'PayMongo checkout will be available after backend integration.';
    });
}
