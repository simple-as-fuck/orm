<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Config\Abstracts;

abstract class Config
{
    final public function getString(string $key): string
    {
        $value = $this->getValue($key);
        if (! is_string($value)) {
            throw new \RuntimeException('Config key: "'.$key.'" is not string');
        }

        return $value;
    }

    /**
     * @return string[]
     */
    final public function getArrayOfString(string $key): array
    {
        $values = $this->getArray($key);
        foreach ($values as $value) {
            if (! is_string($value)) {
                throw new \RuntimeException('Config key: "'.$key.'" is not array of string');
            }
        }

        return $values;
    }

    /**
     * @return mixed
     */
    abstract protected function getValue(string $key);

    /**
     * @return mixed[]
     */
    final private function getArray(string $key): array
    {
        $value = $this->getValue($key);
        if (! is_array($value)) {
            throw new \RuntimeException('Config key: "'.$key.'" is not array');
        }

        return $value;
    }
}
