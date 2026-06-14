<?php

namespace App\Services;

class ClientOcrProfileService
{
    public function profile(?int $clientId = null): array
    {
        $default = config('ocr_profiles.default', []);

        if (!$clientId) {
            return $default;
        }

        $clientProfile = config("ocr_profiles.clients.{$clientId}", []);

        return $this->mergeProfiles($default, $clientProfile);
    }

    public function patterns(string $field, ?int $clientId = null): array
    {
        $profile = $this->profile($clientId);

        return $profile[$field . '_patterns'] ?? [];
    }

    public function reviewThreshold(?int $clientId = null): int
    {
        return (int) ($this->profile($clientId)['review_threshold'] ?? 90);
    }

    public function criticalFields(?int $clientId = null): array
    {
        return $this->profile($clientId)['critical_fields'] ?? [];
    }

    public function extractCandidates(string $field, string $text, ?int $clientId = null): array
    {
        $values = [];

        foreach ($this->patterns($field, $clientId) as $pattern) {
            if (!is_string($pattern) || $pattern === '') {
                continue;
            }

            if (@preg_match_all($pattern, $text, $matches) === false) {
                continue;
            }

            $matchesForPattern = $matches[1] ?? $matches[0] ?? [];

            foreach ($matchesForPattern as $value) {
                $clean = trim(preg_replace('/\s+/', ' ', (string) $value));

                if ($clean !== '' && !in_array($clean, $values, true)) {
                    $values[] = $clean;
                }
            }
        }

        return $values;
    }

    public function shouldReview(array $ocrMeta, ?int $clientId = null): bool
    {
        $threshold = $this->reviewThreshold($clientId);
        $score = (int) ($ocrMeta['accuracy_score'] ?? 0);

        if ($score > 0 && $score < $threshold) {
            return true;
        }

        foreach ($this->criticalFields($clientId) as $field) {
            $confidence = $ocrMeta['confidence'][$field] ?? null;

            if ($confidence !== null && (float) $confidence < 0.70) {
                return true;
            }
        }

        return (bool) ($ocrMeta['requires_review'] ?? false);
    }

    private function mergeProfiles(array $default, array $clientProfile): array
    {
        foreach ($clientProfile as $key => $value) {
            if (is_array($value) && isset($default[$key]) && is_array($default[$key])) {
                $default[$key] = array_values(array_unique(array_merge($default[$key], $value)));
                continue;
            }

            $default[$key] = $value;
        }

        return $default;
    }
}