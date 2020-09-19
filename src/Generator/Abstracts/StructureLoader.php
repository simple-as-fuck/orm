<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

abstract class StructureLoader
{
    /**
     * method load structure for all models from some source
     *
     * @return ModelStructure[]
     */
    public function loadModels(): array
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
            $nullableParams = [];
            foreach ($model->getSimpleParams() as $modelProperty) {
                if ($modelProperty->getName() === 'primaryKey') {
                    throw new \LogicException('Model: "'.$model->getName().'" can not have simple param named: "primaryKey"!');
                }

                if ($modelProperty->getDefaultValue() === null) {
                    $simpleParams[] = $modelProperty;
                    continue;
                }

                $nullableParams[] = $modelProperty;
            }

            $simpleParams = array_merge($simpleParams, $nullableParams);

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
}
