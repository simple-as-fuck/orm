<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

abstract class StructureLoader
{
    /**
     * method load structure for all models from some source
     *
     * @return ModelStructure[]
     */
    abstract public function loadModels(): array;
}
