<?php

namespace App\Helpers;

class CurrencyHelper
{
    private const MAP = [
        '£' => 'GBP',
        '$' => 'USD',
        '€' => 'EUR',
        'kr' => 'DKK',
        'NORGE-FULL' => 'NOK',
        'NOK-SALG' => 'NOK'
    ];

    public static function parseCurrency(?string $currency): ?string
    {
        if (!$currency) {
            return null;
        }

        // normalize spaces
        $currency = trim(preg_replace('/\s+/', ' ', $currency));

        // map symbols / known variants first
        foreach (self::MAP as $key => $iso) {
            if (stripos($currency, $key) !== false) {
                return $iso;
            }
        }

        // remove everything except letters
        $clean = strtoupper(preg_replace('/[^A-Z]/i', '', $currency));

        // validate ISO format (exactly 3 letters)
        if (preg_match('/^[A-Z]{3}$/', $clean)) {
            return $clean;
        }

        return null;
    }

    // public static function parseCurrency(?string $currency): ?string
    // {
    //     if (!$currency) {
    //         return null;
    //     }

    //     $currency = trim(preg_replace('/\s+/', ' ', $currency));

    //     foreach (self::MAP as $key => $iso) {
    //         if (stripos($currency, $key) !== false) {
    //             return $iso;
    //         }
    //     }

    //     if (preg_match('/^[A-Z]{3}$/', strtoupper($currency))) {
    //         return strtoupper($currency);
    //     }

    //     return null;
    // }

    public static function extractCurrencyAndCleanAmount(?string $amount, ?string $fallbackCurrency): array
    {
        if (!$amount) {
            return [null, null];
        }

        $currency = $fallbackCurrency;

        foreach ([
            '£' => 'GBP',
            '$' => 'USD',
            '€' => 'EUR',
        ] as $symbol => $iso) {
            if (stripos($amount, $symbol) !== false) {
                $currency = $iso;
                $amount = str_replace($symbol, '', $amount);
                break;
            }
        }

        $amount = trim($amount);

        return [$currency, $amount];
    }
}