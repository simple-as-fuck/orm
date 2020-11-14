<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

final class TypeTemplates
{
    private string $type;
    /** @var string provide algorithm how is value read from model property, example $value.'->toString()' */
    private string $readValueTemplate;
    /** @var string provide algorithm how is value write into model property, example new \SomeType('.$value.') */
    private string $writeValueTemplate;

    public function __construct(string $type, string $readValueTemplate, string $writeValueTemplate)
    {
        $this->type = $type;
        $this->readValueTemplate = $readValueTemplate;
        $this->writeValueTemplate = $writeValueTemplate;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getReadValueTemplate(): string
    {
        return $this->readValueTemplate;
    }

    public function getWriteValueTemplate(): string
    {
        return $this->writeValueTemplate;
    }
}
