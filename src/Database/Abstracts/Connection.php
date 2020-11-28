<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Abstracts;

abstract class Connection
{
    private Transaction $transaction;

    public function __construct()
    {
        $this->transaction = new NoTransaction();
    }

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

    final public function beginTransaction(): Transaction
    {
        if ($this->transaction->isActive()) {
            return new NoTransaction();
        }

        $this->transaction = $this->createTransaction();
        return $this->transaction;
    }

    abstract protected function createTransaction(): Transaction;
}
