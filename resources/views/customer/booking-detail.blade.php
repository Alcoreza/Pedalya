<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="View the details and status of your Pedalya bike repair booking.">
        <title>Booking {{ $booking }} | Pedalya</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-booking-detail.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-booking-detail.js'])
    </head>
    <body class="customer-booking-page customer-detail-page" data-booking-reference="{{ $booking }}">
        @include('customer.partials.navigation')

        <main class="customer-booking-shell">
            <a class="customer-return-link" href="{{ route('customer.bookings') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6-6 6 6 6M4 12h16"/></svg>
                Back to bookings
            </a>

            <section class="detail-heading" aria-labelledby="detail-title">
                <div>
                    <span class="section-kicker">Booking details</span>
                    <h1 id="detail-title" data-detail-service>Loading…</h1>
                    <div class="detail-heading-meta">
                        <span class="detail-reference" data-detail-reference>{{ $booking }}</span>
                        <span>Your visit, from request to repair.</span>
                    </div>
                </div>
                <div class="detail-current-status">
                    <span>Current status</span>
                    <strong data-detail-booking-status>Loading…</strong>
                </div>
            </section>

            <div class="detail-layout">
                <div class="detail-main-column">
                    <article class="detail-card appointment-card-detail">
                        <div class="detail-card-heading">
                            <div><span>Appointment</span><h2>Your repair visit</h2></div>
                            <span class="appointment-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3v4M17 3v4M4 10h16"/><rect x="4" y="5" width="16" height="16" rx="2"/><path d="m9 15 2 2 4-4"/></svg></span>
                        </div>
                        <dl class="detail-visit-facts">
                            <div><dt>Date</dt><dd data-detail-date>Loading…</dd></div>
                            <div><dt>Start time</dt><dd data-detail-time>Loading…</dd></div>
                            <div><dt>Duration</dt><dd data-detail-duration>Loading…</dd></div>
                        </dl>
                        <div class="detail-visit-location"><span>Visit location</span><strong>Pedalya workshop</strong></div>
                    </article>

                    <article class="detail-card detail-status-card">
                        <div class="detail-card-heading"><div><span>Status overview</span><h2>Your booking at a glance</h2></div></div>
                        <div class="status-overview">
                            @foreach ([
                                ['label' => 'Appointment', 'hook' => 'calendar-status', 'icon' => 'calendar', 'text' => 'Not scheduled', 'path' => 'M7 3v3M17 3v3M4 9h16M5 5h14v14H4V5Z'],
                                ['label' => 'Booking', 'hook' => 'booking-status-duplicate', 'icon' => 'booking', 'text' => 'Pending review', 'path' => 'M7 3v3M17 3v3M4 9h16M5 5h14v14H4V5Z M9 15l2 2 4-4'],
                                ['label' => 'Payment', 'hook' => 'payment-status', 'icon' => 'payment', 'text' => 'Payment pending', 'path' => 'M3 5h18v14H3Z M3 10h18M7 15h3'],
                                ['label' => 'Repair', 'hook' => 'repair-status', 'icon' => 'repair', 'text' => 'Not started', 'path' => 'm14 6 4-2 2 2-2 4-3-1-6 6 1 3-2 2-3-3 2-2 3 1 6-6-1-3Z'],
                            ] as $status)
                                <div>
                                    <span class="record-status status-neutral" data-detail-{{ $status['hook'] }}>
                                        <span class="status-icon" data-status-icon="{{ $status['icon'] }}" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="{{ $status['path'] }}"/></svg></span>
                                        <span data-status-text>{{ $status['text'] }}</span>
                                    </span>
                                    <small>{{ $status['label'] }}</small>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="detail-card manager-message">
                        <span class="section-kicker">Workshop update</span>
                        <h2 data-detail-manager-title>Waiting for workshop review</h2>
                        <p data-detail-manager-copy>Your repair request is in the workshop queue. We’ll show the next update here once the booking is reviewed.</p>
                    </article>

                    <article class="detail-card location-card" id="workshop-location">
                        <div class="detail-card-heading"><div><span>Plan your visit</span><h2>Find the workshop</h2></div></div>
                        <div class="map-preview" role="img" aria-label="Illustrative workshop map, not an exact location">
                            <div class="map-grid"></div><span class="map-road map-road-one"></span><span class="map-road map-road-two"></span>
                            <span class="map-pin" aria-hidden="true">⌖</span><span class="map-label">Pedalya workshop</span>
                        </div>
                        <div class="location-details">
                            <div><strong>Pedalya Bike Repair</strong><span>123 Sample Street, Quezon City</span></div>
                            <a href="https://www.google.com/maps/search/?api=1&query=Pedalya+Bike+Repair" target="_blank" rel="noopener noreferrer">Open in Google Maps <span aria-hidden="true">↗</span></a>
                        </div>
                        <p class="location-preview-note">Sample address and map preview.</p>
                    </article>
                </div>

                <aside class="detail-side-column" aria-label="Booking summary and payment">
                    <article class="detail-card detail-summary-card">
                        <div class="detail-card-heading"><div><span>Booking summary</span><h2>Your service</h2></div></div>
                        <dl>
                            <div><dt>Reference</dt><dd data-detail-reference>{{ $booking }}</dd></div>
                            <div><dt>Service</dt><dd data-detail-service>Loading…</dd></div>
                            <div><dt>Estimated total</dt><dd class="detail-total" data-detail-price>Loading…</dd></div>
                        </dl>
                        <div class="detail-payment-note">
                            <span class="detail-payment-label">Payment status</span>
                            <strong data-detail-payment-summary>Loading…</strong>
                            <span>Online payment is not connected in this preview.</span>
                            <a href="{{ route('customer.payment', $booking) }}"><span>Review payment options</span><span aria-hidden="true">→</span></a>
                        </div>
                        <p class="detail-summary-help">Your booking reference keeps your appointment, payment, and repair updates together.</p>
                    </article>
                </aside>
            </div>

            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>This booking detail uses a representative mock record. Statuses and actions will be connected to the backend later.</p></div>
        </main>
    </body>
</html>
