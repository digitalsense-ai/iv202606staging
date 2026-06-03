<?php

namespace App\Adapters;

class ValidateOcrCommercialAdapter
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
            'Invoice Date' => [
                'content' => $data['invoice_date'] ?? null
            ],
            'Client Name' => [
                'valueString' => $data['recipient']['name'] ?? null
            ],
            'Client Number' => [
                'valueString' => $data['recipient']['org_number'] ?? null
            ],
            'Client Address' => [
                'valueString' => $data['recipient']['address'] ?? null
            ],
            'Currency' => [
                'valueString' => $data['currency'] ?? null
            ],
            'Net Amount' => [
                'valueString' => $data['net_amount'] ?? null
            ],
            'Exchange Currency' => [
                'valueString' => $data['exchange_currency'] ?? null
            ],
            'Exchange Net Amount' => [
                'valueString' => $data['exchange_net_amount'] ?? null
            ],
            'Related Sales Invoices' => [
                'valueString' => $data['related_sales_invoices'] ?? null
            ],
            'Related Sales Orders' => [
                'valueString' => $data['related_sales_orders'] ?? null
            ],
            'Related Shipment Numbers' => [
                'valueString' => $data['related_shipment_nos'] ?? null
            ],
        ];
    }
}