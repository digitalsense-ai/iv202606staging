<?php

namespace App\Services;

class OcrInvoiceNumberService
{
    public function ruleNotes(?string $clientName): array
    {
        if ($clientName === null) {
            return [];
        }

        $clientName = strtolower($clientName);
        $notes = [];

        if (str_contains($clientName, 'rainwear')) {
            $notes[] = 'Commercial invoices use File Name in place of invoice number.';
        }

        if (
            str_contains($clientName, 'rainwear')
            || str_contains($clientName, 'engel')
            || str_contains($clientName, 'berendsohn')
        ) {
            $notes[] = 'Sales invoices use NO Invoice Number in place of invoice number.';
        }

        if (str_contains($clientName, 'horn bord')) {
            $notes[] = 'Non-credit invoices use Order Number in place of invoice number.';
        }

        return $notes;
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