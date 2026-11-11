<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Your Pedalya bike repair booking preview confirmation.">

        <title>Booking preview | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-confirmation.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-confirmation.js'])
    </head>
    <body class="customer-booking-page customer-confirmation-page">
        <header class="customer-header">
            <a class="brand" href="{{ route('home') }}" aria-label="Return to Pedalya home">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="10.5"></circle><circle cx="16" cy="16" r="3"></circle><path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path></svg>
                </span>
                <span>Pedalya</span>
            </a>
            <div class="customer-header-actions">
                <span class="preview-pill"><i></i> Frontend preview</span>
                <a class="header-link" href="{{ route('home') }}">Sign out <span aria-hidden="true">↗</span></a>
            </div>
        </header>
@include('customer.partials.navigation')

        <main class="customer-booking-shell confirmation-shell">
            <nav class="booking-steps" aria-label="Booking progress">
                <span class="booking-step is-complete"><b>✓</b><span>Service</span></span>
                <span class="booking-step is-complete"><b>✓</b><span>Schedule</span></span>
                <span class="booking-step is-complete"><b>✓</b><span>Review</span></span>
                <span class="booking-step is-active"><b>04</b><span>Confirmation</span></span>
            </nav>

            <section class="confirmation-hero" aria-labelledby="confirmation-title">
                <div class="confirmation-mark" aria-hidden="true">✓</div>
                <span class="section-kicker">Preview confirmation</span>
                <h1 id="confirmation-title">Your repair request<br><em>is ready to go.</em></h1>
                <p>We have everything needed to prepare your booking. The real submission and payment connection will be added in the backend phase.</p>
            </section>

            <section class="confirmation-card" aria-labelledby="booking-reference-title">
                <div class="confirmation-card-head"><span>Booking reference</span><strong id="booking-reference-title" data-booking-reference>PDL-PREVIEW-2048</strong></div>
                <div class="confirmation-details">
                    <div><span>Service</span><strong data-confirmation-service>Loading…</strong></div>
                    <div><span>Appointment</span><strong data-confirmation-date>Loading…</strong></div>
                    <div><span>Customer</span><strong data-confirmation-customer>Loading…</strong></div>
                    <div><span>Estimated total</span><strong data-confirmation-price>₱500</strong></div>
                </div>
                <div class="confirmation-status"><span class="status-dot"></span><div><strong>Payment pending</strong><p>Payment options will appear after the backend connection is available.</p></div></div>
            </section>

            <div class="confirmation-actions"><a class="booking-next" href="{{ route('customer.book') }}">Start another booking <span aria-hidden="true">→</span></a><a class="confirmation-secondary" href="{{ route('home') }}">Return to home</a></div>

            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>This is a frontend-only preview. No booking was saved and no payment was processed.</p></div>
        </main>
    </body>
</html>
