<?php

namespace App\Parsers;

use App\Parsers\Concerns\ExtractsCommercialReferences;

class SecondFemaleInvoiceParser implements ClientInvoiceParserInterface
{
    use ExtractsCommercialReferences;

    private const SALES_INVOICE_PREFIXES = ['NO', 'CH', 'UK', 'OSL'];

    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'secondfemale') || str_contains($name, 'second female')) {
            return true;
        }

        $content = $this->normalizeHeaderText($result['analyzeResult']['content'] ?? '');

        return (
                (
                    str_contains($content, 'secondfemale')
                    || str_contains($content, 'second female')
                ) &&
                (
                    str_contains($content, 'org invoice no tracking no')
                    || str_contains($content, 'opr fakturanr tracking no')
                    || str_contains($content, 'fakturaen daekker over foelgende leverancer')
                )
            );
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
        $rows = [];
        $currentOrder = null;
        $currentInvoice = null;
        $currentShipments = [];

        foreach ($this->tokensFromContent($this->commercialContent($result)) as $token) {
            if ($this->isSalesOrder($token)) {
                $this->flushRow($rows, $currentOrder, $currentInvoice, $currentShipments);

                $currentOrder = $token;
                $currentInvoice = null;
                $currentShipments = [];
                continue;
            }

            if ($this->isSalesInvoice($token)) {
                if ($currentInvoice !== null && $currentInvoice !== $token) {
                    $this->flushRow($rows, $currentOrder, $currentInvoice, $currentShipments);
                    $currentShipments = [];
                }

                $currentInvoice = $token;
                continue;
            }

            if ($this->isShipment($token)) {
                $currentShipments[] = $token;
            }
        }

        $this->flushRow($rows, $currentOrder, $currentInvoice, $currentShipments);

        return $this->dedupeRows($rows);
    }

    private function tokensFromContent(string $content): array
    {
        preg_match_all($this->referencePattern(), $content, $matches);

        return array_values(array_map(
            fn ($token) => strtoupper(ltrim(trim($token), '+')),
            $matches[0] ?? []
        ));
    }

    private function flushRow(array &$rows, ?string $order, ?string $invoice, array $shipments): void
    {
        if (!$order && !$invoice && !$shipments) {
            return;
        }

        $rows[] = [
            'sales_order' => $order,
            'sales_invoice' => $invoice,
            'shipments' => array_values(array_unique($shipments)),
        ];
    }

    private function tokensFromLine(string $line): array
    {
        preg_match_all($this->referencePattern(), $line, $matches);

        return array_values(array_unique(array_map(
            fn ($token) => strtoupper(ltrim(trim($token), '+')),
            $matches[0] ?? []
        )));
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
        
        return '/\+?SL\d+|(?:' . $invoicePrefixes . ')\d+|92\d{8,}/i';
    }

    private function isDeliverySectionMarker(string $line): bool
    {
        $line = $this->normalizeHeaderText($line);

        return str_contains($line, 'this invoice includes the following deliveries')
            || str_contains($line, 'deliveries')
            || str_contains($line, 'fakturaen daekker over foelgende')
            || str_contains($line, 'leverancer')
            || str_contains($line, 'opr fakturanr tracking no')
            || str_contains($line, 'org invoice no tracking no');
    }

    private function isSecondFemaleHeaderOrNoise(string $line): bool
    {
        $normalized = $this->normalizeHeaderText($line);

        if (preg_match('/^(page\s+\d+|continued)$/i', $normalized)) {
            return true;
        }

        return str_contains($normalized, 'this invoice includes')
            || str_contains($normalized, 'fakturaen daekker')
            || str_contains($normalized, 'following deliveries')
            || str_contains($normalized, 'foelgende leverancer')
            || str_contains($normalized, 'deliveries')
            || str_contains($normalized, 'leverancer')
            || str_contains($normalized, 'org invoice no tracking no')
            || str_contains($normalized, 'opr fakturanr tracking no');
    }

    private function normalizeHeaderText(string $value): string
    {
        $value = strtolower(trim($value));
        $value = strtr($value, [
            'æ' => 'ae',
            'ø' => 'oe',
            'å' => 'aa',
            '^' => 'ae',
            '0' => 'o',
            '.' => '',
            ':' => '',
        ]);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function lineLooksLikeDeliveryRow(array $tokens): bool
    {
        if (!$tokens) {
            return false;
        }

        return (bool) array_filter($tokens, fn ($token) => $this->isSalesOrder((string) $token))
            || ((bool) array_filter($tokens, fn ($token) => $this->isSalesInvoice((string) $token))
                && (bool) array_filter($tokens, fn ($token) => $this->isShipment((string) $token)))
            || count(array_filter($tokens, fn ($token) => $this->isShipment((string) $token))) > 0;
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
        return (bool) preg_match('/^92\d{8,}$/', trim($value));
    }
}