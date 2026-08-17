<?php

namespace App\Parsers;
use Illuminate\Support\Facades\Log;
class RexholmInvoiceParser implements ClientInvoiceParserInterface
{
    use ParsesInvoiceValues;

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
            //$value = $doc['Related Sales Invoices']['valueString'] ?? '';

            // preg_match_all('/(\d+)\s*\(([^)]+)\)/', $value, $matches, PREG_SET_ORDER);

            // $salesInvoices = [];
            // $salesOrders   = [];

            // foreach ($matches as $match) {
            //     $salesInvoices[] = trim($match[1]); // 9010580
            //     $salesOrders[]   = trim($match[2]); // S4788082
            // }

            // // Shipment numbers
            // $shipmentValue = $doc['Related Shipment Numbers']['valueString'] ?? '';

            // preg_match_all('/\(([^)]+)\)/', $shipmentValue, $shipmentMatches);

            // $shipmentNos = array_map('trim', $shipmentMatches[1]);
//$invoiceNumber = $doc['Invoice Number']['valueString'] ?? null;

            //$value = $doc['Related Sales Invoices']['valueString'] ?? '';
            $value = $this->getValueString($doc['Related Sales Invoices']['valueString'] ?? '');

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $value = trim($value);

            $salesInvoices = [];
            $salesOrders   = [];
            if (str_contains($value, '(')) {
                // Format: 9010580 (S4788082), 9010581 (S4788083)
                preg_match_all('/(\d+)\s*\(([^)]+)\)/', $value, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $salesInvoices[] = trim($match[1]);
                    $salesOrders[]   = trim($match[2]);
                }
            } else {
                // Format: 9010580, 9010581, 9010582
                $salesInvoices = array_filter(array_map('trim', explode(',', $value)));
                $salesOrders = [];
            }

            //$shipmentValue = $doc['Related Shipment Numbers']['valueString'] ?? '';
            $shipmentValue = $doc['Related Shipment Numbers']['valueString'] ?? '';

            if (is_array($shipmentValue)) {
                $shipmentValue = implode(', ', $shipmentValue);
            }

            $shipmentValue = trim($shipmentValue);

            if (str_contains($shipmentValue, '(')) {
                // Extract values inside parentheses
                preg_match_all('/\(([^)]+)\)/', $shipmentValue, $shipmentMatches);
                $shipmentNos = array_map('trim', $shipmentMatches[1]);
            } else {
                // Already comma-separated
                $shipmentNos = array_filter(array_map('trim', explode(',', $shipmentValue)));
            }
// if($invoiceNumber == "PROF03031")
// {
//    Log::info([
//         "clientName" => $clientName,
//         "clientNo" => $clientNo,
//         "invoiceNumber" => $invoiceNumber,
//         "result" => $result,
//         "doc" => $doc,
//         "value" => $value,
//         "salesInvoices" => implode(', ', $salesInvoices),
//         "salesOrders" => implode(', ', $salesOrders),
//         "shipmentNos" => implode(', ', $shipmentNos)
//     ]);
// }
            return [
                'related_sales_invoices' => implode(', ', $salesInvoices),
                'related_sales_orders'   => implode(', ', $salesOrders),
                'related_shipment_nos'   => implode(', ', $shipmentNos),
            ];
        //}
    }
}