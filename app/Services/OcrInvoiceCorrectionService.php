<?php

namespace App\Services;

use App\Models\OcrPdf;
use Illuminate\Support\Arr;

class OcrInvoiceCorrectionService
{
    public function apply(OcrPdf $invoice, array $payload, bool $forceSubmitted = false, ?int $userId = null): array
    {
        $data = $invoice->extracted_data ?? [];
        $invoiceType = Arr::get($payload, 'invoice_type', $invoice->invoice_type);

        $this->set($data, 'invoice_type', $invoiceType);
        $this->set($data, 'supplier.org_number', Arr::get($payload, 'client_no'));
        $this->set($data, 'supplier.name', Arr::get($payload, 'client_name'));
        $this->set($data, 'recipient.org_number', Arr::get($payload, 'client_no'));
        $this->set($data, 'recipient.name', Arr::get($payload, 'client_name'));
        $this->set($data, 'invoice_date', Arr::get($payload, 'invoice_date'));
        $this->set($data, 'invoice_number', Arr::get($payload, 'invoice_no'));
        $this->set($data, 'currency', Arr::get($payload, 'currency'));
        $this->set($data, 'net_amount', Arr::get($payload, 'net_amount'));

        if ($invoiceType === 'com') {
            $this->set($data, 'exchange_currency', Arr::get($payload, 'exchange_currency'));
            $this->set($data, 'exchange_net_amount', Arr::get($payload, 'exchange_net_amount'));
            $this->set($data, 'related_sales_invoices', $this->references(Arr::get($payload, 'related_sales_invoices', [])));

            data_forget($data, 'credit_note');
            data_forget($data, 'vat_rate');
            data_forget($data, 'vat_amount');
            data_forget($data, 'total_amount');
            data_forget($data, 'exchange_rate');
            data_forget($data, 'exchange_vat_amount');
            data_forget($data, 'exchange_total_amount');
        } else {
            $currency = Arr::get($payload, 'currency');
            $exchangeCurrency = Arr::get($payload, 'exchange_currency');
            $netAmount = Arr::get($payload, 'net_amount');
            $exchangeNetAmount = Arr::get($payload, 'exchange_net_amount');
            $vatAmount = Arr::get($payload, 'vat_amount');
            $exchangeVatAmount = Arr::get($payload, 'exchange_vat_amount');
            $totalAmount = Arr::get($payload, 'total_amount');
            $exchangeTotalAmount = Arr::get($payload, 'exchange_total_amount');

            if ($currency !== 'NOK' && $currency !== 'CHF') {
                [$currency, $exchangeCurrency] = [$exchangeCurrency, $currency];
                [$netAmount, $exchangeNetAmount] = [$exchangeNetAmount, $netAmount];
                [$vatAmount, $exchangeVatAmount] = [$exchangeVatAmount, $vatAmount];
                [$totalAmount, $exchangeTotalAmount] = [$exchangeTotalAmount, $totalAmount];
            }

            $this->set($data, 'currency', $currency);
            $this->set($data, 'exchange_currency', $exchangeCurrency);
            $this->set($data, 'net_amount', $netAmount);
            $this->set($data, 'exchange_net_amount', $exchangeNetAmount);
            $this->set($data, 'credit_note', (bool) Arr::get($payload, 'credit_note', false));
            $this->set($data, 'vat_rate', Arr::get($payload, 'vat_rate'));
            $this->set($data, 'vat_amount', $vatAmount);
            $this->set($data, 'total_amount', $totalAmount);
            $this->set($data, 'exchange_rate', Arr::get($payload, 'exchange_rate'));
            $this->set($data, 'exchange_vat_amount', $exchangeVatAmount);
            $this->set($data, 'exchange_total_amount', $exchangeTotalAmount);
            $this->set($data, 'related_sales_invoices', []);
        }

        $note = Arr::get($payload, 'note');
        $this->set($data, 'manual_note', $note);
        $this->set($data, 'manual_input.force_submitted', $forceSubmitted);
        $this->set($data, 'manual_input.updated_at', now()->toDateTimeString());
        $this->set($data, 'manual_input.updated_by', $userId);

        $missing = $this->missingRequiredFields($data);
        $completed = empty($missing) || $forceSubmitted;

        $invoice->update([
            'invoice_type' => $invoiceType,
            'extracted_data' => $data,
            'status' => $completed ? 'completed' : 'failed',
            'error' => $completed ? null : implode("\n", $missing),
            'validation_status' => $completed ? 'validated_with_changes' : 'not_yet_validated',
            'sync_status' => 0,
            'is_locked' => 0,
            'manual_note' => $note,
            'force_submitted' => $forceSubmitted,
            'manual_input_at' => now(),
            'manual_input_by' => $userId,
        ]);

        return [
            'completed' => $completed,
            'missing' => $missing,
        ];
    }

    public function missingRequiredFields(array $data): array
    {
        $missing = [];

        foreach ([
            'invoice_type' => 'Document type missing',
            'invoice_date' => 'Invoice Date missing',
            'invoice_number' => 'Invoice no. missing',
            'currency' => 'Currency missing',
            'net_amount' => 'Net amount missing',
        ] as $field => $message) {
            if (blank(data_get($data, $field))) {
                $missing[] = $message;
            }
        }

        if (data_get($data, 'invoice_type') !== 'com' && blank(data_get($data, 'total_amount'))) {
            $missing[] = 'Total amount missing';
        }

        if (blank(data_get($data, 'recipient.org_number')) && blank(data_get($data, 'supplier.org_number'))) {
            $missing[] = 'Client no. missing';
        }

        return $missing;
    }

    private function set(array &$data, string $key, $value): void
    {
        data_set($data, $key, $value);
    }

    private function references($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }
}
