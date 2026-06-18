<?php

namespace App\Services\Parsers;

use App\Contracts\OcrInvoiceParser;
use App\Services\ClientOcrProfileService;
use App\Services\OcrCorrectionFeedbackService;

abstract class BaseInvoiceParser implements OcrInvoiceParser
{
    public function __construct(
        protected ClientOcrProfileService $profiles,
        protected OcrCorrectionFeedbackService $feedback
    ) {}

    public function parse(array $normalized, array $azureResult, ?int $clientId = null, string $invoiceType = 'sales'): array
    {
        if (isset($normalized['error'])) {
            return $normalized;
        }

        $normalized = $this->normalizeCommonFields($normalized);
        $normalized = $this->applyProfileCandidates($normalized, $azureResult, $clientId);        
        $normalized = $this->recordFeedbackSuggestions($normalized, $clientId);

        return $normalized;
    }

    protected function normalizeCommonFields(array $data): array
    {
        foreach (['invoice_number', 'invoice_no'] as $field) {
            if (!empty($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim(preg_replace('/\s+/', '', $data[$field]));
            }
        }

        foreach (['currency', 'exchange_currency'] as $field) {
            if (!empty($data[$field]) && is_string($data[$field])) {
                $data[$field] = strtoupper(trim($data[$field]));
            }
        }

        foreach (['supplier', 'recipient'] as $party) {
            if (!empty($data[$party]['org_number']) && is_string($data[$party]['org_number'])) {
                $data[$party]['org_number'] = preg_replace('/[^0-9A-Z]/i', '', $data[$party]['org_number']);
            }
        }

        foreach (['net_amount', 'vat_amount', 'total_amount', 'exchange_net_amount', 'exchange_vat_amount', 'exchange_total_amount'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->normalizeAmount($data[$field]);
            }
        }

        return $data;
    }

    protected function applyProfileCandidates(array $data, array $azureResult, ?int $clientId): array
    {
        $text = $this->extractText($azureResult);

        if ($text === '') {
            return $data;
        }

        if (empty($data['invoice_number'])) {
            $candidates = $this->profiles->extractCandidates('invoice_number', $text, $clientId);
            if (!empty($candidates[0])) {
                $data['invoice_number'] = $candidates[0];
            }
        }

        if (empty($data['currency'])) {
            $candidates = $this->profiles->extractCandidates('currency', $text, $clientId);
            if (!empty($candidates[0])) {
                $data['currency'] = strtoupper($candidates[0]);
            }
        }

        return $data;
    }

    protected function recordFeedbackSuggestions(array $data, ?int $clientId): array
    {
        foreach ($this->correctableFields() as $field) {
            $current = data_get($data, $field);

            if ($current === null || $current === '' || !is_scalar($current)) {
                continue;
            }

            $suggested = $this->feedback->suggest($field, (string) $current, $clientId);

            if ($suggested !== null && $suggested !== (string) $current) {
                data_set($data, '_ocr.feedback_suggestions.' . str_replace('.', '_', $field), [
                    'current' => $current,
                    'suggested' => $suggested,
                    'source' => 'feedback_history',
                    'applied' => false,
                ]);
            }
        }

        return $data;
    }

    protected function correctableFields(): array
    {
        return [
            'invoice_number',
            'invoice_date',
            'currency',
            'supplier.org_number',
            'supplier.name',
            'recipient.org_number',
            'recipient.name',
        ];
    }

    protected function extractText(array $azureResult): string
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

    protected function normalizeAmount(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (is_numeric($value)) {
            return $value;
        }

        $clean = preg_replace('/[^0-9,.-]/', '', (string) $value);

        if ($clean === '') {
            return $value;
        }

        if (substr_count($clean, ',') === 1 && substr_count($clean, '.') === 0) {
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '', $clean);
        }

        return is_numeric($clean) ? $clean : $value;
    }
}