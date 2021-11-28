<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Config\Abstracts;

use SimpleAsFuck\Validator\Factory\Validator;
use SimpleAsFuck\Validator\Rule\ArrayRule\TypedKey;

abstract class Config
{
    final public function getInt(string $key): int
    {
        return Validator::make($this->getValue($key))->int()->notNull();
    }

    final public function getString(string $key): string
    {
        return Validator::make($this->getValue($key))->string()->notNull();
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
     * @return array<string, mixed>
     */
    final public function getMap(string $key): array
    {
        return $this->checkArrayStringKey($key, $this->getArray($key));
    }

    /**
     * @return array<string>
     */
    final public function getArrayOfString(string $key): array
    {
        return Validator::make($this->getValue($key))->array()->of(fn (TypedKey $key) => $key->string()->notNull())->notNull();
    }

    /**
     * @return array<string, string>
     */
    final public function getMapOfString(string $key): array
    {
        return $this->checkArrayStringKey($key, $this->getArrayOfString($key));
    }

    /**
     * @return mixed
     */
    abstract protected function getValue(string $key);

    /**
     * @template TValue
     * @param array<TValue> $array
     * @return array<string, TValue>
     */
    private function checkArrayStringKey(string $key, array $array): array
    {
        foreach (array_keys($array) as $arrayKey) {
            if (! is_string($arrayKey)) {
                throw new \RuntimeException('Config key: "'.$key.'" array not contains all keys as string');
            }
        }

        return $array;
    }
}
