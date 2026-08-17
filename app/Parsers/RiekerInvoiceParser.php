<?php

namespace App\Parsers;
use Illuminate\Support\Facades\Log;

class RiekerInvoiceParser implements ClientInvoiceParserInterface
{
    use ParsesInvoiceValues;
    
    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'rieker')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'rieker');
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {
        //$value = trim((string) ($doc['Related Sales Invoices']['valueString'] ?? ''));
        $value = $this->getValueString($doc['Related Sales Invoices']['valueString'] ?? '');

        $salesInvoices = [];
        $salesOrders   = [];
        $shipmentNos   = [];

        if ($value !== '') {
            $salesInvoices = array_values(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', $value))));
        } else {
            $text = $this->ocrText($result);

            /*
             * Fallback for Samlefaktura style:
             *
             * Samlefaktura
             * 24.02.2026 Nr .: SR261000009
             * 26802736 - 26803056
             * page 1
             */
            if (preg_match_all('/\b(\d{6,})\s*[-–—]\s*(\d{6,})\b/u', $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $from = $match[1];
                    $to   = $match[2];

                    $salesInvoices[] = "{$from} - {$to}";
                }
            }
        }

        return [
            'related_sales_invoices' => implode(', ', array_unique($salesInvoices)),
            'related_sales_orders'   => implode(', ', array_unique($salesOrders)),
            'related_shipment_nos'   => implode(', ', array_unique($shipmentNos)),
        ];
    }

    private function ocrText(array $result): string
    {
        if (!empty($result['content'])) {
            return (string) $result['content'];
        }

        if (!empty($result['analyzeResult']['content'])) {
            return (string) $result['analyzeResult']['content'];
        }

        $lines = [];

        foreach (data_get($result, 'analyzeResult.pages', []) as $page) {
            foreach (($page['lines'] ?? []) as $line) {
                if (!empty($line['content'])) {
                    $lines[] = $line['content'];
                }
            }
        }

        return implode("\n", $lines);
    }

    // public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    // {           
    //     $value = $doc['Related Sales Invoices']['valueString'] ?? '';

    //     preg_match_all('/(\d+)\s*\(([^)]+)\)/', $value, $matches, PREG_SET_ORDER);

    //     $salesInvoices = [];
    //     $salesOrders   = [];

    //     foreach ($matches as $match) {
    //         $salesInvoices[] = trim($match[1]); // 9010580
    //         $salesOrders[]   = trim($match[2]); // S4788082
    //     }

    //     // Shipment numbers
    //     $shipmentValue = $doc['Related Shipment Numbers']['valueString'] ?? '';

    //     preg_match_all('/\(([^)]+)\)/', $shipmentValue, $shipmentMatches);

    //     $shipmentNos = array_map('trim', $shipmentMatches[1]);

    //     return [
    //         'related_sales_invoices' => implode(', ', $salesInvoices),
    //         'related_sales_orders'   => implode(', ', $salesOrders),
    //         'related_shipment_nos'   => implode(', ', $shipmentNos),
    //     ];     
    // }
}