<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemReader;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\StorageAttributes;
use SimpleAsFuck\Orm\Config\Abstracts\Config;
use SimpleAsFuck\Orm\Database\Mysql\Connection;
use SimpleAsFuck\Orm\Generator\Abstracts\DirectoryGenerator;
use SimpleAsFuck\Orm\Generator\Abstracts\GeneratedDirectory;
use SimpleAsFuck\Orm\Generator\Abstracts\StructureLoader;
use SimpleAsFuck\Orm\Generator\DirectoryGenerator\ModelAndRepository;
use SimpleAsFuck\Orm\Generator\StructureLoader\Mysql;
use SimpleAsFuck\Orm\Generator\Renderer\BasicRenderer;

final class Generator
{
    private StructureLoader $structureLoader;
    private DirectoryGenerator $directoryGenerator;

    public function __construct(StructureLoader $structureLoader, DirectoryGenerator $directoryGenerator)
    {
        $this->structureLoader = $structureLoader;
        $this->directoryGenerator = $directoryGenerator;
    }

    /**
     * method create generator which generate files from MySql structure
     *
     * @param Config|null $config if null \SimpleAsFuck\Orm\Config\Defaults\Config is used
     * @param Connection|null $connection if null \SimpleAsFuck\Orm\Database\Mysql\Connection is used with 'mysql-...' configuration
     * @param DirectoryGenerator|null $directoryGenerator if null \SimpleAsFuck\Orm\Generator\DirectoryGenerator\ModelAndRepository is used
     */
    public static function createMysql(Config $config = null, Connection $connection = null, DirectoryGenerator $directoryGenerator = null): Generator
    {
        if (! $config) {
            $config = new \SimpleAsFuck\Orm\Config\Defaults\Config();
        }

        if (! $connection) {
            $connection = new Connection(
                $config->getString('mysql-host'),
                $config->getInt('mysql-port'),
                $config->getString('mysql-username'),
                $config->getString('mysql-password'),
                $config->getString('mysql-database-name')
            );
        }

        $renderer = new BasicRenderer();

        if (! $directoryGenerator) {
            $directoryGenerator = new ModelAndRepository($config, $renderer);
        }

        $mysqlLoader = new Mysql($connection, $config, $renderer);

        return new Generator($mysqlLoader, $directoryGenerator);
    }

    /**
     * method load stricture and use generators for update all files
     */
    public function generate(bool $stupidDeveloper = true, ?string $databaseName = null): void
    {
        $modelsStructure = $this->structureLoader->loadModels($databaseName);

        $directoriesContent = $this->directoryGenerator->create($modelsStructure, $stupidDeveloper);
        foreach ($directoriesContent as $directoryContent) {
            $this->updateDirectory($directoryContent);
        }
    }

    /**
     * method load structure and use generators for check all files
     *
     * @throws \RuntimeException
     */
    public function check(bool $stupidDeveloper = true, ?string $databaseName = null): void
    {
        $modelsStructure = $this->structureLoader->loadModels($databaseName);

        $directoriesContent = $this->directoryGenerator->create($modelsStructure, $stupidDeveloper);
        foreach ($directoriesContent as $directoryContent) {
            $this->checkDirectory($directoryContent);
        }
    }

    private function updateDirectory(GeneratedDirectory $generatedDirectory): void
    {
        $fileSystem = $this->createFilesystem($generatedDirectory);
        $existingFiles = $this->loadExistingFilePaths($fileSystem);

        foreach ($generatedDirectory->getFiles() as $file) {
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
     * @throws \RuntimeException
     */
    private function checkDirectory(GeneratedDirectory $generatedDirectory): void
    {
        $fileSystem = $this->createFilesystem($generatedDirectory);
        $existingFiles = $this->loadExistingFilePaths($fileSystem);

        foreach ($generatedDirectory->getFiles() as $file) {
            if (! $fileSystem->fileExists($file->getPath())) {
                throw new \RuntimeException('Generated file "'.$generatedDirectory->getPath().'/'.$file->getPath().'" missing');
            }

            if (! $file->isEditable() && $fileSystem->read($file->getPath()) !== $file->getContent()) {
                throw new \RuntimeException('Generated file "'.$generatedDirectory->getPath().'/'.$file->getPath().'" has wrong content');
            }

            $existingKeys = array_keys($existingFiles, $file->getPath());
            foreach ($existingKeys as $key) {
                unset($existingFiles[$key]);
            }
        }

        if (count($existingFiles) != 0) {
            $paths = [];
            foreach ($existingFiles as $existingFile) {
                $paths[] = $generatedDirectory->getPath().'/'.$existingFile;
            }

            throw new \RuntimeException('Output dir contains redundant files: "'.implode(', ', $paths).'"');
        }
    }

    private function createFilesystem(GeneratedDirectory $generatedDirectory): FilesystemOperator
    {
        return new Filesystem(new LocalFilesystemAdapter($generatedDirectory->getPath()));
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
