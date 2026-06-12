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
         * Handle division expressions
         * Example:
         * 100/68,35 => 1.4631
         */
        if (str_contains($value, '/')) {

            $parts = explode('/', $value);

            if (count($parts) === 2) {

                $left = str_replace(',', '.', $parts[0]);
                $right = str_replace(',', '.', $parts[1]);

                if (
                    is_numeric($left) &&
                    is_numeric($right) &&
                    (float) $right != 0.0
                ) {
                    $value = (string) (
                        (float) $left / (float) $right
                    );
                }
            }
        }
    
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

    // public static function calculateExchangeRateFromVat($nokVat, $eurVat): ?float
    // {
    //     if (!$nokVat || !$eurVat || $eurVat == 0) {
    //         return null;
    //     }

    //     return round($nokVat / $eurVat, 4);
    // }

    public static function calculateExchangeRateFromVat(
        float|int|null $exchangeVatAmount,
        float|int|null $baseVatAmount
    ): ?float {

        if (
            empty($exchangeVatAmount) ||
            empty($baseVatAmount) ||
            $baseVatAmount == 0
        ) {
            return null;
        }

        return round(
            $exchangeVatAmount / $baseVatAmount,
            4
        );
    }
}