<?php

namespace App\Parsers;

use App\Parsers\Concerns\ExtractsCommercialReferences;

class BergToysInvoiceParser implements ClientInvoiceParserInterface
{
    use ExtractsCommercialReferences;

    private const SALES_INVOICE_PREFIXES = ['no', 'ch', 'uk', '2026'];
    private const BLOCKED_INVOICE_PREFIXES = ['chn'];
    private const SALES_ORDER_PREFIXES = ['81', '31', '52', '226'];

    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'berg toys') || ($clientNo && in_array($clientNo, ['934286723', '379603560', '292640361'], true))) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'berg toys');
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {
        $azurePayload = $this->normalizeAzurePayload($doc);

        if ($validate) {
            return $azurePayload;
        }

        $rows = $this->extractColumnAfterHeader(
            $result,
            "ItemID\nDescription\nQuantity\nNet weight per part",
            2,
            [
                0 => 'sales_invoice',
                1 => 'sales_order',
            ]
        );

        $tablePayload = [
            'related_sales_invoices' => $this->joinReferences(
                $this->normalizeReferenceList(
                    array_column($rows, 'sales_invoice'),
                    self::SALES_INVOICE_PREFIXES,
                    self::BLOCKED_INVOICE_PREFIXES
                )
            ),
            'related_sales_orders' => $this->joinReferences(
                $this->normalizeReferenceList(
                    array_column($rows, 'sales_order'),
                    self::SALES_ORDER_PREFIXES
                )
            ),
            'related_shipment_nos' => null,
        ];

        return $this->mergeReferencePayload($azurePayload, $tablePayload);
    }

    private function normalizeAzurePayload(array $doc): array
    {
        return [
            'related_sales_invoices' => $this->joinReferences(
                $this->normalizeReferenceList(
                    $doc['Related Sales Invoices']['valueString'] ?? null,
                    self::SALES_INVOICE_PREFIXES,
                    self::BLOCKED_INVOICE_PREFIXES
                )
            ),
            'related_sales_orders' => $this->joinReferences(
                $this->normalizeReferenceList(
                    $doc['Related Sales Orders']['valueString'] ?? null,
                    self::SALES_ORDER_PREFIXES
                )
            ),
            'related_shipment_nos' => $this->joinReferences(
                $this->normalizeReferenceList($doc['Related Shipment Numbers']['valueString'] ?? null)
            ),
        ];
    }
}
