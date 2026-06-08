<?php

namespace App\Parsers;

class ClientInvoiceParser
{
    public function __construct(
        //protected iterable $parsers
        protected array $parsers
    ) {}

    public function parse(array $result, ?string $clientName, ?string $clientNo, ?bool $validate = false): array
    {
        if($validate)
            $doc = $result;
        else
            $doc = $result['analyzeResult']['documents'][0]['fields'] ?? [];
        //$clientName = $doc['Client Name']['valueString'] ?? null;
        $clientName = $clientName ?? null;
        $clientNo = $clientNo ?? null;

        foreach ($this->parsers as $parser) {
            if ($parser->supports($clientName, $clientNo, $doc, $result)) {
                return $parser->parse($result, $doc, $clientName, $clientNo, $validate);
            }
        }

        return (new DefaultInvoiceParser())->parse($result, $doc, $clientName, $clientNo, $validate);
    }
}