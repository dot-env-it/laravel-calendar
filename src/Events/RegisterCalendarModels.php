<?php

namespace DotEnv\Calendar\Events;

use DotEnv\Calendar\EventRegistry;

class RegisterCalendarModels
{
    public EventRegistry $registry;

    public function __construct(EventRegistry $registry)
    {
        $this->registry = $registry;
    }
}
