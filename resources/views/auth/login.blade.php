<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Sign in to Pedalya to manage your bike repair bookings and updates.">

        <title>Log in | Pedalya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/pages/login.css', 'resources/js/app.js', 'resources/js/pages/login.js'])
    </head>
    <body class="login-page">
        <main class="login-layout">
            <section class="login-story" aria-label="Pedalya experience">
                <div class="login-story-grid" aria-hidden="true"></div>

                <a class="brand login-brand" href="{{ route('home') }}" aria-label="Return to Pedalya home">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 32 32">
                            <circle cx="16" cy="16" r="10.5"></circle>
                            <circle cx="16" cy="16" r="3"></circle>
                            <path d="M16 5.5v7.5M16 19v7.5M5.5 16H13M19 16h7.5M8.6 8.6l5.3 5.3M18.1 18.1l5.3 5.3M23.4 8.6l-5.3 5.3M13.9 18.1l-5.3 5.3"></path>
                        </svg>
                    </span>
                    <span>Pedalya</span>
                </a>

                <div class="login-story-copy login-sequence" style="--login-delay: 120ms">
                    <span class="login-story-kicker">Your repair journey</span>
                    <h1>Every repair has a clear next step.</h1>
                    <p>Return to your account to keep bookings, schedules, and repair information within reach.</p>
                </div>

                <div class="repair-route login-sequence" style="--login-delay: 260ms" aria-hidden="true">
                    <svg class="route-track" viewBox="0 0 560 260" preserveAspectRatio="none">
                        <path class="route-track-base" d="M18 208C111 208 103 74 212 74s113 127 219 127c66 0 86-51 112-117"></path>
                        <path class="route-track-progress" d="M18 208C111 208 103 74 212 74s113 127 219 127c66 0 86-51 112-117"></path>
                    </svg>

                    <div class="route-node route-node-one">
                        <span>01</span>
                        <strong>Request</strong>
                    </div>
                    <div class="route-node route-node-two">
                        <span>02</span>
                        <strong>Schedule</strong>
                    </div>
                    <div class="route-node route-node-three">
                        <span>03</span>
                        <strong>Updates</strong>
                    </div>

                    <div class="route-signal">
                        <span></span>
                    </div>
                </div>

                <div class="login-story-footer login-sequence" style="--login-delay: 400ms">
                    <span class="story-line"></span>
                    <p>One account. A calmer way to care for your bike.</p>
                </div>
            </section>

            <section class="login-form-panel">
                <a class="back-home login-sequence" style="--login-delay: 80ms" href="{{ route('home') }}">
                    <span aria-hidden="true">←</span>
                    Back to home
                </a>

                <div class="login-form-wrap">
                    <div class="login-heading login-sequence" style="--login-delay: 160ms">
                        <div class="secure-label">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10V8a5 5 0 0 1 10 0v2M6 10h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Z"></path></svg>
                            Secure account access
                        </div>
                        <h2>Welcome back.</h2>
                        <p>Enter your details to continue to Pedalya.</p>
                    </div>

                    @if (session('status'))
                        <div class="login-alert login-alert-success" role="status">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="login-alert login-alert-error" role="alert">
                            <strong>We couldn't sign you in.</strong>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form class="login-form login-sequence" style="--login-delay: 240ms" method="POST" action="{{ url('/login') }}" data-login-preview novalidate>
                        @csrf

                        <div class="field-group">
                            <label for="email">Email address</label>
                            <div class="input-shell">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 6 8 6 8-6M5 5h14a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"></path></svg>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required autofocus>
                            </div>
                            <span class="field-error" data-error-for="email"></span>
                        </div>

                        <div class="field-group">
                            <div class="field-label-row">
                                <label for="password">Password</label>
                                <span class="field-hint">At least 8 characters</span>
                            </div>
                            <div class="input-shell">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10V8a5 5 0 0 1 10 0v2M6 10h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Z"></path></svg>
                                <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" minlength="8" required>
                                <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false" data-password-toggle>
                                    <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m4 4 16 16M9.6 6.3A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-2.1 2.7M6.3 7.2C3.9 9 2.5 12 2.5 12s3.5 6 9.5 6a9.8 9.8 0 0 0 2.5-.3M10 10.2a2.5 2.5 0 0 0 3.8 3.8"></path></svg>
                                </button>
                            </div>
                            <span class="field-error" data-error-for="password"></span>
                        </div>

                        <div class="login-options">
                            <label class="remember-control">
                                <input name="remember" type="checkbox">
                                <span aria-hidden="true"><svg viewBox="0 0 16 16"><path d="m3 8 3 3 7-7"></path></svg></span>
                                Keep me signed in
                            </label>
                            <span class="forgot-placeholder" title="Password recovery will be connected with authentication">Forgot password?</span>
                        </div>

                        <button class="login-submit" type="submit">
                            <span>Log in to Pedalya</span>
                            <i aria-hidden="true">→</i>
                        </button>

                        <div class="login-preview-notice" role="status" aria-live="polite" data-login-notice hidden>
                            The login interface is ready. Authentication will be connected during the backend phase.
                        </div>
                    </form>

                    <div class="login-help login-sequence" style="--login-delay: 320ms">
                        <span></span>
                        <p>New to Pedalya? <a href="{{ route('register') }}">Create an account</a></p>
                        <span></span>
                    </div>
                </div>

                <p class="login-legal">By continuing, you agree to use Pedalya responsibly and keep your account secure.</p>
            </section>
        </main>
    </body>
</html>
