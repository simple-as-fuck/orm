<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Pdo;

final class Transaction extends \SimpleAsFuck\Orm\Database\Abstracts\Transaction
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function isActive(): bool
    {
        return $this->pdo->inTransaction();
    }

    public function commit(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollback(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}
