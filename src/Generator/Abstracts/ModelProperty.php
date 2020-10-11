<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

final class ModelProperty
{
    private string $name;
    private string $comment;
    private bool $nullable;
    /** @var bool primary key property is assignable by developer, of is loaded from persist layer */
    private bool $assignable;
    /** @var string small code used while code generation, define how is property read from model into database query (often property name) */
    private string $readConversionCode;
    /** @var string small code used while code generation, define how is property write from database into model (often property name) */
    private string $writeConversionCode;
    private string $type;
    private ?string $defaultValue;

    public function __construct(string $name, string $comment, bool $nullable, bool $assignable, string $readConversionCode, string $writeConversionCode, string $type, ?string $defaultValue)
    {
        $this->name = $name;
        $this->comment = $comment;
        $this->nullable = $nullable;
        $this->assignable = $assignable;
        $this->readConversionCode = $readConversionCode;
        $this->writeConversionCode = $writeConversionCode;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isAssignable(): bool
    {
        return $this->assignable;
    }

    public function getReadConversionCode(): string
    {
        return $this->readConversionCode;
    }

    public function getWriteConversionCode(): string
    {
        return $this->writeConversionCode;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }
}
