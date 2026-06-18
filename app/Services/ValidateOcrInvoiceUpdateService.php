<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Services\ValidateOcrInvoiceDuplicateService;
use App\Models\OcrPdf;

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
                $current[$key] = $value;
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

            //if (($current[$key] ?? null) !== $value) {

                // Log::info('Field changed', [
                //     'invoice_id' => $invoice->id,
                //     'field' => $key,
                //     'old' => $current[$key] ?? null,
                //     'new' => $value,
                // ]);
                
                $current[$key] = $value;
                $changed = true;
            }
        }

        $save_invoice = OcrPdf::query()->find($invoice->id);

        if($changed)
        {            
            Cache::increment('inbox_completed', 1);

            //if($invoice->validation_status == 'not_yet_validated')
            //    $invoice->og_extracted_data = $invoice->extracted_data ?? [];      

            $save_invoice->extracted_data = $current;
            $save_invoice->error = ($mapped['error']) ?? null;
            $save_invoice->status = isset($mapped['error']) ? 'failed' : 'completed';

            $duplicateService = app(
                \App\Services\ValidateOcrInvoiceDuplicateService::class
            );

            $save_invoice->duplicate_hash = $duplicateService->hasMinimumFingerprint($current, $invoice->invoice_type)
                ? $duplicateService->generateHash($current, $invoice->invoice_type)
                : null;
        }

        $save_invoice->sync_status = 0;
        $save_invoice->is_locked = 0;
        $save_invoice->validation_status = $changed
            ? 'validated_with_changes'
            : 'validated';

        $save_invoice->save();
    }
}