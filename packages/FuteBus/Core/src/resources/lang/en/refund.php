<?php

return [
    'meta' => [
        'title'       => 'Ticket Change, Cancellation and Refund Policy - FUTA Bus Lines',
        'description' => 'Rules for changing, cancelling and refunding FUTA Bus Lines tickets.',
    ],
    'brand'              => 'FUTA Bus Lines',
    'heading'            => 'Ticket Change, Cancellation and Refund Policy',
    'transaction_errors' => [
        'title'        => 'Article 1. Refunds for failed online ticket transactions',
        'introduction' => 'Online ticket payments may be refunded in the following transaction-error cases:',
        'items'        => [
            'The online purchase fails and no booking code is issued, but the customer’s account is charged;',
            'Some older ATM cards support transfers but not online payment. When banks cannot confirm a transaction '
                .'during weekends or public holidays, customers may need to pay at the counter to collect the ticket; '
                .'the charged amount will be verified and refunded after reconciliation.',
        ],
    ],
    'processing' => [
        'title'    => 'Article 2. Refund processing time',
        'channels' => [
            ['name' => 'Website', 'time' => 'Track requests in Purchase history. Staff reconcile and record refunds after the bank confirms a successful transfer back.'],
            ['name' => 'Call center / ticket counter', 'time' => 'Contact 1900 6067 or a ticket office for review; processing time depends on reconciliation and the bank.'],
            ['name' => 'Other channels', 'time' => 'Contact the original sales channel for assistance under its policy.'],
        ],
    ],
    'changes' => [
        'title' => 'Article 3. Ticket change or cancellation rules',
        'items' => (require __DIR__.'/cancellation-policy.php')['items'],
    ],
];
