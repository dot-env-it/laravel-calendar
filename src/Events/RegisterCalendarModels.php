<?php

declare(strict_types=1);

namespace DotEnv\Calendar\Events;

use DotEnv\Calendar\EventRegistry;

final class RegisterCalendarModels
{
    public EventRegistry $registry;

    public function __construct(EventRegistry $registry)
    {
        $this->registry = $registry;
    }
}
