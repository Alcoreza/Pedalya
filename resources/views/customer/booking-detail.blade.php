<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="View the details and status of your Pedalya bike repair booking.">

        <title>Booking {{ $booking }} | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-booking-detail.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-booking-detail.js'])
    </head>
    <body class="customer-booking-page customer-detail-page" data-booking-reference="{{ $booking }}">
        @include('customer.partials.navigation')

        <main class="customer-booking-shell">

            <section class="detail-heading" aria-labelledby="detail-title"><div><span class="section-kicker">Booking detail</span><h1 id="detail-title" data-detail-service>Loading…</h1><span class="detail-reference" data-detail-reference>{{ $booking }}</span></div><div class="detail-current-status"><span>Current status</span><strong data-detail-booking-status>Loading…</strong></div></section>

            <div class="detail-layout">
                <section class="detail-main-column">
                    <article class="detail-card appointment-card-detail"><div class="detail-card-heading"><div><span>Appointment</span><h2>Your repair visit</h2></div><span class="appointment-icon" aria-hidden="true">↗</span></div><div class="appointment-detail-grid"><div><span>Date</span><strong data-detail-date>Loading…</strong></div><div><span>Start time</span><strong data-detail-time>Loading…</strong></div><div><span>Duration</span><strong data-detail-duration>Loading…</strong></div><div><span>Location</span><strong>Pedalya workshop</strong></div></div></article>

                    <article class="detail-card location-card"><div class="detail-card-heading"><div><span>Visit location</span><h2>Find the workshop</h2></div><span class="appointment-icon" aria-hidden="true">⌖</span></div><div class="map-preview" role="img" aria-label="Map preview of the Pedalya workshop location"><div class="map-grid"></div><span class="map-road map-road-one"></span><span class="map-road map-road-two"></span><span class="map-pin">⌖</span><span class="map-label">Pedalya workshop</span></div><div class="location-details"><div><strong>Pedalya Bike Repair</strong><span>123 Sample Street, Quezon City</span></div><a href="https://www.google.com/maps/search/?api=1&query=Pedalya+Bike+Repair" target="_blank" rel="noreferrer">Open in Google Maps <span aria-hidden="true">↗</span></a></div><p class="location-preview-note">Map configuration will be connected during the backend/integration phase.</p></article>

                    <article class="detail-card"><div class="detail-card-heading"><div><span>Status overview</span><h2>Where things stand</h2></div></div><p class="detail-status-note">Your booking, payment, appointment, and repair updates are tracked here in one place.</p><div class="status-overview"><div><span class="record-status status-pending" data-detail-booking-status-duplicate><span class="status-icon" data-status-icon="booking" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Z"/><path d="m9 15 2 2 4-4"/></svg></span><span data-status-text>Pending review</span></span><small>Booking</small></div><div><span class="record-status status-pending" data-detail-payment-status><span class="status-icon" data-status-icon="payment" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg></span><span data-status-text>Payment pending</span></span><small>Payment</small></div><div><span class="record-status status-neutral" data-detail-calendar-status><span class="status-icon" data-status-icon="calendar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Z"/></svg></span><span data-status-text>Not scheduled</span></span><small>Calendar</small></div><div><span class="record-status status-neutral" data-detail-repair-status><span class="status-icon" data-status-icon="repair" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m14 6 4-2 2 2-2 4-3-1-6 6 1 3-2 2-3-3 2-2 3 1 6-6-1-3Z"/></svg></span><span data-status-text>Not started</span></span><small>Repair</small></div></div></article>

                    <article class="detail-card manager-message"><span class="section-kicker">Manager update</span><h2 data-detail-manager-title>Waiting for workshop review</h2><p data-detail-manager-copy>Your repair request is in the workshop queue. We’ll show the next update here once the booking is reviewed.</p></article>
                </section>

                <aside class="detail-side-column"><article class="detail-card detail-summary-card"><div class="detail-card-heading"><div><span>Booking summary</span><h2>Details</h2></div></div><dl><div><dt>Reference</dt><dd data-detail-reference>{{ $booking }}</dd></div><div><dt>Service</dt><dd data-detail-service>Loading…</dd></div><div><dt>Estimated total</dt><dd class="detail-total" data-detail-price>Loading…</dd></div></dl><div class="detail-payment-note"><strong>Payment pending</strong><span>Online payment will be available after backend integration.</span><a href="{{ route('customer.payment', $booking) }}">Review payment options →</a></div></article></aside>
            </div>

            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>This booking detail uses a representative mock record. Statuses and actions will be connected to the backend later.</p></div>
        </main>
    </body>
</html>
