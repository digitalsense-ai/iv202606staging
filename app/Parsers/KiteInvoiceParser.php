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

        $tokens = array_merge(
            $this->extractKiteTokens($doc['Related Sales Orders']['valueString'] ?? null),
            $this->extractKiteTokens($doc['Related Sales Invoices']['valueString'] ?? null),
            $this->extractKiteTokens($doc['Related Shipment Numbers']['valueString'] ?? null),
            $this->extractKiteTokens(array_column($rows, 'sales_order')),
            $this->extractKiteTokens(array_column($rows, 'sales_invoice')),
            $this->extractKiteTokens(array_column($rows, 'shipment'))
        );

        return [
            'related_sales_invoices' => $this->joinReferences($this->filterKiteSalesInvoices($tokens)),
            'related_sales_orders' => $this->joinReferences($this->filterKiteSalesOrders($tokens)),
            'related_shipment_nos' => $this->joinReferences($this->filterKiteShipments($tokens)),
        ];
        
        /*
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
        */        
    }

    private function extractKiteTokens(array|string|null $value): array
    {
        $tokens = [];

        foreach ($this->expandReferenceRanges($value) as $item) {
            foreach (preg_split('/\s+/', (string) $item) ?: [] as $token) {
                $token = trim($token, " \t\n\r\0\x0B,.;:");

                if ($token !== '') {
                    $tokens[] = $token;
                }
            }
        }

        return array_values(array_unique($tokens));
    }

    private function filterKiteSalesOrders(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isKiteSalesOrder((string) $value)));
    }

    private function filterKiteSalesInvoices(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isKiteSalesInvoice((string) $value)));
    }

    private function filterKiteShipments(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isKiteShipment((string) $value)));
    }

    private function isKiteSalesOrder(string $value): bool
    {
        return (bool) preg_match('/^25\d{9}$/', trim($value));
    }

    private function isKiteSalesInvoice(string $value): bool
    {
        return (bool) preg_match('/^\d{6}$/', trim($value));
    }

    private function isKiteShipment(string $value): bool
    {
        return (bool) preg_match('/^92\d{9}$/', trim($value));
    }
}