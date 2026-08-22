<?php

return [
    'meta' => [
        'title' => 'Online Booking Terms - FUTA Bus Lines',
        'description' => 'FUTA Bus Lines online booking and carriage terms and conditions.',
    ],
    'brand' => 'FUTA Bus Lines',
    'heading' => 'Online Booking Terms',
    'chapters' => [
        [
            'title' => 'I. General terms and conditions',
            'articles' => [
                [
                    'title' => 'Article 1. Definitions and abbreviations',
                    'items' => [
                        ['label' => '“Fees”:', 'text' => 'fees and taxes imposed by the carrier and competent authorities;'],
                        ['label' => '“Coach station”:', 'text' => 'road-transport infrastructure used for passenger pick-up, drop-off and supporting services;'],
                        ['label' => '“We”:', 'text' => 'Phuong Trang Passenger Transport Joint Stock Company – FUTA Bus Lines;'],
                        ['label' => '“Pick-up/drop-off point”:', 'text' => 'the scheduled origin and destination of a passenger journey;'],
                        ['label' => '“Force majeure”:', 'text' => 'events beyond reasonable control, including natural disasters, fire, war, unrest, strikes or government restrictions;'],
                        ['label' => '“Conditions of carriage”:', 'text' => 'transport requirements published by FUTA on tickets, its website, application or other approved channels;'],
                        ['label' => '“Passenger”:', 'text' => 'any individual using a FUTA service;'],
                        ['label' => '“Baggage”:', 'text' => 'personal belongings and property carried by a passenger during a journey;'],
                        ['label' => '“Contract of carriage”:', 'text' => 'the service agreement between the carrier and passenger, evidenced by a ticket or equivalent record;'],
                        ['label' => '“Electronic invoice”:', 'text' => 'an invoice issued electronically in accordance with accounting and tax law;'],
                        ['label' => '“Lookup code”:', 'text' => 'a nine-character code used to retrieve an electronic invoice after payment;'],
                        ['label' => '“Booking code”:', 'text' => 'reservation details entered through the FUTA website, app, ticket office, agent or call centre;'],
                        ['label' => '“Day”:', 'text' => 'a calendar day, including every day of the week;'],
                        ['label' => '“Personal data”:', 'text' => 'passenger data FUTA may retain, use and share with relevant service providers to deliver the service;'],
                        ['label' => '“Boarding pass (ticket)”:', 'text' => 'evidence of the contract of carriage valid for the named passenger and journey;'],
                        ['label' => '“ZNS”:', 'text' => 'Zalo Notification Service for customer-care messages;'],
                        ['text' => 'Headings are for convenient reference only and do not alter the interpretation of these terms.'],
                    ],
                ],
                [
                    'title' => 'Article 2. Online booking rules',
                    'items' => [
                        ['label' => 'Scope:', 'text' => 'online payment is available for selected services and may be used by members or guests without charge.'],
                        ['label' => 'Reservation:', 'text' => 'customers must verify the journey, departure time, seat and fare before payment.', 'details' => [
                            'A reservation is confirmed only after payment and issuance of a FUTA booking code.',
                            'FUTA is not responsible for bookings made through unauthorized parties.',
                        ]],
                        ['label' => 'Payment confirmation:', 'text' => 'ticket details are sent by email, SMS or ZNS.', 'details' => [
                            'Customers are responsible for providing accurate contact and personal information.',
                            'Call 1900 6067 if no confirmation is received within 30 minutes after payment.',
                        ]],
                        ['label' => 'Transaction security:', 'text' => 'FUTA and its payment partners use reasonable fraud-detection, transaction-control and data-security measures.'],
                        ['label' => 'Personal data:', 'text' => 'transaction data is used for support and service updates under the Privacy Policy and is disclosed only with consent or as legally required.'],
                        ['label' => 'Refunds, cancellations and changes:', 'text' => 'failed transactions are handled through the relevant payment channel; refunds normally take three to five business days.', 'details' => [
                            'A ticket may be changed once; cancellation fees range from 10% to 30%.',
                            'Change or cancellation requests must be made at least 24 hours before departure.',
                        ]],
                        ['label' => 'Sales channels:', 'text' => 'buy through the FUTA website, app, official offices or 1900 6067. FUTA may refuse service for fraud, speculation or ticket reselling.'],
                        ['label' => 'Transfer service:', 'text' => 'contact 1900 6067 before booking; transfer vehicles cannot serve inaccessible locations.'],
                    ],
                ],
                [
                    'title' => 'Article 3. Conditions of carriage',
                    'items' => [
                        ['label' => 'Children and pregnant passengers:', 'text' => 'children under six, no taller than 1.3 m and under 30 kg travel without a ticket; pregnant passengers must be fit to travel.'],
                        ['label' => 'Baggage:', 'text' => 'total baggage must not exceed 20 kg; call 1900 6067 regarding oversized items.'],
                        ['label' => 'Boarding requirements:', 'text' => 'arrive 30 minutes before departure, or 60 minutes during holidays, and present valid ticket details.', 'details' => [
                            'Do not carry strong-smelling food, flammable materials or animals.',
                            'Smoking, alcohol, intoxicants and littering are prohibited on board.',
                        ]],
                    ],
                ],
            ],
        ],
    ],
    'contact' => [
        'prefix' => 'FUTA Bus Lines contact information: If you have questions or requests concerning this Privacy '
            . 'Policy, please contact us at:',
        'or' => 'or call',
    ],
    'quality' => [
        'title' => 'FUTA - Quality is Honor',
        'stats' => [
            ['value' => 'Over 200M', 'label' => 'Over 200 million passengers', 'description' => 'Phuong Trang serves over 200 million passenger journeys nationwide each year', 'image' => 'images/service-quality/passengers.png', 'image_alt' => 'FUTA passengers'],
            ['value' => 'Over 350', 'label' => 'Over 350 offices and branches', 'description' => 'More than 350 ticket offices, transfer stations and terminals across the network', 'image' => 'images/service-quality/ticket-offices.png', 'image_alt' => 'FUTA ticket office'],
            ['value' => 'Over 1,000', 'label' => 'Over 1,000 daily trips', 'description' => 'More than 1,000 intercity and long-distance coach trips every day', 'image' => 'images/service-quality/daily-trips.png', 'image_alt' => 'FUTA coach'],
        ],
    ],
];
