<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Config\Abstracts;

abstract class Config
{
    abstract public function getString(string $key): string;
}
