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
                        window.open(info.event.url, '_blank');
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
