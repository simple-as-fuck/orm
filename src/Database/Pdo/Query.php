<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Pdo;

use SimpleAsFuck\Orm\Database\Abstracts\Result;

final class Query extends \SimpleAsFuck\Orm\Database\Abstracts\Query
{
    private \PDO $pdo;
    private \PDOStatement $statement;

    public function __construct(\PDO $pdo, \PDOStatement $statement)
    {
        $this->pdo = $pdo;
        $this->statement = $statement;
    }

    public function execute(array $inputParameters): Result
    {
        $this->statement->execute($inputParameters);
        return new \SimpleAsFuck\Orm\Database\Pdo\Result($this->pdo, $this->statement);
    }
}
