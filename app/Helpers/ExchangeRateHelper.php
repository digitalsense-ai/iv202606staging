<?php

namespace App\Helpers;

class ExchangeRateHelper
{
    /**
     * Normalize exchange rate
     *
     * Output format:
     * 1,8452
     *
     * Examples:
     * 1.8452      => 1,8452
     * 1,8452      => 1,8452
     * 1 8452      => 1,8452
     * RATE 1.8452 => 1,8452
     * 18452       => 1,8452 (optional heuristic)
     */
    public static function normalize(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        /**
         * Remove spaces
         */
        $value = str_replace([
            ' ',
            "'",
            '’',
        ], '', $value);

        /**
         * Keep only digits + separators
         */
        $value = preg_replace('/[^0-9.,]/', '', $value);

        if (!$value) {
            return null;
        }

        /**
         * Handle comma decimal
         * 1,8452 -> 1.8452
         */
        if (
            str_contains($value, ',') &&
            !str_contains($value, '.')
        ) {
            $value = str_replace(',', '.', $value);
        }

        /**
         * Handle thousand separators
         * 1,234.5678 -> 1234.5678
         */
        if (
            str_contains($value, ',') &&
            str_contains($value, '.')
        ) {
            $value = str_replace(',', '', $value);
        }

        /**
         * Numeric validation
         */
        if (!is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        /**
         * Force 4 decimals
         * Output European decimal
         */
        return number_format(
            $number,
            4,
            ',',
            ''
        );
    }
}