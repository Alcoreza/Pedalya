<?php

return [
    /*
    | Workshop scheduling defaults. These can move to database-backed manager
    | settings later without changing the policy output or booking page.
    */
    'default_capacity' => (int) env('BOOKING_DEFAULT_CAPACITY', 2),
    'default_duration_minutes' => (int) env('BOOKING_DEFAULT_DURATION', 60),
    'timezone' => env('BOOKING_TIMEZONE', 'Asia/Manila'),
    'opening_time' => env('BOOKING_OPENING_TIME', '09:00'),
    'closing_time' => env('BOOKING_CLOSING_TIME', '18:00'),
    'slot_interval_minutes' => (int) env('BOOKING_SLOT_INTERVAL_MINUTES', 90),
    'minimum_notice_minutes' => (int) env('BOOKING_MINIMUM_NOTICE_MINUTES', 30),
    'open_days' => [1, 2, 3, 4, 5, 6],

    'services' => [
        'brake-inspection' => ['duration_minutes' => 45, 'capacity' => null],
        'wheel-alignment' => ['duration_minutes' => 60, 'capacity' => null],
        'full-tune-up' => ['duration_minutes' => 90, 'capacity' => null],
        'flat-tire-repair' => ['duration_minutes' => 30, 'capacity' => null],
    ],

    /* Rules are evaluated in order: ISO weekdays, time window, services. */
    'capacity_overrides' => [
        // ['days' => [6], 'start' => '09:00', 'end' => '12:00', 'services' => [], 'capacity' => 3],
    ],
];
