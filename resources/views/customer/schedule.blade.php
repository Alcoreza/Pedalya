<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Choose an available appointment time for your Pedalya repair.">
        @include('partials.favicon')
        <title>Choose a schedule | Pedalya</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-schedule.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-schedule.js'])
    </head>
    <body class="customer-booking-page customer-schedule-page">
        @include('customer.partials.navigation')
        <main class="customer-booking-shell">
            <section class="booking-intro schedule-intro" aria-labelledby="schedule-title">
                <div>
                    <span class="section-kicker">Choose a time</span>
                    <h1 id="schedule-title">Find a time that<br><em>fits your ride.</em></h1>
                </div>
                <div class="selected-service-summary">{{ $service->name }} · {{ $service->duration_minutes }} min · &#8369;{{ number_format((float) $service->price, 0) }}</div>
            </section>

            <section class="schedule-panel" aria-label="Available appointment times" data-schedule-panel data-service-id="{{ $service->id }}" data-calendar-returned="{{ session('calendar_returned') ? 'true' : 'false' }}">
                <div class="schedule-panel-heading">
                    <div><span data-calendar-month>{{ $selectedDate?->format('F Y') ?? 'Upcoming dates' }}</span><h2>Available appointments</h2></div>
                    <div class="schedule-legend"><span><i class="legend-available"></i> Instant confirmation</span><span><i class="legend-review"></i> Approval required</span></div>
                </div>

                <div class="calendar-preview-bar" role="note">
                    <span class="calendar-preview-mark" data-calendar-day>{{ $selectedDate?->format('d') ?? '—' }}</span>
                    <div><strong>Pedalya workshop calendar</strong><span>Live availability · times shown in Asia/Manila</span></div>
                    <span class="calendar-preview-state"><span data-open-slots>{{ $dateOptions->sum('auto_confirm_slots') }}</span> instant · <span data-review-slots>{{ $dateOptions->sum('review_slots') }}</span> approval needed</span>
                </div>
                <div class="schedule-calendar-connect-row">
                    @if ($calendarConnection)
                        <span class="calendar-connected-label">Google Calendar connected: {{ $calendarConnection->google_email }}</span>
                    @else
                        <a class="calendar-connect-link" href="{{ route('customer.calendar.connect', ['return_to' => 'schedule']) }}">Connect Google Calendar</a>
                    @endif
                    @if (session('calendar_message') || session('calendar_error'))
                        <span role="status">{{ session('calendar_message') ?? session('calendar_error') }}</span>
                    @endif
                </div>

                <div class="date-grid" aria-label="Available dates">
                    @forelse ($dateOptions as $option)
                        <button class="date-card {{ $selectedDate?->format('Y-m-d') === $option['key'] ? 'is-selected' : '' }} {{ $option['available_slots'] === 0 ? 'is-unavailable' : '' }}"
                           type="button" data-date-option="{{ $option['key'] }}"
                           aria-pressed="{{ $selectedDate?->format('Y-m-d') === $option['key'] ? 'true' : 'false' }}">
                            <small>{{ $option['weekday'] }}</small>
                            <strong>{{ $option['day'] }}</strong>
                            <span>
                                @if ($option['auto_confirm_slots'] > 0)
                                    {{ $option['auto_confirm_slots'] }} instant{{ $option['review_slots'] > 0 ? ' · '.$option['review_slots'].' approval' : '' }}
                                @else
                                    Approval only
                                @endif
                            </span>
                        </button>
                    @empty
                        <p>No appointment dates are available for the rest of this month.</p>
                    @endforelse
                </div>

                <script type="application/json" data-schedule-options>{!! json_encode($dateOptionsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>

                <form method="POST" action="{{ route('customer.schedule.store') }}" data-schedule-form>
                    @csrf
                    <input type="hidden" name="date" value="{{ $selectedDate?->format('Y-m-d') }}">
                    <input type="hidden" name="time" value="" data-selected-time>
                    <div class="slot-heading"><span data-selected-long-date>{{ $selectedDate?->format('l, F j') ?? 'No date selected' }}</span><strong>Select a start time</strong></div>
                    <div class="slot-grid" role="radiogroup" aria-label="Available appointment times">
                        @forelse ($availableSlots as $slot)
                            <button class="time-slot {{ $slot['requires_review'] ? 'is-review' : '' }}" type="button" data-slot="{{ $slot['time'] }}" data-review="{{ $slot['requires_review'] ? 'true' : 'false' }}" aria-pressed="false">{{ $slot['label'] }}<span>{{ $slot['requires_review'] ? 'Approval required' : 'Instant confirmation' }}</span></button>
                        @empty
                            <p>No slots are available for this date and service. Choose another date.</p>
                        @endforelse
                    </div>

                    <nav class="booking-steps booking-steps-after-content" aria-label="Booking progress">
                        <a class="booking-step is-complete" href="{{ route('customer.book') }}"><b>&#10003;</b><span>Service</span></a>
                        <span class="booking-step is-active"><b>02</b><span>Schedule</span></span>
                        <span class="booking-step"><b>03</b><span>Review</span></span>
                        <span class="booking-step"><b>04</b><span>Confirmation</span></span>
                    </nav>

                    <div class="booking-action-bar schedule-action-bar">
                        <p class="selection-message" data-schedule-message role="status" aria-live="polite">{{ $errors->first('time') ?: 'Choose a date and time to continue.' }}</p>
                        <a class="customer-return-link" href="{{ route('customer.book') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6-6 6 6 6M4 12h16"/></svg>
                            Back to services
                        </a>
                        <button class="booking-next" type="submit" data-schedule-next disabled>Continue to review <span aria-hidden="true">&#8594;</span></button>
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
