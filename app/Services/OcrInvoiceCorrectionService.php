<?php

namespace App\Services;

use App\Models\OcrPdf;
use App\Models\OcrSyncStatus;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

use App\Repositories\ClientRepository;
use App\Services\ClientResolver;

use App\Helpers\EnvironmentHelper;

class OcrInvoiceCorrectionService
{
    private const LOCAL_CURRENCY_MAP = [
        'no' => 'NOK',
        'gb' => 'GBP',
        'uk' => 'GBP',
        'ch' => 'CHF',
        'dk' => 'DKK',
        'se' => 'SEK',
        'pl' => 'PLN',
        'de' => 'EUR',
        'fr' => 'EUR',
        'it' => 'EUR',
        'es' => 'EUR',
        'nl' => 'EUR',
        'be' => 'EUR',
        'ie' => 'EUR',
        'fi' => 'EUR',
        'at' => 'EUR',
        'pt' => 'EUR',
        'lu' => 'EUR',
    ];

    public function apply(OcrPdf $invoice, array $payload, bool $forceSubmitted = false, ?int $userId = null, bool $searchSave = false): array
    {        
        $data = $invoice->extracted_data ?? [];
        $invoiceType = Arr::get($payload, 'invoice_type', $invoice->invoice_type);
        $countryCode = $this->countryCode($payload, $data);

        $clientName = Arr::get($payload, 'client_name');

        if($clientName && (
                str_contains(strtolower($clientName), 'rainwear') 
                || str_contains(strtolower($clientName), 'engel') 
                || str_contains(strtolower($clientName), 'berendsohn')
                || str_contains(strtolower($clientName), 'horn bord')
            )
        )
        {

        }
        else
        {
            $invoiceNo = Arr::get($payload, 'invoice_no');
            $this->set($data, 'invoice_number', $invoiceNo);
        }

        //$this->set($data, 'invoice_type', $invoiceType);
        $this->set($data, 'country_code', $countryCode);
        $this->set($data, 'invoice_date', Arr::get($payload, 'invoice_date'));

        $this->set($data, 'currency', Arr::get($payload, 'currency'));
        $this->set($data, 'net_amount', $this->formatEuropeanAmount(Arr::get($payload, 'net_amount')));

        if ($invoiceType === 'com') {
            $this->set($data, 'recipient.org_number', Arr::get($payload, 'client_no'));
            $this->set($data, 'recipient.name', $clientName);

            $this->set($data, 'exchange_currency', Arr::get($payload, 'exchange_currency'));
            $this->set($data, 'exchange_net_amount', $this->formatEuropeanAmount(Arr::get($payload, 'exchange_net_amount')));
            $this->set($data, 'related_sales_invoices', $this->references(Arr::get($payload, 'related_sales_invoices', [])));

            data_forget($data, 'credit_note');
            data_forget($data, 'vat_rate');
            data_forget($data, 'vat_amount');
            data_forget($data, 'total_amount');
            data_forget($data, 'exchange_rate');
            data_forget($data, 'exchange_vat_amount');
            data_forget($data, 'exchange_total_amount');
        } else {
            $this->set($data, 'supplier.org_number', Arr::get($payload, 'client_no'));
            $this->set($data, 'supplier.name', $clientName);

            $currency = Arr::get($payload, 'currency');
            $exchangeCurrency = Arr::get($payload, 'exchange_currency');
            $exchangeRate = Arr::get($payload, 'exchange_rate');
            $netAmount = Arr::get($payload, 'net_amount');
            $exchangeNetAmount = Arr::get($payload, 'exchange_net_amount');
            $vatAmount = Arr::get($payload, 'vat_amount');
            $exchangeVatAmount = Arr::get($payload, 'exchange_vat_amount');
            $totalAmount = Arr::get($payload, 'total_amount');

            if (blank($totalAmount)) {
                $net = $this->parseAmount($netAmount);
                $vat = $this->parseAmount($vatAmount);

                if ($net !== null && $vat !== null) {
                    $totalAmount = $this->formatAmount($net + $vat);
                }
            }

            $exchangeTotalAmount = Arr::get($payload, 'exchange_total_amount');            

            if (blank($exchangeTotalAmount)) {
                $exchangeNet = $this->parseAmount($exchangeNetAmount);
                $exchangeVat = $this->parseAmount($exchangeVatAmount);

                if ($exchangeNet !== null && $exchangeVat !== null) {
                    $exchangeTotalAmount = $this->formatAmount($exchangeNet + $exchangeVat);
                }
            }

            if ($this->shouldSwapLocalCurrency($countryCode, $currency, $exchangeCurrency)) {                         
                [$currency, $exchangeCurrency] = [$exchangeCurrency, $currency];
                [$netAmount, $exchangeNetAmount] = [$exchangeNetAmount, $netAmount];
                [$vatAmount, $exchangeVatAmount] = [$exchangeVatAmount, $vatAmount];
                [$totalAmount, $exchangeTotalAmount] = [$exchangeTotalAmount, $totalAmount];

                if (blank($exchangeRate) && blank($exchangeNetAmount) && blank($exchangeVatAmount) && blank($exchangeTotalAmount)) {
                    $exchangeCurrency = null;
                }
            }

            if($searchSave)
                $originalNetAmount = Arr::get($payload, 'original_net_amount');
            else
                $originalNetAmount = $netAmount;

            $this->set($data, 'currency', $currency);
            $this->set($data, 'exchange_currency', $exchangeCurrency);
            //$this->set($data, 'net_amount', $this->formatEuropeanAmount($netAmount));
            $this->set($data, 'net_amount', $this->formatEuropeanAmount($originalNetAmount));
            $this->set($data, 'exchange_net_amount', $this->formatEuropeanAmount($exchangeNetAmount));
            $this->set($data, 'credit_note', (bool) Arr::get($payload, 'credit_note', false));
            $this->set($data, 'vat_rate', Arr::get($payload, 'vat_rate'));
            $this->set($data, 'vat_amount', $this->formatEuropeanAmount($vatAmount));
            $this->set($data, 'total_amount', $this->formatEuropeanAmount($totalAmount));
            $this->set($data, 'exchange_rate', $this->formatEuropeanAmount($exchangeRate, 4));
            $this->set($data, 'exchange_vat_amount', $this->formatEuropeanAmount($exchangeVatAmount));
            $this->set($data, 'exchange_total_amount', $this->formatEuropeanAmount($exchangeTotalAmount));
            //$this->set($data, 'related_sales_invoices', []);

            if($searchSave)
            {
                $discountAmount = Arr::get($payload, 'discount_amount');
                $additionalAmount = Arr::get($payload, 'additional_amount');
                $varianceAmount = Arr::get($payload, 'variance_amount');
                
                $this->set($data, 'discount_amount', $this->formatEuropeanAmount($discountAmount));
                $this->set($data, 'additional_charges', $this->formatEuropeanAmount($additionalAmount));
                $this->set($data, 'variance', $this->formatEuropeanAmount($varianceAmount));                
            }
        }

        $note = Arr::get($payload, 'note');
        // $this->set($data, 'manual_note', $note);
        // $this->set($data, 'manual_input.force_submitted', $forceSubmitted);
        // $this->set($data, 'manual_input.updated_at', now()->toDateTimeString());
        // $this->set($data, 'manual_input.updated_by', $userId);

        $missing = $this->missingRequiredFields($data, $invoiceType);
        $completed = empty($missing) || $forceSubmitted;
        if($completed)
            data_forget($data, 'error');
        else
            $this->set($data, 'error', implode("\n", $missing));

        $environment = EnvironmentHelper::getEnvironment();
        if($searchSave)       
            $invoice->update([
                'invoice_type' => ($invoiceType === '') ? 'multi-invoices' : $invoiceType,
                'extracted_data' => $data,
                'status' => $completed ? 'completed' : 'failed',
                'error' => $completed ? null : implode("\n", $missing),
                'validation_status' => $completed ? 'validated_with_changes' : 'not_yet_validated',
                //'sync_status' => 0,
                //'is_locked' => 0,
                'search_save_note' => $note,
                'force_submitted' => $forceSubmitted,
                'search_save_at' => now(),
                'search_save_by' => $userId,
                'search_save_environment' => $environment,
            ]);        
        else
            $invoice->update([
                'invoice_type' => ($invoiceType === '') ? 'multi-invoices' : $invoiceType,
                'extracted_data' => $data,
                'status' => $completed ? 'completed' : 'failed',
                'error' => $completed ? null : implode("\n", $missing),
                'validation_status' => $completed ? 'validated_with_changes' : 'not_yet_validated',
                //'sync_status' => 0,
                //'is_locked' => 0,
                'manual_note' => $note,
                'force_submitted' => $forceSubmitted,
                'manual_input_at' => now(),
                'manual_input_by' => $userId,
                'manual_input_environment' => $environment,
            ]);
        
        OcrSyncStatus::updateOrCreate(
            [
                'ocr_pdf_id' => $invoice->id,
                'environment' => $environment,
            ],
            [                    
                'sync_status' => 0,
                'is_locked' => 0,
            ]
        );

        return [
            'completed' => $completed,
            'missing' => $missing,
        ];
    }

