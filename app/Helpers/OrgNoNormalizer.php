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
            '377642755000' => '377642755',
            '99701560632431125' => '997015606',
            '37960356080621829001' => '379603560',
            '921032129928729605' => '928729605',
            '926402447928729605' => '928729605',
            '898533972928729605' => '928729605',
            '986465812928729605' => '928729605'
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