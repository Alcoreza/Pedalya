<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Review Pedalya operational and integration activity records.">

        <title>Activity | Pedalya Manager</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/pages/manager-activity.css', 'resources/js/app.js', 'resources/js/pages/manager-workspace.js', 'resources/js/pages/manager-activity.js'])
    </head>
    <body class="manager-dashboard-page manager-activity-page">
        <a class="manager-skip-link" href="#activity-content">Skip to activity</a>

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
                    <a href="{{ route('manager.schedule.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7v5l3 2"></path></svg>
                        <span>Schedule</span>
                    </a>
                    <a href="{{ route('manager.repairs.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 6.5 3-3 3 3-3 3M4 20l7.5-7.5M6.5 4.5l13 13M4 7l3-3"></path></svg>
                        <span>Repairs</span>
                    </a>
                    <a class="is-active" href="{{ route('manager.activity.index') }}" aria-current="page">
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

            <main class="manager-main" id="activity-content">
                <header class="manager-topbar activity-enter" style="--enter-delay: 30ms">
                    <button class="sidebar-open" type="button" aria-expanded="false" aria-controls="manager-navigation" data-sidebar-open>
                        <span class="sr-only">Open manager navigation</span>
                        <span></span><span></span>
                    </button>

                    <div class="topbar-title">
                        <span>Manager workspace</span>
                        <strong>Activity</strong>
                    </div>

                    <div class="topbar-actions">
                        <a class="topbar-primary" href="{{ route('manager.dashboard') }}">Back to dashboard <span aria-hidden="true">↗</span></a>
                    </div>
                </header>

                <section class="activity-intro activity-enter" style="--enter-delay: 80ms" aria-labelledby="activity-title">
                    <div>
                        <span>Operational record</span>
                        <h1 id="activity-title">System<br><em>activity.</em></h1>
                    </div>
                    <div class="record-principle">
                        <span aria-hidden="true">LOG</span>
                        <p>Review recorded operations across bookings and integrations. This is an audit view, not a real-time monitoring claim.</p>
                    </div>
                </section>

                <section class="activity-summary activity-enter" style="--enter-delay: 130ms" aria-label="Activity overview">
                    <article><span>Events today</span><strong>{{ str_pad($summary['today'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                    <article><span>Successful</span><strong>{{ str_pad($summary['successful'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                    <article class="summary-follow-up"><span>Needs follow-up</span><strong>{{ str_pad($summary['follow_up'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                    <article class="summary-manager"><span>Manager changes</span><strong>{{ str_pad($summary['manager_changes'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                </section>

                <form class="activity-filters activity-enter" style="--enter-delay: 180ms" method="GET" action="{{ route('manager.activity.index') }}">
                    <label class="activity-search">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
                        <span class="sr-only">Search activity</span>
                        <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Event, booking reference, actor, or provider">
                    </label>
                    <label><span>Category</span><select name="category"><option value="">All categories</option>@foreach ($filterOptions['categories'] as $value => $label)<option value="{{ $value }}" @selected($filters['category'] === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label><span>Result</span><select name="result"><option value="">All results</option>@foreach ($filterOptions['results'] as $value => $label)<option value="{{ $value }}" @selected($filters['result'] === $value)>{{ $label }}</option>@endforeach</select></label>
                    <button type="submit">Apply <span aria-hidden="true">→</span></button>
                    @if (collect($filters)->filter()->isNotEmpty())<a href="{{ route('manager.activity.index') }}">Clear</a>@endif
                </form>

                <div class="activity-workspace activity-enter" style="--enter-delay: 230ms">
                    <section class="event-console" aria-labelledby="event-console-title">
                        <div class="console-scan" aria-hidden="true"></div>
                        <div class="console-heading">
                            <div><span>Recorded events</span><h2 id="event-console-title">Activity stream</h2></div>
                            <small>{{ $activities->count() }} {{ Str::plural('entry', $activities->count()) }}</small>
                        </div>

                        <div class="event-list">
                            @forelse ($activities as $activity)
                                <a class="event-row {{ $selectedActivity['id'] === $activity['id'] ? 'is-selected' : '' }} event-{{ $activity['result']['key'] }}" style="--row-delay: {{ $loop->index * 45 }}ms" href="{{ route('manager.activity.index', array_merge(request()->except('activity'), ['activity' => $activity['id']])) }}" aria-label="View activity {{ $activity['id'] }}">
                                    <time datetime="{{ $activity['occurred_at']->toIso8601String() }}"><strong>{{ $activity['occurred_at']->format('H:i') }}</strong><span>{{ $activity['occurred_at']->isToday() ? 'Today' : $activity['occurred_at']->format('M d') }}</span></time>
                                    <i aria-hidden="true"></i>
                                    <div class="event-copy"><span>{{ $activity['category']['label'] }} · {{ $activity['event_key'] }}</span><strong>{{ $activity['title'] }}</strong><small>{{ $activity['booking_reference'] }} · {{ $activity['actor'] }}</small></div>
                                    <span class="event-result result-{{ $activity['result']['key'] }}">{{ $activity['result']['label'] }}</span>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"></path></svg>
                                </a>
                            @empty
                                <div class="activity-empty"><span aria-hidden="true">_</span><strong>No matching activity</strong><p>Change or clear the filters to see other recorded events.</p><a href="{{ route('manager.activity.index') }}">Clear filters</a></div>
                            @endforelse
                        </div>
                    </section>

                    @if ($selectedActivity)
                        <aside class="activity-inspector" aria-labelledby="activity-detail-title">
                            <div class="inspector-header">
                                <div><span>Event detail</span><strong>{{ $selectedActivity['id'] }}</strong></div>
                                <span class="detail-result result-{{ $selectedActivity['result']['key'] }}"><i></i>{{ $selectedActivity['result']['label'] }}</span>
                            </div>

                            <div class="event-identity">
                                <span>{{ $selectedActivity['event_key'] }}</span>
                                <h2 id="activity-detail-title">{{ $selectedActivity['title'] }}</h2>
                                <p>{{ $selectedActivity['summary'] }}</p>
                            </div>

                            <dl class="event-facts">
                                <div><dt>Booking reference</dt><dd>{{ $selectedActivity['booking_reference'] }}</dd></div>
                                <div><dt>Category</dt><dd>{{ $selectedActivity['category']['label'] }}</dd></div>
                                <div><dt>Provider</dt><dd>{{ $selectedActivity['provider'] }}</dd></div>
                                <div><dt>Provider reference</dt><dd>{{ $selectedActivity['provider_reference'] ?? 'Not recorded' }}</dd></div>
                                <div><dt>Responsible actor</dt><dd>{{ $selectedActivity['actor'] }}</dd></div>
                                <div><dt>Recorded at</dt><dd><time datetime="{{ $selectedActivity['occurred_at']->toIso8601String() }}">{{ $selectedActivity['occurred_at']->format('F d, Y · g:i A') }}</time></dd></div>
                            </dl>

                            <section class="event-description" aria-labelledby="description-title">
                                <span>Safe event message</span>
                                <h3 id="description-title">What happened</h3>
                                <p>{{ $selectedActivity['detail'] }}</p>
                            </section>

                            <section class="event-metadata" aria-labelledby="metadata-title">
                                <div><span>Structured context</span><h3 id="metadata-title">Event fields</h3></div>
                                <dl>@foreach ($selectedActivity['metadata'] as $field)<div><dt>{{ $field['label'] }}</dt><dd>{{ $field['value'] }}</dd></div>@endforeach</dl>
                            </section>

                            <div class="security-note">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.6 2.7 8.1 7 10 4.3-1.9 7-5.4 7-10V6l-7-3Z"></path><path d="m9 12 2 2 4-4"></path></svg>
                                <p><strong>Sensitive values excluded</strong><span>Passwords, API keys, access tokens, and secret credentials must never appear in activity records.</span></p>
                            </div>
                        </aside>
                    @endif
                </div>
            </main>
        </div>
    </body>
</html>
