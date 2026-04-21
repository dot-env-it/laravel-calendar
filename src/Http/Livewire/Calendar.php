<?php

namespace DotEnv\Calendar\Http\Livewire;

use Carbon\Carbon;
use DotEnv\Calendar\EventRegistry;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Calendar extends Component
{
    public array $events = [];
    public string $dayClickMethod = 'createNewEvent';
    public string $eventClickMethod = 'onEventClick';
    public string $onEventDroppedMethod = 'onEventDropped';

    public $filter = 'all'; // Default filter

    // Tell Livewire to listen for this property change to refresh the calendar
    protected $queryString = ['filter'];

    public bool $showFilter;

    public function mount(?bool $showFilter = null): void
    {
        $this->showFilter = $showFilter ?? config('dot-env-calendar.enable_filter', true);

        $this->loadEvents();
    }

    /**
     * Determine if the UI should actually render the filter dropdown.
     */
    public function shouldRenderFilter(): bool
    {
        if (!$this->showFilter) {
            return false;
        }

        $registeredModels = app(\DotEnv\Calendar\EventRegistry::class)->getModels();

        // Only show if there's more than one model to toggle between
        return count($registeredModels) > 1;
    }

    // This runs automatically when $filter changes
    public function updatedFilter(): void
    {
        $this->loadEvents();
        $this->dispatch('dot-env-calendar:refreshed');
    }

    /**
     * This method is now called via Alpine.js with dates,
     * or via the 'dot-env-calendar:refresh' event.
     */
    #[On('dot-env-calendar:refresh')]
    public function loadEvents($start = null, $end = null): array
    {
//        dump($this->filter);

        // Parse dates or default to the current year
        $startDate = $start ? Carbon::parse($start) : now()->startOfYear();
        $endDate = $end ? Carbon::parse($end) : now()->endOfYear();

        $this->events = collect(app(EventRegistry::class)->getModels())
            ->filter(function ($modelClass) {
                // FILTER LOGIC: If filter isn't 'all', only keep the matching class
                if ($this->filter === 'all') return true;
                return strtolower(class_basename($modelClass)) === strtolower($this->filter);
            })
            ->flatMap(fn($model) => $model::getCalendarQuery($startDate, $endDate)
                ->get()
                ->map->toCalendarEvent())
            ->toArray();

        // If this was a manual refresh (not from FullCalendar's internal fetcher), dispatch
        if (!$start) {
            $this->dispatch('dot-env-calendar:refreshed', events: $this->events);
        }

        return $this->events;
    }

    public function render(): Factory|View
    {
        return view('dot-env-calendar::livewire.calendar');
    }

    public function createNewEvent($year, $month, $day)
    {
        $date = Carbon::create($year, $month, $day)->format('Y-m-d');

        // Updated dispatch name
        $this->dispatch('dot-env-calendar:create-new-event', date: $date);
    }

    public function onEventClick($eventId): void
    {
        [$model, $id] = explode('-', $eventId);

        // The $eventId comes from the 'id' key in your toCalendarEvent map
        $this->dispatch('dot-env-calendar:view-event-details', id: $id, model: $model);
    }

    public function onEventDropped($eventId, $year, $month, $day): void
    {
        [$modelName, $id] = explode('-', $eventId);
        $newDate = Carbon::create($year, $month, $day);

        $modelClass = collect(app(EventRegistry::class)->getModels())
            ->first(fn($m) => class_basename($m) === $modelName);

        if ($modelClass) {

            $record = $modelClass::find($id);

            $map = $record->calendarMap ?? [];

            $canMove = true;

            if (isset($map['editable'])) {
                $canMove = is_bool($map['editable'])
                    ? $map['editable']
                    : (bool)$record->getAttribute($map['editable']);
            }

            if (!$canMove) {
                $this->dispatch('swal', message: 'This item is locked!', type: 'error');
                return;
            }

            $updateData = [
                ($map['date'] ?? 'created_at') => $newDate->format('Y-m-d'),
            ];

            $record->update($updateData);

            $this->loadEvents();


            // Updated dispatch name for consistency
            $this->dispatch('dot-env-calendar:event-updated',
                message: __($modelName) . __(' rescheduled to ') . $newDate->format('M d, Y'),
                type: 'success'
            );
        }
    }
}
