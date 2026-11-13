<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Manage Pedalya repair status records and completion updates.">

        <title>Repairs | Pedalya Manager</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/pages/manager-repairs.css', 'resources/js/app.js', 'resources/js/pages/manager-workspace.js', 'resources/js/pages/manager-repairs.js'])
    </head>
    <body class="manager-dashboard-page manager-repairs-page">
        <a class="manager-skip-link" href="#repairs-content">Skip to repairs</a>

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
                    <a class="is-active" href="{{ route('manager.repairs.index') }}" aria-current="page">
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

            <main class="manager-main" id="repairs-content">
                <header class="manager-topbar repairs-enter" style="--enter-delay: 30ms">
                    <button class="sidebar-open" type="button" aria-expanded="false" aria-controls="manager-navigation" data-sidebar-open>
                        <span class="sr-only">Open manager navigation</span>
                        <span></span><span></span>
                    </button>

                    <div class="topbar-title">
                        <span>Manager workspace</span>
                        <strong>Repairs</strong>
                    </div>

                    <div class="topbar-actions">
                        <a class="topbar-primary" href="{{ route('manager.schedule.index') }}">Open schedule <span aria-hidden="true">↗</span></a>
                    </div>
                </header>

                @if (session('repair_notice'))
                    <div class="repair-notice" role="status" data-repair-notice>
                        <span aria-hidden="true">✓</span>
                        <p>{{ session('repair_notice') }}</p>
                        <button type="button" aria-label="Dismiss notification" data-notice-dismiss>×</button>
                    </div>
                @endif

                <section class="repairs-intro repairs-enter" style="--enter-delay: 80ms" aria-labelledby="repairs-title">
                    <div>
                        <span>Workshop records</span>
                        <h1 id="repairs-title">Repair<br><em>ledger.</em></h1>
                    </div>
                    <div class="intro-rule">
                        <i aria-hidden="true"></i>
                        <p>Track every workshop handoff from bike arrival to customer claim, with payment verified before repair work begins.</p>
                    </div>
                </section>

                <section class="repair-summary repairs-enter" style="--enter-delay: 130ms" aria-label="Repair overview">
                    <article><span>Awaiting arrival today</span><strong>{{ str_pad($summary['awaiting_today'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                    <article class="summary-at-shop"><span>Bike at shop</span><strong>{{ str_pad($summary['at_shop'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                    <article class="summary-progress"><span>Repair in progress</span><strong>{{ str_pad($summary['in_progress'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                    <article class="summary-completed"><span>Completed today</span><strong>{{ str_pad($summary['completed_today'], 2, '0', STR_PAD_LEFT) }}</strong></article>
                </section>

                <nav class="repair-view-tabs repairs-enter" style="--enter-delay: 160ms" aria-label="Repair record views">
                    <a class="{{ $filters['tab'] === 'active' ? 'is-active' : '' }}" href="{{ route('manager.repairs.index', array_filter(['search' => $filters['search'], 'tab' => 'active'])) }}">
                        <span>Active repairs</span><strong>{{ $tabCounts['active'] }}</strong>
                    </a>
                    <a class="{{ $filters['tab'] === 'records' ? 'is-active' : '' }}" href="{{ route('manager.repairs.index', array_filter(['search' => $filters['search'], 'tab' => 'records'])) }}">
                        <span>Completed records</span><strong>{{ $tabCounts['records'] }}</strong>
                    </a>
                </nav>

                <form class="repair-filters repairs-enter" style="--enter-delay: 180ms" method="GET" action="{{ route('manager.repairs.index') }}">
                    <input type="hidden" name="tab" value="{{ $filters['tab'] }}">
                    <label>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
                        <span class="sr-only">Search repairs</span>
                        <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Reference, customer, or service">
                    </label>
                    <div role="group" aria-label="Filter repair records">
                        <a class="{{ $filters['status'] === '' ? 'is-active' : '' }}" href="{{ route('manager.repairs.index', array_filter(['search' => $filters['search'], 'tab' => $filters['tab']])) }}">All</a>
                        @foreach ($statusOptions as $value => $label)
                            <a class="{{ $filters['status'] === $value ? 'is-active' : '' }}" href="{{ route('manager.repairs.index', array_filter(['search' => $filters['search'], 'tab' => $filters['tab'], 'status' => $value])) }}">{{ $label }}</a>
                        @endforeach
                    </div>
                    <button type="submit">Search <span aria-hidden="true">→</span></button>
                </form>

                <div class="repairs-workspace repairs-enter" style="--enter-delay: 230ms">
                    <section class="repair-ledger" aria-labelledby="repair-ledger-title">
                        <div class="ledger-heading">
                            <div><span>{{ $filters['tab'] === 'records' ? 'Workshop history' : 'Current workshop' }}</span><h2 id="repair-ledger-title">{{ $repairs->count() }} {{ Str::plural('record', $repairs->count()) }}</h2></div>
                        </div>

                        <div class="repair-tickets">
                            @forelse ($repairs as $repair)
                                <a class="repair-ticket repair-{{ $repair['repair_status']['key'] }} {{ $selectedRepair['reference'] === $repair['reference'] ? 'is-selected' : '' }}" href="{{ route('manager.repairs.index', array_merge(request()->except('repair'), ['repair' => $repair['reference']])) }}" aria-label="View repair {{ $repair['reference'] }}">
                                    <span class="ticket-index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div class="ticket-copy">
                                        <span>{{ $repair['reference'] }}</span>
                                        <h3>{{ $repair['service'] }}</h3>
                                        <p>{{ $repair['customer']['name'] }}</p>
                                    </div>
                                    <div class="ticket-date">
                                        <span>Appointment</span>
                                        <time datetime="{{ $repair['appointment_at']->toIso8601String() }}">{{ $repair['appointment_at']->format('M d · g:i A') }}</time>
                                    </div>
                                    <span class="ticket-status status-{{ $repair['repair_status']['key'] }}">{{ $repair['repair_status']['label'] }}</span>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"></path></svg>
                                </a>
                            @empty
                                <div class="repairs-empty">
                                    <span aria-hidden="true">◇</span>
                                    <strong>No matching repair records</strong>
                                    <p>Change or clear the filters to view other records.</p>
                                    <a href="{{ route('manager.repairs.index', ['tab' => $filters['tab']]) }}">Clear filters</a>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    @if ($selectedRepair)
                        @php
                            $repairKey = $selectedRepair['repair_status']['key'];
                            $transition = match ($repairKey) {
                                'awaiting_arrival' => ['route' => route('manager.repairs.arrive', $selectedRepair['reference']), 'eyebrow' => 'Workshop intake', 'title' => 'Has the bike arrived?', 'copy' => 'Record the physical handoff before any workshop work begins.', 'button' => 'Mark bike as arrived', 'dialogTitle' => 'Confirm bike arrival?', 'dialogCopy' => 'This records the bike at the shop and adds the manager and arrival time to the audit trail.', 'submit' => 'Confirm arrival', 'icon' => '↓'],
                                'at_shop' => $selectedRepair['payment_status']['key'] === 'successful' ? ['route' => route('manager.repairs.start', $selectedRepair['reference']), 'eyebrow' => 'Workshop handoff', 'title' => 'Ready to begin the repair?', 'copy' => 'Payment is verified. Starting records the manager and workshop start time.', 'button' => 'Start repair', 'dialogTitle' => 'Start this repair?', 'dialogCopy' => 'The customer-visible repair status will change to Repair in progress.', 'submit' => 'Start repair', 'icon' => '→'] : null,
                                'in_progress' => ['route' => route('manager.repairs.complete', $selectedRepair['reference']), 'eyebrow' => 'Final repair status', 'title' => 'Repair work finished?', 'copy' => 'Completion moves this record to Completed records for customer pickup.', 'button' => 'Mark as completed', 'dialogTitle' => 'Mark this repair completed?', 'dialogCopy' => 'The repair will be recorded as completed and ready for customer pickup.', 'submit' => 'Mark completed', 'icon' => '✓'],
                                'completed' => ['route' => route('manager.repairs.claim', $selectedRepair['reference']), 'eyebrow' => 'Customer handoff', 'title' => 'Has the bike been collected?', 'copy' => 'Only confirm this after the bike has been handed back to the customer.', 'button' => 'Mark bike as claimed', 'dialogTitle' => 'Confirm customer claim?', 'dialogCopy' => 'This closes the workshop lifecycle and records who confirmed the handoff.', 'submit' => 'Mark as claimed', 'icon' => '↗'],
                                default => null,
                            };
                        @endphp
                        <aside class="repair-inspector" aria-labelledby="repair-detail-title">
                            <div class="inspector-grid" aria-hidden="true"></div>
                            <div class="inspector-heading">
                                <div>
                                    <span>Repair record</span>
                                    <h2 id="repair-detail-title">{{ $selectedRepair['reference'] }}</h2>
                                </div>
                                @if ($repairKey === 'claimed')
                                    <div class="completion-stamp"><span>Bike</span><strong>Claimed</strong></div>
                                @else
                                    <div class="repair-state-mark state-{{ $repairKey }}"><i></i><span>{{ $selectedRepair['repair_status']['label'] }}</span></div>
                                @endif
                            </div>

                            <div class="repair-customer">
                                <span>{{ $selectedRepair['customer']['initials'] }}</span>
                                <div>
                                    <strong>{{ $selectedRepair['customer']['name'] }}</strong>
                                    <a href="mailto:{{ $selectedRepair['customer']['email'] }}">{{ $selectedRepair['customer']['email'] }}</a>
                                    <a href="tel:{{ preg_replace('/\s+/', '', $selectedRepair['customer']['phone']) }}">{{ $selectedRepair['customer']['phone'] }}</a>
                                </div>
                            </div>

                            <div class="repair-service-card">
                                <span>Repair service</span>
                                <strong>{{ $selectedRepair['service'] }}</strong>
                                <time datetime="{{ $selectedRepair['appointment_at']->toIso8601String() }}">Appointment: {{ $selectedRepair['appointment_at']->format('F d, Y · g:i A') }}</time>
                            </div>

                            <details class="customer-notes">
                                <summary>
                                    <span>Customer problem details</span>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"></path></svg>
                                </summary>
                                <div class="customer-notes-body">
                                    <p>{{ filled($selectedRepair['notes'] ?? null) ? $selectedRepair['notes'] : 'No additional details were provided for this repair.' }}</p>
                                </div>
                            </details>

                            <dl class="repair-status-grid">
                                <div><dt>Booking</dt><dd class="text-confirmed">{{ $selectedRepair['booking_status']['label'] }}</dd></div>
                                <div><dt>Payment</dt><dd class="text-{{ $selectedRepair['payment_status']['key'] }}">{{ $selectedRepair['payment_status']['label'] }}</dd></div>
                                <div><dt>Calendar</dt><dd class="text-{{ $selectedRepair['calendar_status']['key'] }}">{{ $selectedRepair['calendar_status']['label'] }}</dd></div>
                                <div><dt>Repair</dt><dd class="text-{{ $repairKey }}">{{ $selectedRepair['repair_status']['label'] }}</dd></div>
                            </dl>

                            <section class="audit-trail" aria-labelledby="audit-title">
                                <div><span>Record history</span><h3 id="audit-title">Audit trail</h3></div>
                                <ol>
                                    @foreach ($auditEvents as $event)
                                        <li class="audit-{{ $event['tone'] }}">
                                            <i aria-hidden="true"></i>
                                            <div><strong>{{ $event['label'] }}</strong><p>{{ $event['detail'] }}</p><time datetime="{{ $event['at']->toIso8601String() }}">{{ $event['at']->format('M d, Y · g:i A') }}</time></div>
                                        </li>
                                    @endforeach
                                </ol>
                            </section>

                            @if ($transition)
                                <div class="completion-action action-{{ $repairKey }}">
                                    <div><span>{{ $transition['eyebrow'] }}</span><strong>{{ $transition['title'] }}</strong><p>{{ $transition['copy'] }}</p></div>
                                    <button type="button" data-transition-open>{{ $transition['button'] }} <span aria-hidden="true">{{ $transition['icon'] }}</span></button>
                                </div>
                            @elseif ($repairKey === 'at_shop')
                                <div class="payment-lock">
                                    <span aria-hidden="true">!</span>
                                    <div><strong>Payment required before repair can begin</strong><p>Current payment status: {{ $selectedRepair['payment_status']['label'] }}. The bike remains recorded at the shop.</p></div>
                                </div>
                            @else
                                <div class="completion-recorded">
                                    <span aria-hidden="true">✓</span>
                                    <div><strong>Workshop lifecycle closed</strong><p>{{ $selectedRepair['claimed_at']->format('F d, Y · g:i A') }} by {{ $selectedRepair['claimed_by'] }}</p></div>
                                </div>
                            @endif
                        </aside>
                    @endif
                </div>
            </main>
        </div>

        @if ($selectedRepair && $transition)
            <dialog class="completion-dialog" data-transition-dialog>
                <form method="POST" action="{{ $transition['route'] }}">
                    @csrf
                    <div class="dialog-check" aria-hidden="true">{{ $transition['icon'] }}</div>
                    <span>{{ $transition['eyebrow'] }}</span>
                    <h2>{{ $transition['dialogTitle'] }}</h2>
                    <p>{{ $transition['dialogCopy'] }} Booking, payment, and calendar states remain separate.</p>
                    <div class="dialog-record">
                        <span>{{ $selectedRepair['reference'] }}</span>
                        <strong>{{ $selectedRepair['customer']['name'] }}</strong>
                        <small>{{ $selectedRepair['service'] }}</small>
                    </div>
                    <div class="dialog-actions">
                        <button type="button" data-transition-close>Go back</button>
                        <button type="submit" data-transition-submit>{{ $transition['submit'] }}</button>
                    </div>
                </form>
            </dialog>
        @endif
    </body>
</html>
