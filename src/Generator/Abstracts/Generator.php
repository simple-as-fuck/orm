<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

abstract class Generator
{
    /**
     * method update all generated files
     *
     * @param ModelStructure[] $modelsStructure
     * @throws \League\Flysystem\FileNotFoundException
     */
    final public function save(array $modelsStructure, bool $stupidDeveloper): void
    {
        $fileSystem = $this->createFilesystem();
        if (! $fileSystem->has('Generated')) {
            $fileSystem->createDir('Generated');
        }

        $existingFiles = $this->loadExistingFilePaths($fileSystem);
        $files = $this->internalGenerate($modelsStructure, $stupidDeveloper);
        foreach ($files as $file) {
            if (! $file->isEditable() || ! $fileSystem->has($file->getPath())) {
                $fileSystem->put($file->getPath(), $file->getContent());
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
     * @throws \Exception
     */
    final public function check(array $modelsStructure, bool $stupidDeveloper): void
    {
        $fileSystem = $this->createFilesystem();

        $existingFiles = $this->loadExistingFilePaths($fileSystem);
        $files = $this->internalGenerate($modelsStructure, $stupidDeveloper);
        foreach ($files as $file) {
            if (! $fileSystem->has($file->getPath())) {
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
     * @return GeneratedFile[]
     */
    abstract protected function generate(array $modelsStructure, bool $stupidDeveloper): array;

    abstract protected function getOutputPath(): string;

    protected function generatePrimaryKeyType(ModelStructure $modelStructure): string
    {
        if (count($modelStructure->getPrimaryKeys()) > 1) {
            return $modelStructure->getName().'Key';
        }

        return $modelStructure->getPrimaryKeys()[array_key_first($modelStructure->getPrimaryKeys())]->getType();
    }

    /**
     * @param ModelStructure[] $modelsStructure
     * @return GeneratedFile[]
     */
    private function internalGenerate(array $modelsStructure, bool $stupidDeveloper): array
    {
        $files = $this->generate($modelsStructure, $stupidDeveloper);

        if ($stupidDeveloper) {
            $files[] = new GeneratedFile('Generated/.gitignore', "*\n!.gitignore\n");
        }

        return $files;
    }

    private function createFilesystem(): FilesystemInterface
    {
        return new Filesystem(new Local($this->getOutputPath()));
    }

    /**
     * @return string[]
     */
    private function loadExistingFilePaths(FilesystemInterface $filesystem): array
    {
        $paths = [];
        $contents = $filesystem->listContents('', true);
        foreach ($contents as $content) {
            if ($content['type'] !== 'file') {
                continue;
            }

            $paths[] = $content['path'];
        }

        return $paths;
    }
}
