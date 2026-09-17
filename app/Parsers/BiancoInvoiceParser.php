<?php

namespace App\Parsers;
use Illuminate\Support\Facades\Log;

class BiancoInvoiceParser implements ClientInvoiceParserInterface
{
    use ParsesInvoiceValues;

    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {        
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'bianco')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'bianco');
    }

    // public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array
    // {                
    //     $linePattern = '/Omfatter\s*(?:\r?\n\s*)?(N\d{8}(?:\s*-\s*N\d{8})?)/iu';
        
    //     $salesInvoices = [];
    //     $salesOrders   = [];
    //     $shipmentNos   = [];
       
    //     $salesInvoiceValues = $this->getValueString($doc['Related Sales Invoices']['valueString'] ?? '');
    //     $salesOrderValues   = $this->getValueString($doc['Related Sales Orders']['valueString'] ?? '');
    //     $shipmentValues     = $this->getValueString($doc['Related Shipment Numbers']['valueString'] ?? '');
  
    //     $values = [];

    //     if ($salesInvoiceValues !== '') {
    //         $values = array_values(array_filter(
    //             array_map('trim', preg_split('/[\s\r\n,;]+/', $salesInvoiceValues))
    //         ));
    //     }
    //     else if ($salesOrderValues !== '') {
    //         $values = array_values(array_filter(
    //             array_map('trim', preg_split('/[\s\r\n,;]+/', $salesOrderValues))
    //         ));
    //     }
    //     else if ($shipmentValues !== '') {
    //         $values = array_values(array_filter(
    //             array_map('trim', preg_split('/[\s\r\n,;]+/', $shipmentValues))
    //         ));
    //     }

    //     // Always check OCR content also
    //     $text = $this->ocrText($result);

    //     if (preg_match($linePattern, $text, $match)) {            
    //         $values[] = trim($match[1]); 
    //     }

    //     foreach ($values as $item) {
    //         //if (preg_match($invoicePattern, $item)) {
    //             $salesInvoices[] = strtoupper($item);
    //         //}
    //     }

    //     return [
    //         'related_sales_invoices' => implode(', ', array_unique($salesInvoices)),
    //         'related_sales_orders'   => implode(', ', array_unique($salesOrders)),
    //         'related_shipment_nos'   => implode(', ', array_unique($shipmentNos)),
    //     ];
    // }

    public function parse(
        array $result,
        array $doc,
        ?string $clientName = null,
        ?string $clientNo = null,
        ?bool $validate = false
    ): array {
        $salesInvoiceValues = $this->getValueString(
            $doc['Related Sales Invoices']['valueString'] ?? ''
        );

        $salesOrderValues = $this->getValueString(
            $doc['Related Sales Orders']['valueString'] ?? ''
        );

        $shipmentValues = $this->getValueString(
            $doc['Related Shipment Numbers']['valueString'] ?? ''
        );

        /*
         * Parse each field separately.
         */
        $salesInvoices = $this->parseReferences($salesInvoiceValues);
        $salesOrders   = $this->parseReferences($salesOrderValues);
        $shipmentNos   = $this->parseReferences($shipmentValues);

        /*
         * ---------------------------------------------------------
         * Only move references when Sales Invoices already exist.
         * ---------------------------------------------------------
         *
         * Example:
         *
         * Sales invoices:
         * 56204-56229
         *
         * Shipment numbers:
         * 56231-56242, 56244-56247, 24186, 24188-24190
         *
         * Result:
         *
         * Sales invoices:
         * 56204-56229, 56231-56242, 56244-56247
         *
         * Shipment numbers:
         * 24186, 24188-24190
         *
         * ---------------------------------------------------------
         */
        if (!empty($salesInvoices)) {

            /*
             * Get the prefixes/series from existing
             * Sales Invoice references.
             *
             * 56204-56229 => 562
             */
            $invoicePrefixes = $this->getReferencePrefixes(
                $salesInvoices
            );

            // Log::info('Invoice prefixes', [
            //     'invoicePrefixes' => $invoicePrefixes,
            // ]);

            /*
             * -----------------------------------------------------
             * Check Sales Orders
             * -----------------------------------------------------
             */
            $remainingSalesOrders = [];

            foreach ($salesOrders as $reference) {

                if ($this->belongsToInvoiceSeries(
                    $reference,
                    $invoicePrefixes
                )) {

                    /*
                     * Move to Sales Invoices.
                     */
                    $salesInvoices[] = $reference;

                    // Log::info('Moved Sales Order to Sales Invoice', [
                    //     'reference' => $reference,
                    // ]);

                } else {

                    /*
                     * Keep in Sales Orders.
                     */
                    $remainingSalesOrders[] = $reference;
                }
            }

            $salesOrders = $remainingSalesOrders;

            /*
             * -----------------------------------------------------
             * Check Shipment Numbers
             * -----------------------------------------------------
             */
            $remainingShipmentNos = [];

            foreach ($shipmentNos as $reference) {

                if ($this->belongsToInvoiceSeries(
                    $reference,
                    $invoicePrefixes
                )) {

                    /*
                     * Move to Sales Invoices.
                     */
                    $salesInvoices[] = $reference;

                    // Log::info('Moved Shipment Number to Sales Invoice', [
                    //     'reference' => $reference,
                    // ]);

                } else {

                    /*
                     * Keep in Shipment Numbers.
                     */
                    $remainingShipmentNos[] = $reference;
                }
            }

            $shipmentNos = $remainingShipmentNos;
        }

        /*
         * Remove duplicates while preserving order.
         */
        $salesInvoices = $this->uniqueReferences(
            $salesInvoices
        );

        $salesOrders = $this->uniqueReferences(
            $salesOrders
        );

        $shipmentNos = $this->uniqueReferences(
            $shipmentNos
        );

        return [
            'related_sales_invoices' => implode(
                ', ',
                $salesInvoices
            ),

            'related_sales_orders' => implode(
                ', ',
                $salesOrders
            ),

            'related_shipment_nos' => implode(
                ', ',
                $shipmentNos
            ),
        ];
    }


    /**
     * Parse references separated by comma, semicolon or newline.
     *
     * Hyphens are preserved because they represent ranges.
     */
    private function parseReferences(?string $value): array
    {
        if (!$value) {
            return [];
        }

        /*
         * Normalize spaces around hyphens.
         *
         * 56231 - 56242 => 56231-56242
         */
        $value = preg_replace(
            '/\s*-\s*/',
            '-',
            $value
        );

        /*
         * Normalize line endings.
         */
        $value = preg_replace(
            '/\r\n|\r/',
            "\n",
            $value
        );

        /*
         * Split on comma, semicolon or newline.
         *
         * DO NOT split on hyphen.
         */
        $references = preg_split(
            '/[,;\n]+/',
            $value
        );

        return array_values(
            array_filter(
                array_map(
                    fn ($value) => trim($value),
                    $references
                ),
                fn ($value) => $value !== ''
            )
        );
    }


    /**
     * Get prefixes from existing Sales Invoice references.
     *
     * Example:
     *
     * 56204-56229
     * 56231-56242
     *
     * returns:
     *
     * [562]
     */
    private function getReferencePrefixes(array $references): array
    {
        $prefixes = [];

        foreach ($references as $reference) {

            /*
             * Get the first number from the reference.
             *
             * 56204-56229 => 56204
             */
            if (!preg_match('/\d+/', $reference, $match)) {
                continue;
            }

            $number = $match[0];

            if (strlen($number) < 4) {
                continue;
            }

            /*
             * First 3 digits identify the series.
             *
             * 56204 => 562
             */
            $prefixes[] = substr($number, 0, 3);
        }

        return array_values(
            array_unique($prefixes)
        );
    }


    /**
     * Determine whether a reference belongs to one of
     * the Sales Invoice series.
     */
    private function belongsToInvoiceSeries(
        string $reference,
        array $invoicePrefixes
    ): bool {
        /*
         * Example:
         *
         * 56231-56242 => 56231
         * 56244-56247 => 56244
         * 24186       => 24186
         */
        if (!preg_match('/\d+/', $reference, $match)) {
            return false;
        }

        $number = $match[0];

        if (strlen($number) < 4) {
            return false;
        }

        /*
         * Extract first 3 digits.
         */
        $prefix = substr($number, 0, 3);

        $result = in_array(
            $prefix,
            $invoicePrefixes,
            true
        );

        // Log::info('Checking reference against invoice series', [
        //     'reference' => $reference,
        //     'number' => $number,
        //     'prefix' => $prefix,
        //     'invoicePrefixes' => $invoicePrefixes,
        //     'matches' => $result,
        // ]);

        return $result;
    }


    /**
     * Remove duplicate references while preserving order.
     */
    private function uniqueReferences(array $references): array
    {
        $result = [];
        $seen = [];

        foreach ($references as $reference) {

            $reference = trim($reference);

            if ($reference === '') {
                continue;
            }

            $normalized = preg_replace(
                '/\s+/',
                '',
                strtoupper($reference)
            );

            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;

            $result[] = strtoupper($reference);
        }

        return $result;
    }


    private function ocrText(array $result): string
    {
        if (!empty($result['content'])) {
            return (string) $result['content'];
        }

        if (!empty($result['analyzeResult']['content'])) {
            return (string) $result['analyzeResult']['content'];
        }

        $lines = [];

        foreach (data_get($result, 'analyzeResult.pages', []) as $page) {
            foreach (($page['lines'] ?? []) as $line) {
                if (!empty($line['content'])) {
                    $lines[] = $line['content'];
                }
            }
        }

        return implode("\n", $lines);
    }   
}