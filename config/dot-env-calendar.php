<?php

return [
    'default_time' => '10:30:00',

    'initialView' => 'dayGridMonth',

    /*
    |--------------------------------------------------------------------------
    | Dynamic Event Styling
    |--------------------------------------------------------------------------
    | The package automatically colors events based on their date.
    | You can use Bootstrap/Tailwind classes or HEX codes.
    */
    'colors' => [
        'past' => ['class' => 'bg-danger text-white', 'color' => '#dc3545'],
        'today' => ['class' => 'bg-warning text-dark', 'color' => '#ffc107'],
        'tomorrow' => ['class' => 'bg-info text-white', 'color' => '#17a2b8'],
        'future' => ['class' => 'bg-success text-white', 'color' => '#28a745'],
    ],
    'businessHours' => [
        'daysOfWeek' => [0, 1, 2, 3, 4, 5, 6],
        'startTime' => '09:00',
        'endTime' => '18:00',
    ],
    'hideWeekends' => false, // Toggle this if the lawyer wants a 5-day view

    'dayMaxEventRows' => 4,
    'lazyFetching' => true,

    'editable' => true,
    'selectable' => false,

    // Add resizable option
    'eventResizableFromStart' => false,
    'eventDurationEditable' => true,

    'headerToolbar' => [
        'left' => 'prev,next today',
        'center' => 'title',
        'right' => 'dayGridMonth,timeGridWeek,listWeek',
    ],

    'enable_filter' => true, // Enable filter when multiple model's events are displayed.
];
