<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Review and manage Pedalya customer booking requests.">

        <title>Bookings | Pedalya Manager</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/pages/manager-bookings.css', 'resources/js/app.js', 'resources/js/pages/manager-workspace.js', 'resources/js/pages/manager-bookings.js'])
    </head>
    <body class="manager-dashboard-page manager-bookings-page">
        <a class="manager-skip-link" href="#bookings-content">Skip to bookings</a>

        <div class="manager-shell" data-manager-shell>
            <div class="manager-overlay" data-manager-overlay></div>

            <aside class="manager-sidebar" id="manager-navigation" data-manager-sidebar aria-label="Manager navigation">
                <div class="sidebar-top">
                    <a class="manager-brand" href="{{ route('home') }}" aria-label="Pedalya home">
                        <span class="manager-brand-mark" aria-hidden="true">
                            <svg viewBox="0 0 32 32">
                                <circle cx="16" cy="16" r="10.5"></circle>
                                <circle cx="16" cy="16" r="3"></circle>
                                <path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path>
                            </svg>
                        </span>
                        <span>Pedalya</span>
                        <small>MANAGER</small>
                    </a>

                    <button class="sidebar-close" type="button" aria-label="Close navigation" data-sidebar-close>
                        <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </button>
                </div>

                <nav class="manager-nav">
                    <span class="nav-label">Workspace</span>
                    <a href="{{ route('manager.dashboard') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h6V4H4v9Zm0 7h6v-4H4v4Zm10 0h6v-9h-6v9Zm0-16v4h6V4h-6Z"></path></svg>
                        <span>Dashboard</span>
                    </a>
                    <a class="is-active" href="{{ route('manager.bookings.index') }}" aria-current="page">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"></path></svg>
                        <span>Bookings</span>
                        @if ($navigation['pending_bookings'] > 0)
                            <i>{{ $navigation['pending_bookings'] }}</i>
                        @endif
                    </a>
                    <a href="{{ route('manager.schedule.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7v5l3 2"></path></svg>
                        <span>Schedule</span>
                    </a>
                    <a href="{{ route('manager.repairs.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 6.5 3-3 3 3-3 3M4 20l7.5-7.5M6.5 4.5l13 13M4 7l3-3"></path></svg>
                        <span>Repairs</span>
                    </a>
                    <a href="{{ route('manager.activity.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 3.5 7.5v9L12 21l8.5-4.5v-9L12 3Z"></path><path d="M12 8v5M12 16.5v.1"></path></svg>
                        <span>Activity</span>
                    </a>
                </nav>

                <div class="sidebar-foot">
                    <div class="manager-profile">
                        <span>{{ $manager['initials'] }}</span>
                        <div>
                            <strong>{{ $manager['name'] }}</strong>
                            <small>{{ $manager['role'] }}</small>
                        </div>
                        <button type="button" aria-label="Open manager menu">
                            <svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="1"></circle><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle></svg>
                        </button>
                    </div>
                    <a class="sidebar-signout" href="{{ route('login') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 5H5v14h5M14 8l4 4-4 4M8 12h10"></path></svg>
                        Sign out
                    </a>
                </div>
            </aside>

            <main class="manager-main" id="bookings-content">
                <header class="manager-topbar bookings-enter" style="--enter-delay: 30ms">
                    <button class="sidebar-open" type="button" aria-expanded="false" aria-controls="manager-navigation" data-sidebar-open>
                        <span class="sr-only">Open manager navigation</span>
                        <span></span><span></span>
                    </button>

                    <div class="topbar-title">
                        <span>Manager workspace</span>
                        <strong>Bookings</strong>
                    </div>

                    <div class="topbar-actions">
                        <a class="topbar-primary" href="#booking-filters">Find a booking <span aria-hidden="true">↘</span></a>
                    </div>
                </header>

                @if (session('booking_notice'))
                    <div class="booking-notice" role="status" data-booking-notice>
                        <span aria-hidden="true">✓</span>
                        <p>{{ session('booking_notice') }}</p>
                        <button type="button" aria-label="Dismiss notification" data-notice-dismiss>×</button>
                    </div>
                @endif

                <section class="bookings-intro bookings-enter" style="--enter-delay: 80ms" aria-labelledby="bookings-title">
                    <div>
                        <span class="intro-kicker">Request control</span>
                        <h1 id="bookings-title">The booking<br><em>desk.</em></h1>
                    </div>
                    <p>Bookings are confirmed automatically while capacity is available. Only schedule overlaps that exceed capacity are sent for manager review.</p>
                </section>

                <section class="booking-summary bookings-enter" style="--enter-delay: 130ms" aria-label="Booking overview">
                    <article class="summary-primary">
                        <span>All requests</span>
                        <strong>{{ str_pad($summary['all'], 2, '0', STR_PAD_LEFT) }}</strong>
                    </article>
                    <article>
                        <i class="summary-dot dot-amber"></i>
                        <span>Schedule review</span>
                        <strong>{{ $summary['review'] }}</strong>
                    </article>
                    <article>
                        <i class="summary-dot dot-green"></i>
                        <span>Auto-confirmed</span>
                        <strong>{{ $summary['automatic'] }}</strong>
                    </article>
                    <article>
                        <i class="summary-dot dot-red"></i>
                        <span>Calendar follow-up</span>
                        <strong>{{ $summary['calendar_follow_up'] }}</strong>
                    </article>
                </section>

                <form class="booking-filters bookings-enter" id="booking-filters" style="--enter-delay: 180ms" method="GET" action="{{ route('manager.bookings.index') }}">
                    <label class="filter-search">
                        <span class="sr-only">Search bookings</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
                        <input name="search" type="search" value="{{ $filters['search'] }}" placeholder="Reference, customer, email, or service">
                    </label>

                    <label>
                        <span>Booking</span>
                        <select name="booking_status">
                            <option value="">All states</option>
                            @foreach ($filterOptions['booking'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['booking_status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Processing</span>
                        <select name="processing_method">
                            <option value="">All methods</option>
                            @foreach ($filterOptions['processing'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['processing_method'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Payment</span>
                        <select name="payment_status">
                            <option value="">All states</option>
                            @foreach ($filterOptions['payment'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['payment_status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Calendar</span>
                        <select name="calendar_status">
                            <option value="">All states</option>
                            @foreach ($filterOptions['calendar'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['calendar_status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <button type="submit">Apply filters <span aria-hidden="true">→</span></button>
                    @if (collect($filters)->filter()->isNotEmpty())
                        <a href="{{ route('manager.bookings.index') }}">Clear</a>
                    @endif
                </form>

                <div class="bookings-workspace bookings-enter" style="--enter-delay: 230ms">
                    <section class="booking-queue" aria-labelledby="booking-queue-title">
                        <div class="queue-heading">
                            <div>
                                <span>Current view</span>
                                <h2 id="booking-queue-title">{{ $bookings->count() }} {{ Str::plural('booking', $bookings->count()) }}</h2>
                            </div>
                        </div>

                        <div class="queue-labels" aria-hidden="true">
                            <span>Customer</span><span>Requested appointment</span><span>Payment</span><span>Processing</span><span></span>
                        </div>

                        <div class="queue-list">
                            @forelse ($bookings as $booking)
                                <a class="booking-row {{ $selectedBooking['reference'] === $booking['reference'] ? 'is-selected' : '' }}" href="{{ route('manager.bookings.index', array_merge(request()->except('booking'), ['booking' => $booking['reference']])) }}" aria-label="View booking {{ $booking['reference'] }}">
                                    <span class="customer-avatar">{{ $booking['customer']['initials'] }}</span>
                                    <span class="queue-customer">
                                        <strong>{{ $booking['customer']['name'] }}</strong>
                                        <small>{{ $booking['reference'] }} · {{ $booking['service'] }}</small>
                                    </span>
                                    <time datetime="{{ $booking['appointment_at']->toIso8601String() }}">
                                        <strong>{{ $booking['appointment_at']->format('M d') }}</strong>
                                        <small>{{ $booking['appointment_at']->format('g:i A') }}</small>
                                    </time>
                                    <span class="state-chip queue-payment state-{{ $booking['payment_status']['key'] }}">{{ $booking['payment_status']['label'] }}</span>
                                    <span class="state-chip queue-booking process-{{ $booking['processing_method']['key'] }}">{{ $booking['processing_method']['label'] }}</span>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"></path></svg>
                                </a>
                            @empty
                                <div class="bookings-empty">
                                    <span aria-hidden="true">⌕</span>
                                    <strong>No matching bookings</strong>
                                    <p>Change or clear the filters to see other requests.</p>
                                    <a href="{{ route('manager.bookings.index') }}">Clear all filters</a>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    @if ($selectedBooking)
                        <aside class="booking-detail" aria-labelledby="booking-detail-title">
                            <div class="detail-accent" aria-hidden="true"></div>
                            <div class="detail-heading">
                                <div>
                                    <span>Selected request</span>
                                    <h2 id="booking-detail-title">{{ $selectedBooking['reference'] }}</h2>
                                </div>
                                <span class="state-chip state-{{ $selectedBooking['booking_status']['key'] }}">{{ $selectedBooking['booking_status']['label'] }}</span>
                            </div>

                            <div class="detail-customer">
                                <span>{{ $selectedBooking['customer']['initials'] }}</span>
                                <div>
                                    <strong>{{ $selectedBooking['customer']['name'] }}</strong>
                                    <a href="mailto:{{ $selectedBooking['customer']['email'] }}">{{ $selectedBooking['customer']['email'] }}</a>
                                    <a href="tel:{{ preg_replace('/\s+/', '', $selectedBooking['customer']['phone']) }}">{{ $selectedBooking['customer']['phone'] }}</a>
                                </div>
                            </div>

                            <dl class="detail-facts">
                                <div><dt>Repair service</dt><dd>{{ $selectedBooking['service'] }}</dd></div>
                                <div><dt>Requested date</dt><dd>{{ $selectedBooking['appointment_at']->format('F d, Y') }}</dd></div>
                                <div><dt>Requested time</dt><dd>{{ $selectedBooking['appointment_at']->format('g:i A') }}</dd></div>
                                <div><dt>Submitted</dt><dd>{{ $selectedBooking['submitted_at']->diffForHumans() }}</dd></div>
                            </dl>

                            <details class="customer-notes">
                                <summary>
                                    <span>Customer problem details</span>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"></path></svg>
                                </summary>
                                <div class="customer-notes-body">
                                    <p>{{ filled($selectedBooking['notes'] ?? null) ? $selectedBooking['notes'] : 'No additional details were provided for this booking.' }}</p>
                                </div>
                            </details>

                            <div class="status-matrix">
                                <div><span>Booking</span><strong class="status-text text-{{ $selectedBooking['booking_status']['key'] }}">{{ $selectedBooking['booking_status']['label'] }}</strong></div>
                                <div><span>Processing</span><strong class="status-text process-text-{{ $selectedBooking['processing_method']['key'] }}">{{ $selectedBooking['processing_method']['label'] }}</strong></div>
                                <div><span>Payment</span><strong class="status-text text-{{ $selectedBooking['payment_status']['key'] }}">{{ $selectedBooking['payment_status']['label'] }}</strong></div>
                                <div><span>Calendar sync</span><strong class="status-text text-{{ $selectedBooking['calendar_status']['key'] }}">{{ $selectedBooking['calendar_status']['label'] }}</strong></div>
                                <div><span>Service window</span><strong>{{ $selectedBooking['schedule_assessment']['duration_minutes'] }} minutes</strong></div>
                                <div><span>Repair</span><strong class="status-text {{ $selectedBooking['repair_status'] ? 'text-'.$selectedBooking['repair_status']['key'] : '' }}">{{ $selectedBooking['repair_status']['label'] ?? 'Not yet recorded' }}</strong></div>
                            </div>

                            @if ($selectedBooking['booking_status']['key'] === 'review_required')
                                <section class="capacity-review" aria-labelledby="capacity-review-title">
                                    <header>
                                        <div>
                                            <span>Schedule review needed</span>
                                            <h3 id="capacity-review-title">Requested time window</h3>
                                        </div>
                                        <time datetime="{{ $selectedBooking['appointment_at']->toIso8601String() }}">{{ $selectedBooking['appointment_at']->format('g:i A') }}–{{ $selectedBooking['schedule_assessment']['ends_at']->format('g:i A') }}</time>
                                    </header>

                                    <div class="overlap-list">
                                        <span>Bookings within this timeframe</span>
                                        @foreach ($selectedBooking['schedule_assessment']['conflicts'] as $conflict)
                                            <a href="{{ route('manager.bookings.index', array_merge(request()->except('booking'), ['booking' => $conflict['reference']])) }}">
                                                <div><strong>{{ $conflict['reference'] }} · {{ $conflict['customer'] }}</strong><small>{{ $conflict['service'] }}</small></div>
                                                <time>{{ $conflict['appointment_at']->format('g:i A') }}–{{ $conflict['ends_at']->format('g:i A') }}</time>
                                            </a>
                                        @endforeach
                                    </div>
                                </section>
                            @elseif ($selectedBooking['processing_method']['key'] === 'automatic')
                                <div class="processing-note processing-automatic">
                                    <span aria-hidden="true">✓</span>
                                    <strong>Confirmed automatically</strong>
                                </div>
                            @elseif ($selectedBooking['processing_method']['key'] === 'manual')
                                <div class="processing-note processing-manual">
                                    <span aria-hidden="true">↗</span>
                                    <strong>Reviewed by a manager</strong>
                                </div>
                            @endif

                            @if ($selectedBooking['calendar_status']['key'] === 'failed')
                                <div class="integration-note integration-failed">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4 3 20h18L12 4Zm0 5v5m0 3v.1"></path></svg>
                                    <p><strong>Calendar follow-up required</strong><span>The booking remains confirmed. Synchronization can be retried safely by the backend without duplicating the event.</span></p>
                                </div>
                            @elseif ($selectedBooking['calendar_status']['key'] === 'pending')
                                <div class="integration-note">
                                    <span class="sync-loader" aria-hidden="true"></span>
                                    <p><strong>Calendar synchronization pending</strong><span>Confirmation is saved separately from the calendar result.</span></p>
                                </div>
                            @endif

                            @if ($selectedBooking['booking_status']['key'] === 'review_required')
                                <div class="decision-panel">
                                    <span>Schedule decision</span>
                                    <p>Review the overlapping bookings, then confirm this request if the workshop can accommodate it.</p>
                                    <div>
                                        <button class="decision-confirm" type="button" data-decision-open="confirmed">Confirm booking</button>
                                        <button class="decision-reject" type="button" data-decision-open="rejected">Reject request</button>
                                    </div>
                                </div>
                            @endif
                        </aside>
                    @endif
                </div>
            </main>
        </div>

        @if ($selectedBooking && $selectedBooking['booking_status']['key'] === 'review_required')
            <dialog class="decision-dialog" data-decision-dialog>
                <form method="POST" action="{{ route('manager.bookings.decision', $selectedBooking['reference']) }}">
                    @csrf
                    <input type="hidden" name="decision" value="" data-decision-value>
                    <div class="dialog-icon" data-dialog-icon aria-hidden="true"></div>
                    <span data-dialog-eyebrow>Manager decision</span>
                    <h2 data-dialog-title>Confirm this booking?</h2>
                    <p data-dialog-copy></p>
                    <div class="dialog-reference">
                        <span>{{ $selectedBooking['reference'] }}</span>
                        <strong>{{ $selectedBooking['customer']['name'] }}</strong>
                        <small>{{ $selectedBooking['service'] }} · {{ $selectedBooking['appointment_at']->format('M d, g:i A') }}</small>
                    </div>
                    <div class="dialog-actions">
                        <button type="button" data-decision-close>Go back</button>
                        <button type="submit" data-decision-submit>Confirm booking</button>
                    </div>
                </form>
            </dialog>
        @endif
    </body>
</html>
