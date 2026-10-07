<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Events;

class TripCancelled
{
    public function __construct(public int $tripId, public int $ticketCount) {}
}