<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\DirectoryGenerator;

use SimpleAsFuck\Orm\Config\Abstracts\Config;
use SimpleAsFuck\Orm\Generator\Abstracts\DirectoryGenerator;
use SimpleAsFuck\Orm\Generator\Abstracts\GeneratedDirectory;
use SimpleAsFuck\Orm\Generator\Abstracts\GeneratedFile;
use SimpleAsFuck\Orm\Generator\Abstracts\ModelStructure;
use SimpleAsFuck\Orm\Generator\Abstracts\Renderer;

final class ModelAndRepository extends DirectoryGenerator
{
    private Config $config;
    private Renderer $renderer;

    public function __construct(Config $config, Renderer $renderer)
    {
        $this->config = $config;
        $this->renderer = $renderer;
    }

    /**
     * @param ModelStructure[] $modelsStructure
     * @return GeneratedDirectory[]
     */
    public function create(array $modelsStructure, bool $stupidDeveloper): array
    {
        $generatedModels = [];
        $generatedRepositories = [];

        foreach ($modelsStructure as $modelStructure) {
            $content = $this->renderer->renderTemplate($this->config->getString('model-template-path'), [
                'modelName' => $modelStructure->getName(),
                'modelComment' => $modelStructure->getComment(),
                'modelNamespace' => $this->config->getString('model-namespace'),
                'stupidDeveloper' => $stupidDeveloper,
            ]);
            $generatedModels[] = new GeneratedFile($modelStructure->getName().'.php', $content, true);

            $content = $this->renderer->renderTemplate($this->config->getString('model-generated-template-path'), [
                'modelStructure' => $modelStructure,
                'modelNamespace' => $this->config->getString('model-namespace').'\\Generated',
                'stupidDeveloper' => $stupidDeveloper,
            ]);
            $generatedModels[] = new GeneratedFile('Generated/'.$modelStructure->getName().'.php', $content);

            $content = $this->renderer->renderTemplate($this->config->getString('model-result-template-path'), [
                'modelStructure' => $modelStructure,
                'modelNamespace' => $this->config->getString('model-namespace'),
                'resultNamespace' => $this->config->getString('model-namespace').'\\Generated',
                'stupidDeveloper' => $stupidDeveloper,
            ]);
            $generatedModels[] = new GeneratedFile('Generated/'.$modelStructure->getName().'Result.php', $content);

            if ($stupidDeveloper) {
                $generatedModels[] = new GeneratedFile('Generated/.gitignore', "*\n!.gitignore\n");
            }

            $content = $this->renderer->renderTemplate($this->config->getString('repository-template-path'), [
                'modelName' => $modelStructure->getName(),
                'modelComment' => $modelStructure->getComment(),
                'repositoryNamespace' => $this->config->getString('repository-namespace'),
                'stupidDeveloper' => $stupidDeveloper,
            ]);
            $generatedRepositories[] = new GeneratedFile($modelStructure->getName().'Repository.php', $content, true);

            $content = $this->renderer->renderTemplate($this->config->getString('repository-generated-template-path'), [
                'modelNamespace' => $this->config->getString('model-namespace'),
                'modelStructure' => $modelStructure,
                'repositoryNamespace' => $this->config->getString('repository-namespace').'\\Generated',
                'stupidDeveloper' => $stupidDeveloper,
            ]);
            $generatedRepositories[] = new GeneratedFile('Generated/'.$modelStructure->getName().'Repository.php', $content);

            if ($stupidDeveloper) {
                $generatedRepositories[] = new GeneratedFile('Generated/.gitignore', "*\n!.gitignore\n");
            }
        }

        return [
            new GeneratedDirectory($this->config->getString('model-output-path'), $generatedModels),
            new GeneratedDirectory($this->config->getString('repository-output-path'), $generatedRepositories),
        ];
    }
}
