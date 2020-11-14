<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

use SimpleAsFuck\Orm\Config\Abstracts\Config;

abstract class StructureLoader
{
    private Config $config;
    private Renderer $renderer;

    public function __construct(Config $config, Renderer $renderer)
    {
        $this->config = $config;
        $this->renderer = $renderer;
    }

    /**
     * method load structure for all models from some source
     *
     * @return ModelStructure[]
     */
    final public function loadModels(): array
    {
        $models = $this->loadStructure();
        $checkedModels = [];

        foreach ($models as $model) {
            if (count($model->getPrimaryKeys()) === 0) {
                throw new \LogicException('For model: "'.$model->getName().'" are primaryKeys empty. All models must have one or more property as primary (unique) key!');
            }

            $primaryKeys = [];
            $notAssignableKeys = [];
            foreach ($model->getPrimaryKeys() as $primaryKey) {
                if (! $primaryKey->isAssignable()) {
                    $notAssignableKeys[] = $primaryKey;
                    continue;
                }

                $primaryKeys[] = $primaryKey;
            }

            $primaryKeys = array_merge($primaryKeys, $notAssignableKeys);

            if (count($notAssignableKeys) > 1) {
                throw new \LogicException('Model: "'.$model->getName().'" has to many not assignable primaryKeys. Maximum of loaded primary keys from persist layer is one!');
            }

            foreach ($model->getAdditionalKeys() as $modelProperty) {
                if ($modelProperty->getName() === 'primaryKey') {
                    throw new \LogicException('Model: "'.$model->getName().'" can not have additional key named: "primaryKey"!');
                }
            }

            $simpleParams = [];
            $paramsWithDefaultValues = [];
            foreach ($model->getSimpleParams() as $modelProperty) {
                if ($modelProperty->getName() === 'primaryKey') {
                    throw new \LogicException('Model: "'.$model->getName().'" can not have simple params named: "primaryKey"!');
                }

                if ($modelProperty->getDefaultValue() === null) {
                    $simpleParams[] = $modelProperty;
                    continue;
                }

                $paramsWithDefaultValues[] = $modelProperty;
            }

            $simpleParams = array_merge($simpleParams, $paramsWithDefaultValues);

            $checkedModels[] = new ModelStructure($model->getName(), $model->getComment(), $primaryKeys, $model->getAdditionalKeys(), $simpleParams);
        }

        return $checkedModels;
    }

    /**
     * method load structure from some source
     *
     * @return ModelStructure[]
     */
    abstract protected function loadStructure(): array;

    final protected function convertType(string $table, string $column, string $type): string
    {
        $typeMaps = [];
        $typeMaps[$table.'.'.$column] = $this->config->getMapOfString('database-table-map');
        $typeMaps[$column] = $this->config->getMapOfString('database-column-map');
        $typeMaps[$type] = $this->config->getMapOfString('database-type-map');

        foreach ($typeMaps as $searchKey => $typeMap) {
            if (array_key_exists($searchKey, $typeMap)) {
                return $typeMap[$searchKey];
            }
        }

        return 'string';
    }

    final protected function makeModelProperty(string $name, string $comment, bool $nullable, bool $assignable, string $type, ?string $defaultValue): ModelProperty
    {
        $typeMap = $this->config->getMap('custom-type-templates');
        $typeTemplates = null;
        if (array_key_exists($type, $typeMap)) {
            $typeTemplates = new TypeTemplates($type, $typeMap[$type]['read'], $typeMap[$type]['write']);
        }

        return new ModelProperty($name, $comment, $nullable, $assignable, $type, $typeTemplates, $defaultValue, $this->renderer);
    }
}
