<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<style>
    .dot-env-calendar-container {
        min-height: 600px;
    }

    .fc-event {
        cursor: pointer;
        border: none !important;
        transition: transform 0.1s;
    }

    .fc-event:hover {
        transform: scale(1.02);
    }

    .dot-env-calendar .fc-toolbar-title {
        font-size: 1.1rem !important;
        color: #e2e8f0;
    }

    .dot-env-calendar .fc-button-primary {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }

    .dot-env-calendar .fc-button-active {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
    }

    /* Prevent the container from collapsing during data fetch */
    .dot-env-calendar {
        min-height: 600px; /* Adjust based on your UI */
        transition: none !important;
    }

    /* Hide the 'vibrating' loading text and use a clean overlay if needed */
    .fc-view-harness {
        background-color: transparent;
    }

    /* Ensure the Select2 doesn't jump the header */
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        display: flex;
        align-items: center;
    }
</style>

