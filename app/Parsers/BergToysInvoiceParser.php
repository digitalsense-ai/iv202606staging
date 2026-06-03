<?php

namespace App\Parsers;

class BergToysInvoiceParser implements ClientInvoiceParserInterface
{    
    public function supports(?string $clientName, array $doc = [], array $result = []): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'berg toys')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'berg toys'); // FIXED (was wrong)
    }

    public function parse(array $result, array $doc, ?string $clientName = null): array
    {
        $content = $result['analyzeResult']['content'] ?? '';

        $header = "ItemID\nDescription\nQuantity\nNet weight per part";

        $parts = explode($header, $content);

        if (count($parts) <= 1) {
            return [
                'related_sales_invoices' => $doc['Related Sales Invoices']['valueString'] ?? null,
                'related_sales_orders'   => $doc['Related Sales Orders']['valueString'] ?? null,
                'related_shipment_nos'   => $doc['Related Shipment Numbers']['valueString'] ?? null,
            ];
        }

        array_shift($parts);

        $content = implode("\n", $parts);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $content))));

        $data = [];
        $current = [];

        foreach ($lines as $line) {

            if (!preg_match('/^[A-Z0-9]+$/i', $line)) {
                continue;
            }

            $current[] = $line;

            if (count($current) === 2) {
                $data[] = [
                    'sales_invoice' => $current[0],
                    'sales_order'   => $current[1],
                    'shipment'      => [],
                ];

                $current = [];
            }
        }

        return [
            'related_sales_invoices' => implode(', ', array_column($data, 'sales_invoice')),
            'related_sales_orders'   => implode(', ', array_column($data, 'sales_order')),
            'related_shipment_nos'   => '',
        ];
    }
}