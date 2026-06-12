<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OcrCorrectionFeedbackService
{
    public function record(int $invoiceId, string $field, mixed $originalValue, mixed $correctedValue, ?int $clientId = null, ?string $layoutFingerprint = null): void
    {
        if ($originalValue === $correctedValue) {
            return;
        }

        try {
            DB::table('dv_ocr_correction_feedback')->insert([
                'invoice_id' => $invoiceId,
                'client_id' => $clientId,
                'field_name' => $field,
                'original_value' => is_scalar($originalValue) ? (string) $originalValue : json_encode($originalValue),
                'corrected_value' => is_scalar($correctedValue) ? (string) $correctedValue : json_encode($correctedValue),
                'layout_fingerprint' => $layoutFingerprint,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
