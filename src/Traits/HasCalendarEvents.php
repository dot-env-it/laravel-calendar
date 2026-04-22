<?php

namespace DotEnv\Calendar\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Route;

trait HasCalendarEvents
{
    public static function bootHasCalendarEvents(): void
    {
        app(\DotEnv\Calendar\EventRegistry::class)->register([static::class]);
    }

    public function toCalendarEvent(): array
    {

        $map = property_exists($this, 'calendar_fillable') ? $this->calendar_fillable : [];

        // 1. Handle Start Date
        $startField = $map['start_date'] ?? 'created_at'; // Changed from 'date'
        $startValue = $this->getAttribute($startField);
        $startDate = $startValue instanceof Carbon ? $startValue : Carbon::parse($startValue);

        // Auto-Time Logic for Start Date
        if ($startDate instanceof Carbon && $startDate->format('H:i:s') === '00:00:00') {
            $time = config('dot-env-calendar.default_time', '10:30:00');
            [$hours, $minutes, $seconds] = explode(':', $time . ':00');
            $startDate = $startDate->copy()->setTime($hours, $minutes, $seconds);
        }

        // 2. Handle End Date Logic
        $endDate = null;
        if (isset($map['end_date'])) {
            $endValue = $this->getAttribute($map['end_date']);
            if ($endValue) {
                $endDate = $endValue instanceof Carbon ? $endValue : Carbon::parse($endValue);

                // FullCalendar Fix: If end date has no time, it is exclusive.
                // We add 1 day so the event spans the full duration on the UI.
                if (!$endDate->isYesterday() && $endDate->format('H:i:s') === '00:00:00') {
                    $endDate = $endDate->copy()->addDay();
                }
            }
        }

        // 3. Dynamic Color/Class Logic based on Date
        $style = $this->getDynamicCalendarStyle($endDate ?? $startDate, $map);

        $isEditable = true;

        if (isset($map['editable'])) {
            $editableField = $map['editable'];
            // If it's a boolean, use it. If it's a string, check that attribute.
            $isEditable = is_bool($editableField) ? $editableField : (bool)$this->getAttribute($editableField);
        }

        // 1. Determine the ID to use for the URL
        // For Tasks, this will be 'matter_id'. For Matters, it defaults to 'id'.
        $urlId = isset($map['link_id'])
            ? $this->getAttribute($map['link_id'])
            : $this->getKey();


        // 3. Generate URL with the parent ID and any extra query params
        $params = $map['route_params'] ?? [];

        $url = isset($map['route']) && Route::has($map['route']) && $urlId
            ? route($map['route'], array_merge([$urlId], $params))
            : null;

        $title = ($map['prefix'] ?? '') . $this->getAttribute($map['title'] ?? 'title');
        $description = $this->getAttribute($map['description'] ?? 'description') ?? $title;

        return [
            // Inside toCalendarEvent() in HasCalendarEvents.php
            'id' => class_basename($this) . '-' . $this->getKey(),
            'title' => $title,
            'start' => $startDate->toIso8601String(),
            'end' => $endDate ? $endDate->toIso8601String() : null, // Pass to JS
            'allDay' => $endDate ? true : false,

            // Prioritize model-specific map, then dynamic logic
            'color' => $map['color'] ?? $style['color'],
            'className' => $map['class'] ?? $style['class'],
            'editable' => $isEditable,
            'startEditable' => $isEditable,
            'durationEditable' => $isEditable,
            'url' => $url,

            'extendedProps' => [
                'source' => strtolower(class_basename($this)),
                'isLocked' => !$isEditable,
                'description' => $description,
            ],
        ];
    }

    protected function getDynamicCalendarStyle(Carbon $date, array $map): array
    {
        $eventDate = $date->copy()->startOfDay();
        $colors = config('dot-env-calendar.colors');

        // Logic to determine the "key" based on time
        if ($eventDate->isPast() && !$eventDate->isToday()) {
            $status = 'past';
        } elseif ($eventDate->isToday()) {
            $status = 'today';
        } elseif ($eventDate->isTomorrow()) {
            $status = 'tomorrow';
        } else {
            $status = 'future';
        }

        // Return the config values for that status
        return [
            'class' => $colors[$status]['class'] ?? '',
            'color' => $colors[$status]['color'] ?? null,
        ];
    }

    /**
     * Get the query to fetch events with automated security filtering.
     */
    public static function getCalendarQuery($startDate, $endDate): Builder
    {
        $map = (new static)->calendar_fillable ?? [];

        // Updated parameter names
        $startColumn = $map['start_date_field'] ?? ($map['start_date'] ?? 'created_at');
        $endColumn = $map['end_date_field'] ?? ($map['end_date'] ?? null);

        $query = static::query();

        // Check for custom authorization logic in the model
        if (method_exists(static::class, 'applyCalendarFilters')) {
            $query = static::applyCalendarFilters($query);
        }

        // If an end_date_field exists, we must check for overlapping ranges
        if ($endColumn) {
            return $query->where(function ($q) use ($startColumn, $endColumn, $startDate, $endDate) {
                $q->whereBetween($startColumn, [$startDate, $endDate])
                    ->orWhereBetween($endColumn, [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startColumn, $endColumn, $startDate, $endDate) {
                        $sub->where($startColumn, '<=', $startDate)
                            ->where($endColumn, '>=', $endDate);
                    });
            });
        }

        // Default behavior for single-date events
        return $query->whereBetween($startColumn, [$startDate, $endDate]);
    }
}
