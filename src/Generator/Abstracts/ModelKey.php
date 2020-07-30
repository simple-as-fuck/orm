<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

final class ModelKey
{
    private string $name;
    private string $comment;
    private string $type;
    /** @var bool primary key property is assignable by developer, of is loaded from persist layer */
    private bool $assignable;

    public function __construct(string $name, string $comment, string $type, bool $assignable)
    {
        $this->name = $name;
        $this->comment = $comment;
        $this->type = $type;
        $this->assignable = $assignable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isAssignable(): bool
    {
        return $this->assignable;
    }
}
