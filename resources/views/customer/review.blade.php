<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Review your Pedalya bike repair booking.">
        <title>Review booking | Pedalya</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-review.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-review.js'])
    </head>
    <body class="customer-booking-page customer-review-page">
        @include('customer.partials.navigation')
        <main class="customer-booking-shell">
            <section class="booking-intro review-intro" aria-labelledby="review-title">
                <div>
                    <span class="section-kicker">Almost there</span>
                    <h1 id="review-title">Review your<br><em>repair visit.</em></h1>
                </div>
                <p>Check your account details and add any notes for the repair team.</p>
            </section>

            <form class="review-layout" data-review-form method="POST" action="{{ route('customer.review.store') }}" novalidate>
                @csrf
                <section class="review-form-panel" aria-labelledby="contact-title">
                    <div class="panel-heading"><span>01 — Contact details</span><h2>How can we reach you?</h2></div>
                    <div class="review-fields-two">
                        <label class="review-field"><span>Full name</span><input type="text" name="customer_name" value="{{ $user->name }}" autocomplete="name" readonly></label>
                        <label class="review-field"><span>Mobile number</span><input type="tel" name="customer_mobile_number" value="{{ $user->mobile_number }}" autocomplete="tel" readonly></label>
                    </div>
                    <label class="review-field"><span>Email address</span><input type="email" name="customer_email" value="{{ $user->email }}" autocomplete="email" readonly></label>
                    <label class="review-field"><span>What should we know about your bike? <em>Optional</em></span><textarea name="notes" rows="3" maxlength="2000" placeholder="Tell the repair team anything useful about the issue.">{{ old('notes') }}</textarea><small data-error-for="notes">{{ $errors->first('notes') }}</small></label>
                    <label class="review-consent"><input type="checkbox" name="consent" value="1" required><span>I confirm that these booking details are correct.</span><small data-error-for="consent">{{ $errors->first('consent') }}</small></label>
                    <div class="review-form-message" role="alert" aria-live="polite" data-review-message>{{ $errors->first('booking') }}</div>
                </section>

                <aside class="booking-summary-card" aria-labelledby="summary-title">
                    <div class="panel-heading"><span>02 — Booking summary</span><h2 id="summary-title">Your visit</h2></div>
                    <dl class="review-summary-list">
                        <div><dt>Service</dt><dd>{{ $service->name }}</dd></div>
                        <div><dt>Appointment</dt><dd>{{ $appointmentAt->format('D, M j · g:i A') }}</dd></div>
                        <div><dt>Duration</dt><dd>{{ $service->duration_minutes }} min</dd></div>
                        <div><dt>Estimated total</dt><dd class="summary-total">&#8369;{{ number_format((float) $service->price, 2) }}</dd></div>
                    </dl>
                    <div class="summary-payment"><span>Payment status</span><strong>Pending</strong><small>PayMongo checkout starts after you confirm this booking.</small></div>
                    <button class="booking-next review-submit" type="submit">Confirm booking <span aria-hidden="true">&#8594;</span></button>
                    <a class="customer-return-link" href="{{ route('customer.schedule') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6-6 6 6 6M4 12h16"/></svg>
                        Change schedule
                    </a>
                </aside>
            </form>

            <nav class="booking-steps booking-steps-after-content" aria-label="Booking progress">
                <a class="booking-step is-complete" href="{{ route('customer.book') }}"><b>&#10003;</b><span>Service</span></a>
                <a class="booking-step is-complete" href="{{ route('customer.schedule') }}"><b>&#10003;</b><span>Schedule</span></a>
                <span class="booking-step is-active"><b>03</b><span>Review</span></span>
                <span class="booking-step"><b>04</b><span>Confirmation</span></span>
            </nav>
        </main>
    </body>
</html>
