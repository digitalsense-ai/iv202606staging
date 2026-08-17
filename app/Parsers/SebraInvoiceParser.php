<?php

namespace App\Parsers;
use Illuminate\Support\Facades\Log;

class SebraInvoiceParser implements ClientInvoiceParserInterface
{
    use ParsesInvoiceValues;

    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'sebra')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'sebra');
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
             * Fallback for Additional Comments style:
             *             
             * Additional Comments: 9002,9003
             */
            if (preg_match('/Additional\s+Comments\s*:\s*([0-9,\s]+)/i', $text, $match)) {
                $salesInvoices = array_values(array_filter(
                    array_map('trim', explode(',', $match[1]))
                ));
            }

            /*
             * Fallback for Samlefakturaen d\u00e6kker salgsfaktura style:
             *             
             * Samlefakturaen d\u00e6kker salgsfaktura 9002,9003
             */
            if (preg_match('/Samlefakturaen\s+dækker\s+salgsfaktura\s+([0-9,\s]+)/i', $text, $match)) {
                $salesInvoices = array_values(array_filter(
                    array_map('trim', explode(',', $match[1]))
                ));
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