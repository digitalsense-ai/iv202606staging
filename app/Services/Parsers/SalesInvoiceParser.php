<?php

namespace App\Services\Parsers;

class SalesInvoiceParser extends BaseInvoiceParser
{
    public function parse(array $normalized, array $azureResult, ?int $clientId = null, string $invoiceType = 'sales'): array
    {
        $normalized = parent::parse($normalized, $azureResult, $clientId, $invoiceType);

        if (isset($normalized['error'])) {
            return $normalized;
        }

        return $this->applySalesSpecificRules($normalized);
    }

    private function applySalesSpecificRules(array $data): array
    {
        if (!empty($data['invoice_number']) && is_string($data['invoice_number'])) {
            $data['invoice_number'] = strtoupper(trim($data['invoice_number']));
        }

        if (!empty($data['related_sales_invoices']) && is_array($data['related_sales_invoices'])) {
            $data['related_sales_invoices'] = array_values(array_unique(array_filter(array_map(
                fn ($value) => is_string($value) ? strtoupper(trim($value)) : $value,
                $data['related_sales_invoices']
            ))));
        }

        return $data;
    }
}
