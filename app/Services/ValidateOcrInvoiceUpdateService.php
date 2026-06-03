<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

use App\Services\ValidateOcrInvoiceDuplicateService;

class ValidateOcrInvoiceUpdateService
{
    public function apply($invoice, array $mapped)
    {
        $current = $invoice->extracted_data ?? [];

        $changed = false;

        foreach ($mapped as $key => $value) {

            if (($current[$key] ?? null) !== $value) {

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

        if($changed)
        {
            if($invoice->validation_status == 'not_yet_validated')
                $invoice->og_extracted_data = $invoice->extracted_data ?? [];      

            $invoice->extracted_data = $current;

            $invoice->duplicate_hash = app(
                \App\Services\ValidateOcrInvoiceDuplicateService::class
            )->generateHash($current);                
        }

        $invoice->sync_status = 0;
        $invoice->is_locked = 0;
        $invoice->validation_status = $changed
            ? 'validated_with_changes'
            : 'validated';

        $invoice->save();
    }
}