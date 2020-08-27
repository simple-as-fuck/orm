<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Mysql;

use SimpleAsFuck\Orm\Database\Abstracts\Result;

final class Connection extends \SimpleAsFuck\Orm\Database\Abstracts\Connection
{
    private \PDO $pdo;

    public function __construct(string $host, int $port, ?string $username, ?string $passwd, string $databaseName)
    {
        $this->pdo = new \PDO('mysql:dbname='.$databaseName.';host='.$host.';port='.$port, $username, $passwd);
    }

    public function query(string $statement, array $inputParameters): Result
    {
        $statement = $this->pdo->prepare($statement);
        $statement->execute($inputParameters);

        return new \SimpleAsFuck\Orm\Database\Pdo\Result($statement);
    }
}
