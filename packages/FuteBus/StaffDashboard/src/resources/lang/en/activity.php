<?php

return [
    'title'     => 'Activity calendar',
    'today'     => 'Today',
    'previous'  => 'Previous month',
    'next'      => 'Next month',
    'open_day'  => 'View daily activity',
    'weekdays'  => ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
    'legend'    => ['issued' => 'Booked', 'cancelled' => 'Cancelled', 'pending' => 'To review'],
    'day_title' => 'Daily activity',
    'day_intro' => 'Company activity within your access permissions.',
    'counts'    => [
        'issued'          => 'Booked tickets issued', 'cancelled' => 'Tickets cancelled',
        'support_pending' => 'Open complaints / support', 'refund_pending' => 'Unfinished refunds',
    ],
    'basis'        => 'Tickets are counted by issue or cancellation date; exchange replacements are excluded from new bookings. Open requests were created on the selected day and remain unfinished. A dash indicates unavailable access.',
    'open_support' => 'Open support requests',
    'open_refunds' => 'Open refund management',
    'queue_title'  => 'Requests to follow up',
    'queue_kinds'  => ['support' => 'Complaint / support', 'refund' => 'Refund request'],
    'queue_empty'  => 'No accessible open requests are assigned to you or awaiting assignment.',
    'auto_refresh' => 'Updates every 15 seconds',
    'stale'        => 'Unable to refresh. Showing the most recent available data.',
    'retry'        => 'Calendar unavailable. Click to retry.',
];
