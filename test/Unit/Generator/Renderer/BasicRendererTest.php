<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Renderer;

use \PHPUnit\Framework\TestCase;

/**
 * @covers \SimpleAsFuck\Orm\Generator\Renderer\BasicRenderer
 */
final class BasicRendererTest extends TestCase
{
    private BasicRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new BasicRenderer();
    }

    public function testRenderTemplate(): void
    {
        $expectedContent = '
Test template

Hello Word';
        $renderedContent = $this->renderer->renderTemplate(__DIR__.'/TestTemplate.php', ['testVariable' => 'Hello Word']);

        static::assertSame($expectedContent, $renderedContent);
    }
}
