<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Abstracts;

abstract class Connection
{
    /**
     * send one query into database and return result, which can be fetch
     *
     * @param string[]|int[]|float[]|null[] $inputParameters
     */
    final public function query(string $statement, array $inputParameters): Result
    {
        return $this->prepare($statement)->execute($inputParameters);
    }

    /**
     * prepare query in database for future execution
     */
    abstract public function prepare(string $statement): Query;

    abstract public function beginTransaction(): void;
    abstract public function commitTransaction(): void;
    abstract public function rollbackTransaction(): void;
    abstract public function inTransaction(): bool;
}
