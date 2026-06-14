<?php

namespace App\Contracts;

interface OcrInvoiceParser
{
    /**
     * Apply parser-specific cleanup, validation, and correction rules.
     *
     * @param array $normalized Current mapped invoice payload.
     * @param array $azureResult Raw Azure OCR result.
     * @param int|null $clientId Matched client id, when known.
     * @param string $invoiceType sales, com, or multi-invoices.
     * @return array Corrected normalized invoice payload.
     */
    public function parse(array $normalized, array $azureResult, ?int $clientId = null, string $invoiceType = 'sales'): array;
}