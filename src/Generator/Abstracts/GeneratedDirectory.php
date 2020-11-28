<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

final class GeneratedDirectory
{
    private string $path;
    /** @var GeneratedFile[] */
    private array $files;

    /**
     * @param GeneratedFile[] $files
     */
    public function __construct(string $path, array $files)
    {
        $this->path = $path;
        $this->files = $files;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return GeneratedFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}
