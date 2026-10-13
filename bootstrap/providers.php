<?php

use App\Providers\AppServiceProvider;
use Staff\Dashboard\Providers\StaffDashboardServiceProvider;
use Staff\TicketProcessing\Providers\TicketProcessingServiceProvider;

return [
    AppServiceProvider::class,
    StaffDashboardServiceProvider::class,
    TicketProcessingServiceProvider::class,
];
