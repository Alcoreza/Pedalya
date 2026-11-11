const detailPage = document.querySelector('[data-booking-reference]');

if (detailPage) {
    const records = {
        'PDL-1048': { service: 'Brake inspection', date: 'Tue, Mar 17', time: '9:00 AM', duration: '45 min', price: '₱500', current: 'Awaiting review', booking: 'Pending review', payment: 'Payment pending', calendar: 'Not scheduled', repair: 'Not started', managerTitle: 'Waiting for workshop review', managerCopy: 'Your repair request is in the workshop queue. We’ll show the next update here once the booking is reviewed.' },
        'PDL-1037': { service: 'Full tune-up', date: 'Fri, Mar 13', time: '1:30 PM', duration: '90 min', price: '₱1,200', current: 'Repair in progress', booking: 'Confirmed', payment: 'Paid', calendar: 'Synchronized', repair: 'In progress', managerTitle: 'Your booking is confirmed', managerCopy: 'The workshop has accepted your request. Your repair is currently in progress.' },
        'PDL-1019': { service: 'Wheel alignment', date: 'Mon, Mar 02', time: '10:30 AM', duration: '60 min', price: '₱650', current: 'Repair completed', booking: 'Completed', payment: 'Paid', calendar: 'Synchronized', repair: 'Completed', managerTitle: 'Repair completed', managerCopy: 'This repair has been completed. Thank you for trusting Pedalya with your ride.' },
    };
    const reference = detailPage.dataset.bookingReference;
    const record = records[reference] ?? records['PDL-1048'];
    const setText = (selector, value) => document.querySelectorAll(selector).forEach((element) => { element.textContent = value; });
    const statusTone = (value) => value === 'Paid' || value === 'Confirmed' || value === 'Completed' || value === 'Synchronized' ? 'success' : value === 'In progress' ? 'brand' : value === 'Pending review' || value === 'Payment pending' ? 'pending' : 'neutral';
    const setStatus = (selector, value) => { const element = document.querySelector(selector); element.className = `record-status status-${statusTone(value)}`; const text = element.querySelector('[data-status-text]'); if (text) text.textContent = value; };

    setText('[data-detail-service]', record.service);
    setText('[data-detail-date]', record.date);
    setText('[data-detail-time]', record.time);
    setText('[data-detail-duration]', record.duration);
    setText('[data-detail-price]', record.price);
    document.querySelector('[data-detail-booking-status]').textContent = record.current;
    setStatus('[data-detail-booking-status-duplicate]', record.booking);
    setStatus('[data-detail-payment-status]', record.payment);
    setStatus('[data-detail-calendar-status]', record.calendar);
    setStatus('[data-detail-repair-status]', record.repair);
    setText('[data-detail-manager-title]', record.managerTitle);
    setText('[data-detail-manager-copy]', record.managerCopy);
}
