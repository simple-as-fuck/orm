<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

final class ModelStructure
{
    private string $name;
    private string $comment;
    /** @var ModelKey[] array of model properties identify one unique instance */
    private array $primaryKeys;
    /** @var ModelProperty[] array of model properties for frequently selecting from database */
    private array $additionalKeys;
    /** @var ModelProperty[] array of rest model properties */
    private array $simpleParams;

    /**
     * @param ModelKey[] $primaryKeys
     * @param ModelProperty[] $additionalKeys
     * @param ModelProperty[] $simpleParams
     */
    public function __construct(string $name, string $comment, array $primaryKeys, array $additionalKeys, array $simpleParams)
    {
        $this->name = $name;
        $this->comment = $comment;
        $this->primaryKeys = $primaryKeys;
        $this->additionalKeys = $additionalKeys;
        $this->simpleParams = $simpleParams;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @return ModelKey[]
     */
    public function getPrimaryKeys(): array
    {
        return $this->primaryKeys;
    }

    /**
     * @return ModelProperty[]
     */
    public function getAdditionalKeys(): array
    {
        return $this->additionalKeys;
    }

    /**
     * @return ModelProperty[]
     */
    public function getSimpleParams(): array
    {
        return $this->simpleParams;
    }

    /**
     * @return ModelProperty[]
     */
    public function getProperties(): array
    {
        return array_merge($this->additionalKeys, $this->simpleParams);
    }
}
