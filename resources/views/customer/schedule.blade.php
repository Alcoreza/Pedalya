<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Choose an available appointment time for your Pedalya repair booking.">

        <title>Choose a schedule | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-schedule.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-schedule.js'])
    </head>
    <body class="customer-booking-page customer-schedule-page">
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
            <section class="booking-intro schedule-intro" aria-labelledby="schedule-title">
                <div>
                    <span class="section-kicker">Choose a time</span>
                    <h1 id="schedule-title">Find a time that<br><em>fits your ride.</em></h1>
                </div>
                <div class="selected-service-summary" data-selected-service>Selected service: loading…</div>
            </section>

            <section class="schedule-panel" aria-label="Available appointment times" data-schedule-panel>
                <div class="schedule-panel-heading">
                    <div><span>March 2026</span><h2>Available appointments</h2></div>
                    <div class="schedule-legend"><span><i class="legend-available"></i> Available</span><span><i class="legend-unavailable"></i> Unavailable</span></div>
                </div>

                <div class="calendar-preview-bar" role="note">
                    <span class="calendar-preview-mark" aria-hidden="true">31</span>
                    <div><strong>Pedalya workshop calendar</strong><span>Frontend calendar preview · Google Calendar sync pending</span></div>
                    <span class="calendar-preview-state">Open slots</span>
                </div>

                <div class="date-grid" role="radiogroup" aria-label="Appointment dates">
                    <button class="date-card is-selected" type="button" data-date="Tue, Mar 17" aria-pressed="true"><small>Tue</small><strong>17</strong><span>6 slots</span></button>
                    <button class="date-card" type="button" data-date="Wed, Mar 18" aria-pressed="false"><small>Wed</small><strong>18</strong><span>4 slots</span></button>
                    <button class="date-card" type="button" data-date="Thu, Mar 19" aria-pressed="false"><small>Thu</small><strong>19</strong><span>2 slots</span></button>
                    <button class="date-card is-unavailable" type="button" data-date="Fri, Mar 20" aria-pressed="false" disabled><small>Fri</small><strong>20</strong><span>Fully booked</span></button>
                    <button class="date-card" type="button" data-date="Sat, Mar 21" aria-pressed="false"><small>Sat</small><strong>21</strong><span>5 slots</span></button>
                </div>

                <div class="slot-heading"><span data-date-label>Tuesday, March 17</span><strong>Select a start time</strong></div>
                <div class="slot-grid" role="radiogroup" aria-label="Appointment times">
                    @foreach (['9:00 AM', '10:30 AM', '12:00 PM', '1:30 PM', '3:00 PM', '4:30 PM'] as $slot)
                        <button class="time-slot" type="button" data-slot="{{ $slot }}" aria-pressed="false">{{ $slot }}<span>Available</span></button>
                    @endforeach
                </div>
            </section>

            <nav class="booking-steps booking-steps-after-content" aria-label="Booking progress">
                <a class="booking-step is-complete" href="{{ route('customer.book') }}"><b>✓</b><span>Service</span></a>
                <span class="booking-step is-active"><b>02</b><span>Schedule</span></span>
                <span class="booking-step"><b>03</b><span>Review</span></span>
                <span class="booking-step"><b>04</b><span>Confirmation</span></span>
            </nav>

            <div class="booking-action-bar schedule-action-bar">
                <p class="selection-message" data-schedule-message role="status" aria-live="polite">Choose a date and time to continue.</p>
                <a class="customer-return-link" href="{{ route('customer.book') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6-6 6 6 6M4 12h16"/></svg>
                    Back to services
                </a>
                <button class="booking-next" type="button" data-schedule-next disabled>Continue to review <span aria-hidden="true">→</span></button>
            </div>

            <div class="booking-preview-note" role="note">
                <span aria-hidden="true">✦</span>
                <p>Availability shown here is sample data for the frontend preview. Live schedules will be connected in the backend phase.</p>
            </div>
        </main>
    </body>
</html>
