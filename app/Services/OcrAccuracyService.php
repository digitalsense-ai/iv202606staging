<?php

namespace App\Services;

class OcrAccuracyService
{
    /**
     * Enrich mapped invoice data with OCR quality metadata.
     */
    public function enrich(array $normalized, array $azureResult, string $invoiceType = 'sales'): array
    {
        if (isset($normalized['error'])) {
            return $normalized;
        }

        $fullText = $this->extractFullText($azureResult);
        $confidence = $this->extractFieldConfidence($azureResult);
        $candidates = $this->extractCandidates($fullText, $invoiceType);
        $checks = $this->runConsistencyChecks($normalized, $candidates);
        $score = $this->calculateScore($normalized, $confidence, $candidates, $checks);

        $normalized['_ocr'] = [
            'accuracy_score' => $score,
            'requires_review' => $score < 90 || $this->hasCriticalLowConfidence($confidence),
            'confidence' => $confidence,
            'candidates' => $candidates,
            'checks' => $checks,
            'layout_fingerprint' => $this->layoutFingerprint($azureResult),
            'text_hash' => $fullText ? hash('sha256', $fullText) : null,
            'invoice_type' => $invoiceType,
        ];

        return $normalized;
    }

    public function extractFullText(array $azureResult): string
    {
        $content = $azureResult['analyzeResult']['content']
            ?? $azureResult['result']['contents'][0]['markdown']
            ?? $azureResult['content']
            ?? '';

        if (is_string($content) && trim($content) !== '') {
            return trim($content);
        }

        $parts = [];
        foreach (($azureResult['analyzeResult']['pages'] ?? []) as $page) {
            foreach (($page['lines'] ?? []) as $line) {
                if (!empty($line['content'])) {
                    $parts[] = $line['content'];
                }
            }
        }

        return trim(implode("\n", $parts));
    }

    private function extractFieldConfidence(array $azureResult): array
    {
        $documents = $azureResult['analyzeResult']['documents'] ?? [];
        $fields = $documents[0]['fields'] ?? [];
        $confidence = [];

        foreach ($fields as $name => $field) {
            if (isset($field['confidence'])) {
                $confidence[$name] = round((float) $field['confidence'], 4);
            }
        }

        return $confidence;
    }

    private function extractCandidates(string $text, string $invoiceType): array
    {
        $candidates = [
            'invoice_numbers' => [],
            'dates' => [],
            'amounts' => [],
            'currencies' => [],
            'org_numbers' => [],
        ];

        if ($text === '') {
            return $candidates;
        }

        $patterns = [
            'invoice_numbers' => [
                '/(?:invoice|inv|faktura|fakturanr|faktura nr|invoice no)[\s:#.-]*([A-Z0-9][A-Z0-9\/-]{2,})/iu',
                '/\b(?:INV|SI|CI)[-\s]?\d{3,}\b/iu',
            ],
            'dates' => [
                '/\b\d{1,2}[\.\/-]\d{1,2}[\.\/-]\d{2,4}\b/u',
                '/\b\d{4}[\.\/-]\d{1,2}[\.\/-]\d{1,2}\b/u',
            ],
            'amounts' => [
                '/(?:total|amount|balance|subtotal|vat)[^\d]{0,20}([0-9]{1,3}(?:[.,\s][0-9]{3})*(?:[.,][0-9]{2})?)/iu',
            ],
            'currencies' => [
                '/\b(EUR|USD|GBP|DKK|SEK|NOK|CHF|CAD|AUD)\b/u',
            ],
            'org_numbers' => [
                '/(?:cvr|vat|org|organization|company no)[^0-9]{0,15}([0-9][0-9\s.-]{5,20})/iu',
            ],
        ];

        foreach ($patterns as $key => $regexes) {
            foreach ($regexes as $regex) {
                if (preg_match_all($regex, $text, $matches)) {
                    $values = $matches[1] ?? $matches[0] ?? [];
                    foreach ($values as $value) {
                        $clean = trim(preg_replace('/\s+/', ' ', (string) $value));
                        if ($clean !== '' && !in_array($clean, $candidates[$key], true)) {
                            $candidates[$key][] = $clean;
                        }
                    }
                }
            }
        }

        return $candidates;
    }

