<?php

namespace App\Parsers;

class KiteInvoiceParser implements ClientInvoiceParserInterface
{
    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'kite')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'kite'); // FIXED (was wrong)
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {
        $content = $result['analyzeResult']['content'] ?? '';

        $header = "Order Number\nInvoice Number\nTracking Number";

        $parts = explode($header, $content);

        if (count($parts) <= 1) {
            return [
                'related_sales_invoices' => null,
                'related_sales_orders' => null,
                'related_shipment_nos' => null,
            ];
        }

        array_shift($parts);

        $content = implode("\n", $parts);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $content))));

        $data = [];
        $current = [];

        foreach ($lines as $line) {
            if (!preg_match('/\d/', $line)) {
                continue;
            }

            $current[] = $line;

            if (count($current) === 3) {
                $data[] = [
                    'sales_order'   => $current[0],
                    'sales_invoice' => $current[1],
                    'shipment'      => explode(',', $current[2]),
                ];

                $current = [];
            }
        }

        return [
            'related_sales_invoices' => implode(', ', array_column($data, 'sales_invoice')),
            'related_sales_orders'   => implode(', ', array_column($data, 'sales_order')),
            'related_shipment_nos'   => implode(', ', array_merge(...array_column($data, 'shipment'))),
        ];
    }
}