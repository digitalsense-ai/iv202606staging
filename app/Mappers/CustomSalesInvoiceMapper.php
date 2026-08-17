<?php

namespace App\Mappers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Str;

use App\Helpers\CreditNoteHelper;
use App\Helpers\DateHelper;
use App\Helpers\OrgNoNormalizer;
use App\Helpers\CurrencyHelper;
use App\Helpers\EuropeanNumberHelper;
use App\Helpers\VatRateHelper;
use App\Helpers\ExchangeRateHelper;
use App\Support\OcrFallbackFieldExtractor;

use App\Services\ClientResolver;

class CustomSalesInvoiceMapper
{
    public static function map(array $result, array $clients, string $passDate = null, bool $manual = false, bool $searchSave = false): array
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

        $credit_note = CreditNoteHelper::isCreditNote(
            $invoice_type
        );

        $supplierName = $doc ? ($doc['Client Name']['valueString'] ?? '') : '';    
        $orgNo = OrgNoNormalizer::normalize(($doc['Client Number']['valueString'] ?? null), $supplierName);

        if(!$orgNo)
        {            
            $rawVatNumber = $doc['Client Vat Number']['valueString']
                ?? OcrFallbackFieldExtractor::clientNumber($content);

            $orgNo = OrgNoNormalizer::normalize($rawVatNumber, $supplierName);            
        }

        $client_result = app(ClientResolver::class)->resolve(
            $clients,
            $supplierName,
            $orgNo,
            null
        );
        
        if($manual || $searchSave)
        {
            $client_name = $client_result['name'] ?? $supplierName;
            $client_no   = $client_result['org_no'] ?? ($doc['Client Number']['valueString'] ?? null); 
            $extracted_client_no = $client_result['og_org_no'] ?? null; 
            $country_code   = $client_result['country_code'] ?? '';
        }
        else
        {
            $client_name = $client_result['name'] ?? null;
            $client_no   = $client_result['org_no'] ?? null; 
            $extracted_client_no = $client_result['og_org_no'] ?? null; 
            $country_code   = $client_result['country_code'] ?? '';
        } 

