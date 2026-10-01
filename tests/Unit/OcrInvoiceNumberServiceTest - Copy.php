<?php

namespace Tests\Unit;

use App\Services\OcrInvoiceNumberService;
use PHPUnit\Framework\TestCase;

class OcrInvoiceNumberServiceTest extends TestCase
{
    public function test_it_adds_rainwear_commercial_invoice_number_from_file_name(): void
    {
        $service = new OcrInvoiceNumberService();

        $data = $service->apply(
            ['invoice_number' => 'shared-number'],
            'Rainwear ApS',
            'capture_SF-12345_invoice.pdf',
            'com'
        );

        $this->assertSame('SF-12345', $data['special_capture_invoice_number']);
        $this->assertSame('SF-12345', $data['effective_invoice_number']);
        $this->assertSame('shared-number', $data['original_invoice_number']);
    }

    public function test_it_selects_alternate_numbers_without_losing_the_ocr_number(): void
    {
        $service = new OcrInvoiceNumberService();

        $data = $service->apply(
            ['invoice_number' => 'OCR-123', 'order_number' => 'ORDER-456'],
            'Horn Bord ApS',
            null,
            'com'
        );

        $this->assertSame('OCR-123', $data['original_invoice_number']);
        $this->assertSame('ORDER-456', $data['effective_invoice_number']);
        $this->assertSame('OCR-123', $data['invoice_number']);
    }

    public function test_submitted_number_updates_effective_number_and_preserves_original(): void
    {
        $service = new OcrInvoiceNumberService();

        $data = $service->withSubmittedNumber(
            ['invoice_number' => 'OCR-123'],
            'Engel ApS',
            'INVOICE-789'
        );

        $this->assertSame('OCR-123', $data['original_invoice_number']);
        $this->assertSame('INVOICE-789', $data['effective_invoice_number']);
    }

    public function test_it_does_not_change_other_clients_or_invoice_types(): void
    {
        $service = new OcrInvoiceNumberService();

        $this->assertArrayNotHasKey('special_capture_invoice_number', $service->apply(
            ['invoice_number' => '123'],
            'Another Client',
            'capture_SF-12345_invoice.pdf',
            'com'
        ));

        $this->assertArrayNotHasKey('special_capture_invoice_number', $service->apply(
            ['invoice_number' => '123'],
            'Rainwear ApS',
            'capture_SF-12345_invoice.pdf',
            'sales'
        ));
    }

    public function test_it_preserves_an_existing_special_capture_number(): void
    {
        $service = new OcrInvoiceNumberService();

        $data = $service->apply(
            ['special_capture_invoice_number' => 'SF-999'],
            'Rainwear ApS',
            'capture_SF-12345_invoice.pdf',
            'com'
        );

        $this->assertSame('SF-999', $data['special_capture_invoice_number']);
    }

    /**
     * @dataProvider clientRuleProvider
     */
    public function test_it_describes_special_invoice_number_rules(
        ?string $clientName,
        array $expected
    ): void {
        $this->assertSame(
            $expected,
            (new OcrInvoiceNumberService())->ruleNotes($clientName)
        );
    }

    public function clientRuleProvider(): array
    {
        return [
            'rainwear' => [
                'Rainwear ApS',
                [
                    'Commercial invoices use File Name in place of invoice number.',
                    'Sales invoices use NO Invoice Number in place of invoice number.',
                ],
            ],
            'engel' => [
                'Engel Workwear',
                ['Sales invoices use NO Invoice Number in place of invoice number.'],
            ],
            'berendsohn' => [
                'Berendsohn AG',
                ['Sales invoices use NO Invoice Number in place of invoice number.'],
            ],
            'horn bord' => [
                'Horn Bordplader',
                ['Non-credit invoices use Order Number in place of invoice number.'],
            ],
            'regular client' => ['Example Client', []],
            'missing client' => [null, []],
        ];
    }
}