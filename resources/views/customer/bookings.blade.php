<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="View your Pedalya bike repair bookings and statuses.">
    <title>My bookings | Pedalya</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-bookings.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-bookings.js'])
</head>
<body class="customer-booking-page customer-bookings-page">
    @include('customer.partials.navigation')
    <main class="customer-booking-shell">
        <section class="bookings-heading" aria-labelledby="bookings-title">
            <div><span class="section-kicker">Customer workspace</span><h1 id="bookings-title">Your repair<br><em>bookings.</em></h1></div>
            <a class="booking-next new-booking-link" href="{{ route('customer.book') }}">Book a repair <span aria-hidden="true">→</span></a>
        </section>

        <section class="bookings-summary" aria-label="Booking summary">
            <div><span>Total bookings</span><strong>{{ str_pad((string) $summary['total'], 2, '0', STR_PAD_LEFT) }}</strong></div>
            <div><span>Needs your attention</span><strong class="summary-attention">{{ str_pad((string) $summary['attention'], 2, '0', STR_PAD_LEFT) }}</strong></div>
            <div><span>Completed repairs</span><strong>{{ str_pad((string) $summary['completed'], 2, '0', STR_PAD_LEFT) }}</strong></div>
        </section>

        <div class="booking-list-toolbar">
            <div class="booking-filters" role="group" aria-label="Filter bookings">
                <button class="booking-filter is-active" type="button" data-filter="all"><span>All bookings</span></button>
                <button class="booking-filter" type="button" data-filter="active"><span>Active</span></button>
                <button class="booking-filter" type="button" data-filter="completed"><span>Completed</span></button>
            </div>
            <span class="toolbar-note">{{ $summary['total'] }} {{ \Illuminate\Support\Str::plural('booking', $summary['total']) }}</span>
        </div>

        <section class="booking-list" aria-label="Your bookings" data-booking-list>
            @forelse ($bookings as $booking)
                @php
                    $isCompleted = $booking->booking_status === 'completed';
                    $bookingLabel = $booking->booking_status === 'pending' && $booking->processing_method === 'review_required'
                        ? 'Awaiting approval'
                        : ($booking->booking_status === 'confirmed' ? 'Confirmed' : \Illuminate\Support\Str::headline($booking->booking_status));
                    $paymentLabel = $booking->payment_status === 'paid' ? 'Paid' : \Illuminate\Support\Str::headline($booking->payment_status);
                    $bookingTone = in_array($booking->booking_status, ['rejected', 'cancelled'], true) ? 'pending' : ($isCompleted || $booking->booking_status === 'confirmed' ? 'success' : 'pending');
                    $paymentTone = $booking->payment_status === 'paid' ? 'success' : 'pending';
                @endphp
                <article class="booking-record" data-booking-card data-group="{{ $isCompleted ? 'completed' : 'active' }}">
                    <div class="booking-record-main">
                        <div class="booking-record-title"><span class="booking-reference">{{ $booking->reference }}</span><h2>{{ $booking->service?->name ?? 'Repair service' }}</h2></div>
                        <div class="booking-date"><span>Appointment</span><strong>{{ $booking->appointment_at->format('D, M d') }}</strong><small>{{ $booking->appointment_at->format('g:i A') }}</small></div>
                    </div>
                    <div class="booking-record-statuses" aria-label="Booking status details">
                        <div class="booking-status-item"><small>Booking</small><span class="record-status status-{{ $bookingTone }}"><i></i><span class="status-label">{{ $bookingLabel }}</span></span></div>
                        <div class="booking-status-item"><small>Payment</small><span class="record-status status-{{ $paymentTone }}"><i></i><span class="status-label">{{ $paymentLabel }}</span></span></div>
                        <div class="booking-status-item"><small>Repair</small><span class="record-status status-{{ $isCompleted ? 'success' : 'neutral' }}"><i></i><span class="status-label">{{ $isCompleted ? 'Completed' : 'Not started' }}</span></span></div>
                    </div>
                    <a class="record-view-link" href="{{ route('customer.booking.detail', $booking->reference) }}" aria-label="View booking {{ $booking->reference }}"><span class="record-view-content"><span>View booking</span><span class="record-view-arrow" aria-hidden="true">→</span></span></a>
                </article>
            @empty
                <div class="booking-empty"><strong>You don’t have any bookings yet.</strong><span>Start a repair booking and it will appear here.</span><a href="{{ route('customer.book') }}">Book a repair</a></div>
            @endforelse
            @if ($bookings->isNotEmpty())
                <div class="booking-empty" data-booking-empty hidden><strong>No bookings in this view.</strong><span>Try another filter or start a new repair booking.</span></div>
            @endif
        </section>
    </main>
</body>
</html>
