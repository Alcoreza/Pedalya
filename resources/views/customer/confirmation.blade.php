<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Your Pedalya bike repair booking confirmation.">
        <title>Booking confirmed | Pedalya</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-confirmation.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js'])
    </head>
    <body class="customer-booking-page customer-confirmation-page">
        @include('customer.partials.navigation')
        <main class="customer-booking-shell confirmation-shell">
            <section class="confirmation-hero" aria-labelledby="confirmation-title">
                <div class="confirmation-mark" aria-hidden="true">&#10003;</div>
                <span class="section-kicker">Booking saved</span>
                <h1 id="confirmation-title">Your repair request<br><em>is on its way.</em></h1>
                <p>Your booking has been saved. The workshop will review the request and its payment status will update after PayMongo confirms payment.</p>
            </section>

            <section class="confirmation-card" aria-labelledby="booking-reference-title">
                <div class="confirmation-card-head"><span>Booking reference</span><strong id="booking-reference-title">{{ $booking->reference }}</strong></div>
                <div class="confirmation-details">
                    <div><span>Service</span><strong>{{ $booking->service->name }}</strong></div>
                    <div><span>Appointment</span><strong>{{ $booking->appointment_at->setTimezone(config('booking.timezone'))->format('D, M j · g:i A') }}</strong></div>
                    <div><span>Customer</span><strong>{{ $booking->customer_name }}</strong></div>
                    <div><span>Estimated total</span><strong>&#8369;{{ number_format((float) $booking->service_price, 2) }}</strong></div>
                </div>
                <div class="confirmation-status">
                    <span class="status-dot"></span>
                    <div><strong>Payment {{ $booking->payment_status }}</strong><p>Booking status: {{ ucfirst($booking->booking_status) }}.</p></div>
                </div>
            </section>

            @if ($errors->any())
                <div class="review-form-message" role="alert">{{ $errors->first() }}</div>
            @endif

            <nav class="booking-steps booking-steps-after-content" aria-label="Booking progress">
                <span class="booking-step is-complete"><b>&#10003;</b><span>Service</span></span>
                <span class="booking-step is-complete"><b>&#10003;</b><span>Schedule</span></span>
                <span class="booking-step is-complete"><b>&#10003;</b><span>Review</span></span>
                <span class="booking-step is-active"><b>04</b><span>Confirmation</span></span>
            </nav>

            <div class="confirmation-actions">
                @if ($booking->payment_status !== 'paid')
                    <form method="POST" action="{{ route('customer.checkout', ['reference' => $booking->reference]) }}">
                        @csrf
                        <button class="booking-next" type="submit">Pay with PayMongo <span aria-hidden="true">&#8599;</span></button>
                    </form>
                @endif
                <a class="booking-next" href="{{ route('customer.book') }}">Start another booking <span aria-hidden="true">&#8594;</span></a>
                <a class="confirmation-secondary" href="{{ route('customer.dashboard') }}">Return to dashboard</a>
            </div>
        </main>
    </body>
</html>
