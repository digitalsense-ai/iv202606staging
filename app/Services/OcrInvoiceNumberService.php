<?php

namespace App\Services;

class OcrInvoiceNumberService
{
    public function ruleNotes(?string $clientName): array
    {
        //if ($clientName === null) {
        if ($clientName === null || trim($clientName) === '') {
            return [];
        }

        $clientName = strtolower($clientName);
        $notes = [];

        if (str_contains($clientName, 'dfi-geisler')) {
            $notes[] = 'Invoice number: when no invoice number is captured, the invoice date is used in YYYYMMDD format.';
        }

        if (str_contains($clientName, 'rainwear')) {
            //$notes[] = 'Commercial invoices use File Name in place of invoice number.';

            $notes[] = 'Invoice number: commercial invoices use the SF-number from the file name.';
            // $notes[] = 'Invoice number: when OCR returns two lines, the first is treated as NO Invoice Number and the second as Invoice Number.';
        }

        if (
            str_contains($clientName, 'rainwear')
            || str_contains($clientName, 'engel')
            || str_contains($clientName, 'berendsohn')
        ) {
            //$notes[] = 'Sales invoices use NO Invoice Number in place of invoice number.';
            $notes[] = 'Invoice number: sales invoices use NO Invoice Number in place of Invoice Number.';
        }

        if (str_contains($clientName, 'stof')) {
            $notes[] = 'Invoice number: all hyphens are removed from sales invoice numbers.';
            $notes[] = 'Commercial references: related sales invoices are restricted to unique 10-digit numbers beginning with 20, collected from both the captured field and OCR text.';
        }

        if (str_contains($clientName, 'engel')) {
            $notes[] = 'Invoice number: the text ".. ff" is removed from commercial invoice numbers.';
            $notes[] = 'Exchange amounts: a missing exchange VAT amount/currency is recovered from OCR text; "0 NOK" is interpreted as NOK 0.';
        }

        if (str_contains($clientName, 'adag')) {
            $notes[] = 'Additional charges: multiple newline-separated numeric charges are added together before normalization and calculation.';
            $notes[] = 'Net calculation: |Net Amount| + |Additional Charges| + |Variance| - |Discount Amount|.';
        }

        if (str_contains($clientName, 'sgi wholesale') || str_contains($clientName, 'sand cph')) {
            $notes[] = 'Amounts: the captured Variance and Discount Amount values are swapped, so the captured variance is used as the discount amount.';
            $notes[] = 'Net calculation after the swap: |Net Amount| + |Additional Charges| + |Variance| - |Discount Amount|.';
        }

        if (str_contains($clientName, 'horn bord')) {
            //$notes[] = 'Non-credit invoices use Order Number in place of invoice number.';
            $notes[] = 'Invoice number: non-credit invoices use Order Number in place of Invoice Number.';
            $notes[] = 'Credit note: an invoice number beginning with KRE- marks the document as a credit note.';
        }

        if (str_contains($clientName, 'vernon')) {
            $notes[] = 'Invoice type: EX-prefixed invoice numbers and VAT-base text are used to distinguish sales invoices from commercial invoices.';
        }

        if (str_contains($clientName, 'committee xxiv')) {
            $notes[] = 'Invoice type: VAT amount/rate text is used to distinguish sales invoices from commercial invoices.';
        }

        if (str_contains($clientName, 'samsoe samsoe') || str_contains($clientName, 'samsø samsø')) {
            $notes[] = 'Invoice type: the presence of Net Amount or VAT Amount is used to distinguish commercial invoices from sales invoices.';
        }

        $referenceParserClients = [
            'aubo',
            'berendsohn',
            'berg toys',
            'bianco',
            'dan form',
            'dan-form',
            'kite',
            'our units',
            'rexholm',
            'rieker',
            'sebra',
            'secondfemale',
            'second female',
            'sports group',
            'villy',
        ];

        foreach ($referenceParserClients as $referenceParserClient) {
            if (str_contains($clientName, $referenceParserClient)) {
                $notes[] = 'Commercial references: client-specific rules extract and classify related sales invoices, sales orders, and shipment numbers from captured fields and OCR text.';
                break;
            }
        }

        //return $notes;
        return array_values(array_unique($notes));
    }
    
