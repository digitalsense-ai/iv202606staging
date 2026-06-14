<?php

namespace App\Parsers\Concerns;

trait ExtractsCommercialReferences
{
    protected function commercialContent(array $result): string
    {
        return (string) ($result['analyzeResult']['content'] ?? $result['content'] ?? '');
    }

    protected function contentLines(array $result): array
    {
        return array_values(array_filter(array_map(function ($line) {
            $line = trim(preg_replace('/\s+/', ' ', (string) $line));
            return $line === '' ? null : $line;
        }, preg_split('/\R/', $this->commercialContent($result)) ?: [])));
    }

    protected function normalizeReferenceList(array|string|null $value, array $allowedPrefixes = [], array $blockedPrefixes = []): array
    {
        $items = $this->expandReferenceRanges($value);
        $normalized = [];

        foreach ($items as $item) {
            $item = trim((string) $item);
            $item = trim($item, " \t\n\r\0\x0B,.;:");

            if ($item === '') {
                continue;
            }

            if ($allowedPrefixes && !$this->startsWithAny($item, $allowedPrefixes)) {
                continue;
            }

            if ($blockedPrefixes && $this->startsWithAny($item, $blockedPrefixes)) {
                continue;
            }

            $normalized[strtoupper($item)] = $item;
        }

        return array_values($normalized);
    }

    protected function expandReferenceRanges(array|string|null $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $items = is_array($value)
            ? $value
            : preg_split('/[,;\n\r\t]+/', (string) $value);

        $expanded = [];

        foreach ($items ?: [] as $item) {
            $parts = preg_split('/\s+/', trim((string) $item)) ?: [];

            foreach ($parts as $part) {
                $part = trim($part);
                $part = trim($part, " \t\n\r\0\x0B,.;:");

                if ($part === '') {
                    continue;
                }

                if (preg_match('/^([A-Za-z]*)(\d+)\s*-\s*([A-Za-z]*)(\d+)$/', $part, $match)) {
                    $prefixStart = $match[1];
                    $startRaw = $match[2];
                    $prefixEnd = $match[3];
                    $endRaw = $match[4];

                    $startNum = (int) $startRaw;
                    $endNum = (int) $endRaw;

                    if (strlen($endRaw) < strlen($startRaw)) {
                        $endRaw = substr($startRaw, 0, strlen($startRaw) - strlen($endRaw)) . $endRaw;
                        $endNum = (int) $endRaw;
                    }

                    if ($prefixStart === $prefixEnd && $startNum <= $endNum && ($endNum - $startNum) <= 500) {
                        for ($i = $startNum; $i <= $endNum; $i++) {
                            $expanded[] = $prefixStart . str_pad((string) $i, strlen($startRaw), '0', STR_PAD_LEFT);
                        }

                        continue;
                    }
                }

                $expanded[] = $part;
            }
        }

        return array_values(array_unique(array_filter($expanded)));
    }

    protected function joinReferences(array $values): ?string
    {
        $values = array_values(array_filter(array_unique(array_map('trim', $values))));

        return $values ? implode(', ', $values) : null;
    }

    protected function startsWithAny(string $value, array $prefixes): bool
    {
        $value = strtolower(trim($value));

        foreach ($prefixes as $prefix) {
            if (str_starts_with($value, strtolower((string) $prefix))) {
                return true;
            }
        }

        return false;
    }

    protected function extractColumnAfterHeader(array $result, string $header, int $columnsPerRow, array $columnMap): array
    {
        $content = $this->commercialContent($result);
        $parts = explode($header, $content);

        if (count($parts) <= 1) {
            return [];
        }

        array_shift($parts);
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', implode("\n", $parts)) ?: [])));
        $rows = [];
        $current = [];

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));

            if ($line === '' || $this->isLikelyPageNoise($line) || $this->isRepeatedHeader($line, $header)) {
                continue;
            }

            $current[] = $line;

            if (count($current) === $columnsPerRow) {
                $row = [];
                foreach ($columnMap as $index => $name) {
                    $row[$name] = $current[$index] ?? null;
                }
                $rows[] = $row;
                $current = [];
            }
        }

        return $rows;
    }

    protected function isLikelyPageNoise(string $line): bool
    {
        return (bool) preg_match('/^(page\s+\d+|continued|total|subtotal|vat|amount|description|quantity|net weight)/i', $line);
    }

    protected function isRepeatedHeader(string $line, string $header): bool
    {
        $headerWords = array_filter(preg_split('/\s+/', strtolower($header)) ?: []);
        $lineLower = strtolower($line);

        foreach ($headerWords as $word) {
            if (strlen($word) >= 4 && str_contains($lineLower, $word)) {
                return true;
            }
        }

        return false;
    }

    protected function mergeReferencePayload(array $base, array $override): array
    {
        foreach (['related_sales_invoices', 'related_sales_orders', 'related_shipment_nos'] as $field) {
            $baseValues = $this->normalizeReferenceList($base[$field] ?? null);
            $overrideValues = $this->normalizeReferenceList($override[$field] ?? null);
            $base[$field] = $this->joinReferences(array_merge($baseValues, $overrideValues));
        }

        return $base;
    }
}