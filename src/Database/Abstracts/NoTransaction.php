<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Abstracts;

final class NoTransaction extends Transaction
{
    private ?Transaction $realTransaction;

    public function __construct(Transaction $realTransaction = null)
    {
        $this->realTransaction = $realTransaction;
    }

    public function isActive(): bool
    {
        if (! $this->realTransaction) {
            return false;
        }

        return $this->realTransaction->isActive();
    }

    public function commit(): void
    {
    }

    public function rollback(): void
    {
    }
}
