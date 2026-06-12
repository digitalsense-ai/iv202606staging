<?php

namespace App\Parsers;

use Str;
use Illuminate\Support\Facades\Log;

class BergToysInvoiceParser implements ClientInvoiceParserInterface
{    
    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'berg toys') || ($clientNo && ($clientNo == '934286723' || $clientNo == '379603560' || $clientNo == '292640361'))) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'berg toys'); // FIXED (was wrong)
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {
        if($validate)
        {
            $relatedSalesInvoices = $doc['Related Sales Invoices']['valueString'] ?? null;
            $relatedSalesInvoices = $relatedSalesInvoices ?? [];

            $relatedSalesOrders   = $doc['Related Sales Orders']['valueString'] ?? null;
            $relatedSalesOrders   = $relatedSalesOrders ?? [];

            $relatedShipments     = $doc['Related Shipment Numbers']['valueString'] ?? null;
            $relatedShipments   = $relatedShipments ?? [];

            if (!is_array($relatedSalesInvoices)) {
                $relatedSalesInvoices = array_filter(array_map('trim', explode(',', $relatedSalesInvoices)));
            }
//Log::info($relatedSalesInvoices);
            if (!is_array($relatedSalesOrders)) {
                $relatedSalesOrders = array_filter(array_map('trim', explode(',', $relatedSalesOrders)));
            }
//Log::info($relatedSalesOrders);

            if (!is_array($relatedShipments)) {
                $relatedShipments = array_filter(array_map('trim', explode(',', $relatedShipments)));
            }
//Log::info($relatedShipments);

            $related_sales_invoices = null;
            foreach ($relatedSalesInvoices as $relatedSalesInvoice)
            {
                $relatedSalesInvoice = trim($relatedSalesInvoice);

                if(Str::startsWith(Str::lower($relatedSalesInvoice), ['no', 'ch', 'uk', '2026']))
                {
                    if(Str::startsWith(Str::lower($relatedSalesInvoice), ['chn']))
                    {
                    }
                    else
                    {
                        if($related_sales_invoices)
                            $related_sales_invoices .= ', ' . $relatedSalesInvoice;
                        else
                            $related_sales_invoices = $relatedSalesInvoice;
                    }
                }
            }
//Log::info($related_sales_invoices);
            $related_sales_orders = null;
            foreach ($relatedSalesOrders as $relatedSalesOrder)
            {
                $relatedSalesOrder = trim($relatedSalesOrder);

                if(Str::startsWith(Str::lower($relatedSalesOrder), ['81', '31', '52', '226']))
                {
                    if($related_sales_orders)
                        $related_sales_orders .= ', ' . $relatedSalesOrder;
                    else
                        $related_sales_orders = $relatedSalesOrder;
                }
            }
//Log::info($related_sales_orders);
            $related_shipment_nos = null;
            foreach ($relatedShipments as $relatedShipment)
            {
                $relatedShipment = trim($relatedShipment);

                if($related_shipment_nos)
                    $related_shipment_nos .= ', ' . $relatedShipment;
                else
                    $related_shipment_nos = $relatedShipment;
            }
//Log::info($related_shipment_nos);            
            return [
                'related_sales_invoices' => $related_sales_invoices,
                'related_sales_orders'   => $related_sales_orders,
                'related_shipment_nos'   => $related_shipment_nos,
            ];
        }
        else
        {
            $content = $result['analyzeResult']['content'] ?? '';

            $header = "ItemID\nDescription\nQuantity\nNet weight per part";

            $parts = explode($header, $content);
// Log::info($parts);
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
// Log::info("---------------------------------------------------------");           
// Log::info($lines);
            foreach ($lines as $line) {

                if (!preg_match('/^[A-Z0-9]+$/i', $line)) {
                    continue;
                }

                // Keep only values with length > 8
                if (strlen($line) < 8) {
                    continue;
                }

                $current[] = $line;
// Log::info($current);
// Log::info("-----------");
                if (count($current) === 2) {  

                    $sales_invoice = '';
                    if(Str::startsWith(Str::lower($current[0]), ['chn']))
                        $sales_invoice = null;
                    else
                        $sales_invoice = (Str::startsWith(Str::lower($current[0]), ['no', 'ch', 'uk'])) ? $current[0] : null;

                    $data[] = [
                        'sales_invoice' => $sales_invoice,
                        'sales_order'   => (Str::startsWith(Str::lower($current[1]), ['81', '31', '52'])) ? $current[1] : null,
                        'shipment'      => [],
                    ];

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
}