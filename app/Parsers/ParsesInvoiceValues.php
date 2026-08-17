<?php

namespace App\Parsers;

trait ParsesInvoiceValues
{
    protected function getValueString($value): string
    {
        if (is_array($value)) {
            return trim(implode(' ', array_map('trim', $value)));
        }

        return trim((string) $value);
    }
}