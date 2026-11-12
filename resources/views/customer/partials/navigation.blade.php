@php
    $bookingActive = request()->routeIs('customer.bookings', 'customer.booking.detail', 'customer.repair', 'customer.payment', 'customer.payment.success', 'customer.payment.failed');
@endphp

<header class="customer-site-header">
    <div class="customer-nav-shell">
        <a class="brand" href="{{ route('home') }}" aria-label="Return to Pedalya home">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" role="img">
                    <circle cx="16" cy="16" r="10.5"></circle>
                    <circle cx="16" cy="16" r="3"></circle>
                    <path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path>
                </svg>
            </span>
            <span>Pedalya</span>
        </a>

        <nav class="customer-nav" aria-label="Customer navigation">
            <a class="{{ request()->routeIs('customer.dashboard') ? 'is-active' : '' }}" href="{{ route('customer.dashboard') }}" @if(request()->routeIs('customer.dashboard')) aria-current="page" @endif>Dashboard</a>
            <a class="{{ $bookingActive ? 'is-active' : '' }}" href="{{ route('customer.bookings') }}" @if($bookingActive) aria-current="page" @endif>Bookings</a>
        </nav>

        <div class="customer-nav-actions">
            <a class="customer-nav-login" href="{{ route('home') }}">Sign out <span aria-hidden="true">↗</span></a>
            <a class="customer-nav-cta" href="{{ route('customer.book') }}">Book a repair <span class="customer-nav-cta-arrow" aria-hidden="true">↗</span></a>
        </div>
    </div>
</header>
