<?php

namespace App\Parsers;

class ClientInvoiceParser
{
    public function __construct(
        //protected iterable $parsers
        protected array $parsers
    ) {}

    public function parse(array $result): array
    {
        $doc = $result['analyzeResult']['documents'][0]['fields'] ?? [];
        $clientName = $doc['Client Name']['valueString'] ?? null;

        foreach ($this->parsers as $parser) {
            if ($parser->supports($clientName, $doc, $result)) {
                return $parser->parse($result, $doc, $clientName);
            }
        }

        return (new DefaultInvoiceParser())->parse($result, $doc, $clientName);
    }
}