<?php

namespace App\Helpers;

class OcrProgressKeyHelper
{
    public static function getProgressKey(
        string $operation,
        string $progressId
    ): string {
        return "ocr_progress:{$operation}:{$progressId}";
    }
}
