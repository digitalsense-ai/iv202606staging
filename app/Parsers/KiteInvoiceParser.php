<?php

namespace App\Parsers;

use App\Parsers\Concerns\ExtractsCommercialReferences;

class KiteInvoiceParser implements ClientInvoiceParserInterface
{
    use ExtractsCommercialReferences;

    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'kite')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'kite');
    }

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    {
        $tableTokens = $this->tokensFromOrderTables($result['analyzeResult']['content'] ?? '');
        $fieldTokens = array_merge(
            $this->extractKiteTokens($doc['Related Sales Orders']['valueString'] ?? null),
            $this->extractKiteTokens($doc['Related Sales Invoices']['valueString'] ?? null),
            $this->extractKiteTokens($doc['Related Shipment Numbers']['valueString'] ?? null)
        );

        $tokens = $tableTokens ?: $fieldTokens;

        return [
            'related_sales_invoices' => $this->joinReferences($this->filterKiteSalesInvoices($tokens)),
            'related_sales_orders' => $this->joinReferences($this->filterKiteSalesOrders($tokens)),
            'related_shipment_nos' => $this->joinReferences($this->filterKiteShipments($tokens)),
        ];
    }

    private function tokensFromOrderTables(string $content): array
    {
        $tokens = [];

        foreach ($this->kiteOrderTableSections($content) as $section) {
            preg_match_all('/(?:25\d{9}|10\d{9}|92\d{9}|\d{6})/', $section, $matches);

            foreach ($matches[0] ?? [] as $token) {
                $tokens[] = $token;
            }
        }

        return array_values(array_unique($tokens));
    }

    private function kiteOrderTableSections(string $content): array
    {
        $sections = [];
        $lines = preg_split('/\R/', $content) ?: [];
        $collecting = false;
        $current = [];
        $headerScore = 0;

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', (string) $line));
            $normalized = strtolower($line);

            if ($line === '') {
                continue;
            }

            if (str_contains($normalized, 'order number')) {
                if ($current) {
                    $sections[] = implode("\n", $current);
                    $current = [];
                }

                $collecting = true;
                $headerScore = 1;
                continue;
            }

            if ($collecting && $headerScore > 0 && $headerScore < 3) {
                if (str_contains($normalized, 'invoice number') || str_contains($normalized, 'tracking number')) {
                    $headerScore++;
                    continue;
                }
            }

            if (!$collecting) {
                continue;
            }

            if ($this->isKiteTableEndLine($line)) {
                if ($current) {
                    $sections[] = implode("\n", $current);
                    $current = [];
                }

                $collecting = false;
                $headerScore = 0;
                continue;
            }

            if (preg_match('/(?:25\d{9}|10\d{9}|92\d{9}|\d{6})/', $line)) {
                $current[] = $line;
            }
        }

        if ($current) {
            $sections[] = implode("\n", $current);
        }

        return $sections;
    }

    private function isKiteTableEndLine(string $line): bool
    {
        $normalized = strtolower(trim($line));

        if (preg_match('/^\d+$/', $normalized)) {
            return false;
        }

        return str_contains($normalized, 'total')
            || str_contains($normalized, 'subtotal')
            || str_contains($normalized, 'vat')
            || str_contains($normalized, 'cvr')
            || str_contains($normalized, 'invoice date')
            || str_contains($normalized, 'payment')
            || str_contains($normalized, 'the exporter')
            || str_contains($normalized, 'kite');
    }

    private function extractKiteTokens(array|string|null $value): array
    {
        $tokens = [];

        foreach ($this->expandReferenceRanges($value) as $item) {
            foreach (preg_split('/\s+/', (string) $item) ?: [] as $token) {
                $token = trim($token, " \t\n\r\0\x0B,.;:");

                if ($token !== '') {
                    $tokens[] = $token;
                }
            }
        }

        return array_values(array_unique($tokens));
    }

    private function filterKiteSalesOrders(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isKiteSalesOrder((string) $value)));
    }

    private function filterKiteSalesInvoices(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isKiteSalesInvoice((string) $value)));
    }

    private function filterKiteShipments(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $this->isKiteShipment((string) $value)));
    }

    private function isKiteSalesOrder(string $value): bool
    {
        return (bool) preg_match('/^(?:25\d{9}|10\d{9})$/', trim($value));
    }

    private function isKiteSalesInvoice(string $value): bool
    {
        return (bool) preg_match('/^\d{6}$/', trim($value));
    }

    private function isKiteShipment(string $value): bool
    {
        return (bool) preg_match('/^92\d{9}$/', trim($value));
    }
}
