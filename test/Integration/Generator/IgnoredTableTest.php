<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator;

use PHPUnit\Framework\TestCase;

final class IgnoredTableTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__.'/../../../vendor/autoload.php';
    }

    public function testNotGenerateClassForIgnoredTable(): void
    {
        static::assertFalse(class_exists('App\\Model\\Database\\TestIgnoredTable'));
        static::assertFalse(class_exists('App\\Model\\Database\\Generated\\TestIgnoredTable'));
        static::assertFalse(class_exists('App\\Model\\Database\\Generated\\TestIgnoredTableResult'));
        static::assertFalse(class_exists('App\\Service\\Database\\TestIgnoredTableRepository'));
        static::assertFalse(class_exists('App\\Service\\Database\\Generated\\TestIgnoredTableRepository'));

        static::assertTrue(class_exists(\App\Model\Database\TestAutoIncrement::class));
        static::assertTrue(class_exists(\App\Model\Database\Generated\TestAutoIncrement::class));
        static::assertTrue(class_exists(\App\Model\Database\Generated\TestAutoIncrementResult::class));
        static::assertTrue(class_exists(\App\Service\Database\TestAutoIncrementRepository::class));
        static::assertTrue(class_exists(\App\Service\Database\Generated\TestAutoIncrementRepository::class));
    }
}
