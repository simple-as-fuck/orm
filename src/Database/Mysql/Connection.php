<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Mysql;

final class Connection extends \SimpleAsFuck\Orm\Database\Pdo\Connection
{
    public function __construct(string $host, int $port, ?string $username, ?string $passwd, string $databaseName)
    {
        parent::__construct(new \PDO('mysql:dbname='.$databaseName.';host='.$host.';port='.$port, $username, $passwd));
    }
}
