<?php

namespace App\Parsers;

use Str;
use Illuminate\Support\Facades\Log;

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

        // if (str_contains($name, 'berg toys') || ($clientNo && ($clientNo == '934286723' || $clientNo == '379603560' || $clientNo == '292640361'))) {
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

        $tokens = $this->extractBergTokens($result);
        $rows = $this->pairBergRows($tokens);

        $tablePayload = [
            'related_sales_invoices' => $this->joinReferences(
                array_merge(
                    array_column($rows, 'sales_invoice'),
                    $this->tokensByType($tokens, 'invoice')
                )
            ),
            'related_sales_orders' => $this->joinReferences(
                array_merge(
                    array_column($rows, 'sales_order'),
                    $this->tokensByType($tokens, 'order')
                )
            ),
            'related_shipment_nos' => null,
        ];

        return $this->mergeReferencePayload($azurePayload, $tablePayload);

        /*
        if($validate)
        {
            $relatedSalesInvoices = $doc['Related Sales Invoices']['valueString'] ?? null;
            $relatedSalesInvoices = $relatedSalesInvoices ?? [];

            $relatedSalesOrders   = $doc['Related Sales Orders']['valueString'] ?? null;
            $relatedSalesOrders   = $relatedSalesOrders ?? [];

            $relatedShipments     = $doc['Related Shipment Numbers']['valueString'] ?? null;
            $relatedShipments   = $relatedShipments ?? [];

            if (!is_array($relatedSalesInvoices)) {
                $relatedSalesInvoices = array_filter(array_map('trim', explode(',', $relatedSalesInvoices)));
            }
//Log::info($relatedSalesInvoices);
            if (!is_array($relatedSalesOrders)) {
                $relatedSalesOrders = array_filter(array_map('trim', explode(',', $relatedSalesOrders)));
            }
//Log::info($relatedSalesOrders);

            if (!is_array($relatedShipments)) {
                $relatedShipments = array_filter(array_map('trim', explode(',', $relatedShipments)));
            }
//Log::info($relatedShipments);

            $related_sales_invoices = null;
            foreach ($relatedSalesInvoices as $relatedSalesInvoice)
            {
                $relatedSalesInvoice = trim($relatedSalesInvoice);

                if(Str::startsWith(Str::lower($relatedSalesInvoice), ['no', 'ch', 'uk', '2026']))
                {
                    if(Str::startsWith(Str::lower($relatedSalesInvoice), ['chn']))
                    {
                    }
                    else
                    {
                        if($related_sales_invoices)
                            $related_sales_invoices .= ', ' . $relatedSalesInvoice;
                        else
                            $related_sales_invoices = $relatedSalesInvoice;
                    }
                }
            }
//Log::info($related_sales_invoices);
            $related_sales_orders = null;
            foreach ($relatedSalesOrders as $relatedSalesOrder)
            {
                $relatedSalesOrder = trim($relatedSalesOrder);

                if(Str::startsWith(Str::lower($relatedSalesOrder), ['81', '31', '52', '226']))
                {
                    if($related_sales_orders)
                        $related_sales_orders .= ', ' . $relatedSalesOrder;
                    else
                        $related_sales_orders = $relatedSalesOrder;
                }
            }
//Log::info($related_sales_orders);
            $related_shipment_nos = null;
            foreach ($relatedShipments as $relatedShipment)
            {
                $relatedShipment = trim($relatedShipment);

                if($related_shipment_nos)
                    $related_shipment_nos .= ', ' . $relatedShipment;
                else
                    $related_shipment_nos = $relatedShipment;
            }
//Log::info($related_shipment_nos);            
            return [
                'related_sales_invoices' => $related_sales_invoices,
                'related_sales_orders'   => $related_sales_orders,
                'related_shipment_nos'   => $related_shipment_nos,
            ];
        }
        else
        {
            $content = $result['analyzeResult']['content'] ?? '';

            $header = "ItemID\nDescription\nQuantity\nNet weight per part";

            $parts = explode($header, $content);
// Log::info($parts);
            if (count($parts) <= 1) {
                return [
                    'related_sales_invoices' => $doc['Related Sales Invoices']['valueString'] ?? null,
                    'related_sales_orders'   => $doc['Related Sales Orders']['valueString'] ?? null,
                    'related_shipment_nos'   => $doc['Related Shipment Numbers']['valueString'] ?? null,
                ];
            }

            array_shift($parts);

            $content = implode("\n", $parts);
            $lines = array_values(array_filter(array_map('trim', explode("\n", $content))));

            $data = [];
            $current = [];
// Log::info("---------------------------------------------------------");           
// Log::info($lines);
            foreach ($lines as $line) {

                if (!preg_match('/^[A-Z0-9]+$/i', $line)) {
                    continue;
                }

                // Keep only values with length > 8
                if (strlen($line) < 8) {
                    continue;
                }

                $current[] = $line;
// Log::info($current);
// Log::info("-----------");
                if (count($current) === 2) {  

                    $sales_invoice = '';
                    if(Str::startsWith(Str::lower($current[0]), ['chn']))
                        $sales_invoice = null;
                    else
                        $sales_invoice = (Str::startsWith(Str::lower($current[0]), ['no', 'ch', 'uk'])) ? $current[0] : null;

                    $data[] = [
                        'sales_invoice' => $sales_invoice,
                        'sales_order'   => (Str::startsWith(Str::lower($current[1]), ['81', '31', '52'])) ? $current[1] : null,
                        'shipment'      => [],
                    ];

                    $data[] = [
                        'sales_invoice' => $current[0],
                        'sales_order'   => $current[1],
                        'shipment'      => [],
                    ];

                    $current = [];
                }
            }

            return [
                'related_sales_invoices' => implode(', ', array_column($data, 'sales_invoice')),
                'related_sales_orders'   => implode(', ', array_column($data, 'sales_order')),
                'related_shipment_nos'   => '',
            ];
        } 
        */       
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

    private function extractBergTokens(array $result): array
    {
        $content = $this->commercialContent($result);
        $afterHeader = $content;

        if (str_contains($content, self::HEADER)) {
            $parts = explode(self::HEADER, $content, 2);
            $afterHeader = $parts[1] ?? $content;
        }

        $tokens = [];
        $position = 0;

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

                foreach ($this->expandReferenceRanges($token) as $expandedToken) {
                    if ($this->isBergSalesInvoice($expandedToken)) {
                        $tokens[] = [
                            'value' => $expandedToken,
                            'type' => 'invoice',
                            'position' => $position++,
                        ];
                        continue;
                    }

                    if ($this->isBergSalesOrder($expandedToken)) {
                        $tokens[] = [
                            'value' => $expandedToken,
                            'type' => 'order',
                            'position' => $position++,
                        ];
                    }
                }
            }
        }

        return $tokens;
    }

    private function pairBergRows(array $tokens): array
    {
        $rows = [];
        $pendingInvoices = [];
        $pendingOrders = [];

        foreach ($tokens as $token) {
            if (($token['type'] ?? null) === 'invoice') {
                if ($pendingOrders) {
                    $rows[] = [
                        'sales_invoice' => $token['value'],
                        'sales_order' => array_shift($pendingOrders)['value'],
                    ];
                    continue;
                }

                $pendingInvoices[] = $token;
                continue;
            }

            if (($token['type'] ?? null) === 'order') {
                if ($pendingInvoices) {
                    $rows[] = [
                        'sales_invoice' => array_shift($pendingInvoices)['value'],
                        'sales_order' => $token['value'],
                    ];
                    continue;
                }

                $pendingOrders[] = $token;
            }
        }

        foreach ($pendingInvoices as $invoice) {
            $rows[] = [
                'sales_invoice' => $invoice['value'],
                'sales_order' => null,
            ];
        }

        foreach ($pendingOrders as $order) {
            $rows[] = [
                'sales_invoice' => null,
                'sales_order' => $order['value'],
            ];
        }

        return $rows;
    }

    private function tokensByType(array $tokens, string $type): array
    {
        return array_values(array_map(
            fn ($token) => $token['value'],
            array_filter($tokens, fn ($token) => ($token['type'] ?? null) === $type)
        ));
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