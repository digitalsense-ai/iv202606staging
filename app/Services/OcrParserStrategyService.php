<?php

namespace App\Services;

use App\Contracts\OcrInvoiceParser;
use App\Services\Parsers\SalesInvoiceParser;

class OcrParserStrategyService
{
    public function parserFor(string $invoiceType): OcrInvoiceParser
    {
        return match ($invoiceType) {
            'sales', 'multi-invoices', 'com' => app(SalesInvoiceParser::class),
            default => app(SalesInvoiceParser::class),
        };
    }

    public function apply(
        array $normalized,
        array $azureResult,
        ?int $clientId = null,
        string $invoiceType = 'sales'
    ): array {
        if (isset($normalized['error'])) {
            return $normalized;
        }

        $parser = $this->parserFor($invoiceType);

        $normalized = $parser->parse(
            normalized: $normalized,
            azureResult: $azureResult,
            clientId: $clientId,
            invoiceType: $invoiceType
        );

        data_set($normalized, '_ocr.parser_strategy', [
            'parser' => class_basename($parser),
            'invoice_type' => $invoiceType,
            'client_context' => $clientId,
            'feedback_scope' => $clientId ? 'client_then_global' : 'global',
        ]);

        return $normalized;
    }
}
