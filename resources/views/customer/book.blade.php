<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Choose a bike repair service to begin your Pedalya booking.">

        <title>Book a repair | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-booking.js'])
    </head>
    <body class="customer-booking-page">
        <header class="customer-header">
            <a class="brand" href="{{ route('home') }}" aria-label="Return to Pedalya home">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32">
                        <circle cx="16" cy="16" r="10.5"></circle>
                        <circle cx="16" cy="16" r="3"></circle>
                        <path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path>
                    </svg>
                </span>
                <span>Pedalya</span>
            </a>

            <div class="customer-header-actions">
                <span class="preview-pill"><i></i> Frontend preview</span>
                <a class="header-link" href="{{ route('home') }}">Sign out <span aria-hidden="true">↗</span></a>
            </div>
        </header>
@include('customer.partials.navigation')

        <main class="customer-booking-shell">
            <nav class="booking-steps" aria-label="Booking progress">
                <span class="booking-step is-active"><b>01</b><span>Service</span></span>
                <span class="booking-step"><b>02</b><span>Schedule</span></span>
                <span class="booking-step"><b>03</b><span>Review</span></span>
                <span class="booking-step"><b>04</b><span>Confirmation</span></span>
            </nav>

            <section class="booking-intro" aria-labelledby="booking-title">
                <div>
                    <span class="section-kicker">Start a booking</span>
                    <h1 id="booking-title">What does your<br><em>bike need?</em></h1>
                </div>
                <p>Choose a service below. You’ll pick an available appointment time in the next step.</p>
            </section>

            <form class="service-selection" data-service-form>
                <div class="service-grid" role="radiogroup" aria-label="Repair services">
                    @foreach ([
                        ['key' => 'brake-inspection', 'number' => '01', 'name' => 'Brake inspection', 'description' => 'Check stopping power, alignment, and pad wear.', 'duration' => '45 min', 'price' => '₱500', 'tone' => 'mint'],
                        ['key' => 'wheel-alignment', 'number' => '02', 'name' => 'Wheel alignment', 'description' => 'Bring your wheels back to a smooth, steady ride.', 'duration' => '60 min', 'price' => '₱650', 'tone' => 'lime'],
                        ['key' => 'full-tune-up', 'number' => '03', 'name' => 'Full tune-up', 'description' => 'A complete check and adjustment for everyday riding.', 'duration' => '90 min', 'price' => '₱1,200', 'tone' => 'blue'],
                        ['key' => 'flat-tire-repair', 'number' => '04', 'name' => 'Flat tire repair', 'description' => 'Repair or replace the tube and get rolling again.', 'duration' => '30 min', 'price' => '₱350', 'tone' => 'peach'],
                    ] as $service)
                        <label class="service-card service-card-{{ $service['tone'] }}" data-service-card>
                            <input type="radio" name="service" value="{{ $service['key'] }}" data-service-input>
                            <span class="service-card-top"><span>{{ $service['number'] }}</span><i aria-hidden="true">↗</i></span>
                            <span class="service-card-icon" aria-hidden="true">
                                @switch($service['key'])
                                    @case('brake-inspection')
                                        <svg viewBox="0 0 48 48"><circle cx="23" cy="24" r="13"></circle><path d="M29 13h7v22h-7M36 18h4M36 30h4"></path><path d="M23 17v14M16 24h14"></path></svg>
                                        @break
                                    @case('wheel-alignment')
                                        <svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="14"></circle><circle cx="24" cy="24" r="4"></circle><path d="M24 10v10M24 28v10M10 24h10M28 24h10"></path><path d="M16 16l4 4M28 28l4 4M32 16l-4 4M20 28l-4 4"></path></svg>
                                        @break
                                    @case('full-tune-up')
                                        <svg viewBox="0 0 48 48"><path d="M30 12a8 8 0 0 0-9 9L11 31a4 4 0 0 0 6 6l10-10a8 8 0 0 0 9-9l-6 4-5-5 5-5Z"></path><circle cx="14" cy="34" r="1.5"></circle></svg>
                                        @break
                                    @default
                                        <svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="14"></circle><circle cx="24" cy="24" r="5"></circle><path d="M24 10v5M24 33v5M10 24h5M33 24h5"></path><circle cx="31" cy="17" r="1.8"></circle><path d="M29 31l4 4"></path></svg>
                                @endswitch
                            </span>
                            <strong>{{ $service['name'] }}</strong>
                            <span class="service-card-description">{{ $service['description'] }}</span>
                            <span class="service-card-meta"><small>{{ $service['duration'] }}</small><b>{{ $service['price'] }}</b></span>
                            <span class="service-card-check" aria-hidden="true">✓</span>
                        </label>
                    @endforeach
                </div>

                <div class="booking-action-bar">
                    <p class="selection-message" data-selection-message role="status" aria-live="polite">Select a service to continue.</p>
                    <button class="booking-next" type="button" data-next-step disabled>
                        Continue to schedule <span aria-hidden="true">→</span>
                    </button>
                </div>
            </form>

            <div class="booking-preview-note" role="note">
                <span aria-hidden="true">✦</span>
                <p>This is a frontend preview using sample services. Booking persistence and availability will be connected in the backend phase.</p>
            </div>
        </main>
    </body>
</html>
