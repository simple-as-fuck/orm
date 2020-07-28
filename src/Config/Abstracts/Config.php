<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Config\Abstracts;

abstract class Config
{
    /**
     * @return string|bool|string[]
     */
    abstract public function getValue(string $key);
}
