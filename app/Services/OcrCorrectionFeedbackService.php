<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\OcrCorrectionFeedback;

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

        if (!$this->isUsefulCorrectionValue($originalValue) || !$this->isUsefulCorrectionValue($correctedValue)) {
            return false;
        }

        $original = $this->stringValue($originalValue);
        $corrected = $this->stringValue($correctedValue);

        if ($original === null || $corrected === null || trim($original) === '' || trim($corrected) === '') {
            return false;
        }

        try {
            OcrCorrectionFeedback::query()->insert([
                'invoice_id' => $invoiceId,
                'client_id' => $clientId,
                'field_name' => $field,
                'original_value' => $original,
                'corrected_value' => $corrected,
                'original_value_hash' => $this->hashValue($original),
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
        $query = OcrCorrectionFeedback::query()
            ->select('original_value', 'corrected_value', DB::raw('COUNT(*) as occurrences'))
            ->where('field_name', $field)
            ->whereNotNull('original_value')
            ->whereNotNull('corrected_value')
            ->where('original_value', '!=', '')
            ->where('corrected_value', '!=', '')
            ->where('corrected_value', 'not like', '{%')
            ->where('corrected_value', 'not like', '[%')
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
        $query = OcrCorrectionFeedback::query()
            ->where('field_name', $field)
            ->where('original_value_hash', $this->hashValue($value))
            ->whereNotNull('corrected_value')
            ->where('corrected_value', '!=', '')
            ->where('corrected_value', 'not like', '{%')
            ->where('corrected_value', 'not like', '[%')
            ->orderByDesc('updated_at');

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $match = $query->first();

        return $match?->corrected_value;
    }

    private function isUsefulCorrectionValue(mixed $value): bool
    {
        return $value === null || is_scalar($value);
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? trim((string) $value) : null;
    }

    private function hashValue(?string $value): ?string
    {
        return $value === null ? null : hash('sha256', $value);
    }
}
