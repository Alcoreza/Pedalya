<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Manage your customer profile details for Pedalya.">
        <title>Your profile | Pedalya</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-profile.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js', 'resources/js/pages/customer-profile.js'])
    </head>
    <body class="customer-booking-page customer-profile-page">
        @include('customer.partials.navigation')
        <main class="customer-booking-shell profile-shell">
            <section class="profile-heading" aria-labelledby="profile-title">
                <div><span class="section-kicker">Customer profile</span><h1 id="profile-title">Your details,<br><em>your way.</em></h1><p>Keep your contact details ready for your next repair visit.</p></div>
            </section>
            <div class="profile-layout">
                <form class="profile-form-panel" data-profile-form method="POST" action="{{ route('customer.profile.update') }}" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="profile-panel-heading"><div><span>Account information</span><h2>Personal details</h2></div><span class="profile-panel-icon" aria-hidden="true"><span data-profile-initials>{{ strtoupper(substr($user->name, 0, 1)) }}</span>@if(!empty($profilePictureUrl))<img data-profile-picture src="{{ $profilePictureUrl }}" alt="">@endif</span></div>
                    <div class="profile-field-grid">
                        <label class="profile-field"><span>Full name</span><input name="name" type="text" value="{{ old('name', $user->name) }}" required><small data-profile-error="name">{{ $errors->first('name') }}</small></label>
                        <label class="profile-field"><span>Email address</span><input name="email" type="email" value="{{ old('email', $user->email) }}" required><small data-profile-error="email">{{ $errors->first('email') }}</small></label>
                        <label class="profile-field"><span>Phone number</span><input name="mobile_number" type="tel" value="{{ old('mobile_number', $user->mobile_number) }}" required><small data-profile-error="mobile_number">{{ $errors->first('mobile_number') }}</small></label>
                        <label class="profile-field"><span>Preferred location</span><input name="location" type="text" value="Quezon City" required><small data-profile-error="location"></small></label>
                    </div>
                    <div class="profile-form-actions"><p data-profile-message role="status" aria-live="polite">{{ session('status') ?? 'Update your contact details and save your changes.' }}</p><button class="booking-next" type="submit">Save changes <span aria-hidden="true">→</span></button></div>
                </form>
                <aside class="profile-side-panel">
                    <span class="section-kicker">Account snapshot</span><h2>Ready for your next ride.</h2><p>Your saved contact details are linked to your signed-in account.</p>
                    <div class="profile-side-detail"><span>Member since</span><strong>March 2026</strong></div><div class="profile-side-detail"><span>Bookings</span><strong>03 repair visits</strong></div>
                    <a class="customer-return-link" href="{{ route('customer.dashboard') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6-6 6 6 6M4 12h16"/></svg>Back to dashboard</a>
                </aside>
            </div>
        </main>
    </body>
</html>
