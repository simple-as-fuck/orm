<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Renderer;

final class TypeGlobalNameConverter
{
    public function convert(string $value): string
    {
        if (class_exists($value)) {
            $value = "\\".$value;
        }

        return $value;
    }
}
