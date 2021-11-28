<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Renderer;

use PHPUnit\Framework\TestCase;

/**
 * @covers \SimpleAsFuck\Orm\Generator\Renderer\TypeGlobalNameConverter
 */
final class TypeGlobalNameConverterTest extends TestCase
{
    private TypeGlobalNameConverter $converter;

    public function setUp(): void
    {
        $this->converter = new TypeGlobalNameConverter();
    }

    /**
     * @dataProvider dataProviderConvert
     */
    public function testConvert(string $expectedValue, string $typeName): void
    {
        $convertedValue = $this->converter->convert($typeName);

        self::assertSame($expectedValue, $convertedValue);
    }

    /**
     * @return array<array<mixed>>
     */
    public function dataProviderConvert(): array
    {
        return [
            ['string', 'string'],
            ['int', 'int'],
            ['\\DateTime', \DateTime::class],
            ['\\stdClass', \stdClass::class],
        ];
    }
}
