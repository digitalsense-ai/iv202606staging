<?php

namespace App\Services;

class OcrAccuracyService
{
    private array $criticalFields = [
        'invoice_number',
        'invoice_date',
        'currency',
        'net_amount',
        'total_amount',
    ];

    public function enrich(array $normalized, array $azureResult, ?string $invoiceType = null): array
    {
        if (isset($normalized['error'])) {
            return $normalized;
        }

        $fullText = $this->extractFullText($azureResult);
        $confidence = $this->extractConfidenceSummary($azureResult);
        $candidates = $this->buildCandidates($normalized, $fullText);
        $score = $this->calculateScore($normalized, $confidence, $candidates, $invoiceType);

        $normalized['_ocr'] = [
            'accuracy_score' => $score,
            'requires_review' => $score < 90 || $this->hasLowCriticalConfidence($confidence),
            'confidence' => $confidence,
            'candidates' => $candidates,
            'layout_fingerprint' => $this->layoutFingerprint($fullText),
            'full_text_hash' => $fullText ? hash('sha256', $fullText) : null,
        ];

        return $normalized;
    }

    private function calculateScore(array $data, array $confidence, array $candidates, ?string $invoiceType): int
    {
        $score = 40;

        foreach ($this->criticalFields as $field) {
            if (!empty($data[$field])) {
                $score += 8;
            }

            if (($confidence[$field] ?? 0) >= 0.85) {
                $score += 4;
            }

            if (!empty($candidates[$field]) && $this->matchesCandidate($data[$field] ?? null, $candidates[$field])) {
                $score += 3;
            }
        }

        if ($this->amountsBalance($data)) {
            $score += 8;
        }

        if ($invoiceType === 'com' && (!empty($data['related_sales_invoices']) || !empty($data['related_shipment_nos']))) {
            $score += 5;
        }

        return max(0, min(100, $score));
    }

    private function extractFullText(array $result): string
    {
        return trim((string) data_get($result, 'analyzeResult.content', data_get($result, 'content', '')));
    }

    private function extractConfidenceSummary(array $result): array
    {
        $documents = data_get($result, 'analyzeResult.documents', data_get($result, 'documents', []));
        $fields = data_get($documents, '0.fields', []);
        $summary = [];

        foreach ($fields as $name => $field) {
            $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', (string) $name));
            $summary[$key] = round((float) ($field['confidence'] ?? 0), 4);
        }

        return $summary;
    }

    private function buildCandidates(array $normalized, string $fullText): array
    {
        $candidates = [];

        $patterns = [
            'invoice_number' => '/(?:invoice|faktura|inv)[\s\.:#-]*(?:no|nr|number)?[\s\.:#-]*([A-Z0-9][A-Z0-9\/-]{3,})/i',
            'invoice_date' => '/(?:date|dato)[\s\.:#-]*(\d{1,2}[\.\/-]\d{1,2}[\.\/-]\d{2,4}|\d{4}-\d{2}-\d{2})/i',
            'currency' => '/\b(EUR|DKK|SEK|NOK|USD|GBP)\b/i',
            'total_amount' => '/(?:total|amount due|grand total)[^0-9-]*([0-9]{1,3}(?:[\., ][0-9]{3})*(?:[\.,][0-9]{2})?)/i',
            'net_amount' => '/(?:net|subtotal|sub total)[^0-9-]*([0-9]{1,3}(?:[\., ][0-9]{3})*(?:[\.,][0-9]{2})?)/i',
        ];

        foreach ($patterns as $field => $pattern) {
            if (preg_match_all($pattern, $fullText, $matches)) {
                $candidates[$field] = array_values(array_unique(array_filter($matches[1])));
            }
        }

        foreach ($this->criticalFields as $field) {
            if (!empty($normalized[$field])) {
                $candidates[$field] = array_values(array_unique(array_merge(
                    [$normalized[$field]],
                    $candidates[$field] ?? []
                )));
            }
        }

        return $candidates;
    }

    private function hasLowCriticalConfidence(array $confidence): bool
    {
        foreach ($this->criticalFields as $field) {
            if (array_key_exists($field, $confidence) && $confidence[$field] > 0 && $confidence[$field] < 0.80) {
                return true;
            }
        }

        return false;
    }

    private function matchesCandidate(mixed $value, array $candidates): bool
    {
        $value = $this->normalizeComparable($value);

        if (!$value) {
            return false;
        }

        foreach ($candidates as $candidate) {
            if ($value === $this->normalizeComparable($candidate)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeComparable(mixed $value): ?string
    {
        if ($value === null || is_array($value)) {
            return null;
        }

        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/[^A-Z0-9]/', '', $value);

        return $value ?: null;
    }

    private function amountsBalance(array $data): bool
    {
        $net = $this->toFloat($data['net_amount'] ?? null);
        $vat = $this->toFloat($data['vat_amount'] ?? null);
        $total = $this->toFloat($data['total_amount'] ?? null);

        if ($net === null || $vat === null || $total === null) {
            return false;
        }

        return abs(($net + $vat) - $total) <= 0.05;
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || is_array($value)) {
            return null;
        }

        $value = str_replace([' ', ','], ['', '.'], (string) $value);
        $value = preg_replace('/[^0-9.-]/', '', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function layoutFingerprint(string $fullText): ?string
    {
        if ($fullText === '') {
            return null;
        }

        $lines = array_slice(array_filter(array_map('trim', preg_split('/\R/', $fullText) ?: [])), 0, 30);
        $signature = preg_replace('/\d+/', '#', implode('|', $lines));

        return hash('sha256', strtoupper($signature));
    }
}
