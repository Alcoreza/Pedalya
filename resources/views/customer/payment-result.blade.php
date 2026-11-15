<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="PayMongo payment status for your Pedalya booking.">
        <title>Payment status | Pedalya</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-payment-result.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js'])
    </head>
    <body class="customer-booking-page customer-payment-result-page result-{{ $result }}">
        @include('customer.partials.navigation')
        <main class="customer-booking-shell result-shell">
            <section class="result-hero" aria-labelledby="result-title">
                <div class="result-mark" aria-hidden="true">{{ $booking->payment_status === 'paid' ? '✓' : '…' }}</div>
                <span class="section-kicker">PayMongo payment</span>
                <h1 id="result-title">{{ $resultTitle }}</h1>
                <p>{{ $resultMessage }}</p>
            </section>
            <section class="result-card">
                <div class="result-reference-row"><span>Booking reference</span><strong>{{ $booking->reference }}</strong></div>
                <div class="result-status-row"><span>Payment status</span><strong>{{ $paymentStatus }}</strong></div>
                <div class="result-notice">Only a verified PayMongo webhook updates your booking to paid.</div>
            </section>
            <div class="result-actions">
                <a class="booking-next" href="{{ route('customer.confirmation', ['reference' => $booking->reference]) }}">Back to booking <span aria-hidden="true">&#8594;</span></a>
                <a class="confirmation-secondary" href="{{ route('customer.dashboard') }}">Return to dashboard</a>
            </div>
        </main>
    </body>
</html>
