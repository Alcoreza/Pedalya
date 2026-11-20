<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Your Pedalya customer dashboard for bike repair bookings and updates.">
    @include('partials.favicon')
    <title>Your dashboard | Pedalya</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-dashboard.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js'])
</head>
<body class="customer-booking-page customer-dashboard-page">
    @include('customer.partials.navigation')
    <main class="customer-booking-shell">
        <section class="dashboard-welcome" aria-labelledby="dashboard-title">
            <div><span class="section-kicker">Customer dashboard</span><h1 id="dashboard-title">Good morning,<br><em>{{ $firstName }}.</em></h1><p>Here’s the latest on your repair journey.</p></div>
            <a class="booking-next dashboard-book-link" href="{{ route('customer.book') }}">Book a repair <span aria-hidden="true">→</span></a>
        </section>

        <section class="dashboard-stats" aria-label="Customer summary">
            <article><span>Active bookings</span><strong>{{ str_pad((string) $activeBookingsCount, 2, '0', STR_PAD_LEFT) }}</strong><small>Awaiting or underway</small></article>
            <article><span>Needs attention</span><strong class="dashboard-stat-attention">{{ str_pad((string) $attentionCount, 2, '0', STR_PAD_LEFT) }}</strong><small>Approval or payment needed</small></article>
            <article><span>Completed repairs</span><strong>{{ str_pad((string) $completedCount, 2, '0', STR_PAD_LEFT) }}</strong><small>All completed bookings</small></article>
        </section>

        <section class="dashboard-feature-grid" aria-label="Current booking overview">
            <article class="dashboard-next-card">
                <div class="dashboard-card-top"><span>Next appointment</span>
                    @if ($upcomingBooking)
                        @php
                            $upcomingRepairStatus = $upcomingBooking->repair?->status;
                            $upcomingStatusLabel = match ($upcomingRepairStatus) {
                                'awaiting_arrival' => 'Awaiting arrival', 'at_shop' => 'Bike at shop',
                                'in_progress' => 'Repair in progress', 'completed' => 'Repair completed', 'claimed' => 'Bike claimed',
                                default => $upcomingBooking->booking_status === 'pending' && $upcomingBooking->processing_method === 'review_required'
                                    ? 'Awaiting approval' : \Illuminate\Support\Str::headline($upcomingBooking->booking_status),
                            };
                            $upcomingStatusTone = in_array($upcomingRepairStatus, ['completed', 'claimed'], true)
                                ? 'status-success' : ($upcomingRepairStatus === 'in_progress' ? 'status-brand' : ($upcomingBooking->booking_status === 'confirmed' ? 'status-success' : 'status-pending'));
                        @endphp
                        <span class="record-status {{ $upcomingStatusTone }}"><i></i>{{ $upcomingStatusLabel }}</span>
                    @endif
                </div>
                @if ($upcomingBooking)
                    @php
                        $upcomingAppointmentAt = $upcomingBooking->appointment_at->setTimezone(config('booking.timezone'));
                    @endphp
                    <div class="dashboard-appointment">
                        <time class="dashboard-date" datetime="{{ $upcomingAppointmentAt->toIso8601String() }}"><small>{{ $upcomingAppointmentAt->format('D') }}</small><strong class="dashboard-date-number">{{ $upcomingAppointmentAt->format('d') }}</strong><span>{{ $upcomingAppointmentAt->format('M Y') }}</span></time>
                        <div><span class="dashboard-reference">{{ $upcomingBooking->reference }}</span><h2>{{ $upcomingBooking->service?->name ?? 'Repair service' }}</h2><p>{{ $upcomingAppointmentAt->format('g:i A') }} · Pedalya Bike Repair</p></div>
                    </div>
                    <div class="dashboard-card-footer"><span>{{ $upcomingBooking->booking_status === 'pending' && $upcomingBooking->processing_method === 'review_required' ? 'Payment after approval' : \Illuminate\Support\Str::headline($upcomingBooking->payment_status).' payment' }}</span><a href="{{ route('customer.booking.detail', $upcomingBooking->reference) }}">View booking details <span aria-hidden="true">↗</span></a></div>
                @else
                    <div class="dashboard-appointment"><div><h2>No upcoming appointment</h2><p>Your upcoming bookings will appear here.</p></div></div>
                    <div class="dashboard-card-footer"><span>Ready when you are</span><a href="{{ route('customer.book') }}">Book a repair <span aria-hidden="true">↗</span></a></div>
                @endif
            </article>
            <article class="dashboard-progress-card">
                <div class="dashboard-card-top"><span>Booking overview</span><span class="progress-percent">{{ $activeBookingsCount }} active</span></div>
                <h2>{{ $activeBookingsCount > 0 ? 'Your repair requests are in progress.' : 'Your repair journey starts here.' }}</h2>
                <p>{{ $activeBookingsCount > 0 ? 'Check your booking list for the latest appointment and payment statuses.' : 'Book a repair and you can follow its status from your dashboard.' }}</p>
                <a href="{{ route('customer.bookings') }}">View all bookings <span aria-hidden="true">→</span></a>
            </article>
        </section>

        <section class="dashboard-lower-grid">
            <article class="recent-bookings-panel">
                <div class="activity-intro"><div><span>Activity</span><h2>Booking activity</h2><p>Your latest repair bookings.</p></div><a href="{{ route('customer.bookings') }}"><span class="activity-link-label">View all</span></a></div>
                <div class="activity-feed" aria-label="Recent booking activity">
                    @foreach ($recentBookings as $booking)
                        @php
                            $localAppointmentAt = $booking->appointment_at->setTimezone(config('booking.timezone'));
                        @endphp
                        @php
                            $repairStatus = $booking->repair?->status;
                            $repairIsComplete = $booking->booking_status === 'completed' || in_array($repairStatus, ['completed', 'claimed'], true);
                            $repairStatusLabel = match ($repairStatus) {
                                'awaiting_arrival' => 'Awaiting arrival', 'at_shop' => 'Bike at shop',
                                'in_progress' => 'Repair in progress', 'completed' => 'Repair completed', 'claimed' => 'Bike claimed',
                                default => $booking->booking_status === 'pending' && $booking->processing_method === 'review_required'
                                    ? 'Awaiting approval' : \Illuminate\Support\Str::headline($booking->booking_status),
                            };
                            $repairStatusTone = $repairIsComplete ? 'status-success'
                                : ($repairStatus === 'in_progress' ? 'status-brand'
                                    : (in_array($booking->booking_status, ['pending', 'rejected', 'cancelled'], true) ? 'status-pending' : 'status-brand'));
                        @endphp
                        <a class="dashboard-booking-row" href="{{ route('customer.booking.detail', $booking->reference) }}">
                            <div class="dashboard-row-icon {{ $repairIsComplete ? 'row-icon-complete' : '' }}" aria-hidden="true"><svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="14"></circle><circle cx="24" cy="24" r="4"></circle><path d="M24 10v10M24 28v10M10 24h10M28 24h10"></path></svg></div>
                            <div class="dashboard-booking-copy"><strong>{{ $booking->service?->name ?? 'Repair service' }}</strong><span class="dashboard-booking-meta"><span class="dashboard-booking-reference">{{ $booking->reference }}</span><span class="dashboard-booking-divider" aria-hidden="true">·</span><span>{{ $localAppointmentAt->format('M d, g:i A') }}</span></span></div>
                            <span class="record-status {{ $repairStatusTone }}"><i></i>{{ $repairStatusLabel }}</span>
                        </a>
                    @endforeach
                    @if ($recentBookings->isEmpty())
                        <p>You haven’t made a booking yet. <a href="{{ route('customer.book') }}">Book your first repair.</a></p>
                    @endif
                </div>
            </article>
            <article class="dashboard-panel dashboard-location-panel">
                <div class="dashboard-panel-heading"><div><span>Workshop</span><h2>Your repair destination</h2></div><span class="appointment-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12Z"></path><circle cx="12" cy="9" r="2.25"></circle></svg></span></div>
                <div class="dashboard-mini-map" role="group" aria-label="Map of the Pedalya Bike Repair workshop"><iframe
    src="https://www.google.com/maps/embed?pb=!4v1794719640857!6m8!1m7!1s-NHNApbTEcUDyHvdniN88A!2m2!1d14.11882973585545!2d121.556903219214!3f38.98520599254551!4f-18.004327157487083!5f0.7820865974627469"
    title="Pedalya Bike Repair workshop"
    loading="lazy"
    referrerpolicy="strict-origin-when-cross-origin">
    </iframe><span>Pedalya Bike Repair</span></div>
                <p><strong>Lucban-Sampaloc RD</strong><span>Lucban, Quezon</span></p>
                <a href="{{ route('customer.bookings') }}">View your bookings <span aria-hidden="true">↗</span></a>
            </article>
        </section>
    </main>
</body>
</html>
