<?php

namespace Tests\Unit;

use App\Jobs\SplitPdfJob;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class SplitPdfJobTest extends TestCase
{
    /**
     * @dataProvider invoiceTextProvider
     */
    public function test_it_extracts_invoice_numbers_from_ocr_text(string $text, string $expected): void
    {
        $method = new ReflectionMethod(SplitPdfJob::class, 'extractInvoiceNumber');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke(null, $text));
    }

    public function invoiceTextProvider(): array
    {
        return [
            'NO-Invoice direct value' => ["NO-Invoice No.\nINV-454", 'INV-454'],
            'NO-Invoice Nummer layout' => ["NO-Invoice No.\nNummer\nno-123/45", 'NO-123/45'],
            'Invoice punctuation' => ['Invoice No.: ab.123-9', 'AB.123-9'],
            'Norwegian invoice label' => ["Faktura nr.\n987654", '987654'],
            'German invoice label' => ['Rechnungsnr. DE/42', 'DE/42'],
            'legacy punctuation-prefixed number' => ["NO-Invoice No. value\nNummer\n#inv:454", 'INV:454'],
            'legacy Fakturanr characters' => ["Fakturanr.\n2026+09+23", '2026+09+23'],
        ];
    }

    public function test_it_recognizes_an_invoice_heading_when_the_number_is_unreadable(): void
    {
        $method = new ReflectionMethod(SplitPdfJob::class, 'containsInvoiceNumberLabel');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(null, 'NO-Invoice No. ###'));
        $this->assertFalse($method->invoke(null, 'Invoice line items and totals'));
    }

    /**
     * @dataProvider pageRangeProvider
     */
    public function test_it_builds_non_overlapping_ranges(
        array $invoiceByPage,
        int $totalPages,
        array $expected
    ): void {
        $method = new ReflectionMethod(SplitPdfJob::class, 'buildPageRanges');
        $method->setAccessible(true);
        $job = (new ReflectionClass(SplitPdfJob::class))->newInstanceWithoutConstructor();

        $this->assertFalse($method->isStatic());
        $this->assertSame($expected, $method->invoke($job, $invoiceByPage, [], $totalPages));
    }

    public function pageRangeProvider(): array
    {
        return [
            'unreadable first heading belongs to following invoice' => [
                [2 => 'INV-1'],
                2,
                [[1, 2]],
            ],
            'two readable invoices stay separate' => [
                [1 => 'INV-1', 2 => 'INV-2'],
                2,
                [[1, 1], [2, 2]],
            ],
            'unreadable continuation inherits previous invoice' => [
                [1 => 'INV-1'],
                2,
                [[1, 2]],
            ],
            'last invoice remains separate' => [
                [452 => 'INV-262', 454 => 'INV-263'],
                454,
                [[1, 453], [454, 454]],
            ],
        ];
    }
}