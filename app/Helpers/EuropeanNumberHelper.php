<?php

namespace App\Helpers;

class EuropeanNumberHelper
{
    /**
     * Convert various European number formats into:
     * x.xxx,xx
     *
     * Examples:
     * 1234.56       => 1.234,56
     * 1 234.56      => 1.234,56
     * 1'234.56      => 1.234,56
     * 1.234,56      => 1.234,56
     * 1234,56       => 1.234,56
     * 1234          => 1.234,00
     */
    public static function normalize(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        /**
         * Remove spaces and apostrophes
         */
        $value = str_replace([
            ' ',
            "'",
            '’',
        ], '', $value);

        /**
         * Detect decimal separator
         */
        $lastComma = strrpos($value, ',');
        $lastDot   = strrpos($value, '.');

        $decimalSeparator = null;

        if ($lastComma !== false && $lastDot !== false) {
            // whichever comes last is decimal separator
            $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
        } elseif ($lastComma !== false) {
            $decimalSeparator = ',';
        } elseif ($lastDot !== false) {
            $decimalSeparator = '.';
        }

        /**
         * Normalize to float-compatible format
         */
        if ($decimalSeparator === ',') {

            // remove thousand separators
            $value = str_replace('.', '', $value);

            // decimal separator to dot
            $value = str_replace(',', '.', $value);

        } elseif ($decimalSeparator === '.') {

            // remove thousand separators
            $value = str_replace(',', '', $value);

        } else {

            // integer value
            $value .= '.00';
        }

        /**
         * Validate numeric
         */
        if (!is_numeric($value)) {
            return null;
        }

        /**
         * Convert to European format
         */
        return number_format(
            (float) $value,
            2,
            ',',
            '.'
        );
    }

    public static function toFloat(?string $value): float
    {
        if (!$value) {
            return 0;
        }

        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }
}