<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class OrgNoNormalizer
{
    public static function normalize(?string $orgNo, ?string $name = null): ?string
    {
        if (!$orgNo) {
            return null;
        }

        // remove spaces
        //$orgNo = preg_replace('/\s+/', '', trim($orgNo));
        $orgNo = preg_replace('/\D+/', '', $orgNo);

        /**
         * Exact malformed replacements
         */
        $replacements = [
            '91644842'     => '919644842',
            '369530275000' => '369530275',
            '99701560632431125' => '997015606',
        ];

        if (isset($replacements[$orgNo])) {
            return $replacements[$orgNo];
        }

        /**
         * Context-aware correction
         */
        // if (
        //     $orgNo === '41456566' &&
        //     $name &&
        //     Str::contains(Str::lower($name), 'almuegaarden')
        // ) {
        //     return '831160462';
        // }

        return $orgNo;
    }
}