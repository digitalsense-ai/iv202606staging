<?php

namespace App\Parsers;

use App\Parsers\Concerns\ExtractsCommercialReferences;

class BergToysInvoiceParser implements ClientInvoiceParserInterface
{
    use ExtractsCommercialReferences;

    private const HEADER = "ItemID\nDescription\nQuantity\nNet weight per part";
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

        $rows = $this->extractBergRows($result);

        $tablePayload = [
            'related_sales_invoices' => $this->joinReferences(
                array_column($rows, 'sales_invoice')
            ),
            'related_sales_orders' => $this->joinReferences(
                array_column($rows, 'sales_order')
            ),
            'related_shipment_nos' => null,
        ];

        return $this->mergeReferencePayload($azurePayload, $tablePayload);
    }

    private function normalizeAzurePayload(array $doc): array
    {
        return [
            'related_sales_invoices' => $this->joinReferences(
                $this->filterSalesInvoices(
                    $this->expandReferenceRanges($doc['Related Sales Invoices']['valueString'] ?? null)
                )
            ),
            'related_sales_orders' => $this->joinReferences(
                $this->filterSalesOrders(
                    $this->expandReferenceRanges($doc['Related Sales Orders']['valueString'] ?? null)
                )
            ),
            'related_shipment_nos' => $this->joinReferences(
                $this->normalizeReferenceList($doc['Related Shipment Numbers']['valueString'] ?? null)
            ),
        ];
    }

    private function extractBergRows(array $result): array
    {
        $rows = [];
        $content = $this->commercialContent($result);
        $afterHeader = $content;

        if (str_contains($content, self::HEADER)) {
            $parts = explode(self::HEADER, $content, 2);
            $afterHeader = $parts[1] ?? $content;
        }

        $tokens = [];
        foreach (preg_split('/\R/', $afterHeader) ?: [] as $line) {
            $line = trim(preg_replace('/\s+/', ' ', (string) $line));

            if ($line === '' || $this->isLikelyPageNoise($line) || $this->isRepeatedHeader($line, self::HEADER)) {
                continue;
            }

            foreach (preg_split('/\s+/', $line) ?: [] as $token) {
                $token = trim($token, " \t\n\r\0\x0B,.;:");

                if ($token === '') {
                    continue;
                }

                if ($this->isBergSalesInvoice($token) || $this->isBergSalesOrder($token)) {
                    $tokens[] = $token;
                }
            }
        }

        $currentInvoice = null;
        foreach ($tokens as $token) {
            if ($this->isBergSalesInvoice($token)) {
                $currentInvoice = $token;
                continue;
            }

            if ($this->isBergSalesOrder($token)) {
                $rows[] = [
                    'sales_invoice' => $currentInvoice,
                    'sales_order' => $token,
                ];
                $currentInvoice = null;
            }
        }

        return $rows;
    }

    private function filterSalesInvoices(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isBergSalesInvoice((string) $value)));
    }

    private function filterSalesOrders(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isBergSalesOrder((string) $value)));
    }

    private function isBergSalesInvoice(string $value): bool
    {
        $value = trim($value);

        if ($value === '' || $this->startsWithAny($value, self::BLOCKED_INVOICE_PREFIXES)) {
            return false;
        }

        return (bool) preg_match('/^(NO|CH|UK)\d{6,}$/i', $value)
            || (bool) preg_match('/^2026\d{4,}$/', $value);
    }

    private function isBergSalesOrder(string $value): bool
    {
        $value = trim($value);

        return (bool) preg_match('/^(81|31|52|226)\d{5,}$/', $value);
    }
}
