<?php

return [
    'pdf_processing' => [
        // Smalot loads the complete PDF object graph into PHP memory. Keep it
        // for small, text-based PDFs only; larger files go through one Azure
        // batch-read request and cannot exhaust the worker's memory.
        'local_text_max_pages' => (int) env('OCR_LOCAL_TEXT_MAX_PAGES', 25),

        // Ghostscript linearizes unusually large split files so browser PDF
        // viewers can render the first page before downloading the whole blob.
        'optimize_split_files' => env('OCR_OPTIMIZE_SPLIT_FILES', true),
        'optimize_split_min_bytes' => (int) env('OCR_OPTIMIZE_SPLIT_MIN_BYTES', 1048576),
        'ghostscript_binary' => env('GHOSTSCRIPT_BINARY'),
    ],
    
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