<?php

namespace App\Mappers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InvoiceMapper
{
    public static function map(array $result): array
    {
        //$doc = $result['result']['documents'][0]['fields'] ?? [];
        $doc = $result['result']['contents'][0]['fields'] ?? [];

        $supplier = $doc['Supplier']['valueObject'] ?? null;
        $customer = $doc['Customer']['valueObject'] ?? null;
        $delivery = $doc['Delivery']['valueObject'] ?? null;

        $credit_note = false;
        $creditNote = $doc['CreditNote']['valueString'] ?? null;
        if($creditNote && (strtolower($creditNote) == 'kredittnota' || 
            strtolower($creditNote) == 'kreditnota' || 
            strtolower($creditNote) == 'creditnote' || 
            strtolower($creditNote) == 'credit note' || 
            strtolower($creditNote) == 'kredit nota' || 
            strtolower($creditNote) == 'kreditt nota' || 
            strtolower($creditNote) == 'kreditt' || 
            strtolower($creditNote) == 'kredit' || 
            strtolower($creditNote) == 'credit') ||
            (stripos($creditNote, 'kredit') !== false) || (stripos($creditNote, 'credit') !== false) ||
            strtolower($creditNote) == 'true')
            $credit_note = true;

        $items = [];
        foreach ($doc['InvoiceItems']['valueArray'] ?? [] as $item) {
            $row = $item['valueObject'] ?? [];

            $items[] = [
                'item_number' => $row['ItemNumber']['valueString'] ?? null,
                'description' => $row['Description']['valueString'] ?? null,
                'nobb' => $row['Nobb']['valueString'] ?? null,
                'ean_code' => $row['EanCode']['valueString'] ?? null,
                'quantity'    => $row['Quantity']['valueNumber'] ?? null,
                'unit_price'  => $row['UnitPrice']['valueNumber'] ?? null,
                'amount'      => $row['Amount']['valueNumber'] ?? null,
            ];
        }

        $custom_items = [];
        foreach ($doc['CustomsItems']['valueArray'] ?? [] as $custom_item) {
            $row = $custom_item['valueObject'] ?? [];

            $custom_items[] = [
                'customs_code' => $row['CustomsCode']['valueString'] ?? null,
                'origin_country' => $row['OriginCountry']['valueString'] ?? null,
                'package_count' => $row['PackageCount']['valueNumber'] ?? null,
                'weight' => $row['Weight']['valueNumber'] ?? null,
                'customs_amount'    => $row['CustomsAmount']['valueNumber'] ?? null                
            ];
        }

        $invoiceDate = $doc['InvoiceDate']['valueDate'] ?? null;
Log::info("Sales Invoice date: " . $invoiceDate);
        $clientsWithDateIssue = [
            "our units", 
            "vernon", 
        ];

        $supplierName = $supplier ? ($supplier['Name']['valueString'] ?? '') : '';

        $hasDateIssue = collect($clientsWithDateIssue)
            ->contains(fn($name) => stripos($supplierName, $name) !== false);

        if ($hasDateIssue) {
            // Split normalized date
            [$year, $month, $day] = explode('-', $invoiceDate);

            $year = (int) $year;
            $month = (int) $month;
            $day = (int) $day;

            $correctDay = substr($year, -2);
            $correctMonth = $month;
            $correctYear = '20' . $day;

            $invoiceDate = Carbon::createFromFormat('Y-m-d', "$correctYear-$correctMonth-$correctDay")
                                ->format('Y-m-d');
            Log::info("CORRECTED Sales Invoice date: " . $invoiceDate);
        }        

        $mapresult = [
            'invoice_number' => $doc['InvoiceNumber']['valueString'] ?? null,
            'no_invoice_number' => $doc['NOInvoiceNumber']['valueString'] ?? null,
            //'invoice_date'   => $doc['InvoiceDate']['valueDate'] ?? null,
            'invoice_date'   => $invoiceDate ?? null,
            'order_number'   => $doc['OrderNumber']['valueString'] ?? null,
            'payment_terms'   => $doc['PaymentTerms']['valueString'] ?? null,
            'reference'   => $doc['Reference']['valueString'] ?? null,
            'supplier' => $supplier ? [
                'name'    => $supplier['Name']['valueString'] ?? null,
                'address' => $supplier['Address']['valueString'] ?? null,
                'phone_number'  => $supplier['PhoneNumber']['valueString'] ?? null,
                'fax_number'   => $supplier['FaxNumber']['valueString'] ?? null,
                'website'   => $supplier['Website']['valueString'] ?? null,
                'email'   => $supplier['Email']['valueString'] ?? null,
                'bank_name'   => $supplier['BankName']['valueString'] ?? null,
                'bank_account_number'   => $supplier['BankAccountNumber']['valueString'] ?? null,
                'swift'   => $supplier['SWIFT']['valueString'] ?? null,
                'iban'   => $supplier['IBAN']['valueString'] ?? null,
                'cvr_number'   => $supplier['CVRNumber']['valueString'] ?? null,
                'org_number'   => $supplier['OrgNumber']['valueString'] ?? null,
            ] : null,
            'customer' => $customer ? [
                'name'    => $customer['Name']['valueString'] ?? null,
                'account_number'   => $customer['AccountNumber']['valueString'] ?? null,
                'address' => $customer['Address']['valueString'] ?? null                
            ] : null,
            'delivery' => $delivery ? [
                'name'    => $delivery['Name']['valueString'] ?? null,
                'account_number'   => $delivery['AccountNumber']['valueString'] ?? null,
                'address' => $delivery['Address']['valueString'] ?? null                
            ] : null,
            'invoice_items'          => $items,
            'customs_items'          => $custom_items,                                         
            'net_amount'   => $doc['NetAmount']['valueString'] ?? null,
            'discount_amount'   => $doc['DiscountAmount']['valueString'] ?? null,
            'vat_rate'   => $doc['VatRate']['valueNumber'] ?? null,
            'vat_amount'   => $doc['VatAmount']['valueString'] ?? null,
            'currency'   => $doc['Currency']['valueString'] ?? null,
            'additional_charges'   => $doc['AdditionalCharges']['valueString'] ?? null,
            'total_amount'   => $doc['TotalAmount']['valueString'] ?? null,
            //'credit_note'   => $doc['CreditNote']['valueBoolean'] ?? null,
            'credit_note'   => $credit_note ?? null,
            'exchange_rate'   => $doc['ExchangeRate']['valueString'] ?? null,
            'exchange_currency'   => $doc['ExchangeCurrency']['valueString'] ?? null,
            'exchange_net_amount'   => $doc['ExchangeNetAmount']['valueString'] ?? null,
            'exchange_vat_amount'   => $doc['ExchangeVatAmount']['valueString'] ?? null            
        ];
        
        return $mapresult;
    }
}