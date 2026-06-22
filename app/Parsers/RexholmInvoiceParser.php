<?php

namespace App\Parsers;
use Illuminate\Support\Facades\Log;
class RexholmInvoiceParser implements ClientInvoiceParserInterface
{
    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'rexholm')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'rexholm'); // FIXED (was wrong)
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {   
        // if($validate)
        // { 
        //     $salesInvoices = $doc['Related Sales Invoices']['valueString'] ?? null;
            
        //     $salesOrders   = $doc['Related Sales Orders']['valueString'] ?? null;
            
        //     $shipmentNos     = $doc['Related Shipment Numbers']['valueString'] ?? null;
            
        //     return [
        //         'related_sales_invoices' => $salesInvoices,
        //         'related_sales_orders'   => $salesOrders,
        //         'related_shipment_nos'   => $shipmentNos,
        //     ];
        // }
        // else
        // {    
            $value = $doc['Related Sales Invoices']['valueString'] ?? '';

            preg_match_all('/(\d+)\s*\(([^)]+)\)/', $value, $matches, PREG_SET_ORDER);

            $salesInvoices = [];
            $salesOrders   = [];

            foreach ($matches as $match) {
                $salesInvoices[] = trim($match[1]); // 9010580
                $salesOrders[]   = trim($match[2]); // S4788082
            }

            // Shipment numbers
            $shipmentValue = $doc['Related Shipment Numbers']['valueString'] ?? '';

            preg_match_all('/\(([^)]+)\)/', $shipmentValue, $shipmentMatches);

            $shipmentNos = array_map('trim', $shipmentMatches[1]);

            return [
                'related_sales_invoices' => implode(', ', $salesInvoices),
                'related_sales_orders'   => implode(', ', $salesOrders),
                'related_shipment_nos'   => implode(', ', $shipmentNos),
            ];
        //}
    }
}