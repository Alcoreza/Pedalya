<?php

return [
    /*
    | Workshop scheduling defaults. These can move to database-backed manager
    | settings later without changing the policy output or booking page.
    */
    'default_capacity' => (int) env('BOOKING_DEFAULT_CAPACITY', 2),
    'default_duration_minutes' => (int) env('BOOKING_DEFAULT_DURATION', 60),

    'services' => [
        'brake-inspection' => ['duration_minutes' => 45, 'capacity' => null],
        'wheel-alignment' => ['duration_minutes' => 60, 'capacity' => null],
        'general-tune-up' => ['duration_minutes' => 90, 'capacity' => null],
        'chain-replacement' => ['duration_minutes' => 60, 'capacity' => null],
    ],

    /* Rules are evaluated in order: ISO weekdays, time window, services. */
    'capacity_overrides' => [
        // ['days' => [6], 'start' => '09:00', 'end' => '12:00', 'services' => [], 'capacity' => 3],
    ],
];
