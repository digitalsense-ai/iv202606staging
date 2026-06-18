<?php

namespace App\Parsers;

use App\Parsers\Concerns\ExtractsCommercialReferences;
use Illuminate\Support\Facades\Log;

class KiteInvoiceParser implements ClientInvoiceParserInterface
{
    use ExtractsCommercialReferences;

    private const KITE_SALES_ORDER_PATTERN = '25\d{9}|10\d{9}|84\d{9}';

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
        $content = $result['analyzeResult']['content'] ?? '';

        $rows = $this->rowsFromOrderTables($content);

        $fieldTokens = array_merge(
            $this->extractKiteTokens($doc['Related Sales Orders']['valueString'] ?? null),
            $this->extractKiteTokens($doc['Related Sales Invoices']['valueString'] ?? null),
            $this->extractKiteTokens($doc['Related Shipment Numbers']['valueString'] ?? null)
        );

        $tokens = array_merge(
            array_column($rows, 'sales_order'),
            array_column($rows, 'sales_invoice'),
            array_merge(...array_map(fn ($row) => $row['shipments'] ?? [], $rows ?: [[]])),
            $fieldTokens
        );

        Log::info('Kite parser input', [
            // 'has_content' => isset($result['analyzeResult']['content']),
            // 'content_length' => strlen($content),
            // 'content_preview' => substr($content, 0, 500),
            // 'doc_keys' => array_keys($doc),
            'table_rows' => count($rows),
            'orders' => count($this->filterKiteSalesOrders($tokens)),
            'invoices' => count($this->filterKiteSalesInvoices($tokens)),
            'shipments' => count($this->filterKiteShipments($tokens)),
        ]);

        return [
            'related_sales_invoices' => $this->joinReferences($this->filterKiteSalesInvoices($tokens)),
            'related_sales_orders' => $this->joinReferences($this->filterKiteSalesOrders($tokens)),
            'related_shipment_nos' => $this->joinReferences($this->filterKiteShipments($tokens)),
        ];
    }

    private function rowsFromOrderTables(string $content): array
    {
        $rows = [];

        foreach ($this->kiteOrderTableSections($content) as $section) {
            $tokens = $this->tokensFromTableSection($section);

            $currentOrder = null;
            $currentInvoice = null;
            $currentShipments = [];

            foreach ($tokens as $token) {
                if ($this->isKiteSalesOrder($token)) {
                    $this->flushRow($rows, $currentOrder, $currentInvoice, $currentShipments);

                    $currentOrder = $token;
                    $currentInvoice = null;
                    $currentShipments = [];

                    continue;
                }

                if ($this->isKiteSalesInvoice($token)) {
                    if ($currentInvoice !== null && $currentInvoice !== $token) {
                        $this->flushRow($rows, $currentOrder, $currentInvoice, $currentShipments);
                        $currentShipments = [];
                    }

                    $currentInvoice = $token;

                    continue;
                }

                if ($this->isKiteShipment($token)) {
                    $currentShipments[] = $token;
                }
            }

            $this->flushRow($rows, $currentOrder, $currentInvoice, $currentShipments);
        }

        return $this->dedupeRows($rows);
    }

    private function kiteOrderTableSections(string $content): array
    {
        $sections = [];
        $lines = preg_split('/\R/', $content) ?: [];

        $collecting = false;
        $current = [];
        $seenHeaderParts = [];

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', (string) $line));

            if ($line === '') {
                continue;
            }

            $normalized = strtolower($line);

            if (str_contains($normalized, 'order number')) {
                if ($current) {
                    $sections[] = implode("\n", $current);
                    $current = [];
                }

                $collecting = true;
                $seenHeaderParts = ['order'];

                continue;
            }

            if ($collecting && str_contains($normalized, 'invoice number')) {
                $seenHeaderParts[] = 'invoice';
                continue;
            }

            if ($collecting && str_contains($normalized, 'tracking number')) {
                $seenHeaderParts[] = 'tracking';
                continue;
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
                $seenHeaderParts = [];

                continue;
            }

            //if (preg_match('/(?:25\d{9}|10\d{9}|84\d{9}|92\d{9}|\d{6})/', $line)) {
            if (preg_match('/(?:' . self::KITE_SALES_ORDER_PATTERN . '|92\d{9}|\d{6})/', $line)) {
                $current[] = $line;
            }
        }

        if ($current) {
            $sections[] = implode("\n", $current);
        }

        return $sections;
    }

    private function tokensFromTableSection(string $section): array
    {
        //preg_match_all('/(?:25\d{9}|10\d{9}|84\d{9}|92\d{9}|\d{6})/', $section, $matches);        
        preg_match_all('/(?:' . self::KITE_SALES_ORDER_PATTERN . '|92\d{9}|\d{6})/', $section, $matches);

        return array_values(array_map(
            fn ($token) => trim($token),
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
            || str_contains($normalized, 'currency')
            || str_contains($normalized, 'amount');
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
        return array_values(array_unique(array_filter(
            $values,
            fn ($value) => $this->isKiteSalesOrder((string) $value)
        )));
    }

    private function filterKiteSalesInvoices(array $values): array
    {
        return array_values(array_unique(array_filter(
            $values,
            fn ($value) => $this->isKiteSalesInvoice((string) $value)
        )));
    }

    private function filterKiteShipments(array $values): array
    {
        return array_values(array_unique(array_filter(
            $values,
            fn ($value) => $this->isKiteShipment((string) $value)
        )));
    }

    private function isKiteSalesOrder(string $value): bool
    {
        //return (bool) preg_match('/^(?:25\d{9}|10\d{9}|84\d{9})$/', trim($value));        
        return (bool) preg_match('/^(?:' . self::KITE_SALES_ORDER_PATTERN . ')$/', trim($value));
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