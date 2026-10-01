<?php

namespace Tests\Unit;

use App\Http\Controllers\ocr\ManualInputController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ManualInputControllerTest extends TestCase
{
    /**
     * @dataProvider localizedAmountProvider
     */
    public function test_it_normalizes_localized_amounts_for_database_storage(
        ?string $amount,
        ?string $expected
    ): void {
        $method = new ReflectionMethod(ManualInputController::class, 'normalizeDecimalAmount');
        $controller = $this->getMockBuilder(ManualInputController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame($expected, $method->invoke($controller, $amount));
    }

    public function localizedAmountProvider(): array
    {
        return [
            'European thousands and decimal separators' => ['3.155,50', '3155.50'],
            'European decimal separator' => ['788,87', '788.87'],
            'database decimal format' => ['25.00', '25.00'],
            'localized spaces' => ["3\u{00A0}944,37", '3944.37'],
            'negative amount' => ['-1.234,56', '-1234.56'],
            'null amount' => [null, null],
        ];
    }
}