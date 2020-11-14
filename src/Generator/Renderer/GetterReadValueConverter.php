<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Renderer;

final class GetterReadValueConverter
{
    public function convert(string $value): string
    {
        return 'get' . ucfirst($value) . '()';
    }
}
