<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Create your Pedalya account and begin managing bike repair bookings in one place.">

        <title>Create an account | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/pages/register.css', 'resources/js/app.js', 'resources/js/pages/register.js'])
    </head>
    <body class="register-page">
        <div class="register-grid" aria-hidden="true"></div>

        <header class="register-header register-enter" style="--register-delay: 40ms">
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

            <p>Already have an account? <a href="{{ route('login') }}">Log in <span aria-hidden="true">↗</span></a></p>
        </header>

        <main class="register-main">
            <section class="register-intro register-enter" style="--register-delay: 100ms">
                <p class="register-index">01 — ACCOUNT</p>
                <h1>Set up your<br><span>rider profile.</span></h1>
                <p class="register-summary">One account for bookings, schedules, and repair updates.</p>

                <div class="profile-orbit" aria-hidden="true">
                    <div class="orbit-ring orbit-ring-outer">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="orbit-ring orbit-ring-inner"></div>
                    <div class="orbit-core">
                        <svg viewBox="0 0 64 64">
                            <circle cx="32" cy="23" r="10"></circle>
                            <path d="M14 54c2-12 9-18 18-18s16 6 18 18"></path>
                        </svg>
                    </div>
                    <span class="orbit-label orbit-label-one">BOOK</span>
                    <span class="orbit-label orbit-label-two">TRACK</span>
                    <span class="orbit-label orbit-label-three">RIDE</span>
                </div>
            </section>

            <section class="register-form-section register-enter" style="--register-delay: 180ms" aria-labelledby="register-title">
                <div class="register-form-head">
                    <div>
                        <span>NEW CUSTOMER</span>
                        <h2 id="register-title">Create your account</h2>
                    </div>
                    <div class="register-progress" aria-label="Registration step 1 of 1">
                        <strong>1</strong><span></span><small>1</small>
                    </div>
                </div>

                @if (session('status'))
                    <div class="register-alert register-alert-success" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="register-alert register-alert-error" role="alert">
                        @foreach ($errors->all() as $error)
                            <span>{{ $error }}</span>
                        @endforeach
                    </div>
                @endif

                <form class="register-form" method="POST" action="{{ url('/register') }}" data-register-preview novalidate>
                    @csrf

                    <div class="register-fields-two">
                        <div class="register-field">
                            <label for="name">Full name</label>
                            <div class="register-input">
                                <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Juan Dela Cruz" autocomplete="name" required>
                                <span aria-hidden="true">Aa</span>
                            </div>
                            <small class="register-error" data-error-for="name"></small>
                        </div>

                        <div class="register-field">
                            <label for="phone">Mobile number</label>
                            <div class="register-input">
                                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="09XX XXX XXXX" autocomplete="tel" inputmode="tel" required>
                                <span aria-hidden="true">+63</span>
                            </div>
                            <small class="register-error" data-error-for="phone"></small>
                        </div>
                    </div>

                    <div class="register-field">
                        <label for="email">Email address</label>
                        <div class="register-input">
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required>
                            <span aria-hidden="true">@</span>
                        </div>
                        <small class="register-error" data-error-for="email"></small>
                    </div>

                    <div class="register-fields-two">
                        <div class="register-field">
                            <label for="register-password">Password</label>
                            <div class="register-input register-password-input">
                                <input id="register-password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required data-register-password>
                                <button type="button" aria-label="Show password" aria-pressed="false" data-register-password-toggle="register-password">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                                </button>
                            </div>
                            <div class="password-meter" data-password-meter aria-hidden="true"><i></i><i></i><i></i></div>
                            <small class="register-error" data-error-for="password"></small>
                        </div>

                        <div class="register-field">
                            <label for="password_confirmation">Confirm password</label>
                            <div class="register-input register-password-input">
                                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat password" autocomplete="new-password" minlength="8" required>
                                <button type="button" aria-label="Show password" aria-pressed="false" data-register-password-toggle="password_confirmation">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                                </button>
                            </div>
                            <small class="register-error" data-error-for="password_confirmation"></small>
                        </div>
                    </div>

                    <button class="register-submit" type="submit">
                        <span>Create account</span>
                        <i aria-hidden="true">↗</i>
                    </button>

                    <div class="register-preview-notice" role="status" aria-live="polite" data-register-notice hidden>
                        The registration interface is ready. Account creation will work after the authentication backend is connected.
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
