<?php

namespace App\Parsers;

use App\Parsers\Concerns\ExtractsCommercialReferences;

class SecondFemaleInvoiceParser implements ClientInvoiceParserInterface
{
    use ExtractsCommercialReferences;

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
        $tokens = array_merge(
            $this->extractSecondFemaleTokens($doc['Related Sales Orders']['valueString'] ?? null),
            $this->extractSecondFemaleTokens($doc['Related Sales Invoices']['valueString'] ?? null),
            $this->extractSecondFemaleTokens($doc['Related Shipment Numbers']['valueString'] ?? null),
            $this->extractSecondFemaleTokensFromContent($result)
        );

        return [
            'related_sales_orders' => $this->joinReferences($this->filterSalesOrders($tokens)),
            'related_sales_invoices' => $this->joinReferences($this->filterSalesInvoices($tokens)),
            'related_shipment_nos' => $this->joinReferences($this->filterShipments($tokens)),
        ];
    }

    private function extractSecondFemaleTokensFromContent(array $result): array
    {
        $content = $this->commercialContent($result);

        if (preg_match('/This\s+Invoice\s+includes\s+the\s+following\s+deliveries\s*:/i', $content, $match, PREG_OFFSET_CAPTURE)) {
            $content = substr($content, $match[0][1]);
        }

        return $this->extractSecondFemaleTokens($content);
    }

    private function extractSecondFemaleTokens(array|string|null $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $content = is_array($value) ? implode("\n", $value) : (string) $value;

        preg_match_all('/\+?SL\d+|CH\d+|92\d{10,}/i', $content, $matches);

        return array_values(array_unique(array_map(function ($token) {
            return strtoupper(ltrim(trim($token), '+'));
        }, $matches[0] ?? [])));
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
        return (bool) preg_match('/^CH\d+$/i', trim($value));
    }

    private function isShipment(string $value): bool
    {
        return (bool) preg_match('/^92\d{10,}$/', trim($value));
    }
}
