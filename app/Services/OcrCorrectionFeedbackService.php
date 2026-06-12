<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OcrCorrectionFeedbackService
{
    public function capture(
        int $invoiceId,
        string $field,
        mixed $originalValue,
        mixed $correctedValue,
        ?int $clientId = null,
        ?string $layoutFingerprint = null
    ): bool {
        if ($originalValue === $correctedValue) {
            return false;
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

            return true;
        } catch (\Throwable $e) {
            Log::warning('OCR feedback capture failed: ' . $e->getMessage());
            return false;
        }
    }

    public function commonCorrections(string $field, ?int $clientId = null, int $limit = 25): array
    {
        $query = DB::table('dv_ocr_correction_feedback')
            ->select('original_value', 'corrected_value', DB::raw('COUNT(*) as occurrences'))
            ->where('field_name', $field)
            ->groupBy('original_value', 'corrected_value')
            ->orderByDesc('occurrences')
            ->limit($limit);

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return $query->get()->map(function ($row) {
            return [
                'original' => $row->original_value,
                'corrected' => $row->corrected_value,
                'occurrences' => (int) $row->occurrences,
            ];
        })->all();
    }

    public function suggest(string $field, string $value, ?int $clientId = null): ?string
    {
        $query = DB::table('dv_ocr_correction_feedback')
            ->where('field_name', $field)
            ->where('original_value', $value)
            ->orderByDesc('updated_at');

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $match = $query->first();

        return $match?->corrected_value;
    }
}
