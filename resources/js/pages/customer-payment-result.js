const paymentResultPage = document.querySelector('[data-payment-result]');

if (paymentResultPage) {
    const failed = paymentResultPage.dataset.paymentResult === 'failed';
    const reference = paymentResultPage.dataset.paymentReference;
    document.querySelector('[data-result-reference]').textContent = reference;
    document.querySelector('[data-result-mark]').textContent = failed ? '!' : '✓';
    document.querySelector('[data-result-kicker]').textContent = failed ? 'Payment needs attention' : 'Payment received';
    document.querySelector('[data-result-title]').innerHTML = failed ? 'Payment could not<br><em>be completed.</em>' : 'Your payment is<br><em>ready to go.</em>';
    document.querySelector('[data-result-copy]').innerHTML = failed ? `We couldn’t complete the payment preview for booking <strong>${reference}</strong>.` : `Your payment preview is associated with booking <strong>${reference}</strong>.`;
    document.querySelector('[data-result-status]').textContent = failed ? 'Failed preview' : 'Successful preview';
    document.querySelector('[data-result-notice]').textContent = failed ? 'In the connected system, you would be able to retry payment after the issue is resolved.' : 'In the connected system, your verified payment status will appear here.';
}
