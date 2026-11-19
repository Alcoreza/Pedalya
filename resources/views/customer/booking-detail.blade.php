@php
    $appointmentAt = $booking->appointment_at->setTimezone(config('booking.timezone'));
    $bookingLabel = $booking->booking_status === 'pending' && $booking->processing_method === 'review_required'
        ? 'Awaiting manager approval'
        : \Illuminate\Support\Str::headline($booking->booking_status);
    $paymentLabel = $booking->payment_status === 'paid' ? 'Paid' : \Illuminate\Support\Str::headline($booking->payment_status);
    $repairLabels = ['awaiting_arrival' => 'Awaiting bike arrival', 'at_shop' => 'Bike at shop', 'in_progress' => 'Repair in progress', 'completed' => 'Repair completed', 'claimed' => 'Bike claimed'];
    $repairKey = $booking->repair?->status ?? ($booking->booking_status === 'completed' ? 'completed' : ($booking->booking_status === 'confirmed' ? 'awaiting_arrival' : 'not_started'));
    $repairLabel = $repairLabels[$repairKey] ?? 'Not started';
    $calendarLabel = match ($booking->calendar_status) {
        'synchronized' => 'Synchronized', 'failed' => 'Sync failed', 'pending' => 'Sync pending', default => 'Not scheduled',
    };
    [$updateTitle, $updateCopy] = match (true) {
        $booking->booking_status === 'rejected' => ['Booking not approved', 'The manager could not approve this appointment. Please choose another time.'],
        $booking->booking_status === 'pending' && $booking->processing_method === 'review_required' => ['Waiting for workshop review', 'Your appointment request is with the manager.'],
        $repairKey === 'at_shop' => ['Bike received at the workshop', 'The workshop has recorded your bike’s arrival.'],
        $repairKey === 'in_progress' => ['Your repair is in progress', 'A workshop team member has started working on your bike.'],
        $repairKey === 'completed' => ['Repair completed', 'Your bike is ready for pickup at the workshop.'],
        $repairKey === 'claimed' => ['Bike picked up', 'Your bike has been marked as claimed.'],
        $booking->payment_status !== 'paid' => ['Payment pending', 'Repair work can begin after payment is complete and your bike arrives at the shop.'],
        default => ['Booking confirmed', 'Bring your bike to the workshop at the scheduled time.'],
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="View your Pedalya booking details and live repair status.">
    <title>Booking {{ $booking->reference }} | Pedalya</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-booking-detail.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js'])
</head>
<body class="customer-booking-page customer-detail-page">
    @include('customer.partials.navigation')
    <main class="customer-booking-shell">
        <a class="customer-return-link" href="{{ route('customer.bookings') }}">← Back to bookings</a>
        <section class="detail-heading" aria-labelledby="detail-title">
            <div><span class="section-kicker">Booking details</span><h1 id="detail-title">{{ $booking->service?->name ?? 'Repair service' }}</h1><div class="detail-heading-meta"><span class="detail-reference">{{ $booking->reference }}</span><span>Your visit, from request to repair.</span></div></div>
            <div class="detail-current-status"><span>Booking status</span><strong>{{ $bookingLabel }}</strong></div>
        </section>
        <div class="detail-layout">
            <div class="detail-main-column">
                <article class="detail-card appointment-card-detail">
                    <div class="detail-card-heading"><div><span>Appointment</span><h2>Your repair visit</h2></div></div>
                    <dl class="detail-visit-facts"><div><dt>Date</dt><dd>{{ $appointmentAt->format('l, F j, Y') }}</dd></div><div><dt>Start time</dt><dd>{{ $appointmentAt->format('g:i A') }}</dd></div><div><dt>Duration</dt><dd>{{ $booking->duration_minutes }} min</dd></div></dl>
                    <div class="detail-visit-location"><span>Visit location</span><strong>Pedalya workshop</strong></div>
                </article>
                <article class="detail-card detail-status-card">
                    <div class="detail-card-heading"><div><span>Status overview</span><h2>Your booking at a glance</h2></div></div>
                    <div class="status-overview">
                        @foreach (array_filter([['label' => 'Calendar', 'value' => $calendarLabel], ['label' => 'Booking', 'value' => $bookingLabel], ['label' => 'Payment', 'value' => $paymentLabel], ['label' => 'Repair', 'value' => $repairLabel]], fn (array $status): bool => $booking->booking_status !== 'rejected' || $status['label'] !== 'Payment') as $status)
                            <div><span class="record-status {{ in_array($status['value'], ['Paid', 'Confirmed', 'Completed', 'Synchronized', 'Bike at shop', 'Repair completed', 'Bike claimed'], true) ? 'status-success' : 'status-pending' }}"><i></i>{{ $status['value'] }}</span><small>{{ $status['label'] }}</small></div>
                        @endforeach
                    </div>
                </article>
                <article class="detail-card manager-message"><span class="section-kicker">Workshop update</span><h2>{{ $updateTitle }}</h2><p>{{ $updateCopy }}</p></article>
                <article class="detail-card location-card" id="workshop-location">
                    <div class="detail-card-heading"><div><span>Plan your visit</span><h2>Find the workshop</h2></div></div>
                    <div class="map-preview" role="group" aria-label="Google Maps view of the Pedalya Bike Repair workshop"><iframe src="https://www.google.com/maps/embed?pb=!4v1794719640857!6m8!1m7!1s-NHNApbTEcUDyHvdniN88A!2m2!1d14.11882973585545!2d121.556903219214!3f38.98520599254551!4f-18.004327157487083!5f0.7820865974627469" title="Pedalya Bike Repair workshop" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></div>
                    <div class="location-details"><div><strong>Pedalya Bike Repair</strong><span>Lucban-Sampaloc Road, Lucban, Quezon</span></div><a href="https://www.google.com/maps/search/?api=1&amp;query=14.11882973585545%2C121.556903219214" target="_blank" rel="noopener noreferrer">Open in Google Maps ↗</a></div>
                </article>
            </div>
            <aside class="detail-side-column" aria-label="Booking summary and payment">
                <article class="detail-card detail-summary-card" data-booking-status="{{ $booking->booking_status }}"><div class="detail-card-heading"><div><span>Booking summary</span><h2>Your service</h2></div></div>
                    <dl><div><dt>Reference</dt><dd>{{ $booking->reference }}</dd></div><div><dt>Service</dt><dd>{{ $booking->service?->name ?? 'Repair service' }}</dd></div><div><dt>Estimated total</dt><dd class="detail-total">₱{{ number_format((float) $booking->service_price, 2) }}</dd></div></dl>
                    @if ($booking->booking_status !== 'rejected')
                        <div class="detail-payment-note"><span class="detail-payment-label">Payment status</span><strong>{{ $paymentLabel }}</strong><span>{{ $booking->payment?->paid_at ? 'Paid '.$booking->payment->paid_at->setTimezone(config('booking.timezone'))->format('M j, Y · g:i A') : 'Payment information is linked to this booking.' }}</span>
                        @if ($booking->payment_status !== 'paid' && ! ($booking->booking_status === 'pending' && $booking->processing_method === 'review_required') && $booking->booking_status !== 'rejected')<a href="{{ route('customer.payment', $booking->reference) }}"><span>Review payment options</span><span aria-hidden="true">→</span></a>@endif
                    </div>
                    @endif
                    <p class="detail-summary-help">Your booking reference keeps your appointment, payment, and repair updates together.</p>
                </article>
                @if ($booking->booking_status !== 'rejected')
                    <a class="booking-next detail-repair-link" href="{{ route('customer.repair', $booking->reference) }}">View repair progress <span aria-hidden="true">→</span></a>
                @endif
            </aside>
        </div>
    </main>
</body>
</html>
