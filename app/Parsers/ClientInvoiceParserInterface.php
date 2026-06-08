<?php

namespace App\Parsers;

interface ClientInvoiceParserInterface
{
    public function supports(?string $clientName, ?string $clientNo, array $doc = [], array $result = [], ?bool $validate = false): bool;

    public function parse(array $result, array $doc, ?string $clientName = null, ?string $clientNo = null, ?bool $validate = false): array;    
}