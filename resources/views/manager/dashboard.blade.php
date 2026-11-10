<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Pedalya manager dashboard for booking, schedule, repair, and integration monitoring.">

        <title>Manager Dashboard | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/pages/manager-dashboard.css', 'resources/js/app.js', 'resources/js/pages/manager-dashboard.js'])
    </head>
    <body class="manager-dashboard-page">
        <a class="manager-skip-link" href="#dashboard-content">Skip to dashboard</a>

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
                    <a class="is-active" href="{{ route('manager.dashboard') }}" aria-current="page">
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
                    <a href="{{ route('manager.schedule.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7v5l3 2"></path></svg>
                        <span>Schedule</span>
                    </a>
                    <a href="#repair-progress">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 6.5 3-3 3 3-3 3M4 20l7.5-7.5M6.5 4.5l13 13M4 7l3-3"></path></svg>
                        <span>Repairs</span>
                    </a>
                    <a href="#">
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

            <main class="manager-main" id="dashboard-content">
                <header class="manager-topbar dashboard-enter" style="--enter-delay: 40ms">
                    <button class="sidebar-open" type="button" aria-expanded="false" aria-controls="manager-navigation" data-sidebar-open>
                        <span class="sr-only">Open manager navigation</span>
                        <span></span><span></span>
                    </button>

                    <div class="topbar-title">
                        <span>Manager workspace</span>
                        <strong>Dashboard</strong>
                    </div>

                    <div class="topbar-actions">
                        <button class="icon-button" type="button" aria-label="Search dashboard" data-dashboard-search-open>
                            <svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
                        </button>
                        <button class="icon-button activity-button" type="button" aria-label="View activity">
                            <svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 8h18c0-1-3-1-3-8ZM10 21h4"></path></svg>
                        </button>
                        <a class="topbar-primary" href="{{ route('manager.bookings.index') }}">Review requests <span aria-hidden="true">↗</span></a>
                    </div>
                </header>

                <div class="dashboard-search" data-dashboard-search hidden>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
                    <input type="search" placeholder="Search a booking reference or customer" aria-label="Search dashboard">
                    <kbd>ESC</kbd>
                </div>

                <section class="dashboard-heading dashboard-enter" style="--enter-delay: 90ms">
                    <div>
                        <p><span data-day-period>Good day</span>, {{ $manager['first_name'] }}</p>
                        <h1>Keep today<br>moving.</h1>
                    </div>
                    <time datetime="{{ $dashboardDate->toDateString() }}">
                        <span>{{ $dashboardDate->format('l') }}</span>
                        <strong>{{ $dashboardDate->format('d') }}</strong>
                        <small>{{ strtoupper($dashboardDate->format('M Y')) }}</small>
                    </time>
                </section>

                <section class="control-panel dashboard-enter" style="--enter-delay: 150ms" aria-labelledby="control-title">
                    <div class="control-grid" aria-hidden="true"></div>
                    <div class="control-orbit control-orbit-one" aria-hidden="true"></div>
                    <div class="control-orbit control-orbit-two" aria-hidden="true"></div>

                    <div class="control-copy">
                        <span class="control-live"><i></i> Live operations</span>
                        <h2 id="control-title">The workshop is<br><em>on track.</em></h2>
                        <p>Bookings, appointments, and workshop activity are organized in one operational view.</p>
                        <a href="{{ route('manager.schedule.index') }}">View today’s schedule <span aria-hidden="true">→</span></a>
                    </div>

                    <div class="control-dial" aria-label="{{ $dailySummary['confirmed'] }} of {{ $dailySummary['capacity'] }} daily appointments confirmed">
                        <svg viewBox="0 0 160 160" aria-hidden="true">
                            <circle class="dial-base" cx="80" cy="80" r="63"></circle>
                            <circle class="dial-progress" pathLength="100" style="--schedule-progress: {{ $dailySummary['percentage'] }}" cx="80" cy="80" r="63"></circle>
                        </svg>
                        <div>
                            <strong>{{ $dailySummary['confirmed'] }}<span>/{{ $dailySummary['capacity'] }}</span></strong>
                            <small>confirmed today</small>
                        </div>
                    </div>

                    <div class="control-statuses">
                        <div><span>{{ str_pad($dailySummary['upcoming'], 2, '0', STR_PAD_LEFT) }}</span><p>Upcoming<small>appointments</small></p></div>
                        <div><span>{{ str_pad($dailySummary['active_repairs'], 2, '0', STR_PAD_LEFT) }}</span><p>Active<small>repairs</small></p></div>
                    </div>
                </section>

                <section class="metrics-rail dashboard-enter" style="--enter-delay: 220ms" aria-label="Booking metrics">
                    @foreach ($stats as $stat)
                        <article class="metric-item metric-{{ $stat['tone'] }}">
                            <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <strong>{{ $stat['value'] }}</strong>
                                <p>{{ $stat['label'] }}</p>
                            </div>
                            <small>{{ $stat['trend'] }}</small>
                        </article>
                    @endforeach
                </section>

                <div class="dashboard-columns">
                    <section class="dashboard-section requests-section dashboard-enter" id="requests" style="--enter-delay: 280ms" aria-labelledby="requests-title">
                        <div class="section-bar">
                            <div>
                                <span>Incoming work</span>
                                <h2 id="requests-title">Booking requests</h2>
                            </div>
                            <a href="{{ route('manager.bookings.index') }}">View all <span aria-hidden="true">↗</span></a>
                        </div>

                        <div class="request-filters" role="group" aria-label="Filter booking requests">
                            <button class="is-active" type="button" data-request-filter="all">All <span>{{ count($requests) }}</span></button>
                            <button type="button" data-request-filter="pending">Pending</button>
                            <button type="button" data-request-filter="confirmed">Confirmed</button>
                        </div>

                        <div class="request-list" data-request-list>
                            @forelse ($requests as $request)
                                <article class="request-row" data-request-status="{{ $request['status'] }}">
                                    <div class="request-person">
                                        <span>{{ $request['initials'] }}</span>
                                        <div>
                                            <strong>{{ $request['customer'] }}</strong>
                                            <small>{{ $request['reference'] }} · {{ $request['time'] }}</small>
                                        </div>
                                    </div>
                                    <div class="request-service">
                                        <span>Service</span>
                                        <strong>{{ $request['service'] }}</strong>
                                    </div>
                                    <div class="request-schedule">
                                        <span>Requested schedule</span>
                                        <strong>{{ $request['schedule'] }}</strong>
                                    </div>
                                    <span class="status-pill status-{{ $request['status'] }}">{{ ucfirst($request['status']) }}</span>
                                    <a href="#" aria-label="Open booking {{ $request['reference'] }}">
                                        <svg viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"></path></svg>
                                    </a>
                                </article>
                            @empty
                                <div class="dashboard-empty">
                                    <strong>No booking requests</strong>
                                    <span>New requests will appear here.</span>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="dashboard-section schedule-section dashboard-enter" id="today-schedule" style="--enter-delay: 340ms" aria-labelledby="schedule-title">
                        <div class="section-bar">
                            <div>
                                <span>{{ $dashboardDate->format('F d') }}</span>
                                <h2 id="schedule-title">Today’s schedule</h2>
                            </div>
                            <button type="button" aria-label="Open schedule options">
                                <svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="1"></circle><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle></svg>
                            </button>
                        </div>

                        <div class="schedule-timeline">
                            @forelse ($appointments as $appointment)
                                <article class="timeline-item timeline-{{ $appointment['state'] }}">
                                    <time><strong>{{ $appointment['time'] }}</strong><span>{{ $appointment['period'] }}</span></time>
                                    <i aria-hidden="true"></i>
                                    <div>
                                        <span>{{ $appointment['reference'] }}</span>
                                        <strong>{{ $appointment['customer'] }}</strong>
                                        <small>{{ $appointment['service'] }}</small>
                                    </div>
                                    @if ($appointment['state'] === 'current')
                                        <b>NOW</b>
                                    @endif
                                </article>
                            @empty
                                <div class="dashboard-empty schedule-empty">
                                    <strong>No appointments today</strong>
                                    <span>Confirmed appointments will appear here.</span>
                                </div>
                            @endforelse
                        </div>

                        <a class="schedule-link" href="{{ route('manager.schedule.index') }}">Open full schedule <span aria-hidden="true">→</span></a>
                    </section>
                </div>

                <div class="dashboard-lower-grid">
                    <section class="repair-panel dashboard-enter" id="repair-progress" style="--enter-delay: 390ms" aria-labelledby="repair-title">
                        <div class="section-bar">
                            <div><span>Workshop</span><h2 id="repair-title">Repair status</h2></div>
                            <a href="#repair-progress">Open queue <span aria-hidden="true">↗</span></a>
                        </div>
                        <div class="repair-list">
                            @forelse ($repairs as $repair)
                                <article>
                                    <div><strong>{{ $repair['reference'] }}</strong><span>Workshop job</span></div>
                                    <p>{{ $repair['service'] }}</p>
                                    <span class="repair-state repair-state-unrecorded">{{ $repair['status'] ?? 'Status not recorded' }}</span>
                                </article>
                            @empty
                                <div class="dashboard-empty">
                                    <strong>No active repairs</strong>
                                    <span>Active workshop jobs will appear here.</span>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </body>
</html>
