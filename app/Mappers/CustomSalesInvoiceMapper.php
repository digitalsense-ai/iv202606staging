<?php

namespace App\Mappers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Helpers\CreditNoteHelper;
use App\Helpers\DateHelper;
use App\Helpers\OrgNoNormalizer;
use App\Helpers\CurrencyHelper;
use App\Helpers\EuropeanNumberHelper;
use App\Helpers\VatRateHelper;
use App\Helpers\ExchangeRateHelper;

use App\Services\ClientResolver;

class CustomSalesInvoiceMapper
{
    public static function map(array $result, array $clients, string $passDate = null): array
    {    
        $doc = $result['analyzeResult']['documents'][0]['fields'] ?? [];
//Log::info($doc);  

        $credit_note = CreditNoteHelper::isCreditNote(
            $doc['Invoice Type']['valueString'] ?? null
        );

        $supplierName = $doc ? ($doc['Client Name']['valueString'] ?? '') : '';    
        $orgNo = OrgNoNormalizer::normalize(($doc['Client Number']['valueString'] ?? null), $supplierName);

        if(!$orgNo)
            $orgNo = OrgNoNormalizer::normalize(($doc['Client Vat Number']['valueString'] ?? null), $supplierName);

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

        $invoiceDate = DateHelper::parseInvoiceDate(
            $doc['Invoice Date']['content'] ?? null
        );

        $invoiceNumber = $doc['Invoice Number']['valueString'] ?? null;
        $invoiceNumber = trim($invoiceNumber ?? '') ?: null;        

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
        $currency = CurrencyHelper::parseCurrency($og_currency);

        [$og_exchange_currency, $exchange_net_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Exchange Net Amount']['valueString'] ?? null,
            $doc['Exchange Currency']['valueString'] ?? null
        );
        $exchange_currency = CurrencyHelper::parseCurrency($og_exchange_currency);

        [$vat_currency, $vat_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Vat Amount']['valueString'] ?? null,
            $currency ?? null
        );

        [$exchange_vat_currency, $exchange_vat_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Exchange Vat Amount']['valueString'] ?? null,
            $exchange_currency ?? null
        );

        [$discount_currency, $discount_amount] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Discount Amount']['valueString'] ?? null,
            $currency ?? null
        );

        [$additional_charges_currency, $additional_charges] = CurrencyHelper::extractCurrencyAndCleanAmount(
            $doc['Additional Charges']['valueString'] ?? null,
            $currency ?? null
        );

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

        $calcParseNetAmount = ($parseNetAmount + $parseAdditionalCharges + $parseVariance) - $parseDiscountAmount;
        
        /**
         * Net amount should never be greater than total amount.
         * If it is, OCR likely swapped them.
         */
        if(!$credit_note)
        {
            if ($calcParseNetAmount > $parseTotalAmount) {
                [$parseNetAmount, $parseTotalAmount] = [
                    $parseTotalAmount,
                    $parseNetAmount
                ];

                [$net_amount, $total_amount] = [
                    $total_amount,
                    $net_amount
                ];
            }
        }

        if (
            $parseVatAmount > 0  && $parseTotalAmount > 0 && abs($parseTotalAmount - $parseVatAmount) < 0.01
        ) {
            $net_amount = $total_amount;
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
            $parseVatAmount
        );
 
        $parseExchangeVatAmount = EuropeanNumberHelper::toFloat($exchange_vat_amount);
        
        $exchange_rate = ExchangeRateHelper::normalize(
            $doc['Exchange Rate']['valueString'] ?? null
        );
        
        /*
        |--------------------------------------------------------------------------
        | Determine local currency from country code
        |--------------------------------------------------------------------------
        */
        // $localCurrency = match (strtolower($country_code ?? '')) {
        //     'no' => 'NOK',
        //     'gb' => 'GBP',
        //     'ch' => 'CHF',
        //     default => null,
        // };

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

        // if (
        //     empty($effectiveExchangeCurrency) &&
        //     !empty($localCurrency) &&
        //     !empty($currency) &&
        //     $currency !== $localCurrency
        // ) {
        //     $effectiveExchangeCurrency = $localCurrency;
        // }

        if (
            empty($effectiveExchangeCurrency) &&
            !empty($reportCurrency) &&
            !empty($currency) &&
            !in_array($currency, $localCurrencies, true)
        ) {
            $effectiveExchangeCurrency = $reportCurrency;
        }

