<?php

namespace App\Mappers;

use Illuminate\Support\Facades\Log;

use App\Helpers\DateHelper;
use App\Helpers\OrgNoNormalizer;
use App\Helpers\CurrencyHelper;
use App\Helpers\EuropeanNumberHelper;

use App\Services\ClientResolver;
use App\Parsers\ClientInvoiceParser;

class CustomComInvoiceMapper
{
    public static function map(array $result, array $clients, bool $validate = false): array
    {
        $doc = $result['analyzeResult']['documents'][0]['fields'] ?? [];
//Log::info($doc); 
         
       $recipientName = $doc ? ($doc['Client Name']['valueString'] ?? '') : '';
       $orgNo = OrgNoNormalizer::normalize(($doc['Client Number']['valueString'] ?? null), $recipientName);

        $client_result = app(ClientResolver::class)->resolve(
            $clients,
            $recipientName,
            $orgNo,
            null
        );
        $client_name = $client_result['name'] ?? null;
        $client_no   = $client_result['org_no'] ?? null; 

        $invoiceDate = DateHelper::parseInvoiceDate(
            $doc['Invoice Date']['content'] ?? null
        );

        $invoiceNumber = $doc['Invoice Number']['valueString'] ?? null;
        $invoiceNumber = trim($invoiceNumber ?? '') ?: null;        

        if($client_name && stripos($client_name, 'dfi-geisler') !== false)
            $invoiceNumber = !empty($invoiceNumber)
                                ? $invoiceNumber
                                : ($invoiceDate ? str_replace('-', '', $invoiceDate) : null);            
        if($validate)
        {
            $finalRelatedSalesInvoices = $doc['Related Sales Invoices']['valueString'] ?? null;
            $finalRelatedSalesOrders   = $doc['Related Sales Orders']['valueString'] ?? null;
            $finalRelatedShipments     = $doc['Related Shipment Numbers']['valueString'] ?? null;
        }
        else
        {
            $parser = app(ClientInvoiceParser::class);
            $related = $parser->parse($result);

            $finalRelatedSalesInvoices = $related['related_sales_invoices'] ?? null;
            $finalRelatedSalesOrders   = $related['related_sales_orders'] ?? null;
            $finalRelatedShipments     = $related['related_shipment_nos'] ?? null;
        }
       
        [$og_currency, $net_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Net Amount']['valueString'] ?? null,
            $doc['Currency']['valueString'] ?? null
        );
        $currency = CurrencyHelper::parseCurrency($og_currency);

        [$og_exchange_currency, $exchange_net_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Exchange Net Amount']['valueString'] ?? null,
            $doc['Exchange Currency']['valueString'] ?? null
        );
        $exchange_currency = CurrencyHelper::parseCurrency($og_exchange_currency);

        $net_amount = EuropeanNumberHelper::normalize(
            $net_amount ?? null
        );
        
        $exchange_net_amount = EuropeanNumberHelper::normalize(
            $exchange_net_amount ?? null
        );  
        
        $mapresult = [
            'invoice_type' => $doc['Invoice Type']['valueString'] ?? null,
            'invoice_number' => $invoiceNumber ?? null,            
            'invoice_date'   => $invoiceDate ?? null, 
            'recipient' => [
                'extracted_name' => $recipientName,
                'name'    => $client_name ?? null,
                'address' => $doc['Client Address']['valueString'] ?? null,
                'org_number'   => $client_no ?? null,
            ],                                              
            
            'related_sales_invoices' => $finalRelatedSalesInvoices,
            'related_sales_orders'   => $finalRelatedSalesOrders,
            'related_shipment_nos'   => $finalRelatedShipments,
           
            'net_amount'   => $net_amount ?? null,          
            'currency'   => $currency ?? null,
            'exchange_currency'   => $exchange_currency ?? null,
            'exchange_net_amount'   => $exchange_net_amount ?? null,    
        ];

        $error_message = '';
        if (!$invoiceDate)
            $error_message .= "Invoice Date missing\n";

        if (!$invoiceNumber)
            $error_message .= "Invoice no. missing\n";

        if (!$currency)
            $error_message .= "Currency missing\n";

        if (!$net_amount)
            $error_message .= "Net Amount missing";

        if ($error_message) {
            $mapresult['error'] = $error_message;
        }
        
        return $mapresult;        
    }
}