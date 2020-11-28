<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemReader;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\StorageAttributes;

abstract class Generator
{
    /**
     * method update all generated files
     *
     * @param ModelStructure[] $modelsStructure
     */
    final public function generate(array $modelsStructure, bool $stupidDeveloper): void
    {
        $fileSystem = $this->createFilesystem();

        $existingFiles = $this->loadExistingFilePaths($fileSystem);
        $files = $this->internalCreateFiles($modelsStructure, $stupidDeveloper);
        foreach ($files as $file) {
            if (! $file->isEditable() || ! $fileSystem->fileExists($file->getPath())) {
                $fileSystem->write($file->getPath(), $file->getContent());
            }

            $existingKeys = array_keys($existingFiles, $file->getPath());
            foreach ($existingKeys as $key) {
                unset($existingFiles[$key]);
            }
        }

        foreach ($existingFiles as $existingFile) {
            $fileSystem->delete($existingFile);
        }
    }

    /**
     * method check if all files are updated
     *
     * @param ModelStructure[] $modelsStructure
     * @throws \RuntimeException
     */
    final public function check(array $modelsStructure, bool $stupidDeveloper): void
    {
        $fileSystem = $this->createFilesystem();

        $existingFiles = $this->loadExistingFilePaths($fileSystem);
        $files = $this->internalCreateFiles($modelsStructure, $stupidDeveloper);
        foreach ($files as $file) {
            if (! $fileSystem->fileExists($file->getPath())) {
                throw new \RuntimeException('Generated file "'.$this->getOutputPath().'/'.$file->getPath().'" missing');
            }

            if (! $file->isEditable() && $fileSystem->read($file->getPath()) !== $file->getContent()) {
                throw new \RuntimeException('Generated file "'.$this->getOutputPath().'/'.$file->getPath().'" has wrong content');
            }

            $existingKeys = array_keys($existingFiles, $file->getPath());
            foreach ($existingKeys as $key) {
                unset($existingFiles[$key]);
            }
        }

        if (count($existingFiles) != 0) {
            $paths = [];
            foreach ($existingFiles as $existingFile) {
                $paths[] = $this->getOutputPath().'/'.$existingFile;
            }

            throw new \RuntimeException('Output dir contains redundant files: "'.implode(', ', $paths).'"');
        }
    }

    /**
     * @param ModelStructure[] $modelsStructure
     * @return GeneratedDirectory[]
     */
    abstract protected function createClasses(array $modelsStructure, bool $stupidDeveloper): array;

    abstract protected function getOutputPath(): string;

    /**
     * @param ModelStructure[] $modelsStructure
     * @return GeneratedFile[]
     */
    private function internalCreateFiles(array $modelsStructure, bool $stupidDeveloper): array
    {
        $files = $this->createFiles($modelsStructure, $stupidDeveloper);

        if ($stupidDeveloper) {
            $files[] = new GeneratedFile('Generated/.gitignore', "*\n!.gitignore\n");
        }

        return $files;
    }

    private function createFilesystem(): FilesystemOperator
    {
        return new Filesystem(new LocalFilesystemAdapter($this->getOutputPath()));
    }

    /**
     * @return string[]
     */
    private function loadExistingFilePaths(FilesystemReader $filesystem): array
    {
        return $filesystem
            ->listContents('', true)
            ->filter(fn (StorageAttributes $attributes): bool => $attributes->isFile())
            ->map(fn (StorageAttributes $attributes): string => $attributes->path())
            ->toArray()
        ;
    }
}
