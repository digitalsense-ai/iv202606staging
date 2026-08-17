<?php

namespace App\Parsers;
use Illuminate\Support\Facades\Log;

class OurUnitsInvoiceParser implements ClientInvoiceParserInterface
{
    use ParsesInvoiceValues;
    
    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'our units')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'our units');
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {
        $invoicePrefixes = ['NOSIN', 'CHIN', 'SIN', 'CHSIN'];
        $pattern = '/^(?:' . implode('|', $invoicePrefixes) . ')\d+$/i';

        $salesInvoices = [];
        $salesOrders   = [];
        $shipmentNos   = [];

        //$salesInvoiceValues = trim((string)($doc['Related Sales Invoices']['valueString'] ?? ''));
        //$shipmentValues     = trim((string)($doc['Related Shipment Numbers']['valueString'] ?? ''));

        $salesInvoiceValues = $this->getValueString($doc['Related Sales Invoices']['valueString'] ?? '');
        $shipmentValues     = $this->getValueString($doc['Related Shipment Numbers']['valueString'] ?? '');

        $values = [];

        if ($salesInvoiceValues !== '') {
            $values = array_values(array_filter(
                array_map('trim', preg_split('/[\s\r\n,;]+/', $salesInvoiceValues))
            ));              
        } elseif ($shipmentValues !== '') {
            $values = array_values(array_filter(
                array_map('trim', preg_split('/[\s\r\n,;]+/', $shipmentValues))
            )); 
                        
        } else {
            $text = $this->ocrText($result);

            if (preg_match_all('/(?:' . implode('|', $invoicePrefixes) . ')\d+/i', $text, $matches)) {            
                $values = array_map('trim', $matches[0]);
            }
        }

        foreach ($values as $item) {
            if (preg_match($pattern, $item)) {
                $salesInvoices[] = strtoupper($item);
            } else {
                $shipmentNos[] = $item;
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
}