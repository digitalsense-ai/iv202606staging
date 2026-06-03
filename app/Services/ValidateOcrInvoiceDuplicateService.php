<?php

namespace App\Services;

class ValidateOcrInvoiceDuplicateService
{
    public function generateHash(array $data): string
    {
        $normalized = $this->normalizeByType($data);

        return hash(
            'sha256',
            json_encode(
                $normalized,
                JSON_UNESCAPED_UNICODE
            )
        );        
    }
   
    private function resolveType(array $data): string
    {
        $type = strtolower(trim($data['invoice_type'] ?? ''));

        return match (true) {

            str_contains($type, 'commercial') || $type === 'com'
                => 'com',

            str_contains($type, 'sales') || $type === 'sales' || $type === 'multi-invoices'
                => 'sales',

            default => 'generic',
        };
    }

    private function normalizeByType(array $data): array
    {
        return match ($this->resolveType($data)) {

            'com' => $this->normalizeCom($data),

            'sales' => $this->normalizeSales($data),

            default => $this->normalizeGeneric($data),
        };
    }

    private function normalizeCom(array $data): array
    {
        return [
            'invoice_number' => $data['invoice_number'] ?? null,
            'invoice_date' => $data['invoice_date'] ?? null,
            'currency' => $data['currency'] ?? null,
            'net_amount' => $data['net_amount'] ?? null,
            'org_number' => $data['recipient']['org_number'] ?? null,
            'shipments' => $data['related_shipment_nos'] ?? null,
            'sales_invoices' => $data['related_sales_invoices'] ?? null,
        ];
    }

    private function normalizeSales(array $data): array
    {
        return [
            'invoice_number' => $data['invoice_number'] ?? null,
            'no_invoice_number' => $data['no_invoice_number'] ?? null,
            'invoice_date' => $data['invoice_date'] ?? null,
            'order_number' => $data['order_number'] ?? null,
            'currency' => $data['currency'] ?? null,
            'net_amount' => $data['net_amount'] ?? null,
            'vat_amount' => $data['vat_amount'] ?? null,
            'total_amount' => $data['total_amount'] ?? null,
            'org_number' => $data['supplier']['org_number'] ?? null,
        ];
    }

    private function normalizeGeneric(array $data): array
    {
        return [
            'invoice_number' => $data['invoice_number'] ?? null,
            'net_amount' => $data['net_amount'] ?? null,
        ];
    }
}