    public function missingRequiredFields(array $data, string $invoiceType): array
    {
        $missing = [];

        foreach ([
            //'invoice_type' => 'Document type missing',
            'invoice_date' => 'Invoice Date missing',
            'invoice_number' => 'Invoice no. missing',
            'currency' => 'Currency missing',
            'net_amount' => 'Net amount missing',
        ] as $field => $message) {
            if (blank(data_get($data, $field))) {
                $missing[] = $message;
            }
        }

        // if (data_get($data, 'invoice_type') !== 'com' && blank(data_get($data, 'total_amount'))) {
        //     $missing[] = 'Total amount missing';
        // }
        // if (blank(data_get($data, 'recipient.org_number')) && blank(data_get($data, 'supplier.org_number'))) {
        //     $missing[] = 'Client no. missing';
        // }

        if (
            ($invoiceType === 'com' && blank(data_get($data, 'recipient.name'))) 
            || ($invoiceType !== 'com' && blank(data_get($data, 'supplier.name')))
        )
        {
            $missing[] = 'Client Name missing';
        }

        if (
            ($invoiceType === 'com' && blank(data_get($data, 'recipient.org_number'))) 
            || ($invoiceType !== 'com' && blank(data_get($data, 'supplier.org_number')))
        )
        {
            $missing[] = 'Client no. missing';

            if (
                ($invoiceType === 'com' && blank(data_get($data, 'recipient.extracted_org_number'))) 
                || ($invoiceType !== 'com' && blank(data_get($data, 'supplier.extracted_org_number')))
            )
            {
                $missing[] = 'Client No. missing - Invalid Client No.';
            }
        }  

        if (
            ($invoiceType === 'com')
        )
        {
            if (blank(data_get($data, 'related_sales_invoices')))
            {
                if (blank(data_get($data, 'related_sales_orders')))
                {
                    if (blank(data_get($data, 'related_shipment_nos')))
                    {
                        $missing[] = 'References missing';
                    }
                }
            }
        }      
       
        if (
            ($invoiceType !== 'com' && blank(data_get($data, 'vat_rate')))
        )
        {
            $missing[] = 'VAT Rate missing';
        }  

        return $missing;
    }

