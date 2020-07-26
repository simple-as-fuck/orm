<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Abstracts;

abstract class Result
{
    /**
     * fetch one row from query result represented by an object
     * return null if all available rows are fetched
     */
    abstract public function fetch(): ?object;
}
