<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Abstracts;

abstract class Query
{
    /**
     * execute query in database with params and return result, which can be fetch
     *
     * @param string[]|int[]|float[]|null[] $inputParameters
     */
    abstract public function execute(array $inputParameters): Result;
}
