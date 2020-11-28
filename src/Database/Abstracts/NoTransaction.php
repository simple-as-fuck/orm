<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Abstracts;

final class NoTransaction extends Transaction
{
    public function isActive(): bool
    {
        return false;
    }

    public function commit(): void
    {
    }

    public function rollback(): void
    {
    }
}
