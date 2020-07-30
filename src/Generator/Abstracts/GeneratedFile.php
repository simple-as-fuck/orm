<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

final class GeneratedFile
{
    /** @var string path in filesystem where will be file saved  */
    private string $path;
    private string $content;
    /** @var bool define if generated file is editable by developer or updated every generation */
    private bool $editable;

    public function __construct(string $path, string $content, bool $editable = false)
    {
        $this->path = $path;
        $this->content = $content;
        $this->editable = $editable;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isEditable(): bool
    {
        return $this->editable;
    }
}
