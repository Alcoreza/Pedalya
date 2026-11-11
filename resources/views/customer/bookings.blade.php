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
        <header class="customer-header">
            <a class="brand" href="{{ route('home') }}" aria-label="Return to Pedalya home">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="10.5"></circle><circle cx="16" cy="16" r="3"></circle><path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path></svg></span>
                <span>Pedalya</span>
            </a>
            <div class="customer-header-actions"><span class="preview-pill"><i></i> Frontend preview</span><a class="header-link" href="{{ route('home') }}">Sign out <span aria-hidden="true">↗</span></a></div>
        </header>
@include('customer.partials.navigation')

        <main class="customer-booking-shell">
            <section class="bookings-heading" aria-labelledby="bookings-title">
                <div><span class="section-kicker">Customer workspace</span><h1 id="bookings-title">Your repair<br><em>bookings.</em></h1></div>
                <a class="booking-next new-booking-link" href="{{ route('customer.book') }}">Book a repair <span aria-hidden="true">→</span></a>
            </section>

            <section class="bookings-summary" aria-label="Booking summary"><div><span>Total bookings</span><strong>03</strong></div><div><span>Needs your attention</span><strong class="summary-attention">01</strong></div><div><span>Completed repairs</span><strong>01</strong></div></section>

            <div class="booking-list-toolbar"><div class="booking-filters" role="group" aria-label="Filter bookings"><button class="booking-filter is-active" type="button" data-filter="all">All bookings</button><button class="booking-filter" type="button" data-filter="active">Active</button><button class="booking-filter" type="button" data-filter="completed">Completed</button></div><span class="toolbar-note">Sample customer records</span></div>

            <section class="booking-list" aria-label="Your bookings" data-booking-list>
                @foreach ([
                    ['reference' => 'PDL-1048', 'service' => 'Brake inspection', 'date' => 'Tue, Mar 17', 'time' => '9:00 AM', 'booking' => ['label' => 'Pending review', 'tone' => 'pending'], 'payment' => ['label' => 'Payment pending', 'tone' => 'pending'], 'repair' => ['label' => 'Not started', 'tone' => 'neutral'], 'group' => 'active'],
                    ['reference' => 'PDL-1037', 'service' => 'Full tune-up', 'date' => 'Fri, Mar 13', 'time' => '1:30 PM', 'booking' => ['label' => 'Confirmed', 'tone' => 'success'], 'payment' => ['label' => 'Paid', 'tone' => 'success'], 'repair' => ['label' => 'In progress', 'tone' => 'brand'], 'group' => 'active'],
                    ['reference' => 'PDL-1019', 'service' => 'Wheel alignment', 'date' => 'Mon, Mar 02', 'time' => '10:30 AM', 'booking' => ['label' => 'Completed', 'tone' => 'success'], 'payment' => ['label' => 'Paid', 'tone' => 'success'], 'repair' => ['label' => 'Completed', 'tone' => 'success'], 'group' => 'completed'],
                ] as $booking)
                    <article class="booking-record" data-booking-card data-group="{{ $booking['group'] }}">
                        <div class="booking-record-main"><div class="booking-record-title"><span class="booking-reference">{{ $booking['reference'] }}</span><h2>{{ $booking['service'] }}</h2></div><div class="booking-date"><span>Appointment</span><strong>{{ $booking['date'] }}</strong><small>{{ $booking['time'] }}</small></div></div>
                        <div class="booking-record-statuses" aria-label="Booking status details"><div class="booking-status-item"><small>Booking</small><span class="record-status status-{{ $booking['booking']['tone'] }}"><i></i>{{ $booking['booking']['label'] }}</span></div><div class="booking-status-item"><small>Payment</small><span class="record-status status-{{ $booking['payment']['tone'] }}"><i></i>{{ $booking['payment']['label'] }}</span></div><div class="booking-status-item"><small>Repair</small><span class="record-status status-{{ $booking['repair']['tone'] }}"><i></i>{{ $booking['repair']['label'] }}</span></div></div>
                        <a class="record-view-link" href="{{ route('customer.booking.detail', $booking['reference']) }}" aria-label="View booking {{ $booking['reference'] }}"><span class="record-view-content"><span>View booking</span><span class="record-view-arrow" aria-hidden="true">→</span></span></a>
                    </article>
                @endforeach
                <div class="booking-empty" data-booking-empty hidden><strong>No bookings in this view.</strong><span>Try another filter or start a new repair booking.</span></div>
            </section>

            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>These are representative frontend records. Your real bookings will appear here after the backend connection is added.</p></div>
        </main>
    </body>
</html>
