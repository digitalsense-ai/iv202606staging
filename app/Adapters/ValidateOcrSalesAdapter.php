<?php

namespace App\Adapters;

class ValidateOcrSalesAdapter
{
    public function fromExtracted(array $data): array
    {
        return [
            'Invoice Type' => [
                'valueString' => $data['invoice_type'] ?? null
            ],
            'Invoice Number' => [
                'valueString' => $data['invoice_number'] ?? null
            ],
            'NO Invoice Number' => [
                'valueString' => $data['no_invoice_number'] ?? null
            ],
            'Invoice Date' => [
                'content' => $data['invoice_date'] ?? null
            ],
            'Order Number' => [
                'valueString' => $data['order_number'] ?? null
            ],
            'Client Name' => [
                'valueString' => $data['supplier']['name'] ?? null
            ],
            'Client Number' => [
                'valueString' => $data['supplier']['org_number'] ?? null
            ],
            'Client Address' => [
                'valueString' => $data['supplier']['address'] ?? null
            ],
            'Client Vat Number' => [
                'valueString' => $data['supplier']['cvr_number'] ?? null
            ],
            'Currency' => [
                'valueString' => $data['currency'] ?? null
            ],
            'Net Amount' => [
                'valueString' => $data['net_amount'] ?? null
            ],
            'Vat Rate' => [
                'valueString' => $data['vat_rate'] ?? null
            ],
            'Vat Amount' => [
                'valueString' => $data['vat_amount'] ?? null
            ],
            'Discount Amount' => [
                'valueString' => $data['discount_amount'] ?? null
            ],
            'Additional Charges' => [
                'valueString' => $data['additional_charges'] ?? null
            ],
            'Variance' => [
                'valueString' => $data['variance'] ?? null
            ],
            'Total Amount' => [
                'valueString' => $data['total_amount'] ?? null
            ],
            'Exchange Rate' => [
                'valueString' => $data['exchange_rate'] ?? null
            ],
            'Exchange Currency' => [
                'valueString' => $data['exchange_currency'] ?? null
            ],
            'Exchange Net Amount' => [
                'valueString' => $data['exchange_net_amount'] ?? null
            ],
            'Exchange Vat Amount' => [
                'valueString' => $data['exchange_vat_amount'] ?? null
            ],
        ];
    }
}