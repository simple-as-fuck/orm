<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Abstracts;

abstract class Connection
{
    /**
     * send one query into database and return result, which can be fetch
     */
    abstract public function query(string $statement, array $inputParameters): Result;
}
