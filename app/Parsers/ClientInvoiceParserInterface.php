<?php

namespace App\Parsers;

interface ClientInvoiceParserInterface
{
    public function supports(?string $clientName, array $doc = [], array $result = []): bool;

    public function parse(array $result, array $doc, ?string $clientName = null): array;
}