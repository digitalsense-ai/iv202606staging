<?php

namespace App\Parsers;

class DefaultInvoiceParser implements ClientInvoiceParserInterface
{
    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        return true; // fallback always matches
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {
        $doc = $doc ?: ($result['analyzeResult']['documents'][0]['fields'] ?? []);

        return [
            'invoice_type' => $doc['Invoice Type']['valueString'] ?? null,

            'related_sales_invoices' =>
                $doc['Related Sales Invoices']['valueString'] ?? null,

            'related_sales_orders' =>
                $doc['Related Sales Orders']['valueString'] ?? null,

            'related_shipment_nos' =>
                $doc['Related Shipment Numbers']['valueString'] ?? null,
        ];
    }
}