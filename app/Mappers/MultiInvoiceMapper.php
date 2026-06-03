<?php

namespace App\Mappers;

class MultiInvoiceMapper
{
    public static function map(array $result): array
    {        
        $doc = $result['result']['contents'][0]['fields'] ?? [];

        $arr_invoices = $doc['Invoice']['valueArray'] ?? [];
        $recipient = $doc ?? null;
        //$customer = $doc['Customer']['valueObject'] ?? null;
        //$delivery = $doc['Delivery']['valueObject'] ?? null;

        $invoices = [];
        foreach ($arr_invoices as $arr_invoice) 
        {
            $invoice = $arr_invoice['valueObject'] ?? null;
            $supplier = $invoice['Supplier']['valueObject'] ?? null;
            $customer = $invoice['Customer']['valueObject'] ?? null;
            $delivery = $invoice['Delivery']['valueObject'] ?? null;

            $items = [];
            foreach ($invoice['InvoiceItems']['valueArray'] ?? [] as $item) {
                $row = $item['valueObject'] ?? [];

                $items[] = [
                    'item_number' => $row['ItemNumber']['valueString'] ?? null,
                    'description' => $row['Description']['valueString'] ?? null,
                    'nobb' => $row['Nobb']['valueString'] ?? null,
                    'ean_code' => $row['EanCode']['valueString'] ?? null,
                    'quantity'    => $row['Quantity']['valueNumber'] ?? null,
                    'unit_price'  => $row['UnitPrice']['valueString'] ?? null,
                    'amount'      => $row['Amount']['valueString'] ?? null,
                    'discount'      => $row['Discount']['valueNumber'] ?? null,
                ];
            }

            $custom_items = [];
            foreach ($invoice['CustomsItems']['valueArray'] ?? [] as $custom_item) {
                $row = $custom_item['valueObject'] ?? [];

                $custom_items[] = [
                    'customs_code' => $row['CustomsCode']['valueString'] ?? null,
                    'origin_country' => $row['OriginCountry']['valueString'] ?? null,
                    'package_count' => $row['PackageCount']['valueNumber'] ?? null,
                    'weight' => $row['Weight']['valueNumber'] ?? null,
                    'customs_amount'    => $row['CustomsAmount']['valueNumber'] ?? null                
                ];
            }

            $invoices[] = [
                'invoice_number' => $invoice['InvoiceNumber']['valueString'] ?? null,
                'no_invoice_number' => $invoice['NOInvoiceNumber']['valueString'] ?? null,
                'invoice_date'   => $invoice['InvoiceDate']['valueDate'] ?? null,
                'order_number'   => $invoice['OrderNumber']['valueString'] ?? null,
                'payment_terms'   => $invoice['PaymentTerms']['valueString'] ?? null,
                'reference'   => $invoice['Reference']['valueString'] ?? null,
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
                'net_amount'   => $invoice['NetAmount']['valueString'] ?? null,
                'discount_amount'   => $invoice['DiscountAmount']['valueNumber'] ?? null,
                'vat_rate'   => $invoice['VatRate']['valueNumber'] ?? null,
                'vat_amount'   => $invoice['VatAmount']['valueString'] ?? null,
                'currency'   => $invoice['Currency']['valueString'] ?? null,
                'additional_charges'   => $invoice['AdditionalCharges']['valueNumber'] ?? null,
                'total_amount'   => $invoice['TotalAmount']['valueString'] ?? null,
                'credit_note'   => $invoice['CreditNote']['valueBoolean'] ?? null,
                'exchange_rate'   => $invoice['ExchangeRate']['valueString'] ?? null,
                'exchange_currency'   => $invoice['ExchangeCurrency']['valueString'] ?? null,
                'exchange_net_amount'   => $invoice['ExchangeNetAmount']['valueString'] ?? null,
                'exchange_vat_amount'   => $invoice['ExchangeVatAmount']['valueString'] ?? null
            ];
        }

        return $invoices;
    }
}