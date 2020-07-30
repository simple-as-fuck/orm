<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Renderer;

class BasicRenderer extends \SimpleAsFuck\Orm\Generator\Abstracts\Renderer
{

    /**
     * @param string $path full path in file system
     * @param object[]|string[] $templateVariables
     */
    public function renderTemplate(string $path, array $templateVariables): string
    {
        ob_start();
        extract($templateVariables, EXTR_SKIP);
        include $path;
        return (string)ob_get_clean();
    }
}
