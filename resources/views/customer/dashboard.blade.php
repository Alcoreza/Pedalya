<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Your Pedalya customer dashboard for bike repair bookings and updates.">

        <title>Your dashboard | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-dashboard.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js'])
    </head>
    <body class="customer-booking-page customer-dashboard-page">
        <header class="customer-header">
            <a class="brand" href="{{ route('home') }}" aria-label="Return to Pedalya home"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="10.5"></circle><circle cx="16" cy="16" r="3"></circle><path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path></svg></span><span>Pedalya</span></a>
            <div class="customer-header-actions"><span class="preview-pill"><i></i> Frontend preview</span><a class="header-link" href="{{ route('home') }}">Sign out <span aria-hidden="true">↗</span></a></div>
        </header>
@include('customer.partials.navigation')

        <main class="customer-booking-shell">
            <section class="dashboard-welcome" aria-labelledby="dashboard-title"><div><span class="section-kicker">Customer dashboard</span><h1 id="dashboard-title">Good morning,<br><em>Juan.</em></h1><p>Here’s the latest on your repair journey.</p></div><a class="booking-next dashboard-book-link" href="{{ route('customer.book') }}">Book a repair <span aria-hidden="true">→</span></a></section>

            <section class="dashboard-stats" aria-label="Customer summary">
                <article><span>Active bookings</span><strong>02</strong><small>One repair in progress</small></article>
                <article><span>Needs attention</span><strong class="dashboard-stat-attention">01</strong><small>Payment pending</small></article>
                <article><span>Completed repairs</span><strong>01</strong><small>Ready to view</small></article>
            </section>

            <section class="dashboard-feature-grid" aria-label="Current booking overview">
                <article class="dashboard-next-card"><div class="dashboard-card-top"><span>Next appointment</span><span class="record-status status-brand"><i></i>Repair in progress</span></div><div class="dashboard-appointment"><div class="dashboard-date"><small>FRI</small><strong>13</strong><span>MAR 2026</span></div><div><span class="dashboard-reference">PDL-1037</span><h2>Full tune-up</h2><p>1:30 PM · Pedalya Bike Repair</p></div></div><div class="dashboard-card-footer"><span>Estimated completion update pending</span><a href="{{ route('customer.booking.detail', 'PDL-1037') }}">View booking details <span aria-hidden="true">↗</span></a></div></article>
                <article class="dashboard-progress-card"><div class="dashboard-card-top"><span>Repair progress</span><span class="progress-percent">03 / 04</span></div><div class="dashboard-progress-track"><span></span></div><div class="dashboard-progress-labels"><span>Received</span><span>In progress</span><span>Completed</span></div><h2>Your bike is with the workshop.</h2><p>The technician is currently working through your tune-up.</p></article>
            </section>

            <section class="dashboard-lower-grid">
                <article class="dashboard-panel recent-bookings-panel"><div class="dashboard-panel-heading"><div><span>Activity</span><h2>Recent bookings</h2></div><a href="{{ route('customer.bookings') }}">View all <span aria-hidden="true">→</span></a></div><div class="dashboard-booking-row"><div class="dashboard-row-icon">BI</div><div><strong>Brake inspection</strong><span>PDL-1048 · Mar 17, 9:00 AM</span></div><span class="record-status status-pending"><i></i>Pending review</span></div><div class="dashboard-booking-row"><div class="dashboard-row-icon row-icon-complete">WA</div><div><strong>Wheel alignment</strong><span>PDL-1019 · Completed Mar 02</span></div><span class="record-status status-success"><i></i>Completed</span></div></article>
                <article class="dashboard-panel dashboard-location-panel"><div class="dashboard-panel-heading"><div><span>Workshop</span><h2>Visit location</h2></div><span class="appointment-icon" aria-hidden="true">⌖</span></div><div class="dashboard-mini-map"><span>Pedalya Bike Repair</span><i>⌖</i></div><p>123 Sample Street, Quezon City</p><a href="{{ route('customer.bookings') }}">View booking locations <span aria-hidden="true">↗</span></a></article>
            </section>

            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>This dashboard uses representative customer data. Authentication and live booking updates will be connected in the backend phase.</p></div>
        </main>
    </body>
</html>
