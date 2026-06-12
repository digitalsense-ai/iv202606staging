<?php

namespace App\Services;

class ClientOcrProfileService
{
    public function forInvoice(?int $clientId, ?string $invoiceType = null, ?string $layoutFingerprint = null): array
    {
        $profiles = config('ocr_profiles.clients', []);

        $profile = $profiles[$clientId] ?? [];
        $defaults = config('ocr_profiles.defaults', []);

        $merged = array_replace_recursive($defaults, $profile);

        if ($invoiceType) {
            $typeProfile = $merged['invoice_types'][$invoiceType] ?? [];
            $merged = array_replace_recursive($merged, $typeProfile);
        }

        if ($layoutFingerprint) {
            $layoutProfile = $merged['layouts'][$layoutFingerprint] ?? [];
            $merged = array_replace_recursive($merged, $layoutProfile);
        }

        unset($merged['invoice_types'], $merged['layouts']);

        return $merged;
    }

    public function patterns(?int $clientId, ?string $invoiceType, ?string $field, ?string $layoutFingerprint = null): array
    {
        $profile = $this->forInvoice($clientId, $invoiceType, $layoutFingerprint);

        return array_values(array_filter($profile['patterns'][$field] ?? []));
    }

    public function normalizeValue(?int $clientId, ?string $invoiceType, string $field, mixed $value, ?string $layoutFingerprint = null): mixed
    {
        if ($value === null || is_array($value)) {
            return $value;
        }

        $profile = $this->forInvoice($clientId, $invoiceType, $layoutFingerprint);
        $value = trim((string) $value);

        foreach (($profile['replacements'][$field] ?? []) as $search => $replace) {
            $value = str_replace((string) $search, (string) $replace, $value);
        }

        return $value;
    }
}