        if(!$client_no)
        {
            $rawVatNumber = $doc['Client Vat Number']['valueString']
                ?? OcrFallbackFieldExtractor::clientNumber($content);

            $orgNo = OrgNoNormalizer::normalize($rawVatNumber, $supplierName);  

            $client_result = app(ClientResolver::class)->resolve(
                $clients,
                $supplierName,
                $orgNo,
                null
            );
            
            $client_name = $client_result['name'] ?? null;
            $client_no   = $client_result['org_no'] ?? null; 
            $extracted_client_no = $client_result['og_org_no'] ?? null; 
            $country_code   = $client_result['country_code'] ?? ''; 
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

        $noInvoiceNumber = $doc['NO Invoice Number']['valueString'] ?? null;
        $noInvoiceNumber = trim($noInvoiceNumber ?? '') ?: null;        

        if($client_name && stripos($client_name, 'dfi-geisler') !== false)
            $invoiceNumber = !empty($invoiceNumber)
                                ? $invoiceNumber
                                : ($invoiceDate ? str_replace('-', '', $invoiceDate) : null);
        else if($client_name && stripos($client_name, 'rainwear') !== false)
        {
            if ($invoiceNumber && preg_match("/\r\n|\r|\n/", $invoiceNumber)) 
            {
                $arr_invoiceNumber = preg_split("/\r\n|\r|\n/", $invoiceNumber);
                if(count($arr_invoiceNumber) >= 2)
                {
                    $invoiceNumber = $arr_invoiceNumber[1];
                    $noInvoiceNumber = $arr_invoiceNumber[0];
                }
            }                
        }  

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

        $exchange_currency = $doc['Exchange Currency']['valueString'] ?? null;

        [$og_exchange_currency, $exchange_net_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Exchange Net Amount']['valueString'] ?? null,
            $exchange_currency
        );
   
        if($og_exchange_currency)     
            $exchange_currency = CurrencyHelper::parseCurrency($og_exchange_currency);
        else
            $exchange_currency = CurrencyHelper::parseCurrency($exchange_currency);

        [$vat_currency, $vat_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Vat Amount']['valueString'] ?? null,
            $currency ?? null
        );

        [$exchange_vat_currency, $exchange_vat_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Exchange Vat Amount']['valueString'] ?? null,
            $exchange_currency ?? null
        );

        if($client_name && stripos($client_name, 'engel') !== false)
        {            
            if($exchange_currency && !$exchange_vat_amount)
            {              
                if(trim($exchange_currency) == "0 NOK")
                {                    
                    $exchange_currency = str_replace('0 ', '', trim($exchange_currency)); 
                    $exchange_vat_amount = "0";            
                }
                else
                {
                    $exchange_fallback = OcrFallbackFieldExtractor::exchangeVatAmount($content);
                    $exchange_vat_amount = (!is_array($exchange_fallback)) ? $exchange_fallback : null;
                }
            }
            else if(!$exchange_currency && !$exchange_vat_amount)
            {
                $exchange_fallback = OcrFallbackFieldExtractor::exchangeVatAmount($content, true);

                if($exchange_fallback)
                {
                    if (is_array($exchange_fallback))
                    {
                        $exchange_currency = $exchange_fallback['currency'];
                        $exchange_vat_amount = $exchange_fallback['amount'];
                    }
                }
            }
        }

        [$discount_currency, $discount_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Discount Amount']['valueString'] ?? null,
            $currency ?? null
        );
        
        [$additional_charges_currency, $additional_charges] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Additional Charges']['valueString'] ?? null,
            $currency ?? null
        );
        if($client_name && stripos($client_name, 'adag') !== false)
        {
            $sum = 0;
            $parts = array_values(array_filter(array_map('trim', explode("\n", $additional_charges))));
            foreach ($parts as $part) {
                if (is_numeric($part)) {
                    $sum += (float)$part;
                }
            }

            $additional_charges = $sum; // 500
        }

