<?php

declare(strict_types=1);

namespace DotEnv\Calendar;

use Illuminate\Support\Facades\Cache;

class EventRegistry
{
    protected string $cacheKey = 'dot_env_calendar::discovered_models';

    public function register(array $models): void
    {
        $existing = Cache::get($this->cacheKey, []);
        $updated  = array_unique(array_merge($existing, $models));

        Cache::forever($this->cacheKey, $updated);
    }

    public function getModels()
    {
        // Now it returns everything found across all past requests
        return Cache::get($this->cacheKey, []);
    }
}
