<?php

namespace App\Parsers;
use Illuminate\Support\Facades\Log;

class SportsInvoiceParser implements ClientInvoiceParserInterface
{
    use ParsesInvoiceValues;
    
    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {        
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'sports group')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'sports group');
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {        
        
        $salesInvoices = [];
        $salesOrders   = [];
        $shipmentNos   = [];

        // $salesInvoiceValues = trim((string)($doc['Related Sales Invoices']['valueString'] ?? ''));
        // $salesOrderValues = trim((string)($doc['Related Sales Orders']['valueString'] ?? ''));
        // $shipmentValues = trim((string)($doc['Related Shipment Numbers']['valueString'] ?? ''));

        $salesInvoiceValues = $this->getValueString($doc['Related Sales Invoices']['valueString'] ?? '');
        $salesOrderValues   = $this->getValueString($doc['Related Sales Orders']['valueString'] ?? '');
        $shipmentValues     = $this->getValueString($doc['Related Shipment Numbers']['valueString'] ?? '');
              
        // Existing shipment numbers
        if ($shipmentValues !== '') {
            $shipmentNos = array_values(array_filter(
                array_map('trim', preg_split('/[\s\r\n,;]+/', $shipmentValues))
            ));
        }

        // Sales invoices
        if ($salesInvoiceValues !== '') {
            $values = array_values(array_filter(
                array_map('trim', preg_split('/[\s\r\n,;]+/', $salesInvoiceValues))
            ));

            foreach ($values as $item) {
                $item = strtoupper(trim($item));

                // Check if it's a numeric range (e.g. 193472-193474)
                // if (preg_match('/^(\d+)-(\d+)$/', $item, $matches)) {
                //     $start = (int) $matches[1];
                //     $end   = (int) $matches[2];

                //     // Calculate inclusive range size
                //     if (abs($end - $start) + 1 > 300) {
                //         $shipmentNos[] = $item;
                //         continue; // Don't add to sales invoices
                //     }
                // }

                if (preg_match('/^(\d+)-(\d+)$/', $item, $matches)) {
                    $rangeSize = abs((int)$matches[2] - (int)$matches[1]) + 1;

                    if ($rangeSize > 300) {
                        $shipmentNos[] = $item;
                        continue;
                    }
                }

                $salesInvoices[] = $item;
            }
        }

        // Sales orders
        if ($salesOrderValues !== '') {
            $salesOrders = array_values(array_filter(
                array_map('trim', preg_split('/[\s\r\n,;]+/', $salesOrderValues))
            ));
        }

        return [
            'related_sales_invoices' => implode(', ', array_unique($salesInvoices)),
            'related_sales_orders'   => implode(', ', array_unique($salesOrders)),
            'related_shipment_nos'   => implode(', ', array_unique($shipmentNos)),
        ];
    }

    // private function ocrText(array $result): string
    // {
    //     if (!empty($result['content'])) {
    //         return (string) $result['content'];
    //     }

    //     if (!empty($result['analyzeResult']['content'])) {
    //         return (string) $result['analyzeResult']['content'];
    //     }

    //     $lines = [];

    //     foreach (data_get($result, 'analyzeResult.pages', []) as $page) {
    //         foreach (($page['lines'] ?? []) as $line) {
    //             if (!empty($line['content'])) {
    //                 $lines[] = $line['content'];
    //             }
    //         }
    //     }

    //     return implode("\n", $lines);
    // }   
}