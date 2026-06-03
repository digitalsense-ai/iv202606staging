<?php

namespace App\Mappers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ComInvoiceMapper
{
    public static function map(array $result): array
    {
        //$doc = $result['result']['documents'][0]['fields'] ?? [];
        $doc = $result['result']['contents'][0]['fields'] ?? [];

        $recipient = $doc ?? null;
        //$customer = $doc['Customer']['valueObject'] ?? null;
        //$delivery = $doc['Delivery']['valueObject'] ?? null;

        $products = [];
        foreach ($doc['ProductLines']['valueArray'] ?? [] as $product) {
            $row = $product['valueObject'] ?? [];

            $products[] = [
                'product_number' => $row['ProductNumber']['valueString'] ?? null,
                'description' => $row['ProductDescription']['valueString'] ?? null,
                'code' => $row['ProductCode']['valueString'] ?? null,
                'country_of_origin' => $row['CountryOfOrigin']['valueString'] ?? null,
                'package_count'    => $row['PackageCount']['valueNumber'] ?? null,
                'quantity'    => $row['Quantity']['valueNumber'] ?? null,
                'unit_price'  => $row['LineAmount']['valueNumber'] ?? null,
                'weight'      => $row['Weight']['valueNumber'] ?? null,
            ];
        }

        // $custom_items = [];
        // foreach ($doc['CustomsItems']['valueArray'] ?? [] as $custom_item) {
        //     $row = $custom_item['valueObject'] ?? [];

        //     $custom_items[] = [
        //         'customs_code' => $row['CustomsCode']['valueString'] ?? null,
        //         'origin_country' => $row['OriginCountry']['valueString'] ?? null,
        //         'package_count' => $row['PackageCount']['valueNumber'] ?? null,
        //         'weight' => $row['Weight']['valueNumber'] ?? null,
        //         'customs_amount'    => $row['CustomsAmount']['valueNumber'] ?? null                
        //     ];
        // }

        $related_sales_invoices = [];
        foreach ($doc['RelatedSalesInvoices']['valueArray'] ?? [] as $related_sales_invoice) {            
            $related_sales_invoices[] = $related_sales_invoice['valueString'] ?? null;
        }

        $related_shipment_nos = [];
        foreach ($doc['RelatedShipmentNumbers']['valueArray'] ?? [] as $related_shipment_no) {            
            $related_shipment_nos[] = $related_shipment_no['valueString'] ?? null;
        }

        $related_sales_orders = [];
        foreach ($doc['RelatedSalesOrders']['valueArray'] ?? [] as $related_sales_order) {            
            $related_sales_orders[] = $related_sales_order['valueString'] ?? null;
        }        

        $invoiceDate = $doc['InvoiceDate']['valueDate'] ?? null;
Log::info("Com. Invoice date: " . $invoiceDate);
        $clientsWithDateIssue = [
            "our units", 
            "vernon", 
        ];

        $recipientName = $recipient['RecipientName']['valueString'] ?? '';

        $hasDateIssue = collect($clientsWithDateIssue)
            ->contains(fn($name) => stripos($recipientName, $name) !== false);

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
            Log::info("CORRECTED Com. Invoice date: " . $invoiceDate);
        }        
//Log::info("Invoice Amount: ",  $doc);
        return [
            'invoice_number' => $doc['InvoiceNumber']['valueString'] ?? null,
            'no_invoice_number' => $doc['NOInvoiceNumber']['valueString'] ?? null,
            //'invoice_date'   => $doc['InvoiceDate']['valueDate'] ?? null,
            'invoice_date'   => $invoiceDate ?? null,
            'order_number'   => $doc['OrderNumber']['valueString'] ?? null,
            'payment_terms'   => $doc['PaymentTerms']['valueString'] ?? null,
            'reference'   => $doc['Reference']['valueString'] ?? null,
            'incoterm'   => $doc['Incoterm']['valueString'] ?? null,                    
            'recipient' => $recipient ? [
                'name'    => $recipient['RecipientName']['valueString'] ?? null,
                'address' => $recipient['RecipientAddress']['valueString'] ?? null,
                'phone_number'  => $recipient['RecipientPhoneNumber']['valueString'] ?? null,
                'fax_number'   => $recipient['RecipientFaxNumber']['valueString'] ?? null,
                'website'   => $recipient['RecipientWebsite']['valueString'] ?? null,
                'email'   => $recipient['RecipientEmail']['valueString'] ?? null,
                'bank_name'   => $recipient['BankName']['valueString'] ?? null,
                'bank_account_number'   => $recipient['BankAccountNumber']['valueString'] ?? null,
                'swift'   => $recipient['BankSwiftCode']['valueString'] ?? null,
                'iban'   => $recipient['BankIban']['valueString'] ?? null,
                'vat_number'   => $recipient['RecipientVatNumber']['valueString'] ?? null,
                'cvr_number'   => $recipient['RecipientCvrNumber']['valueString'] ?? null,
            ] : null,
            // 'customer' => $customer ? [
            //     'name'    => $customer['Name']['valueString'] ?? null,
            //     'account_number'   => $customer['AccountNumber']['valueString'] ?? null,
            //     'address' => $customer['Address']['valueString'] ?? null                
            // ] : null,
            // 'delivery' => $delivery ? [
            //     'name'    => $delivery['Name']['valueString'] ?? null,
            //     'account_number'   => $delivery['AccountNumber']['valueString'] ?? null,
            //     'address' => $delivery['Address']['valueString'] ?? null                
            // ] : null,
            'product_lines'          => $products,
            'related_sales_invoices'   => $related_sales_invoices,
            'related_shipment_nos'   => $related_shipment_nos,
            'related_sales_orders'   => $related_sales_orders,
            //'customs_items'          => $custom_items, 
            'total_package_count'   => $doc['TotalPackageCount']['valueString'] ?? null,
            'total_amount'   => $doc['TotalAmountNok']['valueString'] ?? null,
            'total_weight'   => $doc['TotalWeight']['valueString'] ?? null,

            // 'net_amount'   => $doc['NetAmount']['valueNumber'] ?? null,
            // 'discount_amount'   => $doc['DiscountAmount']['valueNumber'] ?? null,
            // 'vat_rate'   => $doc['VatRate']['valueNumber'] ?? null,
            // 'vat_amount'   => $doc['VatAmount']['valueNumber'] ?? null,
            'currency'   => $doc['Currency']['valueString'] ?? null,
            // 'additional_charges'   => $doc['AdditionalCharges']['valueNumber'] ?? null,
            // 'total_amount'   => $doc['TotalAmount']['valueNumber'] ?? null,
            // 'credit_note'   => $doc['CreditNote']['valueBoolean'] ?? null            
        ];
    }
}