    private function shouldSwapLocalCurrency(?string $countryCode, ?string $currency, ?string $exchangeCurrency): bool
    {
        $localCurrency = self::LOCAL_CURRENCY_MAP[strtolower((string) $countryCode)] ?? null;

        // return $localCurrency
        //     && $currency === $localCurrency
        //     && $exchangeCurrency !== $localCurrency;

        return $localCurrency !== null
            && $exchangeCurrency !== null
            && $exchangeCurrency !== ''
            && $currency === $localCurrency
            && $exchangeCurrency !== $localCurrency;
    }

    private function countryCode(array $payload, array $data): ?string
    {
        $countryCode = Arr::get($payload, 'country_code')
            ?? data_get($data, 'country_code')
            ?? data_get($data, '_ocr.country_code')
            ?? data_get($data, 'recipient.country_code')
            ?? data_get($data, 'supplier.country_code');

        if(!$countryCode)   
        { 
            $clients = app(ClientRepository::class)->all();
            $client_result = app(ClientResolver::class)->resolve(
                $clients,
                Arr::get($payload, 'client_name'),
                Arr::get($payload, 'client_no'),
                null
            );
            $countryCode = $client_result['country_code'] ?? '';
        }
        return $countryCode;        
    }

    private function set(array &$data, string $key, $value): void
    {
        data_set($data, $key, $value);
    }

    private function references($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }

    private function parseAmount($value): ?float
    {
        if (blank($value)) {
            return null;
        }

        $value = str_replace('.', '', (string) $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function formatAmount(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }
    
    private function formatEuropeanAmount($amount, $decimal = 2): ?string
    {
        if ($amount === null || $amount === '') {
            return $amount;
        }

        if (is_string($amount)) {
            $amount = trim($amount);

            // Both separators exist
            if (str_contains($amount, ',') && str_contains($amount, '.')) {

                $lastComma = strrpos($amount, ',');
                $lastDot = strrpos($amount, '.');

                // US format: 7,573.17
                // Decimal separator is the last dot
                if ($lastDot > $lastComma) {
                    $amount = str_replace(',', '', $amount);
                }
                // European format: 7.573,17
                // Decimal separator is the last comma
                else {
                    $amount = str_replace('.', '', $amount);
                    $amount = str_replace(',', '.', $amount);
                }

            }
            // Only comma exists: 7573,17
            elseif (str_contains($amount, ',')) {
                $amount = str_replace(',', '.', $amount);
            }

            // Only dot exists: 7573.17
        }

        if (is_numeric($amount)) {
            return number_format((float) $amount, $decimal, ',', '.');
        }

        return $amount;
    }
}