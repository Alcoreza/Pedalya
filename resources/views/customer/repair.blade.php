@php($appointment = $booking->appointment_at->setTimezone(config('booking.timezone')))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Follow the live repair progress of your booking.">
    <title>Repair progress | Pedalya</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.googleapis.com/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-repair.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js'])
</head>
<body class="customer-booking-page customer-repair-page">
    @include('customer.partials.navigation')
    <main class="customer-booking-shell">
        <section class="repair-heading" aria-labelledby="repair-title"><div><span class="section-kicker">Repair progress</span><h1 id="repair-title">Follow your<br><em>repair journey.</em></h1><span class="detail-reference">{{ $booking->reference }}</span></div><div class="repair-current-status"><span>Current status</span><strong>{{ $repairStatus['label'] }}</strong></div></section>
        <div class="repair-layout">
            <section class="repair-timeline-card" aria-labelledby="timeline-title"><div class="detail-card-heading"><div><span>Workshop updates</span><h2 id="timeline-title">{{ $booking->service?->name ?? 'Repair timeline' }}</h2></div><span class="repair-bike-icon" aria-hidden="true">✦</span></div>
                <ol class="repair-timeline">
                    @foreach ($timeline as $index => $event)
                        <li class="repair-timeline-item is-{{ $event['state'] }}"><span class="timeline-marker">{{ $event['state'] === 'complete' ? '✓' : $index + 1 }}</span><div class="timeline-copy"><strong>{{ $event['title'] }}</strong><p>{{ $event['copy'] }}</p></div><time class="timeline-date">{{ $event['date_label'] }}</time></li>
                    @endforeach
                </ol>
            </section>
            <aside class="repair-side-column"><article class="detail-card repair-visit-card"><div class="detail-card-heading"><div><span>Appointment</span><h2>Your visit</h2></div></div><dl><div><dt>Service</dt><dd>{{ $booking->service?->name ?? 'Repair service' }}</dd></div><div><dt>Date</dt><dd>{{ $appointment->format('D, M j, Y') }}</dd></div><div><dt>Time</dt><dd>{{ $appointment->format('g:i A') }}</dd></div><div><dt>Payment</dt><dd>{{ $booking->payment_status === 'paid' ? 'Paid' : \Illuminate\Support\Str::headline($booking->payment_status) }}</dd></div></dl></article><a class="booking-next repair-back-bookings" href="{{ route('customer.booking.detail', $booking->reference) }}">View booking details <span aria-hidden="true">→</span></a></aside>
        </div>
    </main>
</body>
</html>
