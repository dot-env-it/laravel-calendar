<?php

namespace DotEnv\Calendar;

use DotEnv\Calendar\Http\Livewire\Calendar;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CalendarServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Allow users to publish the config
        $this->publishes([
            __DIR__.'/../config/dot-env-calendar.php' => config_path('dot-env-calendar.php'),
        ], 'dot-env-calendar-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'dot-env-calendar');
        Livewire::component('dot-env-calendar', Calendar::class);
    }

    public function register(): void
    {
        // Merge config so the package works even if not published
        $this->mergeConfigFrom(__DIR__.'/../config/dot-env-calendar.php', 'dot-env-calendar');

        $this->app->singleton(EventRegistry::class, fn () => new EventRegistry);
    }
}
