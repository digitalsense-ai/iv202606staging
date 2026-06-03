<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class CreditNoteHelper
{
    /**
     * Detect whether invoice is a credit note
     */
    public static function isCreditNote(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        $value = Str::lower(trim($value));

        $keywords = [
            'kredittnota',
            'kreditnota',
            'creditnote',
            'credit note',
            'kredit nota',
            'kreditt nota',
            'kreditt',
            'kredit',
            'credit',
            'true',
        ];

        foreach ($keywords as $keyword) {

            if (Str::contains($value, $keyword)) {
                return true;
            }
        }

        return false;
    }
}