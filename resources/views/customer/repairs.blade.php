<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Choose a repair to view its current progress.">
    @include('partials.favicon')
    <title>My repairs | Pedalya</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.googleapis.com/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-repairs.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js'])
</head>
<body class="customer-booking-page customer-repairs-page">
    @include('customer.partials.navigation')
    <main class="customer-booking-shell">
        <section class="repairs-heading" aria-labelledby="repairs-title"><div><span class="section-kicker">Customer workspace</span><h1 id="repairs-title">Your repair<br><em>progress.</em></h1><p>Choose a repair to see its latest workshop update.</p></div><a class="booking-next" href="{{ route('customer.book') }}">Book a repair <span aria-hidden="true">→</span></a></section>
        <section class="repair-choice-grid" aria-label="Your repairs">
            @forelse ($bookings as $booking)
                @php
                    $key = $booking->repair?->status ?? $booking->repair_status ?? ($booking->booking_status === 'completed' ? 'completed' : ($booking->booking_status === 'confirmed' ? 'awaiting_arrival' : 'not_started'));
                    $labels = ['not_started' => 'Not started', 'awaiting_arrival' => 'Awaiting bike arrival', 'at_shop' => 'Bike at shop', 'in_progress' => 'Repair in progress', 'completed' => 'Repair completed', 'claimed' => 'Bike claimed'];
                    $label = $booking->booking_status === 'pending' && $booking->processing_method === 'review_required' ? 'Awaiting manager approval' : ($labels[$key] ?? \Illuminate\Support\Str::headline($booking->booking_status));
                    $tone = in_array($key, ['completed', 'claimed', 'at_shop'], true) ? 'success' : ($key === 'in_progress' ? 'brand' : 'pending');
                    $appointment = $booking->appointment_at->setTimezone(config('booking.timezone'));
                @endphp
                <article class="repair-choice-card"><div class="repair-choice-top"><span>{{ $booking->reference }}</span><span class="repair-choice-icon" aria-hidden="true">✦</span></div><h2>{{ $booking->service?->name ?? 'Repair service' }}</h2><p>{{ $appointment->format('D, M d · g:i A') }}</p><span class="repair-choice-status status-{{ $tone }}"><i></i>{{ $label }}</span><a href="{{ route('customer.repair', $booking->reference) }}">View repair progress <span aria-hidden="true">→</span></a></article>
            @empty
                <div class="booking-empty"><strong>No repair records yet</strong><span>Your confirmed bookings and repair progress will appear here.</span><a href="{{ route('customer.book') }}">Book a repair</a></div>
            @endforelse
        </section>
    </main>
</body>
</html>
