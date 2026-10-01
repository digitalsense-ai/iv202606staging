<?php

namespace Tests\Unit;

use App\Services\OcrInvoiceNumberService;
use PHPUnit\Framework\TestCase;

class OcrInvoiceNumberServiceTest extends TestCase
{
    /**
     * @dataProvider specialRuleProvider
     */
    public function test_it_lists_all_applicable_special_rules(string $clientName, array $expectedFragments): void
    {
        $notes = (new OcrInvoiceNumberService())->ruleNotes($clientName);
        $description = implode(' ', $notes);

        foreach ($expectedFragments as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $description);
        }
    }

    public function specialRuleProvider(): array
    {
        return [
            'STOF invoice and reference rules' => [
                'STOF Denmark',
                ['all hyphens are removed', '10-digit numbers beginning with 20'],
            ],
            'ADAG charge calculation rules' => [
                'ADAG ApS',
                ['newline-separated numeric charges are added together', '|Net Amount| + |Additional Charges|'],
            ],
            'SGI variance discount rules' => [
                'SGI Wholesale',
                ['Variance and Discount Amount values are swapped', 'captured variance is used as the discount amount'],
            ],
            'Horn invoice number and credit-note rules' => [
                'Horn Bordplader A/S',
                ['use Order Number', 'beginning with KRE-', 'credit note'],
            ],
            'Rainwear has every invoice-number rule' => [
                'Rainwear Denmark',
                ['SF-number from the file name', 'first is treated as NO Invoice Number', 'sales invoices use NO Invoice Number'],
            ],
        ];
    }

    public function test_it_returns_no_notes_for_a_client_without_special_rules(): void
    {
        $this->assertSame([], (new OcrInvoiceNumberService())->ruleNotes('Ordinary Customer'));
    }
}