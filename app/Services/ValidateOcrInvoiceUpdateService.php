<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ValidateOcrInvoiceUpdateService
{
    public function apply($invoice, array $mapped)
    {
        $current = $invoice->extracted_data ?? [];

        $changed = false;
        $originalOcrMeta = $current['_ocr'] ?? [];
        $layoutFingerprint = $originalOcrMeta['layout_fingerprint'] ?? null;

        foreach ($mapped as $key => $value) {
            if ($key === '_ocr') {
                continue;
            }

            $oldValue = $current[$key] ?? null;

            if ($oldValue !== $value) {
                app(OcrCorrectionFeedbackService::class)->capture(
                    (int) $invoice->id,
                    (string) $key,
                    $oldValue,
                    $value,
                    $invoice->client_id ? (int) $invoice->client_id : null,
                    $layoutFingerprint
                );

                $current[$key] = $value;
                $changed = true;
            }
        }

        if($changed)
        {
            Cache::increment('inbox_completed', 1);

            if($invoice->validation_status == 'not_yet_validated')
                $invoice->og_extracted_data = $invoice->extracted_data ?? [];

            $invoice->extracted_data = $current;

            $duplicateService = app(
                \App\Services\ValidateOcrInvoiceDuplicateService::class
            );

            $invoice->duplicate_hash = $duplicateService->hasMinimumFingerprint($current, $invoice->invoice_type)
                ? $duplicateService->generateHash($current, $invoice->invoice_type)
                : null;
        }

        $invoice->sync_status = 0;
        $invoice->is_locked = 0;
        $invoice->validation_status = $changed
            ? 'validated_with_changes'
            : 'validated';

        $invoice->save();
    }
}
