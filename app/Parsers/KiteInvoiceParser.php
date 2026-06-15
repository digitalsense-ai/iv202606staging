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
        $tokens = array_merge(
            $this->extractKiteTokens($doc['Related Sales Orders']['valueString'] ?? null),
            $this->extractKiteTokens($doc['Related Sales Invoices']['valueString'] ?? null),
            $this->extractKiteTokens($doc['Related Shipment Numbers']['valueString'] ?? null),
            $this->tokensFromContent($result['analyzeResult']['content'] ?? '')
        );

        return [
            'related_sales_invoices' => $this->joinReferences($this->filterKiteSalesInvoices($tokens)),
            'related_sales_orders' => $this->joinReferences($this->filterKiteSalesOrders($tokens)),
            'related_shipment_nos' => $this->joinReferences($this->filterKiteShipments($tokens)),
        ];
    }

    private function tokensFromContent(string $content): array
    {
        preg_match_all('/(?:25\d{9}|10\d{9}|\d{6}|92\d{9})/', $content, $matches);

        return array_values(array_unique($matches[0] ?? []));
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
