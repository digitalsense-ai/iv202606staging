<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default OCR profile
    |--------------------------------------------------------------------------
    |
    | These rules are intentionally conservative. Client-specific overrides can
    | be added under the `clients` key using the client_id as the array key.
    |
    */
    'default' => [
        'invoice_number_patterns' => [
            '/(?:invoice|inv|faktura|fakturanr|faktura nr|invoice no)[\s:#.-]*([A-Z0-9][A-Z0-9\/-]{2,})/iu',
            '/\b(?:INV|SI|CI)[-\s]?\d{3,}\b/iu',
        ],
        'date_patterns' => [
            '/\b\d{1,2}[\.\/-]\d{1,2}[\.\/-]\d{2,4}\b/u',
            '/\b\d{4}[\.\/-]\d{1,2}[\.\/-]\d{1,2}\b/u',
        ],
        'currency_patterns' => [
            '/\b(EUR|USD|GBP|DKK|SEK|NOK|CHF|CAD|AUD)\b/u',
        ],
        'amount_patterns' => [
            '/(?:total|amount|balance|subtotal|vat)[^\d]{0,20}([0-9]{1,3}(?:[.,\s][0-9]{3})*(?:[.,][0-9]{2})?)/iu',
        ],
        'critical_fields' => [
            'invoice_number',
            'date',
            'total',
            'currency',
        ],
        'review_threshold' => 90,
    ],

    'clients' => [
        // Example:
        // 123 => [
        //     'invoice_number_patterns' => [
        //         '/Customer specific invoice no[:\s]+([A-Z0-9-]+)/iu',
        //     ],
        //     'review_threshold' => 95,
        // ],
    ],
];