<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

abstract class DirectoryGenerator
{
    /**
     * method create directory structure with files content based on loaded models structure
     *
     * @param ModelStructure[] $modelsStructure
     * @return GeneratedDirectory[]
     */
    abstract public function create(array $modelsStructure, bool $stupidDeveloper): array;
}