    public function apply(array $data, ?string $clientName, ?string $fileName, ?string $invoiceType): array
    {
        $data = $this->preserveOriginal($data);

        if ($this->supports($clientName, $invoiceType) && empty($data['special_capture_invoice_number'])) {
            $invoiceNumber = $this->fromFileName($fileName);

            if ($invoiceNumber !== null) {
                $data['special_capture_invoice_number'] = $invoiceNumber;
            }
        }

        $effectiveInvoiceNumber = $this->effectiveInvoiceNumber($data, $clientName, $invoiceType);

        if ($effectiveInvoiceNumber !== null) {
            $data['effective_invoice_number'] = $effectiveInvoiceNumber;
        }

        return $data;
    }

    public function preserveOriginal(array $data): array
    {
        if (!array_key_exists('original_invoice_number', $data) && !empty($data['invoice_number'])) {
            $data['original_invoice_number'] = $data['invoice_number'];
        }

        return $data;
    }

    public function withSubmittedNumber(array $data, ?string $clientName, mixed $invoiceNumber): array
    {
        $data = $this->preserveOriginal($data);

        if ($this->isSpecialClient($clientName) && $this->normalize($invoiceNumber) !== null) {
            $data['effective_invoice_number'] = $this->normalize($invoiceNumber);
        }

        return $data;
    }

    public function supports(?string $clientName, ?string $invoiceType): bool
    {
        return $invoiceType === 'com'
            && $clientName !== null
            && str_contains(strtolower($clientName), 'rainwear');
    }

    public function fromFileName(?string $fileName): ?string
    {
        if (!$fileName || !preg_match('/(SF-\d+)/i', $fileName, $matches)) {
            return null;
        }

        return strtoupper($matches[1]);
    }

    private function effectiveInvoiceNumber(array $data, ?string $clientName, ?string $invoiceType): ?string
    {
        if (!$this->isSpecialClient($clientName)) {
            return null;
        }

        $clientName = strtolower((string) $clientName);

        if (str_contains($clientName, 'rainwear') && $invoiceType === 'com') {
            return $this->normalize($data['special_capture_invoice_number'] ?? null);
        }

        if (str_contains($clientName, 'horn bord') && empty($data['credit_note'])) {
            return $this->normalize($data['order_number'] ?? $data['invoice_number'] ?? null);
        }

        if ($invoiceType !== 'com' && (
            str_contains($clientName, 'rainwear')
            || str_contains($clientName, 'engel')
            || str_contains($clientName, 'berendsohn')
        )) {
            return $this->normalize($data['no_invoice_number'] ?? $data['invoice_number'] ?? null);
        }

        $invoiceNumber = $this->normalize($data['invoice_number'] ?? null);

        if ($invoiceNumber !== null && str_contains($clientName, 'engel')) {
            return preg_replace('/\s*\.\.\s*ff\s*/i', '', $invoiceNumber) ?: null;
        }

        return $invoiceNumber;
    }

    private function isSpecialClient(?string $clientName): bool
    {
        if ($clientName === null) {
            return false;
        }

        $clientName = strtolower($clientName);

        return str_contains($clientName, 'rainwear')
            || str_contains($clientName, 'engel')
            || str_contains($clientName, 'berendsohn')
            || str_contains($clientName, 'horn bord');
    }

    private function normalize(mixed $invoiceNumber): ?string
    {
        if ($invoiceNumber === null || is_array($invoiceNumber)) {
            return null;
        }

        $invoiceNumber = ltrim(trim((string) $invoiceNumber), '#');

        return $invoiceNumber === '' ? null : $invoiceNumber;
    }
}