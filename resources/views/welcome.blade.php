<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Pedalya makes bike repair booking, scheduling, payment, and repair updates easier to manage in one place.">

        <title>Pedalya | Bike repair, made simple</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/pages/landing.css', 'resources/js/app.js', 'resources/js/pages/landing.js'])
    </head>
    <body class="landing-page">
        <a class="skip-link" href="#main-content">Skip to content</a>

        <header class="site-header" data-site-header>
            <div class="page-shell nav-shell">
                <a class="brand" href="#top" aria-label="Pedalya home">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 32 32" role="img">
                            <circle cx="16" cy="16" r="10.5"></circle>
                            <circle cx="16" cy="16" r="3"></circle>
                            <path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path>
                        </svg>
                    </span>
                    <span>Pedalya</span>
                </a>

                <nav class="desktop-nav" aria-label="Primary navigation">
                    <a href="#features">Why Pedalya</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="#connected">Connected services</a>
                </nav>

                <div class="nav-actions">
                    <a class="nav-login" href="{{ Route::has('login') ? route('login') : '#how-it-works' }}">Log in</a>
                    <a class="button button-small button-primary" href="#how-it-works">
                        Book a repair
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>

                <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" data-menu-toggle>
                    <span class="sr-only">Toggle navigation</span>
                    <span></span><span></span>
                </button>
            </div>

            <div class="mobile-menu" id="mobile-menu" data-mobile-menu hidden>
                <nav class="page-shell" aria-label="Mobile navigation">
                    <a href="#features">Why Pedalya</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="#connected">Connected services</a>
                    <a href="{{ Route::has('login') ? route('login') : '#how-it-works' }}">Log in</a>
                    <a class="button button-primary" href="#how-it-works">Book a repair</a>
                </nav>
            </div>
        </header>

        <main id="main-content">
            <section class="hero" id="top">
                <div class="hero-glow hero-glow-one" aria-hidden="true"></div>
                <div class="hero-glow hero-glow-two" aria-hidden="true"></div>

                <div class="page-shell hero-grid">
                    <div class="hero-copy">
                        <div class="eyebrow hero-enter" style="--enter-delay: 80ms">
                            <span class="eyebrow-dot"></span>
                            Bike care, without the back-and-forth
                        </div>

                        <h1 class="hero-enter" style="--enter-delay: 160ms">
                            Book smarter.<br>
                            <span>Ride sooner.</span>
                        </h1>

                        <p class="hero-lead hero-enter" style="--enter-delay: 240ms">
                            Choose a repair service, find a schedule that works, pay securely, and follow your booking from one calm, connected place.
                        </p>

                        <div class="hero-actions hero-enter" style="--enter-delay: 320ms">
                            <a class="button button-primary button-large" href="#how-it-works">
                                Start a booking
                                <span class="button-arrow" aria-hidden="true">↗</span>
                            </a>
                            <a class="text-link" href="#features">
                                Explore Pedalya
                                <span aria-hidden="true">↓</span>
                            </a>
                        </div>

                        <div class="hero-proof hero-enter" style="--enter-delay: 400ms" aria-label="Pedalya benefits">
                            <div>
                                <strong>One place</strong>
                                <span>Booking to repair updates</span>
                            </div>
                            <div>
                                <strong>Secure flow</strong>
                                <span>Trusted payment handling</span>
                            </div>
                        </div>
                    </div>

                    <div class="hero-visual hero-enter" style="--enter-delay: 220ms" aria-label="Pedalya booking experience preview">
                        <div class="visual-orbit visual-orbit-one" aria-hidden="true"></div>
                        <div class="visual-orbit visual-orbit-two" aria-hidden="true"></div>

                        <div class="bike-stage">
                            <div class="bike-wordmark" aria-hidden="true">RIDE</div>
                            <svg class="bike-illustration" viewBox="0 0 620 400" role="img" aria-label="Minimal line illustration of a bicycle">
                                <g class="bike-wheels">
                                    <circle cx="155" cy="278" r="91"></circle>
                                    <circle cx="468" cy="278" r="91"></circle>
                                </g>
                                <g class="bike-frame">
                                    <path d="M155 278 245 151l86 127H155l67-95h183l63 95"></path>
                                    <path d="M245 151h69M331 278l55-171M365 107h55M222 183l-18-46M184 137h52"></path>
                                    <circle cx="331" cy="278" r="22"></circle>
                                    <path d="m331 278 38 27M331 278l-31-27"></path>
                                </g>
                            </svg>
                        </div>

                        <div class="floating-card card-booking">
                            <span class="card-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"></path></svg>
                            </span>
                            <span>
                                <small>Plan with ease</small>
                                <strong>Choose your schedule</strong>
                            </span>
                        </div>

                        <div class="floating-card card-status">
                            <span class="status-pulse" aria-hidden="true"></span>
                            <span>
                                <small>Stay informed</small>
                                <strong>See booking updates</strong>
                            </span>
                        </div>

                        <div class="visual-caption">
                            <span>01</span>
                            <p>A smoother path from repair request to your next ride.</p>
                        </div>
                    </div>
                </div>

                <div class="page-shell integration-strip hero-enter" style="--enter-delay: 520ms">
                    <span>Connected with the tools you already trust</span>
                    <div>
                        <strong>PayMongo</strong>
                        <i></i>
                        <strong>Google Calendar</strong>
                        <i></i>
                        <strong>Google Maps</strong>
                    </div>
                </div>
            </section>

            <section class="section section-features" id="features">
                <div class="page-shell">
                    <div class="section-heading reveal">
                        <div>
                            <span class="section-kicker">A better repair experience</span>
                            <h2>Less chasing.<br>More riding.</h2>
                        </div>
                        <p>Pedalya brings the essential parts of bike repair booking into a single, easy-to-follow experience for customers and the repair shop.</p>
                    </div>

                    <div class="feature-grid">
                        <article class="feature-card feature-card-large reveal">
                            <div class="feature-number">01</div>
                            <div class="feature-art schedule-art" aria-hidden="true">
                                <div class="mini-calendar">
                                    <div class="mini-calendar-head"><span></span><span></span></div>
                                    <div class="mini-calendar-grid">
                                        @for ($day = 1; $day <= 14; $day++)
                                            <i class="{{ $day === 9 ? 'is-selected' : '' }}">{{ $day }}</i>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h3>Plan without the phone tag</h3>
                                <p>Browse available repair options and choose an appointment schedule through a clear, guided booking flow.</p>
                            </div>
                        </article>

                        <article class="feature-card reveal" style="--reveal-delay: 80ms">
                            <div class="feature-number">02</div>
                            <div class="feature-art payment-art" aria-hidden="true">
                                <div class="payment-chip"><span>₱</span><i></i></div>
                                <svg viewBox="0 0 180 90"><path d="M8 72c29-44 51 7 79-29s48-10 85-32"></path></svg>
                            </div>
                            <div>
                                <h3>Pay through a trusted checkout</h3>
                                <p>Continue to a secure PayMongo payment experience when your booking becomes eligible for payment.</p>
                            </div>
                        </article>

                        <article class="feature-card reveal" style="--reveal-delay: 160ms">
                            <div class="feature-number">03</div>
                            <div class="feature-art updates-art" aria-hidden="true">
                                <span></span><span></span><span></span>
                                <div class="updates-line"></div>
                            </div>
                            <div>
                                <h3>Keep every update in view</h3>
                                <p>Return anytime to see your booking, payment, and repair information without searching through separate messages.</p>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <section class="section process-section" id="how-it-works">
                <div class="page-shell process-grid">
                    <div class="process-intro reveal">
                        <span class="section-kicker section-kicker-light">How it works</span>
                        <h2>Three simple steps to get rolling again.</h2>
                        <p>Designed to feel straightforward from your first click to the moment your bike is ready.</p>
                        <a class="button button-light" href="{{ Route::has('register') ? route('register') : '#connected' }}">
                            Get started
                            <span aria-hidden="true">↗</span>
                        </a>
                    </div>

                    <div class="process-list">
                        <article class="process-item reveal">
                            <span>01</span>
                            <div>
                                <h3>Choose what your bike needs</h3>
                                <p>Review the available repair services and select the option that fits your concern.</p>
                            </div>
                            <i aria-hidden="true">↗</i>
                        </article>
                        <article class="process-item reveal" style="--reveal-delay: 80ms">
                            <span>02</span>
                            <div>
                                <h3>Pick a convenient schedule</h3>
                                <p>Choose an available appointment date and time, then review your booking details.</p>
                            </div>
                            <i aria-hidden="true">↗</i>
                        </article>
                        <article class="process-item reveal" style="--reveal-delay: 160ms">
                            <span>03</span>
                            <div>
                                <h3>Follow your booking with clarity</h3>
                                <p>Check your booking and repair information as the shop reviews and works on your request.</p>
                            </div>
                            <i aria-hidden="true">↗</i>
                        </article>
                    </div>
                </div>
            </section>

            <section class="section connected-section" id="connected">
                <div class="page-shell connected-card reveal">
                    <div class="connected-copy">
                        <span class="section-kicker">One connected experience</span>
                        <h2>Built around the services that keep things moving.</h2>
                        <p>Payments, appointments, and shop location work together through Pedalya while each provider stays focused on what it does best.</p>
                    </div>

                    <div class="connected-services" aria-label="Pedalya integrations">
                        <div class="service-pill">
                            <span class="service-logo paymongo-logo">P</span>
                            <span><strong>PayMongo</strong><small>Secure checkout</small></span>
                        </div>
                        <div class="service-pill">
                            <span class="service-logo calendar-logo">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"></path></svg>
                            </span>
                            <span><strong>Google Calendar</strong><small>Connected scheduling</small></span>
                        </div>
                        <div class="service-pill">
                            <span class="service-logo map-logo">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path><circle cx="12" cy="10" r="2.5"></circle></svg>
                            </span>
                            <span><strong>Google Maps</strong><small>Shop location</small></span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="final-cta">
                <div class="page-shell final-cta-inner reveal">
                    <div>
                        <span class="section-kicker">Your next ride starts here</span>
                        <h2>Ready when your bike needs a little care.</h2>
                    </div>
                    <a class="button button-primary button-large" href="#how-it-works">
                        Book a repair
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <div class="page-shell footer-grid">
                <a class="brand brand-footer" href="#top">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="10.5"></circle><circle cx="16" cy="16" r="3"></circle><path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path></svg>
                    </span>
                    <span>Pedalya</span>
                </a>
                <p>Bike repair booking, made organized and easy to follow.</p>
                <div class="footer-links">
                    <a href="#features">Why Pedalya</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="{{ Route::has('login') ? route('login') : '#top' }}">Log in</a>
                </div>
                <small>© {{ date('Y') }} Pedalya. Academic project by MABS.</small>
            </div>
        </footer>
    </body>
</html>
