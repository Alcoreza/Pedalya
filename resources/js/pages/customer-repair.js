const repairPage = document.querySelector('[data-repair-reference]');

if (repairPage) {
    const records = {
        'PDL-1048': { service: 'Brake inspection', date: 'Tue, Mar 17', time: '9:00 AM', current: 'Awaiting review', steps: [['Booking received', 'Your request has been received by Pedalya.', 'Today', 'complete'], ['Workshop review', 'The team will review your repair request next.', 'Next step', 'current'], ['Repair in progress', 'Work begins after your booking is confirmed.', 'Pending', ''], ['Repair completed', 'We’ll let you know when your bike is ready.', 'Pending', '']] },
        'PDL-1037': { service: 'Full tune-up', date: 'Fri, Mar 13', time: '1:30 PM', current: 'Repair in progress', steps: [['Booking received', 'Your request was received successfully.', 'Mar 10', 'complete'], ['Workshop review', 'Your booking was confirmed by the workshop.', 'Mar 11', 'complete'], ['Repair in progress', 'A technician is currently working on your bike.', 'Today', 'current'], ['Repair completed', 'We’ll let you know when your bike is ready.', 'Pending', '']] },
        'PDL-1019': { service: 'Wheel alignment', date: 'Mon, Mar 02', time: '10:30 AM', current: 'Repair completed', steps: [['Booking received', 'Your request was received successfully.', 'Mar 01', 'complete'], ['Workshop review', 'Your booking was confirmed by the workshop.', 'Mar 01', 'complete'], ['Repair in progress', 'The wheel alignment was completed by the technician.', 'Mar 02', 'complete'], ['Repair completed', 'Your bike is ready for its next ride.', 'Mar 02', 'current']] },
    };
    const record = records[repairPage.dataset.repairReference] ?? records['PDL-1048'];
    const setText = (selector, value) => document.querySelectorAll(selector).forEach((element) => { element.textContent = value; });
    const currentStatus = document.querySelector('[data-current-status]');
    currentStatus.textContent = record.current;
    setText('[data-repair-service]', record.service);
    setText('[data-repair-service-summary]', record.service);
    setText('[data-repair-date]', record.date);
    setText('[data-repair-time]', record.time);
    document.querySelector('[data-repair-timeline]').innerHTML = record.steps.map(([title, copy, date, state], index) => `<li class="repair-timeline-item is-${state}"><span class="timeline-marker">${state === 'complete' || state === 'current' ? '✓' : index + 1}</span><div class="timeline-copy"><strong>${title}</strong><p>${copy}</p></div><time class="timeline-date">${date}</time></li>`).join('');
}