        // Log::info('country_code: ' . ($country_code ?? 'null'));
        // Log::info('localCurrency: ' . ($localCurrency ?? 'null'));
        // Log::info('currency: ' . ($currency ?? 'null'));
        // Log::info('effectiveExchangeCurrency: ' . ($effectiveExchangeCurrency ?? 'null'));

        /*
        |--------------------------------------------------------------------------
        | Foreign invoice?
        |--------------------------------------------------------------------------
        */
        // $isForeignInvoice =
        //     !empty($currency) &&
        //     !empty($effectiveExchangeCurrency) &&
        //     $currency !== $effectiveExchangeCurrency;

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
            //!empty($localCurrency) &&
            //$effectiveExchangeCurrency === $localCurrency &&
            !empty($reportCurrency) &&
            $effectiveExchangeCurrency === $reportCurrency &&
            $parseVatAmount > 0 &&
            $parseExchangeVatAmount > 0;

        if ($isForeignInvoice && $isEligibleVatFx) {

            /*
            |--------------------------------------------------------------------------
            | Calculate exchange rate ONLY if missing
            |--------------------------------------------------------------------------
            */
            if (empty($exchange_rate)) {

                $exchange_rate = ExchangeRateHelper::calculateExchangeRateFromVat(
                    $parseExchangeVatAmount,
                    $parseVatAmount
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate exchange net ONLY if missing
            |--------------------------------------------------------------------------
            */
            if (
                empty($exchange_net_amount) &&
                $exchange_rate &&
                $parseNetAmount > 0
            ) {

                $exchange_net_amount = number_format(
                    $parseNetAmount * $exchange_rate,
                    2,
                    ',',
                    '.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate exchange VAT ONLY if missing
            |--------------------------------------------------------------------------
            */
            if (
                empty($exchange_vat_amount) &&
                $exchange_rate &&
                $parseVatAmount > 0
            ) {

                $exchange_vat_amount = number_format(
                    $parseVatAmount * $exchange_rate,
                    2,
                    ',',
                    '.'
                );
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
                $parseExchangeNetAmount + $parseExchangeVatAmount,
                2,
                ',',
                '.'
            );
        }

// Log::info("net_amount: " . $net_amount);
// Log::info("discount_amount: " . $discount_amount);
// Log::info("vat_rate: " . $vat_rate);
// Log::info("vat_amount: " . $vat_amount);
// Log::info("currency: " . $currency);
// Log::info("additional_charges: " . $additional_charges);
// Log::info("variance: " . $variance);
// Log::info("total_amount: " . $total_amount);
        $mapresult = [
            'invoice_type' => $doc['Invoice Type']['valueString'] ?? null,
            'invoice_number' => $invoiceNumber ?? null,
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

            $invoiceDateCarbon = Carbon::parse($invoiceDate)->startOfDay();

            // Future invoice date
            if ($invoiceDateCarbon->gt($referenceDate)) {
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

        if (!$invoiceNumber)
            $error_message .= "Invoice no. missing\n";

        if (!$currency)
            $error_message .= "Currency missing\n";

        // if ($currency != 'NOK' && $currency != 'CHF' && $currency != 'GBP')
        // {
        //     if(!$exchange_rate 
        //         || !$effectiveExchangeCurrency 
        //         || !$exchange_net_amount 
        //         || !$exchange_vat_amount
        //         || !$exchange_total_amount
        //     )
        //         $error_message .= "Exchange fields missing\n";
        // }

        // Log::info('country_code: ' . ($country_code ?? 'null'));
        // Log::info('localCurrency: ' . ($localCurrency ?? 'null'));
        // Log::info('currency: ' . ($currency ?? 'null'));
        // Log::info('effectiveExchangeCurrency: ' . ($effectiveExchangeCurrency ?? 'null'));

        // if (
        //     $localCurrency &&
        //     $currency &&
        //     $currency !== $localCurrency
        // ) {
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

        if (!$net_amount)
            $error_message .= "Net Amount missing";        

        if ($error_message) {
            $mapresult['error'] = $error_message;
        }

        if ($vat_rate == "100") {
            $mapresult['change_invoice_type'] = true;
        }
     
        return $mapresult;
    }
}