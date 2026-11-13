<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Your Pedalya customer dashboard for bike repair bookings and updates.">

        <title>Your dashboard | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-dashboard.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js'])
    </head>
    <body class="customer-booking-page customer-dashboard-page">
        {{-- Legacy header removed; the shared customer navigation is rendered below.
            <a class="brand" href="{{ route('home') }}" aria-label="Return to Pedalya home"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="10.5"></circle><circle cx="16" cy="16" r="3"></circle><path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path></svg></span><span>Pedalya</span></a>
            <div class="customer-header-actions"><span class="preview-pill"><i></i> Frontend preview</span><a class="header-link" href="{{ route('home') }}">Sign out <span aria-hidden="true">↗</span></a></div>
        --}}
@include('customer.partials.navigation')

        <main class="customer-booking-shell">
            <section class="dashboard-welcome" aria-labelledby="dashboard-title"><div><span class="section-kicker">Customer dashboard</span><h1 id="dashboard-title">Good morning,<br><em>{{ \Illuminate\Support\Str::of(auth()->user()?->name ?? 'Rider')->trim()->before(' ') }}.</em></h1><p>Here’s the latest on your repair journey.</p></div><a class="booking-next dashboard-book-link" href="{{ route('customer.book') }}">Book a repair <span aria-hidden="true">→</span></a></section>

            <section class="dashboard-stats" aria-label="Customer summary">
                <article><span>Active bookings</span><strong>02</strong><small>One repair in progress</small></article>
                <article><span>Needs attention</span><strong class="dashboard-stat-attention">01</strong><small>Payment pending</small></article>
                <article><span>Completed repairs</span><strong>01</strong><small>Ready to view</small></article>
            </section>

            <section class="dashboard-feature-grid" aria-label="Current booking overview">
                <article class="dashboard-next-card"><div class="dashboard-card-top"><span>Next appointment</span><span class="record-status status-brand"><i></i>Repair in progress</span></div><div class="dashboard-appointment"><time class="dashboard-date" datetime="2026-03-13" aria-label="Friday, March 13, 2026"><small>FRI</small><strong class="dashboard-date-number" aria-label="13"><span class="dashboard-date-reel" style="--digit: 1" aria-hidden="true"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></span><span class="dashboard-date-reel" style="--digit: 3" aria-hidden="true"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></span></strong><span>MAR 2026</span></time><div><span class="dashboard-reference">PDL-1037</span><h2>Full tune-up</h2><p>1:30 PM · Pedalya Bike Repair</p></div></div><div class="dashboard-card-footer"><span>Estimated completion update pending</span><a href="{{ route('customer.booking.detail', 'PDL-1037') }}">View booking details <span aria-hidden="true">↗</span></a></div></article>
                <article class="dashboard-progress-card"><div class="dashboard-card-top"><span>Repair progress</span><span class="progress-percent">03 / 04</span></div><div class="dashboard-progress-track"><span></span></div><div class="dashboard-progress-labels"><span>Received</span><span>In progress</span><span>Completed</span></div><h2>Your bike is with the workshop.</h2><p>The technician is currently working through your tune-up.</p></article>
            </section>

            <section class="dashboard-lower-grid">
                <article class="recent-bookings-panel"><div class="activity-intro"><div><span>Activity</span><h2>Booking activity</h2><p>A quick view of what changed across your repair bookings.</p></div><a href="{{ route('customer.bookings') }}"><span class="activity-link-label">View all</span></a></div><div class="activity-feed" aria-label="Recent booking activity"><div class="dashboard-booking-row"><div class="dashboard-row-icon" aria-hidden="true"><svg viewBox="0 0 48 48"><circle cx="23" cy="24" r="13"></circle><path d="M29 13h7v22h-7M36 18h4M36 30h4"></path><path d="M23 17v14M16 24h14"></path></svg></div><div class="dashboard-booking-copy"><strong>Brake inspection</strong><span class="dashboard-booking-meta"><span class="dashboard-booking-reference">PDL-1048</span><span class="dashboard-booking-divider" aria-hidden="true">·</span><span>Mar 17, 9:00 AM</span></span></div><span class="record-status status-pending"><i></i>Pending review</span></div><div class="dashboard-booking-row"><div class="dashboard-row-icon row-icon-complete" aria-hidden="true"><svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="14"></circle><circle cx="24" cy="24" r="4"></circle><path d="M24 10v10M24 28v10M10 24h10M28 24h10"></path><path d="M16 16l4 4M28 28l4 4M32 16l-4 4M20 28l-4 4"></path></svg></div><div class="dashboard-booking-copy"><strong>Wheel alignment</strong><span class="dashboard-booking-meta"><span class="dashboard-booking-reference">PDL-1019</span><span class="dashboard-booking-divider" aria-hidden="true">·</span><span>Completed Mar 02</span></span></div><span class="record-status status-success"><i></i>Completed</span></div></div></article>
                <article class="dashboard-panel dashboard-location-panel"><div class="dashboard-panel-heading"><div><span>Workshop</span><h2>Your repair destination</h2></div><span class="appointment-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12Z"></path><circle cx="12" cy="9" r="2.25"></circle></svg></span></div><div class="dashboard-mini-map" role="group" aria-label="Google Maps location of the Pedalya Bike Repair workshop"><iframe src="https://www.google.com/maps?q=Pedalya%20Bike%20Repair%2C%20123%20Sample%20Street%2C%20Quezon%20City&amp;output=embed" title="Pedalya Bike Repair workshop location" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe><span>Pedalya Bike Repair</span><i aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12Z"></path><circle cx="12" cy="9" r="2.25"></circle></svg></i></div><p><strong>123 Sample Street</strong><span>Quezon City</span></p><a href="{{ route('customer.bookings') }}">View booking locations <span aria-hidden="true">↗</span></a></article>
            </section>

            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>This dashboard uses representative customer data. Authentication and live booking updates will be connected in the backend phase.</p></div>
        </main>
    </body>
</html>
