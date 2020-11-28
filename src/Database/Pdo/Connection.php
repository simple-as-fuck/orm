<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Pdo;

use SimpleAsFuck\Orm\Database\Abstracts\Query;

class Connection extends \SimpleAsFuck\Orm\Database\Abstracts\Connection
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
        $this->pdo->setAttribute(\PDO::ATTR_ORACLE_NULLS, \PDO::NULL_NATURAL);
        $this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    }

    final public function prepare(string $statement): Query
    {
        $statement = $this->pdo->prepare($statement);
        return new \SimpleAsFuck\Orm\Database\Pdo\Query($this->pdo, $statement);
    }

    final public function beginTransaction(): void
    {
        if ($this->inTransaction()) {
            return;
        }

        $this->pdo->beginTransaction();
    }

    final public function commitTransaction(): void
    {
        if ($this->inTransaction()) {
            $this->pdo->commit();
        }
    }

    final public function rollbackTransaction(): void
    {
        if ($this->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    final public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}
