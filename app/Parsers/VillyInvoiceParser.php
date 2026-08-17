<?php

namespace App\Parsers;
use Illuminate\Support\Facades\Log;

class VillyInvoiceParser implements ClientInvoiceParserInterface
{
    use ParsesInvoiceValues;

    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {        
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'villy')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'villy');
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {        
        $linePattern = '/Denne Proforma samlefaktura indeholder.*?:\s*([^\r\n]+)/i';
        $invoicePattern = '/^N\d{6}$/i';

        $salesInvoices = [];
        $salesOrders   = [];
        $shipmentNos   = [];

        // $salesInvoiceValues = trim((string)($doc['Related Sales Invoices']['valueString'] ?? ''));
        // $salesOrderValues = trim((string)($doc['Related Sales Orders']['valueString'] ?? ''));
        // $shipmentValues = trim((string)($doc['Related Shipment Numbers']['valueString'] ?? ''));
        
        $salesInvoiceValues = $this->getValueString($doc['Related Sales Invoices']['valueString'] ?? '');
        $salesOrderValues   = $this->getValueString($doc['Related Sales Orders']['valueString'] ?? '');
        $shipmentValues     = $this->getValueString($doc['Related Shipment Numbers']['valueString'] ?? '');

        $values = [];

        if ($salesInvoiceValues !== '') {
            $values = array_values(array_filter(
                array_map('trim', preg_split('/[\s\r\n,;]+/', $salesInvoiceValues))
            ));
        }
        else if ($salesOrderValues !== '') {
            $values = array_values(array_filter(
                array_map('trim', preg_split('/[\s\r\n,;]+/', $salesOrderValues))
            ));
        }
        else if ($shipmentValues !== '') {
            $values = array_values(array_filter(
                array_map('trim', preg_split('/[\s\r\n,;]+/', $shipmentValues))
            ));
        }

        // Always check OCR content also
        $text = $this->ocrText($result);
       
        if (preg_match($linePattern, $text, $match)) {
            preg_match_all('/N\d{6}/i', $match[1], $matches);
            $values = array_merge($values, $matches[0]);
        }

        foreach ($values as $item) {
            if (preg_match($invoicePattern, $item)) {
                $salesInvoices[] = strtoupper($item);
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