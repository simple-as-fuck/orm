<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Pdo;

final class Result extends \SimpleAsFuck\Orm\Database\Abstracts\Result
{
    private \PDOStatement $statement;

    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function fetch(): ?\stdClass
    {
        $result = $this->statement->fetchObject();
        if ($result === false) {
            return null;
        }
        return $result;
    }
}
