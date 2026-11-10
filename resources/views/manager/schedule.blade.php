<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Manage confirmed Pedalya repair appointments and calendar synchronization.">

        <title>Schedule | Pedalya Manager</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/pages/manager-schedule.css', 'resources/js/app.js', 'resources/js/pages/manager-workspace.js', 'resources/js/pages/manager-schedule.js'])
    </head>
    <body class="manager-dashboard-page manager-schedule-page">
        <a class="manager-skip-link" href="#schedule-content">Skip to schedule</a>

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
                    <a href="{{ route('manager.bookings.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"></path></svg>
                        <span>Bookings</span>
                        @if ($navigation['pending_bookings'] > 0)
                            <i>{{ $navigation['pending_bookings'] }}</i>
                        @endif
                    </a>
                    <a class="is-active" href="{{ route('manager.schedule.index') }}" aria-current="page">
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

            <main class="manager-main" id="schedule-content">
                <header class="manager-topbar schedule-enter" style="--enter-delay: 30ms">
                    <button class="sidebar-open" type="button" aria-expanded="false" aria-controls="manager-navigation" data-sidebar-open>
                        <span class="sr-only">Open manager navigation</span>
                        <span></span><span></span>
                    </button>

                    <div class="topbar-title">
                        <span>Manager workspace</span>
                        <strong>Schedule</strong>
                    </div>

                    <div class="topbar-actions">
                        <a class="topbar-primary" href="{{ route('manager.bookings.index') }}">Review bookings <span aria-hidden="true">↗</span></a>
                    </div>
                </header>

                @if (session('schedule_notice'))
                    <div class="schedule-notice" role="status" data-schedule-notice>
                        <span aria-hidden="true">↻</span>
                        <p>{{ session('schedule_notice') }}</p>
                        <button type="button" aria-label="Dismiss notification" data-notice-dismiss>×</button>
                    </div>
                @endif

                <section class="schedule-intro schedule-enter" style="--enter-delay: 80ms" aria-labelledby="schedule-title">
                    <div>
                        <span>Appointment control</span>
                        <h1 id="schedule-title">Workshop<br><em>week.</em></h1>
                    </div>

                    <div class="week-control" aria-label="Week navigation">
                        <div>
                            <span>{{ $weekStart->format('Y') }}</span>
                            <strong>{{ $weekStart->format('M d') }} — {{ $weekEnd->format('M d') }}</strong>
                        </div>
                        <nav>
                            <a href="{{ route('manager.schedule.index', ['date' => $previousWeek->toDateString()]) }}" aria-label="Previous week">←</a>
                            <a class="today-link" href="{{ route('manager.schedule.index', ['date' => now()->toDateString()]) }}">Today</a>
                            <a href="{{ route('manager.schedule.index', ['date' => $nextWeek->toDateString()]) }}" aria-label="Next week">→</a>
                        </nav>
                    </div>
                </section>

                <section class="schedule-summary schedule-enter" style="--enter-delay: 130ms" aria-label="Schedule overview">
                    <article><span>Confirmed appointments</span><strong>{{ str_pad($summary['confirmed'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                    <article><span>Today</span><strong>{{ str_pad($summary['today'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                    <article><span>Calendar synchronized</span><strong>{{ str_pad($summary['synchronized'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                    <article class="summary-follow-up"><span>Sync follow-up</span><strong>{{ str_pad($summary['follow_up'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                </section>

                <div class="schedule-workspace schedule-enter" style="--enter-delay: 190ms">
                    <section class="week-board" aria-labelledby="week-board-title">
                        <div class="board-heading">
                            <div><span>Confirmed schedule</span><h2 id="week-board-title">Seven-day view</h2></div>
                            <div class="sync-legend" aria-label="Calendar synchronization legend">
                                <span><i class="sync-synchronized"></i> Synced</span>
                                <span><i class="sync-pending"></i> Pending</span>
                                <span><i class="sync-failed"></i> Failed</span>
                            </div>
                        </div>

                        <div class="week-grid">
                            @foreach ($days as $day)
                                <section class="day-column {{ $day['is_today'] ? 'is-today' : '' }}" aria-labelledby="day-{{ $day['date']->format('Ymd') }}">
                                    <header>
                                        <span>{{ $day['date']->format('D') }}</span>
                                        <strong id="day-{{ $day['date']->format('Ymd') }}">{{ $day['date']->format('d') }}</strong>
                                        @if ($day['is_today'])<small>Today</small>@endif
                                    </header>

                                    <div class="day-appointments">
                                        @forelse ($day['appointments'] as $appointment)
                                            <a class="appointment-card sync-card-{{ $appointment['calendar_status']['key'] }} {{ $selectedAppointment['reference'] === $appointment['reference'] ? 'is-selected' : '' }}" href="{{ route('manager.schedule.index', ['date' => $selectedDate->toDateString(), 'booking' => $appointment['reference']]) }}" aria-label="View appointment {{ $appointment['reference'] }}">
                                                <span class="appointment-time">{{ $appointment['appointment_at']->format('g:i A') }}</span>
                                                <strong>{{ $appointment['customer']['name'] }}</strong>
                                                <small>{{ $appointment['service'] }}</small>
                                                <span class="appointment-reference">{{ $appointment['reference'] }}</span>
                                                <i aria-label="Calendar {{ $appointment['calendar_status']['label'] }}"></i>
                                            </a>
                                        @empty
                                            <div class="day-empty"><span>Open day</span></div>
                                        @endforelse
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </section>

                    @if ($selectedAppointment)
                        <aside class="schedule-detail" aria-labelledby="appointment-detail-title">
                            <div class="detail-topline">
                                <span>Appointment detail</span>
                                <i class="detail-sync detail-sync-{{ $selectedAppointment['calendar_status']['key'] }}" aria-hidden="true"></i>
                            </div>

                            <div class="detail-title">
                                <span>{{ $selectedAppointment['reference'] }}</span>
                                <h2 id="appointment-detail-title">{{ $selectedAppointment['customer']['name'] }}</h2>
                                <p>{{ $selectedAppointment['service'] }}</p>
                            </div>

                            <div class="appointment-date">
                                <time datetime="{{ $selectedAppointment['appointment_at']->toIso8601String() }}">
                                    <span>{{ $selectedAppointment['appointment_at']->format('l') }}</span>
                                    <strong>{{ $selectedAppointment['appointment_at']->format('d') }}</strong>
                                    <small>{{ strtoupper($selectedAppointment['appointment_at']->format('M Y')) }}</small>
                                </time>
                                <div>
                                    <span>Start time</span>
                                    <strong>{{ $selectedAppointment['appointment_at']->format('g:i A') }}</strong>
                                    <small>End time not configured</small>
                                </div>
                            </div>

                            <dl class="schedule-facts">
                                <div><dt>Booking</dt><dd class="text-confirmed">{{ $selectedAppointment['booking_status']['label'] }}</dd></div>
                                <div><dt>Payment</dt><dd class="text-{{ $selectedAppointment['payment_status']['key'] }}">{{ $selectedAppointment['payment_status']['label'] }}</dd></div>
                                <div><dt>Calendar</dt><dd class="text-{{ $selectedAppointment['calendar_status']['key'] }}">{{ $selectedAppointment['calendar_status']['label'] }}</dd></div>
                                <div><dt>Repair</dt><dd>{{ $selectedAppointment['repair_status']['label'] ?? 'Not yet recorded' }}</dd></div>
                            </dl>

                            <div class="customer-contact">
                                <span>{{ $selectedAppointment['customer']['initials'] }}</span>
                                <div>
                                    <strong>Customer contact</strong>
                                    <a href="mailto:{{ $selectedAppointment['customer']['email'] }}">{{ $selectedAppointment['customer']['email'] }}</a>
                                    <a href="tel:{{ preg_replace('/\s+/', '', $selectedAppointment['customer']['phone']) }}">{{ $selectedAppointment['customer']['phone'] }}</a>
                                </div>
                            </div>

                            <div class="calendar-contract">
                                <span>Google Calendar</span>
                                @if ($selectedAppointment['calendar_event_id'])
                                    <strong>Event linked</strong>
                                    <small>{{ $selectedAppointment['calendar_event_id'] }}</small>
                                @elseif ($selectedAppointment['calendar_status']['key'] === 'failed')
                                    <strong>Synchronization failed</strong>
                                    <small>Attempt {{ $selectedAppointment['sync_attempts'] }} of 3. The booking remains confirmed.</small>
                                @else
                                    <strong>Synchronization pending</strong>
                                    <small>The booking remains confirmed while the backend completes synchronization.</small>
                                @endif
                            </div>

                            @if ($selectedAppointment['calendar_status']['key'] === 'failed' && $selectedAppointment['sync_attempts'] < 3)
                                <form class="sync-action" method="POST" action="{{ route('manager.schedule.calendar-sync.retry', $selectedAppointment['reference']) }}" data-sync-form>
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">
                                    <button type="submit">Queue safe retry <span aria-hidden="true">↻</span></button>
                                    <p>The backend must check for an existing event before retrying.</p>
                                </form>
                            @elseif ($selectedAppointment['calendar_status']['key'] === 'failed')
                                <div class="sync-exhausted"><strong>Staff follow-up required</strong><span>The configured retry limit has been reached.</span></div>
                            @endif
                        </aside>
                    @endif
                </div>
            </main>
        </div>
    </body>
</html>
