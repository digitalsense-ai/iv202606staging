<?php

namespace App\Parsers;

use App\Parsers\Concerns\ExtractsCommercialReferences;

class KiteInvoiceParser implements ClientInvoiceParserInterface
{
    use ExtractsCommercialReferences;

    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'kite')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'kite');
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {
        $azurePayload = [
            'related_sales_invoices' => $doc['Related Sales Invoices']['valueString'] ?? null,
            'related_sales_orders' => $doc['Related Sales Orders']['valueString'] ?? null,
            'related_shipment_nos' => $doc['Related Shipment Numbers']['valueString'] ?? null,
        ];

        $rows = $this->extractColumnAfterHeader(
            $result,
            "Order Number\nInvoice Number\nTracking Number",
            3,
            [
                0 => 'sales_order',
                1 => 'sales_invoice',
                2 => 'shipment',
            ]
        );

        $tablePayload = [
            'related_sales_invoices' => $this->joinReferences(
                $this->normalizeReferenceList(array_column($rows, 'sales_invoice'))
            ),
            'related_sales_orders' => $this->joinReferences(
                $this->normalizeReferenceList(array_column($rows, 'sales_order'))
            ),
            'related_shipment_nos' => $this->joinReferences(
                $this->normalizeReferenceList(array_column($rows, 'shipment'))
            ),
        ];

        return $this->mergeReferencePayload($azurePayload, $tablePayload);
    }
}
