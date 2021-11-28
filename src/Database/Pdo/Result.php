<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Pdo;

final class Result extends \SimpleAsFuck\Orm\Database\Abstracts\Result
{
    private \PDO $pdo;
    private \PDOStatement $statement;

    public function __construct(\PDO $pdo, \PDOStatement $statement)
    {
        $this->pdo = $pdo;
        $this->statement = $statement;
    }

    public function fetch(): ?\stdClass
    {
        /** @var \stdClass|false $result */
        $result = $this->statement->fetchObject();
        if ($result === false) {
            return null;
        }
        return $result;
    }

    public function lastInsertedId(): ?string
    {
        $id = $this->pdo->lastInsertId();
        if ($id === false) {
            return null;
        }

        return $id;
    }
}
