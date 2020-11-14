<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Config\Abstracts;

abstract class Config
{
    final public function getInt(string $key): int
    {
        $value = $this->getValue($key);
        if (! is_int($value)) {
            throw new \RuntimeException('Config key: "'.$key.'" is not int');
        }

        return $value;
    }

    final public function getString(string $key): string
    {
        $value = $this->getValue($key);
        if (! is_string($value)) {
            throw new \RuntimeException('Config key: "'.$key.'" is not string');
        }

        return $value;
    }

    /**
     * @return mixed[]
     */
    final public function getArray(string $key): array
    {
        $value = $this->getValue($key);
        if (! is_array($value)) {
            throw new \RuntimeException('Config key: "'.$key.'" is not array');
        }

        return $value;
    }

    /**
     * @return mixed[] all keys in array are strings
     */
    final public function getMap(string $key): array
    {
        $values = $this->getArray($key);
        foreach (array_keys($values) as $arrayKey) {
            if (! is_string($arrayKey)) {
                throw new \RuntimeException('Config key: "'.$key.'" array not contains all keys as string');
            }
        }

        return $values;
    }

    /**
     * @return string[]
     */
    final public function getArrayOfString(string $key): array
    {
        $values = $this->getArray($key);
        $this->checkArrayOfString($key, $values);
        return $values;
    }

    /**
     * @return string[] all keys in array are strings
     */
    final public function getMapOfString(string $key): array
    {
        $values = $this->getMap($key);
        $this->checkArrayOfString($key, $values);
        return $values;
    }

    /**
     * @return mixed
     */
    abstract protected function getValue(string $key);

    /**
     * @param mixed[] $values
     */
    private function checkArrayOfString(string $key, array $values): void
    {
        foreach ($values as $value) {
            if (! is_string($value)) {
                throw new \RuntimeException('Config key: "'.$key.'" array not contains all values as string');
            }
        }
    }
}
