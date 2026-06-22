<?php

namespace App\Support;

class OcrFallbackFieldExtractor
{
    public static function invoiceNumber(string $content): ?string
    {
        $lines = preg_split('/\R/u', $content) ?: [];

        /*
        |--------------------------------------------------------------------------
        | 1. Look around invoice labels first
        |--------------------------------------------------------------------------
        */
        $labels = [
            'invoice no',
            'invoice number',
            'invoice nr',
            'fakturanr',
            'faktura nr',
            'samlefaktura',
            'faktura',
        ];

        foreach ($lines as $index => $line) {

            $normalized = mb_strtolower(trim($line));

            foreach ($labels as $label) {

                if (!str_contains($normalized, $label)) {
                    continue;
                }

                // Same line
                if (
                    preg_match(
                        '/\b([A-Z]{2,5}\d{4,10})\b/u',
                        $line,
                        $match
                    )
                ) {
                    return strtoupper($match[1]);
                }

                // Next 3 lines
                for ($i = 1; $i <= 3; $i++) {

                    $candidateLine = trim($lines[$index + $i] ?? '');

                    if (
                        preg_match(
                            '/\b([A-Z]{2,5}\d{4,10})\b/u',
                            $candidateLine,
                            $match
                        )
                    ) {
                        return strtoupper($match[1]);
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Generic fallback
        |--------------------------------------------------------------------------
        */
        if (
            preg_match_all(
                '/\b([A-Z]{2,5}\d{4,10})\b/u',
                $content,
                $matches
            )
        ) {

            foreach ($matches[1] as $candidate) {

                $candidate = strtoupper(trim($candidate));

                $prefix = preg_replace('/\d.*/', '', $candidate);

                if (
                    in_array(
                        $prefix,
                        ['CVR', 'VAT', 'ORG', 'MVA', 'EAN'],
                        true
                    )
                ) {
                    continue;
                }

                $digits = preg_replace('/\D/', '', $candidate);

                if (strlen($digits) > 10) {
                    continue;
                }

                return $candidate;
            }
        }

        return null;
    }

    // public static function invoiceNumber(string $content): ?string
    // {
    //     $patterns = [
    //         // Most reliable: explicit labels
    //         '/(?:Samlefaktura|Invoice\s*No|Invoice\s*Number|Fakturanr\.?|Faktura\s*nr\.?)\s*[:.]?\s*([A-Z0-9\-]{4,20})/iu',
    //     ];

    //     foreach ($patterns as $pattern) {
    //         if (preg_match($pattern, $content, $m)) {
    //             return strtoupper(trim($m[1]));
    //         }
    //     }

    //     // Generic fallback
    //     if (preg_match_all('/\b([A-Z]{2,5}\d{4,10})\b/u', $content, $matches)) {

    //         foreach ($matches[1] as $candidate) {

    //             $candidate = strtoupper(trim($candidate));

    //             // Ignore known non-invoice prefixes
    //             $prefix = preg_replace('/\d.*/', '', $candidate);

    //             if (in_array($prefix, [
    //                 'CVR',
    //                 'VAT',
    //                 'ORG',
    //                 'MVA',
    //                 'EAN',
    //             ], true)) {
    //                 continue;
    //             }

    //             // Ignore unrealistically long identifiers
    //             $digits = preg_replace('/\D/', '', $candidate);

    //             if (strlen($digits) > 10) {
    //                 continue;
    //             }

    //             return $candidate;
    //         }
    //     }

    //     return null;
    // }

    public static function invoiceDate(string $content): ?string
    {
        if (preg_match(
            '/(?:Dato|Invoice Date|Fakturadato).*?(\d{1,2}[\/.-]\d{1,2}[\/.-]\d{2,4})/isu',
            $content,
            $m
        )) {
            return trim($m[1]);
        }

        if (preg_match('/^\s*(\d{1,2}[\/.-]\d{1,2}[\/.-]\d{2,4})\s*$/m', $content, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    // public static function clientNumber(string $content): ?string
    // {
    //     $patterns = [

    //         // VAT No. 123 435 345 MVA
    //         '/VAT\s*No\.?\s*([0-9\s.-]{6,20})\s*MVA/iu',

    //         // CVR/ORG No NO123432789
    //         '/(?:CVR\/ORG\s*No|CVR\s*No|ORG\s*No|Org\.?\s*Nr\.?|SE\/CVR-nr\.?)\s*[:.]?\s*([A-Z]{0,4}\s*\d[\d\s.-]{5,20})/iu',
    //     ];

    //     foreach ($patterns as $pattern) {
    //         if (preg_match($pattern, $content, $m)) {
    //             return trim($m[1]);
    //         }
    //     }

    //     // CHE-123.458.987
    //     if (preg_match('/\b(CHE[- ]?\d{3}\.\d{3}\.\d{3})\b/i', $content, $m)) {
    //         return strtoupper($m[1]);
    //     }

    //     // NO123432789
    //     if (preg_match('/\b(NO\d{9})\b/i', $content, $m)) {
    //         return strtoupper($m[1]);
    //     }

    //     // DK25365682, SE5561234567
    //     if (preg_match('/\b([A-Z]{2}\d{8,12})\b/i', $content, $m)) {
    //         return strtoupper($m[1]);
    //     }

    //     // 914733057, 25365682
    //     if (preg_match('/\b(\d{8,12})\b/', $content, $m)) {
    //         return $m[1];
    //     }

    //     return null;
    // }    

    public static function clientNumber(string $content): ?string
    {
        $patterns = [

            // VAT No. 123 435 345 MVA
            '/VAT\s*No\.?\s*([0-9\s.-]{6,20})\s*MVA/iu',

            // 123 435 345 MVA
            '/\b([0-9][0-9\s.-]{6,20})\s*MVA\b/iu',

            // CVR/ORG No NO123432789           
            '/(?:CVR\/ORG\s*No|CVR\s*No|ORG\s*No|Org\.?\s*Nr\.?|Org\.?\s*No\.?|SE\/CVR-nr\.?|EORI\s*No)\s*[:.]?\s*([A-Z]{0,4}\s*\d[\d\s.-]{5,20})/iu',

            // CHE-123.458.987
            '/\b(CHE[- ]?\d{3}\.\d{3}\.\d{3})\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $m)) {
                return trim($m[1]);
            }
        }

        // NO123432789        
        if (preg_match('/\b(NO\d{9})(?:\s*MVA)?\b/i', $content, $m)) {
            return strtoupper($m[1]);
        }

        // CHE-123.458.987
        if (preg_match('/\b(CHE[- ]?\d{3}\.\d{3}\.\d{3})\b/i', $content, $m)) {
            return strtoupper($m[1]);
        }

        // VAT No. 123 435 345 MVA
        if (preg_match('/VAT\s*No\.?\s*([0-9\s.-]{6,20})\s*MVA/iu', $content, $m)) {
            return trim($m[1]);
        }

        // 123 435 345 MVA
        if (preg_match('/\b([0-9][0-9\s.-]{6,20})\s*MVA\b/iu', $content, $m)) {
            return trim($m[1]);
        }

        // CVR/ORG No NO123432789
        if (
            preg_match(
                '/(?:CVR\/ORG\s*No|CVR\s*No|ORG\s*No|Org\.?\s*Nr\.?|SE\/CVR-nr\.?)\s*[:.]?\s*([A-Z]{0,4}\s*\d[\d\s.-]{5,20})/iu',
                $content,
                $m
            )
        ) {
            return trim($m[1]);
        }

        return null;
    }

    public static function currency(string $content): ?string
    {
        $lines = preg_split('/\R/u', $content) ?: [];

        $currencies = [
            'NOK', 'GBP', 'CHF', 'USD', 'EUR',
            'SEK', 'DKK', 'INR', 'JPY', 'CNY',
            'AUD', 'CAD', 'SGD', 'HKD'
        ];

        $symbols = [
            '$' => 'USD',
            '€' => 'EUR',
            '£' => 'GBP',
            '₹' => 'INR',
            '¥' => 'JPY',
        ];

        foreach ($lines as $index => $line) {

            $line = trim($line);

            // Match currency codes
            foreach ($currencies as $currency) {
                if (stripos($line, $currency) !== false) {
                    return $currency;
                }
            }

            // Match symbols
            foreach ($symbols as $symbol => $currency) {
                if (str_contains($line, $symbol)) {
                    return $currency;
                }
            }

            // Optional: look around nearby lines
            $nearby = implode(' ', array_slice($lines, max(0, $index - 1), 3));

            foreach ($currencies as $currency) {
                if (stripos($nearby, $currency) !== false) {
                    return $currency;
                }
            }
        }

        return null;
    }

    public static function invoiceType(string $content): ?string
    {
        $lines = preg_split('/\R/u', $content) ?: [];
        
        foreach ($lines as $index => $line) {

            $line = strtolower(trim($line));

            if (stripos($line, 'reason for export') !== false) {
                return true;
            }                
        }

        return null;
    }   

    public static function exchangeVatAmount(string $content, bool $both = false): string|array|null
    {
        $lines = preg_split('/\R/u', $content) ?: [];

        foreach ($lines as $line) {

            $line = trim($line);

            if (
                preg_match(
                    '/oplyses\s+momsbeløbet\s+i\s+([A-Z]{3})[,:\s]*([\d.,]+)\s*\1/i',
                    $line,
                    $m
                )
            ) {

                $currency = strtoupper(trim($m[1]));
                $amount   = trim($m[2]);

                if ($both) {
                    return [
                        'currency' => $currency,
                        'amount'   => $amount,
                    ];
                }

                return $amount;
            }
        }

        return null;
    } 
}