<?php

/*
 | Permission keys supported by the application. Add a key here only after
 | protecting the matching route/action with CheckPermission or hasPermissionTo.
 | The database catalog may only register keys declared in this code manifest.
 */
return [
    'bus_company' => ['view', 'create', 'update', 'delete'],
    'bus' => ['view', 'create', 'update', 'delete'],
    'route' => ['view', 'create', 'update', 'delete'],
    'trip' => ['view', 'create', 'update', 'delete', 'cancel'],
    'seat_layout' => ['view', 'create', 'update', 'delete'],
    'booking' => ['view', 'create', 'update', 'cancel'],
    'payment' => ['view', 'create', 'refund'],
    'ticket' => ['view', 'create', 'verify'],
    'customer' => ['view', 'create', 'update', 'delete'],
    'report' => ['view'],
    'user' => ['view', 'create', 'update', 'delete'],
    'setting' => ['view', 'update'],
    'role' => ['view', 'create', 'update', 'delete'],
    'dashboard' => ['view'],
];