        [$variance_currency, $variance] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Variance']['valueString'] ?? null,
            $currency ?? null
        );

        [$total_currency, $total_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Total Amount']['valueString'] ?? null,
            $currency ?? null
        );        

        $net_amount = EuropeanNumberHelper::normalize(
            $net_amount ?? null
        );

        $discount_amount = EuropeanNumberHelper::normalize(
            $discount_amount ?? null
        );

        $additional_charges = EuropeanNumberHelper::normalize(
            $additional_charges ?? null
        );

        $variance = EuropeanNumberHelper::normalize(
            $variance ?? null
        );

        if (strpos($vat_amount, "\n") !== false) {
            $parts = array_map('trim', preg_split('/\r\n|\r|\n/', $vat_amount));

            $vat_rate   = $parts[0] ?? null;
            $vat_amount = $parts[1] ?? null;
        } else {
            $vat_amount = trim($vat_amount);
        }    

        $vat_amount = EuropeanNumberHelper::normalize(
            $vat_amount ?? null
        );

        $total_amount = EuropeanNumberHelper::normalize(
            $total_amount ?? null
        );

        $parseNetAmount = EuropeanNumberHelper::toFloat($net_amount);
        $parseAdditionalCharges = EuropeanNumberHelper::toFloat($additional_charges);
        $parseVariance = EuropeanNumberHelper::toFloat($variance);
        $parseDiscountAmount = EuropeanNumberHelper::toFloat($discount_amount);
        $parseVatAmount = EuropeanNumberHelper::toFloat($vat_amount);
        $parseTotalAmount = EuropeanNumberHelper::toFloat($total_amount);

        if($client_name && (stripos($client_name, 'sgi wholesale') !== false
                || stripos($client_name, 'sand cph') !== false
            )
        )       
            $calcParseNetAmount = (abs($parseNetAmount) + abs($parseAdditionalCharges)) - (abs($parseVariance) + abs($parseDiscountAmount));
        else
            $calcParseNetAmount = (abs($parseNetAmount) + abs($parseAdditionalCharges) + abs($parseVariance)) - abs($parseDiscountAmount);

        /**
         * Net amount should never be greater than total amount.
         * If it is, OCR likely swapped them.
         */
        // if(!$credit_note)
        // {
        //     if (
        //         $parseNetAmount > 0 &&
        //         $parseVatAmount > 0 &&
        //         $parseTotalAmount <= 0
        //     ) {
            if (
                abs($parseNetAmount) > 0 &&
                abs($parseVatAmount) > 0 &&
                abs($parseTotalAmount) <= 0
            ) {
                // Calculate total directly
                $parseTotalAmount = $calcParseNetAmount + abs($parseVatAmount);
                $total_amount = $parseTotalAmount;

                $total_amount = EuropeanNumberHelper::normalize(
                    $total_amount ?? null
                );
            }

            // if ($calcParseNetAmount > $parseTotalAmount) {
            //     [$parseNetAmount, $parseTotalAmount] = [
            //         $parseTotalAmount,
            //         $parseNetAmount
            //     ];

            //     [$net_amount, $total_amount] = [
            //         $total_amount,
            //         $net_amount
            //     ];
            // }
        //}

        if (abs($parseTotalAmount) != 0 && (abs($calcParseNetAmount) > abs($parseTotalAmount))) {
            [$parseNetAmount, $parseTotalAmount] = [
                $parseTotalAmount,
                $parseNetAmount
            ];
            
            $calcParseNetAmount = abs($parseNetAmount);

            [$net_amount, $total_amount] = [
                $total_amount,
                $net_amount
            ];            
        }
        
        if (
            abs($parseVatAmount) > 0  && abs($parseTotalAmount) > 0 && (abs($parseTotalAmount) - abs($parseVatAmount)) < 0.01
        ) {            
            $net_amount = $total_amount;
        }        
   
        if (blank($net_amount)) {            
            //if ($parseVatAmount && $parseTotalAmount) {
            if ($parseVatAmount !== '' && $parseTotalAmount !== null) {
                $calcNetAmount = abs($parseTotalAmount) - abs($parseVatAmount);
                
                if($calcNetAmount == 0)
                    $net_amount = number_format(
                        $calcNetAmount,
                        2,
                        ',',
                        '.'
                    );
                else
                    $net_amount = EuropeanNumberHelper::normalize(
                        $calcNetAmount ?? null
                    );
            }
        }

        $exchange_net_amount = EuropeanNumberHelper::normalize(
            $exchange_net_amount ?? null
        );

        $exchange_vat_amount = EuropeanNumberHelper::normalize(
            $exchange_vat_amount ?? null
        );

        $vat_rate = VatRateHelper::resolve(
            $doc['Vat Rate']['valueString'] ?? null,
            $calcParseNetAmount,
            abs($parseVatAmount)
        );
 
        $parseExchangeVatAmount = EuropeanNumberHelper::toFloat($exchange_vat_amount);

        $exchange_rate = ExchangeRateHelper::normalize(
            $doc['Exchange Rate']['valueString'] ?? null
        );       
        $parseExchangeRate = str_replace(',', '.', $exchange_rate);

        /*
        |--------------------------------------------------------------------------
        | Determine local currency from country code
        |--------------------------------------------------------------------------
        */       
        $localCurrencies = match (strtolower($country_code ?? '')) {
            'no' => ['NOK'],
            'gb' => ['GBP'],
            'ch' => ['CHF', 'EUR'],
            default => [],
        };

        $reportCurrency = match (strtolower($country_code ?? '')) {
            'no' => 'NOK',
            'gb' => 'GBP',
            'ch' => 'CHF',
            default => null,
        };

        /*
        |--------------------------------------------------------------------------
        | Determine exchange currency
        |--------------------------------------------------------------------------
        |
        | Exchange currency should represent the client's local currency.
        | If OCR didn't extract it, fall back to the country-derived currency.
        |
        */
        $effectiveExchangeCurrency = $exchange_currency;
       
        if (
            empty($effectiveExchangeCurrency) &&
            !empty($reportCurrency) &&
            !empty($currency) &&
            !in_array($currency, $localCurrencies, true)
        ) {
            $effectiveExchangeCurrency = $reportCurrency;
        }
      
        /*
        |--------------------------------------------------------------------------
        | Foreign invoice?
        |--------------------------------------------------------------------------
        */       
        $isForeignInvoice =
            !empty($currency) &&
            !in_array($currency, $localCurrencies, true);

        /*
        |--------------------------------------------------------------------------
        | Eligible for automatic VAT FX calculation?
        |--------------------------------------------------------------------------
        |
        | Only calculate exchange values when:
        | - invoice currency differs from local currency
        | - local currency is known
        | - VAT exists in both currencies
        |
        */
        $isEligibleVatFx =
            $isForeignInvoice &&          
            !empty($reportCurrency) &&
            $effectiveExchangeCurrency === $reportCurrency //&&
            //abs($parseVatAmount) > 0 &&
            //abs($parseExchangeVatAmount) > 0
            ;    

        if ($isForeignInvoice && $isEligibleVatFx) {

            /*
            |--------------------------------------------------------------------------
            | Calculate exchange rate ONLY if missing
            |--------------------------------------------------------------------------
            */
            //if (empty($exchange_rate)) {
            if ($parseExchangeRate === null || $parseExchangeRate === '') {
                //if($parseVatAmount && $parseExchangeVatAmount)
                if ($parseVatAmount !== '' && $parseVatAmount !== null &&
                    $parseExchangeVatAmount !== '' && $parseExchangeVatAmount !== null
                    && $exchange_currency
                )
                {
                    $exchange_rate = ExchangeRateHelper::calculateExchangeRateFromVat(
                        abs($parseExchangeVatAmount),
                        abs($parseVatAmount)
                    ); 
                    $parseExchangeRate = str_replace(',', '.', $exchange_rate);

                    $exchange_rate = number_format(
                        (float)$parseExchangeRate,
                        4,
                        ',',
                        '.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate exchange net ONLY if missing
            |--------------------------------------------------------------------------
            */
            // if (
            //     empty($exchange_net_amount) &&
            //     $exchange_rate &&
            //     abs($parseNetAmount) > 0
            // ) {
            if (
                empty($exchange_net_amount) &&
                $parseExchangeRate !== '' && $parseExchangeRate !== null &&               
                $parseNetAmount !== '' && $parseNetAmount !== null
            ) {
                if (is_numeric($parseNetAmount) && is_numeric($parseExchangeRate)) {
                    $exchange_net_amount = number_format(
                        abs($parseNetAmount) * $parseExchangeRate,
                        2,
                        ',',
                        '.'
                    );
                }
                else
                {
                    Log::info([
                        "error" => "Non-numeric format",
                        "parseNetAmount" => $parseNetAmount,
                        "parseExchangeRate" => $parseExchangeRate,
                    ]);
                }
            }
           
            /*
            |--------------------------------------------------------------------------
            | Calculate exchange VAT ONLY if missing
            |--------------------------------------------------------------------------
            */
            // if (
            //     empty($exchange_vat_amount) &&
            //     $exchange_rate &&
            //     abs($parseVatAmount) > 0            
            // ) {
            if (
                empty($exchange_vat_amount) &&
                $parseExchangeRate !== '' && $parseExchangeRate !== null &&   
                $parseVatAmount !== '' && $parseVatAmount !== null
            ) {
                if (is_numeric($parseVatAmount) && is_numeric($parseExchangeRate)) {
                    $exchange_vat_amount = number_format(
                        abs($parseVatAmount) * $parseExchangeRate,
                        2,
                        ',',
                        '.'
                    );
                }
                else
                {
                    Log::info([
                        "error" => "Non-numeric format",
                        "parseVatAmount" => $parseVatAmount,
                        "parseExchangeRate" => $parseExchangeRate,
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate exchange total ONLY if missing
        |--------------------------------------------------------------------------
        */
        if (
            empty($exchange_total_amount) &&
            $exchange_net_amount &&
            $exchange_vat_amount
        ) {

            $parseExchangeNetAmount = EuropeanNumberHelper::toFloat(
                $exchange_net_amount
            );

            $parseExchangeVatAmount = EuropeanNumberHelper::toFloat(
                $exchange_vat_amount
            );

            $exchange_total_amount = number_format(
                abs($parseExchangeNetAmount) + abs($parseExchangeVatAmount),
                2,
                ',',
                '.'
            );
        }                

        if ($exchange_rate !== null && $exchange_rate !== '' && (float)$parseExchangeRate === 0.0)            
            $exchange_rate = number_format(
                (float)$parseExchangeRate,
                4,
                ',',
                '.'
            );
   
        $mapresult = [
            'invoice_type' => $invoice_type,
            'invoice_number' => rtrim((string) $invoiceNumber, '.'), //$invoiceNumber ?? null,
            'no_invoice_number' => $noInvoiceNumber ?? null,
            'invoice_date'   => $invoiceDate ?? null,           
            'order_number'   => $doc['Order Number']['valueString'] ?? null,            
            'supplier' => $doc ? [
                'extracted_name' => $supplierName,    
                'name'    => $client_name ?? null,
                'address' => $doc['Client Address']['valueString'] ?? null,               
                'cvr_number'   => $doc['Client Vat Number']['valueString'] ?? null,
                'org_number'   => $client_no ?? null,
                'extracted_org_number' => $extracted_client_no,
            ] : null,                       
            'net_amount'   => $net_amount ?? null,
            'discount_amount'   => $discount_amount ?? null,
            'vat_rate'   => $vat_rate ?? null,
            'vat_amount'   => $vat_amount ?? null,
            'currency'   => $currency ?? null,
            'additional_charges'   => $additional_charges ?? null,
            'variance'   => $variance ?? null,
            'total_amount'   => $total_amount ?? null,          
            'credit_note'   => $credit_note,
            'exchange_rate'   => $exchange_rate ?? null,
            'exchange_currency' => $effectiveExchangeCurrency ?? null,
            'exchange_net_amount'   => $exchange_net_amount ?? null,
            'exchange_vat_amount'   => $exchange_vat_amount ?? null,
            'exchange_total_amount'   => $exchange_total_amount ?? null            
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
        
        if (!$invoiceNumber)
            $error_message .= "Invoice no. missing\n";

        if (!$currency)
            $error_message .= "Currency missing\n";
        
        if (
            !empty($reportCurrency) &&
            !empty($currency) &&
            !in_array($currency, $localCurrencies, true)
        ) {
            if (
                !$exchange_rate ||
                !$effectiveExchangeCurrency ||
                !$exchange_net_amount ||
                !$exchange_vat_amount ||
                !$exchange_total_amount
            ) {
                $error_message .= "Exchange fields missing\n";
            }
        }
// Log::info("currency: " . $currency);
// Log::info("net_amount: " . $net_amount);
// Log::info("vat_amount: " . $vat_amount);
// Log::info("vat_rate: " . $vat_rate);        
// Log::info("total_amount: " . $total_amount);

// Log::info("exchange_currency: " . $exchange_currency);
// Log::info("exchange_rate: " . $exchange_rate);
// Log::info("exchange_net_amount: " . $exchange_net_amount);
// Log::info("exchange_vat_amount: " . $exchange_vat_amount);
// Log::info("exchange_total_amount: " . ($exchange_total_amount ?? null));

        //if (!$net_amount)
        if (blank($net_amount))
            $error_message .= "Net Amount missing\n";        

        //$vat_amount_value = str_replace(',', '.', trim($vat_amount));
        //if (blank($vat_amount) || (float) $vat_amount_value == 0.0)
        if (blank($vat_amount))
            $error_message .= "VAT Amount missing\n";
        
        //if(!$vat_rate)
        if (blank($vat_rate))
            $error_message .= "VAT Rate missing\n";

        if ($error_message) {
            $mapresult['error'] = $error_message;
        }

        $validInvoiceType = collect($comInvoiceTypes)->contains(function ($type) use ($invoice_type, $vat_rate, $invoiceNumber) {          
            if(strtolower($invoice_type) == "rechnung" && $vat_rate)
                return false;
            else if(strtolower($invoice_type) == "rechnung" && 
                Str::startsWith(Str::lower($invoiceNumber), ['ch'])
            )
                return false;
            else
                return str_contains(strtolower($invoice_type), $type);
        });

        // if($client_name && (stripos($client_name, 'engel') !== false
        //         || stripos($client_name, 'guardian') !== false
        //         || stripos($client_name, 'berendsohn') !== false
        //     )            
        // )
        // {
        //     $validInvoiceType = OcrFallbackFieldExtractor::salesInvoiceType($content);
        // }

        // if (!$invoice_type)        
        // {
            $validInvoiceType = OcrFallbackFieldExtractor::checkInvoiceType($content);

            // if($validInvoiceType)
            //     $mapresult['change_invoice_type'] = true;            
        //}

        if ($vat_rate == "100" || $validInvoiceType) {             
            $mapresult['change_invoice_type'] = true;
        }
    
        // if($client_name && (stripos($client_name, 'vernon') !== false)            
        // )
        // {
        //     $vatBase = OcrFallbackFieldExtractor::checkVatBase($content, 'base');

        //     // Log::info("vatBase: " . $vatBase);
        //     // Log::info("invoiceNumber: " . $invoiceNumber);
            
        //     if(Str::startsWith(Str::lower($invoiceNumber), ['ex']) && !$vatBase)
        //         $mapresult['change_invoice_type'] = true;
        //     else
        //         unset($mapresult['change_invoice_type']);
        // }        

        if($client_name)
        {
            if(stripos($client_name, 'vernon') !== false)
            {
                $vatBase = OcrFallbackFieldExtractor::checkVatBase($content, 'base');
                
                if(Str::startsWith(Str::lower($invoiceNumber), ['ex']) && !$vatBase)
                    $mapresult['change_invoice_type'] = true;
                else
                    unset($mapresult['change_invoice_type']);
            }
            else if(stripos($client_name, 'committee xxiv') !== false)
            {
                $vatBase = OcrFallbackFieldExtractor::checkVatBase($content, 'amount');
                
                if($vatBase)
                    unset($mapresult['change_invoice_type']);
                else
                {
                    $vatBase = OcrFallbackFieldExtractor::checkVatBase($content, 'rate');

                    if($vatBase)
                        unset($mapresult['change_invoice_type']);
                    else    
                        $mapresult['change_invoice_type'] = true;
                }
            }            
        }
   
        $chkSpecificText = OcrFallbackFieldExtractor::chkSpecificText($content, 'samsoe samsoe');        
        if($chkSpecificText)
        {            
            if (blank($vat_amount))
                $mapresult['change_invoice_type'] = true;
            else
                unset($mapresult['change_invoice_type']);            
        }

        if ($invalid_invoice_type !== null)
        {            
            $mapresult['invalid_invoice_type'] = true;            
        }
   
        return $mapresult;
    }
}