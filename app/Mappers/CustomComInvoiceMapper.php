<?php

namespace App\Mappers;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Str;

use App\Helpers\DateHelper;
use App\Helpers\OrgNoNormalizer;
use App\Helpers\CurrencyHelper;
use App\Helpers\EuropeanNumberHelper;
use App\Support\OcrFallbackFieldExtractor;

use App\Services\ClientResolver;
use App\Parsers\ClientInvoiceParser;

class CustomComInvoiceMapper
{
    public static function map(array $result, array $clients, bool $validate = false, string $passDate = null, bool $manual = false, bool $searchSave = false): array
    {
        $comInvoiceTypes = [
            'consolidated',
            'commercial',
            'samlefaktura',
            'nlefaktura',
            'nnlefaktura',
            'export sales',
            'reason for export',
            'reason for export: sale',
            'proforma',
            'proformafaktura',
            'zollrechnung',
            //'reference',
            'total tariff',
            'total tarrif',
            'rechnung',
            'samleliste',
            'invoice declaration',
            'master invoice',
            'customs invoice',
            'collective',
            'reference/invoicing'
        ];

        $doc = $result['analyzeResult']['documents'][0]['fields'] ?? [];
        $content = $result['analyzeResult']['content'] ?? '';
//Log::info($doc); 
        
        $invoice_type = $doc['Invoice Type']['valueString'] ?? null;
        $invalid_invoice_type = OcrFallbackFieldExtractor::invalidInvoiceType($content);

        $recipientName = $doc ? ($doc['Client Name']['valueString'] ?? '') : '';
        //$orgNo = OrgNoNormalizer::normalize(($doc['Client Number']['valueString'] ?? null), $recipientName);

        $clientNumber = $doc['Client Number']['valueString'] ?? null;
        if($clientNumber)
            $rawClientNumber = (strlen(trim($clientNumber)) === 3)
                ? OcrFallbackFieldExtractor::clientNumber($content)
                : $clientNumber;
        else
            $rawClientNumber = OcrFallbackFieldExtractor::clientNumber($content);

        $orgNo = OrgNoNormalizer::normalize($rawClientNumber, $recipientName);

        $client_result = app(ClientResolver::class)->resolve(
            $clients,
            $recipientName,
            $orgNo,
            null
        );
       
        if($manual || $searchSave)
        {
            $client_name = $client_result['name'] ?? $recipientName;
            $client_no   = $client_result['org_no'] ?? $clientNumber; 
            $extracted_client_no = $client_result['og_org_no'] ?? null; 

            // Log::info([
            //     'client_name' => $client_name,
            //     'client_no' => $client_no,
            //     'extracted_client_no' => $extracted_client_no,
            // ]);
        }
        else
        {
            $client_name = $client_result['name'] ?? null;
            $client_no   = $client_result['org_no'] ?? null; 
            $extracted_client_no = $client_result['og_org_no'] ?? null; 
        }

        $invoiceDate = DateHelper::parseInvoiceDate(
            $doc['Invoice Date']['content'] ?? null
        );

        $invoiceNumber = $doc['Invoice Number']['valueString'] ?? null;
        $invoiceNumber = trim($invoiceNumber ?? '') ?: null;
        
        if (!$invoiceDate) {
            $invoiceDate = OcrFallbackFieldExtractor::invoiceDate($content);
        }
        $invoiceDate = DateHelper::parseInvoiceDate($invoiceDate);

        if (!$invoiceNumber) {
            $invoiceNumber = OcrFallbackFieldExtractor::invoiceNumber($content);
        }

        if($client_name && stripos($client_name, 'dfi-geisler') !== false)
            $invoiceNumber = !empty($invoiceNumber)
                                ? $invoiceNumber
                                : ($invoiceDate ? str_replace('-', '', $invoiceDate) : null);            
        // if($validate)
        // {
        //     $parser = app(ClientInvoiceParser::class);
        //     $related = $parser->parse($doc, $client_name, $client_no, true);

        //     // $finalRelatedSalesInvoices = $doc['Related Sales Invoices']['valueString'] ?? null;
        //     // $finalRelatedSalesOrders   = $doc['Related Sales Orders']['valueString'] ?? null;
        //     // $finalRelatedShipments     = $doc['Related Shipment Numbers']['valueString'] ?? null;
        // }
        // else
        // {
            //Log::info($result);

            $parser = app(ClientInvoiceParser::class);
            $related = $parser->parse($result, $client_name, $client_no, false);   

            //Log::info($related);
        //}

        $finalRelatedSalesInvoices = $related['related_sales_invoices'] ?? null;
        $finalRelatedSalesOrders   = $related['related_sales_orders'] ?? null;
        $finalRelatedShipments     = $related['related_shipment_nos'] ?? null;

        [$og_currency, $net_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Net Amount']['valueString'] ?? null,
            $doc['Currency']['valueString'] ?? null
        );
      
        if($og_currency)    
            $currency = CurrencyHelper::parseCurrency($og_currency);
        else
            $currency = CurrencyHelper::parseCurrency($doc['Currency']['valueString'] ?? null);

        if (!$currency) {
            $currency = OcrFallbackFieldExtractor::currency($content);
        }

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

//         if (!$net_amount) {
//             $net_fallback = OcrFallbackFieldExtractor::netAmount($content);
// Log::info($net_fallback);
//             if ($net_fallback) {               
//                 if (is_array($net_fallback)) {
//                     $currency = $currency ?? ($net_fallback['currency'] ?? null);
//                     $net_amount = $net_fallback['amount'] ?? null;
//                 } else {
//                     // string case
//                     $net_amount = $net_fallback;
//                 }
//             }
//         }

        $invoiceNumber = rtrim((string) $invoiceNumber, '.');

        if($manual || $searchSave)
        {            
            $finalRelatedSalesInvoices = $doc['Related Sales Invoices']['valueString'] ?? null;
            $finalRelatedSalesOrders   = $doc['Related Sales Orders']['valueString'] ?? null;
            $finalRelatedShipments     = $doc['Related Shipment Numbers']['valueString'] ?? null;
        }
        
        $mapresult = [
            'invoice_type' => $invoice_type,
            'invoice_number' => $invoiceNumber ?? null,            
            'invoice_date'   => $invoiceDate ?? null, 
            'recipient' => [
                'extracted_name' => $recipientName,
                'name'    => $client_name ?? null,
                'address' => $doc['Client Address']['valueString'] ?? null,
                'org_number'   => $client_no ?? null,
                'extracted_org_number' => $extracted_client_no,
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
        if (!$client_name)
            $error_message .= "Client Name missing\n";

        if (!$client_no)
        {
            if ($extracted_client_no)
                $error_message .= "Client No. missing - Invalid Client No.\n";
            else
                $error_message .= "Client No. missing\n";
        }

        $futureInvoiceDate = false;
        $olderInvoiceDate = false;
        if ($invoiceDate) {

            $fetchDate = $passDate ?? now();

            $referenceDate = Carbon::parse($fetchDate)->startOfDay();
            $futureReferenceDate = Carbon::now()->startOfDay();

            $invoiceDateCarbon = Carbon::parse($invoiceDate)->startOfDay();

            // Future invoice date
            if ($invoiceDateCarbon->gt($futureReferenceDate)) {
                $futureInvoiceDate = true;
            }

            // Older than 6 months
            if ($invoiceDateCarbon->lt(
                $referenceDate->copy()->subMonths(6)
            )) {
                $olderInvoiceDate = true;
            }
        }

        if (!$invoiceDate) {
            $error_message .= "Invoice Date missing\n";
        }
        else {
            if ($futureInvoiceDate) {
                $error_message .= "Invoice Date is in the future\n";
            }

            if ($olderInvoiceDate) {
                $error_message .= "Invoice Date is older than 6 months\n";
            }
        }
        
        // if (!$invoice_type)
        //     $error_message .= "Invoice Type missing\n";

        if (!$invoiceDate) {
            $error_message .= "Invoice Date missing\n";
        }

        if (!$currency)
            $error_message .= "Currency missing\n";

        //if (!$net_amount)
        if (blank($net_amount))
            $error_message .= "Net Amount missing\n";

        //if(!$finalRelatedSalesInvoices)
        if (blank($finalRelatedSalesInvoices))
        {
            if (blank($finalRelatedSalesOrders))
            {
                if (blank($finalRelatedShipments))                
                    $error_message .= "References missing\n";
            }
        }

        if ($error_message) {
            $mapresult['error'] = $error_message;
        }
       
        $validInvoiceType = collect($comInvoiceTypes)->contains(function ($type) use ($invoice_type, $invoiceNumber) {            
            if(strtolower($invoice_type) == "rechnung" && 
                Str::startsWith(Str::lower($invoiceNumber), ['ch'])
            )
                return false;
            else       
                return str_contains(strtolower($invoice_type), $type);
        });
// Log::info("COM invoice_type: " . $invoice_type);
// Log::info("COM invoiceNumber: " . $invoiceNumber);
// Log::info("COM validInvoiceType: " . $validInvoiceType);
        // if($client_name && (stripos($client_name, 'engel') !== false
        //         || stripos($client_name, 'guardian') !== false
        //         || stripos($client_name, 'berendsohn') !== false
        //     )            
        // )
        // {
        //     $validInvoiceType = OcrFallbackFieldExtractor::comInvoiceType($content);            
        // }

        // if($client_name && stripos($client_name, 'second female') !== false)
        // {
        //     $validInvoiceType = OcrFallbackFieldExtractor::checkVatRate($content);
        // }

        if ($invoice_type)
        {
            if (!$validInvoiceType) {              
                $mapresult['change_invoice_type'] = true;
            }            
        }  
//Log::info('1 COM change_invoice_type: ' . ($mapresult['change_invoice_type'] ?? 'vvvvvvvvvvvvvvvvv'));
        // else
        // {   
        if(isset($mapresult['change_invoice_type']))
        {
            if($mapresult['change_invoice_type'])
            {
                if(strtolower($invoice_type) === "invoice")
                {
                    $validInvoiceType = OcrFallbackFieldExtractor::checkInvoiceType($content);

                    if($validInvoiceType)
                       unset($mapresult['change_invoice_type']);     
                    else
                        $mapresult['change_invoice_type'] = true;
                }
            }
            else
            {
                $validInvoiceType = OcrFallbackFieldExtractor::checkInvoiceType($content);
//Log::info('2 COM validInvoiceType: ' . $validInvoiceType);
                if($validInvoiceType)
                   unset($mapresult['change_invoice_type']);     
                else
                    $mapresult['change_invoice_type'] = true;
            }
        }    
            
        //}
 
        // if ($invoice_type && !$validInvoiceType) {  
        // //Log::info("COM change_invoice_type: " . $validInvoiceType);     
        //     $mapresult['change_invoice_type'] = true;
        // }
           
        if($client_name)
        {
            if(stripos($client_name, 'vernon') !== false)
            {
                $vatBase = OcrFallbackFieldExtractor::checkVatBase($content, 'base');
                
                if(Str::startsWith(Str::lower($invoiceNumber), ['ex']))
                    unset($mapresult['change_invoice_type']);
                else
                {
                    if($vatBase)
                        $mapresult['change_invoice_type'] = true;
                }
            }
            else if(stripos($client_name, 'committee xxiv') !== false)
            {
                $vatBase = OcrFallbackFieldExtractor::checkVatBase($content, 'amount');
                // Log::info("COM vatBase: " . $vatBase);
                // Log::info("COM invoiceNumber: " . $invoiceNumber);

                if($vatBase)
                    $mapresult['change_invoice_type'] = true;
                else
                {
                    $vatBase = OcrFallbackFieldExtractor::checkVatBase($content, 'rate');

                    if($vatBase)
                        $mapresult['change_invoice_type'] = true;
                    else
                        unset($mapresult['change_invoice_type']);
                }
            }
        }

        $chkSpecificText = OcrFallbackFieldExtractor::chkSpecificText($content, 'samsoe samsoe');        
        if($chkSpecificText)
        {            
            if (blank($net_amount))
                $mapresult['change_invoice_type'] = true;
            else
                unset($mapresult['change_invoice_type']);
        }

        if ($invalid_invoice_type !== null)
        {       
            $mapresult['invalid_invoice_type'] = true;
        }
//Log::info("COM mapresult change_invoice_type: " . ($mapresult['change_invoice_type'] ?? null));
    //Log::info("COM mapresult invalid_invoice_type: " . ($mapresult['invalid_invoice_type'] ?? null));
        return $mapresult;        
    }
}