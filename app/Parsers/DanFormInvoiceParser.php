<?php

namespace App\Parsers;

class DanFormInvoiceParser implements ClientInvoiceParserInterface
{
    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'dan-form') || str_contains($name, 'dan form')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'dan-form') || str_contains($content, 'dan form');
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {
        $salesOrders = $this->extractReferences($doc['Related Sales Orders']['valueString'] ?? $doc['Related Sales Orders']['content'] ?? null);
        $salesInvoices = $this->extractReferences($doc['Related Sales Invoices']['valueString'] ?? $doc['Related Sales Invoices']['content'] ?? null);
        $shipmentNos = $this->extractReferences($doc['Related Shipment Numbers']['valueString'] ?? $doc['Related Shipment Numbers']['content'] ?? null);

        if ($this->shouldMergeOrdersIntoInvoices($salesOrders, $salesInvoices)) {
            $salesInvoices = array_values(array_unique(array_merge($salesInvoices, $salesOrders)));
            $salesOrders = [];
        }

        return [
            'related_sales_invoices' => $this->joinReferences($salesInvoices),
            'related_sales_orders' => $this->joinReferences($salesOrders),
            'related_shipment_nos' => $this->joinReferences($shipmentNos),
        ];
    }

    private function shouldMergeOrdersIntoInvoices(array $salesOrders, array $salesInvoices): bool
    {
        if (!$salesOrders || !$salesInvoices) {
            return false;
        }

        $invoiceLengths = array_unique(array_map('strlen', $salesInvoices));

        if (count($invoiceLengths) !== 1) {
            return false;
        }

        $invoiceLength = (int) $invoiceLengths[0];

        foreach ($salesOrders as $salesOrder) {
            if (!preg_match('/^\d+$/', $salesOrder) || strlen($salesOrder) !== $invoiceLength) {
                return false;
            }
        }

        return true;
    }

    private function extractReferences(array|string|null $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $content = is_array($value) ? implode(',', $value) : (string) $value;

        preg_match_all('/\b[A-Z]*\d+[A-Z0-9-]*\b/i', $content, $matches);

        return array_values(array_unique(array_map(
            fn ($item) => strtoupper(trim($item, " \t\n\r\0\x0B,.;:")),
            array_filter($matches[0] ?? [])
        )));
    }

    private function joinReferences(array $values): ?string
    {
        $values = array_values(array_unique(array_filter($values)));

        return $values ? implode(', ', $values) : null;
    }
}