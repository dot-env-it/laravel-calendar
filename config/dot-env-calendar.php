<?php

declare(strict_types=1);

return [
    'default_time' => '10:30:00',

    'initialView' => 'dayGridMonth',

    /*
    |--------------------------------------------------------------------------
    | Smart View Switching
    |--------------------------------------------------------------------------
    |
    | When set to true, the calendar will automatically switch to a specific
    | view if there are events or tasks scheduled for today.
    |
    */
    'switch_view_if_event_today' => true,
    'today_event_view'           => 'timeGridDay', // The view to open when events exist today

    // Shows a live marker indicating the current time on time-grid layouts
    'show_now_indicator' => true,

    // Auto-scroll the vertical calendar view to the current time line
    'auto_scroll_to_now' => true,

    /*
    |--------------------------------------------------------------------------
    | Dynamic Event Styling
    |--------------------------------------------------------------------------
    | The package automatically colors events based on their date.
    | You can use Bootstrap/Tailwind classes or HEX codes.
    */
    'colors' => [
        'past'     => ['class' => 'bg-danger text-light', 'color' => '#dc3545'],
        'today'    => ['class' => 'bg-warning text-dark', 'color' => '#ffc107'],
        'tomorrow' => ['class' => 'bg-info text-light', 'color' => '#17a2b8'],
        'future'   => ['class' => 'bg-success text-light', 'color' => '#28a745'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings for Business Hours and Weekends
    |--------------------------------------------------------------------------
    | Define the business hours for the calendar. This will visually differentiate these hours on the calendar.
    | The 'daysOfWeek' array uses 0 for Sunday, 1 for Monday, ..., 6 for Saturday. Adjust as needed.
    */
    'businessHours' => [
        'daysOfWeek' => [0, 1, 2, 3, 4, 5, 6],
        'startTime'  => '09:00',
        'endTime'    => '18:00',
    ],

    'hideWeekends' => false, // Toggle this if the lawyer wants a 5-day view

    /*
    |--------------------------------------------------------------------------
    | Event Time Display Options
    |--------------------------------------------------------------------------
    | Configure how event times are displayed on the calendar. You can choose to show or hide the time, start time,
    | and end time for events.
    */

    'displayEventTime'  => true,
    'displayEventStart' => false,
    'displayEventEnd'   => true,

    /*
    |--------------------------------------------------------------------------
    | Event Time Format
    |--------------------------------------------------------------------------
    | Define the format for displaying event times. This uses the Intl.DateTimeFormat options,
    | allowing you to customize how times are shown (e.g., 12-hour vs 24-hour format).
    */

    'eventTimeFormat' => [
        'hour'   => 'numeric',
        'minute' => '2-digit',
        'hour12' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | All-Day Event Handling
    |--------------------------------------------------------------------------
    | When set to true, any event that does not have a specific time component will
    | automatically be treated as an all-day event.
    */
    'all_day' => true,

    /*
    |--------------------------------------------------------------------------
    | Resizable Events
    |--------------------------------------------------------------------------
    | Enable the ability to resize events by dragging their edges.
    | 'eventResizableFromStart' allows resizing from the start of the event,
    | while 'eventDurationEditable' allows changing the duration by dragging either edge.
    */
    'eventResizableFromStart' => false,
    'eventDurationEditable'   => true,

    /*
    |--------------------------------------------------------------------------
    | Header Toolbar Configuration
    |--------------------------------------------------------------------------
    | Define the buttons and layout of the calendar's header toolbar. The 'left', 'center', and 'right' keys determine
    */
    'headerToolbar' => [
        'left'   => 'prev,next today',
        'center' => 'title',
        'right'  => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
    ],

    /*
    |--------------------------------------------------------------------------
    | Filter Toggle
    |--------------------------------------------------------------------------
    | When multiple models are registered to display events on the calendar, this option enables a filter dropdown
    | that allows users to toggle between different event sources. If set to false, the filter will be hidden
    | and all events will be shown together.
    */

    'enable_filter' => true,

    /*
    |--------------------------------------------------------------------------
    | Other FullCalendar Options
    |--------------------------------------------------------------------------
    |
    */
    'dayMaxEventRows' => 4,
    'lazyFetching'    => true,

    'editable'   => true,
    'selectable' => false,

    'changeStartDateOnDrop' => false, // when you don't want to change start date make it false

    /*
    |--------------------------------------------------------------------------
    | Event list customization
    |--------------------------------------------------------------------------
    |
    */
    'moreLinkText' => ':num events',
    'moreLinkClick' => 'popover', // available options: popover or event

];
