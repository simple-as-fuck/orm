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
    private string $type;
    private ?TypeTemplates $typeTemplates;
    private ?string $defaultValue;
    private Renderer $renderer;

    public function __construct(string $name, string $comment, bool $nullable, bool $assignable, string $type, ?TypeTemplates $typeTemplates, ?string $defaultValue, Renderer $renderer)
    {
        $this->name = $name;
        $this->comment = $comment;
        $this->nullable = $nullable;
        $this->assignable = $assignable;
        $this->type = $type;
        $this->typeTemplates = $typeTemplates;
        $this->defaultValue = $defaultValue;
        $this->renderer = $renderer;
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

    /**
     * method finalize conversion code how is value read from php variable into db
     */
    public function renderReadCode(string $variableName): string
    {
        if (! $this->typeTemplates) {
            return $variableName;
        }

        return $this->renderer->renderTemplate($this->typeTemplates->getReadValueTemplate(), ['variableName' => $variableName]);
    }

    /**
     * method finalize conversion code how is value written into php variable from db
     */
    public function renderWriteCode(string $variableName): string
    {
        if (! $this->typeTemplates) {
            return $variableName;
        }

        return $this->renderer->renderTemplate($this->typeTemplates->getWriteValueTemplate(), ['variableName' => $variableName]);
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
