<div
        x-data="{
        calendar: null,
        init() {
            $(this.$refs.filterSelect).select2({
                minimumResultsForSearch: -1,
                width: '200px'
            }).on('change', (e) => {
                // Only fire if the value actually changed
                @this.set('filter', e.target.value);
            });

            this.calendar = new FullCalendar.Calendar(this.$refs.canvas, {
                initialView: @js(config('dot-env-calendar.initialView', 'dayGridMonth')),
                dayMaxEventRows: @js(config('dot-env-calendar.dayMaxEventRows', 5)),

                // BUSINESS HOURS
                businessHours: @js(config('dot-env-calendar.businessHours')),
                weekends: !@js(config('dot-env-calendar.hideWeekends', false)),

                moreLinkContent: function(args) {
                    return @js(config('dot-env-calendar.moreLinkText')).replace(':num', args.num);
                },

                moreLinkClick: function(info) {
                    @if(config('dot-env-calendar.moreLinkText') === 'popover')
                        return 'popover';
                    @else
                        const date = new Date(info.dateStr);
                        $wire.moreLinkClickEvent(date.getFullYear(), date.getMonth() + 1, date.getDate());
                        return false;
                    @endif
                },

                // Force both start and end times to display on the event blocks
                displayEventTime: false,

                eventContent: function(arg) {
                    let titleText = arg.event.title;
                    let startTimeText = '';
                    let endTimeText = '';

                    // Check if an end time exists for the event
                    if (@js(config('dot-env-calendar.displayEventTime', false)) ) {
                        if (@js(config('dot-env-calendar.displayEventStart', false)) && arg.event.start) {
                            startTimeText = arg.event.start.toLocaleTimeString([], {
                                hour: @js(config('dot-env-calendar.eventTimeFormat.hour', 'numeric')),
                                minute: @js(config('dot-env-calendar.eventTimeFormat.minute', '2-digit')),
                                hour12: @js(config('dot-env-calendar.eventTimeFormat.hour12', true))
                            }).toLowerCase().replace(' ', '');
                        }

                        if (@js(config('dot-env-calendar.displayEventEnd', false)) && arg.event.end) {
                            endTimeText = arg.event.end.toLocaleTimeString([], {
                                hour: @js(config('dot-env-calendar.eventTimeFormat.hour', 'numeric')),
                                minute: @js(config('dot-env-calendar.eventTimeFormat.minute', '2-digit')),
                                hour12: @js(config('dot-env-calendar.eventTimeFormat.hour12', true))
                            }).toLowerCase().replace(' ', '');
                        }
                    }

                    // Create the custom DOM layout
                    let customEl = document.createElement('div');
                    customEl.className = 'fc-event-main-frame';

                    let timeText = '';
                    if (startTimeText && endTimeText) {
                        timeText = `${startTimeText} - ${endTimeText}`;
                    } else if (startTimeText) {
                        timeText = startTimeText;
                    } else if (endTimeText) {
                        timeText = endTimeText;
                    }

                    // Render just the end time and the title
                    customEl.innerHTML = `
                        <div class='fc-event-time'>${timeText}</div>
                        <div class='fc-event-title-container'>
                            <div class='fc-event-title fc-sticky'>${titleText}</div>
                        </div>
                    `;

                    return { domNodes: [customEl] };
                },

                slotLabelFormat: {
                  hour: '2-digit',
                  minute: '2-digit',
                  hour12: true
                },

                eventTimeFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short',
                    omitZeroMinute: true
                },

                // Enable the live current time indicator line
                nowIndicator: @js(config('dot-env-calendar.show_now_indicator', true)),

                // Automatically focuses the viewport window layout directly onto the current hour axis
                scrollTime: @js(config('dot-env-calendar.auto_scroll_to_now', true))
                    ? new Date().toTimeString().split(' ')[0]
                    : '10:00:00',

                // Smooth Loading: This ensures the calendar doesn't collapse while waiting
                lazyFetching: @js(config('dot-env-calendar.editable', true)),
                startParam: 'start',
                endParam: 'end',

                headerToolbar: @js(config('dot-env-calendar.headerToolbar', [
                    'left' => 'prev,next today',
                    'center' => 'title',
                    'right' => 'dayGridMonth,timeGridWeek,listWeek',
                ])),

                editable: @js(config('dot-env-calendar.editable', true)),
                selectable: @js(config('dot-env-calendar.selectable', true)),

                eventResizableFromStart: @js(config('dot-env-calendar.eventResizableFromStart', false)),
                eventDurationEditable: @js(config('dot-env-calendar.eventDurationEditable', true)),

                events: (info, successCallback, failureCallback) => {
                    // Use $wire to get data, then pass it to the callback
                    $wire.loadEvents(info.startStr, info.endStr)
                        .then(events => {

                            // successCallback handles the transition smoothly
                            successCallback(events);

                            // Check if the developer enabled smart view switching in config
                            const enableSwitch = @js(config('dot-env-calendar.switch_view_if_event_today', false));

                            if (enableSwitch && Array.isArray(events)) {
                                // 1. Get today's local date string (YYYY-MM-DD)
                                const todayStr = new Date().toISOString().split('T')[0];

                                // 2. See if any loaded events land on today
                                const hasEventToday = events.some(event => {
                                    if (event && event.start) {
                                        const eventDate = event.start.split('T')[0];
                                        return eventDate === todayStr;
                                    }
                                    return false;
                                });

                                let targetView = @js(config('dot-env-calendar.initialView', 'dayGridMonth'));

                                // 3. Switch view safely using FullCalendar's internal api reference context
                                if (hasEventToday) {
                                    targetView = @js(config('dot-env-calendar.today_event_view', 'timeGridDay'));
                                }

                                // Use FullCalendar's internal execution context if 'this.calendar' isn't mutated yet
                                if (this.calendar) {
                                    this.calendar.changeView(targetView);
                                } else {
                                    // Fallback override for the initial view render array configuration
                                    info.view.calendar.changeView(targetView);
                                }
                            }

                        })
                        .catch(error => {
                            console.error('Calendar Load Error:', error);
                            failureCallback(error);
                        });
                },
                eventDidMount: function(info) {
                    if (info.event.extendedProps.isLocked) {
                        info.el.style.opacity = '0.6';
                        info.el.style.cursor = 'not-allowed';
                        const title = info.el.querySelector('.fc-event-title');
                        if (title) title.prepend('🔒 ');
                    }

                    tippy(info.el, {
                        content: info.event.extendedProps.description || info.event.title,
                        allowHTML: true,
                        placement: 'top',
                        theme: 'light',                                // Optional: adjust to your UI
                        interactive: true,
                        theme: 'light-border', // Example theme
                        animation: 'shift-toward',
                    });
                },

                dateClick: (info) => {
                    const date = new Date(info.dateStr);
                    $wire.createNewEvent(date.getFullYear(), date.getMonth() + 1, date.getDate());
                },

                eventClick: (info) => {
                    info.jsEvent.preventDefault();

                    if (info.event.url && info.event.url !== '' && info.event.url !== 'null') {
                        window.open(info.event.url, info.event.extendedProps._target);
                    } else {
                        $wire.onEventClick(info.event.id);
                    }
                },

                eventDrop: (info) => {
                    const event = info.event;
                    $wire.onEventChanged(
                        event.id,
                        event.start,
                        event.end ? event.end : null,
                    );
                },

                // 2. Added eventResize to handle extending/shrinking
                eventResize: (info) => {
                    const event = info.event;
                    if (event.end) {
                        $wire.onEventChanged(
                            event.id,
                            event.start,
                            event.end ? event.end : null,
                        );
                    }
                },
            });

            this.calendar.render();

            // Refresh calendar when filter changes
            window.addEventListener('dot-env-calendar:refreshed', () => {
                this.calendar.refetchEvents();
            });

            Livewire.hook('request.handled', () => {
                this.calendar.refetchEvents();
            });
        }
    }"
        wire:ignore
>
    @if($this->shouldRenderFilter())
        <div class="flex items-center justify-between mb-6 text-end">
            <div class="flex items-center gap-3">
                <label class="text-gray-400 text-xs font-bold uppercase tracking-wider">Filter By:</label>
                <div wire:ignore>
                    <select x-ref="filterSelect" class="form-select form-select-solid font-bold">
                        <option value="all">All Events</option>
                        @foreach(app(\DotEnv\Calendar\EventRegistry::class)->getModels() as $model)
                            @php $typeName = strtolower(class_basename($model)); @endphp
                            <option
                                    value="{{ $typeName }}" {{ $filter === $typeName ? 'selected' : '' }}>@lang(str($typeName)->title()->plural()->toString())</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endif

    <div x-ref="canvas" class="dot-env-calendar"></div>

</div>
