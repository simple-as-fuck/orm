<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;
use \League\Flysystem\FilesystemInterface;
use \SimpleAsFuck\Orm\Config\Abstracts\Config;

abstract class Generator
{
    /** @var Config */
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * method update all generated files
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function save(): void
    {
        $fileSystem = $this->createFilesystem();

        $existingFiles = $this->loadExistingFilePaths($fileSystem);
        $files = $this->internalGenerate();
        foreach ($files as $file) {
            if (! $file->isEditable()) {
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
     * @throws \Exception
     */
    public function check(): void
    {
        $fileSystem = $this->createFilesystem();

        $existingFiles = $this->loadExistingFilePaths($fileSystem);
        $files = $this->internalGenerate();
        foreach ($files as $file) {
            if (! $fileSystem->has($file->getPath())) {
                throw new \RuntimeException('Generated file "'.$this->getOutputDir().'/'.$file->getPath().'" missing');
            }

            if ($fileSystem->read($file->getPath()) !== $file->getContent() && ! $file->isEditable()) {
                throw new \RuntimeException('Generated file "'.$this->getOutputDir().'/'.$file->getPath().'" has wrong content');
            }

            $existingKeys = array_keys($existingFiles, $file->getPath());
            foreach ($existingKeys as $key) {
                unset($existingFiles[$key]);
            }
        }

        if (count($existingFiles) != 0) {
            $paths = [];
            foreach ($existingFiles as $existingFile) {
                $paths[] = $this->getOutputDir().'/'.$existingFile;
            }

            throw new \RuntimeException('Output dir contains redundant files: "'.implode(', ', $paths).'"');
        }
    }

    /**
     * @return GeneratedFile[]
     */
    abstract protected function generate(): array;

    abstract protected function getOutputDir(): string;

    /**
     * @return GeneratedFile[]
     */
    private function internalGenerate(): array
    {
        $files = $this->generate();

        if ($this->config->getValue('stupid-programmer')) {
            $files[] = new GeneratedFile('Generated/.gitignore', "*\n!.gitignore\n");
        }

        return $files;
    }

    private function createFilesystem(): FilesystemInterface
    {
        $filesystem =  new Filesystem(new Local($this->getOutputDir()));
        if (! $filesystem->has('Generated')) {
            $filesystem->createDir('Generated');
        }

        return $filesystem;
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
