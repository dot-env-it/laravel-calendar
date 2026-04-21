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
                initialView: 'dayGridMonth',

                // BUSINESS HOURS
                businessHours: @js(config('dot-env-calendar.businessHours')),
                weekends: !@js(config('dot-env-calendar.hideWeekends')),

                // Smooth Loading: This ensures the calendar doesn't collapse while waiting
                lazyFetching: true,
                startParam: 'start',
                endParam: 'end',

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
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                editable: true,
                selectable: true,


                eventDidMount: function(info) {
                    if (info.event.extendedProps.isLocked) {
                        info.el.style.opacity = '0.6';
                        info.el.style.cursor = 'not-allowed';
                        const title = info.el.querySelector('.fc-event-title');
                        if (title) title.prepend('🔒 ');
                    }
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
                    const eventId = info.event.id;
                    const newDate = info.event.start;
                    $wire.onEventDropped(
                        eventId,
                        newDate.getFullYear(),
                        newDate.getMonth() + 1,
                        newDate.getDate()
                    );
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
