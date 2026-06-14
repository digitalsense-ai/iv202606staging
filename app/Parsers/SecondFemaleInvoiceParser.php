<?php

namespace App\Parsers;

use App\Parsers\Concerns\ExtractsCommercialReferences;

class SecondFemaleInvoiceParser implements ClientInvoiceParserInterface
{
    use ExtractsCommercialReferences;

    private const SALES_INVOICE_PREFIXES = ['NO', 'CH', 'UK'];

    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'secondfemale') || str_contains($name, 'second female')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'secondfemale')
            || str_contains($content, 'second female')
            || str_contains($content, 'org. invoice no. tracking no');
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {
        $rows = $this->extractRowsFromContent($result);
        $fieldTokens = array_merge(
            $this->extractSecondFemaleTokens($doc['Related Sales Orders']['valueString'] ?? null),
            $this->extractSecondFemaleTokens($doc['Related Sales Invoices']['valueString'] ?? null),
            $this->extractSecondFemaleTokens($doc['Related Shipment Numbers']['valueString'] ?? null)
        );

        return [
            'related_sales_orders' => $this->joinReferences(array_merge(
                array_column($rows, 'sales_order'),
                $this->filterSalesOrders($fieldTokens)
            )),
            'related_sales_invoices' => $this->joinReferences(array_merge(
                array_column($rows, 'sales_invoice'),
                $this->filterSalesInvoices($fieldTokens)
            )),
            'related_shipment_nos' => $this->joinReferences(array_merge(
                array_merge(...array_map(fn ($row) => $row['shipments'] ?? [], $rows ?: [[]])),
                $this->filterShipments($fieldTokens)
            )),
        ];
    }

    private function extractRowsFromContent(array $result): array
    {
        $content = $this->commercialContent($result);

        if (preg_match('/This\s+Invoice\s+includes\s+the\s+following\s+deliveries\s*:/i', $content, $match, PREG_OFFSET_CAPTURE)) {
            $content = substr($content, $match[0][1]);
        }

        $rows = [];
        $current = null;

        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $line = trim(preg_replace('/\s+/', ' ', (string) $line));

            if ($line === '' || $this->isSecondFemaleHeaderOrNoise($line)) {
                continue;
            }

            preg_match_all($this->referencePattern(), $line, $matches);
            $tokens = array_values(array_map(fn ($token) => strtoupper(ltrim(trim($token), '+')), $matches[0] ?? []));

            if (!$tokens) {
                continue;
            }

            foreach ($tokens as $token) {
                if ($this->isSalesOrder($token)) {
                    if ($current) {
                        $rows[] = $current;
                    }

                    $current = [
                        'sales_order' => $token,
                        'sales_invoice' => null,
                        'shipments' => [],
                    ];
                    continue;
                }

                if ($this->isSalesInvoice($token)) {
                    if (!$current) {
                        $current = [
                            'sales_order' => null,
                            'sales_invoice' => null,
                            'shipments' => [],
                        ];
                    }

                    $current['sales_invoice'] = $token;
                    continue;
                }

                if ($this->isShipment($token)) {
                    if (!$current) {
                        $current = [
                            'sales_order' => null,
                            'sales_invoice' => null,
                            'shipments' => [],
                        ];
                    }

                    $current['shipments'][] = $token;
                }
            }
        }

        if ($current) {
            $rows[] = $current;
        }

        return $this->dedupeRows($rows);
    }

    private function dedupeRows(array $rows): array
    {
        $deduped = [];

        foreach ($rows as $row) {
            $row['shipments'] = array_values(array_unique($row['shipments'] ?? []));

            $key = implode('|', [
                $row['sales_order'] ?? '',
                $row['sales_invoice'] ?? '',
                implode(',', $row['shipments']),
            ]);

            $deduped[$key] = $row;
        }

        return array_values($deduped);
    }

    private function extractSecondFemaleTokens(array|string|null $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $content = is_array($value) ? implode("\n", $value) : (string) $value;

        preg_match_all($this->referencePattern(), $content, $matches);

        return array_values(array_unique(array_map(function ($token) {
            return strtoupper(ltrim(trim($token), '+'));
        }, $matches[0] ?? [])));
    }

    private function referencePattern(): string
    {
        $invoicePrefixes = implode('|', self::SALES_INVOICE_PREFIXES);

        return '/\+?SL\d+|(?:' . $invoicePrefixes . ')\d+|92\d{10,}/i';
    }

    private function isSecondFemaleHeaderOrNoise(string $line): bool
    {
        return (bool) preg_match('/^(this invoice includes|deliveries:?|org\. invoice no\. tracking no|org\.?\s+invoice\s+no\.?\s+tracking\s+no\.?)$/i', trim($line));
    }

    private function filterSalesOrders(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isSalesOrder((string) $value)));
    }

    private function filterSalesInvoices(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isSalesInvoice((string) $value)));
    }

    private function filterShipments(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isShipment((string) $value)));
    }

    private function isSalesOrder(string $value): bool
    {
        return (bool) preg_match('/^SL\d+$/i', trim($value));
    }

    private function isSalesInvoice(string $value): bool
    {
        $prefixes = implode('|', self::SALES_INVOICE_PREFIXES);

        return (bool) preg_match('/^(?:' . $prefixes . ')\d+$/i', trim($value));
    }

    private function isShipment(string $value): bool
    {
        return (bool) preg_match('/^92\d{10,}$/', trim($value));
    }
}
