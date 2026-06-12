<?php

namespace App\Services;

use App\Helpers\EuropeanNumberHelper;
use App\Helpers\DateHelper;

class ValidateOcrInvoiceDuplicateService
{
    public function generateHash(array $data, ?string $invoiceType = null): string
    {
        //$normalized = $this->normalizeByType($data);
        $normalized = $this->normalizeByType($data, $invoiceType);

        ksort($normalized);

        return hash(
            'sha256',
            json_encode(
                $normalized,
                JSON_UNESCAPED_UNICODE
            )
        );        
    }
   
    public function hasMinimumFingerprint(array $data, ?string $invoiceType = null): bool
    {
        $normalized = $this->normalizeByType($data, $invoiceType);

        return !empty($normalized['invoice_number'])
            && (!empty($normalized['net_amount']) || !empty($normalized['total_amount']));
    }

    private function resolveType(array $data, ?string $invoiceType = null): string
    {
        //$type = strtolower(trim($data['invoice_type'] ?? ''));
        $type = strtolower(trim($invoiceType ?: ($data['invoice_type'] ?? '')));

        return match (true) {

            str_contains($type, 'commercial') || $type === 'com'
                => 'com',

            str_contains($type, 'sales') || $type === 'sales' || $type === 'multi-invoices'
                => 'sales',

            default => 'generic',
        };
    }

    private function normalizeByType(array $data, ?string $invoiceType = null): array
    {
        return match ($this->resolveType($data, $invoiceType)) {

            'com' => $this->normalizeCom($data),

            'sales' => $this->normalizeSales($data),

            default => $this->normalizeGeneric($data),
        };
    }

    private function normalizeCom(array $data): array
    {
        return [
            // 'invoice_number' => $data['invoice_number'] ?? null,
            // 'invoice_date' => $data['invoice_date'] ?? null,
            // 'currency' => $data['currency'] ?? null,
            // 'net_amount' => $data['net_amount'] ?? null,
            // 'org_number' => $data['recipient']['org_number'] ?? null,
            // 'shipments' => $data['related_shipment_nos'] ?? null,
            // 'sales_invoices' => $data['related_sales_invoices'] ?? null,
            'invoice_number' => $this->normalizeIdentifier($data['invoice_number'] ?? null),
            'invoice_date' => $this->normalizeDate($data['invoice_date'] ?? null),
            'currency' => $this->normalizeIdentifier($data['currency'] ?? null),
            'net_amount' => $this->normalizeAmount($data['net_amount'] ?? null),
            'org_number' => $this->normalizeIdentifier($data['recipient']['org_number'] ?? null),
            //'shipments' => $this->normalizeList($data['related_shipment_nos'] ?? null),
            //'sales_invoices' => $this->normalizeList($data['related_sales_invoices'] ?? null),
        ];
    }

    private function normalizeSales(array $data): array
    {
        return [
            // 'invoice_number' => $data['invoice_number'] ?? null,
            // 'no_invoice_number' => $data['no_invoice_number'] ?? null,
            // 'invoice_date' => $data['invoice_date'] ?? null,
            // 'order_number' => $data['order_number'] ?? null,
            // 'currency' => $data['currency'] ?? null,
            // 'net_amount' => $data['net_amount'] ?? null,
            // 'vat_amount' => $data['vat_amount'] ?? null,
            // 'total_amount' => $data['total_amount'] ?? null,
            // 'org_number' => $data['supplier']['org_number'] ?? null,
            'invoice_number' => $this->normalizeIdentifier($data['invoice_number'] ?? null),
            'no_invoice_number' => $this->normalizeIdentifier($data['no_invoice_number'] ?? null),
            'invoice_date' => $this->normalizeDate($data['invoice_date'] ?? null),
            'order_number' => $this->normalizeIdentifier($data['order_number'] ?? null),
            'currency' => $this->normalizeIdentifier($data['currency'] ?? null),
            'net_amount' => $this->normalizeAmount($data['net_amount'] ?? null),
            'vat_amount' => $this->normalizeAmount($data['vat_amount'] ?? null),
            'total_amount' => $this->normalizeAmount($data['total_amount'] ?? null),
            //'org_number' => $this->normalizeIdentifier($data['supplier']['org_number'] ?? null),
            'org_number' => $this->normalizeIdentifier($data['supplier']['org_number'] ?? ($data['supplier']['cvr_number'] ?? null)),
        ];
    }

    private function normalizeGeneric(array $data): array
    {
        return [
            // 'invoice_number' => $data['invoice_number'] ?? null,
            // 'net_amount' => $data['net_amount'] ?? null,
            'invoice_number' => $this->normalizeIdentifier($data['invoice_number'] ?? null),
            'net_amount' => $this->normalizeAmount($data['net_amount'] ?? null),
            'total_amount' => $this->normalizeAmount($data['total_amount'] ?? null),
        ];
    }

     private function normalizeIdentifier(mixed $value): ?string
    {
        $value = $this->normalizeString($value);

        if ($value === null) {
            return null;
        }

        return preg_replace('/[^A-Z0-9]/', '', strtoupper($value)) ?: null;
    }

    private function normalizeString(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = implode(' ', array_filter($value, fn ($item) => !is_array($item)));
        }

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value === '' ? null : $value;
    }

    private function normalizeDate(mixed $value): ?string
    {
        $value = $this->normalizeString($value);

        if ($value === null) {
            return null;
        }

        try {
            return DateHelper::parseInvoiceDate($value) ?: $value;
        } catch (\Throwable $e) {
            return $value;
        }
    }

    private function normalizeAmount(mixed $value): ?string
    {
        $value = $this->normalizeString($value);

        if ($value === null) {
            return null;
        }

        $normalized = EuropeanNumberHelper::normalize($value);

        if ($normalized === null) {
            return preg_replace('/[^0-9.-]/', '', $value) ?: null;
        }

        return number_format(EuropeanNumberHelper::toFloat($normalized), 2, '.', '');
    }

    private function normalizeList(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (!is_array($value)) {
            $value = preg_split('/[,;\s]+/', (string) $value) ?: [];
        }

        $normalized = array_values(array_unique(array_filter(array_map(
            fn ($item) => $this->normalizeIdentifier($item),
            $value
        ))));

        sort($normalized);

        return $normalized;
    }
}