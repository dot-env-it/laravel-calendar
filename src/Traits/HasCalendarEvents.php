<?php

namespace DotEnv\Calendar\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Route;

trait HasCalendarEvents
{
    public static function bootHasCalendarEvents(): void
    {
        static::retrieved(function ($model) {
            $registry = app(\DotEnv\Calendar\EventRegistry::class);
            $registry->register([get_class($model)]);
        });
    }

    public function toCalendarEvent(): array
    {

        $map = property_exists($this, 'calendar_fillable') ? $this->calendar_fillable : [];
        $startField = $map['date'] ?? 'created_at';
        $dateValue = $this->getAttribute($startField);
        $date = $dateValue instanceof Carbon ? $dateValue : Carbon::parse($dateValue);

        // 1. Handle Auto-Time Logic
        if ($date instanceof Carbon && $date->format('H:i:s') === '00:00:00') {
            $time = config('dot-env-calendar.default_time', '10:30:00');
            [$hours, $minutes, $seconds] = explode(':', $time . ':00');
            $date = $date->copy()->setTime($hours, $minutes, $seconds);
        }

        // 2. Dynamic Color/Class Logic based on Date
        $style = $this->getDynamicCalendarStyle($date, $map);

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


        return [
            // Inside toCalendarEvent() in HasCalendarEvents.php
            'id' => class_basename($this) . '-' . $this->getKey(),
            'title' => ($map['prefix'] ?? '') . $this->getAttribute($map['title'] ?? 'title'),
            'start' => $date instanceof Carbon ? $date->toIso8601String() : $date,

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
        $dbColumn = $map['date_field'] ?? ($map['date'] ?? 'created_at');

        // Start with a base query to respect Global Scopes (like company_id)
        $query = static::query();

        // Check if the model has a custom authorization method
        if (method_exists(static::class, 'applyCalendarFilters')) {
            $query = static::applyCalendarFilters($query);
        }

        return $query->whereBetween($dbColumn, [$startDate, $endDate]);
    }
}
