<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Review the payment details for your Pedalya bike repair booking.">

        <title>Payment | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-payment.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-payment.js'])
    </head>
    <body class="customer-booking-page customer-payment-page" data-payment-reference="{{ $booking }}">
        @include('customer.partials.navigation')

        <main class="customer-booking-shell payment-shell">
            <section class="payment-heading" aria-labelledby="payment-title"><div><span class="section-kicker">Payment details</span><h1 id="payment-title">Complete your<br><em>repair payment.</em></h1><span class="detail-reference" data-payment-reference-label>{{ $booking }}</span></div><div class="payment-status-card"><span>Current status</span><strong>Payment pending</strong></div></section>

            <div class="payment-layout">
                <section class="payment-card payment-provider-card"><div class="payment-provider-mark" aria-hidden="true">P</div><span class="section-kicker">Secure checkout</span><h2>Continue with PayMongo</h2><p>Your payment will be handled through PayMongo after the backend integration is connected.</p><div class="payment-provider-points"><span>Encrypted checkout</span><span>PHP payment</span><span>Booking reference matched</span></div><button class="booking-next payment-placeholder-button" type="button" data-payment-placeholder>Continue to PayMongo <span aria-hidden="true">→</span></button><div class="payment-placeholder-message" role="status" aria-live="polite" data-payment-message>Payment checkout is not connected in this frontend preview.</div></section>
                <aside class="payment-card payment-summary-card"><div class="detail-card-heading"><div><span>Booking summary</span><h2>What you’re paying for</h2></div></div><dl><div><dt>Booking reference</dt><dd data-payment-reference-value>{{ $booking }}</dd></div><div><dt>Service</dt><dd data-payment-service>Loading…</dd></div><div><dt>Appointment</dt><dd data-payment-appointment>Loading…</dd></div><div><dt>Total</dt><dd class="payment-total" data-payment-total>Loading…</dd></div></dl><a class="payment-back-link" href="{{ route('customer.bookings') }}">View all bookings</a><div class="payment-preview-links"><span>Preview result states</span><a href="{{ route('customer.payment.success', $booking) }}">Success</a><a href="{{ route('customer.payment.failed', $booking) }}">Failed</a></div></aside>
            </div>

            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>This page is presentation-only. It does not call PayMongo, create a checkout, or mark the booking as paid.</p></div>
        </main>
    </body>
</html>
