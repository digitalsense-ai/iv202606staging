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
        'NOK-SALG' => 'NOK',
        'NOK NOK' => 'NOK',
        'EURO' => 'EUR',
    ];

    public static function parseCurrency(?string $currency): ?string
    {
        if (!$currency) {
            return null;
        }

        // normalize spaces
        $currency = trim(preg_replace('/\s+/', ' ', $currency));        

        /*
        |--------------------------------------------------------------------------
        | Map known symbols / aliases
        |--------------------------------------------------------------------------
        */
        foreach (self::MAP as $key => $iso) {

            if (stripos($currency, $key) !== false) {
                return $iso;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Handle exchange pairs:
        | DKK/NOK -> NOK
        | EUR-NOK -> NOK
        |--------------------------------------------------------------------------
        */
        if (preg_match('/[\/\-]/', $currency)) {

            $parts = preg_split('/[\/\-]/', $currency);

            if (!empty($parts)) {
                $currency = trim(end($parts));
            }
        }
        
        /*
        |--------------------------------------------------------------------------
        | Clean non letters
        |--------------------------------------------------------------------------
        */
        $clean = strtoupper(preg_replace('/[^A-Z]/i', '', $currency));

        /*
        |--------------------------------------------------------------------------
        | Validate ISO currency
        |--------------------------------------------------------------------------
        */
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

    //     // normalize spaces
    //     $currency = trim(preg_replace('/\s+/', ' ', $currency));

    //     // map symbols / known variants first
    //     foreach (self::MAP as $key => $iso) {
    //         if (stripos($currency, $key) !== false) {
    //             return $iso;
    //         }
    //     }

    //     // remove everything except letters
    //     $clean = strtoupper(preg_replace('/[^A-Z]/i', '', $currency));

    //     // validate ISO format (exactly 3 letters)
    //     if (preg_match('/^[A-Z]{3}$/', $clean)) {
    //         return $clean;
    //     }

    //     return null;
    // }

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

        // foreach ([
        //     '£' => 'GBP',
        //     '$' => 'USD',
        //     '€' => 'EUR',
        // ] as $symbol => $iso) {

        foreach (self::MAP as $symbol => $iso) {
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