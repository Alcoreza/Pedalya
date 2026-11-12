<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Payment result preview for your Pedalya bike repair booking.">

        <title>{{ $result === 'success' ? 'Payment received' : 'Payment could not be completed' }} | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-payment-result.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-payment-result.js'])
    </head>
    <body class="customer-booking-page customer-payment-result-page result-{{ $result }}" data-payment-result="{{ $result }}" data-payment-reference="{{ $payment }}">
        @include('customer.partials.navigation')
        <main class="customer-booking-shell result-shell">
            <section class="result-hero" aria-labelledby="result-title"><div class="result-mark" aria-hidden="true" data-result-mark>✓</div><span class="section-kicker" data-result-kicker>Payment received</span><h1 id="result-title" data-result-title>Your payment is<br><em>ready to go.</em></h1><p data-result-copy>Your payment preview is associated with booking <strong data-result-reference>{{ $payment }}</strong>.</p></section>
            <section class="result-card"><div class="result-reference-row"><span>Payment reference</span><strong data-result-reference>{{ $payment }}</strong></div><div class="result-status-row"><span>Payment status</span><strong data-result-status>Successful preview</strong></div><div class="result-notice" data-result-notice>In the connected system, your verified payment status will appear here.</div></section>
            <div class="result-actions"><a class="booking-next" href="{{ route('customer.booking.detail', $payment) }}">Back to booking <span aria-hidden="true">→</span></a><a class="confirmation-secondary" href="{{ route('customer.dashboard') }}">Return to dashboard</a></div>
            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>No payment was processed. This is a frontend result-state preview for the later PayMongo integration.</p></div>
        </main>
    </body>
</html>
