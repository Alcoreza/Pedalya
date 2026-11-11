<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Choose a Pedalya repair to view its progress.">
        <title>My repairs | Pedalya</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
@vite(['resources/css/app.css', 'resources/css/pages/customer-booking.css', 'resources/css/pages/customer-repairs.css', 'resources/css/pages/customer-typography.css', 'resources/js/app.js'])
    </head>
    <body class="customer-booking-page customer-repairs-page">
        @include('customer.partials.navigation')
        <main class="customer-booking-shell">
            <section class="repairs-heading" aria-labelledby="repairs-title"><div><span class="section-kicker">Customer workspace</span><h1 id="repairs-title">Your repair<br><em>progress.</em></h1><p>Choose a repair to see its latest workshop update.</p></div><a class="booking-next" href="{{ route('customer.book') }}">Book a repair <span aria-hidden="true">→</span></a></section>
            <section class="repair-choice-grid" aria-label="Your repairs">
                @foreach ([['reference' => 'PDL-1037', 'service' => 'Full tune-up', 'date' => 'Fri, Mar 13 · 1:30 PM', 'status' => 'Repair in progress', 'tone' => 'brand'], ['reference' => 'PDL-1019', 'service' => 'Wheel alignment', 'date' => 'Completed Mar 02 · 10:30 AM', 'status' => 'Repair completed', 'tone' => 'success'], ['reference' => 'PDL-1048', 'service' => 'Brake inspection', 'date' => 'Appointment Mar 17 · 9:00 AM', 'status' => 'Awaiting review', 'tone' => 'pending']] as $repair)
                    <article class="repair-choice-card"><div class="repair-choice-top"><span>{{ $repair['reference'] }}</span><span class="repair-choice-icon" aria-hidden="true">✦</span></div><h2>{{ $repair['service'] }}</h2><p>{{ $repair['date'] }}</p><span class="repair-choice-status status-{{ $repair['tone'] }}"><i></i>{{ $repair['status'] }}</span><a href="{{ route('customer.repair', $repair['reference']) }}">View repair progress <span aria-hidden="true">→</span></a></article>
                @endforeach
            </section>
            <div class="booking-preview-note" role="note"><span aria-hidden="true">✦</span><p>These repair records are representative frontend data. Live progress updates will be connected later.</p></div>
        </main>
    </body>
</html>
