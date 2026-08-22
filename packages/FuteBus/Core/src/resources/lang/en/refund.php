<?php

return [
    'meta' => [
        'title' => 'Ticket Change, Cancellation and Refund Policy - FUTA Bus Lines',
        'description' => 'Rules for changing, cancelling and refunding FUTA Bus Lines tickets.',
    ],
    'brand' => 'FUTA Bus Lines',
    'heading' => 'Ticket Change, Cancellation and Refund Policy',
    'transaction_errors' => [
        'title' => 'Article 1. Refunds for failed online ticket transactions',
        'introduction' => 'Online ticket payments may be refunded in the following transaction-error cases:',
        'items' => [
            'The online purchase fails and no booking code is issued, but the customer’s account is charged;',
            'Some older ATM cards support transfers but not online payment. When banks cannot confirm a transaction '
                . 'during weekends or public holidays, customers may need to pay at the counter to collect the ticket; '
                . 'the charged amount will be verified and refunded after reconciliation.',
        ],
    ],
    'processing' => [
        'title' => 'Article 2. Refund processing time',
        'channels' => [
            ['name' => 'Call center', 'time' => 'Three to five business days after Finance and Accounting receives complete payment evidence.'],
            ['name' => 'Ticket counter', 'time' => 'Processed directly and refunded when the counter transaction is handled.'],
            ['name' => 'Application', 'time' => 'Subject to the policy and processing time of the relevant application provider.'],
        ],
    ],
    'changes' => [
        'title' => 'Article 3. Ticket change or cancellation rules',
        'items' => [
            'Each ticket may be changed no more than once.',
            'Cancellation fees range from 10% to 30% of the fare depending on the time remaining before departure, '
                . 'the number of individual or group tickets and the rules in effect when the request is made.',
            'Customers requesting a change or cancellation for a paid ticket must contact the 1900 6067 hotline '
                . 'or a ticket office at least 24 hours before departure for assistance.',
        ],
    ],
];