    private function runConsistencyChecks(array $normalized, array $candidates): array
    {
        $checks = [
            'has_invoice_number_candidate' => !empty($candidates['invoice_numbers']),
            'has_date_candidate' => !empty($candidates['dates']),
            'has_amount_candidate' => !empty($candidates['amounts']),
            'has_currency_candidate' => !empty($candidates['currencies']),
            'amount_consistency' => null,
        ];

        $total = $this->numberFromMixed($normalized['total'] ?? $normalized['total_amount'] ?? null);
        $subtotal = $this->numberFromMixed($normalized['subtotal'] ?? $normalized['sub_total'] ?? null);
        $vat = $this->numberFromMixed($normalized['vat'] ?? $normalized['vat_amount'] ?? null);

        if ($total !== null && $subtotal !== null && $vat !== null) {
            $checks['amount_consistency'] = abs(($subtotal + $vat) - $total) <= 0.05;
        }

        return $checks;
    }

    private function calculateScore(array $normalized, array $confidence, array $candidates, array $checks): int
    {
        $score = 55;

        if ($this->hasAnyValue($normalized, ['invoice_number', 'invoice_no', 'invoice_id'])) {
            $score += 10;
        }
        if ($checks['has_invoice_number_candidate']) {
            $score += 5;
        }
        if ($this->hasAnyValue($normalized, ['invoice_date', 'date'])) {
            $score += 8;
        }
        if ($checks['has_date_candidate']) {
            $score += 4;
        }
        if ($this->hasAnyValue($normalized, ['total', 'total_amount', 'amount'])) {
            $score += 8;
        }
        if ($checks['has_amount_candidate']) {
            $score += 4;
        }
        if ($this->hasAnyValue($normalized, ['currency'])) {
            $score += 3;
        }
        if ($checks['amount_consistency'] === true) {
            $score += 5;
        } elseif ($checks['amount_consistency'] === false) {
            $score -= 10;
        }

        if (!empty($confidence)) {
            $avg = array_sum($confidence) / count($confidence);
            if ($avg >= 0.90) {
                $score += 8;
            } elseif ($avg >= 0.80) {
                $score += 4;
            } elseif ($avg < 0.60) {
                $score -= 10;
            }
        }

        return max(0, min(100, $score));
    }

    private function hasCriticalLowConfidence(array $confidence): bool
    {
        foreach ($confidence as $field => $value) {
            $field = strtolower((string) $field);
            if (preg_match('/invoice|date|amount|total|currency/', $field) && $value < 0.70) {
                return true;
            }
        }

        return false;
    }

    private function layoutFingerprint(array $azureResult): ?string
    {
        $parts = [];

        foreach (($azureResult['analyzeResult']['pages'] ?? []) as $page) {
            $parts[] = implode(':', [
                $page['pageNumber'] ?? '',
                $page['width'] ?? '',
                $page['height'] ?? '',
                $page['unit'] ?? '',
                count($page['lines'] ?? []),
                count($page['tables'] ?? []),
            ]);
        }

        if (empty($parts)) {
            return null;
        }

        return hash('sha256', implode('|', $parts));
    }

    private function hasAnyValue(array $data, array $keys): bool
    {
        foreach ($keys as $key) {
            if (!empty($data[$key])) {
                return true;
            }
        }

        return false;
    }

    private function numberFromMixed(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/[^0-9,.-]/', '', (string) $value);
        if ($clean === '') {
            return null;
        }

        if (substr_count($clean, ',') === 1 && substr_count($clean, '.') === 0) {
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '', $clean);
        }

        return is_numeric($clean) ? (float) $clean : null;
    }
}
