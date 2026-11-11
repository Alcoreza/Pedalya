<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Follow the repair progress of your Pedalya booking.">

        <title>Repair progress | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-repair.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-repair.js'])
    </head>
    <body class="customer-booking-page customer-repair-page" data-repair-reference="{{ $booking }}">
        <header class="customer-header">
            <a class="brand" href="{{ route('home') }}" aria-label="Return to Pedalya home"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="10.5"></circle><circle cx="16" cy="16" r="3"></circle><path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path></svg></span><span>Pedalya</span></a>
            <div class="customer-header-actions"><span class="preview-pill"><i></i> Frontend preview</span><a class="header-link" href="{{ route('home') }}">Sign out <span aria-hidden="true">↗</span></a></div>
        </header>
        @include('customer.partials.navigation')

        <main class="customer-booking-shell">
            <section class="repair-heading" aria-labelledby="repair-title"><div><span class="section-kicker">Repair progress</span><h1 id="repair-title">Follow your<br><em>repair journey.</em></h1><span class="detail-reference" data-repair-reference>{{ $booking }}</span></div><div class="repair-current-status"><span>Current status</span><strong data-current-status>Loading…</strong></div></section>

            <div class="repair-layout">
                <section class="repair-timeline-card" aria-labelledby="timeline-title"><div class="detail-card-heading"><div><span>Live status view</span><h2 id="timeline-title" data-repair-service>Repair timeline</h2></div><span class="repair-bike-icon" aria-hidden="true">✦</span></div><ol class="repair-timeline" data-repair-timeline></ol></section>
                <aside class="repair-side-column"><article class="detail-card repair-visit-card"><div class="detail-card-heading"><div><span>Appointment</span><h2>Your visit</h2></div></div><dl><div><dt>Service</dt><dd data-repair-service-summary>Loading…</dd></div><div><dt>Date</dt><dd data-repair-date>Loading…</dd></div><div><dt>Time</dt><dd data-repair-time>Loading…</dd></div></dl></article><a class="booking-next repair-back-bookings" href="{{ route('customer.bookings') }}">View all bookings <span aria-hidden="true">→</span></a></aside>
            </div>

            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>Repair updates are represented with sample data. Live status changes will be provided by the backend later.</p></div>
        </main>
    </body>
</html>
