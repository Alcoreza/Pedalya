<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Review the payment details for your Pedalya bike repair booking.">

        @include('partials.favicon')
        <title>Payment | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-payment.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js'])
    </head>
    <body class="customer-booking-page customer-payment-page">
        @include('customer.partials.navigation')

        <main class="customer-booking-shell payment-shell">
            <a class="customer-return-link" href="{{ route('customer.booking.detail', $booking->reference) }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6-6 6 6 6M4 12h16"/></svg>
                Back to booking
            </a>
            <section class="payment-heading" aria-labelledby="payment-title"><div><span class="section-kicker">Payment details</span><h1 id="payment-title">Complete your<br><em>repair payment.</em></h1><span class="detail-reference">{{ $booking->reference }}</span></div><div class="payment-status-card"><span>Current status</span><strong>{{ $booking->payment?->status === 'paid' ? 'Paid' : ucfirst($booking->payment?->status ?? $booking->payment_status) }}</strong></div></section>

            <div class="payment-layout">
                <section class="payment-card payment-provider-card"><div class="payment-provider-mark" aria-hidden="true">M</div><span class="section-kicker">Secure checkout</span><h2>Continue with Maya</h2><p>Complete payment through Maya’s hosted checkout page.</p><div class="payment-provider-points"><span>Secure hosted checkout</span><span>PHP payment</span><span>Booking reference matched</span></div>
                    @if ($errors->has('payment'))<div class="review-form-message" role="alert">{{ $errors->first('payment') }}</div>@endif
                    @if ($booking->payment?->status === 'paid')
                        <p role="status">This booking is already paid.</p>
                    @elseif ($booking->booking_status !== 'confirmed')
                        <p role="status">Maya checkout will be available after the manager approves this booking.</p>
                    @else
                        <form method="POST" action="{{ route('customer.checkout', ['reference' => $booking->reference]) }}">@csrf<button class="booking-next payment-placeholder-button" type="submit">Continue to Maya <span aria-hidden="true">→</span></button></form>
                    @endif
                </section>
                <aside class="payment-card payment-summary-card"><div class="detail-card-heading"><div><span>Booking summary</span><h2>What you’re paying for</h2></div></div><dl><div><dt>Booking reference</dt><dd>{{ $booking->reference }}</dd></div><div><dt>Service</dt><dd>{{ $booking->service->name }}</dd></div><div><dt>Appointment</dt><dd>{{ $booking->appointment_at->setTimezone(config('booking.timezone'))->format('D, M j · g:i A') }}</dd></div><div><dt>Total</dt><dd class="payment-total">₱{{ number_format((float) $booking->service_price, 2) }}</dd></div></dl><a class="payment-back-link" href="{{ route('customer.bookings') }}">View all bookings</a></aside>
            </div>

            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>Checkout opens securely from your confirmed booking. Payment status updates after Maya notifies Pedalya.</p></div>
        </main>
    </body>
</html>
