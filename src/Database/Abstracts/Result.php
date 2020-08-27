<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Database\Abstracts;

abstract class Result
{
    /**
     * fetch one row from query result represented by an object
     * return null if all available rows are fetched
     */
    abstract public function fetch(): ?\stdClass;

    /**
     * fetch all rows from query result represented by an array of object
     *
     * @return \stdClass[]
     */
    final public function fetchAll(): array
    {
        $objects = [];
        while (true) {
            $row = $this->fetch();
            if (! $row) {
                break;
            }

            $objects[] = $row;
        }

        return $objects;
    }
}
