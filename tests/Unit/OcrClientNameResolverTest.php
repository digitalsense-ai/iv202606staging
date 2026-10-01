<?php

namespace Tests\Unit;

use App\Services\OcrClientNameResolver;
use PHPUnit\Framework\TestCase;

class OcrClientNameResolverTest extends TestCase
{
    public function test_it_normalizes_client_numbers_independently_of_formatting(): void
    {
        $resolver = new OcrClientNameResolver();

        $this->assertSame('12345678', $resolver->normalizeClientNumber('DK 12-34-56-78'));
        $this->assertSame('12345678', $resolver->normalizeClientNumber('12345678'));
        $this->assertNull($resolver->normalizeClientNumber(null));
    }

    public function test_it_builds_an_unambiguous_country_and_number_label(): void
    {
        $resolver = new OcrClientNameResolver();

        $this->assertSame(
            'ABC - NO - 123456789',
            $resolver->formatLabel('ABC', 'no', '123456789')
        );
        $this->assertSame(
            'ABC - GB - 987654321',
            $resolver->formatLabel('ABC', 'GB', '987654321')
        );
    }
}