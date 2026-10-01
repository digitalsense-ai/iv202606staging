<?php

namespace Tests\Unit;

use App\Services\ValidateOcrInvoiceDuplicateService;
use PHPUnit\Framework\TestCase;

class ValidateOcrInvoiceDuplicateServiceTest extends TestCase
{
    public function test_commercial_hash_uses_special_capture_invoice_number_when_present(): void
    {
        $service = new ValidateOcrInvoiceDuplicateService();
        $base = [
            'invoice_number' => 'same-number',
            'currency' => 'DKK',
            'recipient' => ['org_number' => '12345678'],
        ];

        $first = $service->generateHash($base + ['special_capture_invoice_number' => 'SF-100'], 'com');
        $second = $service->generateHash($base + ['special_capture_invoice_number' => 'SF-200'], 'com');

        $this->assertNotSame($first, $second);
    }

    public function test_effective_number_takes_priority_without_removing_original_number(): void
    {
        $service = new ValidateOcrInvoiceDuplicateService();
        $base = [
            'invoice_number' => 'original-number',
            'original_invoice_number' => 'original-number',
            'currency' => 'DKK',
            'recipient' => ['org_number' => '12345678'],
        ];

        $first = $service->generateHash($base + ['effective_invoice_number' => 'ORDER-100'], 'com');
        $second = $service->generateHash($base + ['effective_invoice_number' => 'ORDER-200'], 'com');

        $this->assertNotSame($first, $second);
    }
}