<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

abstract class Renderer
{
    /**
     * @param string $path full path in file system
     * @param array<mixed> $templateVariables
     */
    abstract public function renderTemplate(string $path, array $templateVariables): string;
}
