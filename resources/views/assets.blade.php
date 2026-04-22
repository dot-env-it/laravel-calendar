<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<style>
    /*.dot-env-calendar-container {*/
    /*    min-height: 600px;*/
    /*    transition: none !important;*/
    /*}*/

    .fc-event {
        cursor: pointer;
        border: none !important;
        transition: transform 0.1s;
    }

    .fc-event:hover {
        transform: scale(1.02);
    }


    /* Hide the 'vibrating' loading text and use a clean overlay if needed */
    /*.fc-view-harness {*/
    /*    background-color: transparent;*/
    /*}*/

    /*!* Ensure the Select2 doesn't jump the header *!*/
    /*.select2-container--default .select2-selection--single {*/
    /*    height: 38px !important;*/
    /*    display: flex;*/
    /*    align-items: center;*/
    /*}*/
</style>

