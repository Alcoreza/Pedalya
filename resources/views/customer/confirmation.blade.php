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
                <div class="confirmation-mark {{ $booking->booking_status === 'pending' && $booking->processing_method === 'review_required' ? 'is-pending' : ($booking->booking_status === 'rejected' ? 'is-rejected' : '') }}" aria-hidden="true">{{ $booking->booking_status === 'pending' && $booking->processing_method === 'review_required' ? '…' : ($booking->booking_status === 'rejected' ? '×' : '✓') }}</div>
                <span class="section-kicker">
                    @if ($booking->booking_status === 'pending' && $booking->processing_method === 'review_required') Approval required
                    @elseif ($booking->booking_status === 'rejected') Request not approved
                    @else Booking confirmed
                    @endif
                </span>
                <h1 id="confirmation-title">
                    @if ($booking->booking_status === 'pending' && $booking->processing_method === 'review_required')
                        Awaiting manager<br><em>approval.</em>
                    @elseif ($booking->booking_status === 'rejected')
                        This time could<br><em>not be approved.</em>
                    @else
                        Your appointment<br><em>is confirmed.</em>
                    @endif
                </h1>
                <p>
                    @if ($booking->processing_method === 'review_required' && $booking->booking_status === 'pending')
                        This date and time already has a booking. Your request is with the manager; you can pay if it is approved.
                    @elseif ($booking->booking_status === 'rejected')
                        The manager couldn’t approve this time. You can choose another date or time and submit a new booking.
                    @else
                        Your booking is confirmed. {{ $booking->payment_status === 'paid' ? 'Your payment is received.' : 'Complete payment with Maya to finish your reservation.' }}
                    @endif
                </p>
            </section>

            <section class="confirmation-card" aria-labelledby="booking-reference-title">
                <div class="confirmation-card-head"><span>Booking reference</span><strong id="booking-reference-title">{{ $booking->reference }}</strong></div>
                <div class="confirmation-details">
                    <div><span>Service</span><strong>{{ $booking->service->name }}</strong></div>
                    <div><span>Appointment</span><strong>{{ $booking->appointment_at->setTimezone(config('booking.timezone'))->format('D, M j · g:i A') }}</strong></div>
                    <div><span>Customer</span><strong>{{ $booking->customer_name }}</strong></div>
                    <div><span>Estimated total</span><strong>&#8369;{{ number_format((float) $booking->service_price, 2) }}</strong></div>
                </div>
                <div class="confirmation-status {{ $booking->booking_status === 'pending' && $booking->processing_method === 'review_required' ? 'is-pending' : ($booking->booking_status === 'rejected' ? 'is-rejected' : 'is-confirmed') }}">
                    <span class="status-dot"></span>
                    <div>
                        @if ($booking->booking_status === 'pending' && $booking->processing_method === 'review_required')
                            <strong>Awaiting manager approval</strong><p>Payment is not available yet.</p>
                        @elseif ($booking->booking_status === 'rejected')
                            <strong>Not approved</strong><p>No payment is due for this booking.</p>
                        @else
                            <strong>Confirmed{{ $booking->payment_status === 'paid' ? ' · Paid' : ' · Payment pending' }}</strong><p>Your appointment is confirmed.</p>
                        @endif
                    </div>
                </div>
            </section>

            <section class="confirmation-calendar" aria-live="polite">
                @if ($booking->booking_status === 'pending' && $booking->processing_method === 'review_required')
                    <div><strong>Google Calendar update after approval</strong><p>Your appointment will be added to Google Calendar after the manager approves it.{{ $booking->user?->googleCalendarConnection ? '' : ' Connect your account from your profile to enable calendar updates.' }}</p></div>
                @elseif (! $booking->user?->googleCalendarConnection)
                    <div><strong>Add this appointment to your Google Calendar</strong><p>Connect your Google account and we’ll add the selected date and time for you.</p></div>
                    <a href="{{ route('customer.calendar.connect') }}">Connect Google Calendar <span aria-hidden="true">&#8599;</span></a>
                @elseif ($booking->calendar_status === 'synchronized')
                    <div><strong>Appointment added to Google Calendar</strong><p>Connected account: {{ $booking->user->googleCalendarConnection->google_email }}</p></div>
                @elseif ($booking->calendar_status === 'failed')
                    <div><strong>Calendar sync needs attention</strong><p>The booking is saved, but Google Calendar could not be updated. You can reconnect your calendar from your profile.</p></div>
                @endif
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
                @if ($booking->payment_status !== 'paid' && $booking->booking_status === 'confirmed')
                    <form method="POST" action="{{ route('customer.checkout', ['reference' => $booking->reference]) }}">
                        @csrf
                        <button class="booking-next" type="submit">Pay with Maya <span aria-hidden="true">&#8599;</span></button>
                    </form>
                @elseif ($booking->booking_status === 'pending')
                    <p class="confirmation-waiting">Payment will be available if the manager approves your requested time.</p>
                @endif
                <a class="booking-next" href="{{ route('customer.book') }}">Start another booking <span aria-hidden="true">&#8594;</span></a>
                <a class="confirmation-secondary" href="{{ route('customer.dashboard') }}">Return to dashboard</a>
            </div>
        </main>
    </body>
</html>
