<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Events;

final readonly class RouteTimingChanged
{
    public function __construct(public int $routeId) {}
}
