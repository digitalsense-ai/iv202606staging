<?php

return [
    'defaults' => [
        'patterns' => [
            'invoice_number' => [],
            'invoice_date' => [],
            'currency' => [],
        ],
        'replacements' => [
            'invoice_number' => [
                'O' => '0',
            ],
        ],
    ],

    'clients' => [
        // client_id => profile
    ],
];
