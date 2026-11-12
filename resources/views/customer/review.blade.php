<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Review your Pedalya bike repair booking details.">

        <title>Review booking | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-review.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-review.js'])
    </head>
    <body class="customer-booking-page customer-review-page">
        {{-- Legacy header removed; the shared customer navigation is rendered below.
            <a class="brand" href="{{ route('home') }}" aria-label="Return to Pedalya home">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="10.5"></circle><circle cx="16" cy="16" r="3"></circle><path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path></svg>
                </span>
                <span>Pedalya</span>
            </a>
            <div class="customer-header-actions">
                <span class="preview-pill"><i></i> Frontend preview</span>
                <a class="header-link" href="{{ route('home') }}">Sign out <span aria-hidden="true">↗</span></a>
            </div>
        --}}
@include('customer.partials.navigation')

        <main class="customer-booking-shell">
            <section class="booking-intro review-intro" aria-labelledby="review-title">
                <div>
                    <span class="section-kicker">Almost there</span>
                    <h1 id="review-title">Review your<br><em>repair visit.</em></h1>
                </div>
                <p>Check the details below, then tell us where to send your booking updates.</p>
            </section>

            <form class="review-layout" data-review-form novalidate>
                <section class="review-form-panel" aria-labelledby="contact-title">
                    <div class="panel-heading"><span>01 — Contact details</span><h2 id="contact-title">How can we reach you?</h2></div>

                    <div class="review-fields-two">
                        <label class="review-field"><span>Full name</span><input type="text" name="name" placeholder="Juan Dela Cruz" autocomplete="name" required><small data-error-for="name"></small></label>
                        <label class="review-field"><span>Mobile number</span><input type="tel" name="phone" placeholder="09XX XXX XXXX" autocomplete="tel" required><small data-error-for="phone"></small></label>
                    </div>
                    <label class="review-field"><span>Email address</span><input type="email" name="email" placeholder="you@example.com" autocomplete="email" required><small data-error-for="email"></small></label>
                    <label class="review-field"><span>What should we know about your bike? <em>Optional</em></span><textarea name="notes" rows="3" placeholder="Tell the repair team anything useful about the issue."></textarea></label>

                    <label class="review-consent"><input type="checkbox" name="consent" required><span>I confirm that these booking details are correct.</span><small data-error-for="consent"></small></label>
                    <div class="review-form-message" role="alert" aria-live="polite" data-review-message></div>
                </section>

                <aside class="booking-summary-card" aria-labelledby="summary-title">
                    <div class="panel-heading"><span>02 — Booking summary</span><h2 id="summary-title">Your visit</h2></div>
                    <dl class="review-summary-list">
                        <div><dt>Service</dt><dd data-summary-service>Loading…</dd></div>
                        <div><dt>Appointment</dt><dd data-summary-date>Loading…</dd></div>
                        <div><dt>Duration</dt><dd data-summary-duration>45 min</dd></div>
                        <div><dt>Estimated total</dt><dd class="summary-total" data-summary-price>₱500</dd></div>
                    </dl>
                    <div class="summary-payment"><span>Payment status</span><strong>Pending</strong><small>Payment options will be connected later.</small></div>
                    <button class="booking-next review-submit" type="submit">Continue to confirmation <span aria-hidden="true">→</span></button>
                    <a class="customer-return-link" href="{{ route('customer.schedule') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6-6 6 6 6M4 12h16"/></svg>
                        Change schedule
                    </a>
                </aside>
            </form>

            <nav class="booking-steps booking-steps-after-content" aria-label="Booking progress">
                <a class="booking-step is-complete" href="{{ route('customer.book') }}"><b>✓</b><span>Service</span></a>
                <a class="booking-step is-complete" href="{{ route('customer.schedule') }}"><b>✓</b><span>Schedule</span></a>
                <span class="booking-step is-active"><b>03</b><span>Review</span></span>
                <span class="booking-step"><b>04</b><span>Confirmation</span></span>
            </nav>

            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>This review is a frontend preview. It will not create a booking until the backend phase is connected.</p></div>
        </main>
    </body>
</html>
