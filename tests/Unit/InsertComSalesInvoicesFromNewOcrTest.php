<?php

namespace Tests\Unit;

use App\Jobs\InsertComSalesInvoicesFromNewOcr;
use PHPUnit\Framework\TestCase;

class InsertComSalesInvoicesFromNewOcrTest extends TestCase
{
    public function test_it_keeps_only_references_without_a_supporting_invoice(): void
    {
        $references = ['300775', '300776', '300777', '300778', '300779', '300780', '300781', '300782'];
        $available = ['300775', '300776', '300777', '300778', '300779', '300780', '300781'];

        $this->assertSame(
            ['300782'],
            InsertComSalesInvoicesFromNewOcr::missingReferenceNumbers($references, $available)
        );
    }

    public function test_it_normalizes_and_deduplicates_references(): void
    {
        $this->assertSame(
            ['300782'],
            InsertComSalesInvoicesFromNewOcr::missingReferenceNumbers(
                [' 300781 ', '300782', '300782', ''],
                ['300781', null]
            )
        );
    }
}