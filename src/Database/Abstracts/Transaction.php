<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Abstracts;

abstract class Transaction
{
    abstract public function isActive(): bool;
    abstract public function commit(): void;
    abstract public function rollback(): void;
}
