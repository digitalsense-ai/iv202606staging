<?php

namespace App\Parsers;
use Illuminate\Support\Facades\Log;

class AuboInvoiceParser implements ClientInvoiceParserInterface
{
    use ParsesInvoiceValues;

    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool
    {        
        $name = strtolower(trim($clientName ?? ''));

        if (str_contains($name, 'aubo production')) {
            return true;
        }

        $content = strtolower($result['analyzeResult']['content'] ?? '');

        return str_contains($content, 'aubo production');
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

    // private function ocrText(array $result): string
    // {
    //     if (!empty($result['content'])) {
    //         return (string) $result['content'];
    //     }

    //     if (!empty($result['analyzeResult']['content'])) {
    //         return (string) $result['analyzeResult']['content'];
    //     }

    //     $lines = [];

    //     foreach (data_get($result, 'analyzeResult.pages', []) as $page) {
    //         foreach (($page['lines'] ?? []) as $line) {
    //             if (!empty($line['content'])) {
    //                 $lines[] = $line['content'];
    //             }
    //         }
    //     }

    //     return implode("\n", $lines);
    // }   

    public function parse(
        array $result,
        array $doc,
        ?string $clientName = null,
        ?string $clientNo = null,
        ?bool $validate = false
    ): array {
        $linePattern = '/Omfatter\s*(?:\r?\n\s*)?(N\d{8}(?:\s*-\s*N\d{8})?)/iu';

        $salesInvoices = [];
        $salesOrders   = [];
        $shipmentNos   = [];

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
         * Sales Invoice is the primary source.
         *
         * If Sales Invoices exist, use them.
         * Otherwise use Sales Orders.
         * Otherwise use Shipment Numbers.
         */
        $values = [];

        if ($salesInvoiceValues !== '') {

            $values = $this->parseSalesInvoiceValues(
                $salesInvoiceValues
            );

        } elseif ($salesOrderValues !== '') {

            $values = $this->parseSalesInvoiceValues(
                $salesOrderValues
            );

        } elseif ($shipmentValues !== '') {

            $values = $this->parseSalesInvoiceValues(
                $shipmentValues
            );
        }

        /*
         * Always check OCR content as well.
         */
        $text = $this->ocrText($result);

        if (preg_match($linePattern, $text, $match)) {

            $ocrReference = trim($match[1]);

            if ($ocrReference !== '') {
                $values[] = strtoupper($ocrReference);
            }
        }

        /*
         * Build Sales Invoices.
         */
        foreach ($values as $item) {

            $item = trim($item);

            /*
             * Ignore empty values and standalone "-".
             */
            if ($item === '' || $item === '-') {
                continue;
            }

            $salesInvoices[] = strtoupper($item);
        }

        /*
         * Remove duplicates while preserving order.
         */
        $salesInvoices = array_values(
            array_unique($salesInvoices)
        );

        /*
         * If a complete range exists, remove the individual
         * invoice numbers that belong to that range.
         *
         * Example:
         *
         * N00229378
         * N00229384
         * N00229378 - N00229384
         *
         * becomes:
         *
         * N00229378 - N00229384
         */
        $rangeValues = array_values(
            array_filter(
                $salesInvoices,
                function ($value) {
                    return preg_match(
                        '/^N\d{8}\s*-\s*N\d{8}$/i',
                        $value
                    );
                }
            )
        );

        if (!empty($rangeValues)) {

            /*
             * Keep only the range values.
             */
            $salesInvoices = $rangeValues;
        }

        return [
            'related_sales_invoices' => implode(
                ', ',
                array_unique($salesInvoices)
            ),

            'related_sales_orders' => implode(
                ', ',
                array_unique($salesOrders)
            ),

            'related_shipment_nos' => implode(
                ', ',
                array_unique($shipmentNos)
            ),
        ];
    }

    /**
     * Parse sales invoice/order/shipment references.
     *
     * Keeps ranges together.
     *
     * Example:
     *
     * N00229378, -, N00229384, N00229378 - N00229384
     *
     * becomes:
     *
     * [
     *     'N00229378',
     *     'N00229384',
     *     'N00229378 - N00229384'
     * ]
     */
    private function parseSalesInvoiceValues(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        $ranges = [];

        /*
         * Find complete N-number ranges first.
         *
         * Examples:
         *
         * N00229378 - N00229384
         * N00229378-N00229384
         */
        $value = preg_replace_callback(
            '/N\d{8}\s*-\s*N\d{8}/iu',
            function ($match) use (&$ranges) {

                $range = preg_replace(
                    '/\s*-\s*/',
                    ' - ',
                    trim($match[0])
                );

                $placeholder = '__RANGE_' . count($ranges) . '__';

                $ranges[$placeholder] = strtoupper($range);

                return $placeholder;
            },
            $value
        );

        /*
         * Split remaining values.
         *
         * Commas, spaces and semicolons are separators.
         */
        $values = preg_split(
            '/[\s,;]+/',
            $value,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $result = [];

        foreach ($values as $item) {

            $item = trim($item);

            /*
             * Ignore standalone hyphen.
             */
            if ($item === '' || $item === '-') {
                continue;
            }

            /*
             * Restore range placeholder.
             */
            if (isset($ranges[$item])) {

                $result[] = $ranges[$item];

            } else {

                $result[] = strtoupper($item);
            }
        }

        return array_values(
            array_unique($result)
        );
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

        foreach (
            data_get(
                $result,
                'analyzeResult.pages',
                []
            ) as $page
        ) {

            foreach (
                ($page['lines'] ?? []) as $line
            ) {

                if (!empty($line['content'])) {
                    $lines[] = $line['content'];
                }
            }
        }

        return implode("\n", $lines);
    }

}