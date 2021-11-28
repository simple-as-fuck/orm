<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Renderer;

use PHPUnit\Framework\TestCase;

/**
 * @covers \SimpleAsFuck\Orm\Generator\Renderer\GetterReadValueConverter
 */
final class GetterReadValueConverterTest extends TestCase
{
    private GetterReadValueConverter $converter;

    public function setUp(): void
    {
        $this->converter = new GetterReadValueConverter();
    }

    /**
     * @dataProvider dataProviderConvert
     */
    public function testConvert(string $expectedValue, string $propertyName): void
    {
        $convertedValue = $this->converter->convert($propertyName);

        self::assertSame($expectedValue, $convertedValue);
    }

    /**
     * @return array<array<mixed>>
     */
    public function dataProviderConvert(): array
    {
        return [
            ['getTest()', 'test'],
            ['getTest()', 'Test'],
        ];
    }
}
